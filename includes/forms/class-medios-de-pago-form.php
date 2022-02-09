<?php
/**
 * This is the Medios de Pago form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Medios de Pago form controller.
 */
class Medios_De_Pago_Form {

	const ID = 'cdls-medios-de-pago';

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
			<h1>Datos del medio de pago</h1>
			<section class="fields">
			<?php

			$form->select(
				array(
					'name'     => 'payment_type',
					'label'    => 'Medio de Pago',
					'options'  => API()->get_payment_methods( array( 1, 2 ) ),
					'selected' => 'Seleccione',
				)
			);

			$form->text(
				array(
					'name'  => 'payment_name',
					'label' => 'Nombre del Titular',
				)
			);

			$form->number(
				array(
					'name'  => 'payment_document_number',
					'label' => 'DNI/CUIT del Titular',
				)
			);

			$form->number(
				array(
					'name'  => 'payment_card_number',
					'label' => 'Número de la tarjeta',
				)
			);

			$form->number(
				array(
					'name'  => 'payment_card_date',
					'label' => 'Vencimiento de la tarjeta',
				)
			);

			$form->number(
				array(
					'name'  => 'payment_cbu',
					'label' => 'Número de CBU',
				)
			);

			?>
			</section>
			<?php $form->submit_button( 'Actualizar Datos' ); ?>
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
