<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_JalaliDate extends GFPersian_Core {

	public function __construct() {

		if ( $this->option( 'jalali', '1' ) != '1' ) {
			return;
		}

		require_once( 'lib/jalali.php' );
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_editor_js', array( $this, 'jalali_settings' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'jalali_checkbox' ), 10, 2 );
		add_action( 'gform_enqueue_scripts', array( $this, 'jalali_datepicker' ), 11, 1 );
		add_filter( 'gform_date_min_year', array( $this, 'jalali_date_min' ), 10, 3 );
		add_filter( 'gform_date_max_year', array( $this, 'jalali_date_max' ), 10, 3 );
		add_filter( 'gform_field_validation', array( $this, 'jalali_validator' ), 999999, 4 );
		add_filter( 'gform_predefined_choices', array( $this, 'jalali_predefined_choices' ), 1 );
	}

	public function tooltips( $tooltips ) {
		$tooltips['gform_activate_jalali'] = '<h6>فعالسازی تاریخ شمسی</h6>در صورتی که از چند فیلد تاریخ استفاده میکنید، فعالسازی تاریخ شمسی یکی از فیلدها کفایت میکند.<br/>تذکر : با توجه به آزمایشی بودن این قسمت ممکن است تداخل توابع سبب ناسازگاری با برخی قالب ها شود.';

		return $tooltips;
	}

	public function jalali_settings() { ?>
        <script type='text/javascript'>
            fieldSettings['date'] += ', .jalali_setting';
            jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
                jQuery('#check_jalali').attr('checked', field['check_jalali'] == true);
            });
        </script>
		<?php
	}

	public function jalali_checkbox( $position, $form_id ) {
		if ( $position == 25 ) { ?>
            <li class="jalali_setting field_setting">
                <input type="checkbox" id="check_jalali"
                       onclick="SetFieldProperty('check_jalali', jQuery(this).is(':checked') ? 1 : 0);"/>
                <label class="inline gfield_value_label" for="check_jalali" class="inline">
                    فعالسازی تاریخ شمسی
					<?php gform_tooltip( "gform_activate_jalali" ) ?>
                </label>
            </li>
			<?php
		}
	}

	public function jalali_datepicker( $form ) {

		if ( ! is_admin() && ( wp_script_is( 'gform_datepicker_init' ) || wp_script_is( 'gform_datepicker_init', 'registered' ) ) ) {

			foreach ( $form['fields'] as $field ) {

				if ( $field['type'] == 'date' && rgar( $field, 'check_jalali' ) ) {

					wp_dequeue_script( 'jquery-ui-datepicker' );
					wp_deregister_script( 'jquery-ui-datepicker' );

					wp_register_script( 'jquery-ui-datepicker', GF_PERSIAN_URL . 'assets/js/jalali-datepicker.js', array(
						'jquery',
						'jquery-migrate',
						'jquery-ui-core',
						'gform_gravityforms'
					), GF_PERSIAN_VERSION, true );
					wp_enqueue_script( 'jquery-ui-datepicker' );
					break;
				}
			}
		}
	}

	public function jalali_date_min( $min_year, $form, $field ) {

		if ( rgar( $field, 'type' ) == 'date' && rgar( $field, 'check_jalali' ) ) {
			$min_year = GF_gregorian_to_jalali( $min_year, 03, 21 );
			$min_year = $min_year[0] + 1;
		}

		return apply_filters( 'gform_jalali_date_min_year', $min_year, $form, $field );
	}

	public function jalali_date_max( $max_year, $form, $field ) {

		if ( rgar( $field, 'type' ) == 'date' && rgar( $field, 'check_jalali' ) ) {
			$max_year = GF_gregorian_to_jalali( $max_year, 03, 21 );
			$max_year = $max_year[0] + 20;
		}

		return apply_filters( 'gform_jalali_date_max_year', $max_year, $form, $field );
	}

	public function jalali_validator( $result, $value, $form, $field ) {

		if ( rgar( $field, 'type' ) == 'date' && rgar( $field, 'check_jalali' ) ) {

			$format      = rgar( $field, 'dateFormat', 'mdy' );
			$formats     = array(
				'mdy'       => 'mm/dd/yyyy',
				'dmy'       => 'dd/mm/yyyy',
				'dmy_dash'  => 'dd-mm-yyyy',
				'dmy_dot'   => 'dd.mm.yyyy',
				'ymd_slash' => 'yyyy/mm/dd',
				'ymd_dash'  => 'yyyy-mm-dd',
				'ymd_dot'   => 'yyyy.mm.dd'
			);
			$format_name = ! empty( $formats[ $format ] ) ? $formats[ $format ] : '';
			$message     = $format_name && rgar( $field, 'dateType' ) == 'datepicker' ? sprintf( esc_html__( 'Please enter a valid date in the format (%s).', 'gravityforms' ), $format_name ) : esc_html__( 'Please enter a valid date.', 'gravityforms' );

			/*این شرط مشخص میکنه فقط اگر خطایی وجود نداشت و یا اگر خطا مربوط به ولیدیت تاریخ بود وارد بررسی شود*/
			if ( ! empty( $result['message'] ) && $message != $result['message'] ) {
				return $result;
			}

			if ( is_array( $value ) && rgempty( 0, $value ) && rgempty( 1, $value ) && rgempty( 2, $value ) ) {
				$value = null;
			}

			if ( ! empty( $value ) ) {
				$date  = GFCommon::parse_date( $value, $format );
				$day   = intval( rgar( $date, 'day' ) );
				$month = intval( rgar( $date, 'month' ) );
				$year  = intval( rgar( $date, 'year' ) );

				$result['is_valid'] = GF_jcheckdate( $month, $day, $year );
				if ( ! $result['is_valid'] && empty( $result['message'] ) ) {
					$result['message'] = $message;
				}
			}
		}

		return $result;
	}

	public function jalali_predefined_choices( $choices ) {

		$month['ماه های ایران'] = array(
			'فروردین',
			'اردیبهشت',
			'خرداد',
			'تیر',
			'مرداد',
			'شهریور',
			'مهر',
			'آبان',
			'آذر',
			'دی',
			'بهمن',
			'اسفند'
		);

		return array_merge( $month, $choices );
	}

}

new GFPersian_JalaliDate();