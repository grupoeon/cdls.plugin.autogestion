<?php
/**
 * This is the cronjobs controller.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

require_once ROOT_DIR . '/includes/abstract-class-cronjob.php';
require_once ROOT_DIR . '/includes/cronjobs/class-modificaciones-cronjob.php';

class Cronjobs_Controller {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	const CRONJOBS = array(
		__NAMESPACE__ . '\Modificaciones_Cronjob',
	);

	public function __construct() {
		add_filter(
			'action_scheduler_timeout_period',
			function() {
				return MINUTE_IN_SECONDS * 10;
			}
		);
		add_action( 'init', array( $this, 'schedule' ) );
	}

	public function schedule() {
		foreach ( self::CRONJOBS as $cronjob ) {
			$cronjob::instance();
		}
	}

}

// @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
function CRON() {
	return Cronjobs_Controller::instance();
}

CRON();
