<?php

namespace NikolayS93\YandexMaps;

class Map
{
    /**
     * Variables for wp_register_script, wp_enqueue_script, wp_localize_script
     */
    const APINAME = 'yandex-maps-api';
    const PUBLICNAME = 'yandex-maps-public';

    /**
     * Singleton
     */
    private static $instance = null;
    private function __construct() {}
    private function __clone() {}

    /**
     * Current Map ID
     */
    private $lastmap_id;

    /**
     * Maps data array
     */
    private $ymaps = array();

    /**
     * Enqueue & Exhange props to scripts
     */
    function enqueue_scripts()
    {
        wp_enqueue_script( self::APINAME );
        wp_enqueue_script( self::PUBLICNAME );

        wp_localize_script( self::PUBLICNAME, "yandex_maps",
            apply_filters( 'yandex_maps', $this->ymaps ) );
    }

    /**
     * Default properties for a simple create yandex map
     */
    static function _def()
    {
        $defaults = array(
            'center' => array('56.852593', '53.204843'), // Ижевск
            'zoom'   => 10,
            'height' => '400px',
            'controls' => array('zoomControl', 'searchControl'),
            'bullets' => array(),
        );

        return apply_filters( 'yandex_maps_defaults', $defaults );
    }

    public static function get_instance()
    {
        if( ! self::$instance ) {
            self::$instance = new self();

            /**
             * Only one register action
             */
            add_action( 'wp_footer', array(self::$instance, 'enqueue_scripts') );
        }

        return self::$instance;
    }

    public function create_map( $map_id, $atts = array() )
    {
        $this->lastmap_id = esc_attr( $map_id );
        $this->ymaps[ $this->lastmap_id ] = wp_parse_args($atts, self::_def());
    }

    public function add_bullet( $atts, $map_id = false )
    {
        if( !$map_id = esc_attr( $map_id ) ) $map_id = $this->lastmap_id;
        if( is_string($atts['coords']) ) $atts['coords'] = explode(':', $atts['coords']);

        $this->ymaps[ $map_id ]['bullets'][] = array(
            'coords' => (array) $atts['coords'],
            'title'  => $atts['title'],
            'body'   => $atts['body'],
            'footer' => $atts['footer'],
            'opened' => $atts['opened'],
            'color'  => esc_attr($atts['color']),
        );
    }
}
