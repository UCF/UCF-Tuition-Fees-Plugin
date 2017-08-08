<?php
/**
 * Responsible for presenting tuition and fees
 **/
if ( ! class_exists( 'UCF_Tuition_Fees_Common' ) ) {
	class UCF_Tuition_Fees_Common {

		/**
		 * Primary function for displaying tuition and fees
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $items Array | The array of tuition and fee items
		 * @param $layout String | The name of the layout to use when displaying tuition data
		 * @param $args Array | Extra arguments to pass to the layout
		 **/
		public static function display( $items, $layout='default', $args=array() ) {
			// Main content/loop
			$layout_content = self::display_default( '', $items, $args );
			if ( has_filter( 'ucf_tuition_fees_display_' . $layout ) ) {
				$layout_content = apply_filters( 'ucf_tuition_fees_display_' . $layout, $layout_content, $items, $args );
			}
			echo $layout_content;
		}

		/**
		 * Default layout
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $content String | Existing content HTML
		 * @param $items Array | The array of tuition items
		 * @param $args Array | Extra arguments for the layout
		 **/
		public static function display_default( $content, $items, $args ) {
			if ( ! is_array( $items ) ) { $items = array(); }
			$resident_total = 0;
			$non_resident_total = 0;

			$formatted = array();

			foreach( $items as $item ) {
				// Throw out extra fees
				if ( strpos( $item->FeeName, '(Per Hour)' ) !== false ) {
					continue;
				}

				$formatted[] = array(
					'name'   => $item->FeeName,
					'res'    => money_format( '%.2n', UCF_Tuition_Fees_Utils::format_fee( $item->MinResidentFee, $item->MaxResidentFee ) ),
					'nonres' => money_format( '%.2n', UCF_Tuition_Fees_Utils::format_fee( $item->MinNonResidentFee, $item->MaxNonResidentFee ) )
				);

				$resident_total += $item->MaxResidentFee;
				$non_resident_total += $item->MaxNonResidentFee;
			}
			setlocale( 'en_US' );
			ob_start();
		?>
			<table class="table tuition-fees-table">
			<?php if ( $args['title'] ) : ?>
				<caption><?php echo $args['title']; ?></caption>
			<?php endif; ?>
				<thead>
					<tr>
						<th>Fee</th>
						<th>Resident</th>
						<th>Non-Resident</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td>Tuition and Fee Total Per Credit Hour</td>
						<td><?php echo money_format( '$%.2n', $resident_total ); ?></td>
						<td><?php echo money_format( '$%.2n', $non_resident_total ); ?></td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach( $formatted as $item ) : ?>
					<tr>
						<td><?php echo $item['name']; ?></td>
						<td><?php echo $item['res']; ?></td>
						<td><?php echo $item['nonres'] ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php
			return ob_get_clean();
		}
	}
}
