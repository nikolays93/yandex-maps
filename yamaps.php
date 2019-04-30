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

// include PLUGIN_DIR . '/admin/gutenberg.php';
include PLUGIN_DIR . '/admin/mce.php';

add_action( 'wp_footer', array(__NAMESPACE__ . '\Plugin', 'enqueue_scripts') );

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\register_scripts');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\register_scripts');
function register_scripts()
{
    if( !apply_filters('register_yandex_maps_script', __return_true()) ) {
        return false;
    }

    wp_register_script( Plugin::APINAME, 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), '', true );
    wp_register_script( Plugin::PUBLICNAME, Plugin::get_plugin_url('/include/public.js'), array('jquery'), '', true );
}

/**
 * Shortcodes
 */

add_shortcode('yamap', __NAMESPACE__ . '\register_shortcode_yamaps');
function register_shortcode_yamaps( $atts = array(), $content = '' ) {
    $atts = shortcode_atts(
        array_merge(array('id' => ''), YandexMap::get_defaults()),
        $atts,
        'yamap'
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

