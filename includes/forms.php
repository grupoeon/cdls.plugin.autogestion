<?php
/**
 * This contains all the logic for outputting forms.
 *
 * @package clds-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

const REGISTERED_FORM_IDS = array(
	'iniciar-sesion'    => __NAMESPACE__ . '\Iniciar_Sesion_Form',
	'cerrar-sesion'     => __NAMESPACE__ . '\Cerrar_Sesion_Form',
	'olvide-contrasena' => __NAMESPACE__ . '\Olvide_Contrasena_Form',
	'perfil'            => __NAMESPACE__ . '\Perfil_Form',
	'altas'             => __NAMESPACE__ . '\Altas_Form',
	'bajas'             => __NAMESPACE__ . '\Bajas_Form',
	'medios-de-pago'    => __NAMESPACE__ . '\Medios_De_Pago_Form',
);

add_shortcode(
	'cdls_form',
	function( $atts ) {

		$atts = shortcode_atts(
			array(
				'id' => null,
			),
			$atts
		);

		$form_id = $atts['id'];

		if ( ! in_array( $form_id, array_keys( REGISTERED_FORM_IDS ), true ) ) {
			return;
		}

		ob_start();

		require_once ROOT_DIR . "/includes/forms/class-{$form_id}-form.php";

		$form_controller = REGISTERED_FORM_IDS[ $form_id ];

		if ( ! class_exists( $form_controller ) ) {
			return;
		}

		$form = new \Formr\Formr( 'bootstrap' );

		$form->action   = '';
		$form->required = '*';

		$form_controller::build( $form );

		if ( $form->submitted() ) {
			$form_controller::on_submit( $form );
		}

		return ob_get_clean();

	}
);
