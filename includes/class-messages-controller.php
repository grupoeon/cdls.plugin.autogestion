<?php
/**
 * This controls all the application messages.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Messages_Controller {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	const MAINTENANCE = <<<MSG
    Estamos mejorando el sistema para que puedas acceder a tu facturación online, por favor vuelve a intentarlo más tarde.
MSG;

	const NOT_LOGGED_IN_REFERRAL = <<<MSG
	Por favor, iniciá sesión para poder acceder a los servicios de autogestión.
MSG;

	const REGISTRATION_INFO = <<<MSG
	Para iniciar sesión primero debés registrarte. Si aún no lo hiciste, podés hacerlo de dos maneras:
	<ol>
		<li>
			Si <b>aún no sos cliente nuestro</b> y querés registrar tu primer vehículo, <a href="/autogestion/registrarse"><b>registrate desde aquí</b></a>.
		</li>
		<li>
			Si <b>sos cliente y tenés vehículos registrados</b> con nosotros, recuperá tu contraseña <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.
		</li>
	</ol>
MSG;

	const CURRENT_USER_CANT_SUBMIT = <<<MSG
	Su usuario no tiene permitido realizar esta operación, disculpe las molestias.
MSG;

	const DOCUMENT_EXISTS = <<<MSG
	El documento pertenece a un usuario registrado, deberá iniciar sesión.
MSG;

	const EMAIL_EXISTS = <<<MSG
	El correo electrónico pertenece a una cuenta ya registrada, deberá ingresar otro correo electrónico.
MSG;

	const REMEMBER_OLD_CLIENTS = <<<MSG
	Recordá que si ya completaste estos datos alguna vez y/o diste de alta un vehículo anteriormente podés <b>recuperar tu contraseña</b> <a href="/autogestion/recuperar-contrasena"><b>desde aquí</b></a>.
MSG;

	const REGISTERED_SUCCESSFULLY = <<<MSG
	Te has registrado correctamente. <br><br> Se envió tu contraseña de acceso a tu correo electrónico. <a href="/autogestion/iniciar-sesion"><b>Inicia sesión</b></a> para continuar.
MSG;

	const MAIL_REGISTRATION_SUBJECT = <<<MSG
	Caminos de las Sierras | Clave de acceso para autogestión
MSG;

	const MAIL_REGISTRATION_CONTENT = <<<MAIL
	Bienvenido.

	Nos es grato comunicarle que la clave para poder ingresar a la sección autogestión del sitio https://www.caminosdelassierras.com.ar/autogestion/iniciar-sesion es:<br>
	<br>
	
	<h1><b>%s</b></h1>
	
	Si el enlace proporcionado no funciona copielo y pegelo en su barra de navegación.
	
	Esperamos que tenga una buena experiencia.
	
	Atte. Caminos de las Sierras
	
	Esta es una respuesta automática, por favor no responda este email.
MAIL;

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function MSG() {
	return Messages_Controller::instance();
}

MSG();
