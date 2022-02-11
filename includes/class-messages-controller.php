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

	const PASSWORD_RECOVERY_EXPLANATION = <<<MSG
	Para recuperar tu contraseña necesitamos <b>verificar tu identidad</b>.<br><br>Ingresá el <b>número de DNI/CUIT</b> con el que te registraste y el <b>dominio/patente</b> de un vehículo que hayas registrado (no importa si fue dado de baja).
MSG;

	const CLIENT_RECORDS_NOT_FOUND = <<<MSG
	Los datos ingresados no concuerdan con nuestros registros, por favor comunicarse con la Oficina de Atención al Usuario de Caminos de las Sierras.
MSG;

	const NEW_PASSWORD_SENT = <<<MSG
	Te enviamos un correo con la nueva contraseña para que puedas iniciar sesión.
MSG;

	const BAJA_WARNING = <<<MSG
	Esta baja será efectiva una vez que reciba la confirmación definitiva por parte de Caminos de las Sierras SA. De no recibirla en 72 hs. hábiles por favor póngase en contacto con la Oficina de Atención al Usuario de Caminos de las Sierras.
MSG;

	const EMAIL_BAJA_SUBJECT = <<<MSG
	Caminos de las Sierras | Solicitud de Baja en trámite
MSG;

	const EMAIL_BAJA_CONTENT = <<<MSG
	Su solicitud de baja de dominio (<b>%s</b>) fue enviada. En el transcurso de las próximas 72 horas hábiles, ud recibirá un email con la confirmación definitiva de la baja solicitada una vez aprobada.
MSG;

	const BAJA_IN_PROGRESS = <<<MSG
	Su solicitud de baja de dominio fue enviada. En el transcurso de las próximas 72 horas hábiles, ud recibirá un email con la confirmación definitiva de la baja solicitada una vez aprobada.
MSG;

	const CMP_WARNING = <<<MSG
	El cambio de un medio de pago se aplica a todos los vehículos de la cuenta. <br>
	Si usted no desea cambiar la totalidad de los vehículos por favor póngase en contacto con la Oficina de Atención al Usuario de Caminos de las Sierras.<br><br>
	El cambio será efectivo una vez que reciba la confirmación definitiva por parte de Caminos de las Sierras SA. De no recibirla en 72 hs. hábiles por favor póngase en contacto con la Oficina de Atención al Usuario.
MSG;

	const EMAIL_CMP_SUBJECT = <<<MSG
	Caminos de las Sierras | Solicitud de Cambio de Medio de Pago en trámite
MSG;

	const EMAIL_CMP_CONTENT = <<<MSG
	Su solicitud de cambio de medio de pago fue enviada. En el transcurso de las próximas 72 horas hábiles ud recibirá un email con la confirmación definitiva del cambio solicitado, una vez que su pedido haya sido procesado y aprobado.
MSG;

	const CMP_IN_PROGRESS = <<<MSG
	Su solicitud de cambio de medio de pago fue enviada. En el transcurso de las próximas 72 horas hábiles ud recibirá un email con la confirmación definitiva del cambio solicitado, una vez que su pedido haya sido procesado y aprobado.
MSG;

	const COMPLETE_PROFILE_INFO = <<<MSG
	Debés completar los datos de tu perfil antes de poder utilizar este formulario. <a href="/autogestion/mi-perfil"><b>Ver perfil</b></a>.
MSG;

	const EMAIL_ALTA_SUBJECT = <<<MSG
	Caminos de las Sierras | Solicitud de Alta en trámite
MSG;

	const EMAIL_ALTA_CONTENT = <<<MSG
	Su solicitud de alta de vehículo (<b>%s</b>) fue enviada. Dentro de las próximas 72 hs hábiles se le enviará un email informándole el resultado de la validación del trámite.
MSG;

	const ALTA_IN_PROGRESS = <<<MSG
	Su solicitud de alta de vehículo fue enviada. Dentro de las próximas 72 hs hábiles se le enviará un email informándole el resultado de la validación del trámite.
MSG;

	const CHANGED_PASSWORD = <<<MSG
	Su contraseña fue actualizada.
MSG;

	const MAIL_CHANGE_PASSWORD_SUBJECT = <<<MSG
	Caminos de las Sierras | Contraseña actualizada.
MSG;

	const MAIL_CHANGE_PASSWORD_CONTENT = <<<MSG
	Su contraseña fue actualizada recientemente, si no fue usted, comuníquese de inmediato con la Oficina de Atención al Usuario de Caminos de las Sierras 
MSG;

	const PASSWORD_NOT_MATCH = <<<MSG
	es incorrecta.
MSG;

}

/**
 * @phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function MSG() {
	return Messages_Controller::instance();
}

MSG();
