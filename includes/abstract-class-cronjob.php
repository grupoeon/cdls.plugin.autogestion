<?php
/**
 * This is the skeleton for all cronjobs.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable WordPress.Files.FileName
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

abstract class Cronjob {
	abstract public static function instance();
	abstract public function get_id();
	abstract public function get_frecuency_in_seconds();
	abstract public function run();

	public function get_action() {
		return 'cdls_cronjob_' . $this->get_id();
	}

	public function __construct() {
		if ( false === as_has_scheduled_action( $this->get_action() ) ) {
			as_schedule_recurring_action(
				strtotime( 'now' ),
				$this->get_frecuency_in_seconds(),
				$this->get_action()
			);
		}
		add_action( $this->get_action(), array( $this, 'run' ) );
	}
}
