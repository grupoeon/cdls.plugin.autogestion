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

		// type(.*) => name,label,value,id.
		// keys must be unique, key must start with type, then it can be anything, ex. text2.
		$array = array(
			'email'    => 'correo,Correo Electrónico,,correo,placeholder="ejemplo@gmail.com"',
			'password' => 'contrasena,Contraseña,,contrasena',
			'submit'   => 'ingresar,,Ingresar,ingresar',
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

		$form->success_message( 'hi' );

	}

}
