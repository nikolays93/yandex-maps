<?php

namespace CDevelopers\Yandex\Map;

class Constructor
{
    private $map_id = '';
    private $ymaps = array();
    private static $instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function get_instance()
    {
      if( ! self::$instance )
          self::$instance = new self();

      add_action( 'wp_footer', array(self::$instance, 'footer_localize_scripts') );

      return self::$instance;
    }

    static function get_defaults()
    {
      $defaults = array(
        'center' => array('56.852593', '53.204843'), // Ижевск
        'zoom'   => 10,
        'controls' => array('zoomControl', 'searchControl'),
        );

      return apply_filters( 'MapConstructor_defaults', $defaults );
    }

    function create_map( $map_id, Array $atts ) {
      $this->map_id = $map_id;
      $this->ymaps[ $this->map_id ] = $atts;
    }

    function add_bullet( Array $coords, $title = '', $map_id = false ) {
      if( !$map_id ) $map_id = $this->map_id;

      $this->ymaps[ $map_id ]['bullets'][] = array(
        'coords' => $coords,
        'title'  => $title,
        );
    }

    static function _register_ymaps_scripts() {
      wp_register_script( 'api-maps-yandex', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), '', true );
      wp_register_script( 'ya-maps-public', Utils::get_plugin_url('assets/ya-maps-public.js'), array('jquery'), '', true );
    }

    static function _enqueue_ymaps_scripts() {
      wp_enqueue_script( 'api-maps-yandex' );
      wp_enqueue_script( 'ya-maps-public' );
    }

    function footer_localize_scripts() {
      wp_localize_script('ya-maps-public', "yamaps_props", apply_filters( 'yamaps_props', $this->ymaps ) );
    }
}