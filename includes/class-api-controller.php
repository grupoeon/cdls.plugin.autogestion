<?php
/**
 * This is the API controller.
 *
 * TODO: Turn this into a separate service.
 * Explanation: Today it makes the database calls locally, it should be a separate service
 * with token credentials, and it should render class-database-controller.php in this plugin
 * unnecessary. This class should then just query/relay requests to the actual API.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

require_once ROOT_DIR . '/includes/class-database-controller.php';

/**
 * The API controller.
 */
class API_Controller {

	/**
	 * The single instance of the class.
	 *
	 * @var API_Controller
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	protected static $_instance = null;

	/**
	 * Main API_Controller Instance.
	 *
	 * Ensures only one instance of API_Controller is loaded or can be loaded.
	 *
	 * @return API_Controller - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function get_pdf_transits_url( $period ) {

		return home_url( '/pdf-transito?period=' . urlencode( $period ) );

	}

	public function get_receipt_url( $receipt ) {

		$orden_venta   = str_pad( $receipt['orden_venta'], 8, '0', STR_PAD_LEFT );
		$punto_venta   = str_pad( $receipt['punto_venta'], 3, '0', STR_PAD_LEFT );
		$letra_factura = $receipt['tipo_factura'];
		$nro_factura   = str_pad( $receipt['nro_factura'], 8, '0', STR_PAD_LEFT );

		$input = $orden_venta . $punto_venta . $letra_factura . $nro_factura;

		return plugins_url( 'includes/receipts/receipts.php', ROOT_FILE ) . '?uid=' . md5( $input );

	}

	public function get_receipt_payment_url( $receipt ) {

		return '/autogestion/realizar-un-pago?type=receipt&id=' . rawurlencode( $receipt['orden_venta'] );

	}

	/**
	 * Logs in the user
	 *
	 * @param string $email The cliente email.
	 * @param string $password The client password.
	 * @return API_Response
	 */
	public function log_in( $email, $password ) {

		$email = filter_var( $email, FILTER_SANITIZE_EMAIL );

		if ( empty( $email ) || empty( $password ) ) {
			return $this->error( 'El usuario y la contraseña son campos requeridos.' );
		}

		$client_id     = DB()->get_client_id_by_email( $email );
		$password_hash = DB()->get_client_password_by_email( $email );

		if ( ! empty( $client_id ) && password_verify( $password, $password_hash ) ) {
			return $this->success( 'Iniciaste sesión correctamente.', array( 'id' => $client_id ) );
		} else {
			return $this->error( 'El usuario o la contraseña son incorrectos. Recordá que las contraseñas del sistema de autogestión anterior no funcionan, podés <b>recuperar tu contraseña</b> <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.' );
		}

	}

	/**
	 * Returns all the client data.
	 *
	 * @param int $client_id The client id.
	 * @return array
	 */
	public function get_client_data( $client_id = null ) {

		session_start();

		if ( empty( $client_id ) ) {
			$client_id = $_SESSION['autogestion_id'];
		}

		if ( empty( $client_id ) ) {
			return array();
		}

		return DB()->get_client_data( $client_id );

	}

	/**
	 * Returns client id $_SESSION.
	 *
	 * @return array
	 */
	public function client_id() {

		session_start();

		return $_SESSION['autogestion_id'];

	}

	/**
	 * Returns client number.
	 *
	 * @return array
	 */
	public function client_number() {

		$client_id = $this->client_id();
		return DB()->get_client_number( $client_id );

	}

	public function get_baja_vehicles() {

		return $this->rows_to_key_value(
			DB()->fetch_all(
				<<<SQL
			SELECT id,dominio FROM vehiculos 
				WHERE id_estado_vehiculo IN (1,2,3,5) 
				AND nro_cliente = :nro_cliente;
SQL
				,
				array(
					'nro_cliente' => API()->client_number(),
				)
			),
			'dominio',
			'dominio'
		);

	}

	/**
	 * Checks if client data is complete.
	 *
	 * @return true|array
	 */

