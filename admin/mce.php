<?php

/**
 * MCE Editor
 */
add_action('admin_head', array(__CLASS__, 'add_mce_script'));
function add_mce_script()
{
    $screen = get_current_screen();
    if ( !isset( $screen->id ) || $screen->base != 'post' ) return;

    /**
     * Enqueue Yandex Map API
     */
    wp_enqueue_script( YandexMap::APINAME );

    /**
     * Init Construct Yandex Map Method
     */
    wp_enqueue_script( YandexMap::PUBLICNAME );

    /**
     * Enqueue Admin Script
     */
    wp_enqueue_script( 'yamaps', Plugin::get_plugin_url( '/admin/assets/mce.js' ),
        array( 'shortcode', 'wp-util', 'jquery', Map::APINAME ), false, true );
    wp_enqueue_style( 'yamaps-style', Plugin::get_plugin_url('/admin/assets/mce.css'));

    /**
     * Exchange admin script properties
     */
    wp_localize_script( 'yamaps', 'yandex_maps', array('EditYandexMapContainer' => Map::_def()) );
}

add_action('media_buttons', array(__CLASS__, 'insert_yandex_map'), 12);
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