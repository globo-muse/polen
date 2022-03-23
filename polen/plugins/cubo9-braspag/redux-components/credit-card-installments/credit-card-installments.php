<?php
/**
 * Credit Card Installments.
 *
 * @package     Cubo9Marketplace/ReduxComponents
 * @author      Cubo9
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'C9_Redux_Credit_Card_Installments', false ) ) {

	if( !class_exists( 'Redux_Field' ) ) {
		include_once ABSPATH . '/polen/plugins/redux-framework/redux-core/inc/classes/class-redux-field.php';
	}
	/**
	 * Main C9_Redux_Credit_Card_Installments class
	 *
	 * @since       1.0.0
	 */
	class C9_Redux_Credit_Card_Installments extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			$this->installments = 12;
			$this->add_text   = ( isset( $this->field['add_text'] ) ) ? $this->field['add_text'] : esc_html__( 'Add More', 'redux-framework' );
			$this->show_empty = ( isset( $this->field['show_empty'] ) ) ? $this->field['show_empty'] : true;
			$this->field['name_suffix'] = ( isset( $this->field['name_suffix'] ) && ! empty( $this->field['name_suffix'] ) ) ? $this->field['name_suffix'] : '[]';
			$this->field['add_number'] = ( isset( $this->field['add_number'] ) && is_numeric( $this->field['add_number'] ) ) ? $this->field['add_number'] : 1;

			if( ! $this->show_empty ) {
				$show_empty = ' style="display: none;"';
			} else {
				$show_empty = '';
			}

			echo '
				<p style="margin: 0px 0px 10px; 0px;>
					<span style="clear:both;display:block;height:0;"></span>
					<a href="javascript:void(0);" class="button button-primary redux-credit-card-installments-add" data-add_number="' . esc_attr( $this->field['add_number'] ) . '" data-id="' . esc_attr( $this->field['id'] ) . '-ul" data-name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '">' . esc_html( $this->add_text ) . '</a>
				</p>

				<div id="creditCardBrandsInstallments">
				';
					$this->table( array(), true, $show_empty );
			if ( isset( $this->value ) && is_array( $this->value ) ) {
				foreach ( $this->value['brand'] as $k => $value ) {
					if ( '' !== $value || ( '' === $value && true === $this->show_empty ) ) {
						$args = array(
							'active' => $this->value['active'][$k], 
							'brand' => $value,
							'slug'  => $this->value['slug'][$k],
							'icon'  => $this->value['icon'][$k],
							'debit_tax'  => $this->value['debit_tax'][$k],
							'installments' => array(
								'1'  => $this->value['installments'][1][$k],
								'2'  => $this->value['installments'][2][$k],
								'3'  => $this->value['installments'][3][$k],
								'4'  => $this->value['installments'][4][$k],
								'5'  => $this->value['installments'][5][$k],
								'6'  => $this->value['installments'][6][$k],
								'7'  => $this->value['installments'][7][$k],
								'8'  => $this->value['installments'][8][$k],
								'9'  => $this->value['installments'][9][$k],
								'10'  => $this->value['installments'][10][$k],
								'11'  => $this->value['installments'][11][$k],
								'12'  => $this->value['installments'][12][$k],
							),
						);
						$this->table( $args );
					}
				}
			}

			echo '
				<div>
				';
		}

		public function table( $args = array(), $is_base = false, $show_empty = '' ) {
			$table_base_1 = ( $is_base ) ? ' redux-credit-card-installments-base-table' : '';
			$table_base_2 = ( $is_base ) ? ' redux-credit-card-installments-base-table-installments' : '';
			echo '
					<div>
						<!-- Bandeiras -->
						<table class="redux-credit-card-installments' . $table_base_1 . '"' . $show_empty . '>
							<thead>
								<tr>
									<th style="width: 15%;">
										Ativo?
									</th>
									<th style="width: 25%;">
										Bandeira
									</th>
									<th style="width: 25%;">
										Slug
									</th>
									<th style="width: 25%;">
										Ícone
									</th>
									<th style="width: 10%; text-align: center;">
										Ações
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
				';
			
			$active = $this->field['name'] . '[active]'  . $this->field['name_suffix'];
			$brand = $this->field['name'] . '[brand]'  . $this->field['name_suffix'];
			$slug = $this->field['name'] . '[slug]' . $this->field['name_suffix'];
			$icon = $this->field['name'] . '[icon]' . $this->field['name_suffix'];
			$debit_tax = $this->field['name'] . '[debit_tax]' . $this->field['name_suffix'];

			$active_value = ( isset( $args['active'] ) && intval( $args['active'] ) == intval( '1' ) ) ? '1' : '0';
			if( intval( $active_value ) == intval( '1' ) ) {
				$active_select1 = ' selected';
				$active_select2 = '';
			} else {
				$active_select1 = '';
				$active_select2 = ' selected';
			}
			
			$brand_value = ( isset( $args['brand'] ) ) ? $args['brand'] : '';
			$slug_value = ( isset( $args['slug'] ) ) ? $args['slug'] : '';
			$icon_value = ( isset( $args['icon'] ) ) ? $args['icon'] : '';
			$debit_tax_value = ( isset( $args['debit_tax'] ) ) ? $args['debit_tax'] : '';

			echo '
									<td>
										<select name="' . esc_attr( $active ) . '" class="widefat">
											<option value="1"' . $active_select1 . '>Sim</option>
											<option value="0"' . $active_select2 . '>Não</option>
										</select>
									</td>
									<td>
										<input type="text" name="' . esc_attr( $brand ) . '" value="' . $brand_value . '" class="widefat" />
									</td>
									<td>
										<input type="text" name="' . esc_attr( $slug ) . '" value="' . $slug_value . '" class="widefat" />
									</td>
									<td>
										<input type="text" name="' . esc_attr( $icon ) . '" value="' . $icon_value . '" class="widefat" />
									</td>
									<td style="text-align: center;">
										<a href="javascript:void(0);" class="deletion redux-credit-card-installments-remove">
											<i class="el el-remove"></i>
										</a>
									</td>
								</tr>
							</tbody>
						</table>

						<!-- Taxas Débito e Crédito -->
						<table class="redux-credit-card-installments' . $table_base_2 . '"' . $show_empty . '>
							<thead>
								<tr>
									<th colspan="' . $this->installments . '" style="text-align: center;">Taxas (MDR)</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<th colspan="' . $this->installments . '">Débito</th>
								</tr>
								<tr>
									<td>
										<input type="text" name="' . esc_attr( $debit_tax ) . '" value="' . $debit_tax_value . '" class="widefat" />
									</td>
									<td colspan="' . ( $this->installments - 1 ) . '">
										&nbsp;
									</td>
								</tr>
								<tr>
									<th colspan="' . $this->installments . '">Crédito</th>
								</tr>
								<tr>
				';
			
			for( $i = 0; $i < $this->installments; $i++ ) {
				echo '
									<th style="text-align: center;">
										' . ($i+1) . 'x
									</th>
					';
			}

			echo '
								</tr>
								<tr>
				';
			
			for( $i = 0; $i < $this->installments; $i++ ) {
				$field_name = $this->field['name'] . '[installments][' . ($i+1) . ']'. $this->field['name_suffix'];
				$field_value = ( isset( $args['installments'][ ($i+1) ] ) ) ? $args['installments'][ ($i+1) ] : '';
				echo '
									<td>
										<input type="text" name="' . esc_attr( $field_name ) . '" value="' . $field_value . '" class="widefat" />
									</td>
						';
			}

			echo '
								</tr>
							</tbody>
						</table>
						<hr>
					</div>
				';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
			wp_enqueue_script(
				'redux-field-credit-card-installments-js',
				PLUGIN_CUBO9_BRASPAG_URL . 'redux-components/credit-card-installments/credit-card-installments.js', // . Redux_Functions::is_min() . '.js',
				array( 'jquery', 'redux-js' ),
				$this->timestamp,
				true
			);

			wp_enqueue_style(
				'redux-field-credit-card-installments-css',
				PLUGIN_CUBO9_BRASPAG_URL . 'redux-components/credit-card-installments/credit-card-installments.css',
				array(),
				$this->timestamp,
				'all'
			);
		}
	}
}

class_alias( 'C9_Redux_Credit_Card_Installments', 'ReduxFramework_Credit_Card_Installments' );

