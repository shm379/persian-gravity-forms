<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFPersian_Currencies extends GFPersian_Core {

	public function __construct() {

		if ( $this->option( 'currencies', '1' ) != '1' ) {
			return;
		}

		add_filter( 'gform_currencies', array( $this, 'iran_currencies' ) );
	}

	public function iran_currencies( $currencies ) {

		unset( $currencies['IRR'], $currencies['irr'], $currencies['IRT'], $currencies['irt'] );

		$is_rtl = true; /*may be need use is_rtl() later but not now*/
		$is_fa  = apply_filters( 'gform_iran_currencies_is_fa', get_locale() == 'fa_IR', $currencies );

		$ir_currencies = array(

			'IRR' => array(
				'name'               => 'ریال ایران',
				'symbol_left'        => ( ! $is_rtl ? ( $is_fa ? 'ریال' : 'Rial' ) : '' ),
				'symbol_right'       => ( $is_rtl ? ( $is_fa ? 'ریال' : 'Rial' ) : '' ),
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 0
			),

			'IRHR' => array(
				'name'               => 'هزار ریال ایران',
				'symbol_left'        => ( ! $is_rtl ? ( $is_fa ? 'هزار ریال' : 'Thousand Rial' ) : '' ),
				'symbol_right'       => ( $is_rtl ? ( $is_fa ? 'هزار ریال' : 'Thousand Rial' ) : '' ),
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 0
			),

			'IRT' => array(
				'name'               => 'تومان ایران',
				'symbol_left'        => ( ! $is_rtl ? ( $is_fa ? 'تومان' : 'Toman' ) : '' ),
				'symbol_right'       => ( $is_rtl ? ( $is_fa ? 'تومان' : 'Toman' ) : '' ),
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 0
			),

			'IRHT' => array(
				'name'               => 'هزار تومان ایران',
				'symbol_left'        => ( ! $is_rtl ? ( $is_fa ? 'هزار تومان' : 'Thousand Toman' ) : '' ),
				'symbol_right'       => ( $is_rtl ? ( $is_fa ? 'هزار تومان' : 'Thousand Toman' ) : '' ),
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 0
			),

		);

		return array_merge( $ir_currencies, $currencies );
	}
}

new GFPersian_Currencies();