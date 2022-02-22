<?php
/**
 * This is the database controller.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable WordPress.DB.RestrictedClasses.mysql__PDO
 */


namespace CdlS;

defined( 'ABSPATH' ) || die;

class Database_Controller {

	private const DATABASE_HOST     = 'localhost';
	private const DATABASE_NAME     = 'caminosdelassier_sistema';
	private const DATABASE_USER     = 'etruel';
	private const DATABASE_PASSWORD = 'Cedo87+G*FGFF';

	protected static $_instance = null;

	private $_db = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function db() {
		if ( empty( $this->_db ) ) {
			$this->_db = new \PDO(
				'mysql:host=' . self::DATABASE_HOST . ';dbname=' . self::DATABASE_NAME,
				self::DATABASE_USER,
				self::DATABASE_PASSWORD
			);
			$this->_db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->_db->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
		}
		return $this->_db;
	}

	public function is_current_password( $password ) {

		$client_data = $this->get_client_data( API()->client_id() );
		return password_verify( $password, $client_data['contrasena'] );

	}

	/**
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput
	 */
	public function new_procedure( $procedure_type ) {

		$ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );

		$procedure_id = DB()->insert(
			<<<SQL
				INSERT INTO gestiones ( id_tipo_gestion, estado_gestion, ip_gestion ) 
				VALUES (:type, '1', :ip);
SQL,
			array(
				'type' => $procedure_type,
				'ip'   => $ip,
			)
		);

		return $procedure_id;

	}

	public function fetch_all( $query, $parameters = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function fetch( $query, $parameters = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $stmt->fetch( \PDO::FETCH_ASSOC );
	}

	public function get_payments( $client_number ) {

		return $this->fetch_all(
			'SELECT * from facturas_morosas 
				JOIN facturas
				ON facturas.orden_venta = facturas_morosas.orden_venta
				WHERE facturas_morosas.nro_cliente = :nro_cliente',
			array( 'nro_cliente' => $client_number )
		);

	}

	public function get_payment( $client_number, $receipt_id ) {

		return $this->fetch(
			'SELECT * from facturas_morosas 
				JOIN facturas
				ON facturas.orden_venta = facturas_morosas.orden_venta
				WHERE facturas_morosas.nro_cliente = :nro_cliente
				AND facturas_morosas.orden_venta = :orden_venta
				AND facturas_morosas.abonado < 1
				AND facturas_morosas.estado_deuda = "R"',
			array(
				'nro_cliente' => $client_number,
				'orden_venta' => $receipt_id,
			)
		);

	}

	public function document_exists( $document, $client_id = null ) {
		$db  = $this->db();
		$sql = 'SELECT COUNT(*) FROM clientes WHERE documento = :documento';
		if ( $client_id ) {
			$sql .= ' AND id != :client_id';
		}
		$stmt = $db->prepare( $sql );
		$args = array(
			'documento' => $document,
		);
		if ( $client_id ) {
			$args['client_id'] = $client_id;
		}
		$stmt->execute( $args );
		return (bool) $stmt->fetchColumn();
	}

	public function email_exists( $email, $client_id = null ) {
		$db  = $this->db();
		$sql = 'SELECT COUNT(*) FROM clientes WHERE correo = :correo';
		if ( $client_id ) {
			$sql .= ' AND id != :client_id';
		}
		$stmt = $db->prepare( $sql );
		$args = array(
			'correo' => $email,
		);
		if ( $client_id ) {
			$args['client_id'] = $client_id;
		}
		$stmt->execute( $args );
		return (bool) $stmt->fetchColumn();
	}

	public function client_has_email( $client_id ) {

		$db   = $this->db();
		$sql  = 'SELECT clientes.correo FROM clientes WHERE id = :id';
		$stmt = $db->prepare( $sql );
		$stmt->execute(
			array(
				'id' => $client_id,
			)
		);
		$fetch = $stmt->fetchColumn();

		return ! empty( $fetch );

	}

	public function client_same_email( $client_id, $email ) {

		$db   = $this->db();
		$sql  = 'SELECT COUNT(*) FROM clientes WHERE id = :id AND correo = :email';
		$stmt = $db->prepare( $sql );
		$stmt->execute(
			array(
				'id'    => $client_id,
				'email' => $email,
			)
		);
		$fetch = $stmt->fetchColumn();

		return boolval( $fetch );

	}


	public function verify_identity( $document, $domain ) {
		$db   = $this->db();
		$sql  = 'SELECT clientes.id FROM clientes JOIN vehiculos ON clientes.nro_cliente = vehiculos.nro_cliente WHERE clientes.documento = :documento AND vehiculos.dominio = :dominio';
		$stmt = $db->prepare( $sql );
		$stmt->execute(
			array(
				'documento' => $document,
				'dominio'   => $domain,
			)
		);
		return (int) $stmt->fetchColumn();
	}

	public function insert( $query, $parameters = array() ) {

		$db   = $this->db();
		$stmt = $db->prepare( $query );
		$stmt->execute( $parameters );
		return $db->lastInsertId();
	}

	public function error() {
		$db = $this->db();
		return $db->errorInfo();
	}

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

	public function get_client_id_by_email( $email ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT id FROM clientes WHERE correo = :correo' );
		$stmt->execute( array( 'correo' => $email ) );
		return $stmt->fetchColumn();
	}

	public function get_client_password_by_email( $email ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT contrasena FROM clientes WHERE correo = :correo' );
		$stmt->execute( array( 'correo' => $email ) );
		return $stmt->fetchColumn();
	}

	public function get_document_types() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _tipos_documento WHERE id IN (2,5)' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_provinces() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _provincias' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_cities() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _localidades' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_fiscal_conditions() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _condiciones_fiscales' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}


	public function get_stations() {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM _estaciones' );
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_vehicle_categories( $no_bikes = true ) {
		$db   = $this->db();
		$stmt = $db->prepare(
			'SELECT * FROM _categorias_vehiculo' . ( $no_bikes ? ' WHERE id > 1' : '' )
		);
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_payment_methods( $types = array() ) {
		$db   = $this->db();
		$stmt = $db->prepare(
			'SELECT * FROM _medios_de_pago'
			. ( count( $types ) ? ' WHERE tipo_medio_pago IN (' . join( ',', $types ) . ')' : '' )
		);
		$stmt->execute();
		return $stmt->fetchAll( \PDO::FETCH_ASSOC );
	}

	public function get_client_data( $client_id ) {
		$db   = $this->db();
		$stmt = $db->prepare( 'SELECT * FROM clientes WHERE id = :client_id' );
		$stmt->execute( array( 'client_id' => $client_id ) );
		return $stmt->fetch( \PDO::FETCH_ASSOC );
	}
}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function DB() {
	return Database_Controller::instance();
}
