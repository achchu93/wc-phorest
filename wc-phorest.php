<?php
/**
 * Plugin Name: WooCommerce Phorest
 * Plugin URI: https://github.com/achchu93/wc-phorest
 * Description: An extension to connect Woocommerce with Phorest
 * Author: Spaceship
 * Author URI: https://spaceship.ie/
 * Text Domain: wc-phorest
 * Domain Path: /i18n/languages/
 * Version: 0.0.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.2
 *
 */

namespace Phorest;

use Phorest\Admin\Admin;

defined( 'ABSPATH' ) || exit;

final class WC_Phorest {

	public static $instance = null;

	public function __construct(){

		// check if woocommerce plugin is active
		if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
			add_action('admin_notices', [ $this, 'wc_not_active_message' ] );
			return false;
      	}

		$this->define_constants();
		$this->register_autoloader();
		$this->init();
	}

	public static function instance(){

		if( is_null( self::$instance ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function define_constants(){
		define( 'WCPH_PLUGIN_FILE', __FILE__ );
	}

	private function register_autoloader(){
		spl_autoload_register( [ $this, 'autoloader' ] );
	}

	public function autoloader( $class ){

		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}

		$file = $this->get_plugin_path() . 'includes/' . preg_replace( '/^' . __NAMESPACE__ . '\\\/', '', $class ) . '.php';
    	require_once $file;
	}

	public function get_plugin_path(){
		return trailingslashit( dirname( WCPH_PLUGIN_FILE ) );
	}

	public function init(){
		new Admin();
	}

	public function wc_not_active_message(){
		echo sprintf(
			'<div class="error woocommerce-message wc-connect"><p>%1$s %2$s</p></div>',
			'<strong>WooCommerce Phorest</strong> requires WooCommerce to be installed and activated first.',
			'Please install <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce' ).'">WooCommerce</a> first.'
		);
	}

}
WC_Phorest::instance();