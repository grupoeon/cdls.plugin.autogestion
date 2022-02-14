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

		if ( ! API()->is_client_data_complete()['valid'] ) {
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

	public static function get_validation_rules( $data ) {

		$cmp_handler = FORM()->get_form_handler( 'medios-de-pago', new \Formr\Formr() );
		$cmp_rules   = $cmp_handler::get_validation_rules( $data );

		$rules = array(
			'required' => array(
				'marca',
				'modelo',
				'dominio',
				'dominio_confirmar',
				'id_categoria',
			),
			'in'       => array(
				array( 'id_categoria', array( 2, 3, 4, 5, 6, 7 ) ),
			),
			'alphaNum' => array( 'marca', 'modelo' ),
			'domain'   => array( 'dominio', 'dominio_confirmar' ),
			'equals'   => array(
				array( 'dominio', 'dominio_confirmar' ),
			),
		);

		return array_merge_recursive( $cmp_rules, $rules );

	}

	public static function get_validation_labels() {

		$cmp_handler = FORM()->get_form_handler( 'medios-de-pago', new \Formr\Formr() );
		$cmp_labels  = $cmp_handler::get_validation_labels();

		$labels = array(
			'dominio_confirmar' => 'Dominio (confirmar)',
		);

		return array_replace_recursive( $cmp_labels, $labels );

	}

	/**
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	 * @phpcs:disable WordPress.PHP.DisallowShortTernary.Found
	 */
	public function submit() {

		$procedure_id = DB()->new_procedure( 1 );

		$dominio      = sanitize_text_field( wp_unslash( $_POST['dominio'] ) );
		$marca        = sanitize_text_field( wp_unslash( $_POST['marca'] ) );
		$modelo       = sanitize_text_field( wp_unslash( $_POST['modelo'] ) );
		$id_categoria = intval( wp_unslash( $_POST['id_categoria'] ) );

		$id_medio_de_pago        = intval( wp_unslash( $_POST['id_medio_de_pago'] ) );
		$nro_cbu                 = sanitize_text_field( wp_unslash( $_POST['nro_cbu'] ) );
		$nro_tarjeta             = sanitize_text_field( wp_unslash( $_POST['nro_tarjeta'] ) );
		$nro_medio_de_pago       = $nro_tarjeta ?: $nro_cbu;
		$vencimiento_tarjeta_ano = intval( wp_unslash( $_POST['vencimiento_tarjeta_ano'] ) );
		$vencimiento_tarjeta_mes = sanitize_text_field( wp_unslash( $_POST['vencimiento_tarjeta_mes'] ) );
		$vencimiento_tarjeta     = $vencimiento_tarjeta_ano . $vencimiento_tarjeta_mes;
		$nombre_medio_de_pago    = sanitize_text_field( wp_unslash( $_POST['nombre'] ) );
		$documento_medio_de_pago = intval( wp_unslash( $_POST['documento'] ) );

		try {
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
					'dominio'                 => $dominio,
					'marca'                   => $marca,
					'modelo'                  => $modelo,
					'id_categoria'            => $id_categoria,
					'id_medio_de_pago'        => $id_medio_de_pago,
					'nro_medio_de_pago'       => $nro_medio_de_pago,
					'vencimiento_tarjeta'     => $vencimiento_tarjeta,
					'nombre_medio_de_pago'    => $nombre_medio_de_pago,
					'documento_medio_de_pago' => $documento_medio_de_pago,
				)
			);
		} catch ( \PDOException $e ) {

			$this->error_message( MSG()::DATABASE_ERROR );
			return;

		}

		$client_data         = API()->get_client_data();
		$tipo_documento      = intval( $client_data['id_tipo_documento'] );
		$documento           = $client_data['documento'];
		$correo              = $client_data['correo'];
		$fecha_gestion       = TIME()->now();
		$apellido            = 2 === $tipo_documento
									? $client_data['razon_social']
									: $client_data['apellido'];
		$nombre              = 5 === $tipo_documento
									? $client_data['nombre']
									: '';
		$telefono            = $client_data['telefono'];
		$calle               = $client_data['calle'];
		$nro_calle           = $client_data['nro_calle'];
		$piso                = $client_data['piso'];
		$departamento        = $client_data['departamento'];
		$id_localidad        = $client_data['id_localidad'];
		$id_provincia        = $client_data['id_provincia'];
		$codigo_postal       = $client_data['codigo_postal'];
		$id_condicion_fiscal = $client_data['id_condicion_fiscal'];

		$tipo_medio_de_pago = 9 === $id_medio_de_pago
									? 2
									: 1;
		$id_medio_de_pago   = 9 === $id_medio_de_pago
									? ''
									: $id_medio_de_pago;

		TXT()->generate( 'Altas', ";$apellido;$nombre;$tipo_documento;$documento;$correo;$telefono;;$calle;$nro_calle;$piso;$departamento;;$id_provincia;$id_localidad;$codigo_postal;$id_condicion_fiscal;;$dominio;$marca;$modelo;;;;;;$tipo_medio_de_pago;$id_medio_de_pago;$nro_medio_de_pago;$vencimiento_tarjeta_mes;$vencimiento_tarjeta_ano;$vencimiento_tarjeta_mes;$vencimiento_tarjeta_ano;;$fecha_gestion;$fecha_gestion;$id_categoria;$procedure_id;" );

		wp_mail(
			$correo,
			MSG()::EMAIL_ALTA_SUBJECT,
			sprintf( MSG()::EMAIL_ALTA_CONTENT, $dominio )
		);

		$this->success_message( MSG()::ALTA_IN_PROGRESS );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in() && API()->is_client_data_complete()['valid'];
	}

}
