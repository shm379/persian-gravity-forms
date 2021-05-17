<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Payments extends GFPersian_Core {

	public function __construct() {

		if ( $this->option( 'payments', '1' ) != '1' ) {
			return;
		}

		$version = self::get_gform_version();
		if ( version_compare( $version, '2.3-dev-1', '>=' ) ) {
			add_filter( 'gform_is_duplicate', array( $this, 'better_noDuplicate_2_3_dev_1' ), 10, 4 );
		} elseif ( class_exists( 'GFParsi' ) ) {
			GFParsi::better_noDuplicate_hook( $version );
		}

		add_filter( 'gform_payment_status', array( $this, 'payment_status_entry' ) );
		add_action( 'gform_entries_first_column', array( $this, 'payment_detail_entries' ), 10, 5 );
		add_filter( 'admin_print_footer_scripts', array( $this, 'payment_status_conditional_logic' ) );

		add_action( 'gform_notification_ui_settings', array( $this, 'payment_status_hint' ), 10, 3 );
		add_action( 'gform_confirmation_ui_settings', array( $this, 'payment_status_hint' ), 10, 3 );

		add_filter( 'gform_entry_meta', array( $this, 'gform_entry_meta' ) );
		add_filter( 'gform_is_value_match', array( $this, 'is_value_match' ), 10, 6 );
		add_action( 'gf_gateway_js', array( $this, 'prev_gateways_chart_js' ) );
	}

	public static function _payment_status( $entry, $only_name = false, $status = '' ) {

		$status = ! empty( $status ) ? $status : rgar( $entry, 'payment_status' );

		$status = ucfirst( $status );

		if ( in_array( $status, array( 'Completed', 'Paid', 'Active', 'Actived', 'Approved' ) ) ) {
			$status = array( '#3d804c', 'موفق' );

		} elseif ( $status == 'Failed' ) {
			$status = array( '#ff4b44', 'ناموفق' );

		} elseif ( $status == 'Cancelled' ) {
			$status = array( '#FFA500', 'منصرف شده' );

		} elseif ( ! empty( $status ) ) {
			$status = array( '#3399FF', 'در انتظار پرداخت' );

		} else {
			$status = array();
		}

		if ( empty( $status[0] ) || empty( $status[1] ) ) {
			return '';
		}

		if ( $only_name ) {
			return $status[1];
		}

		return "<span style='color: {$status[0]}'>{$status[1]}</span>";
	}

	public function payment_status_entry( $status ) {

		if ( GFCommon::is_entry_detail() ) {
			$status = self::_payment_status( '', false, $status );
		}

		return $status;
	}

	public function payment_detail_entries( $form_id, $field_id, $value, $entry, $query_string ) {

		$url  = remove_query_arg( array( 's', 'field_id', 'operator' ) );
		$urls = array();

		$status = self::_payment_status( $entry );
		if ( ! empty( $status ) ) {

			$url = add_query_arg( array(
				's'        => ucfirst( rgar( $entry, 'payment_status' ) ),
				'field_id' => 'payment_status',
				'operator' => 'is'
			), $url );

			$urls[] = '<a href="' . $url . '"> ' . $status . ' </a>';
		}

		$gateway = gform_get_meta( rgar( $entry, 'id' ), 'payment_gateway' );
		if ( ! empty( $gateway ) ) {
			$url    = add_query_arg( array(
				's'        => $gateway,
				'field_id' => 'payment_gateway',
				'operator' => 'is'
			), $url );
			$urls[] = '<a href="' . $url . '" style="color:black"> ' . $gateway . ' </a>';
		}

		if ( ! empty( $urls ) ) {
			echo implode( ' - ', $urls );
		}
	}

	public function better_noDuplicate_2_3_dev_1( $count, $form_id, $field, $value ) {

		global $wpdb;

		$entry_meta_table_name = GFFormsModel::get_entry_meta_table_name();
		$entry_table_name      = GFFormsModel::get_entry_table_name();

		switch ( GFFormsModel::get_input_type( $field ) ) {
			case 'time':
				$value = sprintf( "%02d:%02d %s", $value[0], $value[1], $value[2] );
				break;
			case 'date':
				$value = GFFormsModel::prepare_date( $field->dateFormat, $value );
				break;
			case 'number':
				$value = GFCommon::clean_number( $value, $field->numberFormat );
				break;
			case 'phone':
				$value          = str_replace( array( ')', '(', '-', ' ' ), '', $value );
				$sql_comparison = 'replace( replace( replace( replace( ld.value, ")", "" ), "(", "" ), "-", "" ), " ", "" ) = %s';
				break;
			case 'email':
				$value = is_array( $value ) ? rgar( $value, 0 ) : $value;
				break;
		}

		$inner_sql_template = "SELECT %s as input, ld.entry_id
                                FROM {$entry_meta_table_name} ld
                                INNER JOIN {$entry_table_name} l ON l.id = ld.entry_id\n";


		$inner_sql_template .= "WHERE l.form_id=%d AND ld.form_id=%d
                                AND ld.meta_key = %s
                                AND (payment_status IS NULL OR LOWER(payment_status) IN ('', 'approved', 'approve', 'completed', 'complete', 'actived', 'active', 'paid'))
                                AND status='active' AND ld.meta_value = %s";

		$sql = "SELECT count(distinct input) as match_count FROM ( ";

		$input_count = 1;
		if ( is_array( $field->get_entry_inputs() ) ) {
			$input_count = sizeof( $field->inputs );
			$inner_sql   = '';
			foreach ( $field->inputs as $input ) {
				$union     = empty( $inner_sql ) ? '' : ' UNION ALL ';
				$inner_sql .= $union . $wpdb->prepare( $inner_sql_template, $input['id'], $form_id, $form_id, $input['id'], $value[ $input['id'] ] );
			}
		} else {
			$inner_sql = $wpdb->prepare( $inner_sql_template, $field->id, $form_id, $form_id, $field->id, $value );
		}

		$sql .= $inner_sql . "
                ) as count
                GROUP BY entry_id
                ORDER BY match_count DESC";

		$count = gf_apply_filters( array(
			'gform_is_duplicate_better',
			$form_id
		), $wpdb->get_var( $sql ), $form_id, $field, $value );

		return $count != null && $count >= $input_count;
	}

	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/

	public function payment_status_hint( $ui_settings, $notif_confirm, $form ) {

		$is_default = rgar( $notif_confirm, 'isDefault' );
		if ( ! empty( $is_default ) ) {
			return $ui_settings;
		}

		$current_action = current_action();
		if ( stripos( $current_action, 'confirmation' ) !== false ) {
			$current_action = 'تاییدیه';
		} else {
			$current_action = 'اعلان';
		}

		$ui_settings['payment_status_hint'] = '
                <tr>
                    <th><label for="stickylist_confirmation_type">وضعیت پرداخت</label></th>
                    <td>' . sprintf( 'برای محدود کردن این %s به وضعیت پرداخت های مورد نظر، از طریق منطق شرطی بالا وضعیت پرداخت مورد نظر را ست نمایید. توجه نمایید که همه درگاه های پرداخت از وضعیت "منصرف شده" پشتیبانی نمیکنند. ضمن اینکه نسخه درگاه پرداخت ایرانی شما باید حداقل 2.3 باشد.', $current_action ) . '</td>
                </tr>';

		return $ui_settings;
	}

	public function payment_status_conditional_logic() {

		if ( ! $this->is_gravity_page() || ! in_array( rgget( 'subview' ), array( 'confirmation', 'notification' ) ) ) {
			return;
		}
		?>

        <script type="text/javascript">
            if (window.gform) {
                gform.addFilter('gform_conditional_logic_fields', function (options, form, selectedFieldId) {
                    options.push({
                        label: 'وضعیت پرداخت',
                        value: 'payment_status'
                    });
                    return options;
                });
                jQuery(document).ready(function ($) {
                    var $InputsRefreshTimeout = false;
                    var baseSelector = '.gf_conditional_logic_rules_container';
                    $(document).bind('gform_load_field_settings', function (event, field) {
                        $(document).find(baseSelector + ' select[id*="_rule_field_"]').each(function () {
                            ConditionalLogicPaymentStatus($(this));
                        });
                    });
                    $(document).on('change', baseSelector + ' select[id*="_rule_field_"]', function () {
                        ConditionalLogicPaymentStatus($(this));
                    });
                    $(document).on('change', baseSelector + ' select[id*="_rule_operator_"]', function () {
                        ConditionalLogicPaymentStatus($(this).prev('select'));
                    });
                    $(document).on('change', baseSelector + ' .add_field_choice', function () {
                        delayedRefreshPaymentStatusInputs();
                    });
                    gform.addFilter('gform_conditional_logic_values_input', function (fields) {
                        delayedRefreshPaymentStatusInputs();
                        return fields;
                    });

                    function delayedRefreshPaymentStatusInputs() {
                        $InputsRefreshTimeout = setTimeout(function () {
                            $(document).find(baseSelector + ' select[id*="_rule_field_"]').each(function () {
                                ConditionalLogicPaymentStatus($(this));
                            });
                        }, 10);
                    }

                    function ConditionalLogicPaymentStatus($select) {
                        var value = $select.val();
                        if (value == 'payment_status') {
                            var $_input,
                                bits = $select.attr('id').split('_'),
                                index = bits[bits.length - 1],
                                prep = bits.indexOf('rule'),
                                prefix = bits.slice(0, prep).join('_'),
                                $input = $('#' + prefix + '_rule_value_' + index),
                                $operator = $('#' + prefix + '_rule_operator_' + index);
                            $operator.find(":not(option[value^='is'])").each(function () {
                                $(this).remove();
                            });
                            var input_value, input_name, input_id;
                            input_value = 'completed';
                            if (typeof $input.val() != 'undefined' && $input.val().length)
                                input_value = $input.val();
                            if (typeof $input.attr('id') != 'undefined')
                                input_id = $input.attr('id');
                            else
                                input_id = $operator.attr('id').replace('operator', 'value');
                            if (typeof $input.attr('name') != 'undefined')
                                input_name = $input.attr('name');
                            else
                                input_name = input_id;
                            var options = '<option value="completed">موفق</option>';
                            options += '<option value="failed">ناموفق</option>';
                            options += '<option value="cancelled">منصرف شده</option>';
                            options = options.replace(/ selected="selected"/g, '');
                            options = options.replace("value=\"" + input_value + "\"", "value=\"" + input_value + "\" selected=\"selected\"");
                            if (typeof $input[0] == 'undefined') {
                                $operator.after('<input id="paymetn_statuses_temp"/>');
                                $_input = $('#paymetn_statuses_temp')[0];
                            } else {
                                $_input = $input[0];
                            }
                            $_input.outerHTML = "<select value='" + input_value + "' id='" + input_id + "' name='" + input_name + "'  class='gfield_rule_select gfield_rule_value_dropdown'>" + options + "</select>";
                        }
                    }

                    delayedRefreshPaymentStatusInputs();
                });
            }
        </script>
		<?php
	}

	public function gform_entry_meta( $metas ) {

		if ( empty( $metas['payment_status'] ) ) {
			$metas['payment_status'] = array(
				'label' => esc_html__( 'Payment Status', 'gravityforms' ),
				'type'  => 'payment_status'
			);
		}

		return $metas;
	}

	public function is_value_match( $is_match, $field_value/*entry['payment_status']*/, $rule_value, $rule_operator, $source_field = null, $rule ) {

		$fieldId = rgar( $rule, 'fieldId' );
		if ( empty( $fieldId ) ) {
			/*یه باگ مسخره توی گرویتی فرم ۱٫۹ بود*/
			$fieldId = rgar( $source_field, 'fieldId' );
		}

		if ( $fieldId == 'payment_status' ) {

			$field_value = strtolower( $field_value );

			if ( in_array( $field_value, array( 'completed', 'paid', 'active', 'actived', 'approved' ) ) ) {
				$field_value = 'completed';
			}

			$rule_value = ! empty( $rule_value ) ? $rule_value : 'completed';

			remove_filter( 'gform_is_value_match', array( $this, __FUNCTION__ ) );
			$is_match = GFFormsModel::is_value_match( $field_value, $rule_value, $rule_operator );
			add_filter( 'gform_is_value_match', array( $this, __FUNCTION__ ), 10, 6 );
		}

		return $is_match;
	}
	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/
	/*-----------------------------------------------------------------------------------*/
	public static function notification( $form, $entry ) {
		$entry          = self::get_entry( rgar( $entry, 'id' ) );
		$notifications  = GFCommon::get_notifications_to_send( 'form_submission', $form, $entry );
		$_notifications = array();
		foreach ( (array) $notifications as $notification ) {
			$logic = rgar( $notification, 'conditionalLogic' );
			$rules = rgar( $logic, 'rules' );
			if ( empty( $rules ) ) {
				continue;
			}
			$fieldIds = wp_list_pluck( $rules, 'fieldId' );
			if ( in_array( 'payment_status', $fieldIds ) ) {
				$_notifications[] = $notification;
			}
		}
		if ( ! empty( $_notifications ) ) {
			$_notifications = wp_list_pluck( $_notifications, 'id' );
			GFCommon::send_notifications( $_notifications, $form, $entry );
		}
	}

	public static function confirmation( $form, $entry, $fault ) {

		if ( ! class_exists( "GFFormDisplay" ) ) {
			require_once( GFCommon::get_base_path() . "/form_display.php" );
		}

		$entry        = self::get_entry( rgar( $entry, 'id' ) );
		$confirmation = GFFormDisplay::handle_confirmation( $form, $entry );
		if ( is_array( $confirmation ) && isset( $confirmation["redirect"] ) ) {
			header( "Location: {$confirmation["redirect"]}" );
			exit;
		}
		$fault                                    = ! empty( $fault ) ? $fault : '';
		$confirmation                             = str_ireplace( '{fault}', $fault, $confirmation );
		GFFormDisplay::$submission[ $form['id'] ] = array(
			"is_confirmation"      => true,
			"confirmation_message" => $confirmation,
			"form"                 => $form,
			"entry"                => $entry,
			"lead"                 => $entry,
			"page_number"          => 1
		);
	}

	public static function currency( $entry = '', $form = '' ) {

		if ( is_numeric( $entry ) ) {
			$entry = self::get_entry( $entry );
		}

		if ( rgar( $entry, 'currency' ) ) {
			return rgar( $entry, 'currency' );
		}

		if ( is_numeric( $form ) ) {
			$form = GFAPI::get_form( $form );
		}

		if ( rgar( $form, 'currency' ) ) {
			return rgar( $form, 'currency' );
		}

		return GFCommon::get_currency();
	}

	public static function amount( $amount, $to_currency = 'IRR', $entry = '', $form = '' ) {

		$currency = self::currency( $entry, $form );

		if ( in_array( $currency, array( 'IRHR', 'IRHT' ) ) ) {
			$currency = str_ireplace( 'H', '', $currency );
			$amount   *= 1000;
		}

		if ( $currency == 'IRR' && $to_currency == 'IRT' ) {
			$amount /= 10;
		}

		if ( $currency == 'IRT' && $to_currency == 'IRR' ) {
			$amount *= 10;
		}

		return $amount;
	}

	public static function check_verification( $entry, $gateway, $params ) {

		if ( empty( $params ) || trim( $params ) == '' ) {
			return false;
		}

		$params = self::params_verification( $gateway, $params );

		$table_name = '';
		if ( method_exists( 'GFFormsModel', 'get_lead_meta_table_name' ) ) {
			$table_name = GFFormsModel::get_lead_meta_table_name();
		}
		if ( version_compare( self::get_gform_version(), '2.3-dev-1', '>=' ) ) {
			if ( method_exists( 'GFFormsModel', 'get_entry_meta_table_name' ) ) {
				$table_name = GFFormsModel::get_entry_meta_table_name();
			}
		}

		if ( ! empty( $table_name ) ) {

			global $wpdb;
			$check = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE meta_key='_verification_params' AND meta_value='%s'", $params ) );

			if ( ! empty( $check ) ) {

				if ( is_numeric( $entry ) ) {
					$entry = self::get_entry( $entry );
				}

				$entry["payment_date"]   = gmdate( "Y-m-d H:i:s" );
				$entry["payment_status"] = "Failed";
				$entry["payment_amount"] = 0;
				$entry["is_fulfilled"]   = 0;
				GFAPI::update_entry( $entry );

				global $current_user;
				$user_id   = 0;
				$user_name = 'مهمان';
				if ( $current_user && $user_data = get_userdata( $current_user->ID ) ) {
					$user_id   = $current_user->ID;
					$user_name = $user_data->display_name;
				}

				$Message = 'تراکنش ناموفق ::: نتیجه تراکنش قبلا مشخص شده بود و تراکنش دوباره تکرار شد.';

				RGFormsModel::add_note( $entry["id"], $user_id, $user_name, $Message );

				$form = GFAPI::get_form( rgar( $entry, 'form_id' ) );

				self::notification( $form, $entry );
				self::confirmation( $form, $entry, $Message );

				return true;
			}
		}

		return false;
	}

	public static function set_verification( $entry, $gateway, $params ) {

		if ( empty( $params ) || trim( $params ) == '' ) {
			return false;
		}

		if ( ! is_numeric( $entry ) ) {
			$entry = rgar( $entry, 'id' );
		}

		$params = self::params_verification( $gateway, $params );

		gform_update_meta( $entry, '_verification_params', $params );
	}

	private static function params_verification( $gateway, $params ) {
		if ( is_array( $params ) || is_object( $params ) ) {
			$params = implode( '_', (array) $params );
		}

		return trim( $gateway . '_' . $params );
	}

	private static function get_gform_version() {
		$version = GFCommon::$version;
		if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
			$version = GFFormsModel::get_database_version();
		}

		return $version;
	}

	public static function transaction_id( $entry ) {
		return GFPersian_Transaction_ID::create_transaction_id( $entry, 'return' );
	}

	public static function nusoap() {
		if ( ! class_exists( 'nusoap_client' ) ) {
			require_once 'lib/nusoap.php';
		}
	}

	public function prev_gateways_chart_js() {

		wp_dequeue_script( 'jquery-ui-jdatepicker' );
		wp_deregister_script( 'jquery-ui-jdatepicker' );

		wp_register_script( 'jquery-ui-jdatepicker', GFPersian_Payments::get_base_url() . '/assets/js/jalali-datepicker.js', array(
			'jquery',
			'jquery-migrate',
			'jquery-ui-core',
		), GFCommon::$version, true );
		wp_enqueue_script( 'jquery-ui-jdatepicker' );

	}

	public static function fix_mobile( $Mobile = '' ) {

		if ( empty( $Mobile ) ) {
			return '';
		}

		$Mobile = str_ireplace( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $Mobile ); //farsi

		$Mobile = str_ireplace( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ),
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $Mobile ); //arabi

		$Mobile = preg_replace( '/\D/is', '', $Mobile );
		$Mobile = ltrim( $Mobile, '0' );
		$Mobile = substr( $Mobile, 0, 2 ) == '98' ? substr( $Mobile, 2 ) : $Mobile;

		return '0' . $Mobile;
	}
}

new GFPersian_Payments;