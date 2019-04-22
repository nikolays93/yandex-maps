<?php

namespace NikolayS93\YandexMaps;

// add_action( 'init', 'my_custom_block_for_gutenberg' );
function my_custom_block_for_gutenberg() {
    // wp_register_script(
    //     'mcbfg_script_editor',
    //     get_template_directory_uri() . '/my_gutenberg/my_first_block/script_editor.js',
    //     array( 'wp-blocks', 'wp-element', 'wp-components' )
    // );
    // wp_register_style(
    //     'mcbfg_style_editor',
    //     get_template_directory_uri() . '/my_gutenberg/my_first_block/style_editor.css',
    //     array( 'wp-edit-blocks' )
    // );
    // wp_register_style(
    //     'mcbfg_style',
    //     get_template_directory_uri() . '/my_gutenberg/my_first_block/style.css',
    //     array( 'wp-blocks' )
    // );

    // register_block_type( 'my-gutenberg/my-first-block', array(
    //     'editor_script' => 'mcbfg_script_editor',
    //     'editor_style' => 'mcbfg_style_editor',
    //     'style' => 'mcbfg_style'
    // ) );
}

add_action('admin_head', __NAMESPACE__ . '\add_gutenberg_script');
function add_gutenberg_script()
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
    wp_enqueue_script( 'yamaps', Plugin::get_plugin_url( '/admin/assets/gutenberg.js' ),
        array( 'wp-blocks', 'wp-element', 'wp-components', 'shortcode', 'wp-util', 'jquery', Plugin::APINAME ), false, true );
    wp_enqueue_style( 'yamaps-style', Plugin::get_plugin_url('/admin/assets/gutenberg.css'));

    /**
     * Exchange admin script properties
     */
    wp_localize_script( 'yamaps', 'yandex_maps', array('EditYandexMapContainer' => YandexMap::get_defaults()) );
}