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
		 **/
		public static function display( $items, $title, $layout='default', $args=array() ) {
			$content = display_default( $items, $title, $args );

			if ( has_filter( 'ucf_tuition_fees_display_' . $layout ) ) {
				$content = apply_filters( 'ucf_tuition_fees_display_' . $layout, $content, $items, $title, $args );
			}

			return $content;
		}

		/**
		 * Default layout
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param $items Array | The array of tuition items
		 * @param $title string | The title to display
		 **/
		public static function display_default( $items, $title, $args ) {
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
			<?php if ( $title ) : ?>
				<caption><?php echo $title; ?></caption>
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
