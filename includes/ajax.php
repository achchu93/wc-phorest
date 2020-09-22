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

		if( $new_product ){

			$new_product = wc_get_product( $new_product );

			$tr  = '<tr data-row="'._wp_specialchars( wp_json_encode( $product ), ENT_QUOTES, 'UTF-8', true ).'">';
			$tr .= '<th scope="row" class="check-column"><input type="checkbox" name="products[]" value="'.$product['productId'].'" /></th>';
			$tr .= '<td class="name column-name has-row-actions column-primary" data-colname="'.__( 'Product Name', 'wc-phorest' ).'">'.$product['name'].'<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>';
			$tr .= '<td class="barcode column-barcode" data-colname="'.__( 'Barcode', 'wc-phorest' ).'">'.$product['barcode'].'</td>';
			$tr .= '<td class="price column-price" data-colname="'.__( 'Price', 'wc-phorest' ).'">'.wc_price($product['price']).'</td>';
			$tr .= '<td class="stock column-stock" data-colname="'.__( 'Stock', 'wc-phorest' ).'">'.$product['quantityInStock'].'</td>';
			$tr .= '<td class="in_store column-in_store" data-colname="'.__( 'In store', 'wc-phorest' ).'"><a href="'.get_edit_post_link($new_product->get_id()).'" target="_blank"><strong>'.$new_product->get_name().'</strong></a><strong>Stock:</strong> '.$new_product->get_stock_quantity().'</td>';
			$tr .= '<td class="actions column-actions" data-colname="'.__( 'Actions', 'wc-phorest' ).'"><button type="button" class="single-sync button"><i class="dashicons dashicons-update-alt"></i></button></td>';
			$tr .= '</tr>';

			wp_send_json_success( [ 'product' => $tr ] );
		}

		wp_send_json_error( [ 'message' => __( 'An error occurred', 'wc-phorest' ) ] );
	}


	public function wcph_single_sync(){

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
		$product_id = wc_get_product_id_by_sku( $product['barcode'] );

		if( !$product_id ){
			wp_send_json_error( [ 'message' => __( 'woocommerce product not found', 'wc-phorest' ) ] );
		}

		$wc_product = wc_get_product( $product_id );
		$wc_product->set_stock_quantity( $product['quantityInStock'] );
		$wc_product->save();

		$tr  = '<tr data-row="'._wp_specialchars( wp_json_encode( $product ), ENT_QUOTES, 'UTF-8', true ).'">';
		$tr .= '<th scope="row" class="check-column"><input type="checkbox" name="products[]" value="'.$product['productId'].'" /></th>';
		$tr .= '<td class="name column-name has-row-actions column-primary" data-colname="'.__( 'Product Name', 'wc-phorest' ).'">'.$product['name'].'<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>';
		$tr .= '<td class="barcode column-barcode" data-colname="'.__( 'Barcode', 'wc-phorest' ).'">'.$product['barcode'].'</td>';
		$tr .= '<td class="price column-price" data-colname="'.__( 'Price', 'wc-phorest' ).'">'.wc_price($product['price']).'</td>';
		$tr .= '<td class="stock column-stock" data-colname="'.__( 'Stock', 'wc-phorest' ).'">'.$product['quantityInStock'].'</td>';
		$tr .= '<td class="in_store column-in_store" data-colname="'.__( 'In store', 'wc-phorest' ).'"><a href="'.get_edit_post_link($wc_product->get_id()).'" target="_blank"><strong>'.$wc_product->get_name().'</strong></a><strong>Stock:</strong> '.$wc_product->get_stock_quantity().'</td>';
		$tr .= '<td class="actions column-actions" data-colname="'.__( 'Actions', 'wc-phorest' ).'"><button type="button" class="single-sync button"><i class="dashicons dashicons-update-alt"></i></button></td>';
		$tr .= '</tr>';

		wp_send_json_success( [ 'product' => $tr ] );
	}

}