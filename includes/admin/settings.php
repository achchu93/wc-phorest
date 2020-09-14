<?php

namespace Phorest\Admin;

use Phorest\Api\Settings as Settings_Api;
use Phorest\Api\Base as API;

defined( 'ABSPATH' ) || exit;

class Settings {

	public $settings_api;

	public function __construct(){

		$this->init();
		$this->init_hooks();
	}

	private function init(){

		$this->settings_api = new Settings_Api;
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		$this->settings_api->admin_init();
	}

	private function init_hooks(){

		add_action( 'update_option_phorest_auth', [ $this, 'auth_validation' ], 10, 3 );
	}

	private function get_settings_sections(){

		return [
			[
				'id'    => 'phorest_auth',
                'title' => __( 'Authentication', 'wc-phorest' )
			]
		];
	}

	private function get_settings_fields(){

		return [
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
			]
		];
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

		$api 	  = new API();
		$branches = $api->get_branches();

		if( !is_array( $branches ) ) {
			add_settings_error( 'phorest_auth', null, __( $branches, 'wc-phorest' ), 'error' );
		}
	}
}