<?php

namespace Phorest\Admin;

defined( 'ABSPATH' ) || exit;

class Settings {

	public $settings_api;

	public function __construct(){

        $this->init();
	}

	private function init(){

		$this->settings_api = new \WeDevs_Settings_API;
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );
		$this->settings_api->admin_init();
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
                    'type'              => 'text'
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
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }
}