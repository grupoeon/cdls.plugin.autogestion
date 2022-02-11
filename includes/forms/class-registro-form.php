<?php
/**
 * This is the registration form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Registro_Form extends Form {

	public function build( $form ) {

		$form->required = '(nro_calle),(piso),(departamento)';

		$form->warning_message( 'Recordá que si ya completaste estos datos alguna vez y/o diste de alta un vehículo anteriormente podés <b>recuperar tu contraseña</b> <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.' );

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Ingresá tus datos</h1>
			<section class="fields">
			<?php

			$form->select(
				array(
					'name'     => 'id_tipo_documento',
					'label'    => 'Tipo de Documento',
					'options'  => API()->get_document_types(),
					'selected' => 'Seleccione',
				)
			);

			$form->number(
				array(
					'name'  => 'documento',
					'label' => 'Número de Documento',
				)
			);

			$form->text(
				array(
					'name'  => 'nombre',
					'label' => 'Nombre',
				)
			);

			$form->text(
				array(
					'name'  => 'apellido',
					'label' => 'Apellido',
				)
			);

			$form->text(
				array(
					'name'  => 'razon_social',
					'label' => 'Razón Social',
				)
			);

			$form->select(
				array(
					'name'     => 'id_condicion_fiscal',
					'label'    => 'Condición Fiscal',
					'options'  => API()->get_fiscal_conditions(),
					'selected' => 'Seleccione',
				)
			);

			$form->email(
				array(
					'name'  => 'correo',
					'label' => 'Correo electrónico',
				)
			);

			$form->number(
				array(
					'name'  => 'telefono',
					'label' => 'Teléfono particular',

				)
			);

			$form->text(
				array(
					'name'  => 'calle',
					'label' => 'Calle',
				)
			);

			$form->text(
				array(
					'name'  => 'nro_calle',
					'label' => 'Nro. Calle',
				)
			);

			$form->text(
				array(
					'name'  => 'piso',
					'label' => 'Piso',
				)
			);

			$form->text(
				array(
					'name'  => 'departamento',
					'label' => 'Departamento',
				)
			);

			$form->select(
				array(
					'name'     => 'id_provincia',
					'label'    => 'Provincia',
					'options'  => API()->get_provinces(),
					'selected' => 'Seleccione',
				)
			);

			$form->select(
				array(
					'name'     => 'id_localidad',
					'label'    => 'Localidad',
					'options'  => API()->get_cities(),
					'selected' => 'Seleccione',
				)
			);

			$form->text(
				array(
					'name'  => 'codigo_postal',
					'label' => 'Código Postal',
				)
			);

			?>
			</section>
			<?php $form->submit_button( 'Registrarme' ); ?>
		</section>
		<?php

		$form->close();

	}

	public function submit( $form ) {

		\Valitron\Validator::lang( 'es' );
		$v = new \Valitron\Validator( $_POST );

		$v->rules(
			array(
				'in'              => array(
					array( 'id_tipo_documento', array_keys( API()->get_document_types() ) ),
					array( 'id_provincia', array_keys( API()->get_provinces() ) ),
					array( 'id_localidad', array_keys( API()->get_cities() ) ),
				),
				'numeric'         => array( 'documento', 'telefono', 'nro_calle' ),
				'optional'        => array( 'nro_calle', 'piso', 'departamento' ),
				'lengthBetween'   => array(
					array( 'documento', 7, 11 ),
				),
				'fiscalCondition' => array( 'id_condicion_fiscal' ),
				'email'           => array( 'correo' ),
				'regex'           => array(
					array( 'nombre', "/[\w \.']+/" ),
					array( 'apellido', "/[\w \.']+/" ),
					array( 'razon_social', "/[\w \.\d']+/" ),
				),
				'lengthMax'       => array(
					array( 'telefono', 10 ),
					array( 'calle', 40 ),
					array( 'nro_calle', 6 ),
					array( 'piso', 3 ),
					array( 'departamento', 3 ),
					array( 'codigo_postal', 4 ),
					array( 'apellido', 50 ),
					array( 'nombre', 50 ),
					array( 'razon_social', 80 ),
				),

			)
		);

		$v->rule(
			function( $field, $value, $params, $fields ) {
				return ! DB()->document_exists( $_POST['documento'] );
			},
			'documento'
		)->message( 'El documento pertenece a un usuario registrado, deberá iniciar sesión.' );

		$v->rule(
			function( $field, $value, $params, $fields ) {
				return ! DB()->email_exists( $_POST['correo'] );
			},
			'correo'
		)->message( 'El correo electrónico pertenece a una cuenta ya registrada, deberá ingresar otro correo electrónico.' );

		$v->labels(
			array(
				'id_tipo_documento'   => 'Tipo de Documento',
				'documento'           => 'Número de Documento',
				'id_condicion_fiscal' => 'Condición Fiscal',
			)
		);

		$valid = $v->validate() ? true : $v->errors();

		if ( $valid === true ) {

			$password = bin2hex( openssl_random_pseudo_bytes( 4 ) );
			$hash     = password_hash( $password, PASSWORD_DEFAULT );

			DB()->query(
				'INSERT INTO clientes  (
					id_tipo_documento,
					documento,
					apellido,
					nombre,
					razon_social,
					correo,
					telefono,
					calle,
					nro_calle,
					piso,
					departamento,
					codigo_postal,
					id_provincia,
					id_localidad,
					id_condicion_fiscal,
					cuenta_corriente,
					registrado,
					contrasena
				) VALUES (
					:id_tipo_documento,
					:documento,
					:apellido,
					:nombre,
					:razon_social,
					:correo,
					:telefono,
					:calle,
					:nro_calle,
					:piso,
					:departamento,
					:codigo_postal,
					:id_provincia,
					:id_localidad,
					:id_condicion_fiscal,
					0,
					1,
					:contrasena
				)',
				array(
					'id_tipo_documento'   => $_POST['id_tipo_documento'],
					'documento'           => $_POST['documento'],
					'apellido'            => $_POST['apellido'],
					'nombre'              => $_POST['nombre'],
					'razon_social'        => $_POST['razon_social'],
					'correo'              => $_POST['correo'],
					'telefono'            => $_POST['telefono'],
					'calle'               => $_POST['calle'],
					'nro_calle'           => $_POST['nro_calle'],
					'piso'                => $_POST['piso'],
					'departamento'        => $_POST['departamento'],
					'codigo_postal'       => $_POST['codigo_postal'],
					'id_provincia'        => $_POST['id_provincia'],
					'id_localidad'        => $_POST['id_localidad'],
					'id_condicion_fiscal' => $_POST['id_condicion_fiscal'],
					'contrasena'          => $hash,
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

			$form->success_message( 'Te has registrado correctamente. <br><br> Se envió tu contraseña de acceso a tu correo electrónico. <a href="/autogestion/iniciar-sesion"><b>Inicia sesión</b></a> para continuar.' );

		} else {
			$errors = $valid;
			ob_start();
			foreach ( $errors as $error ) {
				?>
				<li><?php echo esc_html( $error[0] ); ?></li>
				<?php
			}
			$error_messages = ob_get_clean();
			$message        = "Tu información tiene los siguientes errores: <ul>$error_messages</ul>";
			$form->error_message( $message );
		}

	}

}
