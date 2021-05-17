<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_National_ID extends GFPersian_Core {

	public function __construct() {

	    if ( $this->option( 'national_id', '1' ) != '1' ) {
			return;
		}

		if ( is_admin() ) {
			add_filter( 'gform_add_field_buttons', array( $this, 'button' ) );
			add_filter( 'gform_field_type_title', array( $this, 'title' ) );
			add_filter( 'gform_editor_js_set_default_values', array( $this, 'label' ) );
			add_action( 'gform_editor_js', array( $this, 'settings' ) );
			add_action( 'gform_field_standard_settings', array( $this, 'standard_settings' ), 10, 2 );
			add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
			add_filter( 'gform_admin_pre_render', array( $this, 'admin_conditional_logic' ) );
		}

		add_action( 'gform_field_input', array( $this, 'input' ), 10, 5 );
		add_action( 'gform_field_css_class', array( $this, 'classes' ), 10, 3 );
		add_action( 'gform_pre_submission', array( $this, 'pre_submission' ) );
		add_filter( 'gform_field_validation', array( $this, 'validator' ), 10, 4 );
		add_action( 'gform_register_init_scripts', array( $this, 'frontend_script' ), 10, 1 );
	}

	public function button( $field_groups ) {

		foreach ( $field_groups as &$group ) {
			if ( $group['name'] == 'gf_persian_fields' ) {
				$group['fields'][] = array(
					'class'     => 'button',
					'value'     => 'کد ملی',
					'data-type' => 'ir_national_id',
					//'onclick' => "StartAddField('ir_national_id');"//deprecated
				);
			}
		}

		return $field_groups;
	}

	public function title( $type ) {
		if ( $type == 'ir_national_id' ) {
			return 'کد ملی';
		}

		return $type;
	}

	public function label() { ?>
        case 'ir_national_id':
        field.label = 'کد ملی';
        break;
		<?php
	}

	public function classes( $classes, $field, $form ) {
		if ( $field['type'] == 'ir_national_id' ) {
			$classes .= ' gform_ir_national_id';
		}

		return $classes;
	}

	public function tooltips( $tooltips ) {
		$tooltips['tooltip_showLocation']   = '<h6>نمایش لحظه ای شهر از روی کد ملی </h6>نمایش شهر و پیغام زیر فیلد کد ملی بعد از پر شدن فیلد. تذکر : در صورتی که این گزینه را فعال نمایید ،ممکن است فراخوانی شهر های ایران با توجه به زیاد بودن آنها سبب سنگین شدن صفحه گردد.';
		$tooltips['tooltip_showSeperator']  = '<h6>جدا سازی ارقام</h6>در صورتی که این گزینه را فعال نمایید ، پس از پر شدن فیلد کد ملی ، <strong>در صورتی که کد ملی وارد شده صحیح تشخصی داده شود</strong> ؛ کد ملی به صورت زیر در خواهد آمد و در غیر این صورت علی صحیح نبودن کد ملی زیر فیلد نمایش داده خواهد شد :<br>xxx-xxxxxx-x';
		$tooltips['tooltip_forceEnglish']   = '<h6>تبدیل اعداد فارسی و عربی به انگلیسی</h6>در صورتی که کاربر کد ملی خود را به صورت انگلیسی وارد نکند با خطا مواجه خواهد شد. پس با فعالسازی این گزینه اعداد وارد شده برای کد ملی را به صورت خودکار به انگلیسی تبدیل کنید.';
		$tooltips['tooltip_notDigitError']  = 'با توجه به اینکه کد ملی فقط باید به صورت عدد باشد ، در صورتی که کاراکتری غیر از عدد وارد شده باشد پیغام خطا نمایش داده خواهد شد.<br/>پیغام پیشفرض : کد ملی فقط باید به صورت عدد وارد شود.';
		$tooltips['tooltip_qtyDigitError']  = 'با توجه به اینکه کد ملی می بایست 10 رقمی باشد اگر تعداد رقم وارد شده ، اشتباه باشد پیغام خطا نمایش داده خواهد شد.<br>پیغام پیشفرض : کد ملی می بایست 10 رقمی باشد. تنها در صورتی مجاز به استفاده از کد های 8 یا 9 رقمی هستید که ارقام سمت چپ 0 باشند.';
		$tooltips['tooltip_duplicateError'] = 'در صورتی که از تب وِیژگی تیک گزینه بدون تکرار را زده باشید ؛ بعد از پر شدن فرم و زدن دکمه ارسال پیغامی مبتنی بر تکراری بودن کد ملی نمایش داده خواهد شد. <br/>پیغام پیشفرض : این کد ملی توسط فرد دیگری ثبت شده است.';
		$tooltips['tooltip_isInvalidError'] = 'در صورتی که کد ملی وارد شده مطابق با الگوریتم کشور نباشد پیغام خطا نمایش داده خواهد شد.<br/>پیغام پیشفرض : کد ملی وارد شده مطابق با استانداردهای کشور نمی باشد.';

		return $tooltips;
	}

	public function input( $input, $field, $value, $entry_id, $form_id ) {

		if ( $field['type'] == 'ir_national_id' ) {

			$field_id        = $field['id'];
			$form_id         = ! empty( $form_id ) ? $form_id : rgget( 'id' );
			$is_admin        = is_admin();
			$is_frontend     = ! $is_admin;
			$is_entry_detail = GFCommon::is_entry_detail();
			$is_form_editor  = GFCommon::is_form_editor();

			if ( $is_frontend && RGFormsModel::get_input_type( $field ) == 'adminonly_hidden' ) {
				return '';
			}

			$size         = rgar( $field, 'size' );
			$class_suffix = $is_entry_detail ? '_admin' : '';
			$class        = $size . $class_suffix;

			$input_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$field_id" : 'input_' . $form_id . "_$field_id";

			$tabindex      = GFCommon::get_tabindex();
			$disabled_text = $is_form_editor ? "disabled='disabled'" : '';

			$max_length = rgar( $field, 'showSeperator' ) ? 12 : 10;
			$max_length = "maxlength='{$max_length}'";
			/*
			$this->get_conditional_logic_event( 'keyup' )  //text or radio
			$this->get_conditional_logic_event( 'change' ) //select
			$this->get_conditional_logic_event( 'click' )  // checkbox or radio
			//note : radio has keyup and click
            */

			$logic_event = ! $is_form_editor && ! $is_entry_detail ? $field->get_conditional_logic_event( 'keyup' ) : '';

			$placeholder_attribute = $field->get_field_placeholder_attribute();
			$required_attribute    = $field->isRequired ? 'aria-required="true"' : '';
			$invalid_attribute     = $field->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
			$html5_attributes      = " {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$max_length} ";

			$input = '<div class="ginput_container ginput_container_text ginput_container_ir_national_id">';
			$input .= '<input onblur="ir_national_id_' . $field_id . '(this);" name="input_' . $field_id . '" id="' . $input_id . '" type="text" value="' . esc_attr( $value ) . '" class="ir_national_id ' . esc_attr( $class ) . '" ' . $tabindex . ' ' . $logic_event . ' ' . $html5_attributes . ' ' . $disabled_text . '/>';
			$input .= '</div>';

			if ( $is_frontend ) {
				$input .= '<span class="ir_national_id_location" id="ir_national_id_location_' . $field_id . '"></span>';
			}
		}

		return $input;
	}

	public function settings() { ?>
        <script type='text/javascript'>
            fieldSettings["ir_national_id"] = ".placeholder_setting, .input_mask_setting, .label_placement_setting, .prepopulate_field_setting, .conditional_logic_field_setting, .label_setting, .admin_label_setting, .size_setting, .rules_setting, .visibility_setting, .duplicate_setting, .default_value_setting, .description_setting, .css_class_setting, .ir_national_id_setting";
            jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
                jQuery("#showLocation").attr("checked", field["showLocation"] == true);
                jQuery("#showSeperator").attr("checked", field["showSeperator"] == true);
                jQuery("#forceEnglish").attr("checked", field["forceEnglish"] == true);
                jQuery("#notDigitError").val(field["notDigitError"]);
                jQuery("#qtyDigitError").val(field["qtyDigitError"]);
                jQuery("#duplicateError").val(field["duplicateError"]);
                jQuery("#isInvalidError").val(field["isInvalidError"]);
            });
        </script>
		<?php
	}


	public function admin_conditional_logic( $form ) {

		if ( GFCommon::is_entry_detail() ) {
			return $form;
		}

		echo "<script type='text/javascript'>" .
		     " gform.addFilter('gform_is_conditional_logic_field', function (isConditionalLogicField, field) {" .
		     "     return field.type == 'ir_national_id' ? true : isConditionalLogicField;" .
		     '	});' .
		     "	gform.addFilter('gform_conditional_logic_operators', function (operators, objectType, fieldId) {" .
		     '		var targetField = GetFieldById(fieldId);' .
		     "		if (targetField && targetField['type'] == 'ir_national_id') {" .
		     "			operators = {'is':'is','isnot':'isNot', '>':'greaterThan', '<':'lessThan', 'contains':'contains', 'starts_with':'startsWith', 'ends_with':'endsWith'};" .
		     '		}' .
		     '		return operators;' .
		     '	});' .
		     '</script>';

		return $form;
	}

	public function standard_settings( $position, $form_id ) {

		if ( $position == 50 ) { ?>

            <li class="ir_national_id_setting field_setting">

                <input type="checkbox" id="showLocation"
                       onclick="SetFieldProperty('showLocation', this.checked);"/>
                <label for="showLocation" class="inline">
                    نمایش شهر بر اساس کد ملی
					<?php gform_tooltip( "tooltip_showLocation" ); ?>
                </label>

                <br/>
                <input type="checkbox" id="showSeperator"
                       onclick="SetFieldProperty('showSeperator', this.checked);"/>
                <label for="showSeperator" class="inline">
                    جداسازی خودکار ارقام توسط خط فاصله
					<?php gform_tooltip( "tooltip_showSeperator" ); ?>
                </label>

                <br/>
                <input type="checkbox" id="forceEnglish"
                       onclick="SetFieldProperty('forceEnglish', this.checked);"/>
                <label for="forceEnglish" class="inline">
                    تبدیل خودکار اعداد فارسی و عربی به انگلیسی
					<?php gform_tooltip( "tooltip_forceEnglish" ); ?>
                </label>
                <br/>

                <br/>
                <label for="notDigitError">
                    پیغام زمانی که مقدار وارد شده شامل کاراکتر غیر عددی باشد
					<?php gform_tooltip( "tooltip_notDigitError" ); ?>
                </label>
                <input type="text" class="fieldwidth-3" id="notDigitError" size="35"
                       onkeyup="SetFieldProperty('notDigitError', this.value);"/>
                <br/>

                <br/>
                <label for="qtyDigitError">
                    پیغام زمانیکه تعداد ارقام وارد شده استاندارد نباشد
					<?php gform_tooltip( "tooltip_qtyDigitError" ); ?>
                </label>
                <input type="text" class="fieldwidth-3" id="qtyDigitError" size="35"
                       onkeyup="SetFieldProperty('qtyDigitError', this.value);"/>
                <br/>

                <br/>
                <label for="isInvalidError">
                    پیغام زمانیکه کد ملی وارد شده مطابق با الگوی ملی نباشد
					<?php gform_tooltip( "tooltip_isInvalidError" ); ?>
                </label>
                <input type="text" class="fieldwidth-3" id="isInvalidError" size="35"
                       onkeyup="SetFieldProperty('isInvalidError', this.value);"/>
                <br/>

                <br/>
                <label for="duplicateError">
                    پیغام زمانیکه کد ملی وارد شده قبلا ثبت شده باشد
					<?php gform_tooltip( "tooltip_duplicateError" ); ?>
                </label>
                <input type="text" class="fieldwidth-3" id="duplicateError" size="35"
                       onkeyup="SetFieldProperty('duplicateError', this.value);"/>
                <br/>
            </li>
			<?php
		}
	}

	public function frontend_script( $form ) {

		$flag_js = $flag_fn = false;
		$fields  = GFCommon::get_fields_by_type( $form, array( 'ir_national_id' ) );

		foreach ( (array) $fields as $field ) {

			$location     = rgar( $field, 'showLocation', 0 ) ? 1 : 0;
			$seperator    = rgar( $field, 'showSeperator', 0 ) ? 1 : 0;
			$forceEnglish = rgar( $field, 'forceEnglish', 0 ) ? 1 : 0;

			if ( ! $flag_js && ( $location + $seperator ) ) {

				$flag_js = true;

				wp_dequeue_script( 'gform_ir_national_id' );
				wp_deregister_script( 'gform_ir_national_id' );

				wp_register_script( 'gform_ir_national_id', GF_PERSIAN_URL . 'assets/js/national_id.min.js', array(), GF_PERSIAN_VERSION, false );
				wp_enqueue_script( 'gform_ir_national_id' );
			}
			?>
            <script type="text/javascript">
				<?php if ( ! $flag_fn && $forceEnglish) : ?>
                function ir_national_id_to_english(number) {
                    return number.replace(/[۰|٠]/g, '0').replace(/[۱|١]/g, '1')
                        .replace(/[۲|٢]/g, '2').replace(/[۳|٣]/g, '3')
                        .replace(/[۴|٤]/g, '4').replace(/[۵|٥]/g, '5').replace(/[۶|٦]/g, '6')
                        .replace(/[۷|٧]/g, '7').replace(/[۸|٨]/g, '8').replace(/[۹|٩]/g, '9')
                }
				<?php $flag_fn = true; endif;?>
                function ir_national_id_<?php echo $field['id']; ?>(_this) {
					<?php if ( $forceEnglish ) : ?>
                    _this.value = ir_national_id_to_english(_this.value);
					<?php endif;
					if ( $location + $seperator ) : ?>
                    var field_id = "<?php echo $field['id'] ?>";
                    var message1 = "<?php echo rgar( $field, 'notDigitError', 'کد ملی فقط باید به صورت عدد وارد شود.' ); ?>";
                    var message2 = "<?php echo rgar( $field, 'qtyDigitError', 'کد ملی می بایست 10 رقمی باشد.' ); ?>";
                    var message3 = "<?php echo rgar( $field, 'isInvalidError', 'کد ملی وارد شده مطابق با استانداردهای کشور نمی باشد.' ); ?>";
                    GFPersian_National_ID_Handler(_this, field_id, message1, message2, message3, <?php echo $seperator ?>, <?php echo $location ?>);
                    jQuery(_this).trigger('change');
					<?php endif;?>
                }
            </script>
			<?php
		}
	}

	public function is_valid( $value = '' ) {

		if ( ! empty( $value ) ) {

			$_value = $value;

			if ( strlen( $value ) == 8 ) {
				$_value = '00' . $value;
			}

			if ( strlen( $value ) == 9 ) {
				$_value = '0' . $value;
			}

			$pre_check = array(
				'0000000000',
				'1111111111',
				'2222222222',
				'3333333333',
				'4444444444',
				'5555555555',
				'6666666666',
				'7777777777',
				'8888888888',
				'9999999999',
				'0123456789',
			);

			if ( in_array( $_value, $pre_check ) ) {
				return 2;
			}

			if ( ! is_numeric( $value ) ) {
				return 4;
			}

			$value = (string) preg_replace( '/[^0-9]/', '', $value );

			if ( strlen( $value ) > 10 || strlen( $value ) < 8 ) {
				return 3;
			}

			$value = $_value;

			$list_code = str_split( $value );
			$last      = (int) $list_code[9];
			unset( $list_code[9] );

			$i   = 10;
			$sum = 0;
			foreach ( $list_code as $key => $val ) {
				$sum += intval( $val ) * $i;
				$i --;
			}

			$mod = (int) $sum % 11;

			if ( $mod >= 2 ) {
				$mod = 11 - $mod;
			}
			if ( $mod != $last ) {
				return 2;
			} else {
				return 1;
			}
		}

		return false;
	}

	public function validator( $result, $value, $form, $field ) {

		if ( $field['type'] == 'ir_national_id' ) {

			$value    = ! empty( $value ) ? str_replace( '-', '', $value ) : '';
			$is_valid = $this->is_valid( $value );

			if ( $is_valid == 4 ) {
				$result['is_valid'] = false;
				$result['message']  = rgar( $field, 'notDigitError', 'کد ملی فقط باید به صورت عدد وارد شود.' );

				return $result;
			}

			if ( $is_valid == 3 ) {
				$result['is_valid'] = false;
				$result['message']  = rgar( $field, 'qtyDigitError', 'کد ملی می بایست 10 رقمی باشد. تنها در صورتی مجاز به استفاده از کد های 8 یا 9 رقمی هستید که ارقام سمت چپ 0 باشند.' );

				return $result;
			}

			if ( $is_valid == 2 ) {
				$result['is_valid'] = false;
				$result['message']  = rgar( $field, 'isInvalidError', 'کد ملی وارد شده مطابق با استانداردهای کشور نمی باشد.' );

				return $result;
			}

			if ( $field['noDuplicates'] ) {

				if ( strlen( $value ) == 8 ) {
					$value = '00' . $value;
				}

				if ( strlen( $value ) == 9 ) {
					$value = '0' . $value;
				}

				if ( RGFormsModel::is_duplicate( $form['id'], $field, $value ) ) {
					$result['is_valid'] = false;
					$result['message']  = rgar( $field, 'duplicateError', 'این کد ملی توسط فرد دیگری ثبت شده است.' );

					return $result;
				}
			}
		}

		return $result;
	}

	public function pre_submission( $form ) {

		$ir_national_id_fields = GFCommon::get_fields_by_type( $form, array( 'ir_national_id' ) );

		foreach ( (array) $ir_national_id_fields as $field ) {

			$input_name  = "input_{$field['id']}";
			$input_value = rgpost( $input_name );

			if ( ! empty( $input_value ) ) {

				if ( strlen( $input_value ) == 8 ) {
					$_POST["input_{$field['id']}"] = '00' . $input_value;
				} elseif ( strlen( $input_value ) == 9 ) {
					$_POST["input_{$field['id']}"] = '0' . $input_value;
				} else {
					$_POST["input_{$field['id']}"] = $input_value;
				}
			}
		}
	}
}

new GFPersian_National_ID();