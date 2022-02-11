<?php
/**
 * This is the registration form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Iniciar_Sesion_Form extends Form {

	/**
	 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		// @phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['not_logged_in'] ) ) {
			$this->error_message( MSG()::NOT_LOGGED_IN_REFERRAL, true );
		}

		$this->warning_message( MSG()::REGISTRATION_INFO, true );

		?>

		<section class="cdls-form login">
			<section class="section">
				<h1>Iniciar Sesión</h1>
				<section class="fields">
				<?php $this->output_form_fields(); ?>
				</section>
			</section>
		</section>
		<?php

	}

	public function output_form_fields() {

		$array = array(
			'email'    => 'email,Correo Electrónico,,email,placeholder="Ingresá tu correo electrónico" style="margin-bottom:1rem;"',
			'password' => 'password,Contraseña,,password,placeholder="Ingresá tu contraseña"',
			'hidden'   => array(
				'name'  => 'cdls_form_id',
				'value' => $this->form->id,
			),
			'submit'   => 'login,,Ingresar,login',
		);

		/**
		 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		 */
		echo $this->form->fastform( $array );

	}

	public function get_validation_rules() {

		return array(
			'required' => array( 'email', 'password' ),
			'email'    => array( 'email' ),
		);

	}

	public function submit() {

		$response = AG()->log_in(
			$this->post( 'email' ),
			$this->post( 'password' )
		);

		if ( $response['success'] ) {
			$this->success_message( $response['data']['message'] );
		} else {
			$this->error_message( $response['data']['message'] );
		}

	}

	public function current_user_can_submit() {
		return true;
		//return AG()->is_client_logged_in();
	}

}
