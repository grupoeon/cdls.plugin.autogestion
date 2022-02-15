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

	const CRONJOBS = array(
		'modificaciones' => __NAMESPACE__ . '\Modificaciones_Cronjob',
	);

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'schedule' ) );
	}

	public function schedule() {
		foreach ( self::CRONJOBS as $cronjob_id => $cronjob_class ) {
			require_once ROOT_DIR . "/includes/cronjobs/class-$cronjob_id-cronjob.php";
			$cronjob = new $cronjob_class();
			$cronjob->schedule();
		}

	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function CRON() {
	return Cronjob_Controller::instance();
}

CRON();
