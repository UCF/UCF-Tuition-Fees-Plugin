<?php
/**
 * Commands for importing and updating tuition and fee data
 */
class TuitionCommand extends WP_CLI_Command {
	/**
	 * Imports tuition and fee data
	 *
	 * ## OPTIONS
	 *
	 * <api>
	 * : The url of the tuition and fees feed (Required)
	 *
	 * [--post-type=<post_type>]
	 * : The post type to import data into. Defaults to `degree`
	 *
	 * ## EXAMPLES
	 *
	 * $ wp tuition import https://finacctg.fa.ucf.edu/sas/feed/feed.cfm
	 *
	 * $ wp tuition import https://finacctg.fa.ucf.edu/sas/feed/feed.cfm --post-type=programs
	 */
	public function import( $args, $assoc_args ) {
		$api = $args[0];
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
