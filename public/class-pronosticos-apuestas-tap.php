<?php
/**
 * Pronosticos Apuestas TAP.
 *
 * @package   Pronosticos_Apuestas_TAP
 * @author    Alain Sanchez <luka.ghost@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.linkedin.com/in/mrbrazzi/
 * @copyright 2014 Alain Sanchez
 */
namespace PronosticosApuestasTAP\Frontend;

use PronosticosApuestasTAP\Backend\Pronosticos_Apuestas_TAP_Admin;
use PronosticosApuestasTAP\Common\Pedido;
use PronosticosApuestasTAP\Common\PedidoRepository;
use PronosticosApuestasTAP\Common\ShoppingCartManager;
use PronosticosApuestasTAP\Common\Suscripcion;
use PronosticosApuestasTAP\Common\SuscripcionRepository;
use PronosticosApuestasTAP\Common\SuscriptionManager;
use PronosticosApuestasTAP\Common\TipsterRepository;

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-pronosticos-apuestas-tap-admin.php`
 *
 * @package Pronosticos_Apuestas_TAP
 * @author  Alain Sanchez <luka.ghost@gmail.com>
 */
class Pronosticos_Apuestas_TAP {
    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.2.6';
    /**
     * Unique identifier for your plugin.
     *
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'pronosticos-apuestas-tap';
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct() {
        // Load plugin text domain
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        // Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        /*
         * Define custom functionality.
         * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_action( 'pronostico_apuestas_user_bar_menuitem', array( $this, 'user_bar_menuitem' ) );
//        add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );
//		add_filter( 'wp_filter', array( $this, 'filter_method_name' ) );
        /**
         * Shopping Cart actions
         */
        add_action( 'pronostico_apuestas_checkout_shopping_cart', array( $this, 'checkout_shopping_cart' ), 10, 1 );
        add_action( 'wp_ajax_nopriv_pronostico_apuestas_update_shopping_cart', array( $this, 'update_shopping_cart' ) );
        add_action( 'wp_ajax_pronostico_apuestas_update_shopping_cart', array( $this, 'update_shopping_cart' ) );
        /**
         * cupon
         */
        add_action( 'wp_ajax_nopriv_pronostico_apuestas_validar_cupon', array( $this, 'validar_cupon' ) );
        add_action( 'wp_ajax_pronostico_apuestas_validar_cupon', array( $this, 'validar_cupon' ) );
        /**
         * paypal
         */
        add_action( 'wp_ajax_nopriv_pronostico_apuestas_confirm_paypal', array($this, 'confirm_paypal') );
        add_action( 'wp_ajax_pronostico_apuestas_confirm_paypal', array($this, 'confirm_paypal') );
        add_action( 'pronostico_apuestas_paypal_response', array( $this, 'paypal_response' ), 10, 1 );
        /**
         * emails
         */
        add_action( 'pronostico_apuestas_enviar_email_suscripcion_por_paypal', array( $this, 'enviar_email_suscripcion_por_paypal' ), 10, 1 );
        add_action( 'pronostico_apuestas_enviar_email_suscripcion_por_paysafecard_creada', array( $this, 'enviar_email_suscripcion_por_paysafecard_creada' ), 10, 1 );
        add_filter( 'wp_mail_from', array( $this, 'enviar_email_from' ), 10, 1 );
        add_filter( 'wp_mail_from_name', array( $this, 'enviar_email_from_name' ), 10, 1 );
        add_filter( 'wp_mail_content_type', array( $this, 'enviar_email_content_type' ), 10, 1 );
        /**
         * schedule
         */
        add_action( 'wp' , array( $this, 'activar_cancelar_suscripcion'));
        add_action( 'pronostico_apuestas_cancelar_suscripcion_hourly_event', array( $this, 'cancelar_suscripcion' ) );
        add_action( 'pronostico_apuestas_cancelar_suscripcion', array( $this, 'cancelar_suscripcion' ) );

        add_action( 'wp_ajax_pronostico_apuestas_listar_suscripciones', array( $this, 'listar_suscripciones' ) );
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide       True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate( $network_wide ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            if ( $network_wide ) {
                // Get all blog ids
                $blog_ids = self::get_blog_ids();
                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::single_activate();
                    restore_current_blog();
                }

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean $network_wide       True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() ) {
            if ( $network_wide ) {
                // Get all blog ids
                $blog_ids = self::get_blog_ids();
                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::single_deactivate();
                    restore_current_blog();

                }

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int $blog_id ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {
        if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
            return;
        }
        switch_to_blog( $blog_id );
        self::single_activate();
        restore_current_blog();

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids() {
        global $wpdb;
        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

        return $wpdb->get_col( $sql );

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private static function single_activate() {
        add_option( 'PRONOSTICO_APUESTAS_PROMOCION', Pronosticos_Apuestas_TAP_Admin::get_instance()->get_promocion() );
        add_option( 'PRONOSTICO_APUESTAS_METODOS_PAGO', Pronosticos_Apuestas_TAP_Admin::get_instance()->get_metodos_pago() );

        //execute create statistics table
        self::get_instance()->create_tables();
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate() {
        remove_action( 'pronostico_apuestas_cancelar_suscripcion_hourly_event', array( self::$instance, 'cancelar_suscripcion' ) );
        remove_action( 'wp' , array( self::$instance, 'activar_cancelar_suscripcion'));

        wp_delete_post( intval( get_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' ) ), true );
        wp_delete_post( intval( get_option( 'PRONOSTICO_APUESTAS_PAGINA_SOPORTE' ) ), true );
        wp_delete_post( intval( get_option( 'PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_ACEPTADO' ) ), true );
        wp_delete_post( intval( get_option( 'PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_CANCELADO' ) ), true );

//        delete_option( 'PRONOSTICO_APUESTAS_PROMOCION' );
//        delete_option( 'PRONOSTICO_APUESTAS_METODOS_PAGO' );
        delete_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' );
        delete_option( 'PRONOSTICO_APUESTAS_PAGINA_SOPORTE' );
        delete_option( 'PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_ACEPTADO' );
        delete_option( 'PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_CANCELADO' );
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        if(is_single(get_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' ))){
            wp_enqueue_style( $this->plugin_slug . '-cart', plugins_url( 'assets/css/cart.css', __FILE__ ), array('bootstrap',), self::VERSION );
        }
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        if(is_single(get_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' ))){
            $cart_vars = array(
                'url' => admin_url('admin-ajax.php'),
            );
            wp_enqueue_script('cart-vars', plugins_url( 'assets/js/cart-vars.js', __FILE__ ));
            wp_localize_script('cart-vars', 'cartVars', $cart_vars);
            // we always need jquery:
            wp_enqueue_script('jquery');
            wp_enqueue_script( $this->plugin_slug . '-cart', plugins_url( 'assets/js/cart.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        }
//        wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
    }

    /**
     * @since    1.0.0
     */
    public function user_bar_menuitem() {
        global $post; ?>
        <li>
            <?php $page_id = get_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' ); ?>
            <?php $post = get_post( $page_id ); ?>
            <?php the_title( sprintf( '<a href="%s" rel="bookmark"><span class="user-bar-menuitem icon-cart"></span>', esc_url( get_permalink() ) ), '</a>' ); ?>
        </li>
        <li>
        <?php $page_id = get_option( 'PRONOSTICO_APUESTAS_PAGINA_SOPORTE' ); ?>
        <?php $post = get_post( $page_id ); ?>
        <?php the_title( sprintf( '<a href="%s" rel="bookmark"><span class="user-bar-menuitem icon-support"></span>', esc_url( get_permalink() ) ), '</a>' ); ?>
        </li><?php
        wp_reset_postdata();
    }

    /**
     * @since    1.0.0
     */
    public function admin_bar_menu() {
        global $wp_admin_bar;
        $page_id = get_option( 'PRONOSTICO_APUESTAS_PAGINA_CARRITO' );
        if ( $page_id ) {
            $page = get_post( $page_id );
            /* Add the main siteadmin menu item */
            $wp_admin_bar->add_menu( array(
                    'id'     => 'pronostico-apuesta-pagina-carrito',
                    'parent' => 'top-secondary',
                    'title'  => get_the_title( $page->ID ),
                    'href'   => get_permalink( $page->ID ),
                )
            );
        }
        $page_id = get_option( 'PRONOSTICO_APUESTAS_PAGINA_SOPORTE' );
        if ( $page_id ) {
            $page = get_post( $page_id );
            $wp_admin_bar->add_menu( array(
                    'id'     => 'pronostico-apuesta-pagina-soporte',
                    'parent' => 'top-secondary',
                    'title'  => get_the_title( $page->ID ),
                    'href'   => get_permalink( $page->ID ),
                )
            );
        }
    }

    /**
     * @since    1.0.0
     */
    public function create_tables() {
        PedidoRepository::get_instance()->create_table();
        SuscripcionRepository::get_instance()->create_table();
    }

    /**
     * @since    1.0.0
     */
    public function checkout_shopping_cart($step = 'resumen')
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        global $current_user, $post;

        $tipster = $cart = null;

        if ( is_user_logged_in() ) {
            get_currentuserinfo();
        }

        switch($step){
            case 'pago':
                if (! is_user_logged_in() ) {
                    break;
                }
                if(isset($_POST['pedido']) && strcmp($_POST['pedido'], $_SESSION['TAPASCID']) === 0){
                    $pedido = $_POST['pedido'];

                    $cart = ShoppingCartManager::get_instance()->getCart($pedido);
                    $ahora = new \DateTime();
                    $cart->setNumero($ahora->format('YmdHis'));
                    PedidoRepository::get_instance()->update($cart);
                }
                break;
            case 'paysafecard':
                if (! is_user_logged_in() ) {
                    break;
                }
                if(isset($_POST['pedido']) && strcmp($_POST['pedido'], $_SESSION['TAPASCID']) === 0){
                    $pedido = $_POST['pedido'];

                    $cart = ShoppingCartManager::get_instance()->getCart($pedido);
                    $cart->setFormaDePago(Pedido::PAYSAFECARD);
                    $cart->setEstado(Pedido::PREPEDIDO);
                    PedidoRepository::get_instance()->update($cart);

                    SuscriptionManager::get_instance()->create($cart);
                    do_action('pronostico_apuestas_enviar_email_suscripcion_por_paysafecard_creada', $cart);
                }
                unset($_SESSION['TAPASCID']);
                unset($_SESSION['PAYPAL_TOKEN']);
                break;
            default: // resumen
                $tipster_id = null;

                if(isset($_GET['tipster'])){
                    $tipster_id = $_GET['tipster'];
                }

                $tipster = TipsterRepository::get_instance()->findBy($tipster_id);

                $cart = ShoppingCartManager::get_instance()->getCurrentCart( $current_user, $tipster );

                break;
        }

        if(null === $cart){
            unset($_SESSION['TAPASCID']);
            include_once( __DIR__ . '/views/shopping-cart-invalid.php' );
        }else{
            include_once( __DIR__ . '/views/shopping-cart-step-'.$step.'.php' );
        }
    }

    public function update_shopping_cart()
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        $response = array();

        if( ( isset($_POST['cart']) && strcmp($_POST['cart'],$_SESSION['TAPASCID']) === 0 ) && isset($_POST['tipster']) && isset($_POST['periodo']) ){
            $cart = $_POST['cart'];
            $tipster = $_POST['tipster'];
            $periodo = $_POST['periodo'];
            $cupon = isset($_POST['cupon']) && !empty($_POST['cupon']) ? $_POST['cupon'] : null;

            $precio = 0;
            $tipster_suscripcion = get_post_meta($tipster, '_tipster_suscripcion', true);
            foreach ( $tipster_suscripcion as $suscripcion ) {
                if($suscripcion['periodo'] === $periodo){
                    $precio = $suscripcion['precio'];
                    break;
                }
            }

            $pedido = ShoppingCartManager::get_instance()->getCart($cart);
            $elementos = $pedido->getElementos();
            $elementos[$tipster]['periodo'] = $periodo;
            $elementos[$tipster]['precio'] = $precio;
            $pedido->setElementos($elementos);

            $pedido = $this->verificar_cupon($pedido, $cupon);

            PedidoRepository::get_instance()->update($pedido);

            $response = array(
                    'total' => number_format($pedido->getTotal(), 2, '.', ''),
                    'subtotal' => number_format($pedido->getSubtotal(), 2, '.', ''),
                    'descuento' => number_format($pedido->getDescuento(), 2, '.', ''),
                    'precio' => number_format($precio, 2, '.', ''),
                    'tipster' => $tipster
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public function validar_cupon()
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        $response = array();

        if(isset($_POST['cart']) && isset($_POST['cupon'])){
            $cart = $_POST['cart'];
            $cupon = isset($_POST['cupon']) ? $_POST['cupon']  : null;

            $pedido = ShoppingCartManager::get_instance()->getCart($cart);

            $pedido = $this->verificar_cupon($pedido, $cupon);

            PedidoRepository::get_instance()->update($pedido);

            $response = array(
                'total' => number_format($pedido->getTotal(), 2, '.', ''),
                'descuento' => number_format($pedido->getDescuento(), 2, '.', ''),
                'valido' => $pedido->getDescuento() === 0 ? 0 : 1
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * @param \PronosticosApuestasTAP\Common\Pedido $pedido
     * @param null                                  $cupon
     *
     * @return \PronosticosApuestasTAP\Common\Pedido
     */
    private function verificar_cupon(Pedido $pedido, $cupon = null)
    {
        if(null !== $cupon){ // verificar si es un cupon valido
            $promocion = get_option('PRONOSTICO_APUESTAS_PROMOCION');
            if(!empty($promocion) && isset($promocion['codigo']) && isset($promocion['descuento']) && isset($promocion['fecha_fin'])){
                if(strcmp($promocion['codigo'], $cupon) === 0){ // se verifica si el cupon es valido segun el codigo
                    $promocion['fecha_fin'] = \DateTime::createFromFormat('d/m/Y', $promocion['fecha_fin']);

                    $ahora = new \DateTime('now');
                    if($ahora <= $promocion['fecha_fin']){ // se verifica si el cupon todavia esta vigente
                        $pedido->setCupon($promocion['codigo']);
                        $subtotal = $pedido->getSubtotal();
                        $descuento = ( floatval($subtotal) * floatval($promocion['descuento']) ) / 100;
                        $pedido->setDescuento($descuento);
                    }else{
                        $pedido->setCupon(null);
                        $pedido->setDescuento(0);
                    }
                }else{
                    $pedido->setCupon(null);
                    $pedido->setDescuento(0);
                }
            }else{
                $pedido->setCupon(null);
                $pedido->setDescuento(0);
            }
        }else{
            $pedido->setCupon(null);
            $pedido->setDescuento(0);
        }

        return $pedido;
    }

    public function confirm_paypal()
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        $response = array();

        if(isset($_POST['cart']) && $_POST['cart'] === $_SESSION['TAPASCID']){
            $token = wp_create_nonce('pronostico_apuestas_paypal_check');
            $_SESSION['PAYPAL_TOKEN'] = $token;
            $cart = $_POST['cart'];
            $pedido = ShoppingCartManager::get_instance()->getCart($cart);
            $response = array(
                'success' => true,
                'token' => $token,
                'pid' => $pedido->getNumero()
            );

        }else{
            $response['success'] = false;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public function paypal_response($response)
    {
        $session_id = session_id();
        if(empty($session_id)) @session_start();
        $cart = null;

        if(is_user_logged_in() && isset($_GET['token']) && strcmp($_GET['token'], $_SESSION['PAYPAL_TOKEN']) === 0 && wp_verify_nonce($_GET['token'], 'pronostico_apuestas_paypal_check')){

            $cart = $_SESSION['TAPASCID'];
            $pedido = ShoppingCartManager::get_instance()->getCart($cart);

            unset($_SESSION['TAPASCID']);
            unset($_SESSION['PAYPAL_TOKEN']);

            if(null !== $pedido){
                $pedido->setFormaDePago(Pedido::PAYPAL);

                switch($response){
                    case 'cancel':
                        $pedido->setEstado(Pedido::CANCELADO);
                        break;
                    default: // success
                        $pedido->setEstado(Pedido::PAGADO);
                        SuscriptionManager::get_instance()->create($pedido);
                        break;
                }
                PedidoRepository::get_instance()->update($pedido);

                include_once( __DIR__ . '/views/paypal-response-'.$response.'.php' );

            }else{
                include_once( __DIR__ . '/views/shopping-cart-invalid.php' );
            }

        }else{
            include_once( __DIR__ . '/views/paypal-response-invalid.php' );
        }
    }

    public function enviar_email_suscripcion_por_paypal(Pedido $pedido)
    {
        $current_user = $pedido->getUsuario();
        $template_uri = get_template_directory_uri();
        $homepage = esc_url(home_url('/'));
        $contact_page = of_get_option('epic_contact_page');
        $contact_page_link = esc_url(get_the_permalink($contact_page));
        $url_rss = esc_url(of_get_option('epic_url_rss'));
        $url_facebook = esc_url(of_get_option('epic_url_facebook'));
        $url_twitter = esc_url(of_get_option('epic_url_twitter'));
        $url_google_plus = esc_url(of_get_option('epic_url_google_plus'));
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
                            <p>Gracias por suscribirte al servicio <strong>premium</strong> de <a href="{$homepage}">Apuesta Blog</a>.</p>
                            <p>Has elegido como m&eacute;todo de pago <strong>Paypal</strong>. Hemos recibido la confirmaci&oacute;n del pago de su suscripci&oacute;n.</p>
                            <p>Tu n&uacute;mero de orden es: <strong>{$pedido->getNumero()}</strong>.</p>
                            <p>Cuando el tipster publique los pron&oacute;sticos recibir&aacute;s autom&aacute;ticamente un correo electr&oacute;nico.</p>
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
        $headers = array(
                'From: Apuesta Blog <contacto@apuestablog.com>',
                'Content-Type: text/html; charset=UTF-8'
        );
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message_for_admins = sprintf(__('Usuario: %s'), $current_user->user_login) . "\r\n\r\n";
        $message_for_admins .= sprintf(__('Metodo de pago: %s'), $pedido->getFormaDePago()) . "\r\n";
        $message_for_admins .= sprintf(__('Orden #: %s'), $pedido->getNumero()) . "\r\n";

        $multiple_recipients = array(
                'contacto@todoapuestas.org',
                'aladroke@apuestablog.com'
        );
        @wp_mail($multiple_recipients, sprintf(__('[%s] Nueva suscripcion por Paypal'), $blogname), $message_for_admins, $headers);


        if(!empty($current_user->user_email)){
            wp_mail($current_user->user_email, sprintf(__('[%s] Solicitud de suscripcion creada'), $blogname), $message_for_users, $headers);
        }
    }

    public function enviar_email_suscripcion_por_paysafecard_creada(Pedido $pedido)
    {
        $current_user = $pedido->getUsuario();
        $template_uri = get_template_directory_uri();
        $homepage = esc_url(home_url('/'));
        $contact_page = of_get_option('epic_contact_page');
        $contact_page_link = esc_url(get_the_permalink($contact_page));
        $url_rss = esc_url(of_get_option('epic_url_rss'));
        $url_facebook = esc_url(of_get_option('epic_url_facebook'));
        $url_twitter = esc_url(of_get_option('epic_url_twitter'));
        $url_google_plus = esc_url(of_get_option('epic_url_google_plus'));
        $total = number_format($pedido->getTotal(), 2, '.', '');
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
                            <p>Gracias por suscribirte al servicio <strong>premium</strong> de <a href="{$homepage}">Apuesta Blog</a>.</p>
                            <p>Has elegido como m&eacute;todo de pago <strong>Paysafecard</strong>, para confirmar tu suscripci&oacute;n debes enviarnos la cantidad de <strong>{$total}</strong> &euro; en pines de <strong>Paysafecard</strong> a <a href="mailto:contacto@apuestablog.com" class="color-333333">contacto@apuestablog.com</a>.</p>
                            <p>Tu n&uacute;mero de orden es: <strong>{$pedido->getNumero()}</strong>.</p>
                            <p>Una vez confirmemos que los pines tienen saldo y son v&aacute;lidos activaremos la cuenta. Este proceso es manual as&iacute; que por favor ten paciencia.</p>
                            <p>Cuando el tipster publique los pron&oacute;sticos recibir&aacute;s autom&aacute;ticamente un correo electr&oacute;nico.</p>
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
        $message_for_admins .= sprintf(__('Metodo de pago: %s'), $pedido->getFormaDePago()) . "\r\n";
        $message_for_admins .= sprintf(__('Orden #: %s'), $pedido->getNumero()) . "\r\n";

        $multiple_recipients = array(
                'contacto@todoapuestas.org',
                'aladroke@apuestablog.com'
        );
        @wp_mail($multiple_recipients, sprintf(__('[%s] Nueva suscripcion por Paysafecard'), $blogname), $message_for_admins, $headers);


        if(!empty($current_user->user_email)){
            wp_mail($current_user->user_email, sprintf(__('[%s] Solicitud de suscripcion creada'), $blogname), $message_for_users, $headers);
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

    public function activar_cancelar_suscripcion() {
        if ( !wp_next_scheduled( 'pronostico_apuestas_cancelar_suscripcion_hourly_event' ) ) {
            wp_schedule_event(time(), 'hourly', 'pronostico_apuestas_cancelar_suscripcion_hourly_event');
        }
    }

    public function cancelar_suscripcion()
    {
        $ahora = new \DateTime('now');
        $estado = Suscripcion::ACTIVO;
        SuscriptionManager::get_instance()->cancelar_suscripcion($ahora->getTimestamp(), $estado);
    }

    public function listar_suscripciones()
    {
        $start = isset($_GET['start']) ? $_GET['start'] : 0;
        $limit = isset($_GET['length']) ? $_GET['length'] : 10;
        $order = $_GET['order'];
        $search = $_GET['search'];
        $filter = $_GET['filter'];

        $suscripciones = SuscriptionManager::get_instance()->get_list_elements($start, $limit, $order[0], $search['value'], $filter);
        $data = array();
        foreach ( $suscripciones as $suscripcion ) {
            $tipster_name = '';
            $tipster = TipsterRepository::get_instance()->findBy($suscripcion->getTipster());
            if(null !== $tipster){
                $tipster_name = get_the_title($tipster->ID);
            }

            $periodo = $suscripcion->getPeriodo();
            $fecha_inicio = $fecha_fin = '';
            if(strcmp($suscripcion->getEstado(), Suscripcion::ACTIVO) === 0){
                $fecha_fin = date('d/m/Y H:i', $suscripcion->getFechaFin());
                $fecha_inicio = new \DateTime();
                $fecha_inicio->setTimestamp($suscripcion->getFechaFin());
                $intervalo = new \DateInterval('P'.$periodo.'M1D');
                $fecha_inicio->sub($intervalo);
                $fecha_inicio = date('d/m/Y H:i', $fecha_inicio->getTimestamp());
            }

            $action_url = admin_url( 'admin.php?page='.Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug().'/suscripciones' );
            $nonce_field_edit = wp_nonce_field( 'form_pronostico_apuestas_suscripcion_edit_'.$suscripcion->getId(), 'suscripcion[_crsf_token]', true, false);
            $nonce_field_delete = wp_nonce_field( 'form_pronostico_apuestas_suscripcion_delete_'.$suscripcion->getId(), 'suscripcion[_crsf_token]', true, false);
            $button_text_edit = __('<strong>Editar</strong>', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug());
            $button_text_delete = __('Eliminar', Pronosticos_Apuestas_TAP::get_instance()->get_plugin_slug());

            $data[] = array(
                    'id' => $suscripcion->getId(),
                    'numero' => $suscripcion->getNumero(),
                    'usuario' => $suscripcion->getUsuario()->display_name,
                    'tipster' => $tipster_name,
                    'periodo' => sprintf(_n('%s MES', '%s MESES', $periodo, 'epic'), $periodo),
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'forma_de_pago' => $suscripcion->getFormaDePago(),
                    'estado' => $suscripcion->getEstado(),
                    'accion' => '<form action="'.$action_url.'" method="post">
                    <input type="hidden" name="op" value="edit">
                    <input type="hidden" name="suscripcion[id]" value="'.$suscripcion->getId().'">
                    '.$nonce_field_edit.'
                    <button type="submit" class="button action">'.$button_text_edit.'</button>
                </form><form action="'.$action_url.'" method="post">
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="suscripcion[id]" value="'.$suscripcion->getId().'">
                    '.$nonce_field_delete.'
                    <button type="submit" class="button action">'.$button_text_delete.'</button>
                </form>'
            );
        }

        $response = array(
                'recordsTotal' => SuscriptionManager::get_instance()->get_total_elements(),
                'recordsFiltered' => SuscriptionManager::get_instance()->get_total_filtred($search['value'], $filter),
                'data' => $data,
                'draw' => isset($_GET['draw']) ?  $_GET['draw'] : 1
        );

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
