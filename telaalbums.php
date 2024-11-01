<?php
/*
Plugin Name: 	Tela Albums: Google Photo Albums for Wordpress
Plugin URI: 	https://telaaedifex.com/albums/
Description:	A Google Photos plugin for Wordpress, Telaaedifex's Albums, allows you to display public and private (unlisted) albums on your site in your language!
Author: 	Isaac Brown
Version: 1.5.2.8
Author URI: 	https://telaaedifex.com
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ( plugin_dir_path( __FILE__ ) . 'public/public.php' );
register_activation_hook( __FILE__, array( 'telaalbums', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'telaalbums', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'telaalbums', 'uninstall' ) );
add_action( 'plugins_loaded', array( 'telaalbums', 'get_instance' ) );

if ( is_admin() ) {
	
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );
	add_action( 'plugins_loaded', array( 'telaalbums_admin', 'get_instance' ) );
}