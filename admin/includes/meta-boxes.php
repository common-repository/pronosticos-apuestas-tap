<?php

namespace PronosticosApuestasTAP\Backend\Common;

use PronosticosApuestasTAP\Frontend\Pronosticos_Apuestas_TAP;

/**
 * Include and setup custom metaboxes and fields.
 *
 * @category Tipster_TAP
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */
class Meta_Boxes_Post_Type {
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
        add_filter( 'cmb_meta_boxes', array( $this, 'post_type_tipster_metabox' ), 101 );
        add_action( 'init', array( $this, 'cmb_initialize_cmb_meta_boxes' ), 9999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
     * Define the metabox and field configurations for post-type tipster.
     *
     * @param  array $meta_boxes
     * @return array
     */
    function post_type_tipster_metabox( array $meta_boxes ) {
        global $post;

        $periodos = array(
            '1' => __( '1 mes', 'epic' ),
            '2' => __( '2 meses', 'epic' ),
            '3' => __( '3 meses', 'epic' ),
            '6' => __( '6 meses', 'epic' ),
        );
        // Start with an underscore to hide fields from custom fields list
        $prefix = '_tipster_';

        $meta_boxes['tipster_suscripcion'] = array(
            'id'         => 'tipster_suscripcion',
            'title'      => __( 'Suscripcion', 'epic' ),
            'pages'      => array( 'tipster' ), // Tells CMB to use user_meta vs post_meta
            'show_names' => true,
            'cmb_styles' => true, // Show cmb bundled styles.. not needed on user profile page
            'fields'     => array(
                array(
                    'name' => __( 'Precios y Periodos', 'epic' ),
                    'id'         => $prefix . 'suscripcion',
                    'type'        => 'group',
                    'options'     => array(
                        'group_title'   => __( 'Periodo {#}', 'epic' ), // {#} gets replaced by row number
                        'add_button'    => __( 'Agregar', 'epic' ),
                        'remove_button' => __( 'Eliminar', 'epic' ),
                        'sortable'      => true, // beta
                    ),
                    'fields'      => array(
                        array(
                            'name'    => __( 'Periodo', 'epic' ),
                            'desc'    => __( 'Seleccione una opcion', 'epic' ),
                            'id'      => 'periodo',
                            'type'    => 'select',
                            'options' => $periodos
                        ),
                        array(
                            'name' => __( 'Precio', 'epic' ),
                            'desc' => __( 'Escriba el precio correspondiente al periodo', 'epic' ),
                            'id'   => 'precio',
                            'type' => 'text_small'
                        ),
                    )
                ),
            )
        );

        return $meta_boxes;
    }

    /**
     * Initialize the metabox class.
     */
    function cmb_initialize_cmb_meta_boxes() {

        if ( ! class_exists( 'cmb_Meta_Box' ) )
            require_once dirname(__FILE__). '/cmb/init.php';

    }

    function enqueue_scripts(){
        $screen = get_current_screen();
        switch($screen->id){
            case "tipster":
                wp_enqueue_script( 'datepicker-es', plugins_url( 'assets/js/datepicker.es.js', dirname(__FILE__) ), array( 'jquery' ), Pronosticos_Apuestas_TAP::VERSION );
                break;
            default:
                break;
        }
    }
}








