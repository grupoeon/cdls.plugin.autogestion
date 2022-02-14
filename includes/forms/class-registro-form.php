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

	/**
	 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		$this->form->required = '(nro_calle),(piso),(departamento)';

		$this->warning_message( MSG()::REMEMBER_OLD_CLIENTS, true );

		echo $this->form->open(
			$this->form->id,
			$this->form->name,
			'',
			'POST',
			'class="cdls-form"'
		);

		?>
		<section class="section">
			<h1>Ingresá tus datos</h1>
			<section class="fields">
			<?php $this->output_form_fields(); ?>
			</section>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<?php echo $this->form->submit_button( 'Registrarme' ); ?>
		</section>
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		$profile_handler = FORM()->get_form_handler( 'perfil', $this->form );
		$profile_handler->output_form_fields();

	}

	public static function get_validation_rules( $data ) {

		$profile_handler = FORM()->get_form_handler( 'perfil', new \Formr\Formr() );
		$profile_rules   = $profile_handler::get_validation_rules( $data );
		unset( $profile_rules['document_exists_except_owner'] );
		unset( $profile_rules['email_exists_except_owner'] );
		$profile_rules['document_exists'] = array( 'documento' );
		$profile_rules['email_exists']    = array( 'correo' );
		return $profile_rules;

	}


	public static function get_validation_labels() {

		$profile_handler = FORM()->get_form_handler( 'perfil', new \Formr\Formr() );
		return $profile_handler::get_validation_labels();

	}

	public function submit() {

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
			MSG()::MAIL_REGISTRATION_SUBJECT,
			sprintf( MSG()::MAIL_REGISTRATION_CONTENT, $password )
		);

		$this->success_message( MSG()::REGISTERED_SUCCESSFULLY );

	}

	public function current_user_can_submit() {
		return ! AG()->is_client_logged_in();
	}

}
