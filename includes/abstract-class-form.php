<?php
/**
 * This is the skeleton for all forms.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable WordPress.Files.FileName
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

abstract class Form {
	private $messages = array();
	abstract public function submit();
	abstract public function build();
	abstract public function output_form_fields();
	abstract public function get_validation_rules();
	abstract public function current_user_can_submit();

	public function __construct( $form ) {
		$this->form = $form;
	}

	public function message( $type, $message, $echo = false ) {
		$message = $this->form->{"{$type}_message"}( $message );
		if ( $echo ) {
			echo wp_kses_post( $message );
		} else {
			$this->messages[] = $message;
		}
	}

	public function success_message( $message, $echo = false ) {
		$this->message( 'success', $message, $echo );
	}

	public function error_message( $message, $echo = false ) {
		$this->message( 'error', $message, $echo );
	}

	public function warning_message( $message, $echo = false ) {
		$this->message( 'warning', $message, $echo );
	}

	public function messages() {
		foreach ( (array) $this->messages as $messages ) {
			foreach ( $messages as $message ) {
				echo wp_kses_post( $message );
			}
		}
	}

	public function validate() {

		\Valitron\Validator::lang( 'es' );
		/**
		 * @phpcs:disable WordPress.Security.NonceVerification.Missing
		 */
		$v = new \Valitron\Validator( $_POST );

		$rules = $this->get_validation_rules();

		$v->rules( $rules );

		$valid = $v->validate() ? true : $v->errors();

		return $valid;

	}

	public function post( $key ) {
		if ( empty( $_POST[ $key ] ) ) {
			return null;
		}
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	}
}
