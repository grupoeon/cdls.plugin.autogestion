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
				if ( 5 === intval( $fields['id_tipo_documento'] ) ) {
					return in_array( intval( $value ), array( 1 ), true );
				} else {
					return in_array( intval( $value ), array( 2, 3, 5 ), true );
				}
				return false;
			},
			'es incorrecta para ese tipo de documento.'
		);

		Validator::addRule(
			'domain',
			function( $field, $value, array $params, array $fields ) {
				return preg_match( '/[A-Za-z]{2}\d{3}[A-Za-z]{2}|[A-Za-z]{3}\d{3}/', $value );
			},
			'tiene un formato incorrecto. Formato: AA000AA ó AAA000.'
		);

		Validator::addRule(
			'domainBajas',
			function( $field, $value, array $params, array $fields ) {
				return preg_match( '/[A-Za-z]{2}\d{3}[A-Za-z]{2}|[A-Za-z]{3}\d{3}|\d{3}[A-Za-z]{3}|[A-Za-z]\d{3}[A-Za-z]{3}/', $value );
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

	}

	/**
	 * @phpcs:disable WordPress.PHP.DisallowShortTernary.Found
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 */
	public function when( $data = array(), $conditions, $then_rules, $else_rules = array() ) {

		$data = $data ?: $_POST;

		$condition_validator = new \Valitron\Validator( $data );
		$condition_validator->rules( $conditions );
		$conditions_passed = $condition_validator->validate();

		if ( $conditions_passed ) {
			return $then_rules;
		}

		return $else_rules;

	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function V() {
	return Validations_Controller::instance();
}

V();