	public function get_client_status( $client_id = null ) {

		session_start();

		if( empty( $client_id ) ) {
			$client_id = $_SESSION['autogestion_id'];
		}

		if( empty( $client_id ) ){
			return false;
		}

		

		return DB()->get_client_status($client_id);
	}

	 public function check_client_status( $client_id = null ) {
		 
		session_start();

		if( empty( $client_id ) ) {
			$client_id = $_SESSION['autogestion_id'];
		}

		if( empty( $client_id ) ){
			return false;
		}

		$client_status = $this->get_client_status($client_id);

		if( empty( $client_status ) ){
			return false;
		}else{
			return true;
		}


	 }

	public function is_client_data_complete( $client_id = null ) {

		session_start();

		if ( empty( $client_id ) ) {
			$client_id = $_SESSION['autogestion_id'];
		}

		if ( empty( $client_id ) ) {
			return false;
		}

		$client_data = $this->get_client_data( $client_id );

		\Valitron\Validator::lang( 'es' );
		$v = new \Valitron\Validator( $client_data );

		$form_handler = FORM()->get_form_handler( 'perfil', new \Formr\Formr() );

		$v->rules( $form_handler::get_validation_rules( $client_data ) );

		$v->labels( $form_handler::get_validation_labels() );

		$valid  = $v->validate();
		$errors = $v->errors();

		return array(
			'valid'  => $valid,
			'errors' => $errors,
		);

	}

	/**
	 * Returns the available document types.
	 *
	 * @return array
	 */
	public function get_document_types() {
		return $this->rows_to_key_value( DB()->get_document_types() );
	}

	/**
	 * Returns the available provinces.
	 *
	 * @return array
	 */
	public function get_provinces() {
		return $this->rows_to_key_value( DB()->get_provinces() );
	}

	/**
	 * Returns the available cities.
	 *
	 * @return array
	 */
	public function get_cities() {
		return $this->rows_to_key_value( DB()->get_cities() );
	}

	/**
	 * Returns the available fiscal conditions.
	 *
	 * @return array
	 */
	public function get_fiscal_conditions() {
		return $this->rows_to_key_value( DB()->get_fiscal_conditions() );
	}

	public function get_stations() {
		return $this->rows_to_key_value( DB()->get_stations() );
	}

	/**
	 * Returns the available vehicle categories.
	 *
	 * @param boolean $no_bikes Ignore bike category.
	 * @return array
	 */
	public function get_vehicle_categories( $no_bikes = true ) {
		return DB()->get_vehicle_categories( $no_bikes );
	}

	/**
	 * Returns the available payment methods.
	 *
	 * @param boolean $types Filter by payment types.
	 * @return array
	 */
	public function get_payment_methods( $types = array(), $key = 'id' ) {
		return $this->rows_to_key_value( DB()->get_payment_methods( $types ), $key );
	}

	/**
	 * Returns a simulated API response (failure).
	 *
	 * @param string $message The message.
	 * @param array  $data The data.
	 * @return API_Response
	 */
	private function error( $message, $data = array() ) {
		return array(
			'success' => false,
			'data'    => array_merge(
				array(
					'message' => $message,
				),
				$data
			),
		);
	}

	/**
	 * Returns a simulated API response (success).
	 *
	 * @param string $message The message.
	 * @param array  $data The data.
	 * @return API_Response
	 */
	private function success( $message, $data = array() ) {
		return array(
			'success' => true,
			'data'    => array_merge(
				array(
					'message' => $message,
				),
				$data
			),
		);
	}

	/**
	 * Transforms PDO fetchAll() associative array into key value pairs.
	 *
	 * @param Row[]  $rows Array of mysql rows.
	 * @param string $key The column name to use as array key.
	 * @param string $value The column name to use as array value.
	 * @return array
	 */
	private function rows_to_key_value( $rows, $key = 'id', $value = 'nombre' ) {
		$results = array();
		foreach ( $rows as $row ) {
			$results[ $row[ $key ] ] = $row[ $value ];
		}
		return $results;
	}

}

/**
 * Returns the only instance of the API_Controller.
 *
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 *
 * @return API_Controller
 */
function API() {
	return API_Controller::instance();
}
