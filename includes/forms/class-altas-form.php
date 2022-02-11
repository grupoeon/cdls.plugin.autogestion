<?php
/**
 * This is the altas form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Altas_Form extends Form {

	/**
	 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		echo $this->form->open(
			$this->form->id,
			$this->form->name,
			'',
			'POST',
			'class="cdls-form"'
		);

		if ( true !== API()->is_client_data_complete() ) {
			$this->error_message( MSG()::COMPLETE_PROFILE_INFO, true );
		}

		?>
			<?php $this->output_form_fields(); ?>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<?php echo $this->form->submit_button( 'Solicitar Alta' ); ?>
		</section>
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		?>

		<section class="section">
			<h1>Datos del vehículo</h1>
			<section class="fields">
			<?php

			echo $this->form->text(
				array(
					'name'  => 'marca',
					'label' => 'Marca',
				)
			);

			echo $this->form->text(
				array(
					'name'  => 'modelo',
					'label' => 'Modelo',
				)
			);

			echo $this->form->text(
				array(
					'name'   => 'dominio',
					'label'  => 'Dominio',
					'string' => 'placeholder="ej. AA000AA ó AAA000"',
				)
			);

			echo $this->form->text(
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

					echo $this->form->radio(
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
				$cmp_handler = FORM()->get_form_handler( 'medios-de-pago', $this->form );
				$cmp_handler->output_form_fields();
			?>
			</section>

			<?php

	}

	public static function get_validation_rules() {

		$cmp_handler = FORM()->get_form_handler( 'medios-de-pago', new \Formr\Formr() );
		$cmp_rules   = $cmp_handler::get_validation_rules();

		$rules = array(
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
		);

		return array_merge_recursive( $cmp_rules, $rules );

	}

	public static function get_validation_labels() {

		$cmp_handler = FORM()->get_form_handler( 'medios-de-pago', new \Formr\Formr() );
		$cmp_labels  = $cmp_handler::get_validation_labels();

		$labels = array(
			'id_medio_de_pago'  => 'Medio de Pago',
			'dominio_confirmar' => 'Dominio (confirmar)',
		);

		return array_replace_recursive( $cmp_labels, $labels );

	}

	public function submit() {

		$procedure_id = DB()->new_procedure( 1 );

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
				'nro_gestion'             => $procedure_id,
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

		$client_data             = API()->get_client_data();
		$tipo_documento          = $client_data['id_tipo_documento'];
		$documento               = $client_data['documento'];
		$correo                  = $client_data['correo'];
		$fecha_gestion           = date( 'Y-m-d H:i:s' );
		$apellido                = $tipo_documento == 2 ? $client_data['razon_social'] : $client_data['apellido'];
		$nombre                  = $tipo_documento == 5 ? $client_data['nombre'] : '';
		$telefono                = $client_data['telefono'];
		$calle                   = $client_data['calle'];
		$nro_calle               = $client_data['nro_calle'];
		$piso                    = $client_data['piso'];
		$departamento            = $client_data['departamento'];
		$id_localidad            = $client_data['id_localidad'];
		$id_provincia            = $client_data['id_provincia'];
		$codigo_postal           = $client_data['codigo_postal'];
		$id_condicion_fiscal     = $client_data['id_condicion_fiscal'];
		$id_medio_de_pago        = $_POST['id_medio_de_pago'];
		$tipo_medio_de_pago      = $id_medio_de_pago == 9 ? 2 : 1;
		$id_medio_de_pago        = $id_medio_de_pago == 9 ? '' : $id_medio_de_pago;
		$vencimiento_tarjeta     = $_POST['vencimiento_tarjeta'];
		$mes_vencimiento_tarjeta = substr( $vencimiento_tarjeta, 4 );
		$ano_vencimiento_tarjeta = substr( $vencimiento_tarjeta, 0, 4 );
		$nro_medio_de_pago       = $_POST['nro_tarjeta'] ?: $_POST['nro_cbu'];
		$marca                   = $_POST['marca'];
		$dominio                 = $_POST['dominio'];
		$modelo                  = $_POST['modelo'];
		$id_categoria            = $_POST['id_categoria'];

		TXT()->generate( 'Altas', ";$apellido;$nombre;$tipo_documento;$documento;$correo;$telefono;;$calle;$nro_calle;$piso;$departamento;;$id_provincia;$id_localidad;$codigo_postal;$id_condicion_fiscal;;$dominio;$marca;$modelo;;;;;;$tipo_medio_de_pago;$id_medio_de_pago;$nro_medio_de_pago;$mes_vencimiento_tarjeta;$ano_vencimiento_tarjeta;$mes_vencimiento_tarjeta;$ano_vencimiento_tarjeta;;$fecha_gestion;$fecha_gestion;$id_categoria;$procedure_id;" );

		wp_mail(
			$correo,
			MSG()::EMAIL_ALTA_SUBJECT,
			sprintf( MSG()::EMAIL_ALTA_CONTENT, $dominio )
		);

		$this->success_message( MSG()::ALTA_IN_PROGRESS );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in() && API()->is_client_data_complete();
	}

}
