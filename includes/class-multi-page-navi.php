<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Multipage_Navigation extends GFPersian_Core {

	public $_args = array();

	private static $script_displayed;

	public function __construct() {

		if ( is_admin() || $this->option( 'multipage_nav', '1' ) != '1' ) {
			return;
		}

		$this->_args = array(
			'activate_on_last_page' => $this->option( 'multipage_nav_last', '1' )
		);

		add_filter( 'gform_pre_render', array( $this, 'output_navigation_script' ), 10, 2 );
	}

	public function output_navigation_script( $form, $is_ajax ) {

		// only apply this to multi-page forms
		if ( empty( $form['pagination']['pages'] ) || ! is_array( $form['pagination']['pages'] ) || count( $form['pagination']['pages'] ) <= 1 ) {
			return $form;
		}

		$this->register_script( $form );

		if ( ! $this->_args['activate_on_last_page'] || $this->is_last_page( $form ) || $this->is_last_page_reached() ) {
			add_filter( "gform_form_tag_{$form['id']}", function ( $tag ) {
				return $tag . '<input id="gform_multi_page_nav_last_page_reached" name="gform_multi_page_nav_last_page_reached" value="1" type="hidden" />';
			} );
		}

		// only output the gform_multi_page_nav object once regardless of how many forms are being displayed
		// also do not output again on ajax submissions
		if ( self::$script_displayed || ( $is_ajax && rgpost( 'gform_submit' ) ) ) {
			return $form;
		}
		?>

        <script type="text/javascript">

            (function ($) {

                window.gform_multi_page_navObj = function (args) {

                    this.formId = args.formId;
                    this.formElem = jQuery('form#gform_' + this.formId);
                    this.currentPage = args.currentPage;
                    this.lastPage = args.lastPage;
                    this.activateOnLastPage = args.activateOnLastPage;

                    this.init = function () {

                        // if this form is ajax-enabled, we'll need to get the current page via JS
                        if (this.isAjax())
                            this.currentPage = this.getCurrentPage();

                        if (!this.isLastPage() && !this.isLastPageReached())
                            return;

                        var gform_multi_page_nav = this;
                        var steps = $('form#gform_' + this.formId + ' .gf_step');

                        steps.each(function () {

                            var stepNumber = parseInt($(this).find('span.gf_step_number').text());

                            if (stepNumber != gform_multi_page_nav.currentPage) {
                                $(this).html(gform_multi_page_nav.createPageLink(stepNumber, $(this).html()))
                                    .addClass('gform_multi_page_nav-step-linked');
                            } else {
                                $(this).addClass('gform_multi_page_nav-step-current');
                            }

                        });

                        $(document).on('click', '#gform_' + this.formId + ' a.gform_multi_page_nav-page-link', function (event) {
                            event.preventDefault();

                            var hrefArray = $(this).attr('href').split('#');
                            if (hrefArray.length >= 2) {
                                var pageNumber = hrefArray.pop();
                                gform_multi_page_nav.postToPage(pageNumber, !$(this).hasClass('gform_multi_page_navmp-default'));
                            }

                        });

                    };

                    this.createPageLink = function (stepNumber, HTML) {
                        return '<a href="#' + stepNumber + '" class="gform_multi_page_nav-page-link gform_multi_page_nav-default">' + HTML + '</a>';
                    };

                    this.postToPage = function (page) {
                        this.formElem.append('<input type="hidden" name="gform_multi_page_nav_page_change" value="1" />');
                        this.formElem.find('input[name="gform_target_page_number_' + this.formId + '"]').val(page);
                        this.formElem.submit();
                    };

                    this.getCurrentPage = function () {
                        return this.formElem.find('input#gform_source_page_number_' + this.formId).val();
                    };

                    this.isLastPage = function () {
                        return this.currentPage >= this.lastPage;
                    };

                    this.isLastPageReached = function () {
                        return this.formElem.find('input[name="gform_multi_page_nav_last_page_reached"]').val() == true;
                    };

                    this.isAjax = function () {
                        return this.formElem.attr('target') == 'gform_ajax_frame_' + this.formId;
                    };

                    this.init();

                }

            })(jQuery);

        </script>

		<?php
		self::$script_displayed = true;

		return $form;
	}

	public function register_script( $form ) {

		$page_number = GFFormDisplay::get_current_page( $form['id'] );
		$last_page   = count( $form['pagination']['pages'] );

		$args = array(
			'formId'             => $form['id'],
			'currentPage'        => $page_number,
			'lastPage'           => $last_page,
			'activateOnLastPage' => $this->_args['activate_on_last_page'],
		);

		$script = "window.gform_multi_page_nav = new gform_multi_page_navObj(" . json_encode( $args ) . ");";
		GFFormDisplay::add_init_script( $form['id'], 'gform_multi_page_nav', GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_last_page( $form ) {

		$page_number = GFFormDisplay::get_current_page( $form['id'] );
		$last_page   = count( $form['pagination']['pages'] );

		return $page_number >= $last_page;
	}

	public function is_last_page_reached() {
		return rgpost( 'gform_multi_page_nav_last_page_reached' );
	}

}

new GFPersian_Multipage_Navigation();