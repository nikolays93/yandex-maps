<?php

namespace CDevelopers\Yandex\Map;

add_shortcode( Utils::get_shortcode_name(), __NAMESPACE__ . '\shortcode_callback_func' );
function shortcode_callback_func( $atts = array(), $content = '' ) {
    $atts = shortcode_atts( array(
        'title' => '',
        'coords' => '',
        'zoom' => 12,
        'height' => 400,
    ), $atts, Utils::get_shortcode_name() );

    if( ! $atts['coords'] || (false === strpos($atts['coords'], ' ')) )
        return '';

    $map = new yaMaps( $atts );
    $map::load_api_maps();

    return sprintf('<div id="ya-maps-%s" style="height: %dpx;"></div>',
        $map->get_id(),
        intval( $atts['height'] )
    );
}