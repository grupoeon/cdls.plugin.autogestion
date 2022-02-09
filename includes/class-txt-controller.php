<?php
/**
 * This is the TXT controller.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The TXT controller.
 */
class TXT_Controller {

	const TXT_ROOT_DIR = ABSPATH . '/proytec/sistema';

	/**
	 * The single instance of the class.
	 *
	 * @var TXT_Controller
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	protected static $_instance = null;

	/**
	 * Main TXT_Controller Instance.
	 *
	 * Ensures only one instance of TXT_Controller is loaded or can be loaded.
	 *
	 * @return TXT_Controller - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function generate( $id, $row ) {

		file_put_contents( self::TXT_ROOT_DIR . "/$id.txt", $row . "\r\n", FILE_APPEND );

	}

}

/**
 * Returns the only instance of the TXT_Controller.
 *
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 *
 * @return TXT_Controller
 */
function TXT() {
	return TXT_Controller::instance();
}
