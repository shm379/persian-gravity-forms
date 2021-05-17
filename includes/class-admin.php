<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Admin extends GFPersian_Core {

	public function __construct() {
		add_filter( 'gform_print_styles', array( $this, 'print_styles' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 999 );
		add_filter( 'gform_noconflict_styles', array( $this, 'noconflict_styles' ) );
		add_filter( 'gform_noconflict_scripts', array( $this, 'noconflict_scripts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_rss_widget' ) );
		add_action( 'admin_footer', array( $this, 'hide_license' ), 9999 );
	}

	public function admin_styles() {

		if ( $this->is_gravity_page() ) {

			if ( is_rtl() && $this->option( 'rtl_admin', '1' ) == '1' ) {
				wp_register_style( 'gform_admin_rtl_style', GF_PERSIAN_URL . 'assets/css/rtl-admin.css' );
				wp_enqueue_style( 'gform_admin_rtl_style' );
			}

			$this->font_styles();
		}
	}

	public function print_styles( $styles ) {

		$styles = (array) $styles;

		if ( is_rtl() && $this->option( 'rtl_admin', '1' ) == '1' ) {
			$styles[] = $style = 'gform_print_rtl_style';
			wp_register_style( $style, GF_PERSIAN_URL . 'assets/css/rtl-print.css' );
		}

		return array_merge( $styles, $this->font_styles() );
	}

	private function font_styles( $only_face = false ) {

		$styles = array();

		if ( ( $font = $this->option( 'font_admin', 'vazir' ) ) != '0' ) {

			$styles[] = $style = 'gform_admin_font_face';
			wp_register_style( $style, GF_PERSIAN_URL . "assets/css/font-face-{$font}.css" );
			wp_enqueue_style( $style );

			if ( ! $only_face ) {
				$styles[] = $style = 'gform_print_font_style';
				wp_register_style( $style, GF_PERSIAN_URL . "assets/css/font-admin.css" );
				wp_enqueue_style( $style );
			}
		}

		return $styles;
	}


	public function noconflict_scripts( $scripts ) {
		//$scripts[] = '';//ممکنه احتیاج بشه
		return $scripts;
	}

	public function noconflict_styles( $styles ) {
		$styles[] = 'gform_print_rtl_style';
		$styles[] = 'gform_admin_rtl_style';
		$styles[] = 'gform_admin_font_face';
		$styles[] = 'gform_print_font_style';

		return $styles;
	}

	public function dashboard_rss_widget() {

		if ( current_user_can( 'manage_options' ) && $this->option( 'rss_widget', '1' ) == '1' ) {

			/*wp_add_dashboard_widget( 'GFPersian_RSS', 'آخرین مطالب گرویتی فرم پارسی',
				array( $this, 'callback_rss_widget' ) );*/

			add_meta_box( 'GFPersian_RSS', 'آخرین مطالب گرویتی فرم پارسی', array( $this, 'callback_rss_widget' ),
				'dashboard', 'side', 'low' );
		}
	}

	public function callback_rss_widget() {

		$font  = $this->font_styles( true );
		$style = ! empty( $font ) ? 'style="font-family: GFPersian;"' : ''; ?>

        <div class="rss-widget" <?php echo $style; ?>>
			<?php wp_widget_rss_output( array(
				'url'          => 'http://gravityforms.ir/feed/',
				'items'        => 2,
				'show_summary' => 1,
				'show_author'  => 1,
				'show_date'    => 1
			) ); ?>
            <div style="border-top: 1px solid #e7e7e7; padding-top: 12px !important; font-size: 13px; height: 20px;">

                <img src="<?php echo GF_PERSIAN_URL ?>assets/images/logo.png" width="30" height="auto"
                     style="float: right; margin: -10px 1px 0 10px"/>

                <a href="http://gravityforms.ir" target="_blank" title="گرویتی فرم پارسی">
                    مشاهده وب سایت گرویتی فرم پارسی
                </a>
            </div>
        </div>
		<?php
	}


	public function hide_license() {
		if ( $this->is_gravity_page() && $this->option( 'hide_lic', '0' ) == '1' ) { ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('img').each(function () {
                        if (this.title.indexOf("licensed") !== -1 || this.alt.indexOf("licensed") !== -1)
                            $(this).hide().parent("a").hide().parent("div").hide();
                    });
                });
            </script>
			<?php
		}
	}

}

new GFPersian_Admin;