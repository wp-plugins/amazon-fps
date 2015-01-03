<?php
/**
 * Plugin Name:       Amazon FPS
 * Description:       Accept payments using Amazon Flexible Payments Service.
 * Version:           1.0
 * Author:            wpecommerce
 * Author URI:        https://wp-ecommerce.net/
 * Plugin URI:        https://wp-ecommerce.net/wordpress-amazon-fps-plugin
 * Text Domain:       afpsdg_locale
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-afpsdg.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-shortcode-afpsdg.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-amazonfpsdg.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/class-order.php' );
require_once(plugin_dir_path( __FILE__ ) . 'includes/Amazon/CBUI/CBUISingleUsePipeline.php');	
require_once(plugin_dir_path( __FILE__ ) . 'includes/Amazon/FPS/Client.php');	
require_once(plugin_dir_path( __FILE__ ) . 'includes/Amazon/FPS/Model/PayRequest.php');	
require_once(plugin_dir_path( __FILE__ ) . 'includes/Amazon/FPS/Model/Amount.php');	
require_once(plugin_dir_path( __FILE__ ) . 'includes/Amazon/IpnReturnUrlValidation/SignatureUtilsForOutbound.php');	
  
function afps_require_once($path) {
	require_once(plugin_dir_path( __FILE__ ) . 'includes/'.$path);
}

function afps_include_once($path) {
	include_once(plugin_dir_path( __FILE__ ) . 'includes/'.$path);
}
/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */

register_activation_hook( __FILE__, array( 'AFPSDG', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AFPSDG', 'deactivate' ) );

/*
 */
add_action( 'plugins_loaded', array( 'AFPSDG', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AFPSDGShortcode', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-afpsdg-admin.php' );
	add_action( 'plugins_loaded', array( 'AFPSDG_Admin', 'get_instance' ) );

}
