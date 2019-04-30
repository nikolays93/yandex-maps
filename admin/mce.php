<?php

namespace NikolayS93\YandexMaps;

/**
 * MCE Editor
 */
add_action('admin_head', __NAMESPACE__ . '\add_mce_script');
function add_mce_script()
{
    $screen = get_current_screen();
    if ( !isset( $screen->id ) || $screen->base != 'post' ) return;

    /**
     * Enqueue Yandex Map API
     */
    wp_enqueue_script( Plugin::APINAME );

    /**
     * Init Construct Yandex Map Method
     */
    wp_enqueue_script( Plugin::PUBLICNAME );

    /**
     * Enqueue Admin Script
     */
    wp_enqueue_script( 'yamaps', Plugin::get_plugin_url( '/admin/assets/mce.js' ),
        array( 'shortcode', 'wp-util', 'jquery', Plugin::APINAME ), false, true );
    wp_enqueue_style( 'yamaps-style', Plugin::get_plugin_url('/admin/assets/mce.css'));

    /**
     * Exchange admin script properties
     */
    wp_localize_script( 'yamaps', 'yandex_maps', array('EditYandexMapContainer' => YandexMap::get_defaults()) );
}



add_action('media_buttons', __NAMESPACE__ . '\insert_yandex_map', 12);
function insert_yandex_map()
{
    /**
     * Insert Yandex Map button
     */
    printf('<a href="#" class="button button-yandex-map" id="insert-yandex-map">%s</a>',
        __( "Добавить Яндекс карту", DOMAIN ) );

    /**
     * Insert Yandex Map modal construct
     */
    include PLUGIN_DIR . '/admin/template/tmpl-yandex-map-modal-content.html';
}