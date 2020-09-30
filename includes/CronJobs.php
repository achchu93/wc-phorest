<?php

namespace Phorest;

use Phorest\Api\Base as Api;
use League\Csv\Reader;
use League\Csv\Statement;

defined( 'ABSPATH' ) || exit;

class CronJobs {

	public $jobs = [
		'product_import'
	];

	public function __construct() {
		add_filter( 'cron_schedules', [ $this, 'schedules' ] );
		add_action( 'init', [ $this, 'register_cron_jobs' ] );

		// remove all the jobs on plugin deactivation
		register_deactivation_hook( 'WCPH_PLUGIN_FILE', [ $this, 'unregister_cron_jobs' ] );
	}

	public function schedules( $schedules ){

		$schedules['twicehourly'] = [
			'interval' => 1800,
        	'display'  => __( 'Every Half an hour', 'wc-phorest' )
		];

		return $schedules;
	}

	public function register_cron_jobs(){

		foreach( $this->jobs as $job ){
			$this->{'schedule_'.$job}( "wcph_$job" );
			add_action( "wcph_$job", [ $this, "run_$job" ] );
		}
	}

	public function schedule_product_import( $job ){

		$settings = get_option( 'phorest_import', [] );

		if( empty( $settings['branch_id'] ) ){
			return;
		}

		if ( ! wp_next_scheduled( $job ) ) {
			wp_schedule_event( time(), 'twicehourly', $job );
		}
	}

	public function run_product_import(){

		$settings = get_option( 'phorest_import', [] );

		if( empty( $settings['branch_id'] ) ){
			$this->unschedule_job( 'wcph_product_import' );
			return;
		}

		$api  = new Api();
		$job  = $api->create_csv_export_job();
		if( isset( $job['jobId'] ) ){
			$csv_data = $api->get_csv_export_job( $job['jobId'] );

			$reader   = Reader::createFromString( @file_get_contents( $csv_data['tempCsvExternalUrl'] ) );

			if( $reader->count() === 0 ){
				return;
			}

			$reader->setHeaderOffset(0);

			$stmt    = (new Statement())
						->where( function( $record ){ return $record['item_type'] === 'PRODUCT'; } );
			$records = $stmt->process($reader);

			$last_product_update = get_option( '_ph_last_product_update', '' );

			foreach( $records as $offset => $row ){
				$t_time  = strtotime( "{$row['purchased_date']} {$row['purchase_time']}" );
				$product = wc_get_product_id_by_sku( $row['product_barcode'] );

				if( !$product || ( !empty( $last_product_update ) && strtotime( $last_product_update ) <= $t_time ) ){
					continue;
				}

				$new_qty = wc_update_product_stock( $product, intval( $row['quantity'] ), 'decrease' );

				if( $new_qty ){
					update_option( '_ph_last_product_update', $t_time );
				}
			}

			update_option( '_ph_last_stock_update', strtotime("now") );
		}

	}

	public function unregister_cron_jobs(){

		foreach( $this->jobs as $job ){
			$this->unschedule_job( "wcph_$job" );
		}
	}

	private function unschedule_job( $job ){

		$timestamp = wp_next_scheduled( $job );
		wp_unschedule_event( $timestamp, $job );
	}
}