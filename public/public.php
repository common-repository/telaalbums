<?php
/*
 *
 * @package   telaalbums
 * @author    Isaac Brown <xevidos@gmail.com>
 * @license   
 * @link      https://telaaedifex.com
 * @copyright 2018 Isaac Brown
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package telaalbums
 * @author  Isaac Brown <xevidos@gmail.com>
 */
 
class telaalbums {
	
	//Constants
	const DEBUG = false;
	const OPTION_USERS_TABLE = 'telaalbums_users';
	const OPTION_SETUP = 'telaalbums_setup';
	//Access
	const OPTION_ALBUM_COUNT = 'telaalbums_album_count';
	const OPTION_ALBUM_GRAYSCALE = 'telaalbums_album_grayscale';
	const OPTION_ALBUM_HEADING = 'telaalbums_album_heading';
	const OPTION_ALBUM_ROTATE = 'telaalbums_album_rotate';
	const OPTION_ALBUM_SHADOW = 'telaalbums_album_shadow';
	const OPTION_ALBUM_CAPTION_LENGTH = 'telaalbums_albums_caption_length';
	const OPTION_ALBUM_PER_PAGE = 'telaalbums_albums_per_page';
	const OPTION_ALBUM_THUMBNAIL_SIZE = 'telaalbums_albums_thumbnail_size';
	const OPTION_ALLOW_DOWNLOAD = 'telaalbums_allow_download';
	const OPTION_CROP_THUMBNAILS = 'telaalbums_crop_thumbnails';
	const OPTION_DISPLAY_ALBUM_DETAILS = 'telaalbums_display_album_details';
	const OPTION_DISPLAY_PHOTO_CAPTIONS = 'telaalbums_display_album_details';
	const OPTION_DISPLAY_PUBLIC_ONLY = 'telaalbums_display_public_only';
	const OPTION_FILTER_CHARACTER = 'telaalbums_filter_character';
	const OPTION_GALLERY_HEADING = 'telaalbums_gallery_heading';
	const OPTION_HIDE_VIDEOS = 'telaalbums_hide_videos';
	const OPTION_PHOTO_GRAYSCALE = 'telaalbums_photo_grayscale';
	const OPTION_PHOTO_ROTATE = 'telaalbums_photo_rotate';
	const OPTION_PHOTO_SHADOW = 'telaalbums_photo_shadow';
	const OPTION_PHOTOS_CAPTION_LENGTH = 'telaalbums_photos_caption_length';
	const OPTION_PHOTOS_DESCRIPTION_LENGTH = 'telaalbums_photos_description_length';
	const OPTION_PHOTOS_PER_PAGE = 'telaalbums_photos_per_page';
	const OPTION_PHOTOS_SIZE = 'telaalbums_photos_size';	
	const OPTION_PHOTOS_THUMBNAIL_SIZE = 'telaalbums_photos_thumbnail_size';	
	const OPTION_REQUIRE_FILTER = 'telaalbums_require_filter';
	const OPTION_TRUNCATE_ALBUM_NAMES = 'telaalbums_truncate_album_names';
	const OPTION_TRUNCATE_PHOTO_NAMES = 'telaalbums_truncate_photo_names';
	
	const OPTIONS = array(
		self::OPTION_ALBUM_CAPTION_LENGTH => "25",
		self::OPTION_ALBUM_COUNT => "true",
		self::OPTION_ALBUM_GRAYSCALE => "true",
		self::OPTION_ALBUM_HEADING => "default",
		self::OPTION_ALBUM_PER_PAGE => "0",
		self::OPTION_ALBUM_ROTATE => "true",
		self::OPTION_ALBUM_SHADOW => "true",
		self::OPTION_ALBUM_THUMBNAIL_SIZE => "240",
		self::OPTION_ALLOW_DOWNLOAD => "true",
		self::OPTION_CROP_THUMBNAILS => "true",
		self::OPTION_DISPLAY_ALBUM_DETAILS => "false",
		self::OPTION_DISPLAY_PHOTO_CAPTIONS => "false",
		self::OPTION_DISPLAY_PUBLIC_ONLY => "false",
		self::OPTION_GALLERY_HEADING => "default",
		self::OPTION_FILTER_CHARACTER => "_",
		self::OPTION_HIDE_VIDEOS => "false",
		self::OPTION_PHOTO_GRAYSCALE => "true",
		self::OPTION_PHOTO_ROTATE => "true",
		self::OPTION_PHOTO_SHADOW => "true",
		self::OPTION_PHOTOS_CAPTION_LENGTH => "25",
		self::OPTION_PHOTOS_PER_PAGE => "0",
		self::OPTION_PHOTOS_SIZE => "1280",
		self::OPTION_PHOTOS_THUMBNAIL_SIZE => "240",
		self::OPTION_REQUIRE_FILTER => "false",
		self::OPTION_SETUP => "false",
		self::OPTION_TRUNCATE_ALBUM_NAMES => "true",
		self::OPTION_TRUNCATE_PHOTO_NAMES => "true",
	);
	const VERSION = '1.5.2.8';
	
	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'telaalbums';

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	
	/**
	 * Slug of the plugin screen.
	 * @var      string
	 */
	public $plugin_screen_hook_suffix = null;
	
	public $redirect_uri = "";
	
	public $albums = array();
	
