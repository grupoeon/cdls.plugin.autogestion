<?php
/**
 * This is the login form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The login form controller.
 */
class Iniciar_Sesion_Form {

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		if ( isset( $_GET['not_logged_in'] ) ) {
			$form->error_message( 'Por favor, iniciá sesión para poder acceder a los servicios de autogestión.' );
		}

		// type(.*) => name,label,value,id.
		// keys must be unique, key must start with type, then it can be anything, ex. text2.
		$array = array(
			'email'    => 'email,Correo Electrónico,,email,placeholder="Ingresá tu correo electrónico"',
			'password' => 'password,Contraseña,,password,placeholder="Ingresá tu contraseña"',
			'submit'   => 'login,,Ingresar,login',
		);

		$form->fastform( $array );

	}

	/**
	 * This method validates & processes submitted data using Formr.
	 * It should also print success or error messages.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function on_submit( $form ) {

		$rules = array(
			'email'    => array( 'Correo electrónico', 'required' ),
			'password' => array( 'Contraseña', 'required' ),
		);

		$data = $form->fastpost( $rules );

		$response = AG()->log_in( $data['email'], $data['password'] );

		if ( $response['success'] ) {
			$form->success_message( $response['data']['message'] );
		} else {
			$form->error_message( $response['data']['message'] );
		}

	}

}
