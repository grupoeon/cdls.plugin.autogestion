<?php
/**
 * This is the logout form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The logout form controller.
 */
class Cerrar_Sesion_Form {

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		AG()->log_out();
		$form->success_message( 'Cerraste sesión correctamente.' );

	}

}