	public $i = 0;
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts and styles.
	 */
	private function __construct() {
		
		// Activate plugin when new blog is added
		$this->redirect_uri = admin_url() . "admin.php?page=telaalbums-redirect-uri";
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
		add_action( 'template_redirect', array( $this, 'handle_page_request' ) );
		add_shortcode( $this->plugin_slug, array( $this, 'shortcode' ) );
	}
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			
			if ( $network_wide ) {
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				
				foreach ( $blog_ids as $blog_id ) {
					
					switch_to_blog( $blog_id );
					self::single_activate();
				}
				
				restore_current_blog();
			} else {
				
				self::single_activate();
			}
		} else {
			
			self::single_activate();
		}
	}
	
	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {
		
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			
			return;
		}
		
		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}
	
	public static function create_url_data( $data ) {
		
		return urlencode( base64_encode( json_encode( $data ) ) );
	}
	
	public function curl( &$user_info, $url, $paremeters=array(), $try=0 ) {
		
		if( ! empty( $user_info ) ) {
			
			$this->refresh_token( $user_info );
			$access_token = $user_info["access_token"];
		} else {
			
			$access_token = "";
		}
		
		$curl = curl_init();
		
		if( !empty( $paremeters ) ) {
			
			$protocol = "POST";
		} else {
			
			$protocol = "GET";
		}
		
		$variables = array(
			CURLOPT_CUSTOMREQUEST => $protocol,
			CURLOPT_URL => $url,
			CURLOPT_REFERER => "",
			//output will be a return value from curl_exec() instead of simply echoed
			CURLOPT_RETURNTRANSFER => 1,
			//max seconds to wait
			CURLOPT_TIMEOUT => 12,
			//don't follow any Location headers, use only the CURLOPT_URL, this is for security,
			CURLOPT_FOLLOWLOCATION => 0,
			//do not fail verbosely fi the http_code is an error, this is for security,
			CURLOPT_FAILONERROR => 0,
			//do verify the SSL of CURLOPT_URL, this is for security,
			CURLOPT_SSL_VERIFYPEER => 1,
			// don't output verbosely to stderr, this is for security
			CURLOPT_VERBOSE => 0
		);
		
		if( $access_token !== null ) {
			
			$variables[CURLOPT_HTTPHEADER] = array( 'Authorization: Bearer ' . $access_token );
		} else {
			
			$variables[CURLOPT_HTTPHEADER] = array();
		}
		
		if( !empty( $paremeters ) ) {
			
			if( is_array( $paremeters ) ) {
				
				$payload = json_encode( $paremeters );
				array_push( $variables[CURLOPT_HTTPHEADER], "Content-Type: application/json" );
			} else {
				
				$payload = $paremeters;
			}
			$variables[CURLOPT_POSTFIELDS] = $payload;
			$variables[CURLOPT_POST] = true;
			
			array_push( $variables[CURLOPT_HTTPHEADER], "Content-Length: " . strlen( $payload ) );
			array_push( $variables[CURLOPT_HTTPHEADER], "User-Agent: telaalbums/1.0 +https://telaaedifex.com/albums" );
		}
		
		curl_setopt_array( $curl, $variables );
		$response = curl_exec( $curl );
		curl_close( $curl );
		$result = json_decode( $response, true );
		
		if( isset( $result["error"] ) && $try < 5 ) {
			
			$this->refresh_token( $user_info );
			$result = $this->curl( $user_info, $url, $paremeters, ( ++$try )  );
		} elseif( isset( $result["error"] ) && $try >= 5 ) {
			
			echo esc_html( var_export( $result ) );
		}
		
		return( $result );
	}
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			
			if ( $network_wide ) {
				
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				
				foreach ( $blog_ids as $blog_id ) {
					
					switch_to_blog( $blog_id );
					self::single_deactivate();
				}
				
				restore_current_blog();
			} else {
				
				self::single_deactivate();
			}
		} else {
			
			self::single_deactivate();
		}
	}
	
	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}
	
	
	/**
	 * Register and enqueue public-facing style sheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		
		//Main plugin style sheet
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
		
		//Album optional styles
		if ( get_option( self::OPTION_ALBUM_GRAYSCALE ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-albums-grayscale', plugins_url( 'assets/css/albums/grayscale.css', __FILE__ ), array(), self::VERSION );
		}
		
		if ( get_option( self::OPTION_ALBUM_SHADOW ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-albums-shadow', plugins_url( 'assets/css/albums/shadow.css', __FILE__ ), array(), self::VERSION );
		}
		
		if ( get_option( self::OPTION_ALBUM_ROTATE ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-albums-rotate', plugins_url( 'assets/css/albums/rotate.css', __FILE__ ), array(), self::VERSION );
		}
		
		//Photo optional styles
		if ( get_option( self::OPTION_PHOTO_GRAYSCALE ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-photos-grayscale', plugins_url( 'assets/css/photos/grayscale.css', __FILE__ ), array(), self::VERSION );
		}
		
		if ( get_option( self::OPTION_PHOTO_SHADOW ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-photos-shadow', plugins_url( 'assets/css/photos/shadow.css', __FILE__ ), array(), self::VERSION );
		}
		
		if ( get_option( self::OPTION_PHOTO_ROTATE ) !== "false" ) {
			
			wp_enqueue_style( $this->plugin_slug . '-photos-rotate', plugins_url( 'assets/css/photos/rotate.css', __FILE__ ), array(), self::VERSION );
		}
	}
	
	public function find_album( $atts, &$user_info ) {
		
		$albums = $this->get_albums( $atts, $user_info );
		$id = null;
		
		
		foreach( $albums["albums"] as $album ) {
				
			if( $album["title"] == $atts["album"] || $album["id"] == $atts["album"] ) {
				
				$id = $album;
				break;
			}
		}
		if( $id === null ) {
			
			?>
			<p>Error, could not find album specified ( <?php echo esc_html( $atts["album"] );?> ) for user ( <?php echo esc_html( $user_info["username"] );?> )</p>
			<?php
		}
		
		return $id;
	}
	
	public function get_album( $atts, &$user_info ) {
		
		return $this->find_album( $atts, $user_info );
	}
	
	public function get_albums( $atts, &$user_info ) {
		
		if( ! isset( $this->albums[$user_info["username"]] ) || empty( $this->albums[$user_info["username"]] ) ) {
			
			$url = "https://photoslibrary.googleapis.com/v1/albums";
			$albums = array( "albums" => array() );
			
			do {
				
				if( isset( $result["nextPageToken"] ) && $result["nextPageToken"] != null ) {
					
					$url = "https://photoslibrary.googleapis.com/v1/albums?pageSize=50&pageToken={$result["nextPageToken"]}";
				} else {
					
					$url = "https://photoslibrary.googleapis.com/v1/albums?pageSize=50";
				}
				
				$result = self::curl( $user_info, $url, array() );
				$albums["albums"] = array_merge( $albums["albums"], $result["albums"] );
			} while( isset( $result["nextPageToken"] ) && $result["nextPageToken"] != null );

			if( ! isset( $albums["albums"] ) ) {
				
				echo( var_export( $albums, $result ) );
			}
			
			$this->albums[$user_info["username"]] = $albums;
		} else {
			
			$albums = $this->albums[$user_info["username"]];
		}
		
		return( $albums );
	}
	
	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		
		global $wpdb;
		
		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
		WHERE archived = '0'
		AND spam = '0'
		AND deleted = '0'";
		
		return $wpdb->get_col( $sql );
	}
	
	public static function get_client_ip() {
		
		$ipaddress = '';
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && $_SERVER['HTTP_CLIENT_IP'] ) {
			
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} else if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
			
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if( isset( $_SERVER['HTTP_X_FORWARDED'] ) && $_SERVER['HTTP_X_FORWARDED'] ) {
			
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} else if( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && $_SERVER['HTTP_FORWARDED_FOR'] ) {
			
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if( isset( $_SERVER['HTTP_FORWARDED'] ) && $_SERVER['HTTP_FORWARDED'] ) {
			
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} else if( isset( $_SERVER['REMOTE_ADDR'] ) && $_SERVER['REMOTE_ADDR'] ) {
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			
			$ipaddress = 'UNKNOWN';
		}
		return $ipaddress;
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	public function get_photos( $atts, &$user_info ) {
		
		
		$album = $this->find_album( $atts, $user_info );
		$url = "https://photoslibrary.googleapis.com/v1/mediaItems:search";
		$photos = array( "mediaItems" => array() );
		
		do {
			
			if( isset( $result["nextPageToken"] ) && $result["nextPageToken"] != null ) {
				
				$query = array( "pageSize" => "100","albumId" => $album["id"], "pageToken" => $result["nextPageToken"] );
			} else {
				
				$query = array( "pageSize" => "100","albumId" => $album["id"] );
			}
			
			$result = self::curl( $user_info, $url, $query );
			$photos["mediaItems"] = array_merge( $photos["mediaItems"], $result["mediaItems"] );
		} while( isset( $result["nextPageToken"] ) && $result["nextPageToken"] != null );
		
		return( $photos );
	}
	
	/**
	 * Get plugin dir
	 *
	 * @since    1.0.1
	 */
	public static function get_plugin_dir() {
		
		return plugin_dir_path( __FILE__ );
	}
	
	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		
		return $this->plugin_slug;
	}
	
	/**
	 * Get plugin url
	 *
	 * @since    1.0.1
	 */
	public static function get_plugin_url() {
		
		return plugin_dir_url( __FILE__ );
	}
	
	public static function get_url() {
		
		return ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
	
	public static function get_url_data( $data ) {
		
		return json_decode( base64_decode( stripslashes( urldecode( $data ) ) ), true );
	}
	
	public function handle_page_request() {
		
		send_origin_headers();
	}
	
	public function load_album( $atts, &$user_info ) {
		
		$this->output_start();
		echo $this->load_photos( $atts, $user_info );
		return ob_get_clean();
	}
	
	public function load_albums( $atts, &$user_info ) {
		
		$this->output_start();
		//Check to see what the permalink structure is.
		if( isset( $_SERVER["REQUEST_URI"] ) ) {
				
			$uri = $_SERVER["REQUEST_URI"];
		}
		
		if ( get_option( 'permalink_structure' ) != '' ) {
			
			list( $back_link, $uri_tail ) = array_pad( explode( '?', $uri, 2 ), 2, null );
			$urlchar = '?';
			$splitchar = '\?';
		} else {
			
			list( $back_link, $uri_tail ) = array_pad( explode( '&', $uri, 2 ), 2, null );
			$urlchar = '&';
			$splitchar = $urlchar;
		}
		
		$access_token = $user_info['access_token'];
		$caption_length = get_option( self::OPTION_ALBUM_CAPTION_LENGTH );
		$filter_character = get_option( self::OPTION_FILTER_CHARACTER );
		$thumbnail_size = $atts["thumbnail_size"];
		$allow_download = get_option( self::OPTION_ALLOW_DOWNLOAD );
		$crop_thumbnails = get_option( self::OPTION_CROP_THUMBNAILS );
		$display_album_details = get_option( self::OPTION_DISPLAY_ALBUM_DETAILS );
		$display_public_only = get_option( self::OPTION_DISPLAY_PUBLIC_ONLY );
		$require_filter = get_option( self::OPTION_REQUIRE_FILTER );
		$per_page = $atts['per_page'];
		$token_expires = $user_info['token_expires'];
		$truncate_album_names = get_option( self::OPTION_TRUNCATE_ALBUM_NAMES );
		$username = $user_info['username'];
		$query = "=-w$thumbnail_size";
		$url = $this->get_url();
		$title = "";
		$hide_albums = explode( ",", $atts["hide_albums"] );
		/**
		 * First we check and see if a filter is required.  If a filter is
		 * required and not given then we stop here.
		 */
		if ( ! $require_filter == "false" ) {
			
			if ( ( ! isset( $filter ) ) || ( $filter == "" ) ) {
				
				echo esc_html( "<p>Error:  A filter is required however, none was specified." );
				die();
			}
		}
		
		if( $crop_thumbnails == "true" ) {
			
			$query .= "-c";
		}
		
		$albums = self::get_albums( $atts, $user_info );
		
		if( ! $atts["filter"] == "" ) {
			
			$gallery = "Gallery";
		} else {
			
			$gallery = ucfirst( strtolower( $atts["filter"] ) ) . " Gallery";
		}
		
		$this->output_start();
		
		$album_count = 0;
		$photo_count = 0;
		
		if( $atts["per_page"] > 0 ) {
				
			if( isset( $atts["data"]["i{$this->i}"][$user_info["username"]]["page"] ) ) {
				
				$page = $atts["data"]["i{$this->i}"][$user_info["username"]]["page"];
			} else {
				
				$page = 1;
			}
			
			if ( $page > 1 ) {
				
				$start_index = ( ($page - 1) * $atts["per_page"] ) + 1;
			} else {
				$start_index = 1;
			}
			
			$end_idex = $start_index + $atts["per_page"] - 1;
		}
		
		foreach( $albums["albums"] as $album ) {
			
			if( strpos( $album["title"], $filter_character ) ) {
				
				$album_filter = substr( $album["title"], strpos( $album["title"], $filter_character ) + 1 );
				$title = str_replace( $filter_character . $album_filter, "", $album["title"] );
			} else {
				
				$album_filter = "";
				$title = $album["title"];
			}
			
			if( $atts["filter"] != "" ) {
				
				if( $atts["filter"] != $album_filter ) {
					
					continue;
				}
			}
			
			if( in_array( $title, $hide_albums ) ) {
				
				continue;
			}
			
			$album_count++;
			$photo_count += $album["mediaItemsCount"];
				
			if( ( $atts["per_page"] > 0 ) && ( $album_count < $start_index || $album_count > $end_idex ) ) {
				
				continue;
			}
			
			if ( ( strlen( $title ) > $caption_length ) && ( $caption_length > 0 ) && ( $truncate_album_names == "true" ) ) {
				
				$title = substr( $title, 0, $caption_length ) . "...";
			}
			
			$data = $atts["data"];
			$data["i{$this->i}"][$user_info["username"]]["album"]["id"] = $album["title"];
			unset( $data["i{$this->i}"][$user_info["username"]]["page"] );
			$data = $this->create_url_data( $data );
			
			if( ! empty( $_GET ) || strpos( "album=", $url ) ) {
				
				$album_url = preg_replace( '/album=[^&]*/', "album={$data}", $url );
			} else {
				
				$album_url = "{$url}{$urlchar}album=$data";
			}
			
			?>
			<div class='telaalbums_albumcover'>
				<a class="overlay" href="<?php echo esc_url( $album_url );?>" style="width: <?php echo esc_html( $thumbnail_size );?>px;">
					<img class="telaalbums_img" alt="<?php echo esc_html( $album["title"] );?>" title="<?php echo esc_html( $album["title"] );?>" src="<?php echo esc_url( $album["coverPhotoBaseUrl"] . $query );?>" />
				</a>
				<div class="telaalbums_galdata">
					<a class="album_link" href="<?php echo esc_url( $album_url );?>"><?php echo esc_html( $title );?></a>
					<span class="telaalbums_albstat"><?php echo esc_html( $album["mediaItemsCount"] );?> Images</span>
				</div>
			</div>
			<?php
		}
		?>
		<div class="telaalbums_footer"></div>
		<div style='clear: both'></div>
		<?php
		$albums = ob_get_clean();
		
		$this->output_start();
		if ( $atts["per_page"] > 0 ) {
			
			?>
			<div id='pages'>
				Page: 
			<?php
			
			$page_count = ( $album_count / $atts["per_page"] ) + 1;
			
			for( $i=1; $i < $page_count; $i++ ) {
				
				$data = $atts["data"];
				$data["i{$this->i}"][$user_info["username"]]["page"] = $i;
				$data = $this->create_url_data( $data );
				
				if( ! empty( $_GET ) || strpos( "album=", $url ) ) {
					
					$page_url = preg_replace( '/album=[^&]*/', "album={$data}", $url );
				} else {
					
					$page_url = "{$url}{$urlchar}album=$data";
				}
				
				if ( $i == $page ) {
					
					?>
					<strong><?php echo esc_html( $i );?></strong>
					<?php
				} else {
					
					?>
					<a class='page_link' href='<?php echo esc_url( $page_url );?>'><?php echo esc_html( $i );?></a>
					<?php
				}
			}
			?>
			</div>
			<?php
		}
		$paginate = ob_get_clean();
		
		$this->output_start();
		?>
		<div style='clear: both'></div>
		<div id='telaheader'>
			<span class='lang_gallery'><?php echo esc_html( $gallery );?></span>
			<div>
				<span class='total_images'><?php echo esc_html( $photo_count );?> Photos in <?php echo esc_html( $album_count );?> Albums</span>
			</div>
		</div>
		<?php
		$header = ob_get_clean();
		
		echo $header;
		echo $albums;
		echo $paginate;
		return ob_get_clean();
	}
	
	public function load_photos( $atts, &$user_info ) {
		
		$this->output_start();
		$allow_download = get_option( self::OPTION_ALLOW_DOWNLOAD );
		$hide_videos = get_option( self::OPTION_HIDE_VIDEOS );
		$caption_length = get_option( self::OPTION_PHOTOS_CAPTION_LENGTH );
		$crop_thumbnails = get_option( self::OPTION_CROP_THUMBNAILS );
		$require_filter = get_option( self::OPTION_REQUIRE_FILTER );
		$truncate_photo_names = get_option( self::OPTION_TRUNCATE_PHOTO_NAMES );
		$thumbnail_query = "=-w{$atts["thumbnail_size"]}";
		$photo_query = "=-w{$atts["photos_size"]}";
		$photos = $this->get_photos( $atts, $user_info );
		$photo_count = 0;
		$url = $this->get_url();
		$albums = $this->get_albums( $atts, $user_info )["albums"];
		$filter_character = get_option( self::OPTION_FILTER_CHARACTER );
		
		if ( get_option( 'permalink_structure' ) != '' ) {
			
			list( $back_link, $uri_tail ) = array_pad( explode( '?', $url, 2 ), 2, null );
			$urlchar = '?';
			$splitchar = '\?';
		} else {
			
			list( $back_link, $uri_tail ) = array_pad( explode( '&', $url, 2 ), 2, null );
			$urlchar = '&';
			$splitchar = $urlchar;
		}
		
		foreach( $albums as $album ) {
			
			if( $album["title"] == $atts["album"] || $album["id"] == $atts["album"] ) {
				
				if( strpos( $album["title"], $filter_character ) ) {
					
					$album_filter = substr( $album["title"], strpos( $album["title"], $filter_character ) + 1 );
					$album_title = str_replace( $filter_character . $album_filter, "", $album["title"] );
				} else {
					
					$album_title = $album["title"];
				}
				
				break;
			}
		}
		
		if( $crop_thumbnails == "true" ) {
			
			$thumbnail_query .= "-c";
		}
		
		if( $atts["per_page"] > 0 ) {
				
			if( isset( $atts["data"]["i{$this->i}"][$user_info["username"]]["album"]["page"] ) ) {
				
				$page = $atts["data"]["i{$this->i}"][$user_info["username"]]["album"]["page"];
			} else {
				
				$page = 1;
			}
			
			if ( $page > 1 ) {
				
				$start_index = ( ($page - 1) * $atts["per_page"] ) + 1;
			} else {
				$start_index = 1;
			}
			
			$end_idex = $start_index + $atts["per_page"] - 1;
		}
		
		$this->output_start();
		foreach( $photos["mediaItems"] as $photo ) {
			
			$photo_count++;
				
			if( ( $atts["per_page"] > 0 ) && ( $photo_count < $start_index || $photo_count > $end_idex ) ) {
				
				continue;
			}
			
			if( ! isset( $photo["description"] ) ) {
				
				$photo["description"] = "{$album_title} - " . preg_replace( '/\\.[^.\\s]{3,4}$/', '', $photo["filename"] );
			}
			
			if ( ( ( strlen( $photo["description"] ) > $caption_length ) ) && ( $caption_length > 0 ) && ( $truncate_photo_names == "true" ) ) {
				
				$title = substr( $photo["description"], 0, $caption_length ) . "...";
			} else {
				
				$title = $photo["description"];
			}
			
			if( strpos( $photo["mimeType"], "video" ) !== false ) {
				
				$photo_url = $photo["baseUrl"] . "=dv";
			} else {
				
				$photo_url = $photo["baseUrl"] . $photo_query;
			}
			
			$thumbnail_source = $photo["baseUrl"] . $thumbnail_query;
			?>
			<div class='telaalbums_thumbnail'>
				<a class="overlay" href="<?php echo esc_url( $photo_url );?>" style="width: <?php echo esc_html( $atts["thumbnail_size"] );?>px;">
					<img class="telaalbums_img" alt="<?php echo esc_html( $title );?>" title="<?php echo esc_html( $title );?>" src="<?php echo esc_url( $thumbnail_source );?>" />
				</a>
				<div class="telaalbums_data">
					<a alt="<?php echo esc_html( $title );?>" title="<?php echo esc_html( $title );?>" href="<?php echo esc_url( $photo_url );?>"><?php echo esc_html( $title );?></a>
					<?php
					if( $allow_download == "true" ) {
						
						?>
						<span style="float: right; padding-top: 3px;">
							<a rel="nobox" title="Save <?php echo esc_html( $photo["filename"] );?> " href="<?php echo esc_url( $photo["baseUrl"] . "=-h" . $photo["mediaMetadata"]["height"] );?>" target="_blank">
								<img border="0" style="padding-left: 5px;" src="https://telaaedifex.com/wp-content/plugins/telaalbums/public/assets/images/disk_bw.png">
							</a>
						</span>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
		<div class="telaalbums_footer"></div>
		<div style='clear: both'></div>
		<?php
		$photos = ob_get_clean();
		
		$this->output_start();
		if ( $atts["per_page"] > 0 ) {
			
			?>
			<div id='pages'>
				Page: 
			<?php
			
			$page_count = ( $photo_count / $atts["per_page"] ) + 1;
			
			for( $i=1; $i < $page_count; $i++ ) {
				
				$data = $atts["data"];
				$data["i{$this->i}"][$user_info["username"]]["album"]["page"] = $i;
				$data = $this->create_url_data( $data );
				
				if( ! empty( $_GET ) || strpos( "album=", $url ) ) {
					
					$page_url = preg_replace( '/album=[^&]*/', "album={$data}", $url );
				} else {
					
					$page_url = "{$url}{$urlchar}album=$data";
				}
				
				if ( $i == $page ) {
					
					?>
					<strong><?php echo esc_html( $i );?></strong>
					<?php
				} else {
					
					?>
					<a class='page_link' href='<?php echo esc_url( $page_url );?>'><?php echo esc_html( $i );?></a>
					<?php
				}
			}
			?>
			</div>
			<?php
		}
		$paginate = ob_get_clean();
		
		$this->output_start();
		?>
		<div style='clear: both'></div>
		<div id='telaheader'>
			<span class='lang_gallery'><?php echo esc_html( $album_title );?></span>
			<div>
				<span class='total_images'><?php echo esc_html( $photo_count );?> Photos</span>
				<?php
	
				if( isset( $_GET["album"] ) && isset( $atts["data"]["i{$this->i}"][$user_info["username"]]["album"]["id"] ) && ( $atts["data"]["i{$this->i}"][$user_info["username"]]["album"]["id"] == $atts["album"] ) ) {
					
					$data = $atts["data"];
					unset( $data["i{$this->i}"][$user_info["username"]] );
					$url_data = $this->create_url_data( $data );
					
					if( ( ! empty( $_GET ) || strpos( "album=", $url ) ) && ! empty( $data["i{$this->i}"] ) ) {
						
						$return_url = preg_replace( '/album=[^&]*/', "album={$url_data}", $url );
					} elseif( ( ! empty( $_GET ) || strpos( "album=", $url ) ) && empty( $data["i{$this->i}"] ) ) {
						
						if( $urlchar == "?" ) {
							
							$string = "/\\{$urlchar}album=[^&]*/";
						} else {
							
							$string = "/{$urlchar}album=[^&]*/";
						}
						
						$return_url = preg_replace( $string, "", $url );
					} else {
						
						$return_url = "{$url}{$urlchar}album=$url_data";
					}
					
					?>
					<span><a class='back_to_list' href="<?php echo esc_url( $return_url );?>">...Back to Albums</a></span>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		$header = ob_get_clean();
		
		echo $header;
		echo $photos;
		echo $paginate;
		return ob_get_clean();
	}
	
	public function load_slideshow( $atts, &$user_info ) {
		
		$this->output_start();
		$allow_download = get_option( self::OPTION_ALLOW_DOWNLOAD );
		$hide_videos = get_option( self::OPTION_HIDE_VIDEOS );
		$caption_length = get_option( self::OPTION_PHOTOS_CAPTION_LENGTH );
		$crop_thumbnails = get_option( self::OPTION_CROP_THUMBNAILS );
		$require_filter = get_option( self::OPTION_REQUIRE_FILTER );
		$truncate_photo_names = get_option( self::OPTION_TRUNCATE_PHOTO_NAMES );
		$photo_query = "=-w{$atts["photos_size"]}";
		$photos = $this->get_photos( $atts, $user_info );
		$photo_count = 0;
		$url = $this->get_url();
		$albums = $this->get_albums( $atts, $user_info )["albums"];
		$slideshow_id = "telaalbums_slideshow_{$this->i}";
		
		foreach( $albums as $album ) {
			
			if( $album["title"] == $atts["album"] || $album["id"] == $atts["album"] ) {
				
				break;
			}
		}
		
		$this->output_start();
		?>
		<div id='<?php echo esc_html( $slideshow_id );?>' style='position:relative;margin:0 auto;top:0px;left:0px;width:600px;height:500px;overflow:hidden;visibility:hidden;'>
			<div data-u='slides' style='cursor:default;position:relative;top:0px;left:0px;width:600px;height:500px;overflow:hidden;'>
			<?php
			foreach( $photos["mediaItems"] as $photo ) {
				
				$photo_url = $photo["baseUrl"] . $photo_query;
				?>
				<div style='background-color:#000000;'>
					<img data-u='image' src='<?php echo esc_url( $photo_url );?>' width='auto' height='<?php echo esc_html( $atts["photos_size"] );?>' />
				</div>
				<?php
			}
			?>
			</div>
			<!-- Bullet Navigator -->
			<div data-u='navigator' class='jssorb13' style='bottom:16px;right:16px;' data-autocenter='1'>
				<!-- bullet navigator item prototype -->
				<div data-u='prototype' style='width:21px;height:21px;'></div>
			</div>
			<!-- Arrow Navigator -->
			<span data-u='arrowleft' class='jssora06l' style='top:0px;left:8px;width:45px;height:45px;' data-autocenter='2'></span>
			<span data-u='arrowright' class='jssora06r' style='top:0px;right:8px;width:45px;height:45px;' data-autocenter='2'></span>
		</div>
		<script defer="defer" type='text/javascript'>
			window.onload = function() {
				telaalbums.slideshow.init( '<?php echo esc_html( $slideshow_id );?>' );
			};
		</script>
		<?php
		$slideshow = ob_get_clean();
		
		$this->output_start();
		?>
		<div style='clear: both'></div>
		<div id='telaheader'>
			<span class='lang_gallery'><?php echo esc_html( $album["title"] );?></span>
		</div>
		<?php
		$header = ob_get_clean();
		
		echo $header;
		echo $slideshow;
		return ob_get_clean();
	}
	
	public function output_start() {
		
		if( function_exists( 'ob_gzhandler' ) && ini_get( 'zlib.output_compression' ) ) {
			
			ob_start( "ob_gzhandler" );
		} else {
			
			ob_start();
		}
	}
	
	public function refresh_token( &$user_info, $try=0 ) {
		
		$expires = $user_info['token_expires'] - date( "U" );
		$array = array();
		
		if ( date( "U" ) > $user_info['token_expires'] || $expires < '1000' ) {
			
			/* Refresh token and get the response variable back.  If the access token
			 * has been changed more specifically the date, then set the variables below
			 * to stop the issue with empty page until reload.
			 */
			
			//Refresh the oauth2 token if it has expired
			$token_expires = $user_info['token_expires'];
			$client_id = $user_info['client_id'];
			$client_secret = $user_info['client_secret'];
			$refresh_token = $user_info['refresh_token'];
			$username = $user_info['username'];
			$post_body =  'access_type=offline'
			.'&client_id='.urlencode( $client_id )
			.'&client_secret='.urlencode( $client_secret )
			.'&refresh_token='.urlencode( $refresh_token )
			.'&grant_type=refresh_token';
			$url = "https://www.googleapis.com/oauth2/v3/token";
			
			$response = $this->curl( $array, $url, $post_body );
			
			if( isset( $response['access_token'] ) ) {
				
				$token_expires = date( "U" ) + $response['expires_in'];
				//Unset variables
				unset( $access_token );
				
				//Re-Save the variables
				$access_token = $response['access_token'];
				
				//Save to database
				global $wpdb;
				$table_name = $wpdb->prefix . self::OPTION_USERS_TABLE;
				$sql = $wpdb->prepare(
					"UPDATE $table_name
					SET access_token = %s, token_expires = %s
					WHERE username = %s;",
					$access_token,
					$token_expires,
					$username
				);
				$wpdb->query( $sql );
				$user_info['access_token'] = $access_token;
				$user_info['token_expires'] = $token_expires;
			} elseif( ! isset( $response['access_token'] ) && $try < 5 ) {
				
				$this->refresh_token( $user_info, ( ++$try ) );
			} else {
				
				?>
				<p>
					Tela Albums could not refresh the access token.  The following response was recieved:<br>
					<?php echo var_export( $response );?>
				</p>
				<?php
			}
		}
	}
	
	/**
	 * Handle the shortcode.
	 *
	 * @return a buffered html output.
	 */
	public function shortcode( $atts = [], $content = null, $tag = '' ) {
		
		if( isset( $_GET["action"] ) && $_GET["action"] == "edit" ) {
			
			return;
		}
		
		$this->output_start();

		if( ! is_array( $atts ) ) {
			$atts = array();
		}
		
		global $wpdb;
		
		$this->enqueue_scripts();
		$this->enqueue_styles();
		
		//Normalize attribute keys into all lowercase characters.
		$atts = array_change_key_case( $atts, CASE_LOWER );
		$table_name = $wpdb->prefix . self::OPTION_USERS_TABLE;
		$total_users = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		$user_info = array();
		$atts["data"] = array();
		$ouput = "";
		
		if( isset( $atts["username"] ) ) {
			
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE `username`=%s", array( $atts["username"] ) );
			$result = $wpdb->get_results( $query, ARRAY_A );
		} else {
			
			$result = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
		}
		
		if ( $total_users == '0' ) {
			
			/**
			 * if the total amount of users is zero that means that the plugin is
			 * not setup.  Therefore we should warn the user by printing a message 
			 * onto the page.
			 */
			
			echo esc_html( 'Error, you have not setup the plugin.  Please add a user to get started.' );
			return ob_get_clean();
		} elseif( $total_users > 1 ) {
			
			/**
			 * If there are multiple users specified, we need to ask them to 
			 * specify which user they are trying to access with the shortcode
			 * attributes below.
			 */
			
			if( isset( $atts["album"] ) && ! isset( $atts["username"] ) ) {
				
				echo esc_html( "Error, when using more than one user and attempting to use the album attribute please specify the user as username=\"username\" in the shortcode." );
				return ob_get_clean();
			}
			
			if( isset( $atts["slideshow"] ) && ! isset( $atts["username"] ) ) {
				
				echo esc_html( "Error, when using more than one user and attempting to use the slideshow attribute please specify the user as username=\"username\" in the shortcode." );
				return ob_get_clean();
			}
		}
		
		$default_atts = $atts;
		
		foreach( $result as $id => $row ) {
			
			$user_info["username"] = $row['username'];
			
			if( isset( $atts["album"] ) ) {
			} elseif( isset( $_GET["album"] ) ) {
				
				$atts["album"] = "";
				$atts["data"] = $this->get_url_data( $_GET["album"] );
				
				if( ( json_last_error() == JSON_ERROR_NONE ) && ( isset( $atts["data"]["i{$this->i}"][$row["username"]]["album"] ) && $atts["data"]["i{$this->i}"][$row["username"]]["album"] != null) ) {
					
					$atts["album"] = $atts["data"]["i{$this->i}"][$row["username"]]["album"]["id"];
				}
			} else {
				
				$atts["album"] = "";
			}
			
			if( ! isset( $atts["data"]["i{$this->i}"][$row["username"]] ) || $atts["data"]["i{$this->i}"][$row["username"]] == null ) {
					
				$atts["data"]["i{$this->i}"][$row["username"]] = array();
			}
			
			if( ! isset( $atts["filter"] ) ) {
				
				$atts["filter"] = "";
			}
			
			if( ! isset( $atts["hide_albums"] ) ) {
				
				$atts["hide_albums"] = "";
			}
			
			if ( ! isset( $atts['per_page'] ) ) {
				
				$atts['per_page'] = get_option( self::OPTION_ALBUM_PER_PAGE );
			}
			
			if( ! isset( $atts["slideshow"] ) ) {
				
				$atts["slideshow"] = "";
			}
			
			if ( $atts["album"] == "" && $atts["slideshow"] == "" ) {
				
				//If no album or slideshow is set, then load the main functionality of the plugin.
				if( ! isset( $atts["thumbnail_size"] ) ) {
					
					$atts["thumbnail_size"] = get_option( self::OPTION_ALBUM_THUMBNAIL_SIZE );
				}
				echo $this->load_albums( $atts, $row );
			} elseif ( ! $atts["album"] == "" ) {
				
				//If an album or filter is set, then either load that album or load the filter.
				if( ! isset( $atts["thumbnail_size"] ) ) {
					
					$atts["thumbnail_size"] = get_option( self::OPTION_PHOTOS_THUMBNAIL_SIZE );
				}
				
				if( ! isset( $atts["photos_size"] ) ) {
					
					$atts["photos_size"] = get_option( self::OPTION_PHOTOS_SIZE );
				}
				
				echo $this->load_album( $atts, $row );
			} elseif ( ! $atts["slideshow"] == "" ) {
				
				//If the slideshow shortcode is set then load the slideshow.
				if( ! isset( $atts["photos_size"] ) ) {
					
					$atts["photos_size"] = get_option( self::OPTION_PHOTOS_SIZE );
				}
				
				$atts["album"] = $atts["slideshow"];
				
				wp_enqueue_style( $this->plugin_slug . '-plugin-slideshow-style', plugins_url( 'assets/css/slideshow/slideshow.css', __FILE__ ), array(), self::VERSION );
				wp_enqueue_script( $this->plugin_slug . '-plugin-slideshow-jssor', plugins_url( 'assets/js/slideshow/jssor.slider-23.1.5.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
				wp_enqueue_script( $this->plugin_slug . '-plugin-slideshow-script', plugins_url( 'assets/js/slideshow/slideshow.js', __FILE__ ), array( 'jquery' ), self::VERSION );
				echo $this->load_slideshow( $atts, $row );
			}
			
			//Reset atts for next iteration.
			$atts = $default_atts;
		}
		
		$this->i++;
		return ob_get_clean();
	}
	
	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		
		if ( ! current_user_can( 'activate_plugins' ) ) {
			
			return;
		}
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $wpdb;		
		$table_name = $wpdb->prefix . self::OPTION_USERS_TABLE;
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				username text NOT NULL,
				client_id varchar(255) NOT NULL,
				client_secret varchar(255) NOT NULL,
				access_token varchar(255) NOT NULL,
				token_expires varchar(25) NOT NULL,
				refresh_token varchar(255) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";
		dbDelta( $sql );
		
		$username = get_option("telaalbums_google_username");
		$client_id = get_option("telaalbums_client_id");
		$client_secret = get_option("telaalbums_client_secret");
		$access_token = get_option("telaalbums_oauth_token");
		$refresh_token = get_option("telaalbums_refresh_token");
		$token_expires = 0;
		
		if ( isset( $username ) && isset( $client_id ) && isset( $client_secret ) && isset( $access_token ) && isset( $refresh_token ) && isset( $token_expires ) ) {
			
			delete_option("telaalbums_access_token");
			delete_option("telaalbums_client_secret");
			delete_option("telaalbums_client_id");
			delete_option("telaalbums_google_username");
			delete_option("telaalbums_oauth_token");
			delete_option("telaalbums_refresh_token");
			delete_option("telaalbums_token_expires");
			
		}
		
		$options = self::OPTIONS;
		
		foreach( $options as $option => $value ) {
			
			if ( FALSE === get_option( $option ) ) {
				
				add_option( $option, $value );
			}
		}
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		
		if ( ! current_user_can( 'activate_plugins' ) ) {
			
			return;
		}
	}

	/**
	 * Fired for each blog when the plugin is uninstalled.
	 *
	 * @since    1.0.0
	 */
	private static function single_uninstall() {
		
		if ( ! current_user_can( 'activate_plugins' ) ) {
			
			return;
		}
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $wpdb;		
		$table_name = $wpdb->prefix . self::OPTION_USERS_TABLE;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "DROP TABLE $table_name;";
		dbDelta( $sql );
		
		$options = array(
			
			//Delete variables not stored as constants
			"telaalbums_setup",
			"telaalbums_access_token",
			"telaalbums_add_widget",
			"telaalbums_albpage_desc",
			"telaalbums_album_details",
			"telaalbums_album_thumbsize",
			"telaalbums_albums_per_page",
			"telaalbums_allow_slideshow",
			"telaalbums_cache_thumbs",
			"telaalbums_caption_length",
			"telaalbums_check_for_updates",
			"telaalbums_client_id",
			"telaalbums_client_secret",
			"telaalbums_comments_widget_title",
			"telaalbums_crop_thumbs",
			"telaalbums_css_album_grayscale",
			"telaalbums_css_album_shadow",
			"telaalbums_css_album_turn",
			"telaalbums_css_album_rotate",
			"telaalbums_css_photo_grayscale",
			"telaalbums_css_photo_shadow",
			"telaalbums_css_photo_turn",
			"telaalbums_css_photo_rotate",
			
			"telaalbums_date_format",
			"telaalbums_description_length",
			"telaalbums_developer_mode",
			"telaalbums_oauth_token",
			"telaalbums_hide_video",
			"telaalbums_image_size",
			"telaalbums_images_on_front",
			"telaalbums_images_per_page",
			"telaalbums_jq_pagination",
			"telaalbums_language",
			"telaalbums_main_photo",
			"telaalbums_main_photo_page",
			"telaalbums_oauth_token",
			"telaalbums_permit_download",
			"telaalbums_photo_widget_title",
			"telaalbums_google_username",
			"telaalbums_public_only",
			"telaalbums_public_albums_only",
			"telaalbums_refresh_token",
			"telaalbums_require_filter",
			"telaalbums_show_album_animations",
			"telaalbums_show_button",
			"telaalbums_show_caption",
			"telaalbums_show_comments",
			"telaalbums_show_dropbox",
			"telaalbums_show_footer",
			"telaalbums_show_n_albums",
			"telaalbums_thumbnail_size",
			"telaalbums_token_expires",
			"telaalbums_truncate_names",
			"telaalbums_updates",
			"telaalbums_version",
			"telaalbums_which_jq",
			"telaalbums_widget_album_name",
			"telaalbums_widget_comments",
			"telaalbums_widget_num_random_photos",
			"telaalbums_widget_size",
			"telaalbums_widget",
		);
		
		foreach( $options as $option ) {
			
			delete_option( $option );
		}
		
		foreach( self::OPTIONS as $option => $value ) {
			
			delete_option( $option );
		}
	}
	
	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function uninstall( $network_wide ) {
		
		if ( ! current_user_can( 'activate_plugins' ) ) {
			
			return;
		}
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			
			if ( $network_wide ) {
				
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
				
				foreach ( $blog_ids as $blog_id ) {
					
					switch_to_blog( $blog_id );
					self::single_uninstall();
				}
				
				restore_current_blog();
			} else {
				
				self::single_uninstall();
			}
		} else {
			
			self::single_uninstall();
		}
	}
}