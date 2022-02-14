<?php
/**
 * This is the medios de pago form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Medios_De_Pago_Form extends Form {

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

		$this->warning_message( MSG()::CMP_WARNING, true );

		if ( ! API()->is_client_data_complete()['valid'] ) {
			$this->error_message( MSG()::COMPLETE_PROFILE_INFO, true );
		}

		?>
		<section class="section">
			<h1>Datos del medio de pago</h1>
			<section class="fields">
			<?php $this->output_form_fields(); ?>
			</section>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<?php echo $this->form->submit_button( 'Actualizar Datos' ); ?>
		</section>
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		echo $this->form->select(
			array(
				'name'     => 'id_medio_de_pago',
				'label'    => 'Medio de Pago',
				'options'  => API()->get_payment_methods( array( 1, 2 ) ),
				'selected' => 'Seleccione',
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'nombre',
				'label' => 'Nombre del Titular',
			)
		);

		echo $this->form->number(
			array(
				'name'  => 'documento',
				'label' => 'DNI/CUIT del Titular',
			)
		);

		echo $this->form->number(
			array(
				'name'  => 'nro_tarjeta',
				'label' => 'Número de la tarjeta',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'vencimiento_tarjeta_ano',
				'label'  => 'Vencimiento Año (AAAA)',
				'string' => 'placeholder="ej. 2028"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'vencimiento_tarjeta_mes',
				'label'  => 'Vencimiento Mes (MM)',
				'string' => 'placeholder="ej. 04"',
			)
		);

		echo $this->form->number(
			array(
				'name'  => 'nro_cbu',
				'label' => 'Número de CBU',
			)
		);

	}

	public static function get_validation_rules( $data ) {

		return array_merge_recursive(
			array(
				'required'      => array( 'id_medio_de_pago', 'nombre', 'documento' ),
				'in'            => array(
					array( 'id_medio_de_pago', array( 1, 2, 3, 4, 5, 6, 7, 8, 9 ) ),
				),
				'numeric'       => array( 'documento' ),
				'lengthBetween' => array(
					array( 'documento', 7, 11 ),
				),
				'lengthMax'     => array(
					array( 'nombre', 50 ),
				),
			),
			V()->when(
				$data,
				array(
					'required' => array( 'id_medio_de_pago' ),
					'in'       => array(
						array( 'id_medio_de_pago', array( 1, 2, 3, 4, 5, 6, 7, 8 ) ),
					),
				),
				array(
					'required'      => array(
						'nro_tarjeta',
						'vencimiento_tarjeta_ano',
						'vencimiento_tarjeta_mes',
					),
					'length'        => array(
						array( 'vencimiento_tarjeta_ano', 4 ),
						array( 'vencimiento_tarjeta_mes', 2 ),
					),
					'lengthBetween' => array(
						array( 'nro_tarjeta', 14, 16 ),
					),
				)
			),
			V()->when(
				$data,
				array(
					'required' => array( 'id_medio_de_pago' ),
					'in'       => array(
						array( 'id_medio_de_pago', array( 9 ) ),
					),
				),
				array(
					'required' => array( 'nro_cbu' ),
					'length'   => array(
						array( 'nro_cbu', 22 ),
					),
				)
			)
		);

	}

	public static function get_validation_labels() {
		return array(
			'id_medio_de_pago'        => 'Medio de Pago',
			'vencimiento_tarjeta_ano' => 'Vencimiento Tarjeta Año',
		);
	}

	/**
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	 * @phpcs:disable WordPress.PHP.DisallowShortTernary.Found
	 */
	public function submit() {

		$procedure_id = DB()->new_procedure( 3 );

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
					INSERT INTO gestiones_medios_de_pago ( 
						nro_gestion, 
						nro_cliente,
						id_medio_de_pago,
						nro_medio_de_pago,
						vencimiento_tarjeta,
						nombre_medio_de_pago,
						documento_medio_de_pago  ) 
					VALUES (
						:nro_gestion, 
						:nro_cliente,
						:id_medio_de_pago,
						:nro_medio_de_pago,
						:vencimiento_tarjeta,
						:nombre_medio_de_pago,
						:documento_medio_de_pago 
					);

SQL,
				array(
					'nro_gestion'             => $procedure_id,
					'nro_cliente'             => API()->client_number(),
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
		$tipo_documento      = $client_data['id_tipo_documento'];
		$documento           = $client_data['documento'];
		$correo              = $client_data['correo'];
		$tipo_medio_de_pago  = 9 === $id_medio_de_pago
								? 2
								: 1;
		$vencimiento_tarjeta = $vencimiento_tarjeta ?: '';
		$fecha_gestion       = TIME()->now( 'Ymdhis' );

		TXT()->generate( 'CMP', "$tipo_documento;$documento;$correo;$tipo_medio_de_pago;$nro_medio_de_pago;$vencimiento_tarjeta;$nombre_medio_de_pago;$documento_medio_de_pago;$procedure_id;$fecha_gestion" );

		wp_mail( $correo, MSG()::EMAIL_CMP_SUBJECT, MSG()::EMAIL_CMP_CONTENT );

		$this->success_message( MSG()::CMP_IN_PROGRESS );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in() && API()->is_client_data_complete()['valid'];
	}

}
