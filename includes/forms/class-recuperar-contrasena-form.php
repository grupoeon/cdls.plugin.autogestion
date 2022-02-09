<?php
/**
 * This is the password recovery for standing clients form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The password recovery form controller.
 */
class Recuperar_Contrasena_Form {

	const ID = 'cdls-recuperar-contrasena';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->warning_message( 'Para recuperar tu contraseña necesitamos <b>verificar tu identidad</b>.<br><br>Ingresá el <b>número de DNI/CUIT</b> con el que te registraste y el <b>dominio/patente</b> de un vehículo que hayas registrado (no importa si fue dado de baja).' );

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Verificá tu identidad</h1>
			<section class="fields">
				<?php

				$form->number(
					array(
						'name'  => 'document_number',
						'label' => 'Número de DNI/CUIT',
					)
				);

				$form->text(
					array(
						'name'  => 'vehicle_domain',
						'label' => 'Dominio',
					)
				);

				?>
			</section>
		</section>
		<section class="section">
			<h1>Datos de recuperación</h1>
			<section class="fields">
				<?php

				$form->email(
					array(
						'name'  => 'email',
						'label' => 'Correo electrónico',
					)
				);

				$form->email(
					array(
						'name'  => 'email_confirm',
						'label' => 'Correo electrónico (confirmar)',
					)
				);

				?>
			</section>
			<?php $form->submit_button( 'Recuperar Contraseña' ); ?>
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
