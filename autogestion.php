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

require ROOT_DIR . '/vendor/autoload.php';
require ROOT_DIR . '/includes/post-type.php';
require ROOT_DIR . '/includes/enqueues.php';
require ROOT_DIR . '/includes/forms/iniciar-sesion.php';
