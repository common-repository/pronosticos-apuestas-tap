<?php

namespace PronosticosApuestasTAP\Common;

/**
 * Members post type.
 *
 * @package WordPress
 * @subpackage Theme
 */

class Members_Post_Type{
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
        add_action( 'init', array( $this, 'post_type_members') );

        $pagina_carrito = get_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO');
        $pagina_soporte = get_option('PRONOSTICO_APUESTAS_PAGINA_SOPORTE');
        if(!$pagina_carrito && !$pagina_soporte){
            add_action( 'init', array( $this, 'post_type_members_create' ) );
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
     * Register a members post type
     */
    function post_type_members() {
        $labels = array(
            'name'                => _x( 'Members', 'post type general name', 'epic' ),
            'singular_name'       => _x( 'Members', 'post type singular name', 'epic' ),
        );
        $args = array(
            'label'               => __( 'members', 'epic' ),
            'description'         => __( 'Pagina de members', 'epic' ),
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
        register_post_type( 'usuario', $args );
    }

    /**
     * Create a ads post type
     */
    function post_type_members_create(){
        $post_type_arg = array('post_type' => 'usuario', 'post_status' => 'publish', 'post_title' => 'Carrito');
        $post_type_id = wp_insert_post($post_type_arg);
        add_option('PRONOSTICO_APUESTAS_PAGINA_CARRITO', $post_type_id);

        $post_type_arg = array('post_type' => 'usuario', 'post_status' => 'publish', 'post_title' => 'Soporte');
        $post_type_id = wp_insert_post($post_type_arg);
        add_option('PRONOSTICO_APUESTAS_PAGINA_SOPORTE', $post_type_id);
    }
}