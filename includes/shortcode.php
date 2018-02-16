<?php

namespace CDevelopers\Yandex\Map;

add_shortcode( 'yamaps', __NAMESPACE__ . '\register_shortcode_yamaps' );
function register_shortcode_yamaps( $atts = array(), $content = '' ) {
  $atts = shortcode_atts( array(
    'id'     => '',
    'center' => '56.852593:53.204843',
    'zoom'   => '12',
    'width'  => '100%',
    'height' => '400px',
  ), $atts, 'yamaps' );

  if( !$atts['center'] || false === strpos($atts['center'], ':') )
    return false;

  if( !$atts['id'] ) $atts['id'] = 'singleton';
  $atts['center'] = explode(":", $atts['center']);

  $ymaps = Constructor::get_instance();
  $ymaps->create_map($atts['id'], $atts);

  $container = sprintf('<div id="%s" style="width: %s;height: %s;">%s</div>',
    esc_attr( $atts['id'] ),
    // $atts['center'],
    // $atts['zoom'],
    esc_attr( $atts['width'] ),
    esc_attr( $atts['height'] ),
    do_shortcode( $content, $ignore_html = true )
    );

  Constructor::_enqueue_ymaps_scripts();
  return apply_filters( 'yamaps_shortcode_container', $container );
}

add_shortcode( 'ya_bullet', __NAMESPACE__ . '\register_shortcode_ya_bullet' );
function register_shortcode_ya_bullet( $atts = array(), $content = '' ) {
  $atts = shortcode_atts( array(
    'title'  => '',
    'coords' => '',
  ), $atts, 'ya_bullet' );

  if( !$atts['coords'] || false === strpos($atts['coords'], ':') )
    return false;

  $coords = explode(':', $atts['coords']);
  $ymaps = Constructor::get_instance();
  $ymaps->add_bullet($coords, $atts['title']);
}
