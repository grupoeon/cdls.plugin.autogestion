<?php
/**
 * This is the main controller.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The main controller.
 */
class Autogestion_Controller {

	/**
	 * The single instance of the class.
	 *
	 * @var Autogestion_Controller
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	protected static $_instance = null;

	/**
	 * Main Autogestion_Controller Instance.
	 *
	 * Ensures only one instance of Autogestion_Controller is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @return Autogestion_Controller - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}

Autogestion_Controller::instance();
