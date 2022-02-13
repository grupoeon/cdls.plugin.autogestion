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

		Validator::addRule(
			'document_exists_except_owner',
			function( $field, $value, array $params, array $fields ) {
				return ! DB()->document_exists( $value, API()->client_id() );
			},
			MSG()::DOCUMENT_EXISTS
		);

		Validator::addRule(
			'email_exists_except_owner',
			function( $field, $value, array $params, array $fields ) {
				return ! DB()->email_exists( $value, API()->client_id() );
			},
			MSG()::EMAIL_EXISTS
		);

		Validator::addRule(
			'document_exists',
			function( $field, $value, array $params, array $fields ) {
				return ! DB()->document_exists( $value );
			},
			MSG()::DOCUMENT_EXISTS
		);

		Validator::addRule(
			'email_exists',
			function( $field, $value, array $params, array $fields ) {
				return ! DB()->email_exists( $value );
			},
			MSG()::EMAIL_EXISTS
		);

		Validator::addRule(
			'currentPassword',
			function( $field, $value, array $params, array $fields ) {
				return DB()->is_current_password( $value );
			},
			MSG()::PASSWORD_NOT_MATCH
		);

		Validator::addRule(
			'required_if_cbu',
			function( $field, $value, array $params, array $fields ) {
				if ( 9 == $fields['id_medio_de_pago'] ) {
					return ! empty( $value );
				}
				return true;
			},
			'es requerido'
		);

		Validator::addRule(
			'required_if_tarjeta',
			function( $field, $value, array $params, array $fields ) {
				if ( intval( $fields['id_medio_de_pago'] ) <= 8 ) {
					return ! empty( $value );
				}
				return true;
			},
			'es requerido'
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
