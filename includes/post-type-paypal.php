<?php

namespace PronosticosApuestasTAP\Common;

/**
 * Paypal post type.
 *
 * @package WordPress
 * @subpackage Theme
 */

class Paypal_Post_Type{
    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since     1.0.0
     */
    private function __construct() {
        add_action( 'init', array( $this, 'post_type_paypal') );

        $paypal_pago_aceptado = get_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_ACEPTADO');
        $paypal_pago_cancelado = get_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_CANCELADO');
        if(!$paypal_pago_aceptado && !$paypal_pago_cancelado){
            add_action( 'init', array( $this, 'post_type_paypal_create' ) );
        }
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
         * - Uncomment following lines if the admin class should only be available for super admins
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
     * Register a paypal post type
     */
    function post_type_paypal() {
        $labels = array(
            'name'                => _x( 'Paypal', 'post type general name', 'epic' ),
            'singular_name'       => _x( 'Paypal', 'post type singular name', 'epic' ),
        );
        $args = array(
            'label'               => __( 'paypal', 'epic' ),
            'description'         => __( 'Paginas de paypal', 'epic' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-generic',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'paypal', $args );
    }

    /**
     * Create a ads post type
     */
    function post_type_paypal_create(){
        $post_type_arg = array('post_type' => 'paypal', 'post_status' => 'publish', 'post_title' => 'Pago Aceptado');
        $post_type_id = wp_insert_post($post_type_arg);
        add_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_ACEPTADO', $post_type_id);

        $post_type_arg = array('post_type' => 'paypal', 'post_status' => 'publish', 'post_title' => 'Pago Cancelado');
        $post_type_id = wp_insert_post($post_type_arg);
        add_option('PRONOSTICO_APUESTAS_PAGINA_PAYPAL_PAGO_CANCELADO', $post_type_id);
    }
}