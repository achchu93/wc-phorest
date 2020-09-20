<?php

namespace Phorest\Admin;

use Phorest\Admin\Settings;
use Phorest\Admin\ProductList;

defined( 'ABSPATH' ) || exit;

class Admin {

	public function __construct(){

		$this->init_hooks();
	}

	public function init_hooks(){
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_head', [ $this, 'remove_sub_phorest' ] );
		add_action( 'admin_menu', [ $this, 'settings_menu' ] );
		add_action( 'admin_menu', [ $this, 'import_menu' ] );
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
	}

	public function remove_sub_phorest(){

		global $submenu;

		if( isset( $submenu['wc-phorest'] ) ){
			unset( $submenu['wc-phorest'][0] );
		}
	}

	public function import_menu(){

		$import = new Import();
		$import_page = add_submenu_page(
			'wc-phorest',
			__( 'Import Products', 'wc-phorest' ),
			__( 'Import', 'wc-phorest' ),
			'manage_woocommerce',
			'wc-phorest-import',
			[ $import, 'import_page' ]
		);

		add_action( "load-{$import_page}", [ $this, 'import_page_init' ] );
	}

	public function import_page_init(){

		global $ph_product_list;

		$ph_product_list = new ProductList();
	}

	public function settings_menu(){

		$settings = new Settings();
		add_submenu_page(
			'wc-phorest',
			__( 'Phorest settings', 'wc-phorest' ),
			__( 'Settings', 'wc-phorest' ),
			'manage_woocommerce',
			'wc-phorest-settings',
			[ $settings, 'settings_page' ]
		);
	}
}