<?php
/**
 * Settings template for RFM ratings to be used over HubSpot.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Hubwoo_RFM_Configuration' ) ) {
	/**
	 * Setup RFM settings board display.
	 *
	 * @package    hubspot-for-woocommerce
	 * @subpackage hubspot-for-woocommerce/includes
	 */
	class Hubwoo_RFM_Configuration extends WP_List_Table {
		/**
		 * Prepare the RFM Settings data table.
		 *
		 * @since 1.0.0
		 */
		public function prepare_items() {

			$per_page              = 10;
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = array();
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$data                  = $this->table_data();
			$current_page          = $this->get_pagenum();
			$total_items           = count( $data );
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);
			$data        = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
			$this->items = $data;
		}

		/**
		 * Get the RFM Setting column.
		 *
		 * @since 1.0.0
		 */
		public function get_columns() {

			$columns = array(
				'score'     => __( 'Score', 'hubspot-for-woocommerce' ) . '<p>(' . __( 'Ratings for RFM Segmentation', 'hubspot-for-woocommerce' ) . ')</p>',
				'recency'   => __( 'Recency', 'hubspot-for-woocommerce' ) . '<p>(' . __( 'Days Since last Order', 'hubspot-for-woocommerce' ) . ')</p>',
				'frequency' => __( 'Frequency', 'hubspot-for-woocommerce' ) . '<p>(' . __( 'Total Orders Placed', 'hubspot-for-woocommerce' ) . ')</p>',
				'monetary'  => __( 'Monetary', 'hubspot-for-woocommerce' ) . '<p>(' . __( 'Total Money Spent', 'hubspot-for-woocommerce' ) . ')</p>',
			);
			return $columns;
		}

		/**
		 * Display the table data its values.
		 *
		 * @since 1.0.0
		 */
		private function table_data() {

			$temp_data = array();

			$rfm_settings      = array(
				'score_5' => 5,
				'score_4' => 4,
				'score_3' => 3,
				'score_2' => 2,
				'score_1' => 1,
			);
			$hubwoo_rfm_at_5   = get_option(
				'hubwoo_rfm_5',
				array(
					0 => 30,
					1 => 20,
					2 => 1000,
				)
			);
			$hubwoo_from_rfm_4 = get_option(
				'hubwoo_from_rfm_4',
				array(
					0 => 31,
					1 => 10,
					2 => 750,
				)
			);
			$hubwoo_to_rfm_4   = get_option(
				'hubwoo_to_rfm_4',
				array(
					0 => 90,
					1 => 20,
					2 => 1000,
				)
			);
			$hubwoo_from_rfm_3 = get_option(
				'hubwoo_from_rfm_3',
				array(
					0 => 91,
					1 => 5,
					2 => 500,
				)
			);
			$hubwoo_to_rfm_3   = get_option(
				'hubwoo_to_rfm_3',
				array(
					0 => 180,
					1 => 10,
					2 => 750,
				)
			);
			$hubwoo_from_rfm_2 = get_option(
				'hubwoo_from_rfm_2',
				array(
					0 => 181,
					1 => 2,
					2 => 250,
				)
			);
			$hubwoo_to_rfm_2   = get_option(
				'hubwoo_to_rfm_2',
				array(
					0 => 360,
					1 => 5,
					2 => 500,
				)
			);
			$hubwoo_rfm_at_1   = get_option(
				'hubwoo_rfm_1',
				array(
					0 => 361,
					1 => 2,
					2 => 250,
				)
			);

			foreach ( $rfm_settings as $key => $single_setting ) {

				if ( 5 == $single_setting ) {

					$new_data = array(
						'score'     => '<h2>' . $single_setting . '</h2>',
						'recency'   => '<p><span>' . __( 'Less than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_5[]" value = "' . $hubwoo_rfm_at_5[0] . '"></p>',
						'frequency' => '<p><span>' . __( 'More than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_5[]" value = "' . $hubwoo_rfm_at_5[1] . '"></p>',
						'monetary'  => '<p><span>' . __( 'More than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_5[]" value = "' . $hubwoo_rfm_at_5[2] . '"></p>',
					);
				} elseif ( 1 == $single_setting ) {

					$new_data = array(
						'score'     => '<h2>' . $single_setting . '</h2>',
						'recency'   => '<p><span>' . __( 'More than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_1[]" value="' . $hubwoo_rfm_at_1[0] . '"></p>',
						'frequency' => '<p><span>' . __( 'Less than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_1[]" value="' . $hubwoo_rfm_at_1[1] . '"></p>',
						'monetary'  => '<p><span>' . __( 'Less than', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_rfm_1[]" value="' . $hubwoo_rfm_at_1[2] . '"></p>',
					);
				} else {

					if ( 4 == $single_setting ) {

						$rfm_from_0 = $hubwoo_from_rfm_4[0];
						$rfm_from_1 = $hubwoo_from_rfm_4[1];
						$rfm_from_2 = $hubwoo_from_rfm_4[2];
						$rfm_to_0   = $hubwoo_to_rfm_4[0];
						$rfm_to_1   = $hubwoo_to_rfm_4[1];
						$rfm_to_2   = $hubwoo_to_rfm_4[2];
					} elseif ( 3 == $single_setting ) {

						$rfm_from_0 = $hubwoo_from_rfm_3[0];
						$rfm_from_1 = $hubwoo_from_rfm_3[1];
						$rfm_from_2 = $hubwoo_from_rfm_3[2];
						$rfm_to_0   = $hubwoo_to_rfm_3[0];
						$rfm_to_1   = $hubwoo_to_rfm_3[1];
						$rfm_to_2   = $hubwoo_to_rfm_3[2];
					} elseif ( 2 == $single_setting ) {

						$rfm_from_0 = $hubwoo_from_rfm_2[0];
						$rfm_from_1 = $hubwoo_from_rfm_2[1];
						$rfm_from_2 = $hubwoo_from_rfm_2[2];
						$rfm_to_0   = $hubwoo_to_rfm_2[0];
						$rfm_to_1   = $hubwoo_to_rfm_2[1];
						$rfm_to_2   = $hubwoo_to_rfm_2[2];
					}

					$new_data = array(
						'score'     => '<h2>' . $single_setting . '</h2>',
						'recency'   => '<p><span>' . __( 'From', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_from_rfm_' . $single_setting . '[]" value="' . $rfm_from_0 . '"></p><p><span>' . __( 'To', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_to_rfm_' . $single_setting . '[]" value="' . $rfm_to_0 . '"></p>',
						'frequency' => '<p><span>' . __( 'From', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_from_rfm_' . $single_setting . '[]" value="' . $rfm_from_1 . '"></p><p><span>' . __( 'To', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_to_rfm_' . $single_setting . '[]" value="' . $rfm_to_1 . '"></p>',
						'monetary'  => '<p><span>' . __( 'From', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_from_rfm_' . $single_setting . '[]" value="' . $rfm_from_2 . '"></p><p><span>' . __( 'To', 'hubspot-for-woocommerce' ) . '</span><input size="5" type="number" class="hubwoo_rfm_data_fields" name="hubwoo_to_rfm_' . $single_setting . '[]" value="' . $rfm_to_2 . '"></p>',
					);
				}

				$temp_data[] = $new_data;
			}

			return $temp_data;
		}

		/**
		 * Return default column name.
		 *
		 * @since 1.0.0
		 * @param int    $item rfm setting column type.
		 * @param string $column_name column name.
		 * @since 1.0.0
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'score':
				case 'recency':
				case 'frequency':
				case 'monetary':
					return $item[ $column_name ];
				default:
					return $item;
			}
		}

		/**
		 * Display the row placeholder.
		 *
		 * @since 1.0.0
		 */
		public function display_rows_or_placeholder() {

			if ( $this->has_items() ) {

				$this->display_rows();
			} else {

				?>
					<tr class="no-items"><td class="colspanchange" colspan="<?php echo esc_attr( $this->get_column_count() ); ?>">
				<?php
					$this->no_items();
				?>
				</td></tr>
				<?php
			}
		}
	}
}

