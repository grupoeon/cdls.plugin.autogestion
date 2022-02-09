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

		if ( API()->is_client_data_complete() === true ) :

			?>
		<section class="section">
			<h1>Datos del vehículo</h1>
			<section class="fields">
			<?php

			$form->text(
				array(
					'name'  => 'marca',
					'label' => 'Marca',
				)
			);

			$form->text(
				array(
					'name'  => 'modelo',
					'label' => 'Modelo',
				)
			);

			$form->text(
				array(
					'name'   => 'dominio',
					'label'  => 'Dominio',
					'string' => 'placeholder="ej. AA000AA ó AAA000"',
				)
			);

			$form->text(
				array(
					'name'   => 'dominio_confirmar',
					'label'  => 'Dominio (confirmar)',
					'string' => 'placeholder="ej. AA000AA ó AAA000"',

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
							'name'  => 'id_categoria',
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
					'name'     => 'id_medio_de_pago',
					'label'    => 'Medio de Pago',
					'options'  => API()->get_payment_methods( array( 1, 2 ) ),
					'selected' => 'Seleccione',
				)
			);

			$form->text(
				array(
					'name'  => 'nombre',
					'label' => 'Nombre del Titular',
				)
			);

			$form->number(
				array(
					'name'  => 'documento',
					'label' => 'DNI/CUIT del Titular',
				)
			);

			$form->number(
				array(
					'name'  => 'nro_tarjeta',
					'label' => 'Número de la tarjeta',
				)
			);

			$form->number(
				array(
					'name'  => 'vencimiento_tarjeta',
					'label' => 'Vencimiento de la tarjeta',
				)
			);

			$form->number(
				array(
					'name'  => 'nro_cbu',
					'label' => 'Número de CBU',
				)
			);

			?>
			</section>
			<?php $form->submit_button( 'Solicitar Alta' ); ?>
		</section>
			<?php

		else :
			$form->error_message( 'Debés completar los datos de tu perfil antes de dar de alta un vehículo. <a href="/autogestion/mi-perfil"><b>Ver perfil</b></a>.' );
		endif;

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

		\Valitron\Validator::lang( 'es' );
		$v = new \Valitron\Validator( $_POST );

		$v->rules(
			array(
				'required'      => array(
					'dominio',
					'marca',
					'modelo',
					'dominio_confirmar',
					'id_categoria',
				),
				'in'            => array(
					array( 'id_categoria', array( 2, 3, 4, 5, 6, 7 ) ),
					array( 'id_medio_de_pago', array( 1, 2, 3, 4, 5, 6, 7, 8, 9 ) ),
				),
				'domain'        => array( 'dominio', 'dominio_confirmar' ),
				'alphaNum'      => array( 'marca', 'modelo' ),
				'numeric'       => array( 'documento' ),
				'lengthBetween' => array(
					array( 'documento', 7, 11 ),
				),
				'lengthMax'     => array(
					array( 'nombre', 50 ),
				),
				'lengthBetween' => array(
					array( 'nro_tarjeta', 14, 16 ),
				),
				'length'        => array(
					array( 'vencimiento_tarjeta', 6 ),
					array( 'nro_cbu', 22 ),
				),
				'equals'        => array(
					array( 'dominio', 'dominio_confirmar' ),
				),

			)
		);

		$v->labels(
			array(
				'id_medio_de_pago'  => 'Medio de Pago',
				'dominio_confirmar' => 'Dominio (confirmar)',
			)
		);

		$valid = $v->validate() ? true : $v->errors();

		if ( $valid === true ) {
			$gestion_id = DB()->insert(
				<<<SQL
					INSERT INTO gestiones ( id_tipo_gestion, estado_gestion, ip_gestion ) 
					VALUES ('1', '1', :ip);

SQL,
				array(
					'ip' => $_SERVER['REMOTE_ADDR'],
				)
			);

			DB()->insert(
				<<<SQL
					INSERT INTO gestiones_altas (
						nro_gestion,
						id_cliente,
						dominio,
						marca,
						modelo,
						id_categoria,
						id_medio_de_pago,
						nro_medio_de_pago,
						vencimiento_tarjeta,
						nombre_medio_de_pago,
						documento_medio_de_pago  )
					VALUES (
						:nro_gestion,
						:id_cliente,
						:dominio,
						:marca,
						:modelo,
						:id_categoria,
						:id_medio_de_pago,
						:nro_medio_de_pago,
						:vencimiento_tarjeta,
						:nombre_medio_de_pago,
						:documento_medio_de_pago
					);

			SQL,
				array(
					'nro_gestion'             => $gestion_id,
					'id_cliente'              => API()->client_id(),
					'dominio'                 => $_POST['dominio'],
					'marca'                   => $_POST['marca'],
					'modelo'                  => $_POST['modelo'],
					'id_categoria'            => $_POST['id_categoria'],
					'id_medio_de_pago'        => $_POST['id_medio_de_pago'],
					'nro_medio_de_pago'       => $_POST['nro_tarjeta'] ? $_POST['nro_tarjeta'] : $_POST['nro_cbu'],
					'vencimiento_tarjeta'     => $_POST['nro_tarjeta'] ? $_POST['vencimiento_tarjeta'] : null,
					'nombre_medio_de_pago'    => $_POST['nombre'],
					'documento_medio_de_pago' => $_POST['documento'],
				)
			);

			$form->success_message( 'Tu solicitud de cambio de medio de pago fue enviada.' );
		} else {
			$errors = $valid;
			ob_start();
			foreach ( $errors as $error ) {
				?>
				<li><?php echo esc_html( $error[0] ); ?></li>
				<?php
			}
			$error_messages = ob_get_clean();
			$message        = "Revisa los siguientes errores: <ul>$error_messages</ul>";
			$form->error_message( $message );
		}

	}

}
