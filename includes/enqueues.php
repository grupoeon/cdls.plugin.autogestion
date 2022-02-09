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
			TEXT_DOMAIN,
			plugins_url( 'public/autogestion.css', ROOT_FILE ),
			null,
			DEBUG ? wp_rand( 0, PHP_INT_MAX ) : VERSION
		);

		wp_enqueue_script(
			TEXT_DOMAIN,
			plugins_url( 'public/autogestion.js', ROOT_FILE ),
			null,
			DEBUG ? wp_rand( 0, PHP_INT_MAX ) : VERSION,
			true
		);

	}
);
