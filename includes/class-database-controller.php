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
	 * Returns the queries result.
	 *
	 * @return array
	 */
	public function fetchAll( $query, $parameters = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the queries result.
	 *
	 * @return array
	 */
	public function insert( $query, $parameters = array() ) {

		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $db->lastInsertId();
	}

		/**
		 * Returns the queries result.
		 *
		 * @return array
		 */
	public function error() {
		$db = $this->db();
		return $db->errorInfo();
	}

	/**
	 * Returns the queries result.
	 *
	 * @return array
	 */
	public function query( $query, $parameters = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_client_number( $client_id ) {

		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT nro_cliente FROM clientes WHERE id = :id' );
		$stmt->execute( array( 'id' => $client_id ) );
		return $stmt->fetchColumn();

	}

	/**
	 * Returns the client ID by email.
	 *
	 * @param string $email Email.
	 * @return int
	 */
	public function get_client_id_by_email( $email ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT id FROM clientes WHERE correo = :correo' );
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
		$stmt = $db->prepare( 'SELECT contrasena FROM clientes WHERE correo = :correo' );
		$stmt->execute( array( 'correo' => $email ) );
		return $stmt->fetchColumn();
	}

	/**
	 * Returns the available document types.
	 *
	 * @return array
	 */
	public function get_document_types() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _tipos_documento' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the available provinces.
	 *
	 * @return array
	 */
	public function get_provinces() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _provincias' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the available cities.
	 *
	 * @return array
	 */
	public function get_cities() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _localidades' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the available fiscal conditions.
	 *
	 * @return array
	 */
	public function get_fiscal_conditions() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _condiciones_fiscales' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the available vehicle categories.
	 *
	 * @param boolean $no_bikes Wether or not to include the bike category.
	 * @return array
	 */
	public function get_vehicle_categories( $no_bikes = true ) {
		$db   = $this->db();
		$stmt = $db->prepare(
			'SELECT * FROM _categorias_vehiculo' . ( $no_bikes ? ' WHERE id > 1' : '' )
		);
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the available payment methods.
	 *
	 * @param boolean $types Filter by payment types.
	 * @return array
	 */
	public function get_payment_methods( $types = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare(
			'SELECT * FROM _medios_de_pago'
			. ( count( $types ) ? ' WHERE tipo_medio_pago IN (' . join( ',', $types ) . ')' : '' )
		);
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	/**
	 * Returns the client data.
	 *
	 * @param int $client_id The client ID.
	 * @return array
	 */
	public function get_client_data( $client_id ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM clientes WHERE id = :client_id' );
		$stmt->execute( array( 'client_id' => $client_id ) );
		return $stmt->fetch( \PDO::FETCH_ASSOC );
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
