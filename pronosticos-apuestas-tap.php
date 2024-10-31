<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Pronosticos_Apuestas_TAP
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 *
 * @wordpress-plugin
 * Plugin Name:       Pronosticos Apuestas TAP
 * Plugin URI:       http://www.todoapuestas.org
 * Description:       Plugin para gestionar pronosticos de apuestas
 * Version:           1.2.6
 * Author:       Alain Sanchez
 * Author URI:       http://www.linkedin.com/in/mrbrazzi/
 * Text Domain:       pronosticos-apuestas-tap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

namespace PronosticosApuestasTAP;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-pronosticos-apuestas-tap.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( '\PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP', 'deactivate' ) );

/*
 *
 */
add_action( 'plugins_loaded', array( '\PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-pronosticos-apuestas-tap-admin.php' );
	add_action( 'plugins_loaded', array( '\PronosticosApuestasTAP\Backend\Pronosticos_Apuestas_TAP_Admin', 'get_instance' ) );

    if( !class_exists('PronosticosApuestasTAP\Backend\Common\Meta_Boxes_Post_Type')){
        require_once plugin_dir_path( __FILE__ ) . 'admin/includes/meta-boxes.php';
        add_action( 'plugins_loaded', array( '\PronosticosApuestasTAP\Backend\Common\Meta_Boxes_Post_Type', 'get_instance' ) );
    }
}

if( !class_exists('PronosticosApuestasTAP\Common\Members_Post_Type')){
	require_once plugin_dir_path( __FILE__ ) . 'includes/post-type-members.php';
	add_action( 'plugins_loaded', array( '\PronosticosApuestasTAP\Common\Members_Post_Type', 'get_instance' ) );
}

if( !class_exists('PronosticosApuestasTAP\Common\Paypal_Post_Type')){
    require_once plugin_dir_path( __FILE__ ) . 'includes/post-type-paypal.php';
    add_action( 'plugins_loaded', array( '\PronosticosApuestasTAP\Common\Paypal_Post_Type', 'get_instance' ) );
}

if(!function_exists('loadClasses')):
function loadClasses(){
    $classess = array(
        '\PronosticosApuestasTAP\Common\BaseRepositoryInterface',
        '\PronosticosApuestasTAP\Common\AbstractRepository',
        '\PronosticosApuestasTAP\Common\TipsterRepository',
        '\PronosticosApuestasTAP\Common\Pedido',
        '\PronosticosApuestasTAP\Common\PedidoRepository',
        '\PronosticosApuestasTAP\Common\Suscripcion',
        '\PronosticosApuestasTAP\Common\SuscripcionRepository',
        '\PronosticosApuestasTAP\Common\UsuarioRepository',
        '\PronosticosApuestasTAP\Common\ShoppingCartManager',
        '\PronosticosApuestasTAP\Common\SuscriptionManager'
    );

    $includePath = plugin_dir_path( __FILE__ ).'includes';

    foreach ( $classess as $className ) {
        $namespace = '';

        if (false !== ($lastNsPos = strripos($className, '\\'))) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
        }

        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fullFileName = $includePath . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($fullFileName) && !class_exists($namespace.'\\'.$className)) {
            require_once $fullFileName;
        }
    }
}
endif;
loadClasses();


