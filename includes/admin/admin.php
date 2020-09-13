<?php

namespace Phorest\Admin;

defined( 'ABSPATH' ) || exit;

class Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	public function admin_menu(){

		add_menu_page(
			__( 'Phorest', 'wc-phorest' ),
			__( 'Phorest', 'wc-phorest' ),
			'manage_woocommerce',
			'wc-phorest',
			null,
			null,
			60
		);

		add_submenu_page(
			'wc-phorest',
			__( 'Phorest settings', 'wc-phorest' ),
			__( 'Settings', 'wc-phorest' ),
			'manage_woocommerce',
			'wc-phorest-settings',
			array( $this, 'settings_page' )
		);
	}

	public function settings_page(){

	}
}