<?php

namespace Phorest\Admin;

defined( 'ABSPATH' ) || exit;

class Import {

	public function import_page(){

		global $ph_product_list;

		echo '<div class="wrap">';

		$ph_product_list->prepare_items();

		echo '<form id="import-phorest-products" method="get">';
		$ph_product_list->views();
		$ph_product_list->display();
		echo '</form>';

		echo '</div>';
	}

}