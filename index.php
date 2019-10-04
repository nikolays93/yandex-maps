<?php

/*
 * Plugin Name: Wordpress Yandex Maps
 * Plugin URI: https://github.com/nikolays93/yandex-maps/
 * Description: <a href="https://tech.yandex.ru/maps/doc/jsapi/2.1/terms/index-docpage/">Условия использования</a> Яндекс карт
 * Version: 1.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 */

namespace NikolayS93\YandexMaps;

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'You shall not pass' );
}

if ( ! defined( __NAMESPACE__ . '\PLUGIN_DIR' ) ) {
    define( __NAMESPACE__ . '\PLUGIN_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
}

require_once ABSPATH . "wp-admin/includes/plugin.php";
if ( ! @include_once PLUGIN_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) {
    include PLUGIN_DIR . 'include/class/Creational/Singleton.php';
    include PLUGIN_DIR . 'include/class/ORM/MapsCollection.php';
    include PLUGIN_DIR . 'include/class/Plugin.php';
    include PLUGIN_DIR . 'include/class/Register.php';
}

include PLUGIN_DIR . '/include/shortcode.php';

// include PLUGIN_DIR . '/admin/gutenberg.php';
include PLUGIN_DIR . '/admin/mce.php';

/**
 * Returns the single instance of this plugin, creating one if needed.
 *
 * @return Plugin
 */
function Plugin() {
    return Plugin::get_instance();
}

/**
 * Initialize this plugin once all other plugins have finished loading.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\Plugin', 10 );
add_action( 'plugins_loaded', function () {

    $Register = new Register();
    // $Register->register_plugin_page();

    add_action('wp_enqueue_scripts', array($Register, 'register_front_scripts') );
    add_action('admin_enqueue_scripts', array($Register, 'register_front_scripts') );

    add_shortcode( 'yamap', __NAMESPACE__ . '\register_yamaps_shortcode' );

    add_action( 'wp_footer', array(Plugin()->getCollection(), 'enqueue_scripts') );

}, 20 );

register_activation_hook( __FILE__,
    array( __NAMESPACE__ . '\Register', 'activate' ) );
register_deactivation_hook( __FILE__,
    array( __NAMESPACE__ . '\Register', 'deactivate' ) );
register_uninstall_hook( __FILE__,
    array( __NAMESPACE__ . '\Register', 'uninstall' ) );
