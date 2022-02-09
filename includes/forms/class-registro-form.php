<?php
/**
 * This is the registration form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The registration form controller.
 */
class Registro_Form {

	const ID = 'cdls-registro-primera-vez';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->warning_message( 'Recordá que si ya completaste estos datos alguna vez y/o diste de alta un vehículo anteriormente podés <b>recuperar tu contraseña</b> <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.' );

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Ingresá tus datos</h1>
			<section class="fields">
			<?php

			$form->select(
				array(
					'name'     => 'document_type',
					'label'    => 'Tipo de Documento',
					'options'  => API()->get_document_types(),
					'selected' => 'Seleccione',
				)
			);

			$form->number(
				array(
					'name'  => 'document_number',
					'label' => 'Número de Documento',
				)
			);

			$form->text(
				array(
					'name'  => 'name',
					'label' => 'Nombre',
				)
			);

			$form->text(
				array(
					'name'  => 'last_name',
					'label' => 'Apellido',
				)
			);

			$form->text(
				array(
					'name'  => 'company_name',
					'label' => 'Razón Social',
				)
			);

			$form->select(
				array(
					'name'     => 'fiscal_condition',
					'label'    => 'Condición Fiscal',
					'options'  => API()->get_fiscal_conditions(),
					'selected' => 'Seleccione',
				)
			);

			$form->email(
				array(
					'name'  => 'email',
					'label' => 'Correo electrónico',
				)
			);

			$form->number(
				array(
					'name'  => 'phone',
					'label' => 'Teléfono particular',

				)
			);

			$form->text(
				array(
					'name'  => 'street',
					'label' => 'Calle',
				)
			);

			$form->text(
				array(
					'name'  => 'street_number',
					'label' => 'Nro. Calle',
				)
			);

			$form->text(
				array(
					'name'  => 'floor',
					'label' => 'Piso',
				)
			);

			$form->text(
				array(
					'name'  => 'apartment',
					'label' => 'Departamento',
				)
			);

			$form->select(
				array(
					'name'     => 'province',
					'label'    => 'Provincia',
					'options'  => API()->get_provinces(),
					'selected' => 'Seleccione',
				)
			);

			$form->select(
				array(
					'name'     => 'city',
					'label'    => 'Localidad',
					'options'  => API()->get_cities(),
					'selected' => 'Seleccione',
				)
			);

			$form->text(
				array(
					'name'  => 'postcode',
					'label' => 'Código Postal',
				)
			);

			?>
			</section>
			<?php $form->submit_button( 'Registrarme' ); ?>
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
