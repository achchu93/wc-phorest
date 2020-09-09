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

defined( 'ABSPATH' ) || exit;


final class WC_Phorest {

	public static $instance = null;

	public function __construct() {

		// check if woocommerce plugin is active
		if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			add_action('admin_notices', [ $this, 'wc_not_active_message' ] );
			return false;
      	}

		$this->define_constants();
		$this->includes();
		$this->init();
	}

	public static function instance() {

		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function compatibility_check(){

		throw new Error('error');

	}

	public function includes() {

	}

	public function define_constants(){

	}

	public function init(){

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