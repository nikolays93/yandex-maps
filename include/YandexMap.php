<?php

namespace NikolayS93\YandexMaps;

use NikolayS93\YandexMaps\Creational\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class YandexMap
{
    private $id;

    public $cetner = array();
    public $zoom;
    public $width;
    public $height;
    public $controls = array();
    public $bullets = array();

    /**
     * Default properties for a simple create yandex map
     */
    static function get_defaults()
    {
        $defaults = array(
            'center'   => array('56.852593', '53.204843'), // Ижевск
            'zoom'     => 10,
            'width'    => '100%',
            'height'   => '400px',
            'controls' => array('zoomControl', 'searchControl'),
        );

        return apply_filters( 'yandex_maps_defaults', $defaults );
    }

    function __construct( $map_id = null, $atts )
    {
        if( !$map_id ) $map_id = 'singleton';
        $this->id = (string) esc_attr($map_id);

        $defaults = static::get_defaults();
        $atts = wp_parse_args($atts, $defaults);

        $Coords = new Coords($atts['center']);
        $this->cetner = $Coords->getCoords();

        $this->zoom = (int) $atts['zoom'];

        /**
         * @todo add sanitize lenght
         */
        $this->width = $atts['width'];
        $this->height = $atts['height'];

        /**
         * @todo add sanitize controls
         */
        $this->controls = $atts['controls'];
    }

    function getId()
    {
        return $this->id;
    }

    function getCenter()
    {
        return $this->center;
    }

    function addBullet( NikolayS93\YandexMaps\YandexBullet $Bullet )
    {
        if( $Bullet->getCoords() ) {
            $this->bullets[] = $Bullet;
        }
    }
}
