<?php
/**
 * This is the time controller.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Time_Controller {


	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function now( $format = 'Y-m-d H:i:s' ) {

		return $this->create( 'now', $format );

	}

	public function create( $string, $format = 'Y-m-d H:i:s' ) {

		$date = new \DateTime( $string, new \DateTimeZone( wp_timezone_string() ) );
		return $date->format( $format );

	}

	public function create_from_format( $format = 'Y-m-d H:i:s', $string ) {
		return \DateTime::createFromFormat(
			$format,
			$string,
			new \DateTimeZone( wp_timezone_string() )
		);
	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function TIME() {
	return Time_Controller::instance();
}
