<?php
/**
 * This is the password recovery for standing clients form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The password recovery form controller.
 */
class Recuperar_Contrasena_Form {

	const ID = 'cdls-recuperar-contrasena';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->warning_message( 'Para recuperar tu contraseña necesitamos <b>verificar tu identidad</b>.<br><br>Ingresá el <b>número de DNI/CUIT</b> con el que te registraste y el <b>dominio/patente</b> de un vehículo que hayas registrado (no importa si fue dado de baja).' );

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Verificá tu identidad</h1>
			<section class="fields">
				<?php

				$form->number(
					array(
						'name'  => 'documento',
						'label' => 'Número de DNI/CUIT',
					)
				);

				$form->text(
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

				$form->email(
					array(
						'name'  => 'correo',
						'label' => 'Correo electrónico',
					)
				);

				$form->email(
					array(
						'name'  => 'correo_confirmar',
						'label' => 'Correo electrónico (confirmar)',
					)
				);

				?>
			</section>
			<?php $form->submit_button( 'Recuperar Contraseña' ); ?>
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
				'numeric'       => array( 'documento' ),
				'lengthBetween' => array(
					array( 'documento', 7, 11 ),
				),
				'email'         => array(
					array( 'correo' ),
				),
				'lengthBetween' => array(
					array( 'nro_tarjeta', 14, 16 ),
				),
				'equals'        => array(
					array( 'correo', 'correo_confirmar' ),
				),
				'domainBajas'   => array( 'dominio' ),

			)
		);

		$v->labels(
			array(
				'correo_confirmar' => 'Correo electrónico (confirmar)',
			)
		);

		$valid = $v->validate() ? true : $v->errors();

		if ( $valid === true ) {

			$client_id = DB()->verify_identity( $_POST['documento'], $_POST['dominio'] );

			if ( ! $client_id ) {

				$form->error_message( 'Los datos ingresados no concuerdan con nuestros registros, por favor comunicarse con la Oficina de Atención al Usuario de Caminos de las Sierras.' );

			} else {

				if ( DB()->email_exists( $_POST['correo'] ) ) {

					$form->error_message( 'El correo ya se encuenta registrado, ingrese otro por favor.' );

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
						'Caminos de las Sierras | Clave de acceso para autogestión',
						<<<MAIL
						Bienvenido.
		
						Nos es grato comunicarle que la clave para poder ingresar a la sección autogestión del sitio https://www.caminosdelassierras.com.ar/autogestion/iniciar-sesion es:<br>
						<br>
						
						<h1><b>$password</b></h1>
						
						Si el enlace proporcionado no funciona copielo y pegelo en su barra de navegación.
						
						Esperamos que tenga una buena experiencia.
						
						Atte. Caminos de las Sierras
						
						Esta es una respuesta automática, por favor no responda este email.
		MAIL
					);

					$form->success_message( 'Te enviamos un correo con la nueva contraseña para que puedas iniciar sesión.' );

				}
			}
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
