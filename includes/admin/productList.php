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
			'in_store' 		=> __( 'In store', 'wc-phorest' )
		);
	}

	public function prepare_items(){

		$branch = wc_phorest_settings( 'branch_id', 'phorest_import', '' );

		if( !empty( $_GET['ph_branch'] ) ){
			$branch = $_GET['ph_branch'];
		}

		if( empty( $branch ) ){
			$this->items = [];
			return;
		}

		$page = !empty( $_GET['paged'] ) ? intval( $_GET['paged'] ) - 1 : 0;

		$api      = new Api();
		$data     = $api->get_products( $branch, $page );
		$products = $data['products'];
		foreach( $products as $product ){
			$this->items[] = [
				'ID' => $product['productId'],
				'name' => $product['name'],
				'barcode' => $product['barcode'],
				'price' => wc_price( $product['price'] ),
				'stock' => $product['quantityInStock']
			];
		}

		if( count( $data['page'] ) ){
			$this->set_pagination_args(
				array(
					'total_items' => isset( $data['page']['totalElements'] ) ? $data['page']['totalElements'] : count( $products ),
					'per_page'    => isset( $data['page']['size'] ) ? $data['page']['size'] : count( $products ),
					'total_pages' => isset( $data['page']['totalPages'] ) ? $data['page']['totalPages'] : 1,
				)
			);
		}
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

	public function column_stock( $data ){
		return $data['stock'];
	}

	public function column_in_store( $data ){
		$product_id = wc_get_product_id_by_sku( $data['barcode'] );

		return $product_id ? 'In store' : 'Not in store';
	}

	public function no_items() {
        _e( 'No products found. Please check your default branch or select one from navigation.', 'wc-phorest' );
    }

	public function get_bulk_actions(){
		return [
			'import_product' => __( 'Import Products', 'wc-phorest' )
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
}