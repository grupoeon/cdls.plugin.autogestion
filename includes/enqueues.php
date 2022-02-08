<?php
/**
 * This contains all the scripts and styles enqueued by this plugin.
 *
 * @package clds-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_singular( POST_TYPE ) ) {
			return;
		}

		wp_enqueue_style(
			TEXT_DOMAIN . '-bootstrap',
			'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
			null,
			VERSION
		);

		wp_enqueue_script(
			TEXT_DOMAIN . '-bootstrap',
			'https://dn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
			null,
			VERSION,
			true
		);

	}
);
