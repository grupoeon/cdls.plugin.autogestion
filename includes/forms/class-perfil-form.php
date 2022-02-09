<?php
/**
 * This is the Perfil form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Perfil form controller.
 */
class Perfil_Form {

	const ID = 'cdls-perfil';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$client_data = API()->get_client_data();
		var_dump( $client_data );

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Datos del usuario</h1>
			<section class="fields">
			<?php

			$form->select(
				array(
					'name'     => 'document_type',
					'label'    => 'Tipo de Documento',
					'options'  => API()->get_document_types(),
					'selected' => 'Seleccione',
					'value'    => $client_data['id_tipo_documento'],
				)
			);

			$form->number(
				array(
					'name'  => 'document_number',
					'label' => 'Número de Documento',
					'value' => $client_data['documento'],
				)
			);

			$form->text(
				array(
					'name'  => 'name',
					'label' => 'Nombre',
					'value' => $client_data['nombre'],
				)
			);

			$form->text(
				array(
					'name'  => 'last_name',
					'label' => 'Apellido',
					'value' => $client_data['apellido'],
				)
			);

			$form->text(
				array(
					'name'  => 'company_name',
					'label' => 'Razón Social',
					'value' => $client_data['razon_social'],
				)
			);

			$form->select(
				array(
					'name'     => 'fiscal_condition',
					'label'    => 'Condición Fiscal',
					'options'  => API()->get_fiscal_conditions(),
					'selected' => 'Seleccione',
					'value'    => $client_data['id_condicion_fiscal'],
				)
			);

			$form->email(
				array(
					'name'  => 'email',
					'label' => 'Correo electrónico',
					'value' => $client_data['correo'],
				)
			);

			$form->number(
				array(
					'name'  => 'phone',
					'label' => 'Teléfono particular',
					'value' => $client_data['telefono'],

				)
			);

			$form->text(
				array(
					'name'  => 'street',
					'label' => 'Calle',
					'value' => $client_data['calle'],
				)
			);

			$form->text(
				array(
					'name'  => 'street_number',
					'label' => 'Nro. Calle',
					'value' => $client_data['nro_calle'],
				)
			);

			$form->text(
				array(
					'name'  => 'floor',
					'label' => 'Piso',
					'value' => $client_data['piso'],
				)
			);

			$form->text(
				array(
					'name'  => 'apartment',
					'label' => 'Departamento',
					'value' => $client_data['departamento'],
				)
			);

			$form->select(
				array(
					'name'     => 'province',
					'label'    => 'Provincia',
					'options'  => API()->get_provinces(),
					'selected' => 'Seleccione',
					'value'    => $client_data['id_provincia'],
				)
			);

			$form->select(
				array(
					'name'     => 'city',
					'label'    => 'Localidad',
					'options'  => API()->get_cities(),
					'selected' => 'Seleccione',
					'value'    => $client_data['id_localidad'],
				)
			);

			$form->text(
				array(
					'name'  => 'postcode',
					'label' => 'Código Postal',
					'value' => $client_data['codigo_postal'],
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
