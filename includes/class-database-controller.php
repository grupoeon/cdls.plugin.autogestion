<?php
/**
 * This is database controller.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

/**
 * The the database controller.
 *
 * @phpcs:disable WordPress.DB.RestrictedClasses.mysql__PDO
 */
class Database_Controller {

	private const DATABASE_HOST     = 'localhost';
	private const DATABASE_NAME     = 'caminosdelassier_sistema';
	private const DATABASE_USER     = 'etruel';
	private const DATABASE_PASSWORD = 'Cedo87+G*FGFF';


	/**
	 * The single instance of the class.
	 *
	 * @var Database_Controller
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	protected static $_instance = null;

	/**
	 * The single instance of the PDO connection.
	 *
	 * @var PDO_Connection
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	private $_db = null;

	/**
	 * Main Database_Controller Instance.
	 *
	 * Ensures only one instance of Database_Controller is loaded or can be loaded.
	 *
	 * @return Database_Controller - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Returns the only instance of the PDO Database Connection.
	 *
	 * @return PDO_Connection
	 */
	public function db() {
		if ( empty( $this->_db ) ) {
			$this->_db = new \PDO(
				'mysql:host=' . self::DATABASE_HOST . ';dbname=' . self::DATABASE_NAME,
				self::DATABASE_USER,
				self::DATABASE_PASSWORD
			);
		}
		return $this->_db;
	}

	/**
	 * Returns the client ID by email.
	 *
	 * @param string $email Email.
	 * @return int
	 */
	public function get_client_id_by_email( $email ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT id from clientes WHERE correo = :correo' );
		$stmt->execute( array( 'correo' => $email ) );
		return $stmt->fetchColumn();
	}


	/**
	 * Returns the client password by email.
	 *
	 * @param string $email Email.
	 * @return string
	 */
	public function get_client_password_by_email( $email ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT contrasena from clientes WHERE correo = :correo' );
		$stmt->execute( array( 'correo' => $email ) );
		return $stmt->fetchColumn();
	}
}

/**
 * Returns the only instance of the Database_Controller.
 *
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 *
 * @return Database_Controller
 */
function DB() {
	return Database_Controller::instance();
}
