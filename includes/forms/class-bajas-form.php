<?php
/**
 * This is the bajas form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Bajas_Form extends Form {

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

		$this->warning_message( MSG()::BAJA_WARNING, true );

		if ( ! API()->is_client_data_complete()['valid'] ) {
			$this->error_message( MSG()::COMPLETE_PROFILE_INFO, true );
		}

		?>
		<section class="section">
			<h1>Datos de la baja</h1>
			<section class="fields">
				<?php echo $this->output_form_fields(); ?>
			</section>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<?php echo $this->form->submit_button( 'Solicitar Baja' ); ?>
		</section>
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		echo $this->form->select(
			array(
				'name'     => 'dominios',
				'label'    => 'Dominio',
				'options'  => API()->get_baja_vehicles(),
				'selected' => 'Seleccione',
			)
		);

	}

	public static function get_validation_rules( $data ) {
		return array(
			'required'    => array( 'dominios' ),
			'domainBajas' => array( 'dominios' ),
		);
	}
	public static function get_validation_labels() {
		return array( 'dominios' => 'Dominio' );
	}

	/**
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	 */
	public function submit() {

		$procedure_id = DB()->new_procedure( 2 );

		$dominio = sanitize_text_field( wp_unslash( $_POST['dominios'] ) );

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
				'nro_gestion' => $procedure_id,
				'nro_cliente' => API()->client_number(),
				'dominio'     => $dominio,
			)
		);

		$client_data    = API()->get_client_data();
		$tipo_documento = $client_data['id_tipo_documento'];
		$documento      = $client_data['documento'];
		$correo         = $client_data['correo'];
		$fecha_gestion  = TIME()->now( 'Ymdhis' );

		TXT()->generate( 'Bajas', "$tipo_documento;$documento;$correo;$dominio;$procedure_id;$fecha_gestion" );

		wp_mail(
			$correo,
			MSG()::EMAIL_BAJA_SUBJECT,
			sprintf( MSG()::EMAIL_BAJA_CONTENT, $dominio )
		);

		$this->success_message( MSG()::BAJA_IN_PROGRESS );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in() && API()->is_client_data_complete()['valid'];
	}

}
