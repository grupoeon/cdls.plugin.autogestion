<?php
/**
 * This is the Perfil form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The Perfil form controller.
 */
class Perfil_Form {

	const ID = 'cdls-perfil';

	/**
	 * This method builds the form using Formr.
	 *
	 * @param Formr $form The Formr instance.
	 * @return void
	 */
	public static function build( $form ) {

		$form->required = '(nro_calle),(piso),(departamento)';

		$client_data = API()->get_client_data();

		$complete_data = API()->is_client_data_complete();

		if ( $complete_data === true ) {
		} else {
			$errors = $complete_data;
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

		$form->open( self::ID, self::ID, '', 'POST', 'class="cdls-form"' );

		?>
		<section class="section">
			<h1>Datos del usuario</h1>
			<section class="fields">
			<?php

			$form->select(
				array(
					'name'     => 'id_tipo_documento',
					'label'    => 'Tipo de Documento',
					'options'  => API()->get_document_types(),
					'selected' => 'Seleccione',
					'value'    => intval( $client_data['id_tipo_documento'] ),
					// 'string'   => 'disabled="true"',
				)
			);

			$form->number(
				array(
					'name'  => 'documento',
					'label' => 'Número de Documento',
					'value' => $client_data['documento'],
					// 'string' => 'disabled="true"',
				)
			);

			$form->text(
				array(
					'name'  => 'nombre',
					'label' => 'Nombre',
					'value' => $client_data['nombre'],
				)
			);

			$form->text(
				array(
					'name'  => 'apellido',
					'label' => 'Apellido',
					'value' => $client_data['apellido'],
				)
			);

			$form->text(
				array(
					'name'  => 'razon_social',
					'label' => 'Razón Social',
					'value' => $client_data['razon_social'],
				)
			);

			$form->select(
				array(
					'name'     => 'id_condicion_fiscal',
					'label'    => 'Condición Fiscal',
					'options'  => API()->get_fiscal_conditions(),
					'selected' => 'Seleccione',
					'value'    => intval( $client_data['id_condicion_fiscal'] ),
				)
			);

			$form->email(
				array(
					'name'  => 'correo',
					'label' => 'Correo electrónico',
					'value' => $client_data['correo'],
				)
			);

			$form->number(
				array(
					'name'  => 'telefono',
					'label' => 'Teléfono particular',
					'value' => $client_data['telefono'],

				)
			);

			$form->text(
				array(
					'name'  => 'calle',
					'label' => 'Calle',
					'value' => $client_data['calle'],
				)
			);

			$form->text(
				array(
					'name'  => 'nro_calle',
					'label' => 'Nro. Calle',
					'value' => $client_data['nro_calle'],
				)
			);

			$form->text(
				array(
					'name'  => 'piso',
					'label' => 'Piso',
					'value' => $client_data['piso'],
				)
			);

			$form->text(
				array(
					'name'  => 'departamento',
					'label' => 'Departamento',
					'value' => $client_data['departamento'],
				)
			);

			$form->select(
				array(
					'name'     => 'id_provincia',
					'label'    => 'Provincia',
					'options'  => API()->get_provinces(),
					'selected' => 'Seleccione',
					'value'    => intval( $client_data['id_provincia'] ),
				)
			);

			$form->select(
				array(
					'name'     => 'id_localidad',
					'label'    => 'Localidad',
					'options'  => API()->get_cities(),
					'selected' => 'Seleccione',
					'value'    => intval( $client_data['id_localidad'] ),
				)
			);

			$form->text(
				array(
					'name'  => 'codigo_postal',
					'label' => 'Código Postal',
					'value' => $client_data['codigo_postal'],
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
				return ! DB()->document_exists( $_POST['documento'], API()->client_id() );
			},
			'documento'
		)->message( 'El documento pertenece a un usuario registrado, deberá iniciar sesión.' );

		$v->rule(
			function( $field, $value, $params, $fields ) {
				return ! DB()->email_exists( $_POST['correo'], API()->client_id() );
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
			DB()->query(
				'UPDATE clientes 
				SET 
					apellido = :apellido,
					nombre = :nombre,
					razon_social = :razon_social,
					correo = :correo,
					telefono = :telefono,
					calle = :calle,
					nro_calle = :nro_calle,
					piso = :piso,
					departamento = :departamento,
					codigo_postal = :codigo_postal,
					id_provincia = :id_provincia,
					id_localidad = :id_localidad,
					id_condicion_fiscal = :id_condicion_fiscal
				WHERE id = :id_cliente',
				array(
					'id_cliente'          => API()->client_id(),
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
				)
			);

			$form->success_message( 'Tu información ha sido actualizada.' );
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
