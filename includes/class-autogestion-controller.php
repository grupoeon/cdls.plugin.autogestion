<?php
/**
 * This is the main controller.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

session_start();

/**
 * The main controller.
 */
class Autogestion_Controller {

	const LOGIN_FORM_ID            = 3354;
	const LOGIN_FORM_MENU_ID       = 3377;
	const LOGOUT_FORM_MENU_ID      = 3419;
	const FORGOT_PASSWORD_FORM_ID  = 3367;
	const PROFILE_FORM_ID          = 3368;
	const RECOVER_PASSWORD_FORM_ID = 3753;
	const REGISTER_FORM_ID         = 3750;
	const PAYMENT_FORM_ID          = 3372;
	const BAJAS_FORM_ID            = 3373;
	const MENU_ID                  = 13;
	const CHANGE_PASSWORD_FORM_ID  = 4084;

	/**
	 * The single instance of the class.
	 *
	 * @var Autogestion_Controller
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	 */
	protected static $_instance = null;

	/**
	 * Main Autogestion_Controller Instance.
	 *
	 * Ensures only one instance of Autogestion_Controller is loaded or can be loaded.
	 *
	 * @return Autogestion_Controller - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * The constructor.
	 */
	public function __construct() {

		$this->init_hooks();

	}

	/**
	 * Adds the necessary hooks for the plugin to work.
	 *
	 * @return void
	 */
	private function init_hooks() {

		add_action( 'template_redirect', array( $this, 'navigation_guards' ) );
		add_action( 'wp_nav_menu_objects', array( $this, 'menu_guards' ), 10, 2 );

	}

	/**
	 * Logs in a client and saves it in $_SESSION.
	 *
	 * @param string $email The cliente email.
	 * @param string $password The client password.
	 * @return API_Response
	 */
	public function log_in( $email, $password ) {

		$response = API()->log_in( $email, $password );

		if ( $response['success'] ) {
			$_SESSION['autogestion_id'] = $response['data']['id'];
		}

		return $response;

	}

	/**
	 * Determines if the client is logged in.
	 *
	 * @return boolean
	 */
	public function is_client_logged_in() {

		return ! empty( $_SESSION['autogestion_id'] );

	}

	/**
	 * Logs the client out.
	 *
	 * @return void
	 */
	public function log_out() {

		unset( $_SESSION['autogestion_id'] );

	}

	/**
	 * Controls the navigation guards.
	 * Is the user logged in?
	 * Is the archive allowed?
	 *
	 * @return void
	 */
	public function navigation_guards() {

		if ( is_post_type_archive( POST_TYPE ) ) {
			wp_safe_redirect( get_permalink( self::PROFILE_FORM_ID ) );
		}

		if ( ! is_singular( POST_TYPE ) ) {
			return;
		}

		if ( 3416 === get_the_ID() ) {
			AG()->log_out();
			wp_safe_redirect( get_permalink( self::LOGIN_FORM_ID ) );
		}

		if ( in_array(
			get_the_ID(),
			array(
				self::LOGIN_FORM_ID,
				self::FORGOT_PASSWORD_FORM_ID,
				self::RECOVER_PASSWORD_FORM_ID,
				self::REGISTER_FORM_ID,
			),
			true
		) ) {
			return;
		}

		if ( $this->is_client_logged_in() ) {
			return;
		}

		wp_safe_redirect(
			add_query_arg( array( 'not_logged_in' => true ), get_permalink( self::LOGIN_FORM_ID ) )
		);

	}

	/**
	 * Controls the menu item guards.
	 * Is the user logged in?
	 *
	 * @param MenuItem[] $sorted_menu_objects The menu items.
	 * @param any        $args The arguments.
	 * @return MenuItem[]
	 */
	public function menu_guards( $sorted_menu_objects, $args ) {

		if ( self::MENU_ID !== intval( $args->menu ) ) {

			return $sorted_menu_objects;

		}

		$sorted_menu_objects = array_filter(
			$sorted_menu_objects,
			function( $menu_item ) {

				if ( self::LOGIN_FORM_MENU_ID === $menu_item->ID && AG()->is_client_logged_in() ) {
					return false;
				}

				if ( self::LOGOUT_FORM_MENU_ID === $menu_item->ID && ! AG()->is_client_logged_in() ) {
					return false;
				}

				if ( in_array(
					$menu_item->ID,
					array( self::PAYMENT_FORM_ID, self::BAJAS_FORM_ID ),
					true
				) && empty( API()->client_number() ) ) {
					return false;
				}

				if ( self::CHANGE_PASSWORD_FORM_ID === $menu_item->ID && ! AG()->is_client_logged_in() ) {
					return false;
				}

				return true;

			}
		);

		return $sorted_menu_objects;

	}

}

/**
 * Returns the only instance of the Autogestion_Controller.
 *
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 *
 * @return Autogestion_Controller
 */
function AG() {

	return Autogestion_Controller::instance();

}

AG();
