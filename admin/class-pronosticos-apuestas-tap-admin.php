<?php
/**
 * Pronosticos Apuestas TAP
 *
 * @package   Pronosticos_Apuestas_TAP_Admin
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */
namespace PronosticosApuestasTAP\Backend;

use PronosticosApuestasTAP\Common\NoExisteNumeroPedidoException;
use PronosticosApuestasTAP\Common\NoExisteUsuarioAsociadoPedidoException;
use PronosticosApuestasTAP\Common\PedidoRepository;
use PronosticosApuestasTAP\Common\ShoppingCartManager;
use PronosticosApuestasTAP\Common\Suscripcion;
use PronosticosApuestasTAP\Common\SuscripcionRepository;
use PronosticosApuestasTAP\Common\SuscriptionManager;
use PronosticosApuestasTAP\Common\TipsterRepository;
use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-pronosticos-apuestas-tap.php`
 *
 * @TODO    : Rename this class to a proper name for your plugin.
 *
 * @package Pronosticos_Apuestas_TAP_Admin
 * @author  Alain Sanchez <luka.ghost@gmail.com>
 */
class Pronosticos_Apuestas_TAP_Admin {
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;
    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    private $promocion = array(
        'codigo'    => '',
        'descuento' => '',
        'fecha_fin' => '',
    );

    private $metodos_pago = array(
        'paysafecard' => array(
            'activado'       => 0,
            'email_contacto' => '',
        ),
        'paypal'      => array(
            'activado'      => 0, // 1 => enable or 0 => disable
            'currency'      => 'EUR',
            'language'      => 'es_ES',
            'live'          => array(
                'account' => '',
                'path'    => 'paypal', // https://www.paypal.com/cgi-bin/webscr
            ),
            'sandbox'       => array(
                'account' => '',
                'path'    => 'sandbox.paypal', // https://www.sandbox.paypal.com/cgi-bin/webscr
            ),
            'mode'          => 'sandbox', // sandbox or live
            'payment_action' => 'sale', // sale or authorization
            'target'        => '_self', // _blank or _self
            'url'           => array(
                'cancel' => '',
                'return' => '',
            ),
        ),
        'skrill'      => array(
            'activado' => 0,
        ),
    );

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
        /*
         *
         */
        /* if( ! is_super_admin() ) {
            return;
        } */
        /*
         * Call $plugin_slug from public plugin class.
         *
         */
        $plugin            = Pronosticos_Apuestas_TAP::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();
        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

        add_action( 'pronostico_apuestas_save_promocion', array( $this, 'save_promocion' ), 10, 1 );
        add_action( 'pronostico_apuestas_metodos_pago', array( $this, 'metodos_pago' ), 10, 1);

        add_action( 'pronostico_apuestas_gestion_suscripciones', array( $this, 'gestion_suscripciones' ), 10, 1 );

        add_action( 'pronostico_apuestas_enviar_email_suscripcion_por_paysafecard', array( $this, 'enviar_email_suscripcion_por_paysafecard' ), 10, 1 );
        add_action( 'pronostico_apuestas_enviar_email_suscripcion_por_paypal_editada', array( $this, 'enviar_email_suscripcion_por_paypal_editada' ), 10, 1 );
        add_filter( 'wp_mail_from', array( $this, 'enviar_email_from' ), 10, 1 );
        add_filter( 'wp_mail_from_name', array( $this, 'enviar_email_from_name' ), 10, 1 );
        add_filter( 'wp_mail_content_type', array( $this, 'enviar_email_content_type' ), 10, 1 );

        add_action( 'wp_insert_post', array( $this, 'save_post' ), 20, 3 );
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {
        /*
         *
         */
        /* if( ! is_super_admin() ) {
            return;
        } */
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @since     1.0.0
     *
     * @return array
     */
    public function get_promocion()
    {
        return $this->promocion;
    }

    /**
     * @since     1.0.0
     *
     * @return array
     */
    public function get_metodos_pago()
    {
        return $this->metodos_pago;
    }

    /**
     * Register and enqueue admin-specific style sheet.
     *
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }
        $screen = get_current_screen();
        switch($screen->id){
            case $this->plugin_screen_hook_suffix['root']:
                wp_enqueue_style('jquery-ui-datepicker', plugins_url( 'assets/css/jquery.ui.all.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                break;
            case $this->plugin_screen_hook_suffix['configuracion']:
                break;
            case $this->plugin_screen_hook_suffix['suscripciones']:
                $op = isset($_POST['op']) ? $_POST['op'] : 'list';
                switch($op){
                    case 'new':
                        wp_enqueue_style('jquery-ui-datepicker', plugins_url( 'assets/css/jquery.ui.all.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_style('select2', plugins_url( 'assets/css/select2.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                    case 'add':
                        break;
                    case 'edit':
                    case 'update':
                        wp_enqueue_style('jquery-ui-datepicker', plugins_url( 'assets/css/jquery.ui.all.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                    case 'delete':
                        break;
                    default: // list
                        wp_enqueue_style('jquery-ui-datepicker', plugins_url( 'assets/css/jquery.ui.all.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_style('jquery.dataTables', plugins_url( 'assets/css/jquery.dataTables.min.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
//                        wp_enqueue_style('dataTables.tableTools', plugins_url( 'assets/css/dataTables.tableTools.min.css', __FILE__ ), array(), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     *
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_scripts() {
        if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
            return;
        }
        $screen = get_current_screen();
        switch($screen->id){
            case $this->plugin_screen_hook_suffix['root']:
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script( $this->plugin_slug . '-datepicker-es', plugins_url( 'assets/js/datepicker.es.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), Pronosticos_Apuestas_TAP::VERSION );
                wp_enqueue_script( $this->plugin_slug . '-configuracion', plugins_url( 'assets/js/configuracion.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), Pronosticos_Apuestas_TAP::VERSION );
                break;
            case $this->plugin_screen_hook_suffix['configuracion']:
                break;
            case $this->plugin_screen_hook_suffix['suscripciones']:
                $op = isset($_POST['op']) ? $_POST['op'] : 'list';
                switch($op){
                    case 'new':
                        wp_enqueue_script('jquery-ui-core');
                        wp_enqueue_script('jquery-ui-widget');
                        wp_enqueue_script('jquery-ui-datepicker');
                        wp_enqueue_script( $this->plugin_slug . '-datepicker-es', plugins_url( 'assets/js/datepicker.es.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), Pronosticos_Apuestas_TAP::VERSION );
                        wp_enqueue_script('select2', plugins_url( 'assets/js/select2.min.js', __FILE__ ), array( 'jquery' ), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_script('select2_locale_es', plugins_url( 'assets/js/select2_locale_es.js', __FILE__ ), array( 'select2' ), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_script($this->plugin_slug .'suscripcion-add', plugins_url( 'assets/js/suscripcion-add.js', __FILE__ ), array( 'jquery', 'select2' ), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                    case 'add':
                        break;
                    case 'edit':
                    case 'update':
                        wp_enqueue_script('jquery-ui-core');
                        wp_enqueue_script('jquery-ui-widget');
                        wp_enqueue_script('jquery-ui-datepicker');
                        wp_enqueue_script( $this->plugin_slug . '-datepicker-es', plugins_url( 'assets/js/datepicker.es.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), Pronosticos_Apuestas_TAP::VERSION );
                        wp_enqueue_script($this->plugin_slug .'suscripcion-edit', plugins_url( 'assets/js/suscripcion-edit.js', __FILE__ ), array( 'jquery' ), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                    case 'delete':
                        break;
                    default: // list
                        $suscripciones = array(
                            'dataTable' => array(
                                'ajax' => array(
                                    'url' => admin_url('admin-ajax.php?action=pronostico_apuestas_listar_suscripciones'),
                                ),
                                'processing' => true,
                                'deferRender' => true,
                                'serverSide' => true,
                                'searching' => true,
                                'lengthMenu' => array(array(10, 25, 50, 75, 100, -1), array(10, 25, 50, 75, 100, "todos")),
                                'paging' => true,
                                'order' => array(0, 'desc'),
                                'language' => array(
                                    'url' => plugins_url('assets/js/Spanish-datatable.lang', __FILE__)
                                ),
                                'columns' => array(
                                    array('data' => 'numero'),
                                    array('data' => 'usuario', 'sortable' => false),
                                    array('data' => 'tipster', 'sortable' => false),
                                    array('data' => 'periodo'),
                                    array('data' => 'fecha_inicio', 'sortable' => false),
                                    array('data' => 'fecha_fin'),
                                    array('data' => 'forma_de_pago'),
                                    array('data' => 'estado'),
                                    array('data' => 'accion', 'sortable' => false)
                                )
                            )
                        );
                        wp_enqueue_script($this->plugin_slug .'suscripcion-list-vars', plugins_url( 'assets/js/suscripcion-list-vars.js', __FILE__ ));
                        wp_localize_script($this->plugin_slug .'suscripcion-list-vars', 'suscripciones', $suscripciones);
//                        wp_enqueue_script('dataTables.tableTools', plugins_url( 'assets/js/dataTables.tableTools.min.js', __FILE__ ), array( 'jquery', 'jquery.dataTables' ), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_script('jquery-ui-core');
                        wp_enqueue_script('jquery-ui-widget');
                        wp_enqueue_script('jquery-ui-datepicker');
                        wp_enqueue_script( $this->plugin_slug . '-datepicker-es', plugins_url( 'assets/js/datepicker.es.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), Pronosticos_Apuestas_TAP::VERSION );
                        wp_enqueue_script('jquery.dataTables', plugins_url( 'assets/js/jquery.dataTables.js', __FILE__ ), array( 'jquery' ), Pronosticos_Apuestas_TAP::VERSION);
                        wp_enqueue_script($this->plugin_slug .'suscripcion-list', plugins_url( 'assets/js/suscripcion-list.js', __FILE__ ), array( 'jquery' ), Pronosticos_Apuestas_TAP::VERSION);
                        break;
                }
                break;
            default:
                break;
        }

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        $this->plugin_screen_hook_suffix[ 'root' ] = add_menu_page(
            __( 'Pronosticos Apuestas', $this->plugin_slug ),
            __( 'Pronosticos Apuestas', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            '',
            'dashicons-admin-generic'
        );

        $this->plugin_screen_hook_suffix['configuracion'] = add_submenu_page(
            $this->plugin_slug,
            __('Pronosticos Apuestas', $this->plugin_slug),
            __('Ajustes', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_configuracion_page' )
        );
//        $this->plugin_screen_hook_suffix['pedidos'] = add_submenu_page(
//            $this->plugin_slug,
//            __('Pronosticos Apuestas', $this->plugin_slug),
//            __('Pedidos', $this->plugin_slug),
//            'manage_options',
//            $this->plugin_slug.'/pedidos',
//            array( $this, 'display_pedidos_page' )
//        );
        $this->plugin_screen_hook_suffix['suscripciones'] = add_submenu_page(
            $this->plugin_slug,
            __('Pronosticos Apuestas', $this->plugin_slug),
            __('Suscripciones', $this->plugin_slug),
            'manage_options',
            $this->plugin_slug.'/suscripciones',
            array( $this, 'display_suscripciones_page' )
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_configuracion_page() {
        include_once(plugin_dir_path( __FILE__ ) . 'views/configuracion.php' );
    }

    public function display_pedidos_page() {
        include_once(plugin_dir_path( __FILE__ ) .  'views/pedidos.php' );
    }

    public function display_suscripciones_page() {
        include_once(plugin_dir_path( __FILE__ ) .  'views/suscripciones.php' );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {
        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_slug ) . '">' . __( 'Ajustes', $this->plugin_slug ) . '</a>',
            ),
            $links
        );

    }

    /**
     * NOTE:     Actions are points in the execution of a page or process
     *           lifecycle that WordPress fires.
     *
     *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
     *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function save_promocion($promocion) {
        $promocion = array_merge($this->promocion, $promocion);
        update_option('PRONOSTICO_APUESTAS_PROMOCION', $promocion);
        add_settings_error('pronostico-apuestas-promocion', 'form-pronostico-apuestas-promocion', __('Promocion guardada satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
    }

    public function metodos_pago($metodos_pago)
    {
        $metodos_pago = array_merge($this->metodos_pago, $metodos_pago);
        update_option('PRONOSTICO_APUESTAS_METODOS_PAGO', $metodos_pago);
        add_settings_error('pronostico-apuestas-metodos-pago', 'form-pronostico-apuestas-metodos-pago', __('Metodos de pago actualizados satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
    }

    public function gestion_suscripciones($op)
    {
        switch($op){
            case 'new':
                $pedidos = PedidoRepository::get_instance()->findByNumberNotEmpty();
                include_once( __DIR__.'/views/suscripciones-add.php' );
                break;
            case 'add':
                if(isset($_POST['suscripcion']) && wp_verify_nonce($_POST['suscripcion']['_crsf_token'], 'form_pronostico_apuestas_suscripcion_add')){
                    $suscripcion = $_POST['suscripcion'];
                    try{
                        $pedido = ShoppingCartManager::get_instance()->getByNumber($suscripcion['numero']);
                        SuscriptionManager::get_instance()->createFromPedidoAndSuscripcion($pedido, $suscripcion);
                        add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Suscripcion agregada satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
                        include_once( __DIR__.'/views/suscripciones-new.php' );
                    }catch(\Exception $e){
                        add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', $e->getMessage());
                    }
                }else{
                    add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Error. Ha intentado realizar una operacion no autorizada.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()));
                    include_once( __DIR__.'/views/suscripciones-error.php' );
                }
                break;
            case 'edit':
                if(isset($_POST['suscripcion']) && wp_verify_nonce($_POST['suscripcion']['_crsf_token'], 'form_pronostico_apuestas_suscripcion_edit_'.$_POST['suscripcion']['id'])){
                    $s = $_POST['suscripcion'];
                    $suscripcion = SuscriptionManager::get_instance()->get_by_id($s['id']);
                    include_once( __DIR__.'/views/suscripciones-edit.php' );
                }else{
                    add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Error. Ha intentado realizar una operacion no autorizada.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()));
                    include_once( __DIR__.'/views/suscripciones-error.php' );
                }
                break;
            case 'update':
                if(isset($_POST['suscripcion']) && wp_verify_nonce($_POST['suscripcion']['_crsf_token'], 'form_pronostico_apuestas_suscripcion_update')){
                    $s = $_POST['suscripcion'];
                    $suscripcion = SuscriptionManager::get_instance()->update($s);
                    add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Suscripcion guardada satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
                    include_once( __DIR__.'/views/suscripciones-edit.php' );
                }else{
                    add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Error. Ha intentado realizar una operacion no autorizada.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()));
                    include_once( __DIR__.'/views/suscripciones-error.php' );
                }
                break;
            case 'delete':
                if(isset($_POST['suscripcion']) && wp_verify_nonce($_POST['suscripcion']['_crsf_token'], 'form_pronostico_apuestas_suscripcion_delete_'.$_POST['suscripcion']['id'])){
                    $s = $_POST['suscripcion'];
                    $result = SuscriptionManager::get_instance()->delete($s);
                    if((boolean)$result['success']){
                        add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Suscripcion eliminada satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
                    }else{
                        add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __($result['message'], Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()));
                    }
                    include_once( __DIR__.'/views/suscripciones-delete.php' );
                }else{
                    add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Error. Ha intentado realizar una operacion no autorizada.', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()));
                    include_once( __DIR__.'/views/suscripciones-error.php' );
                }
                break;
            case 'fix':
                SuscriptionManager::get_instance()->fixRecords();
                add_settings_error('pronostico-apuestas-suscripcion', 'form-pronostico-apuestas-suscripcion', __('Correccion aplicada satisfactoriamente', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug()), 'updated');
                include_once( __DIR__.'/views/suscripciones-fixed-records.php' );
                break;
            default: // list
                include_once( __DIR__.'/views/suscripciones-list.php' );
                break;
        }
    }

    /**
     * NOTE:     Filters are points of execution in which WordPress modifies data
     *           before saving it or sending it to the browser.
     *
     *           Filters: http://codex.wordpress.org/Plugin_API#Filters
     *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     * @since    1.0.0
     */
    public function filter_method_name()
    {

    }

    public function enviar_email_suscripcion_por_paysafecard(Suscripcion $suscripcion)
    {
        $current_user = $suscripcion->getUsuario();
        $template_uri = get_template_directory_uri();
        $homepage = esc_url(home_url('/'));
        $contact_page = of_get_option('epic_contact_page');
        $contact_page_link = esc_url(get_the_permalink($contact_page));
        $url_rss = esc_url(of_get_option('epic_url_rss'));
        $url_facebook = esc_url(of_get_option('epic_url_facebook'));
        $url_twitter = esc_url(of_get_option('epic_url_twitter'));
        $url_google_plus = esc_url(of_get_option('epic_url_google_plus'));
        $estado = $suscripcion->getEstado();
        $message_for_users = <<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            html {
                font-family: sans-serif;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            body {
                margin: 0;
            }
            .container {
                padding-right: 15px;
                padding-left: 15px;
                margin-right: auto;
                margin-left: auto;
            }
            @media (min-width: 768px) {
                .container {
                    width: 750px;
                }
            }
            @media (min-width: 992px) {
                .container {
                    width: 970px;
                }
            }
            @media (min-width: 1200px) {
                .container {
                    width: 1123px;
                }
            }
            .row {
                margin-right: -15px;
                margin-left: -15px;
            }
            .col-xs-12 {
                position: relative;
                min-height: 1px;
                padding-right: 15px;
                padding-left: 15px;
                width: 100%;
                float: left;
            }
            .center-block {
                display: block;
                margin-right: auto;
                margin-left: auto;
            }
            .header-bg {
                background: url("{$template_uri}/img/email-header.png") center center no-repeat;
                width: 100%;
                max-width: 1123px;
                height: 227px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                z-index: 10;
            }
            .footer-bg {
                background: url("{$template_uri}/img/email-footer.png") center center no-repeat;
                width: 100%;
                height: 58px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                max-width: 1123px;
            }
            .content-bg {
                background-color: #eae6e1;
                height: 400px;
                margin-top: -50px;
                margin-bottom: 10px;
                padding: 85px 130px 36px;
                z-index: 1;
                width: 863px;
            }
            .message-body {
                background-color: #fefefe;
                max-width: 840px;
                height: 100%;
                padding: 30px 60px;
            }
            /* Social Icon */
            .social-icons {
                list-style: none;
                padding-left: 0;
                height: 42px;
                width: 260px;
                margin-top: 10px;
                margin-bottom: 0;
            }
            .social-icons > li {
                padding: 0 5px;
                display: block;
                float: left;
            }

            .social-icons > li > a {
                background: url("{$template_uri}/img/email-social-icons.png") no-repeat;
                color: transparent;
                height: 42px;
                width: 42px;
                display: block;
                position: relative;
            }

            .social-icons > li > a.icon-rss {
                background-position: 0 0;
            }

            .social-icons > li > a.icon-facebook {
                background-position: -51px 0;
            }

            .social-icons > li > a.icon-twitter {
                background-position: -103px 0;
            }

            .social-icons > li > a.icon-google-plus {
                background-position: -155px 0;
            }

            .social-icons > li > a.icon-envelope {
                background-position: -206px 0;
            }

            .social-icons > li > a:hover, .social-icons > li > a:focus {
                background-color: transparent;
            }

            .social-icons > li > a.icon-rss:hover, .social-icons > li > a.icon-rss:focus {
                background-position: 0 -45px;
            }

            .social-icons > li > a.icon-facebook:hover, .social-icons > li > a.icon-facebook:focus {
                background-position: -51px -45px;
            }

            .social-icons > li > a.icon-twitter:hover, .social-icons > li > a.icon-twitter:focus {
                background-position: -103px -45px;
            }

            .social-icons > li > a.icon-google-plus:hover, .social-icons > li > a.icon-google-plus:focus {
                background-position: -155px -45px;
            }

            .social-icons > li > a.icon-envelope:hover, .social-icons > li > a.icon-envelope:focus {
                background-position: -206px -45px;
            }
            .btn-change-password{
                background-color: #5CA595;
                color: #fff;
                padding: 10px 15px;
            }
            .btn-change-password:hover{
                background-color: #BAD437;
            }
            .color-333333,
            .color-333333:visited{
                color: #333333;
            }
            hr {
                margin: 20px 0;
                color: #dedad6;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="header-bg"></div>
                    <div class="content-bg">
                        <div class="message-body">
                            <p>Estimado/a {$current_user->display_name},</p>
                            <p>Su suscripci&oacute;n al servicio <strong>premium</strong> de <a href="{$homepage}">Apuesta Blog</a> se encuentra <strong>{$estado}</strong>.</p>
                            <p>Tu n&uacute;mero de orden es: <strong>{$suscripcion->getNumero()}</strong>.</p>
                            <p>Gracias,</p>
                            <p>El equipo de <a href="{$homepage}" class="color-333333">Apuesta Blog</a></p>
                        </div>
                        <hr>
                        <p class="text-center">
                            <a href="mailto:contacto@apuestablog.com" class="color-333333">contacto@apuestablog.com</a>
                        </p>
                        <p class="text-center">
                            <a href="{$homepage}" class="color-333333">www.apuestablog.com</a>
                        </p>
                    </div>
                    <div class="footer-bg">
                        <ul class="social-icons center-block">
                            <li>
                                <a class="icon-rss" href="{$url_rss}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-facebook" href="{$url_facebook}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-twitter" href="{$url_twitter}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-google-plus" href="{$url_google_plus}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-envelope" href="{$contact_page_link}">
                                    &nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message_for_admins = sprintf(__('Usuario: %s'), $current_user->user_login) . "\r\n\r\n";
        $message_for_admins .= sprintf(__('Orden #: %s'), $suscripcion->getNumero()) . "\r\n";
        $message_for_admins .= sprintf(__('Estado: %s'), $suscripcion->getEstado()) . "\r\n";

        $multiple_recipients = array(
            'contacto@todoapuestas.org',
            'aladroke@apuestablog.com'
        );
        @wp_mail($multiple_recipients, sprintf(__('[%s] Suscripcion por Paysafecard'), $blogname), $message_for_admins, $headers);


        if(!empty($current_user->user_email)){
            wp_mail($current_user->user_email, sprintf(__('[%s] Estado de la Suscripcion'), $blogname), $message_for_users, $headers);
        }
    }

    public function enviar_email_suscripcion_por_paypal_editada(Suscripcion $suscripcion)
    {
        $current_user = $suscripcion->getUsuario();
        $template_uri = get_template_directory_uri();
        $homepage = esc_url(home_url('/'));
        $contact_page = of_get_option('epic_contact_page');
        $contact_page_link = esc_url(get_the_permalink($contact_page));
        $url_rss = esc_url(of_get_option('epic_url_rss'));
        $url_facebook = esc_url(of_get_option('epic_url_facebook'));
        $url_twitter = esc_url(of_get_option('epic_url_twitter'));
        $url_google_plus = esc_url(of_get_option('epic_url_google_plus'));
        $estado = $suscripcion->getEstado();
        $message_for_users = <<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            html {
                font-family: sans-serif;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            body {
                margin: 0;
            }
            .container {
                padding-right: 15px;
                padding-left: 15px;
                margin-right: auto;
                margin-left: auto;
            }
            @media (min-width: 768px) {
                .container {
                    width: 750px;
                }
            }
            @media (min-width: 992px) {
                .container {
                    width: 970px;
                }
            }
            @media (min-width: 1200px) {
                .container {
                    width: 1123px;
                }
            }
            .row {
                margin-right: -15px;
                margin-left: -15px;
            }
            .col-xs-12 {
                position: relative;
                min-height: 1px;
                padding-right: 15px;
                padding-left: 15px;
                width: 100%;
                float: left;
            }
            .center-block {
                display: block;
                margin-right: auto;
                margin-left: auto;
            }
            .header-bg {
                background: url("{$template_uri}/img/email-header.png") center center no-repeat;
                width: 100%;
                max-width: 1123px;
                height: 227px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                z-index: 10;
            }
            .footer-bg {
                background: url("{$template_uri}/img/email-footer.png") center center no-repeat;
                width: 100%;
                height: 58px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                max-width: 1123px;
            }
            .content-bg {
                background-color: #eae6e1;
                height: 400px;
                margin-top: -50px;
                margin-bottom: 10px;
                padding: 85px 130px 36px;
                z-index: 1;
                width: 863px;
            }
            .message-body {
                background-color: #fefefe;
                max-width: 840px;
                height: 100%;
                padding: 30px 60px;
            }
            /* Social Icon */
            .social-icons {
                list-style: none;
                padding-left: 0;
                height: 42px;
                width: 260px;
                margin-top: 10px;
                margin-bottom: 0;
            }
            .social-icons > li {
                padding: 0 5px;
                display: block;
                float: left;
            }

            .social-icons > li > a {
                background: url("{$template_uri}/img/email-social-icons.png") no-repeat;
                color: transparent;
                height: 42px;
                width: 42px;
                display: block;
                position: relative;
            }

            .social-icons > li > a.icon-rss {
                background-position: 0 0;
            }

            .social-icons > li > a.icon-facebook {
                background-position: -51px 0;
            }

            .social-icons > li > a.icon-twitter {
                background-position: -103px 0;
            }

            .social-icons > li > a.icon-google-plus {
                background-position: -155px 0;
            }

            .social-icons > li > a.icon-envelope {
                background-position: -206px 0;
            }

            .social-icons > li > a:hover, .social-icons > li > a:focus {
                background-color: transparent;
            }

            .social-icons > li > a.icon-rss:hover, .social-icons > li > a.icon-rss:focus {
                background-position: 0 -45px;
            }

            .social-icons > li > a.icon-facebook:hover, .social-icons > li > a.icon-facebook:focus {
                background-position: -51px -45px;
            }

            .social-icons > li > a.icon-twitter:hover, .social-icons > li > a.icon-twitter:focus {
                background-position: -103px -45px;
            }

            .social-icons > li > a.icon-google-plus:hover, .social-icons > li > a.icon-google-plus:focus {
                background-position: -155px -45px;
            }

            .social-icons > li > a.icon-envelope:hover, .social-icons > li > a.icon-envelope:focus {
                background-position: -206px -45px;
            }
            .btn-change-password{
                background-color: #5CA595;
                color: #fff;
                padding: 10px 15px;
            }
            .btn-change-password:hover{
                background-color: #BAD437;
            }
            .color-333333,
            .color-333333:visited{
                color: #333333;
            }
            hr {
                margin: 20px 0;
                color: #dedad6;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="header-bg"></div>
                    <div class="content-bg">
                        <div class="message-body">
                            <p>Estimado/a {$current_user->display_name},</p>
                            <p>Su suscripci&oacute;n al servicio <strong>premium</strong> de <a href="{$homepage}">Apuesta Blog</a> se encuentra <strong>{$estado}</strong>.</p>
                            <p>Tu n&uacute;mero de orden es: <strong>{$suscripcion->getNumero()}</strong>.</p>
                            <p>Gracias,</p>
                            <p>El equipo de <a href="{$homepage}" class="color-333333">Apuesta Blog</a></p>
                        </div>
                        <hr>
                        <p class="text-center">
                            <a href="mailto:contacto@apuestablog.com" class="color-333333">contacto@apuestablog.com</a>
                        </p>
                        <p class="text-center">
                            <a href="{$homepage}" class="color-333333">www.apuestablog.com</a>
                        </p>
                    </div>
                    <div class="footer-bg">
                        <ul class="social-icons center-block">
                            <li>
                                <a class="icon-rss" href="{$url_rss}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-facebook" href="{$url_facebook}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-twitter" href="{$url_twitter}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-google-plus" href="{$url_google_plus}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-envelope" href="{$contact_page_link}">
                                    &nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message_for_admins = sprintf(__('Usuario: %s'), $current_user->user_login) . "\r\n\r\n";
        $message_for_admins .= sprintf(__('Orden #: %s'), $suscripcion->getNumero()) . "\r\n";
        $message_for_admins .= sprintf(__('Estado: %s'), $suscripcion->getEstado()) . "\r\n";

        $multiple_recipients = array(
            'contacto@todoapuestas.org',
            'aladroke@apuestablog.com'
        );
        @wp_mail($multiple_recipients, sprintf(__('[%s] Suscripcion por Paypal'), $blogname), $message_for_admins, $headers);


        if(!empty($current_user->user_email)){
            wp_mail($current_user->user_email, sprintf(__('[%s] Estado de la Suscripcion'), $blogname), $message_for_users, $headers);
        }
    }

    public function enviar_email_from($from_email)
    {
        $from_email = 'contacto@apuestablog.com';
        return $from_email;
    }

    public function enviar_email_from_name($from_name)
    {
        $from_name = 'Apuesta Blog';
        return $from_name;
    }

    public function enviar_email_content_type($content_type)
    {
        $content_type = 'text/html';
        return $content_type;
    }

    /**
     * Send emails to user with suscription active
     *
     * @param            $post_id
     * @param bool|false $post
     */
    public function save_post($post_id, $post = false, $update = false)
    {
        $tipo_publicacion = get_post_meta($post_id, '_post_tipo_publicacion', true);
        $email_enviado = intval(get_post_meta($post_id, '_pronostico_apuesta_email_enviado', true));

        if(false === wp_is_post_revision($post) && strcmp($post->post_type, 'post') === 0 && strcmp($tipo_publicacion, 'pick') === 0){
            $resultado = get_post_meta($post_id, '_pick_resultado', true);
            $pronostico_pago = get_post_meta($post->ID, '_pick_pronostico_pago', true);
            if(strcmp($pronostico_pago, 'on' === 0)){
                $pronostico_pago = true;
            }else{
                $pronostico_pago = false;
            }
            if(0 === $email_enviado && $pronostico_pago && strcmp($resultado, 'pendiente') === 0){
                $tipster_id = get_post_meta($post_id, '_pick_tipster', true);
                $tipster = TipsterRepository::get_instance()->findBy($tipster_id);

                $evento     = wp_specialchars_decode( get_post_meta( $post_id, '_pick_evento', true ), ENT_QUOTES );
                $pronostico = wp_specialchars_decode( get_post_meta( $post_id, '_pick_pronostico', true ), ENT_QUOTES );
                $cuota      = get_post_meta( $post_id, '_pick_cuota', true );
                $stake      = get_post_meta( $post_id, '_pick_stake', true );
                $casa       = get_post_meta( $post_id, '_pick_casa_apuesta', true );
                $bookies    = get_option( 'tipster_tap_bookies' );
                $fecha      = get_post_meta( $post_id, '_pick_fecha_evento', true );
                $hora       = get_post_meta( $post_id, '_pick_hora_evento', true );
                $tipo_apuesta = ucfirst(get_post_meta( $post_id, '_pick_tipo_apuesta', true ));

                $fecha_evento = \DateTime::createFromFormat('d/m/Y H:i', $fecha.' '.$hora);
                $ahora = new \DateTime('now');
                $tiempo_restante = $this->tiempo_restante($fecha_evento->getTimestamp() - $ahora->getTimestamp());
                $fecha_evento = ucwords(date_i18n('j F, Y H:i', $fecha_evento->getTimestamp()));

                $pick_url          = esc_url( get_the_permalink( $post_id ) );
                $template_uri      = get_template_directory_uri();
                $homepage          = esc_url( home_url( '/' ) );
                $contact_page      = of_get_option( 'epic_contact_page' );
                $contact_page_link = esc_url( get_the_permalink( $contact_page ) );
                $url_rss           = esc_url( of_get_option( 'epic_url_rss' ) );
                $url_facebook      = esc_url( of_get_option( 'epic_url_facebook' ) );
                $url_twitter       = esc_url( of_get_option( 'epic_url_twitter' ) );
                $url_google_plus   = esc_url( of_get_option( 'epic_url_google_plus' ) );

                $headers = array('Content-Type: text/html; charset=UTF-8');
                $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

                $estado = Suscripcion::ACTIVO;
                $suscripciones = SuscriptionManager::get_instance()->get_suscripciones($tipster_id, $estado);
                $suscriptores = array();
                foreach ( $suscripciones as $suscripcion ) {
                    $usuario = $suscripcion->getUsuario();
                    $suscriptores[] = array(
                        'numero' => $suscripcion->getNumero(),
                        'usuario' => $usuario->display_name,
                    );
                    $message_for_suscriptor = <<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            html {
                font-family: sans-serif;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            body {
                margin: 0;
            }
            .container {
                padding-right: 15px;
                padding-left: 15px;
                margin-right: auto;
                margin-left: auto;
            }
            @media (min-width: 768px) {
                .container {
                    width: 750px;
                }
            }
            @media (min-width: 992px) {
                .container {
                    width: 970px;
                }
            }
            @media (min-width: 1200px) {
                .container {
                    width: 1123px;
                }
            }
            .row {
                margin-right: -15px;
                margin-left: -15px;
            }
            .col-xs-12 {
                position: relative;
                min-height: 1px;
                padding-right: 15px;
                padding-left: 15px;
                width: 100%;
                float: left;
            }
            .center-block {
                display: block;
                margin-right: auto;
                margin-left: auto;
            }
            .header-bg {
                background: url("{$template_uri}/img/email-header.png") center center no-repeat;
                width: 100%;
                max-width: 1123px;
                height: 227px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                z-index: 10;
            }
            .footer-bg {
                background: url("{$template_uri}/img/email-footer.png") center center no-repeat;
                width: 100%;
                height: 58px;
                display: inline-block;
                vertical-align: middle;
                position: static;
                max-width: 1123px;
            }
            .content-bg {
                background-color: #eae6e1;
                height: 400px;
                margin-top: -50px;
                margin-bottom: 10px;
                padding: 85px 130px 36px;
                z-index: 1;
                width: 863px;
            }
            .message-body {
                background-color: #fefefe;
                max-width: 840px;
                height: 100%;
                padding: 30px 60px;
            }
            .text-center {
                text-align: center;
            }
            .text-uppercase{
                text-transform: uppercase;
            }
            /* Social Icon */
            .social-icons {
                list-style: none;
                padding-left: 0;
                height: 42px;
                width: 260px;
                margin-top: 10px;
                margin-bottom: 0;
            }
            .social-icons > li {
                padding: 0 5px;
                display: block;
                float: left;
            }

            .social-icons > li > a {
                background: url("{$template_uri}/img/email-social-icons.png") no-repeat;
                color: transparent;
                height: 42px;
                width: 42px;
                display: block;
                position: relative;
            }

            .social-icons > li > a.icon-rss {
                background-position: 0 0;
            }

            .social-icons > li > a.icon-facebook {
                background-position: -51px 0;
            }

            .social-icons > li > a.icon-twitter {
                background-position: -103px 0;
            }

            .social-icons > li > a.icon-google-plus {
                background-position: -155px 0;
            }

            .social-icons > li > a.icon-envelope {
                background-position: -206px 0;
            }

            .social-icons > li > a:hover, .social-icons > li > a:focus {
                background-color: transparent;
            }

            .social-icons > li > a.icon-rss:hover, .social-icons > li > a.icon-rss:focus {
                background-position: 0 -45px;
            }

            .social-icons > li > a.icon-facebook:hover, .social-icons > li > a.icon-facebook:focus {
                background-position: -51px -45px;
            }

            .social-icons > li > a.icon-twitter:hover, .social-icons > li > a.icon-twitter:focus {
                background-position: -103px -45px;
            }

            .social-icons > li > a.icon-google-plus:hover, .social-icons > li > a.icon-google-plus:focus {
                background-position: -155px -45px;
            }

            .social-icons > li > a.icon-envelope:hover, .social-icons > li > a.icon-envelope:focus {
                background-position: -206px -45px;
            }
            .color-333333,
            .color-333333:visited{
                color: #333333;
            }
            .color-5CA595,
            .color-5CA595:visited{
                color: #5CA595;
            }
            hr {
                margin: 20px 0;
                color: #dedad6;
            }
            table {
                border-spacing: 0;
                border-collapse: collapse;
                background-color: transparent;
            }
            td,
            th {
                padding: 0;
            }
            .table {
                width: 100%;
                max-width: 100%;
                margin-bottom: 20px;
            }
            .table > thead > tr > th,
            .table > tbody > tr > th,
            .table > tfoot > tr > th,
            .table > thead > tr > td,
            .table > tbody > tr > td,
            .table > tfoot > tr > td {
                padding: 8px;
                line-height: 1.42857143;
                vertical-align: top;
                border-top: 1px solid #ddd;
            }
            .table > thead > tr > th {
                vertical-align: bottom;
                border-bottom: 2px solid #31917d;
                color: #ffffff;
            }
            .table-bordered {
                border: 1px solid #ddd;
            }
            .table > thead > tr > th.info {
                background-color: #31917d;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="header-bg"></div>
                    <div class="content-bg">
                        <div class="message-body">
                            <p>Estimado/a {$usuario->display_name},</p>
                            <p>A continuaci&oacute;n encontrar&aacute;s los detalles del tip</p>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="info text-uppercase">Detalles del tip</th>
                                        <th class="info">Tipster: {$tipster->post_title}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>{$evento}</strong></td>
                                        <td>Tipo de apuesta: {$tipo_apuesta}</td>
                                    </tr>
                                    <tr>
                                        <td><span class="text-uppercase">Apuesta pre-partido</span></td>
                                        <td>Apuesta: {$pronostico}</td>
                                    </tr>
                                    <tr>
                                        <td>Fecha: {$fecha_evento}</td>
                                        <td>Mejor cuota: {$cuota}</td>
                                    </tr>
                                    <tr>
                                        <td>Tiempo restante: {$tiempo_restante} (cuando se envi&oacute;)</td>
                                        <td>Stake: {$stake}% de {$bookies[ $casa ][ "nombre" ]}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p><a href="{$pick_url}" target="_blank" title="{$evento}" class="color-5CA595">Clic aqu&iacute; si quieres leer el an&aacute;lisis pre-evento.</a></p>
                        </div>
                        <hr>
                        <p class="text-center">
                            <a href="mailto:contacto@apuestablog.com" class="color-333333">contacto@apuestablog.com</a>
                        </p>
                        <p class="text-center">
                            <a href="{$homepage}" class="color-333333">www.apuestablog.com</a>
                        </p>
                    </div>
                    <div class="footer-bg">
                        <ul class="social-icons center-block">
                            <li>
                                <a class="icon-rss" href="{$url_rss}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-facebook" href="{$url_facebook}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-twitter" href="{$url_twitter}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-google-plus" href="{$url_google_plus}">
                                    &nbsp;
                                </a>
                            </li>
                            <li>
                                <a class="icon-envelope" href="{$contact_page_link}">
                                    &nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
EOF;

                    if(!empty($usuario->user_email)){
                        wp_mail($usuario->user_email, sprintf(__('[%s] Detalles del tip'), $blogname), $message_for_suscriptor, $headers);
                    }
                }

                if(!empty($suscriptores)){
                    $message_for_admins = sprintf('Usuario(s) que han recibido el tip de: "%s"', $evento). "\r\n\r\n";
                    foreach ( $suscriptores as $suscriptor ) {
                        $message_for_admins .= sprintf(__('Usuario: %s , Orden #: %s'), $suscriptor['usuario'], $suscriptor['numero']) . "\r\n";
                    }

                    $multiple_recipients = array(
                        'contacto@todoapuestas.org',
                        'aladroke@apuestablog.com'
                    );
                    @wp_mail($multiple_recipients, sprintf(__('[%s] Pronostico enviado'), $blogname), $message_for_admins, $headers);
                }

                update_post_meta($post_id, '_pronostico_apuesta_email_enviado', 1);
            }
        }
    }

    private function tiempo_restante($segundos)
    {
        $bit = array(
            'a' => $segundos / 31556926 % 12,
            'sem' => $segundos / 604800 % 52,
            'd' => $segundos / 86400 % 7,
            'h' => $segundos / 3600 % 24,
            'm' => $segundos / 60 % 60,
            's' => $segundos % 60
        );

        $ret = array();
        foreach($bit as $k => $v)
            if($v > 0)$ret[] = $v . $k;

        return join(' ', $ret);
    }
}
