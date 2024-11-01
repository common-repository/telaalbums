<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package telaalbums_Admin
 * @author  Isaac Brown <xevidos@gmail.com>
 */
class telaalbums_admin {

	/**
	 * Caches the post types object
	 * @var     object
	 */
	protected $post_types = null;

	/**
	 * Slug of the plugin screen.
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * A reference to the public plugin class
	 * @var      object
	 */
	protected $plugin = null;

	/**
	 * Instance of this class.
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct() {
		
		/**
		 * Call $plugin_slug from public plugin class.
		 */
		$this->plugin = telaalbums::get_instance();
		$this->plugin_slug = $this->plugin->get_plugin_slug();
		$this->errors = new WP_Error();
		$this->alternate = '';
		
		add_action( "admin_menu", array( $this, 'add_plugin_admin_menu' ) );
		add_action( "admin_init" , array( $this, 'admin_init' ) );
		
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . "{$this->plugin_slug}.php" );
		add_filter( "plugin_action_links_{$plugin_basename}", array( $this, "add_action_links" ) );
	}
	
	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug . '-settings' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}
	
	/**
	 * Add the option page
	 * Enqueue the javascript and CSS
	 */
	public function add_plugin_admin_menu() {
		
		// Settings
		$this->plugin_screen_hook_suffix = add_menu_page( 'Tela Albums', 'Tela Albums', 'manage_options', $this->plugin_slug, array( $this,'telaalbums' ) );
		add_submenu_page( $this->plugin_slug, 'Settings', 'Settings', 'manage_options', $this->plugin_slug . '-settings', array( $this, 'settings' ) );
		add_submenu_page( $this->plugin_slug, 'Users', 'Users', 'manage_options', $this->plugin_slug . '-users', array( $this,'users' ) );
		add_submenu_page( null, 'Setup', 'Setup', 'manage_options', $this->plugin_slug . '-setup', array( $this,'setup' ) );
		add_submenu_page( null, 'Setup', 'Setup', 'manage_options', $this->plugin_slug . '-redirect-uri', array( $this,'redirect_uri' ) );
	}
	
	public function add_user() {
		
		$setup = get_option( telaalbums::OPTION_SETUP );
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		global $wpdb;
		$table_name = $wpdb->prefix . telaalbums::OPTION_USERS_TABLE;
		$user = $wpdb->get_results( "SELECT * FROM $table_name LIMIT 1;", ARRAY_A );
		
		if( empty( $user ) || ! $setup ) {
			
			update_option( telaalbums::OPTION_SETUP, 'false' );
		} else {
			
			$user = $user[0];
		}
		
		if( isset( $_GET["username"] ) ) {
			
			$data['username'] = str_replace( array( "@gmail.com", "@google.com" ), "", preg_replace( "/\s+/", "", $_GET["username"] ) );
			$data['client_id'] = preg_replace( "/\s+/", "", $user["client_id"] );
			$data['client_secret'] = preg_replace( "/\s+/", "", $user["client_secret"] );
			$query = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE username=%s;', $data['username']  );
			$entry = $wpdb->get_results( $query );
			$redirect_uri = urlencode( admin_url() . "admin.php?page=telaalbums-redirect-uri" );
			
			if ( ! empty( $entry ) ) {
				
				$result = $wpdb->update( $table_name, $data, array( "username" => $data["username"] ), array( "%s", "%s" ) );
			} else {
				
				$result = $wpdb->insert( $table_name, $data, array( "%s", "%s", "%s" ) );
			}
			
			?>
			<script>
				window.location.href = "<?php echo "https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/photoslibrary&response_type=code&access_type=offline&redirect_uri={$redirect_uri}&approval_prompt=force&client_id=" . urlencode( $user["client_id"] );?>"
			</script>
			<?php
			die();
		} else {
			
			$username = "";
		}
		
		?>
		<div class="telaalbum_card">
			<h3>Add User:</h3>
			<form id='project_creds' method='GET'>
				<label style="display: block;">Username:</label>
				<input type="text" name="username">
				<input type="hidden" name="page" value="telaalbums-users">
				<input type="submit" value="Add User">
			</form>
		</div>
		<?php
	}
	
	/**
	 * Register the javascript
	 *
	 * @return null If the page being displayed isn't the plugin admin page, return
	 */
	public function admin_enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), telaalbums::VERSION );
	}

	/**
	 * Register the CSS stylesheet
	 *
	 * @return null If the page being displayed isn't the plugin admin page, return
	 */
	public function admin_enqueue_styles() {
		
		wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), telaalbums::VERSION );
	}
	
	
	public function admin_notices() {
		
		if ( get_option( telaalbums::OPTION_SETUP ) === "true" ) {
			?>
			<div class="notice notice-error">
				<p><?php _e( "Tela Albums will not work unless you go through the setup process.  <a href='" . admin_url() . "admin.php?page=telaalbums-users'>Click here to add a user</a>", '' ); ?></p>
			</div>
			<?php
		}
	}
	
	public function admin_init() {
		
		//Access Section
		add_settings_section(
			'settings-section',
			'Settings',
			array( $this,'settings_callback'),
			$this->plugin_slug . '-settings'
		);
		
		add_settings_field(
			telaalbums::OPTION_ALLOW_DOWNLOAD,
			'Allow Downloading Origionals',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALLOW_DOWNLOAD, 'Determines whether the user can download the original full size image.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_CAPTION_LENGTH,
			'Albums Caption Length',
			array( $this,'number_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_CAPTION_LENGTH, 'What is the max amount of characters an album name can be?  Warning:  Long names can break the layout of albums or your site.  0 means no name at all' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_COUNT,
			'Album Count',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_COUNT, 'Show the count of albums and photos beside Gallery header.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_HEADING,
			'Album Heading',
			array( $this,'text_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_HEADING, 'Default: default, leave blank for no header' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_PER_PAGE,
			'Albums Per Page',
			array( $this,'number_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_PER_PAGE, 'Albums per page. Zero means don\'t paginate.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_THUMBNAIL_SIZE,
			'Album Thumbnail Size',
			array( $this,'size_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_THUMBNAIL_SIZE, 'Size of album thumbnails.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_CROP_THUMBNAILS,
			'Crop Thumbnails',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_CROP_THUMBNAILS, 'Crop image thumbnails to square size or use actual ratio?' )
		);
		
		add_settings_field(
			telaalbums::OPTION_DISPLAY_PUBLIC_ONLY,
			'Display Public Albums Only',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_DISPLAY_PUBLIC_ONLY, 'Display only public albums?' )
		);
		
		add_settings_field(
			telaalbums::OPTION_FILTER_CHARACTER,
			'Filter Character',
			array( $this,'text_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_FILTER_CHARACTER, 'Default: _' )
		);
		
		add_settings_field(
			telaalbums::OPTION_GALLERY_HEADING,
			'Gallery Heading',
			array( $this,'text_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_GALLERY_HEADING, 'Default: default, leave blank for no header' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTOS_CAPTION_LENGTH,
			'Photos Caption Length',
			array( $this,'number_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTOS_CAPTION_LENGTH, 'What is the max amount of characters a photo name can be?  Warning:  Long names or can break the layout of albums or your site. 0 means no name at all' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTOS_PER_PAGE,
			'Photos Per Page',
			array( $this,'number_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTOS_PER_PAGE, 'Photos per page. Zero means don\'t paginate.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTOS_SIZE,
			'Photos Size',
			array( $this,'size_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTOS_SIZE, 'Photo Size.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTOS_THUMBNAIL_SIZE,
			'Photo Thumbnail Size',
			array( $this,'size_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTOS_THUMBNAIL_SIZE, 'Photo Thumbnail Size.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_REQUIRE_FILTER,
			'Require Filter',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_REQUIRE_FILTER, 'Do not display any albums other than ones with the filter specified.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_TRUNCATE_ALBUM_NAMES,
			'Truncate Album Names',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_TRUNCATE_ALBUM_NAMES, 'Do not display full album names.  This fixes layout issues.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_TRUNCATE_PHOTO_NAMES,
			'Truncate Photo Names',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_TRUNCATE_PHOTO_NAMES, 'Do not display full photo names.  This fixes layout issues.' )
		);
		
		//Style Section
		add_settings_field(
			telaalbums::OPTION_ALBUM_GRAYSCALE,
			'Album Grayscale',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_GRAYSCALE, 'Show Grayscale on hover over album.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_SHADOW,
			'Album Shadow',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_SHADOW, 'Show shadow behind album.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_ALBUM_ROTATE,
			'Album Rotate',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_ALBUM_ROTATE, 'Rotate on hover over album.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTO_GRAYSCALE,
			'Photo Grayscale',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTO_GRAYSCALE, 'Show Grayscale on hover over photo.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTO_SHADOW,
			'Photo Shadow',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTO_SHADOW, 'Show shadow behind photo.' )
		);
		
		add_settings_field(
			telaalbums::OPTION_PHOTO_ROTATE,
			'Photo Rotate',
			array( $this,'true_field_callback' ),
			$this->plugin_slug . '-settings',
			'settings-section',
			array( telaalbums::OPTION_PHOTO_ROTATE, 'Rotate on hover over photo.' )
		);
		
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_CAPTION_LENGTH, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_COUNT, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_GRAYSCALE, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_HEADING, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_PER_PAGE, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_ROTATE, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_SHADOW, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALBUM_THUMBNAIL_SIZE, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_ALLOW_DOWNLOAD, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_CROP_THUMBNAILS, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_DISPLAY_ALBUM_DETAILS, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_DISPLAY_PUBLIC_ONLY, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_FILTER_CHARACTER, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_GALLERY_HEADING, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTO_GRAYSCALE, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTO_ROTATE, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTO_SHADOW, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTOS_CAPTION_LENGTH, array( $this, 'sanitize_text' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTOS_PER_PAGE, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTOS_SIZE, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_PHOTOS_THUMBNAIL_SIZE, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_REQUIRE_FILTER, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_TRUNCATE_ALBUM_NAMES, array( $this, 'sanitize_number' ) );
		register_setting( "{$this->plugin_slug}-settings", telaalbums::OPTION_TRUNCATE_PHOTO_NAMES, array( $this, 'sanitize_number' ) );
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		if ( null == self::$instance ) {
			
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function redirect_uri() {
		
		global $wpdb;
		$table_name = $wpdb->prefix . telaalbums::OPTION_USERS_TABLE;
		$data = $wpdb->get_results( 'SELECT * FROM ' . $table_name . ' ORDER BY id DESC LIMIT 1;', ARRAY_A )[0];
		$client_id 	   = $data['client_id'];
		$client_secret = $data['client_secret'];
		$curl = curl_init();
		$redirect_uri = admin_url() . "admin.php?page=telaalbums-redirect-uri";
		$site_url 	   = admin_url();
		$username 	   = $data['username'];
		$date 		   = date( "U" );
		$post_body = 'access_type=offline'
		.'&code=' . $_GET['code']
		.'&grant_type=authorization_code'
		.'&redirect_uri='.$redirect_uri
		.'&client_id='.$client_id
		.'&client_secret='.$client_secret;
		
		curl_setopt_array( $curl,
			array(
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token',
				CURLOPT_HTTPHEADER => array( "Content-Type: application/x-www-form-urlencoded",
					"Content-Length: ". strlen( $post_body ),
					"User-Agent: Telaaedifex's Albums/0.2 +https://telaaedifex.com/albums"
				),
				CURLOPT_POSTFIELDS => $post_body,
				CURLOPT_REFERER => $redirect_uri, 
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => 12,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_FAILONERROR => 0,
				CURLOPT_SSL_VERIFYPEER => 1,
				CURLOPT_VERBOSE => 0,
			)
		);
		
		$orig_response = curl_exec( $curl );
		$response = json_decode( $orig_response, true );
		$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		curl_close ($curl );
		
		if ( isset( $response['access_token'] ) ) {
			
			$token_expires = date("U") + $response['expires_in'];
			$data['access_token']  = $response['access_token'];
			$data['refresh_token'] = $response['refresh_token'];
			$data['token_expires'] = $token_expires;
			$result = $wpdb->update( $table_name, $data, array( 'username' => $username ), array( "%s", "%s", "%s" ) );
			$uri = $_SERVER["REQUEST_URI"];
			$site_url 	   = admin_url();
			$settings_url  = $site_url . "admin.php?page=telaalbums-settings";
			$users_url  = $site_url . "admin.php?page=telaalbums-users";
			
			?>
			<h2>User added!</h2>
			<p>
				Token retrieved and saved in WordPress configuration database.<br>
				Continue to <a href='<?php echo esc_url( $settings_url );?>'>configure your settings!</a><br>
				Or go to <a href='<?php echo esc_url( $users_url );?>'>to see your users!</a>
			</p>
			<?php
		} else {
			
			?>
			<p>
				Error, could not get access token.  The following information was provided.<br>
				<?php echo esc_html( $orig_response );?>
			</p>
			<?php
		}
	}
	
	public function remove_user() {
		
		if( isset( $_POST["dropuser"] ) ) {
			
			global $wpdb;
			$table_name = $wpdb->prefix . telaalbums::OPTION_USERS_TABLE;
			$wpdb->delete( $table_name, array( 'username' => $_POST["dropuser"] ), array( "%s" ) );
		}
		
		?>
		<div class="telaalbum_card" >
			<h3>Remove User:</h3>
			<form id='drop_user' method='post'>
				<label>Username:</label><br>
				<input required="required" id='dropuser' name='dropuser' style='width:400px;' />
				<input type='hidden' name='page' value='telaalbums-users' />
				<input type='submit' value='Drop User' />
			</form>
		</div>
		<?php
	}
	
	public function settings() {
		
		?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e( 'Tela Albums Options', $this->plugin_slug ); ?></h2>
			<form method="post" action="options.php">
			<?php
				settings_fields( $this->plugin_slug . '-settings' );
				do_settings_sections( $this->plugin_slug . '-settings' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}
	
	public function settings_options_callback() {}
	
	public function settings_instructions_callback() {}
	
	public function settings_callback() {}
	
	public function settings_email_callback() {
		
		?>
		<pre>
		<?php
		$headers = $this->plugin->build_headers( false, true );
		foreach( $headers as $header ) {
			
			echo esc_html( $header ) ."\n";
		}
		?>
		</pre>
		<hr/>
		<?php
	}
    
	public function setup() {
		
		global $wpdb;
		$table_name = $wpdb->prefix . telaalbums::OPTION_USERS_TABLE;
		$redirect_uri = admin_url() . "admin.php?page=telaalbums-redirect-uri";
		
		if( isset( $_POST["save"] ) ) {
			
			
			$data = array();
			$data['username'] = str_replace( array( "@gmail.com", "@google.com" ), "", preg_replace( "/\s+/", "", $_POST["username"] ) );
			$data['client_id'] = preg_replace( "/\s+/", "", $_POST["client_id"] );
			$data['client_secret'] = preg_replace( "/\s+/", "", $_POST["client_secret"] );
			$query = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE username=%s;', $data['username']  );
			
			echo esc_html( $data['username'] ) . "<br>";
			echo esc_html( $data['client_id'] ) . "<br>";
			echo esc_html( $data['client_secret'] ) . "<br>";
			
			$entry = $wpdb->get_results( $query );
			
			if ( ! empty( $entry ) ) {
				
				$result = $wpdb->update( $table_name, $data, array( "username" => $data["username"] ), array( "%s", "%s" ) );
			} else {
				
				$result = $wpdb->insert( $table_name, $data, array( "%s", "%s", "%s" ) );
			}
			
			update_option( telaalbums::OPTION_SETUP, "true" );
			$redirect = "https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/photoslibrary&response_type=code&access_type=offline&redirect_uri=" . esc_url( $redirect_uri ) . "&approval_prompt=force&client_id=" . urlencode( $data['client_id'] );
			?>
			<script>window.location.href = "<?php echo $redirect;?>"</script>
			<?php
			exit();
		}
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$url = ( isset($_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$charset_collate = $wpdb->get_charset_collate();
		$query = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			username text NOT NULL,
			client_id text NOT NULL,
			client_secret text NOT NULL,
			access_token text NOT NULL,
			token_expires text NOT NULL,
			refresh_token text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $query );
		
		?>
		<h1>Setup</h1>
		
		<ol>
			<li>Head to the <a target='_BLANK' href='https://console.developers.google.com/'>Google Developer Console</a> and login</li>
			<li>Click on the project list in the top left hand corner shown below</li>
			<li>A window should pop up, proceed to click the 'New Project' button</li>
			<li>Enter a new project name and click create project</li>
			<li>After the window closes it go back to the drop down and make sure that the current project is the one you just created</li>
			<li>After selecting the project, click on enable APIs and services</li>
			<li>Type Google Photos in the search box and then select it from the list that appears then click the Enable button</li>
			<li>Go to the sidebar and click on the three lines in the top left corner.</li>
			<li>Select APIs & Services -> Credentials</li>
			<li>Select Oauth Consent Screen</li>
			<li>Fill out an Application Name</li>
			<li>Fill out a support email</li>
			<li>Click on Add Scope</li>
			<li>Down at the bottom of the window that pops up click on the link that says manually paste</li>
			<li>In the box that pops up paste: https://www.googleapis.com/auth/photoslibrary</li>
			<li>Click add</li>
			<li>In the Authorized Domains field, enter: <?php echo esc_html( $_SERVER["HTTP_HOST"] );?></li>
			<li>Scroll to the bottom of the page and click Save</li>
			<hr />
			<li>Select the Credentials tab</li>
			<li>Click Create Credentials -> OAuth Client ID</li>
			<li>Select Web Application</li>
			<li>Choose a name</li>
			<li>In the Authorized Javascript Origins box, enter: <?php echo esc_html( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://{$_SERVER['SERVER_NAME']}" );?></li>
			<li>In the Authorized Redirect URIs box, enter: <?php echo esc_html( $redirect_uri );?></li>
			
			
			<form action="" method="post" >
				<label>Client ID</label><br>
				<input required="required" name="client_id" type="text"><br>
				<label>Client Secret</label><br>
				<input required="required" name="client_secret" type="text"><br>
				<label>Google Username</label><br>
				<input required="required" name="username" type="text"><br>
				<input name="save" type='submit' value='Save'><br>
			</form>
		</ol>
		<?php
	}
	
	public function telaalbums() {
		
		wp_enqueue_style( $this->plugin_slug . '-admin-menu-style', plugins_url( 'assets/css/menu.css', __FILE__ ), array(), telaalbums::VERSION );
		wp_enqueue_script( $this->plugin_slug . '-admin-menu-js', plugins_url( 'assets/js/menu.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), telaalbums::VERSION );
		?>
		<div class="telatabs">
			<ul>
				<li><a href="#announcements"><span>Announcements</span></a></li>
				<li><a href="#help"><span>Help</span></a></li>
				<li><a href="#version-info"><span>Version Information</span></a></li>
			</ul>
			<div class="contents">
				<div id="announcements">
					<h2>Announcements</h2>
					<?php
					include_once( ABSPATH . WPINC . '/feed.php' );
					list($ws,$os) = array_pad(explode(" ", $_SERVER['SERVER_SOFTWARE'], 2), 2, null);
					$curlver = curl_version();
					
					// Get a SimplePie feed object from the specified feed source.
					$rss = fetch_feed( 'https://telaaedifex.com/category/telaalbums/feed/' );
					$maxitems = 5;
					
					if ( ! is_wp_error( $rss ) ) {
						
						// Checks that the object is created correctly
						// Figure out how many total items there are, but limit it to 5. 
						$maxitems = $rss->get_item_quantity( 5 ); 
						
						// Build an array of all the items, starting with element 0 (first element).
						$rss_items = $rss->get_items( 0, $maxitems );
					
					}
					
					?>
					<ul>
					<?php
					
					if ( $maxitems == 0 ) {
						
						?>
						<li>No announcements found.</li>;
						<?php
					} else {
						
						foreach ( $rss_items as $item ) {
							
							?>
							<li><a href="<?php echo esc_url( $item->get_permalink() );?>" title="<?php echo esc_html( printf( __( 'Posted %s', 'my-text-domain' ), $item->get_date('j F Y | g:i a') ) );?>" target="_blank"><?php echo esc_html( $item->get_title() );?></a></li>
							<?php
						}
					}
					?>
					</ul>
				</div>
				<div id="help">
					<h2>Help</h2>
					<p>
						If you encounter any issues, head to the <strong><a href='https://telaaedifex.com/albums/' target='_BLANK'>support site</a> or <a href='https://telaaedifex.com/support/' target='_BLANK'>open a ticket</a></strong> in our ticketing system.
						
						
					</p>
				</div>
				<div id="version-info">
					<h2>Version Information</h2>
					<table>
						<tr>
							<th>Version:</th>
							<td><?php echo esc_html( telaalbums::VERSION );?></td>
						</tr>
						<tr>
							<th>Hostname:</th>
							<td><?php echo esc_html( $_SERVER['SERVER_NAME'] );?></td>
						</tr>
						
						<tr>
							<th valign="top">Webserver:</th>
							<td><?php echo esc_html( $ws . $os );?></td>
						</tr>
						<tr>
							<th valign="top">PHP/cURL:</th>
							<td><?php echo esc_html( phpversion() . " / " . $curlver['version'] );?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
    
    public function users() {
		
		global $wpdb;
		$labels = array(
			'id',
			'username',
			'client_id',
			'client_secret',
			'access_token',
			'token_expires',
			'refresh_token',
		);
		$table_name = $wpdb->prefix . telaalbums::OPTION_USERS_TABLE;
		?>
		<style>
			
			.telaalbum_card {
				
				display: inline-block;
				margin-right: 10px;
				vertical-align: top;
			}
			
			table {
				width:100%;
				margin-top: 25px;
				table-layout: fixed;
				background-color: #FFFFFF;
			}
			
			th {
				padding: 20px 15px;
				text-align: left;
				font-weight: 500;
				font-size: 12px;
				color: #000000;
				text-transform: uppercase;
				border-bottom: 1px solid #e1e1e1;
			}
			
			td {
				padding: 15px;
				text-align: left;
				vertical-align:middle;
				font-weight: 300;
				font-size: 12px;
				color: #000000;
				border-bottom: solid 1px rgba(255,255,255,0.1);
				word-wrap: break-word;
			}
		</style>
		<h1>Users</h1>
		<?php
		$this->add_user();
		$this->remove_user();
		$usernames  = $wpdb->get_results( "SELECT * FROM {$table_name}" );
		$url = ( isset($_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		if( empty( $usernames ) ) {
			
			update_option( telaalbums::OPTION_SETUP, 'false' );
			?>
			<script>
				let url = ("<?php echo esc_url( $url );?>");
				window.location.href = url.replace( "?page=<?php echo $this->plugin_slug . '-users';?>", "?page=<?php echo $this->plugin_slug . '-setup';?>" );
			</script>
			<?php
			die();
		}
		
		?>
		<table>
			<tr>
			<?php
			foreach( $labels as $field ) {
				
				?>
				<th><?php echo esc_html( $field );?></th>
				<?php
			}
			
			foreach ( $usernames as $user ) {
				
				?>
				<tr>
				<?php
				foreach ( $user as $column => $field ) {
					
					switch( $column ) {
						
						case( "token_expires" ):
							
							?>
							<td><?php echo esc_html( date( "Y-m-d h:i:s A", $field ) );?></td>
							<?php
						break;
						
						default:
							
							?>
							<td><?php echo esc_html( $field );?></td>
							<?php
						break;
					}
				}
				?>
				</tr>
				<?php
			}
			?>
			</tr>
		</table>
		<?php
	}
    
    	/**
	 * Callback function to sanitize the email setting values
	 *
	 * @param  string $option The option value being saved
	 *
	 * @return string       The option value after being sanitized
	 */
	public function sanitize_email( $option ) {
		
		$message = null;
		$type = null;
		
		if ( strstr( $option, "\n" ) ) {
			
			$addresses = explode( "\n", $option );
			foreach( $addresses as $address ) {
				
				$address = str_replace( array( "\r", "\n" ), '', $address );
				if ( ! filter_var( $address, FILTER_VALIDATE_EMAIL ) ) {
					
					$message = __( "Please enter a valid email address. (" . esc_html( $option ) . ")", $this->plugin_slug );
					$type = "error";
					$option = ''; // Reset value
					
					// Add notification for errors or updates
					add_settings_error(
						$this->plugin_slug,
						esc_attr( 'settings_updated' ),
						$message,
						$type
					);
					break;
				}
			}
		} elseif( 0 < strlen($option) ) {
			
			if ( ! filter_var( $option, FILTER_VALIDATE_EMAIL ) ) {
				
				$message = __( "Please enter a valid email address. (" . esc_html($option) . ")", $this->plugin_slug );
				$type = "error";
				$option = ''; // Reset value
				
				// Add notification for errors or updates
				add_settings_error(
					$this->plugin_slug,
					esc_attr( 'settings_updated' ),
					$message,
					$type
				);
			}
		}
		
		return $option;
	}

	public function sanitize_footer_url( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! filter_var( $option, filter_VALIDATE_URL ) ) {
			
			$message = __( "Please enter a valid footer URL.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}
	
	public function sanitize_header_url( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! filter_var( $option, filter_VALIDATE_URL ) ) {
			
			$message = __( "Please enter a valid header URL.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}
	
	/**
	 * Callback function to sanitize the OID setting values
	 *
	 * @param  string $option The option value being saved
	 *
	 * @return string       The option value after being sanitized
	 */
	public function sanitize_oid( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! preg_match( "/^[A-Z0-9]*$/", $option ) ) {
			
			$message = __( "Please enter a valid OID.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}
	
	public function sanitize_optional_redirect_url( $option ) {
		
		$message = null;
		$type = null;
		
		if ( strlen($option) > 0 && ! filter_var( $option, filter_VALIDATE_URL ) ) {
			
			$message = __( "Please enter a valid redirect URL.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}
	
	
	/**
	 * Callback function to sanitize the phone number setting values
	 *
	 * @param  string $option The option value being saved
	 *
	 * @return string       The option value after being sanitized
	 */
	public function sanitize_phone( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! preg_match( "/^[\(\)\-\. 0-9]*$/", $option ) ) {
			
			$message = __( "Please enter a valid phone number.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}

	/**
	 * Callback function to sanitize the URL setting values
	 *
	 * @param  string $option The option value being saved
	 *
	 * @return string       The option value after being sanitized
	 */
	public function sanitize_redirect_url( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! filter_var( $option, filter_VALIDATE_URL ) ) {
			
			$message = __( "Please enter a valid redirect URL.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}

	/**
	 * Callback function to sanitize the text setting values
	 *
	 * @param  string $option The option value being saved
	 *
	 * @return string       The option value after being sanitized
	 */
	public function sanitize_text( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! preg_match( "/^[_a-zA-Z \-\.0-9]*$/", $option ) ) {
			
			$message = __( "Please enter a valid value.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		return $option;
	}
	
	public function sanitize_textarea( $option ) {
		
		$message = null;
		$type = null;
		
		if ( ! preg_match( "/[a-zA-Z \-\.0-9\S]*/s", $option ) ) {
			
			$message = __( "Please enter a valid value.", $this->plugin_slug );
			$type = "error";
			$option = ''; // Reset value
			
			// Add notification for errors or updates
			add_settings_error(
				$this->plugin_slug,
				esc_attr( 'settings_updated' ),
				$message,
				$type
			);
		}
		
		return $option;
	}
    
	/**
	 * Generates the form input for the option
	 *
	 * @param  array $args Additional arguments that are passed to the callback function.
	 *
	 * @return string       The form input HTML
	 */
	public function checkbox_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"regular-text\" type='checkbox' " . ($option ? 'checked' : '') . " />";
		if ( isset( $args[1] ) ) {
			
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function email_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"regular-text\" type='email' value='{$option}' />";
		if ( isset( $args[1] ) ) {
			
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function large_text_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"large-text\" type='text' value='{$option}' />";
		if ( isset( $args[1] ) ) {
			
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function number_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"regular-text\" type=\"num\" value=\"{$option}\" />";
		if ( isset( $args[1] ) ) {
			
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function phone_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"regular-text\" type=\"tel\" value=\"{$option}\" />";
		if ( isset( $args[1] ) ) {
			
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function size_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		$array = array(
			'200',
			'240',
			'288',
			'320',
			'400',
			'512',
			'576',
			'640',
			'720',
			'800',
			'912',
			'1024',
			'1152',
			'1280',
			'1440',
			'1600',
		);
		echo "<select id='$args[0]' name='$args[0]'>";
		foreach( $array as $value ) {
			
			echo "<option value='{$value}' " . selected( $option, $value ) . ">{$value}</option>";
		}
		echo "</select>";
		if( isset( $args[1] ) ) {
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function text_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"regular-text\" type='text' value='{$option}' />";
		if( isset( $args[1] ) ) {
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function textarea_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		echo "<textarea id='{$args[0]}' name='{$args[0]}' class=\"large-text\" rows='7' cols='50' type='textarea'>{$option}</textarea>";
		if( isset( $args[1] ) ) {
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function true_field_callback( $args ) {
		
		$option = get_option( $args[0] );
		$options = array(
			'true',
			'false',
		);
		echo "<select id='$args[0]' name='$args[0]'>";
		foreach( $options as $value ) {
			echo "<option value='{$value}' " . selected( $option, $value ) . ">{$value}</option>";
		}
		echo "</select>";
		if( isset( $args[1] ) ) {
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
	
	public function url_field_callback( $args ) {
		$option = get_option( $args[0] );
		echo "<input id='{$args[0]}' name='{$args[0]}' class=\"large-text\" type='url' value='{$option}' />";
		if ( isset( $args[1] ) ) {
			echo "<span class=\"description description-text\">{$args[1]}</span>";
		}
	}
}