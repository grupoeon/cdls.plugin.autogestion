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

		$form->warning_message(
			<<<TXT
		El cambio de un medio de pago se aplica a todos los vehículos de la cuenta. 
		<br>Si usted no desea cambiar la totalidad de los vehículos por favor póngase en contacto con la Oficina de Atención al Usuario de Caminos de las Sierras.
		<br><br>El cambio será efectivo una vez que reciba la confirmación definitiva por parte de Caminos de las Sierras SA. De no recibirla en 72 hs. hábiles por favor póngase en contacto con la Oficina de Atención al Usuario.
TXT
		);

		?>
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
					'name'   => 'vencimiento_tarjeta',
					'label'  => 'Vencimiento de la tarjeta',
					'string' => 'placeholder="ej. MMAAAA"',
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

		\Valitron\Validator::lang( 'es' );
		$v = new \Valitron\Validator( $_POST );

		$v->rules(
			array(
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
				'lengthBetween' => array(
					array( 'nro_tarjeta', 14, 16 ),
				),
				'length'        => array(
					array( 'vencimiento_tarjeta', 6 ),
					array( 'nro_cbu', 22 ),
				),

			)
		);

		$v->labels(
			array(
				'id_medio_de_pago' => 'Medio de Pago',
			)
		);

		$valid = $v->validate() ? true : $v->errors();

		if ( $valid === true ) {
			$gestion_id = DB()->insert(
				<<<SQL
					INSERT INTO gestiones ( id_tipo_gestion, estado_gestion, ip_gestion ) 
					VALUES ('3', '1', :ip);

SQL,
				array(
					'ip' => $_SERVER['REMOTE_ADDR'],
				)
			);

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
					'nro_gestion'             => $gestion_id,
					'nro_cliente'             => API()->client_number(),
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
