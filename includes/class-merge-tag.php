<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Merge_Tags extends GFPersian_Core {

	private $entry_time = 0;
	private static $_entry = null;
	private static $_virual_entry = null;

	public function __construct() {

		if ( $this->option( 'add_merge_tags', '1' ) == '1' ) {

			add_filter( 'gform_admin_pre_render', array( $this, 'merge_tags_keys' ) );
			add_filter( 'gform_replace_merge_tags', array( $this, 'merge_tags_values' ), 999, 7 );
			/*------------------------------------------------------------*/
			//todo:enable for next updates if was needed
			if ( apply_filters( 'enable_subtotal_merge_tag', false ) ) {
				add_filter( 'gform_pre_render', array( $this, 'maybe_replace_subtotal_merge_tag' ) );
				add_filter( 'gform_pre_validation', array( $this, 'maybe_replace_subtotal_merge_tag' ), 10, 1 );
			}
		}

		if ( $this->option( 'post_content_merge_tags', '1' ) == '1' ) {

			$entry_time = $this->option( 'entry_time', '0' );
			if ( intval( $entry_time ) > 0 ) {
				$this->entry_time = intval( $entry_time ) * 60;
			}

			add_filter( 'the_content', array( $this, 'merge_tags_values_post_content' ), 1 );
			add_filter( 'gform_confirmation', array( $this, 'confirmation_append_entry' ), 20, 3 );
		}

		if ( $this->option( 'pre_submission_merge_tags', '1' ) == '1' ) {
			add_filter( 'gform_pre_render', array( $this, 'merge_tags_pre_submission' ) );
		}
	}

	/*-------------------------------------------------------------*/
	/*--------Start of Persian Gravity Merge Tags------------------*/
	/*-------------------------------------------------------------*/
	public static function get_merge_tags( $form ) {
		$merge_tags = array(
			'{rtl_start}'             => 'ابتدای راستچین سازی',
			'{rtl_end}'               => 'انتهای راستچین سازی',
			'{transaction_id}'        => __( 'Transaction Id', 'gravityforms' ),
			'{transaction_id_table}'  => sprintf( 'جدول %s', __( 'Transaction Id', 'gravityforms' ) ),
			'{payment_gateway}'       => 'عنوان درگاه پرداخت',
			'{payment_gateway_table}' => 'جدول درگاه پرداخت',
			'{payment_status}'        => 'عنوان وضعیت پرداخت',
			'{payment_status_table}'  => 'جدول وضعیت پرداخت',
			'{payment_table}'         => sprintf( 'جدول پرداخت (وضعیت - نام درگاه - %s)', __( 'Transaction Id', 'gravityforms' ) ),
		);

		if ( GFCommon::has_post_field( rgar( $form, 'fields' ) ) ) {
			$merge_tags['{post_permalink}'] = 'لینک پست';
		}

		return $merge_tags;
	}

	public function merge_tags_keys( $form ) {

		if ( GFCommon::is_entry_detail() ) {
			return $form;
		}
		?>

        <script type="text/javascript">
            gform.addFilter('gform_merge_tags', function (mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option) {
                mergeTags['gf_persian'] = {
                    label: 'گرویتی فرم پارسی',
                    tags: []
                };
				<?php foreach ( self::get_merge_tags( $form ) as $key => $val ) { ?>
                mergeTags['gf_persian'].tags.push({tag: '<?php echo $key ?>', label: '<?php echo $val ?>'});
				<?php } ?>
                return mergeTags;
            });

			<?php
			/*todo:enable for next updates if was needed*/
			if ( apply_filters( 'enable_subtotal_merge_tag', false ) ) :?>
            jQuery(document).ready(function ($) {
                $('#field_calculation_formula_variable_select').find('optgroup').eq(0).append('<option value="{subtotal}">مجموع قیمت ها</option>');
            });
			<?php endif; ?>
        </script>
		<?php
		return $form;
	}

	public function merge_tags_values( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		//supprots deprecated merge tags
		$deprecated_tags = array(
			'{payment_pack}'        => '{payment_table}',
			'{payment_status_css}'  => '{payment_status_table}',
			'{transaction_id_css}'  => '{transaction_id_table}',
			'{payment_gateway_css}' => '{payment_gateway_table}',
		);

		$text = str_ireplace( array_keys( $deprecated_tags ), array_values( $deprecated_tags ), $text );

		$entry           = self::get_entry( rgar( $entry, 'id' ) );
		$transaction_id  = rgar( $entry, 'transaction_id' );
		$payment_status  = GFPersian_Payments::_payment_status( $entry );
		$payment_gateway = gform_get_meta( rgar( $entry, 'id' ), 'payment_gateway' );

		$merge_tags = array(
			'{transaction_id}'  => $transaction_id,
			'{payment_gateway}' => $payment_gateway,
			'{payment_status}'  => strip_tags( $payment_status ),
		);

		$tabled_tags = array(
			'{payment_status_table}'  => array( 'وضعیت پرداخت', $payment_status ),
			'{payment_gateway_table}' => array( 'درگاه پرداخت', $payment_gateway ),
			'{transaction_id_table}'  => array( __( 'Transaction Id', 'gravityforms' ), $transaction_id ),
		);

		foreach ( $tabled_tags as $tag => $value ) {

			if ( empty( $value[1] ) ) {
				$merge_tags[ $tag ] = '';
				continue;
			}
			ob_start(); ?>
            <tr bgcolor="<?php echo esc_attr( apply_filters( 'gform_email_background_color_label', '#EAF2FA', $tag, $entry ) ); ?>">
                <td colspan="2" style="padding:5px !important">
                    <font style="font-family: sans-serif; font-size:12px;"><strong><?php echo $value[0]; ?></strong></font>
                </td>
            </tr>
            <tr bgcolor="#FFFFFF">
                <td width="20">&nbsp;</td>
                <td style="padding:5px !important">
                    <font style="font-family:sans-serif;font-size:12px"><?php echo $value[1]; ?></font>
                </td>
            </tr>
			<?php
			$merge_tags[ $tag ] = ob_get_clean();
		}
		$merge_tags['{payment_table}'] = $merge_tags['{payment_status_table}'] . $merge_tags['{payment_gateway_table}'] . $merge_tags['{transaction_id_table}'];

		foreach ( $merge_tags as $key => $value ) {
			if ( ! empty( $value ) && stripos( $key, '_table}' ) !== false ) {
				$merge_tags[ $key ] = '
                <table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
                    <tr>
                        <td>
                            <table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">' . $merge_tags[ $key ] . '</table>
                        </td>
                   </tr>
               </table>';
			}
		}

		$merge_tags = array_merge( $merge_tags, array(
			'{rtl_start}'      => '<div style="text-align: right !important; direction: rtl !important;">',
			'{rtl_end}'        => '</div>',
			'{post_permalink}' => rgar( $entry, 'post_id' ) ? get_permalink( rgar( $entry, 'post_id' ) ) : '',
		) );

		return str_replace( array_keys( $merge_tags ), array_values( $merge_tags ), $text );
	}
	/*-------------------------------------------------------------*/
	/*--------End of Persian Gravity Merge Tags--------------------*/
	/*-------------------------------------------------------------*/


	/*-------------------------------------------------------------*/
	/*--------Start of Subtotal Merge Tags-------------------------*/
	/*-------------------------------------------------------------*/
	public function maybe_replace_subtotal_merge_tag( $form, $filter_tags = true ) {

		foreach ( $form['fields'] as &$field ) {

			if ( current_filter() == 'gform_pre_render' ) {
				$filter_tags = false;
				if ( rgar( $field, 'origCalculationFormula' ) ) {
					$field['calculationFormula'] = $field['origCalculationFormula'];
				}
			}

			if ( ! $this->has_subtotal_merge_tag( $field ) ) {
				continue;
			}

			$subtotal_merge_tags             = $this->get_subtotal_merge_tag_string( $form, $field, $filter_tags );
			$field['origCalculationFormula'] = $field['calculationFormula'];
			$field['calculationFormula']     = str_replace( '{subtotal}', $subtotal_merge_tags, $field['calculationFormula'] );
		}

		return $form;
	}

	public function get_subtotal_merge_tag_string( $form, $current_field, $filter_tags = false ) {

		$product_fields = array();

		foreach ( $form["fields"] as $field ) {

			if ( ! in_array( $field["type"], array( 'product', 'shopping_cart' ) ) ) {
				continue;
			}

			switch ( $field["type"] ) {
				case 'product':
					$option_fields = GFCommon::get_product_fields_by_type( $form, array( "option" ), $field['id'] );
					// can only have 1 quantity field
					$quantity_field   = GFCommon::get_product_fields_by_type( $form, array( "quantity" ), $field['id'] );
					$quantity_field   = rgar( $quantity_field, 0 );
					$product_fields[] = array(
						'product'  => $field,
						'options'  => $option_fields,
						'quantity' => $quantity_field
					);
					break;

				case 'shopping_cart':
					//todo : for next update
					break;
			}
		}
		$shipping_field = GFCommon::get_fields_by_type( $form, array( "shipping" ) );
		$pricing_fields = array( "products" => $product_fields, "shipping" => $shipping_field );

		$product_tag_groups = array();
		foreach ( $pricing_fields['products'] as $product ) {

			$product_field  = rgar( $product, 'product' );
			$option_fields  = rgar( $product, 'options' );
			$quantity_field = rgar( $product, 'quantity' );

			// do not include current field in subtotal
			if ( $product_field['id'] == $current_field['id'] ) {
				continue;
			}

			$product_tags = GFCommon::get_field_merge_tags( $product_field );
			$quantity_tag = 1;

			// if a single product type, only get the "price" merge tag
			if ( in_array( GFFormsModel::get_input_type( $product_field ), array(
				'singleproduct',
				'calculation',
				'hiddenproduct'
			) ) ) {

				// single products provide quantity merge tag
				if ( empty( $quantity_field ) && ! rgar( $product_field, 'disableQuantity' ) ) {
					$quantity_tag = $product_tags[2]['tag'];
				}

				$product_tags = array( $product_tags[1] );
			}

			// if quantity field is provided for product, get merge tag
			if ( ! empty( $quantity_field ) ) {
				$quantity_tag = GFCommon::get_field_merge_tags( $quantity_field );
				$quantity_tag = $quantity_tag[0]['tag'];
			}


			if ( is_numeric( $quantity_tag ) ) {
				$qty_value = $quantity_tag;
			} else {
				// extract qty input ID from the merge tag
				preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $quantity_tag, $matches, PREG_SET_ORDER );
				$qty_input_id = rgars( $matches, '0/1' );
				$qty_value    = rgpost( 'input_' . str_replace( '.', '_', $qty_input_id ) );
			}

			if ( $filter_tags && floatval( $qty_value ) <= 0 ) {
				continue;
			}

			$product_tags = wp_list_pluck( $product_tags, 'tag' );
			$option_tags  = array();

			foreach ( $option_fields as $option_field ) {

				if ( is_array( $option_field['inputs'] ) ) {

					$choice_number = 1;

					foreach ( $option_field['inputs'] as &$input ) {

						//hack to skip numbers ending in 0. so that 5.1 doesn't conflict with 5.10
						if ( $choice_number % 10 == 0 ) {
							$choice_number ++;
						}

						$input['id'] = $option_field['id'] . '.' . $choice_number ++;

					}
				}

				$new_options_tags = GFCommon::get_field_merge_tags( $option_field );
				if ( ! is_array( $new_options_tags ) ) {
					continue;
				}

				if ( GFFormsModel::get_input_type( $option_field ) == 'checkbox' ) {
					array_shift( $new_options_tags );
				}

				$option_tags = array_merge( $option_tags, $new_options_tags );
			}

			$option_tags = wp_list_pluck( $option_tags, 'tag' );

			$product_tag_groups[] = '( ( ' . implode( ' + ', array_merge( $product_tags, $option_tags ) ) . ' ) * ' . $quantity_tag . ' )';

		}

		$shipping_tag = 0;
		//Shipping should not be included in subtotal, correct?
		if ( rgar( $pricing_fields, 'shipping' ) ) {
			$shipping_tag = GFCommon::get_field_merge_tags( rgars( $pricing_fields, 'shipping/0' ) );
			$shipping_tag = $shipping_tag[0]['tag'];
		}

		$subtotal_merge_tags = '( ( ' . implode( ' + ', $product_tag_groups ) . ' ) + ' . $shipping_tag . ' )';

		return $subtotal_merge_tags;
	}

	public function has_subtotal_merge_tag( $field ) {

		// check if form is passed
		if ( isset( $field['fields'] ) ) {

			$form = $field;
			foreach ( $form['fields'] as $field ) {
				if ( $this->has_subtotal_merge_tag( $field ) ) {
					return true;
				}
			}

		} else {

			if ( isset( $field['calculationFormula'] ) && strpos( $field['calculationFormula'], '{subtotal}' ) !== false ) {
				return true;
			}

		}

		return false;
	}
	/*-------------------------------------------------------------*/
	/*--------End of Subtotal Merge Tags---------------------------*/
	/*-------------------------------------------------------------*/


	/*-------------------------------------------------------------*/
	/*--------Start of Post Content Merge Tags---------------------*/
	/*-------------------------------------------------------------*/
	public function merge_tags_values_post_content( $post_content ) {

		$entry_time = $this->entry_time;

		if ( ! self::$_entry ) {

			$entry_id = rgget( 'entry' );

			if ( $entry_id ) {
				if ( ( ! $entry_time ) && ( ! is_numeric( $entry_id ) || intval( $entry_id ) <= 0 ) ) {

					if ( method_exists( 'GFCommon', 'openssl_decrypt' ) ) {
						$entry_id = GFCommon::openssl_decrypt( strval( $entry_id ) );
					} elseif ( method_exists( 'GFCommon', 'decrypt' ) ) {
						$entry_id = GFCommon::decrypt( strval( $entry_id ) );
					}
					$entry_id = intval( $entry_id );
				}
			} else {
				$post = get_post();
				if ( $post ) {
					$entry_id = get_post_meta( $post->ID, '_gform-entry-id', true );
				}
			}

			if ( $entry_id ) {
				$entry = self::get_entry( $entry_id );
			}

			self::$_entry = ! empty( $entry ) && $entry ? $entry : false;
		}
		$entry = self::$_entry;

		if ( ! $entry ) {
			$post_content = $this->replace_field_label_merge_tags( $post_content, '' );
		} else {

			if ( $entry_time ) {
				$confirm_time = gform_get_meta( rgar( $entry, 'id' ), 'gform_page_confirm_time' );
				if ( empty( $confirm_time ) ) {
					gform_update_meta( $entry['id'], 'gform_page_confirm_time', time() );
				} elseif ( $confirm_time + $entry_time < time() ) {
					return $this->replace_field_label_merge_tags( $post_content, '' );
				}
			}

			$form         = GFFormsModel::get_form_meta( $entry['form_id'] );
			$post_content = $this->replace_field_label_merge_tags( $post_content, $form );
			$post_content = GFCommon::replace_variables( $post_content, $form, $entry, false, false, false );
		}

		return $post_content;
	}

	public function replace_field_label_merge_tags( $text, $form ) {

		if ( ! empty( $form ) ) {

			preg_match_all( '/{([^:]+?)}/', $text, $matches, PREG_SET_ORDER );
			if ( empty( $matches ) ) {
				return $text;
			}

			foreach ( $matches as $match ) {

				list( $search, $field_label ) = $match;

				foreach ( $form['fields'] as $field ) {

					$matches_admin_label = rgar( $field, 'adminLabel' ) == $field_label;
					$matches_field_label = false;

					if ( is_array( $field['inputs'] ) ) {
						foreach ( $field['inputs'] as $input ) {
							if ( GFFormsModel::get_label( $field, $input['id'] ) == $field_label ) {
								$matches_field_label = true;
								$input_id            = $input['id'];
								break;
							}
						}
					} else {
						$matches_field_label = GFFormsModel::get_label( $field ) == $field_label;
						$input_id            = $field['id'];
					}

					if ( ! $matches_admin_label && ! $matches_field_label ) {
						continue;
					}

					$replace = sprintf( '{%s:%s}', $field_label, (string) $input_id );
					$text    = str_replace( $search, $replace, $text );

					break;
				}
			}
		} else {

			/*
			preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER );
			if( !empty( $matches ) ) {
				foreach( $matches as $match ) {
					if ( isset($match[0]))
						$text = str_replace( $match[0], '' , $text );
				}
			}

			unset($matches);

			preg_match_all( '/{([^:]+?)}/', $text, $matches, PREG_SET_ORDER );
			if( !empty( $matches ) ) {
				foreach( $matches as $match ) {
					if ( isset($match[0]))
						$text = str_replace( $match[0], '' , $text );
				}
			}
			*/

		}

		return $text;
	}

	public function confirmation_append_entry( $confirmation, $form, $entry ) {

		$is_ajax_redirect = is_string( $confirmation ) && strpos( $confirmation, 'gformRedirect' );
		$is_redirect      = is_array( $confirmation ) && isset( $confirmation['redirect'] );

		if ( ! $is_ajax_redirect && ! $is_redirect ) {
			return $confirmation;
		}

		$entry_id = $entry['id'];

		if ( ! $this->entry_time ) {
			if ( method_exists( 'GFCommon', 'openssl_encrypt' ) ) {
				$entry_id = rawurlencode( GFCommon::openssl_encrypt( strval( $entry_id ) ) );
			} elseif ( method_exists( 'GFCommon', 'encrypt' ) ) {
				$entry_id = rawurlencode( GFCommon::encrypt( strval( $entry_id ) ) );
			}
		}

		if ( $is_ajax_redirect ) {
			preg_match_all( '/gformRedirect.+?(http.+?)(?=\'|")/', $confirmation, $matches, PREG_SET_ORDER );
			list( $full_match, $url ) = $matches[0];
			$redirect_url = add_query_arg( array( 'entry' => $entry_id ), $url );
			$confirmation = str_replace( $url, $redirect_url, $confirmation );
		} else {
			$redirect_url             = add_query_arg( array( 'entry' => $entry_id ), $confirmation['redirect'] );
			$confirmation['redirect'] = $redirect_url;
		}

		return $confirmation;
	}
	/*-------------------------------------------------------------*/
	/*--------End of Post Content Merge Tags-----------------------*/
	/*-------------------------------------------------------------*/


	/*-------------------------------------------------------------*/
	/*--------Start of Pre Submission Merge Tags-------------------*/
	/*-------------------------------------------------------------*/
	public function merge_tags_pre_submission( $form ) {

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return $form;
		}

		$current_page = isset( GFFormDisplay::$submission[ $form['id'] ] ) && isset( GFFormDisplay::$submission[ $form['id'] ]['page_number'] ) ? GFFormDisplay::$submission[ $form['id'] ]['page_number'] : 1;
		//$fields       = array();

		if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return $form;
		}

		// get all HTML fields on the current page
		foreach ( $form['fields'] as &$field ) {

			// skip all fields on the first page
			if ( rgar( $field, 'pageNumber' ) <= 1 ) {
				continue;
			}

			$default_value = rgar( $field, 'defaultValue' );
			preg_match_all( '/{.+}/', $default_value, $matches, PREG_SET_ORDER );
			if ( ! empty( $matches ) ) {
				// if default value needs to be replaced but is not on current page, wait until on the current page to replace it
				if ( rgar( $field, 'pageNumber' ) != $current_page ) {
					$field['defaultValue'] = '';
				} else {
					$field['defaultValue'] = $this->preview_replace_variables( $default_value, $form );
				}
			}

			// only run 'content' filter for fields on the current page
			if ( rgar( $field, 'pageNumber' ) != $current_page ) {
				continue;
			}

			$html_content = rgar( $field, 'content' );
			preg_match_all( '/{.+}/', $html_content, $matches, PREG_SET_ORDER );
			if ( ! empty( $matches ) ) {
				$field['content'] = $this->preview_replace_variables( $html_content, $form );
			}

		}

		return $form;
	}

	public function preview_special_merge_tags( $value, $input_id, $merge_tag, $field ) {

		// added to prevent overriding :noadmin filter (and other filters that remove fields)
		if ( ! $value ) {
			return $value;
		}

		$input_type = RGFormsModel::get_input_type( $field );

		$is_upload_field = in_array( $input_type, array( 'post_image', 'fileupload' ) );
		$is_multi_input  = is_array( rgar( $field, 'inputs' ) );
		$is_input        = intval( $input_id ) != $input_id;

		if ( ! $is_upload_field && ! $is_multi_input ) {
			return $value;
		}

		// if is individual input of multi-input field, return just that input value
		if ( $is_input ) {
			return $value;
		}

		$form     = RGFormsModel::get_form_meta( $field['formId'] );
		$entry    = $this->create_entry( $form );
		$currency = GFCommon::get_currency();

		if ( is_array( rgar( $field, 'inputs' ) ) ) {
			$value = RGFormsModel::get_lead_field_value( $entry, $field );

			return GFCommon::get_lead_field_display( $field, $value, $currency );
		}

		$input_name = "input_{$field['id']}";

		$file_info = RGFormsModel::get_temp_filename( $form['id'], $input_name );
		$source    = RGFormsModel::get_upload_url( $form['id'] ) . "/tmp/" . $file_info["temp_filename"];

		$value = '';
		if ( $file_info ) {
			switch ( RGFormsModel::get_input_type( $field ) ) {
				case "post_image":
					list( , $image_title, $image_caption, $image_description ) = explode( "|:|", $entry[ $field['id'] ] );
					$value = ! empty( $source ) ? $source . "|:|" . $image_title . "|:|" . $image_caption . "|:|" . $image_description : "";
					break;

				case "fileupload" :
					$value = $source;
					break;

			}
		}

		switch ( $input_type ) {
			case 'fileupload':

				if ( ! empty( $value ) ) {
					$input_name = "input_" . str_replace( '.', '_', $field['id'] );
					$file_info  = RGFormsModel::get_temp_filename( $form['id'], $input_name );
					$value      = esc_attr( str_replace( " ", "%20", $value ) );
					$value      = "<a href='$value' target='_blank' title='" . __( "Click to view", "gravityforms" ) . "'>" . $file_info['uploaded_filename'] . "</a>";
				}
				break;
			default:
				$value = GFCommon::get_lead_field_display( $field, $value, $currency );
				break;
		}

		return $value;
	}

	public function preview_replace_variables( $content, $form ) {

		$entry = $this->create_entry( $form );

		// add filter that will handle getting temporary URLs for file uploads and post image fields (removed below)
		// beware, the RGFormsModel::create_lead() function also triggers the gform_merge_tag_filter at some point and will
		// result in an infinite loop if not called first above
		add_filter( 'gform_merge_tag_filter', array( $this, 'preview_special_merge_tags' ), 10, 4 );

		$content = GFCommon::replace_variables( $content, $form, $entry, false, false, false );

		// remove filter so this function is not applied after preview functionality is complete
		remove_filter( 'gform_merge_tag_filter', array( $this, 'preview_special_merge_tags' ) );

		return $content;
	}

	public function create_entry( $form ) {

		if ( empty( self::$_virual_entry ) ) {
			self::$_virual_entry = GFFormsModel::create_lead( $form );
			if ( class_exists( 'GFCache' ) ) {
				foreach ( $form['fields'] as &$field ) {
					if ( GFFormsModel::get_input_type( $field ) == 'total' ) {
						GFCache::delete( 'GFFormsModel::get_lead_field_value__' . $field['id'] );
					}
				}
			}
		}

		return self::$_virual_entry;
	}
	/*-------------------------------------------------------------*/
	/*--------End of Pre Submission Merge Tags---------------------*/
	/*-------------------------------------------------------------*/

}

new GFPersian_Merge_Tags;