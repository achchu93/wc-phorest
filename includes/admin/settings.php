<?php

namespace Phorest\Admin;

use Phorest\Api\Settings as Settings_Api;
use Phorest\Api\Base as API;

defined( 'ABSPATH' ) || exit;

class Settings {

	public $settings_api;

	private $api;

	public function __construct(){

		$this->init();
		$this->init_hooks();
	}

	private function init(){

		$this->api = new API();
		$this->settings_api = new Settings_Api;
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		$this->settings_api->admin_init();
	}

	private function init_hooks(){

		add_action( 'update_option_phorest_auth', [ $this, 'auth_validation' ], 10, 3 );
	}

	private function get_settings_sections(){

		return apply_filters( 'wc_phorest_settings_sections', [
			[
				'id'    => 'phorest_auth',
				'title' => __( 'Authentication', 'wc-phorest' ),
				'desc'  => __( 'Authentication details of Phorest', 'wc-phorest' )
			],
			[
				'id'    => 'phorest_import',
				'title' => __( 'Import', 'wc-phorest' ),
				'desc'  => __( 'Phorest product import settings', 'wc-phorest' )
			]
		]);
	}

	private function get_settings_fields(){

		return apply_filters( 'wc_phorest_settings_fields', [
			'phorest_auth' => [
				[
					'name'              => 'username',
                    'label'             => __( 'Username', 'wc-phorest' ),
                    'desc'              => __( 'please provide your phorest username here', 'wc-phorest' ),
                    'placeholder'       => __( 'Username', 'wc-phorest' ),
					'type'              => 'text',
					'attributes'  		=> [
						'readonly' => 'true',
						'onfocus'  => 'this.removeAttribute(\'readonly\');'
					]
				],
				[
					'name'              => 'password',
                    'label'             => __( 'Password', 'wc-phorest' ),
                    'desc'              => __( 'please provide your phorest passowrd here', 'wc-phorest' ),
                    'placeholder'       => __( 'Password', 'wc-phorest' ),
                    'type'              => 'password'
				],
				[
					'name'              => 'business_id',
                    'label'             => __( 'Business ID', 'wc-phorest' ),
                    'desc'              => __( 'please provide your phorest budsiness ID', 'wc-phorest' ),
                    'placeholder'       => __( 'Business ID', 'wc-phorest' ),
                    'type'              => 'text'
				]
			],
			'phorest_import' => [
				[
					'name'              => 'branch_id',
                    'label'             => __( 'Default Branch', 'wc-phorest' ),
                    'desc'              => __( 'Branch to import product from', 'wc-phorest' ),
                    'placeholder'       => __( 'Branch', 'wc-phorest' ),
					'type'              => 'select',
					'options' 			=> $this->get_branches()
				],
				[
					'name'              => 'wc_product_field',
                    'label'             => __( 'WC Product Field', 'wc-phorest' ),
                    'desc'              => __( 'Field to map with phorest product. Default to sku', 'wc-phorest' ),
                    'placeholder'       => __( 'Eg: sku', 'wc-phorest' ),
                    'type'              => 'text'
				],
				[
					'name'              => 'ph_product_field',
                    'label'             => __( 'Phorest Product Field', 'wc-phorest' ),
                    'desc'              => __( 'Field to map with woocommerce product. Default to barcode', 'wc-phorest' ),
                    'placeholder'       => __( 'Eg: barcode', 'wc-phorest' ),
                    'type'              => 'text'
				],
				[
					'name'              => 'import_outofstock',
                    'label'             => __( 'Out of stock products', 'wc-phorest' ),
                    'desc'              => __( 'Import out of stock product', 'wc-phorest' ),
					'type'              => 'checkbox',
					'default' 			=> 'off'
				],
				[
					'name'              => 'replace_existing',
                    'label'             => __( 'Existing products', 'wc-phorest' ),
                    'desc'              => __( 'Replace existing products', 'wc-phorest' ),
					'type'              => 'checkbox',
					'default' 			=> 'off'
				]
			]
		]);
	}

	public function settings_page(){
		settings_errors();

        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
	}

	public function auth_validation( $old_value, $value, $option ){

		if( empty( $value['username'] ) || empty( $value['password'] ) || empty( $value['business_id'] ) ) {
			add_settings_error( 'phorest_auth', 422, __( 'Invalid authentication data', 'wc-phorest' ), 'error' );
			return;
		}

		$branches = $this->api->get_branches();

		if( !is_array( $branches ) ) {
			add_settings_error( 'phorest_auth', null, __( $branches, 'wc-phorest' ), 'error' );
		}
	}

	public function get_branches(){

		$options = [
			'' => __( 'Select a branch', 'wc-phorest' )
		];

		foreach( $this->api->get_branches() as $branch ){
			$options[$branch['branchId']] = $branch['name'];
		}

		return $options;
	}
}