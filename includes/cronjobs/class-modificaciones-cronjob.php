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

	const MODIFICACIONES_FOLDER = ABSPATH . 'proytec/peajes/modificaciones';
	const CLIENTS_TABLE         = 'TEST_clientes';
	const VEHICLES_TABLE        = 'TEST_vehiculos';
	const MAX_FILES_PER_RUN     = 5;

	public function get_id() {
		return 'modificaciones';
	}

	public function get_frecuency_in_seconds() {
		return MINUTE_IN_SECONDS * 5;
	}

	public function run() {

		$paths = glob( self::MODIFICACIONES_FOLDER . '/*.txt' );
		$this->sort_by_filename_date( $paths );
		$paths = array_slice( $paths, 0, self::MAX_FILES_PER_RUN );

		foreach ( $paths as $path ) {
			$this->import_updates( $path );
		}

	}

	/**
	 * @phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fclose
	 */
	private function import_updates( $path ) {

		$handle = $this->utf8_fopen_read( $path );

		if ( $handle ) {
			while ( ( $data = fgetcsv( $handle, null, ';', '"' ) ) !== false ) {
				$this->import_update( $data, $path );
			}

			fclose( $handle );
		} else {
			return;
		}

	}

	private function import_update( $data, $path ) {
		$data = array_combine(
			$this->get_headers(),
			$data
		);

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

	private function sort_by_filename_date( &$paths ) {
		usort(
			$paths,
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

	private function utf8_fopen_read( $fileName ) {
		$fc     = iconv( 'windows-1250', 'utf-8', file_get_contents( $fileName ) );
		$handle = fopen( 'php://memory', 'rw' );
		fwrite( $handle, $fc );
		fseek( $handle, 0 );
		return $handle;
	}
}
