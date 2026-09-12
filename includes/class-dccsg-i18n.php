<?php
/**
 * Design Cart Column Scroll Gallery
 *
 * Author: Paweł Nosko https://www.designcart.pl/pawel-nosko.html
 * Company: Design Cart https://www.designcart.pl/
 *
 * @package Design_Cart_CS_Gallery
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tłumaczenia interfejsu admina (PL / EN).
 */
class DCCSG_I18n {

	/**
	 * @var array<string,array<string,string>>|null
	 */
	private static $strings = null;

	/**
	 * @return string
	 */
	public static function admin_lang() {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		return ( 0 === strpos( strtolower( $locale ), 'en' ) ) ? 'en' : 'pl';
	}

	/**
	 * @param string $key .
	 * @return string
	 */
	public static function t( $key ) {
		self::load();
		$lang = self::admin_lang();
		if ( isset( self::$strings[ $lang ][ $key ] ) ) {
			return self::$strings[ $lang ][ $key ];
		}
		if ( isset( self::$strings['pl'][ $key ] ) ) {
			return self::$strings['pl'][ $key ];
		}
		return $key;
	}

	/**
	 * @return void
	 */
	private static function load() {
		if ( null !== self::$strings ) {
			return;
		}
		self::$strings = require DCCSG_PATH . 'includes/translations.php';
	}
}
