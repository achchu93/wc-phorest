<?php

namespace Phorest\Api;

class Base {

	protected $url;

	protected $username;

	protected $password;

	protected $settings;

	protected $error_messages = [];

	protected $success_messages = [];

	public function __construct() {

		$this->url = 'http://api-gateway-eu.phorest.com/third-party-api-server/api/business';
		$this->settings = get_option( 'phorest_auth', [] ) + get_option( 'phorest_import', [] );
		$this->username = !empty( $this->settings['username'] ) ? $this->settings['username'] : '';
		$this->password = !empty( $this->settings['password'] ) ? $this->settings['password'] : '';

		$this->set_error_messages();
		$this->set_success_messages();
	}

	private function set_error_messages(){
		$this->error_messages = [
			401 => __( 'Unauthorized', 'wc-phorest' ),
			403 => __( 'Forbidden', 'wc-phorest' ),
			404 => __( 'Business with a given id doesn\'t exist', 'wc-phorest' )
		];
	}

	private function set_success_messages(){
		$this->success_messages = [
			200 => '',
			201 => ''
		];
	}

	protected function request( $endpoint = '/', $method = 'GET', $data = [] ){

		$request = wp_remote_request(
			$this->url . $endpoint,
			[
				'method'  => $method,
				'headers' => [
					'content-type'  => 'application/json',
					'authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password )
				],
				'body'    => $data
			]
		);

		return $this->parse_request_data($request);
	}

	protected function parse_request_data( $request_data ){

		if( is_wp_error( $request_data ) || !array_key_exists( wp_remote_retrieve_response_code( $request_data ), $this->success_messages ) ){
			return $this->parse_error_request_data( $request_data );
		}

		return $this->parse_success_request_data( json_decode( wp_remote_retrieve_body( $request_data ), true ) );
	}

	protected function parse_error_request_data( $data ){

		$message = wp_remote_retrieve_response_message( $data );

		if( !is_wp_error( $data ) ){
			$decoded = json_decode( wp_remote_retrieve_body( $data ), true );

			if( !empty( $decoded['message'] ) ) {
				$message = $decoded['message'];
			}elseif( isset( $this->error_messages[ wp_remote_retrieve_response_code( $data ) ] ) ){
				$message = $this->error_messages[ wp_remote_retrieve_response_code( $data ) ];
			}
		}

		return [
			'success' => false,
			'message' => $message,
			'code'    => wp_remote_retrieve_response_code( $data )
		];
	}

	protected function parse_success_request_data( $data ){

		return [
			'success' => true,
			'message' => $data,
			'code'    => wp_remote_retrieve_response_code( $data )
		];

	}

	public function get_branches(){

		$response = $this->request( "/{$this->settings['business_id']}/branch" );
		return isset( $response['message']['_embedded']['branches'] ) ? $response['message']['_embedded']['branches'] : $response['message'];
	}

	public function get_products( $branch_id = '', $page = 0 ){

		if( empty( $branch_id ) ){
			$branch_id = $this->settings['branch_id'];
		}

		$response = $this->request( "/{$this->settings['business_id']}/branch/{$branch_id}/product", "GET", [ "page" => $page ] );

		$data               = [];
		$data['products']   = isset( $response['message']['_embedded']['products'] ) ? $response['message']['_embedded']['products'] : [];
		$data['page']       = isset( $response['message']['page'] ) ? $response['message']['page'] : [];

		return $data;
	}

}