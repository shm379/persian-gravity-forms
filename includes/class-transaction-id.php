<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Transaction_ID extends GFPersian_Core {

	private $transaction_id_title;

	public function __construct() {

		if ( $this->option( 'enable_transaction_id', '1' ) != '1' ) {
			return;
		}

		$this->transaction_id_title = $this->option( 'transaction_id_title', 'شماره تراکنش' );

		add_filter( 'gettext', array( $this, 'change_transaction_id_title' ), 999, 3 );
		add_filter( 'ngettext', array( $this, 'change_transaction_id_title' ), 999, 3 );
		add_action( 'gform_entry_created', array( __CLASS__, 'create_transaction_id' ), 10, 2 );
	}

	public static function create_transaction_id( $entry, $form ) {

		$default_mask = '9999999999';
		$masked_input = self::_option( 'transaction_id_mask', $default_mask );
		$masked_input = apply_filters( 'gform_transaction_id', $masked_input, $entry, $form );
		$masked_input = ! empty( $masked_input ) ? $masked_input : $default_mask;

		$transaction_id = '';
		foreach ( str_split( $masked_input ) as $string ) {
			if ( in_array( $string, array( '*', 'a' ) ) ) {
				$rand = $string == '*' ? array_map( 'strval', range( 0, 9 ) ) : array();
				$rand = array_merge( $rand, range( 'A', 'Z' ), range( 'a', 'z' ) );
				shuffle( $rand );
				$transaction_id .= $rand[ rand( 0, count( $rand ) - 1 ) ];
			} elseif ( $string == '9' ) {
				$transaction_id .= rand( 0, 9 );
			} else {
				$transaction_id .= $string;
			}
		}

		if ( $form == 'return' ) {
			if ( self::_option( 'enable_transaction_id', '1' ) != '1' ) {
				return '';
			}

			return $transaction_id;
		}

		GFAPI::update_entry_property( $entry['id'], 'transaction_id', $transaction_id );
	}


	public function change_transaction_id_title( $translated_text, $untranslated_text, $domain ) {
		if ( $domain == 'gravityforms' && strtolower( $untranslated_text ) == 'transaction id' ) {

			return $this->transaction_id_title;
		}

		return $translated_text;
	}

}

new GFPersian_Transaction_ID;