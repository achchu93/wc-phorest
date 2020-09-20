<?php

namespace Phorest\Admin;

use Phorest\Api\Base as Api;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ProductList extends \WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'ph_product',
				'plural'   => 'ph_products',
				'ajax'     => true
			)
		);
	}

	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'name'         	=> __( 'Product Name', 'wc-phorest' ),
			'price' 		=> __( 'Price', 'wc-phorest' ),
			'barcode'       => __( 'Barcode', 'wc-phorest' )
		);
	}

	public function prepare_items(){

		$branch = wc_phorest_settings( 'branch_id', 'phorest_import', '' );

		if( empty( $branch ) ){
			$this->items = [];
			return;
		}

		$api      = new Api();
		$products = $api->get_products();
		foreach( $products as $product ){
			$this->items[] = [
				'ID' => $product['productId'],
				'name' => $product['name'],
				'price' => wc_price( $product['price'] ),
				'barcode' => $product['barcode']
			];
		}

		$this->set_pagination_args(
			array(
				'total_items' => count( $products ),
				'per_page'    => count( $products ),
				'total_pages' => 1,
			)
		);
	}

	public function column_cb( $data ) {
		return sprintf( '<input type="checkbox" name="key[]" value="%1$s" />', $data['ID'] );
	}

	public function column_name( $data ){
		return $data['name'];
	}

	public function column_price( $data ){
		return $data['price'];
	}

	public function column_barcode( $data ){
		return $data['barcode'];
	}

	public function get_bulk_actions(){
		return [
			'import_product' => __( 'Import Products', 'wc-phorest' )
		];
	}
}