<?php
/**
 * This is the cronjob controller.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Cronjob_Controller {


	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'schedule' ) );
		add_action( 'cdls_cronjob', array( $this, 'cronjob' ) );
	}

	public function schedule() {
		if ( false === as_has_scheduled_action( 'cdls_cronjob' ) ) {
			as_schedule_recurring_action( strtotime( 'now' ), MINUTE_IN_SECONDS * 1, 'cdls_cronjob' );
		}
	}

	public function cronjob() {

		TXT()::generate( 'Cronjob', TIME()->now() );

	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function CRON() {
	return Cronjob_Controller::instance();
}
