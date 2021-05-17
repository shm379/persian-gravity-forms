<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Snippets extends GFPersian_Core {

	public function __construct() {

		if ( $this->option( 'label_visibility', '1' ) == '1' ) {
			add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );
		}

		if ( $this->option( 'private_post', '1' ) == '1' ) {
			add_action( 'gform_post_status_options', array( $this, 'add_private_post_status' ) );
		}

		if ( $this->option( 'newsletter', '1' ) == '1' ) {
			add_filter( 'gform_notification_events', array( $this, 'add_newsletter_notification_event' ) );
			add_filter( 'gform_before_resend_notifications', array( $this, 'add_notification_filter' ) );
		}
	}

	public function add_private_post_status( $post_statuses ) {
		$post_statuses['private'] = 'خصوصی';

		return $post_statuses;
	}

	/*---------------------------------------------------------------------------------------------*/
	/*----------Start of NewsLetter----------------------------------------------------------------*/
	/*---------------------------------------------------------------------------------------------*/
	public function add_newsletter_notification_event( $events ) {
		$events['newsletter'] = 'خبرنامه';

		return $events;
	}

	public function add_notification_filter( $form ) {
		add_filter( 'gform_notification', array( $this, 'evaluate_notification_conditional_logic' ), 10, 3 );

		return $form;
	}

	public function evaluate_notification_conditional_logic( $notification, $form, $entry ) {

		// if it fails conditional logic, suppress it
		if ( $notification['event'] == 'newsletter' && ! GFCommon::evaluate_conditional_logic( rgar( $notification, 'conditionalLogic' ), $form, $entry ) ) {
			add_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		}

		return $notification;
	}

	public function abort_next_notification( $args ) {
		remove_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		$args['abort_email'] = true;

		return $args;
	}
	/*---------------------------------------------------------------------------------------------*/
	/*----------End of NewsLetter------------------------------------------------------------------*/
	/*---------------------------------------------------------------------------------------------*/

}

new GFPersian_Snippets;