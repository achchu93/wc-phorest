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
			'barcode'       => __( 'Barcode', 'wc-phorest' ),
			'price' 		=> __( 'Price', 'wc-phorest' ),
			'stock' 		=> __( 'Stock', 'wc-phorest' ),
			'in_store' 		=> __( 'In store', 'wc-phorest' ),
			'actions'       => __( 'Actions', 'wc-phorest' )
		);
	}

	public function prepare_items(){

		$this->process_bulk_action();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		$product_data = $this->get_products();
		$products     = isset( $product_data['products'] ) ? $product_data['products'] : [];

		foreach( $products as $product ){
			$this->items[] = [
				'ID' 	  => $product['productId'],
				'name' 	  => $product['name'],
				'barcode' => $product['barcode'],
				'price'   => wc_price( $product['price'] ),
				'stock'   => $product['quantityInStock'],
				'product' => $product
			];
		}

		if( count( $product_data['page'] ) ){
			$this->set_pagination_args(
				array(
					'total_items' => isset( $product_data['page']['totalElements'] ) ? $product_data['page']['totalElements'] : count( $products ),
					'per_page'    => isset( $product_data['page']['size'] ) ? $product_data['page']['size'] : count( $products ),
					'total_pages' => isset( $product_data['page']['totalPages'] ) ? $product_data['page']['totalPages'] : 1,
				)
			);
		}
	}

	public function column_cb( $data ) {
		return sprintf( '<input type="checkbox" name="products[]" value="%1$s" />', $data['ID'] );
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

	public function column_stock( $data ){
		return $data['stock'];
	}

	public function column_in_store( $data ){

		if( !empty( $data['barcode'] ) && $id = wc_get_product_id_by_sku( $data['barcode'] ) ){
			$product = wc_get_product( $id );
			$string  = sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', get_edit_post_link( $product->get_id() ), $product->get_name() );
			$string .= sprintf( '<strong>Stock:</strong> %s', $product->get_stock_quantity() );

			return $string;
		}

		return 'Not in store';
	}

	public function column_actions( $data ){

		$actions    = '';
		$product_id = !empty( $data['barcode'] ) ? wc_get_product_id_by_sku( $data['barcode'] ) : 0;
		$product    = wc_get_product( $product_id );

		if( !$product ){
			$actions .= '<button type="button" class="single-import button"><i class="dashicons dashicons-arrow-down-alt"></i></button>';
		}else{
			$actions .= '<button type="button" class="single-sync button"><i class="dashicons dashicons-update-alt"></i></button>';
		}

		return $actions;
	}

	public function no_items() {
        _e( 'No products found. Please check your default branch or select one from navigation.', 'wc-phorest' );
    }

	public function get_bulk_actions(){
		return [
			'import_product' => __( 'Import Products', 'wc-phorest' ),
			'update_stock' 	 => __( 'Update Stock', 'wc-phorest' ),
		];
	}

	protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        }
        ?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if( $which === 'top' ): ?>
			<div class="alignleft actions bulkactions">
				<label for="ph_branch_selection_top" class="screen-reader-text">Select a branch</label>
				<select name="ph_branch" id="ph_branch_selection_top">
					<option value="-1"><?php _e( 'Select a branch', 'wc-phorest' ); ?></option>
					<?php
					$api       = new Api();
					$branches  = $api->get_branches();
					$branch_id = !empty( $_GET['ph_branch'] ) ? $_GET['ph_branch'] : '';

					if( empty( $branch_id ) ){
						$branch_id   = wc_phorest_settings( 'branch_id', 'phorest_import', '' );
					}

					foreach( $branches as $branch ){
						?>
						<option value="<?php echo $branch['branchId']; ?>" <?php selected( $branch_id, $branch['branchId'], true ); ?> ><?php echo $branch['name']; ?></option>
						<?php
					}
					?>
				</select>
				<?php if( !$this->has_items() ){
					submit_button( __( 'Apply' ), 'action', '', false);
				} ?>
			</div>
			<?php endif; ?>

			<?php if ( $this->has_items() ) : ?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			endif;

			$last_updated = get_option( '_ph_last_stock_update', '' );
			if( !empty( $last_updated ) ){
				echo sprintf(
					'<span class="ph-last-update"><strong>%1$s : %2$s</strong></span>',
					__( 'Products last updated at', 'wc-phorest' ),
					date( 'Y-m-d h:i:s A', intval( $last_updated ) )
				);
			}

			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
        <?php
    }

	protected function extra_tablenav( $which ){
		do_action( 'manage_ph_products_extra_tablenav', $which );
	}

	public function single_row( $item ) {
		echo '<tr data-row="'._wp_specialchars( wp_json_encode( $item['product'] ), ENT_QUOTES, 'UTF-8', true ).'">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	private function get_products(){

		$branch = wc_phorest_settings( 'branch_id', 'phorest_import', '' );

		if( !empty( $_REQUEST['ph_branch'] ) ){
			$branch = $_REQUEST['ph_branch'];
		}

		if( empty( $branch ) ){
			$this->items = [];
			return [];
		}

		$page   = !empty( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) - 1 : 0;
		$search = !empty( $_REQUEST['s'] ) ? $_GET['s'] : '';

		$api      = new Api();

		return $api->get_products( $branch, [ "page" => $page, "searchQuery" => $search ] );
	}

	public function process_bulk_action(){

		$action = $this->current_action();
		if ( ! $action || !is_array( $_REQUEST['products'] ) || !count( $_REQUEST['products'] ) ) {
			return;
		}

		if( !is_callable( [ $this, $action ] ) ){
			return;
		}

		$this->{$action}();
	}

	private function import_product(){

		$product_data = $this->get_products();
		if( !isset( $product_data['products'] ) ){
			return;
		}

		foreach( $product_data['products'] as $product ){
			if( in_array( $product['productId'], $_REQUEST['products'] ) ){

				if( wc_get_product_id_by_sku( $product['barcode'] ) ){
					continue;
				}

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
			}
		}

		wp_redirect( remove_query_arg(
			array( '_wp_http_referer', '_wpnonce', 'products', 'action', 'action2' ),
			wp_unslash( $_SERVER['REQUEST_URI'] )
		));
		exit;
	}

	private function update_stock(){

		$product_data = $this->get_products();
		if( !isset( $product_data['products'] ) ){
			return;
		}

		foreach( $product_data['products'] as $product ){
			if( in_array( $product['productId'], $_REQUEST['products'] ) ){

				$product_id = wc_get_product_id_by_sku( $product['barcode'] );

				if( !$product_id ){
					continue;
				}

				$wc_product = wc_get_product( $product_id );
				$wc_product->set_stock_quantity( $product['quantityInStock'] );
				$wc_product->save();
			}
		}

		wp_redirect( remove_query_arg(
			array( '_wp_http_referer', '_wpnonce', 'products', 'action', 'action2' ),
			wp_unslash( $_SERVER['REQUEST_URI'] )
		));
		exit;
	}
}