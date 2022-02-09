<?php
/**
 * This is the Bajas form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Bajas form controller.
 */
class Bajas_Form {

	const ID = 'cdls-bajas';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		$form->warning_message( 'Esta baja será efectiva una vez que reciba la confirmación definitiva por parte de Caminos de las Sierras SA. De no recibirla en 72 hs. hábiles por favor póngase en contacto con la Oficina de Atención al Usuario de Caminos de las Sierras.' );

		?>
		<section class="section">
			<h1>Datos de la baja</h1>
			<section class="fields">
				<?php
					$form->select(
						array(
							'name'     => 'dominios',
							'label'    => 'Dominio',
							'options'  => API()->get_baja_vehicles(),
							'selected' => 'Seleccione',
						)
					);
				?>
			</section>
			<?php $form->submit_button( 'Solicitar Baja' ); ?>
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
				'required'    => array( 'dominios' ),
				'domainBajas' => array( 'dominios' ),
			)
		);

		$valid = $v->validate() ? true : $v->errors();

		if ( $valid === true ) {
			$gestion_id = DB()->insert(
				<<<SQL
					INSERT INTO gestiones ( id_tipo_gestion, estado_gestion, ip_gestion ) 
					VALUES ('2', '1', :ip);

SQL,
				array(
					'ip' => $_SERVER['REMOTE_ADDR'],
				)
			);

			DB()->insert(
				<<<SQL
					INSERT INTO gestiones_bajas ( 
						nro_gestion, 
						nro_cliente,
						dominio ) 
					VALUES (
						:nro_gestion, 
						:nro_cliente,
						:dominio
					)

SQL,
				array(
					'nro_gestion' => $gestion_id,
					'nro_cliente' => API()->client_number(),
					'dominio'     => $_POST['dominios'],
				)
			);

			$client_data    = API()->get_client_data();
			$tipo_documento = $client_data['id_tipo_documento'];
			$documento      = $client_data['documento'];
			$correo         = $client_data['correo'];
			$dominio        = $_POST['dominios'];
			$fecha_gestion  = date( 'Ymdhis' );

			TXT()->generate( 'Bajas', "$tipo_documento;$documento;$correo;$dominio;$gestion_id;$fecha_gestion" );

			wp_mail( $correo, 'Caminos de las Sierras | Solicitud de Baja en trámite', "Su solicitud de baja de dominio (<b>$dominio</b>) fue enviada. En el transcurso de las próximas 72 horas hábiles, ud recibirá un email con la confirmación definitiva de la baja solicitada una vez aprobada." );

			$form->success_message( 'Su solicitud de baja de dominio fue enviada. En el transcurso de las próximas 72 horas hábiles, ud recibirá un email con la confirmación definitiva de la baja solicitada una vez aprobada.' );
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
