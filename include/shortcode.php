<?php

namespace NikolayS93\YandexMaps;

/**
 * Shortcodes
 */
function register_shortcode_yamaps( $atts = array(), $content = '' ) {
    $atts = shortcode_atts(
        array_merge(array('id' => ''), Map::_def()),
        $atts,
        Plugin::get_shortcode_name()
    );

    if( !$atts['center'] || false === strpos($atts['center'], ':') )
        return false;

    if( !$atts['id'] ) $atts['id'] = 'singleton';

    $ymaps = Map::get_instance();
    $ymaps->create_map($atts['id'], $atts);

    $container = sprintf('<div id="%s" style="width: %s;height: %s;">%s</div>',
        esc_attr( $atts['id'] ),
        // $atts['center'],
        // $atts['zoom'],
        esc_attr( $atts['width'] ),
        esc_attr( $atts['height'] ),
        do_shortcode( $content, $ignore_html = true )
    );

    return apply_filters( 'yamaps_shortcode_container', $container );
}


function register_shortcode_bullet( $atts = array(), $content = '' ) {
    $atts = shortcode_atts( array(
        'coords' => '',
        'title'  => '',
        'body' => '',
        'footer' => '',
        'color' => '',
        'opened' => '',
    ), $atts, 'bullet' );

    if( !$atts['coords'] || false === strpos($atts['coords'], ':') )
        return false;

    $ymaps = Map::get_instance();
    $ymaps->add_bullet($atts);
}
