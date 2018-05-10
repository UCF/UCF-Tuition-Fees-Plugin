<?php
/**
 * Commands for importing and updating tuition and fee data
 */
class Tuition_Command extends WP_CLI_Command {
	/**
	 * Imports tuition and fee data
	 *
	 * ## OPTIONS
	 *
	 * [--api=<api>]
	 * : The url of the tuition and fees feed. Defaults to the base feed url set in plugin options
	 *
	 * [--post-type=<post_type>]
	 * : The post type to import data into. Defaults to `degree`
	 *
	 * ## EXAMPLES
	 *
	 * $ wp tuition import
	 *
	 * $ wp tuition import --api="https://finacctg.fa.ucf.edu/sas/feed/feed.cfm" --post-type="programs"
	 */
	public function import( $args, $assoc_args ) {
		$api = isset( $assoc_args['api'] ) ? $assoc_args['api'] : UCF_Tuition_Fees_Config::get_option_or_default( 'base_feed_url' );
		$post_type = isset( $assoc_args['post-type'] ) ? $assoc_args['post-type'] : 'degree';

		$import = new Tuition_Fees_Data_Importer( $api, $post_type );

		try {
			$import->import();
		}
		catch( Exception $e ) {
			WP_CLI::error( $e->getMessage(), $e->getCode() );
		}

		WP_CLI::success( $import->get_stats() );
	}
}
