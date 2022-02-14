<?php
/**
 * This is the profile form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Perfil_Form extends Form {

	/**
	 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		$this->form->required = '(nro_calle),(piso),(departamento)';

		$client_data_complete = API()->is_client_data_complete();

		if ( ! $client_data_complete['valid'] ) {
			FORM()->add_error_messages_from_validation( $this, $client_data_complete['errors'] );
		}

		echo $this->form->open(
			$this->form->id,
			$this->form->name,
			'',
			'POST',
			'class="cdls-form"'
		);

		?>
		<section class="section">
			<h1>Datos del usuario</h1>
			<section class="fields">
				<?php $this->output_form_fields(); ?>
			</section>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<?php echo $this->form->submit_button( 'Actualizar Datos' ); ?>
		</section>
		<?php

		echo $this->form->close();

	}

	public function output_form_fields() {

		$client_data = API()->get_client_data();

		echo $this->form->select(
			array(
				'name'     => 'id_tipo_documento',
				'label'    => 'Tipo de Documento',
				'options'  => API()->get_document_types(),
				'selected' => 'Seleccione',
				'value'    => intval( $client_data['id_tipo_documento'] ),
				// 'string'   => 'disabled="true"',
			)
		);

		echo $this->form->number(
			array(
				'name'  => 'documento',
				'label' => 'Número de Documento',
				'value' => $client_data['documento'],
				// 'string' => 'disabled="true"',
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'nombre',
				'label' => 'Nombre',
				'value' => $client_data['nombre'],
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'apellido',
				'label' => 'Apellido',
				'value' => $client_data['apellido'],
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'razon_social',
				'label' => 'Razón Social',
				'value' => $client_data['razon_social'],
			)
		);

		echo $this->form->select(
			array(
				'name'     => 'id_condicion_fiscal',
				'label'    => 'Condición Fiscal',
				'options'  => API()->get_fiscal_conditions(),
				'selected' => 'Seleccione',
				'value'    => intval( $client_data['id_condicion_fiscal'] ),
			)
		);

		echo $this->form->email(
			array(
				'name'  => 'correo',
				'label' => 'Correo electrónico',
				'value' => $client_data['correo'],
			)
		);

		echo $this->form->number(
			array(
				'name'  => 'telefono',
				'label' => 'Teléfono particular',
				'value' => $client_data['telefono'],

			)
		);

		echo $this->form->text(
			array(
				'name'  => 'calle',
				'label' => 'Calle',
				'value' => $client_data['calle'],
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'nro_calle',
				'label' => 'Nro. Calle',
				'value' => $client_data['nro_calle'],
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'piso',
				'label' => 'Piso',
				'value' => $client_data['piso'],
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'departamento',
				'label' => 'Departamento',
				'value' => $client_data['departamento'],
			)
		);

		echo $this->form->select(
			array(
				'name'     => 'id_provincia',
				'label'    => 'Provincia',
				'options'  => API()->get_provinces(),
				'selected' => 'Seleccione',
				'value'    => intval( $client_data['id_provincia'] ),
			)
		);

		echo $this->form->select(
			array(
				'name'     => 'id_localidad',
				'label'    => 'Localidad',
				'options'  => API()->get_cities(),
				'selected' => 'Seleccione',
				'value'    => intval( $client_data['id_localidad'] ),
			)
		);

		echo $this->form->text(
			array(
				'name'  => 'codigo_postal',
				'label' => 'Código Postal',
				'value' => $client_data['codigo_postal'],
			)
		);

	}

	public static function get_validation_rules( $data ) {

		return array(
			'required'                     => array( 'id_tipo_documento', 'documento', 'id_condicion_fiscal', 'correo', 'telefono', 'calle', 'id_provincia', 'id_localidad', 'codigo_postal' ),
			'in'                           => array(
				array( 'id_tipo_documento', array_keys( API()->get_document_types() ) ),
				array( 'id_provincia', array_keys( API()->get_provinces() ) ),
				array( 'id_localidad', array_keys( API()->get_cities() ) ),
			),
			'numeric'                      => array( 'documento', 'telefono', 'nro_calle' ),
			'optional'                     => array( 'nro_calle', 'piso', 'departamento' ),
			'lengthBetween'                => array(
				array( 'documento', 7, 11 ),
			),
			'fiscalCondition'              => array( 'id_condicion_fiscal' ),
			'email'                        => array( 'correo' ),
			'regex'                        => array(
				array( 'nombre', "/[\w \.']+/" ),
				array( 'apellido', "/[\w \.']+/" ),
				array( 'razon_social', "/[\w \.\d']+/" ),
			),
			'lengthMax'                    => array(
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
			'document_exists_except_owner' => array( 'documento' ),
			'email_exists_except_owner'    => array( 'correo' ),
		);

	}

	public static function get_validation_labels() {

		return array(
			'id_tipo_documento'   => 'Tipo de Documento',
			'id_condicion_fiscal' => 'Condición Fiscal',
			'id_provincia'        => 'Provincia',
			'id_localidad'        => 'Localidad',
			'documento'           => 'Número de Documento',
		);

	}

	public function submit() {

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

		$client_data         = API()->get_client_data();
		$tipo_documento      = $client_data['id_tipo_documento'];
		$documento           = $client_data['documento'];
		$correo              = $client_data['correo'];
		$fecha_modificacion  = date( 'Ymdhis' );
		$apellido            = $tipo_documento == 2 ? $client_data['razon_social'] : $client_data['apellido'];
		$nombre              = $tipo_documento == 5 ? $client_data['nombre'] : '';
		$telefono            = $client_data['telefono'];
		$calle               = $client_data['calle'];
		$nro_calle           = $client_data['nro_calle'];
		$piso                = $client_data['piso'];
		$departamento        = $client_data['departamento'];
		$id_localidad        = $client_data['id_localidad'];
		$id_provincia        = $client_data['id_provincia'];
		$codigo_postal       = $client_data['codigo_postal'];
		$id_condicion_fiscal = $client_data['id_condicion_fiscal'];

		TXT()->generate( 'Datos', "$tipo_documento;$documento;$correo;$apellido;$nombre;$telefono;$calle;$nro_calle;$piso;$departamento;$id_localidad;$id_provincia;$codigo_postal;$id_condicion_fiscal;$fecha_modificacion" );

		$this->success_message( 'Tu información ha sido actualizada.' );

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in();
	}

}
