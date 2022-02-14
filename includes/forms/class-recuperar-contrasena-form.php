<?php
/**
 * This is the registration for old clientes form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Recuperar_Contrasena_Form extends Form {

	/**
	 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		$this->warning_message( MSG()::PASSWORD_RECOVERY_EXPLANATION, true );

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
		<?php echo $this->form->submit_button( 'Recuperar Contraseña' ); ?>
		</section>

		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		?>

		<section class="section">
			<h1>Verificá tu identidad</h1>
			<section class="fields">
			<?php

			echo $this->form->number(
				array(
					'name'  => 'documento',
					'label' => 'Número de DNI/CUIT',
				)
			);

			echo $this->form->text(
				array(
					'name'  => 'dominio',
					'label' => 'Dominio',
				)
			);

			?>
			</section>
		</section>
		<section class="section">
			<h1>Datos de recuperación</h1>
			<section class="fields">
				<?php

				echo $this->form->email(
					array(
						'name'  => 'correo',
						'label' => 'Correo electrónico',
					)
				);

				echo $this->form->email(
					array(
						'name'  => 'correo_confirmar',
						'label' => 'Correo electrónico (confirmar)',
					)
				);

				?>

		<?php

	}

	public static function get_validation_rules( $data ) {

		return array(
			'required'      => array( 'documento', 'dominio', 'correo', 'correo_confirmar' ),
			'numeric'       => array( 'documento' ),
			'lengthBetween' => array(
				array( 'documento', 7, 11 ),
			),
			'email'         => array(
				array( 'correo' ),
			),
			'equals'        => array(
				array( 'correo', 'correo_confirmar' ),
			),
			'domainBajas'   => array( 'dominio' ),
		);

	}

	public static function get_validation_labels() {

		return array(
			'correo_confirmar' => 'Correo electrónico (confirmar)',
		);

	}

	public function submit() {

		$client_id = DB()->verify_identity( $_POST['documento'], $_POST['dominio'] );

		if ( ! $client_id ) {

			$this->error_message( MSG()::CLIENT_RECORDS_NOT_FOUND );

		} else {

			if ( DB()->email_exists( $_POST['correo'] ) ) {

				$this->error_message( MSG()::EMAIL_EXISTS );

			} else {

				$password = bin2hex( openssl_random_pseudo_bytes( 4 ) );
				$hash     = password_hash( $password, PASSWORD_DEFAULT );

				DB()->query(
					'UPDATE clientes 
					SET 
						correo = :correo,
						registrado = 1,
						fecha_registro = :fecha,
						contrasena = :contrasena
					WHERE id = :id',
					array(
						'id'         => $client_id,
						'correo'     => $_POST['correo'],
						'contrasena' => $hash,
						'fecha'      => date( 'Y-m-d H:i:s' ),
					)
				);

				wp_mail(
					$_POST['correo'],
					MSG()::MAIL_REGISTRATION_SUBJECT,
					sprintf( MSG()::MAIL_REGISTRATION_CONTENT, $password )
				);

				$this->success_message( MSG()::NEW_PASSWORD_SENT );

			}
		}

	}

	public function current_user_can_submit() {
		return ! AG()->is_client_logged_in();
	}

}
