<?php
/**
 * Autogestión
 *
 * @package           clds-autogestion
 * @author            Grupo EON
 * @copyright         Grupo EON
 *
 * @wordpress-plugin
 * Plugin Name:       Autogestión
 * Plugin URI:        https://github.com/grupoeon/plugin.cdls-autogestion
 * Description:       Un plugin para habilitar la Autogestión de los usuarios.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Grupo EON
 * Author URI:        https://grupoeon.com.ar
 * Text Domain:       cdls-autogestion
 * Update URI:        https://github.com/grupoeon/plugin.cdls-autogestion
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

const TEXT_DOMAIN = 'cdls-autogestion';
const VERSION     = '1.0.0';
const ROOT_DIR    = __DIR__;
const ROOT_FILE   = __FILE__;
const POST_TYPE   = 'autogestion';
const DEBUG       = false;
const MAINTENANCE = true;

require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/includes/post-type.php';
require_once ROOT_DIR . '/includes/enqueues.php';

require_once ROOT_DIR . '/includes/class-messages-controller.php';
require_once ROOT_DIR . '/includes/class-time-controller.php';
require_once ROOT_DIR . '/includes/class-validations-controller.php';
require_once ROOT_DIR . '/includes/class-form-controller.php';
require_once ROOT_DIR . '/includes/class-api-controller.php';
require_once ROOT_DIR . '/includes/class-txt-controller.php';
require_once ROOT_DIR . '/includes/class-autogestion-controller.php';
