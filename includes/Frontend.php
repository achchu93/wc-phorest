<?php

namespace Phorest;

use Phorest\Api\Base as Api;

class Frontend {

	public function __construct() {
		add_action( 'woocommerce_order_status_completed', [ $this, 'sync_phorest_product' ], 10, 2 );
	}

	public function sync_phorest_product( $order_id, $order ){

		$line_items = $order->get_items();
		$ph_items 	= array_filter( $line_items, function( $item ){
			return !empty( get_post_meta( $item->get_product_id(), 'wcph_productId', true ) );
		});

		// bail out if there is not phorest items
		if( !count( $ph_items ) ){
			return;
		}

		$total = 0;
		$items = [];

		foreach( $ph_items as $item ){

			$items[] = [
				'branchProductId' => get_post_meta( $item->get_product_id(), 'wcph_productId', true ),
				'quantity' 		  => $item->get_quantity(),
				'price' 		  => $order->get_item_total( $item, true, true ) // item price with tax and rounded
			];

			$total += $item->get_total();
		}

		$request  = [
			'clientId' => 'lM82gdvTuQnnTNt008KcbA', // has to change dynamically
			'number'   => $order->get_order_number(),
			'payments' => [
				[
					'amount' 		=> round( $total, 2 ),
					'customTypeCode' => $order->get_payment_method(),
					'customTypeName' => $order->get_payment_method_title(),
					'paymentId' 	 => $order->get_transaction_id(),
					'type' 			 => 'CREDIT'
				]
			],
			'items'   => $items
		];

		$api      = new Api();
		$purchase = $api->create_purchase( '', $request );

		if( !empty( $purchase['purchaseId'] ) ){
			update_post_meta( $order_id, 'wcph_transaction_number', $purchase['transactionNumber'] );
			update_post_meta( $order_id, 'wcph_transaction_data', $purchase );
		}
	}

}