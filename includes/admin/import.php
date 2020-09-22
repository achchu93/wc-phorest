<?php

namespace Phorest\Admin;

defined( 'ABSPATH' ) || exit;

class Import {

	public function import_page(){

		global $ph_product_list;

		echo '<div class="wrap">';

		$ph_product_list->prepare_items();

		echo '<form id="import-phorest-products" method="get">';
		echo '<input type="hidden" name="page" value="'.$_GET['page'].'" />';
		$ph_product_list->search_box( __( 'Search', 'wc-phorest' ), 'search_id' );
		$ph_product_list->display();
		echo '</form>';

		echo '</div>';
	}

}