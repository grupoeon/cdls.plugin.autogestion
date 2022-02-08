<?php
/**
 * This contains all the post types.
 *
 * @package clds-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

add_action(
	'init',
	function () {
		register_post_type(
			POST_TYPE,
			array(
				'labels'      => array(
					'name'          => __( 'Autogestión', 'cdls-autogestion' ),
					'singular_name' => __( 'Autogestión', 'cdls-autogestion' ),
				),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'autogestion' ),
				'menu_icon'   => 'dashicons-universal-access-alt',
			)
		);
	}
);
