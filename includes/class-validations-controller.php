<?php
/**
 * This controls all the custom validators.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

use \Valitron\Validator;

class Validations_Controller {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		Validator::addRule(
			'fiscalCondition',
			function( $field, $value, array $params, array $fields ) {
				if ( $fields['id_tipo_documento'] == 5 ) {
					return in_array( $value, array( 1 ) );
				} else {
					return in_array( $value, array( 2, 3, 5 ) );
				}
				return false;
			},
			'es incorrecta para ese tipo de documento.'
		);

		Validator::addRule(
			'domain',
			function( $field, $value, array $params, array $fields ) {
				return preg_match( '/\w{2}\d{3}\w{2}|\w{3}\d{3}/', $value );
			},
			'tiene un formato incorrecto. Formato: AA000AA ó AAA000.'
		);

		Validator::addRule(
			'domainBajas',
			function( $field, $value, array $params, array $fields ) {
				return preg_match( '/\w{2}\d{3}\w{2}|\w{3}\d{3}|\d{3}\w{3}|\w\d{3}\w{3}/', $value );
			},
			'tiene un formato incorrecto.'
		);

	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function VALID() {
	return Validations_Controller::instance();
}

VALID();
