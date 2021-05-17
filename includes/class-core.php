<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Core {

	private static $settings;

	public function __construct() {

		if ( ! class_exists( 'GFCommon' ) ) {
			add_action( 'admin_notices', array( $this, 'gform_not_exist' ) );

			return;
		}

		/*if ( version_compare( GFCommon::$version, '1.9.9.9.9', "<=" ) ) {
			add_action( 'admin_notices', array( $this, 'gform_min_version' ) );
		}*/

		add_action( 'admin_notices', array( $this, 'plugin_update' ), 9999 );
		add_filter( 'load_textdomain_mofile', array( $this, 'load_translate' ), 10, 2 );
		add_action( 'gform_loaded', array( $this, 'load_settings' ), 5 );
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_filter( 'gform_add_field_buttons', array( $this, 'fields_group' ) );

		$this->include_files();
	}

	public function gform_not_exist() {
		$message = sprintf( 'شما فقط بسته گرویتی فرم پارسی را نصب کرده اید. در حالیکه نصب هسته اصلی گرویتی فرم هم نیاز است. %sسوالات متداول%s', '<a href="http://gravityforms.ir/faq/" target="_blank">', '</a>' );
		printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
	}

	public function gform_min_version() {
		$message = sprintf( 'برای استفاده از کلیه پلاگین های گرویتی فرم پارسی نسخه هسته گرویتی فرم شما باید حداقل 2.0 به بالا باشد. هرچه سریعتر نسبت به ارتقای هسته گرویتی فرم خود اقدام نمایید. وگرنه ممکن است در حین کار با پلاگین ها دچار مشکل شوید. %sراهنمای بروز رسانی%s', '<a href="http://gravityforms.ir/11378/" target="_blank">', '</a>' );
		printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
	}

	public function plugin_update() {

		if ( get_option( 'gform_pending_installation' ) ) {
			update_option( 'gform_pending_installation', false );
			$current_version = get_option( 'rg_form_version' );
			if ( $current_version === false ) {
				if ( class_exists( 'GFCommon' ) ) {
					update_option( 'rg_form_version', GFCommon::$version );
				} else {
					update_option( 'rg_form_version', '2.0.0' );
				}
			}
		}

		if ( ! get_option( 'gf_persian_updated' ) ) {

			for ( $i = 1; $i <= 5; $i ++ ) {
				delete_option( 'persian_gf_notice_v' . $i );
			}

			//update national id
			global $wpdb;
			$table  = RGFormsModel::get_meta_table_name();
			$update = $wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, 'mellicart', 'ir_national_id')" );
			if ( $update !== false ) {
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id\"', '\"showLocation\"')" );
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id_sp\"', '\"showSeperator\"')" );
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id_sp1\"', '\"notDigitError\"')" );
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id_sp2\"', '\"qtyDigitError\"')" );
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id_sp3\"', '\"duplicateError\"')" );
				$wpdb->query( "UPDATE $table SET display_meta = REPLACE(display_meta, '\"field_ir_national_id_sp4\"', '\"isInvalidError\"')" );
				update_option( 'gf_persian_updated', GF_PERSIAN_VERSION );
			}

			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( 'گرویتی فرم پارسی با موفقیت بروز شد. %sرفتن به صفحه تنظیمات%s', '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=persian' ) . '">', '</a>' ) . '</p></div>';
		}

		if ( get_option( 'gf_persian_notice' ) != GF_PERSIAN_VERSION ) {

			delete_option( 'gf_persian_gateway' );
			update_option( 'gf_persian_notice', GF_PERSIAN_VERSION );

			$notices   = array();
			$notices[] = sprintf( 'تمام پلاگین های گرویتی فرم پارسی بروزرسانی و با نسخه آخر گرویتی فرم هماهنگ شده اند. برای دانلود بروز رسانی ها به سایت گرویتی فرم پارسی و منوی "%sسوابق خرید%s" مراجعه نمایید.', '<a target="_blank" href="http://gravityforms.ir/payment/download-history/">', '</a>' );
			$notices[] = sprintf( 'برای مشاهده راهنمای بروز رسانی درگاه های پرداخت %sکلیک کنید.%s', '<a target="_blank" href="http://gravityforms.ir/33598">', '</a>' );
			echo '<div class="notice notice-success is-dismissible"><p>' . implode( '<hr>', $notices ) . '</p></div>';
		}
	}

	public function tooltips( $tooltips ) {

		$tooltips['form_gf_persian_fields'] = '<h6>گرویتی فرم پارسی</h6>فیلدهای برنامه نویسی شده توسط گرویتی فرم پارسی به مرور اینجا اضافه خواهند شد.';

		return $tooltips;
	}


	public function fields_group( $field_groups ) {

		$group = 'gf_persian_fields';

		if ( ! function_exists( 'wp_list_pluck' ) || ! in_array( $group, wp_list_pluck( $field_groups, 'name' ) ) ) {
			array_push( $field_groups, array(
				'name'   => $group,
				'label'  => 'فیلدهای گرویتی فرم پارسی',
				'fields' => array()
			) );
		}

		return $field_groups;
	}

	public function load_settings() {

		if ( method_exists( 'GFForms', 'include_addon_framework' ) ) {

			GFForms::include_addon_framework();
			GFAddOn::register( 'GFPersian_Settings' );

			require_once( 'class-settings.php' );
		}
	}

	public function load_translate( $mo_file, $domain ) {

		if ( $this->option( 'translate', '1' ) == '1' && get_locale() == 'fa_IR' ) {

			$translates = array(
				'gravityforms',
				'gravityformscoupons',
				'gravityformsmailchimp',
				'gravityformspolls',
				'gravityformsquiz',
				'gravityformssignature',
				'gravityformssurvey',
				'gravityformsuserregistration',
				'gravityformsauthorizenet',
				'gravityformsaweber',
				'gravityformscampaignmonitor',
				'gravityformspaypalpaymentspro',
				'gravityformsfreshbooks',
				'gravityformspaypal',
				'gravityformspaypalpro',
				'gravityformstwilio',
				'gravityformsstripe',
				'gravityformszapier',
				'sticky-list',
				'gf-limit'
			);

			if ( in_array( $domain, $translates ) ) {
				$mo_file = dirname( plugin_dir_path( __FILE__ ) ) . "/languages/$domain/$domain-fa_IR.mo";
			}
		}

		return $mo_file;
	}

	private function include_files() {
		include 'class-admin.php';
		include 'class-address.php';
		include 'class-payments.php';
		include 'class-snippets.php';
		include 'class-merge-tag.php';
		include 'class-currencies.php';
		include 'class-jalali-date.php';
		include 'class-live-preview.php';
		include 'class-transaction-id.php';
		include 'class-multi-page-navi.php';

		include 'class-deprecated.php';
		if ( get_option( 'gf_persian_updated' ) ) {
			include 'class-national-id.php';
		} else {
			//the class is existed in class-deprecated.php
			new GFParsi_MelliCode();
		}
	}

	public static function _option( $setting_name = '', $default = null ) {

		if ( empty( self::$settings ) ) {
			if ( class_exists( 'GFPersian_Settings' ) ) {
				if ( method_exists( 'GFPersian_Settings', 'get_plugin_settings' ) ) {
					if ( is_callable( array( 'GFPersian_Settings' => 'get_plugin_settings' ) ) ) {
						self::$settings = GFPersian_Settings::get_instance()->get_plugin_settings();
					}
				}
			}
			if ( empty( self::$settings ) && defined( 'GF_PERSIAN_SLUG' ) ) {
				self::$settings = get_option( 'gravityformsaddon_' . GF_PERSIAN_SLUG . '_settings' );
			}
		}

		$settings = self::$settings;

		if ( ! empty( $setting_name ) ) {
			$settings = isset( $settings[ $setting_name ] ) ? $settings[ $setting_name ] : '';
		}

		return ! empty( $settings ) || strval( $settings ) == '0' ? $settings : $default;
	}

	public function option( $setting_name = '', $default = null ) {
		return self::_option( $setting_name, $default );
	}

	public function is_gravity_page() {

		$is_gform     = class_exists( 'RGForms' ) ? RGForms::is_gravity_page() : false;
		$current_page = trim( strtolower( rgget( 'page' ) ) );

		return $is_gform || substr( $current_page, 0, 2 ) == 'gf' || stripos( $current_page, 'gravity' ) !== false;
	}

	public static function get_base_url() {
		return plugins_url( '', dirname( __FILE__ ) );
	}


	public static function get_entry( $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );

		if ( is_wp_error( $entry ) ) {
			return false;
		}

		return $entry;
	}
}

new GFPersian_Core;