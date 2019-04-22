<?php

namespace NikolayS93\YandexMaps;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class YandexBullet
{
    public $title;
    public $body;
    public $footer;
    public $coords;

    public $opened;
    public $color;

    function __construct( $atts )
    {
        $this->title = (string) sanitize_text_field( $atts['title'] );
        $this->body = (string) sanitize_text_field( $atts['body'] );
        $this->footer = (string) sanitize_text_field( $atts['footer'] );

        $Coords = new Coords($atts['coords']);
        $this->coords = $Coords->getCoords();

        $this->opened = (bool) $atts['opened'];
        $this->color = 0 !== strpos($atts['color'], '#') ? esc_attr("#" . $atts['color']) : esc_attr($atts['color']);
    }

    function getCoords()
    {
        return $this->coords;
    }
}
