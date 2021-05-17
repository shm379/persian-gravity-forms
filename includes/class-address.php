<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Adress extends GFPersian_Core {

	public function __construct() {

		if ( $this->option( 'address', '1' ) != '1' ) {
			return;
		}

		add_action( 'gform_editor_js', array( $this, 'iran_cities_editor_js' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'iran_cities_option' ), 10, 2 );
		add_filter( 'gform_address_types', array( $this, 'iran_address_type' ) );
		add_filter( 'gform_predefined_choices', array( $this, 'iran_provinces_choices' ), 1 );
		add_filter( 'gform_field_content', array( $this, 'iran_cities_field_type' ), 10, 5 );
		add_action( 'gform_register_init_scripts', array( $this, 'init_script' ), 10, 1 );
		add_action( 'gform_enqueue_scripts', array( $this, 'external_js' ), 10, 2 );
	}

	public function iran_provinces() {
		return array(
			'آذربایجان شرقی',
			'آذربایجان غربی',
			'اردبیل',
			'اصفهان',
			'البرز',
			'ایلام',
			'بوشهر',
			'تهران',
			'چهارمحال و بختیاری',
			'خراسان جنوبی',
			'خراسان رضوی',
			'خراسان شمالی',
			'خوزستان',
			'زنجان',
			'سمنان',
			'سیستان و بلوچستان',
			'فارس',
			'قزوین',
			'قم',
			'کردستان',
			'کرمان',
			'کرمانشاه',
			'کهگیلویه و بویراحمد',
			'گلستان',
			'گیلان',
			'لرستان',
			'مازندران',
			'مرکزی',
			'هرمزگان',
			'همدان',
			'یزد'
		);
	}

	public function iran_address_type( $address_types ) {

		$address_types['iran'] = array(
			'label'       => 'ایران',
			'country'     => 'ایران',
			'zip_label'   => 'کدپستی',
			'state_label' => 'استان',
			'states'      => array_merge( array( '' ), $this->iran_provinces() )
		);

		return $address_types;
	}

	public function iran_provinces_choices( $choices ) {

		$states['استان های ایران'] = $this->iran_provinces();

		return $choices = array_merge( $states, $choices );
	}

	public function iran_cities_option( $position, $form_id ) {
		if ( $position == 25 ) { ?>
            <li class="iran_cities field_setting">
                <input type="checkbox" id="iran_cities"
                       onclick="SetFieldProperty('iran_cities', jQuery(this).is(':checked') ? 1 : 0);"/>
                <label class="inline gfield_value_label" for="iran_cities">فعالسازی شهرهای ایران</label>
            </li>
			<?php
		}
	}

	public function iran_cities_editor_js() { ?>

        <script type='text/javascript'>
            jQuery(document).ready(function ($) {
                fieldSettings["address"] += ", .iran_cities";
                $(document).bind("gform_load_field_settings", function (event, field, form) {
                    $("#iran_cities").attr("checked", field["iran_cities"] == true);
                    var $address_type = $('#field_address_type');
                    if (!$('#iran_cities_div').length) {
                        var $iran_cities = $(".iran_cities");
                        var $iran_cities_input = $iran_cities.html();
                        $iran_cities.remove();
                        $address_type.after('<div id="iran_cities_div"><br>' + $iran_cities_input + '</div>');
                    }
                    if ($address_type.val() === 'iran')
                        $('#iran_cities_div').show();
                    else
                        $('#iran_cities_div').hide();
                    $address_type.change(function () {
                        if ($(this).val() === 'iran')
                            $("#iran_cities_div").slideDown();
                        else
                            $("#iran_cities_div").slideUp();
                    });
                });
            });
        </script>

		<?php
	}

	public function iran_cities_field_type( $content, $field, $value, $entry_id, $form_id ) {

		if ( $this->is_iran_cities( $field ) ) {

			$id = absint( $field['id'] );

			preg_match( '/<input.*?(name=["\']input_' . $id . '.3["\'].*?)\/??>/i', $content, $match );

			if ( ! empty( $match[0] ) && ! empty( $match[1] ) ) {
				$city_input = trim( $match[1] );
				$city_input = str_ireplace( 'value=', 'data-selected=', $city_input );
				$content    = str_replace( $match[0], "<select {$city_input}><option value='' selected='selected'>&nbsp;&nbsp;</option></select>", $content );
			}
		}

		return $content;
	}

	public function external_js( $form, $ajax ) {

		$fields = GFCommon::get_fields_by_type( $form, array( 'address' ) );

		foreach ( (array) $fields as $field ) {

			if ( $this->is_iran_cities( $field ) ) {

				wp_dequeue_script( 'gform_iran_citeis' );
				wp_deregister_script( 'gform_iran_citeis' );

				wp_register_script( 'gform_iran_citeis', GF_PERSIAN_URL . 'assets/js/iran-cities-full.min.js', array(), GF_PERSIAN_VERSION, false );
				wp_enqueue_script( 'gform_iran_citeis' );

				add_action( 'gform_enqueue_scripts', array( $this, 'frontend_rtl' ), 999, 1 );

				break;
			}
		}
	}

	public function frontend_rtl( $form ) {

		if ( ! apply_filters( 'gform_iran_cities_fronend_rtl', is_rtl() && ! is_admin() ) ) {
			return;
		}
		?>
        <style type="text/css">
            <?php
			foreach ( $form['fields'] as &$field ) {
				if ( $this->is_iran_cities( $field ) ) {
				    $id = $form['id'] . '_' . $field['id'];
					echo '
					html[dir="rtl"] #input_' . $id . '_4_container{
						float: right !important;
						padding-right: 0 !important;
						padding-left: 16px !important;
					}
					html[dir="rtl"] #input_' . $id . '_3_container{
						float: left !important;
						padding-right: 0 !important;
						padding-left: 0 !important;
					}';
				}
			}
			?>
        </style>
		<?php
	}

	public function init_script( $form ) {

		foreach ( $form['fields'] as &$field ) {

			if ( $this->is_iran_cities( $field ) ) {

				$id = $form['id'] . '_' . $field['id'];

				$script = 'jQuery().ready(function($){' .
				          '$(".has_city #input_' . $id . '_3").html(gform_iranCities(""+$(".has_city #input_' . $id . '_4").val()));' .
				          'if ($(".has_city #input_' . $id . '_3").attr("data-selected")) {' .
				          '$(".has_city #input_' . $id . '_3").val($(".has_city #input_' . $id . '_3").attr("data-selected"));' .
				          '}' .
				          '$(document.body).on("change", ".has_city #input_' . $id . '_4" ,function(){' .
				          '$(".has_city #input_' . $id . '_3").html(gform_iranCities(""+$(".has_city #input_' . $id . '_4").val()));' .
				          '}).on("change", ".has_city #input_' . $id . '_3" ,function(){' .
				          '$(this).attr("data-selected", $(this).val());' .
				          '})' .
				          '})';
				GFFormDisplay::add_init_script( $form['id'], 'iran_address_city_' . $id, GFFormDisplay::ON_PAGE_RENDER, $script );
			}
		}
	}

	private function is_iran_cities( $field ) {
		return $field['type'] == 'address' && $field['addressType'] == 'iran' && rgar( $field, 'iran_cities' ) && ! is_admin();
	}

}

new GFPersian_Adress();