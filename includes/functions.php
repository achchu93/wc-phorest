<?php

function wc_phorest_settings( $option, $section, $default = '' ){

	$options = get_option( $section, [] );

	return isset( $options[ $option ] ) ? $options[ $option ] : $default;
}


function wc_phorest_order_by_number( $number ){

	$posts = get_posts([
		'post_type' => 'shop_order',
		'fields' 	=> 'ids',
		'post_status' => 'wc-completed',
		'posts_per_page' => 1,
		'meta_key' => 'wcph_transaction_number',
		'meta_value' => $number
	]);

	return count( $posts ) ? reset( $posts ) : false;
}