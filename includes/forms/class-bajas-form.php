<?php
/**
 * This is the Bajas form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Bajas form controller.
 */
class Bajas_Form {

	const ID = 'cdls-bajas';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Datos de la baja</h1>
			<section class="fields">
			</section>
			<?php $form->submit_button( 'Solicitar Baja' ); ?>
		</section>
		<?php

		$form->close();

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

	}

}
