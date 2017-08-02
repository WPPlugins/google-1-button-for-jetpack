<?php
/*
 * Plugin Name: Google +1 for Jetpack
 * Plugin URI: 
 * Description: Add a Google +1 button to the Jetpack Sharing module
 * Author: Alfonso Bernal
 * Version: 1.0
 * Author URI: http://tusuperagente.com
 * License: GPL2+
 */

class GooglePlusOneJP_Button {
	private static $instance;
	
	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new GooglePlusOneJP_Button;
 
		return self::$instance;
	}

	private function __construct() {
		// Check if Jetpack and the sharing module is active
		//if ( class_exists( 'Jetpack' ) && Jetpack::init()->is_module_active( 'sharedaddy' ) )
			add_action( 'plugins_loaded', array( $this, 'setup' ) );
	}
	
	public function setup() {
        add_filter( 'sharing_services', array( 'Share_GooglePlusOneJP', 'inject_service' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
    }
	
	// Add Javascript in the footer
	public function enqueue_script() {
		wp_enqueue_script( 'googleplusone-js', ( is_ssl() ? 'https:' : 'http:' ) . '//apis.google.com/js/platform.js', false, null, true );
	}
}

// Include Jetpack's sharing class, Sharing_Source
$share_plugin = wp_get_active_and_valid_plugins();
if ( is_multisite() ) {
	$share_plugin = array_unique( array_merge($share_plugin, wp_get_active_network_plugins() ) );
}
$share_plugin = preg_grep( '/\/jetpack\.php$/i', $share_plugin );

if ( empty( $share_plugin ) ) {
	add_action( 'admin_notices', 'mwpjp_install_jetpack' );
	// Prompt to install Jetpack
	function mwpjp_install_jetpack() {
		echo '<div class="error"><p>';
		printf(__( 'To use the Google +1 button for Jetpack, you\'ll need to install and activate <a href="%1$s">Jetpack</a> first.'), 'plugin-install.php?tab=search&s=jetpack&plugin-search-input=Search+Plugins','mwpjp' );
		echo '</p></div>';
	}
} else {

if ( ! class_exists( 'Sharing_Source' ) )
	include_once( preg_replace( '/jetpack\.php$/i', 'modules/sharedaddy/sharing-sources.php', reset( $share_plugin ) ) );

// Build button
class Share_GooglePlusOneJP extends Sharing_Source {
	var $shortname = 'googleplusonejp';	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
	}

	public function get_name() {
		return __( 'Google +1', 'googleplusonejp' );
	}
	
	public function get_display( $post ) {	
		return '<div class="g-plusone" data-size="medium"></div>';
	}

	// Add the googleplusonejp Button to the list of services in Sharedaddy
	public function inject_service ( array $services ) {
		if ( ! array_key_exists( 'googleplusonejp', $services ) ) {
			$services['googleplusonejp'] = 'Share_GooglePlusOneJP';
		}
		return $services;
	}
}
}

// And boom.
GooglePlusOneJP_Button::get_instance();
