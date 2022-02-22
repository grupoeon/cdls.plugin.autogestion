<?php
/**
 * This is the change password form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Cambiar_Contrasena_Form extends Form {

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

		?>

		<?php $this->output_form_fields(); ?>
		<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
		<?php echo $this->form->submit_button( 'Cambiar Contraseña' ); ?>
		</section>

		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		?>

		<section class="section">
			<h1>Cambiar contraseña</h1>
			<section class="fields">
			<?php

			echo $this->form->password(
				array(
					'name'   => 'contrasena_anterior',
					'label'  => 'Contraseña anterior',
					'string' => 'data-view-password',
				)
			);

			echo $this->form->password(
				array(
					'name'   => 'contrasena_nueva',
					'label'  => 'Contraseña nueva',
					'string' => 'data-view-password',
				)
			);

			echo $this->form->password(
				array(
					'name'   => 'contrasena_nueva_confirmar',
					'label'  => 'Contraseña nueva (confirmar)',
					'string' => 'data-view-password',
				)
			);

			?>
			</section>
		<?php

	}

	public static function get_validation_rules( $data ) {

		return array(
			'required'        => array( 'contrasena_anterior', 'contrasena_nueva' ),
			'different'       => array( array( 'contrasena_anterior', 'contrasena_nueva' ) ),
			'currentPassword' => array( 'contrasena_anterior' ),
			'equals'          => array( array( 'contrasena_nueva', 'contrasena_nueva_confirmar' ) ),
		);

	}

	public static function get_validation_labels() {

		return array(
			'contrasena_anterior'        => 'Contraseña actual',
			'contrasena_nueva'           => 'Contraseña nueva',
			'contrasena_nueva_confirmar' => 'Contraseña nueva (confirmar)',
		);

	}

	/**
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	 */
	public function submit() {

		$client_id   = API()->client_id();
		$client_data = API()->get_client_data();
		/**
		 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		 */
		$password = wp_unslash( $_POST['contrasena_nueva'] );
		$hash     = password_hash( $password, PASSWORD_DEFAULT );

		DB()->insert(
			'UPDATE clientes 
			SET 
				contrasena = :contrasena
			WHERE id = :id',
			array(
				'id'         => $client_id,
				'contrasena' => $hash,
			)
		);

		wp_mail(
			$client_data['correo'],
			MSG()::MAIL_CHANGE_PASSWORD_SUBJECT,
			sprintf( MSG()::MAIL_CHANGE_PASSWORD_CONTENT, $password )
		);

		$this->success_message( MSG()::CHANGED_PASSWORD );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in();
	}

}
