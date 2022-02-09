<?php
/**
 * This is the login form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The login form controller.
 */
class Iniciar_Sesion_Form {

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		// @phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['not_logged_in'] ) ) {
			$form->error_message( 'Por favor, iniciá sesión para poder acceder a los servicios de autogestión.' );
		}

		$form->warning_message(
			<<<MSG
Para iniciar sesión primero debés registrarte. Si aún no lo hiciste, podés hacerlo de dos maneras:
<ol>
<li>Si <b>aún no sos cliente nuestro</b> y querés registrar tu primer vehículo, <a href="/autogestion/registrarse"><b>registrate desde aquí</b></a>.</li>
<li>Si <b>sos cliente y tenés vehículos registrados</b> con nosotros, recuperá tu contraseña <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.</li>
</ol>
MSG
		);

		?>

		<section class="cdls-form login">
			<section class="section">
				<h1>Iniciar Sesión</h1>
				<section class="fields">
				<?php

				// type(.*) => name,label,value,id.
				// keys must be unique, key must start with type, then it can be anything, ex. text2.
				$array = array(
					'email'    => 'email,Correo Electrónico,,email,placeholder="Ingresá tu correo electrónico" style="margin-bottom:1rem;"',
					'password' => 'password,Contraseña,,password,placeholder="Ingresá tu contraseña"',
					'submit'   => 'login,,Ingresar,login',
				);

				$form->fastform( $array );

				?>
				</section>
			</section>
		</section>
		<?php

	}

	/**
	 * This method validates & processes submitted data using Formr.
	 * It should also print success or error messages.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function on_submit( $form ) {

		$rules = array(
			'email'    => array( 'Correo electrónico', 'required' ),
			'password' => array( 'Contraseña', 'required' ),
		);

		$data = $form->fastpost( $rules );

		$response = AG()->log_in( $data['email'], $data['password'] );

		if ( $response['success'] ) {
			$form->success_message( $response['data']['message'] );
		} else {
			$form->error_message( $response['data']['message'] );
		}

	}

}
