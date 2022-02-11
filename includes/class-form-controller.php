<?php
/**
 * This controls all the Autogestion forms.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

require_once ROOT_DIR . '/includes/abstract-class-form.php';

class Form_Controller {

	const FORM_HANDLER_ID = 'cdls_form_id';

	const ALLOWED_FORM_HANDLERS = array(
		'iniciar-sesion'       => __NAMESPACE__ . '\Iniciar_Sesion_Form',
		'olvide-contrasena'    => __NAMESPACE__ . '\Olvide_Contrasena_Form',
		'perfil'               => __NAMESPACE__ . '\Perfil_Form',
		'altas'                => __NAMESPACE__ . '\Altas_Form',
		'bajas'                => __NAMESPACE__ . '\Bajas_Form',
		'medios-de-pago'       => __NAMESPACE__ . '\Medios_De_Pago_Form',
		'registro'             => __NAMESPACE__ . '\Registro_Form',
		'recuperar-contrasena' => __NAMESPACE__ . '\Recuperar_Contrasena_Form',
	);


	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		$this->init_hooks();

	}

	public function init_hooks() {
		add_action( 'template_redirect', array( $this, 'handle_submissions' ) );
		add_shortcode( 'cdls_form', array( $this, 'handle_shortcode' ) );
	}

	public function handle_submissions() {

		global  $cdls_submitted_form,
				$cdls_submitted_form_handler_id,
				$cdls_submitted_form_handler;

		$form = new \Formr\Formr( 'bootstrap', 'hush' );

		if ( $form->submitted() ) {
			/**
			 * @phpcs:disable WordPress.Security.NonceVerification.Missing
			 */
			if ( empty( $_POST[ self::FORM_HANDLER_ID ] ) ) {
				return;
			}

			$handler_id = sanitize_text_field( wp_unslash( $_POST[ self::FORM_HANDLER_ID ] ) );

			$handler = $this->get_form_handler( $handler_id, $form );

			$cdls_submitted_form            = $form;
			$cdls_submitted_form_handler_id = $handler_id;
			$cdls_submitted_form_handler    = $handler;

			if ( $handler ) {

				if ( ! $handler->current_user_can_submit() ) {
					$handler->error_message( MSG()::CURRENT_USER_CANT_SUBMIT );
					return;
				}

				$valid = $handler->validate();

				if ( true !== $valid ) {

					$this->add_error_messages_from_validation( $handler, $valid );
					return;

				}

				$handler->submit();

			}
		}
	}

	public function add_error_messages_from_validation( $handler, $errors ) {

		ob_start();
		foreach ( $errors as $error ) {
			?>
			<li><?php echo esc_html( $error[0] ); ?></li>
			<?php
		}
		$error_messages = ob_get_clean();
		$message        = "Revisa los siguientes errores: <ul>$error_messages</ul>";
		$handler->error_message( $message );

	}

	public function get_form_handler( $handler_id, $form ) {

		if ( ! in_array(
			$handler_id,
			array_keys( self::ALLOWED_FORM_HANDLERS ),
			true
		) ) {
			return null;
		}

		require_once ROOT_DIR . "/includes/forms/class-{$handler_id}-form.php";

		$form_handler_classname = self::ALLOWED_FORM_HANDLERS[ $handler_id ];
		$form_handler           = new $form_handler_classname( $form );

		return $form_handler;

	}

	public function handle_shortcode( $atts ) {

		global  $cdls_submitted_form,
				$cdls_submitted_form_handler_id,
				$cdls_submitted_form_handler;

		$atts = shortcode_atts(
			array(
				'id' => null,
			),
			$atts
		);

		$handler_id = $atts['id'];

		if ( $cdls_submitted_form_handler_id === $handler_id ) {
			$form = $cdls_submitted_form;
		} else {
			$form = new \Formr\Formr( 'bootstrap', 'hush' );
		}

		$form->action   = '';
		$form->required = '*';
		$form->id       = $handler_id;
		$form->name     = $handler_id;

		if ( $cdls_submitted_form_handler_id === $handler_id ) {
			$handler = $cdls_submitted_form_handler;
		} else {
			$handler = $this->get_form_handler( $handler_id, $form );
		}

		if ( ! $handler ) {
			return;
		}

		ob_start();

		if ( MAINTENANCE && ! current_user_can( 'manage_options' ) ) {

			$handler->error_message( MSG()::MAINTENANCE, true );
			return ob_get_clean();

		}

		$handler->build();

		$handler->messages();

		return ob_get_clean();

	}

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function FORM() {
	return Form_Controller::instance();
}

FORM();
