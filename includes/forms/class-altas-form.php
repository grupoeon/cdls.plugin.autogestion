<?php
/**
 * This is the Altas form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Altas form controller.
 */
class Altas_Form {

	const ID = 'cdls-altas';

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
			<h1>Datos del usuario</h1>
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

			$form->checkbox(
				array(
					'name'     => 'terms',
					'value'    => 1,
					'selected' => true,
					'label'    => 'Acepto los <a 
                                                href="/terminos-y-condiciones-patente/" 
                                                target="_blank">términos y condiciones</a>.',
				)
			);

			?>
			</section>
		</section>
		<section class="section">
			<h1>Datos del vehículo</h1>
			<section class="fields">
			<?php

			$form->text(
				array(
					'name'  => 'vehicle_brand',
					'label' => 'Marca',
				)
			);

			$form->text(
				array(
					'name'  => 'vehicle_model',
					'label' => 'Modelo',
				)
			);

			$form->text(
				array(
					'name'  => 'vehicle_domain',
					'label' => 'Dominio',
				)
			);

			$form->text(
				array(
					'name'  => 'vehicle_domain_confirm',
					'label' => 'Dominio (confirmar)',
				)
			);

			?>

				<section class="vehicle-category">

				<?php
				foreach ( API()->get_vehicle_categories() as $vehicle_category ) {

					$name        = $vehicle_category['nombre'];
					$description = $vehicle_category['descripcion'];
					$value       = $vehicle_category['id'];

					$form->radio(
						array(
							'name'  => 'vehicle_category',
							'label' => "<b>$name</b>
											<img 
												src='/wp-content/uploads/vehicle_categories/$value.jpg' 
												alt='$description'
												title='$description'
											>",
							'value' => $value,
						)
					);

				}
				?>

				</section>
			</section>
		</section>
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
			<?php $form->submit_button( 'Solicitar Alta' ); ?>
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
