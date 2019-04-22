<?php

namespace NikolayS93\YandexMaps;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class Coords
{
    public $coords = array();

    function __construct( $coords )
    {
        if( is_string($coords) && false !== strpos($coords, ':') ) {
            @list($this->coords[0], $this->coords[1]) = explode(':', $coords);
        }
        elseif ( is_array($coords) ) {
            @list($this->coords[0], $this->coords[1]) = $coords;
        }
    }

    function getCoords()
    {
        if( !sizeof($this->coords) ) {
            $this->coords = false;
        }

        return $this->coords;
    }
}
