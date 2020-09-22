<?php

namespace Phorest;

use Phorest\Api\Base as Api;

defined( 'ABSPATH' ) || exit;

class Ajax {


	public function __construct() {

		$actions = [
			'wcph_single_import' => false,
			'wcph_single_sync' => false,
		];

		foreach( $actions as $action => $frontend ){
			add_action( "wp_ajax_{$action}", [ $this, $action ] );

			if( $frontend ){
				add_action( "wp_ajax_nopriv_{$action}", [ $this, $action ] );
			}
		}

	}

	public function wcph_single_import(){

		check_ajax_referer( 'wcph-admin-action', 'nonce' );

		if( empty( $_POST['barcode'] ) ){
			wp_send_json_error( [ 'message' => __( 'Barcode is required', 'wc-phorest' ) ] );
		}

		$api     = new Api();
		$products = $api->get_products( $_POST['branch_id'], [ 'searchQuery' => $_POST['barcode'] ] );

		if( !isset( $products['products'] ) || !count( $products['products'] ) ){
			wp_send_json_error( [ 'message' => __( 'product not found', 'wc-phorest' ) ] );
		}

		$product    = current( $products['products'] );
		$wc_product = new \WC_Product();
		$wc_product->set_props([
			'name' 			 => $product['name'],
			'price' 		 => $product['price'],
			'regular_price'  => $product['price'],
			'manage_stock' 	 => true,
			'stock_quantity' => $product['quantityInStock'],
			'sku' 			 => $product['barcode']
		]);

		foreach( [ 'code', 'barcode' ] as $meta ){
			$wc_product->add_meta_data( "wcph_{$meta}", $product[$meta] );
		}

		$new_product = $wc_product->save();

		wp_send_json_success( [ 'product' => $new_product ] );
	}

}