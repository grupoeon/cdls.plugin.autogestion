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
			return $this->error( 'El usuario o la contraseña son incorrectos.' );
		}

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
