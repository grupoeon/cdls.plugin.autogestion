<?php
/**
 * This is the Modificaciones cronjob.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Modificaciones_Cronjob extends Cronjob {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_id() {
		return 'modificaciones';
	}

	public function get_frecuency_in_seconds() {
		return MINUTE_IN_SECONDS * 5;
	}

	const MODIFICACIONES_FOLDER = ABSPATH . 'proytec/peajes/__test_modificaciones';
	const CLIENTS_TABLE         = '__test_clientes';
	const VEHICLES_TABLE        = '__test_vehiculos';
	const MAX_FILES_PER_RUN     = 1;


	/**
	 * 1. Get list of all modification files.
	 * 2. Sort the list by filename date format.
	 * 3. Get the first file.
	 * 4. Load the file as an array of lines.
	 * 5. Enhance the array values to respect some formatting rules and append some derived properties.
	 * 6. For each line:
	 *      6.1. Check if the client exists, then update it or insert it.
	 *      6.2. Check if the vehicle exists, then update it or insert it.
	 *      6.3. Disable the vehicle for all other clientes (in case other clientes had the vehicle before).
	 * 7. Move processed file to a subfolder.
	 *
	 * @return void
	 */
	public function run() {

		$files = $this->get_files();
		$this->sort_files( $files );
		$file  = array_shift( $files );
		$lines = $this->get_lines( $file );

		if ( empty( $lines ) ) {
			return;
		}

		foreach ( (array) $lines as $i => $line ) {

			$client_number = $line['nro_cliente'];

			if ( $this->client_exists( $client_number ) ) {
				$this->update_client( $line );
			} else {
				$this->insert_client( $line );
			}

			$iso_band = $line['banda_iso'];

			if ( $this->vehicle_exists( $client_number, $iso_band ) ) {
				$this->update_vehicle( $line );
			} else {
				$this->insert_vehicle( $line );
			}

			$this->disable_for_everyone_else( $client_number, $iso_band );

			update_option( 'cdls_cronjob_modificaciones_last_processed_line', $i );

		}

		$this->move_file( $file );

	}


	private function client_exists( $client_number ) {

		try {
			$clients_table = self::CLIENTS_TABLE;

			$db   = DB()->db();
			$stmt = $db->prepare( "SELECT COUNT(id) FROM $clients_table WHERE nro_cliente = :client_number" );
			$stmt->execute(
				array(
					'client_number' => $client_number,
				)
			);

			return (bool) $stmt->fetchColumn();
		} catch ( \PDOException $e ) {
			return null;
		}

	}

	private function insert_client( $line ) {

		$clients_table       = self::CLIENTS_TABLE;
		$client_number       = $line['nro_cliente'];
		$document_type_id    = $line['id_tipo_documento'];
		$document            = $line['documento'];
		$last_name           = 5 === intval( $document_type_id ) ? $line['apellido'] : null;
		$name                = 5 === intval( $document_type_id ) ? $line['nombre'] : null;
		$company_name        = 2 === intval( $document_type_id ) ? $line['apellido'] : null;
		$phone               = $line['telefono'];
		$street              = $line['calle'];
		$street_number       = $line['nro_calle'];
		$postcode            = $line['codigo_postal'];
		$province_id         = $line['id_provincia'];
		$city_id             = $line['id_localidad'];
		$fiscal_condition_id = $line['id_condicion_fiscal'];

		try {
			DB()->insert(
				"INSERT INTO $clients_table (
					nro_cliente,
					id_tipo_documento,
					documento,
					apellido,
					nombre,
					razon_social,
					correo,
					telefono,
					calle,
					nro_calle,
					codigo_postal,
					id_provincia,
					id_localidad,
					id_condicion_fiscal,
					cuenta_corriente,
					registrado,
					contrasena
				) VALUES (
					:client_number,
					:document_type_id,
					:document,
					:last_name,
					:first_name,
					:company_name,
					NULL,
					:phone,
					:street,
					:street_number,
					:postcode,
					:province_id,
					:city_id,
					:fiscal_condition_id,
					0,
					0,
					NULL
				)",
				array(
					'client_number'       => $client_number,
					'document_type_id'    => $document_type_id,
					'document'            => $document,
					'last_name'           => $last_name,
					'first_name'          => $name,
					'company_name'        => $company_name,
					'phone'               => $phone,
					'street'              => $street,
					'street_number'       => $street_number,
					'postcode'            => $postcode,
					'province_id'         => $province_id,
					'city_id'             => $city_id,
					'fiscal_condition_id' => $fiscal_condition_id,
				)
			);
		} catch ( \PDOException $e ) {
			r( $line );
			r( $e->getMessage() );
		}

	}

	private function update_client( $line ) {

		$clients_table       = self::CLIENTS_TABLE;
		$client_number       = $line['nro_cliente'];
		$document_type_id    = $line['id_tipo_documento'];
		$document            = $line['documento'];
		$last_name           = 5 === intval( $document_type_id ) ? $line['apellido'] : null;
		$name                = 5 === intval( $document_type_id ) ? $line['nombre'] : null;
		$company_name        = 2 === intval( $document_type_id ) ? $line['apellido'] : null;
		$phone               = $line['telefono'];
		$street              = $line['calle'];
		$street_number       = $line['nro_calle'];
		$postcode            = $line['codigo_postal'];
		$province_id         = $line['id_provincia'];
		$city_id             = $line['id_localidad'];
		$fiscal_condition_id = $line['id_condicion_fiscal'];

		try {
			DB()->insert(
				"UPDATE `$clients_table` 
					SET id_tipo_documento = :document_type_id,
						documento = :document,
						apellido = :last_name,
						nombre = :first_name,
						razon_social = :company_name,
						telefono = :phone,
						calle = :street,
						nro_calle = :street_number,
						codigo_postal = :postcode,
						id_provincia = :province_id,
						id_localidad = :city_id,
						id_condicion_fiscal = :fiscal_condition_id
					WHERE nro_cliente = :client_number 
				",
				array(
					'client_number'       => $client_number,
					'document_type_id'    => $document_type_id,
					'document'            => $document,
					'last_name'           => $last_name,
					'first_name'          => $name,
					'company_name'        => $company_name,
					'phone'               => $phone,
					'street'              => $street,
					'street_number'       => $street_number,
					'postcode'            => $postcode,
					'province_id'         => $province_id,
					'city_id'             => $city_id,
					'fiscal_condition_id' => $fiscal_condition_id,
				)
			);
		} catch ( \PDOException $e ) {
			r( $line );
			r( $e->getMessage() );
		}

	}

	private function vehicle_exists( $client_number, $iso_band ) {
		try {
			$vehicles_table = self::VEHICLES_TABLE;

			$db   = DB()->db();
			$stmt = $db->prepare(
				"SELECT COUNT(id) 
					FROM $vehicles_table 
					WHERE nro_cliente = :client_number 
					AND banda_iso = :iso_band"
			);
			$stmt->execute(
				array(
					'client_number' => $client_number,
					'iso_band'      => $iso_band,
				)
			);

			return (bool) $stmt->fetchColumn();
		} catch ( \PDOException $e ) {
			r( $client_number . ' ' . $iso_band );
			r( $e->getMessage() );
		}
	}

	private function insert_vehicle( $line ) {

		$vehicles_table      = self::VEHICLES_TABLE;
		$client_number       = $line['nro_cliente'];
		$domain              = $line['dominio'];
		$iso_band            = $line['banda_iso'];
		$vehicle_status_id   = $line['id_estado_vehiculo'];
		$vehicle_status      = $line['estado'];
		$vehicle_brand       = $line['marca'];
		$vehicle_model       = $line['modelo'];
		$vehicle_category_id = $line['id_categoria'];

		try {
			DB()->insert(
				"INSERT INTO `$vehicles_table` (`banda_iso`, `nro_cliente`, `dominio`, `marca`, `modelo`, `id_categoria`, `id_estado_vehiculo`, `estado`) VALUES (:iso_band, :client_number, :domain, :vehicle_brand, :vehicle_model, :vehicle_category_id, :vehicle_status_id, :vehicle_status)",
				array(
					'client_number'       => $client_number,
					'domain'              => $domain,
					'iso_band'            => $iso_band,
					'vehicle_status_id'   => $vehicle_status_id,
					'vehicle_status'      => $vehicle_status,
					'vehicle_brand'       => $vehicle_brand,
					'vehicle_model'       => $vehicle_model,
					'vehicle_category_id' => $vehicle_category_id,
				)
			);
		} catch ( \PDOException $e ) {
			r( $line );
			r( $e->getMessage() );
		}

	}

	private function update_vehicle( $line ) {

		$vehicles_table      = self::VEHICLES_TABLE;
		$client_number       = $line['nro_cliente'];
		$domain              = $line['dominio'];
		$iso_band            = $line['banda_iso'];
		$vehicle_status_id   = $line['id_estado_vehiculo'];
		$vehicle_status      = $line['estado'];
		$vehicle_brand       = $line['marca'];
		$vehicle_model       = $line['modelo'];
		$vehicle_category_id = $line['id_categoria'];

		try {
			DB()->insert(
				"UPDATE `$vehicles_table`
					SET dominio = :domain,
						marca = :vehicle_brand,
						modelo = :vehicle_model,
						id_categoria = :vehicle_category_id,
						id_estado_vehiculo = :vehicle_status_id,
						estado = :vehicle_status
					WHERE banda_iso = :iso_band
					AND nro_cliente = :client_number",
				array(
					'client_number'       => $client_number,
					'domain'              => $domain,
					'iso_band'            => $iso_band,
					'vehicle_status_id'   => $vehicle_status_id,
					'vehicle_status'      => $vehicle_status,
					'vehicle_brand'       => $vehicle_brand,
					'vehicle_model'       => $vehicle_model,
					'vehicle_category_id' => $vehicle_category_id,
				)
			);
		} catch ( \PDOException $e ) {
			r( $line );
			r( $e->getMessage() );
		}

	}

	private function disable_for_everyone_else( $client_number, $iso_band ) {

		$vehicles_table = self::VEHICLES_TABLE;
		try {
			DB()->insert(
				"UPDATE `$vehicles_table`
					SET id_estado_vehiculo = 6,
						estado = 0
					WHERE banda_iso = :iso_band
					AND nro_cliente != :client_number",
				array(
					'client_number' => $client_number,
					'iso_band'      => $iso_band,
				)
			);
		} catch ( \PDOException $e ) {
			r( $client_number . ' ' . $iso_band );
			r( $e->getMessage() );
		}

	}

	private function move_file( $file ) {

		update_option( 'cdls_cronjob_modificaciones_last_processed_file', $file );
		$destination_path = self::MODIFICACIONES_FOLDER . '/procesados/';
		rename( $file, $destination_path . pathinfo( $file, PATHINFO_BASENAME ) );

	}

	private function get_files() {
		return glob( self::MODIFICACIONES_FOLDER . '/*.txt' );
	}

	private function sort_files( &$files ) {
		usort(
			$files,
			// Sorts by ascending date.
			function( $a, $b ) {

				$a_date_string = substr( basename( $a ), -16, -4 );
				$a_date        = TIME()->create_from_format( 'YmdHi', $a_date_string );

				$b_date_string = substr( basename( $b ), -16, -4 );
				$b_date        = TIME()->create_from_format( 'YmdHi', $b_date_string );

				return $a_date > $b_date;

			}
		);
	}

	private function get_lines( $file ) {
		$lines = $this->csv_to_array( $file, ';' );
		$this->enhance_lines( $lines );
		return $lines;
	}

	private function enhance_lines( &$lines ) {
		foreach ( $lines as &$line ) {
			$line['dominio']             = preg_replace( '/^_(.*)$/', '$1', $line['dominio'] );
			$line['id_estado_vehiculo']  = $this->get_vehicle_status_id( $line['estado_vehiculo'], $line );
			$line['estado']              = $this->get_vehicle_status( $line['estado_vehiculo'] );
			$line['id_condicion_fiscal'] = $this->get_fiscal_condition( $line['id_condicion_fiscal'] );
			$line['id_tipo_documento']   = $this->get_document_type( $line['tipo_documento'] );
		}
	}

	private function get_document_type( $document ) {

		switch ( $document ) {
			case 'DNI':
				return 5;
			case 'CUIT':
				return 2;
			case 'LC':
				return 4;
			case 'LE':
				return 3;
			case 'CI':
				return 1;
			case 'CUIL':
				return 6;
			default:
				return null;
		}

	}

	private function get_fiscal_condition( $fiscal_condition ) {

		switch ( $fiscal_condition ) {
			case 'ARIVAGRALA':
				return 5;
			case 'ARIVAGRALB':
				return 1;
			case 'ARIVAEXEN':
				return 2;
			case 'ARMONOTRIB':
				return 3;
			default:
				return 1;
		}

	}

	private function get_vehicle_status_id( $vehicle_status, $line ) {

		if ( 1 === $this->get_vehicle_status( $vehicle_status ) ) {
			switch ( strlen( $line['banda_iso'] ) ) {
				case 16:
					return 2;

				case 6:
				case 7:
					return 1;

				default:
					return 3;
			}
		} else {
			switch ( $line['estado_vehiculo'] ) {
				case 'Inhabilitado por Deuda':
					return 4;
				case 'Inhabilitado cambio medio pago':
					return 5;
				case 'Inhabilitado por Facturación':
					return 7;
				default:
					return 6;
			}
		}

	}

	private function get_vehicle_status( $vehicle_status ) {
		return '' === $vehicle_status ? 1 : 0;
	}



	private function get_headers() {
		return array(
			'tipo_documento',
			'documento',
			'apellido',
			'nombre',
			'calle',
			'nro_calle',
			'id_localidad',
			'id_provincia',
			'codigo_postal',
			'telefono',
			'nro_cliente',
			'nro_cliente_viejo',
			'id_condicion_fiscal',
			'correo',
			'fecha_modificacion',
			'banda_iso',
			'dominio',
			'fecha_alta_dominio',
			'fecha_baja_dominio',
			'id_categoria',
			'marca',
			'modelo',
			'estado_vehiculo',
			'empty',
		);
	}

	private function csv_to_array( $filename = '', $delimiter = ',' ) {
		if ( ! file_exists( $filename ) || ! is_readable( $filename ) ) {
			return false;
		}

		/*
		$data = array();
		if ( ( $handle = fopen( $filename, 'r' ) ) !== false ) {
			while ( ( $row = fgetcsv( $handle, 1000, $delimiter ) ) !== false ) {
				$data[] = array_combine( $this->get_headers(), $row );
			}
			fclose( $handle );
		}

		return $data;
		*/

		$csv = new \ParseCsv\Csv();
		$csv->encoding( 'Windows-1252', 'UTF-8' );
		$csv->delimiter               = $delimiter;
		$csv->heading                 = false;
		$csv->linefeed                = "\r\n";
		$csv->use_mb_convert_encoding = true;
		$csv->parseFile( $filename );
		$lines   = $csv->data;
		$headers = $this->get_headers();
		foreach ( $lines as $i => $line ) {
			$lines[ $i ] = array_combine( $headers, $line );
		}

		return $lines;

	}

}
