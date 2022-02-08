<?php
/**
 * This is the login form.
 *
 * @package cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

add_shortcode(
	'clds_forms_iniciar-sesion',
	function() {

		ob_start();

		$form = new \Formr\Formr( 'bootstrap' );

		$form->action   = '';
		$form->required = '*';

		$array = array(
			'text'     => 'name,Name,John Wick',
			'email'    => 'email,Email,johnwick@gunfu.com',
			'textarea' => 'comments,Comments',
		);

		$form->fastform( $array );

		if ( $form->submitted() ) {
			// Some logic here.
			$form->success_message( 'wohoo' );
		}

		return ob_get_clean();

	}
);
