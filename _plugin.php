<?php

/*
 * Plugin Name: Wordpress Yandex Maps
 * Plugin URI: https://github.com/nikolays93/yandex-maps/
 * Description: <a href="https://tech.yandex.ru/maps/doc/jsapi/2.1/terms/index-docpage/">Условия использования</a> Яндекс карт
 * Version: 0.3
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 */

namespace NikolayS93\YandexMaps;

use NikolayS93\WPAdminPage as Admin;

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');
if (version_compare(PHP_VERSION, '5.4') < 0) {
    throw new \Exception('Plugin requires PHP 5.4 or above');
}

if( !defined(__NAMESPACE__ . '\PLUGIN_DIR') ) define(__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
if( !defined(__NAMESPACE__ . '\PLUGIN_FILE') ) define(__NAMESPACE__ . '\PLUGIN_FILE', __FILE__);

require_once ABSPATH . "wp-admin/includes/plugin.php";
require_once PLUGIN_DIR . '/vendor/autoload.php';

/**
 * Uniq prefix
 */
if(!defined(__NAMESPACE__ . '\DOMAIN')) define(__NAMESPACE__ . '\DOMAIN', Plugin::get_plugin_data('TextDomain'));

add_action( 'plugins_loaded', __NAMESPACE__ . '\__init', 10 );
function __init() {

    /** @var Admin\Page */
    // $Page = new Admin\Page( Plugin::get_option_name(), __('New Plugin name Title', DOMAIN), array(
    //     'parent'      => '', // woocommerce
    //     'menu' => __('Example', DOMAIN),
    //     // 'validate'    => array($this, 'validate_options'),
    //     'permissions' => 'manage_options',
    //     'columns'     => 2,
    // ) );

    // // $Page->set_assets( function() {} );

    // $Page->set_content( function() {
    //     Plugin::get_admin_template('menu-page', false, $inc = true);
    // } );

    // $Page->add_section( new Admin\Section(
    //     'Section',
    //     __('Section'),
    //     function() {
    //         Plugin::get_admin_template('section', false, $inc = true);
    //     }
    // ) );

    // $metabox = new Admin\Metabox(
    //     'metabox',
    //     __('metabox', DOMAIN),
    //     function() {
    //         Plugin::get_admin_template('metabox', false, $inc = true);
    //     },
    //     $position = 'side',
    //     $priority = 'high'
    // );

    // $Page->add_metabox( $metabox );
}

add_action( 'wp_footer', array(__NAMESPACE__ . '\Plugin', 'enqueue_scripts') );

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );

add_action('wp_enqueue_scripts', array(__CLASS__, 'register_scripts'));
add_action('admin_enqueue_scripts', array(__CLASS__, 'register_scripts'));

add_shortcode(Plugin::get_shortcode_name(), __NAMESPACE__ . '\register_shortcode_yamaps');
function register_shortcode_yamaps( $atts = array(), $content = '' ) {
    $atts = shortcode_atts(
        array_merge(array('id' => ''), YandexMap::get_defaults()),
        $atts,
        Plugin::get_shortcode_name()
    );

    $Yamap = new YandexMap($atts['id'], $atts);

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

add_shortcode('bullet', __NAMESPACE__ . '\register_shortcode_bullet');
function register_shortcode_bullet( $atts = array(), $content = '' ) {
    $Bullet = new YandexBullet( $atts );

    $Yamap = Plugin::get_current_map();
    $Yamap->addBullet($Bullet);
}


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
    wp_enqueue_script( 'yamaps', Plugin::get_plugin_url( '/admin/assets/yandex-maps-admin.js' ),
        array( 'shortcode', 'wp-util', 'jquery', Map::APINAME ), false, true );
    wp_enqueue_style( 'yamaps-style', Plugin::get_plugin_url('/admin/assets/yandex-maps-admin.css'));

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

function register_scripts()
{
    if( !apply_filters('register_yandex_maps_script', __return_true()) )
        return false;

    $api = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
    $public = self::get_plugin_url('/yandex-maps-public.js');

    wp_register_script( Map::APINAME, $api, array(), '', true );
    wp_register_script( Map::PUBLICNAME, $public, array('jquery'), '', true );
}