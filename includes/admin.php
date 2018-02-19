<?php

namespace CDevelopers\Yandex\Map;

add_action('admin_head', __NAMESPACE__ . '\add_mce_script');
function add_mce_script()
{
    if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
        return;
    }

    wp_enqueue_script( 'yamaps', Utils::get_plugin_url( 'assets/ya-maps-admin.js' ),
        array( 'shortcode', 'wp-util', 'jquery' ), false, true );

    wp_enqueue_script( 'api-maps-yandex' );
    wp_localize_script('api-maps-yandex', 'YandexMap', array('defaults' => Constructor::get_defaults()) );
}

add_action( 'media_buttons', __NAMESPACE__ . '\add_yandex_map', 12 );
function add_yandex_map()
{
    printf('<a href="#" class="button button-yandex-map" id="insert-yandex-map">%s</a>',
        __( "Добавить Яндекс карту", DOMAIN ) );

    Utils::load_file_if_exists(
        Utils::get_plugin_dir() . '/templates/tmpl-yandex-map-modal-content.html' );
}
