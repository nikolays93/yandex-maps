<?php

/*
 * Plugin Name: Wordpress Yandex Maps
 * Plugin URI: https://github.com/nikolays93
 * Description: <a href="https://tech.yandex.ru/maps/doc/jsapi/2.1/terms/index-docpage/">Условия использования</a> Яндекс карт
 * Version: 0.2.0
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 */

/**
 * Фильтры плагина:
 * "get_{Text Domain}_option_name" - имя опции плагина
 * "get_{Text Domain}_option" - значение опции плагина
 * "get_{Text Domain}_plugin_url" - УРЛ плагина
 */

namespace NikolayS93\YandexMaps;

if ( ! defined( 'ABSPATH' ) )
  exit('You shall not pass'); // disable direct access

require_once ABSPATH . "wp-admin/includes/plugin.php";

if (version_compare(PHP_VERSION, '5.3') < 0) {
    throw new \Exception('Plugin requires PHP 5.3 or above');
}

class Plugin
{
    protected static $data;
    protected static $options;

    private function __construct() {}

    // static function activate() { add_option( self::get_option_name(), array() ); }
    // static function uninstall() { delete_option( self::get_option_name() ); }

    /**
     * Получает название опции плагина
     *     Чаще всего это название плагина
     *     Чаще всего оно используется как название страницы настроек
     * @return string
     */
    public static function get_option_name()
    {
        return apply_filters("get_{DOMAIN}_option_name", DOMAIN);
    }

    public static function get_shortcode_name()
    {
        return apply_filters("get_{DOMAIN}_shortcode_name", 'yamap');
    }

    public static function define()
    {
        self::$data = get_plugin_data(__FILE__);

        if( !defined(__NAMESPACE__ . '\DOMAIN') )
            define(__NAMESPACE__ . '\DOMAIN', self::$data['TextDomain']);

        if( !defined(__NAMESPACE__ . '\PLUGIN_DIR') )
            define(__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
    }

    public static function initialize()
    {
        load_plugin_textdomain(DOMAIN, false, basename(PLUGIN_DIR) . '/languages/');

        require PLUGIN_DIR . '/include/utils.php';
        require_once PLUGIN_DIR . '/include/class/map.php';

        include PLUGIN_DIR . '/include/shortcode.php';

        add_action('wp_enqueue_scripts', array(__CLASS__, 'register_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'register_scripts'));

        /**
         * MCE Editor
         */
        add_action('admin_head', array(__CLASS__, 'add_mce_script'));
        add_action('media_buttons', array(__CLASS__, 'insert_yandex_map'), 12);

        add_shortcode(Plugin::get_shortcode_name(), __NAMESPACE__ . '\register_shortcode_yamaps');
        add_shortcode('bullet', __NAMESPACE__ . '\register_shortcode_bullet');
    }

    static function insert_yandex_map()
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

    static function add_mce_script()
    {
        $screen = get_current_screen();
        if ( !isset( $screen->id ) || $screen->base != 'post' ) return;

        /**
         * Enqueue Yandex Map API
         */
        wp_enqueue_script( Map::APINAME );

        /**
         * Enqueue admin script
         */
        wp_enqueue_script( 'yamaps', Utils::get_plugin_url( '/admin/assets/yandex-maps-admin.js' ),
            array( 'shortcode', 'wp-util', 'jquery', Map::APINAME ), false, true );

        /**
         * Exchange admin script properties
         */
        wp_localize_script( 'yamaps', 'YandexMap', array('defaults' => Map::_def()) );
    }

    static function register_scripts()
    {
        if( !apply_filters('register_yandex_maps_script', __return_true()) )
            return false;

        $api = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
        $public = Utils::get_plugin_url('/yandex-maps-public.js');

        wp_register_script( Map::APINAME, $api, array(), '', true );
        wp_register_script( Map::PUBLICNAME, $public, array('jquery'), '', true );
    }
}

Plugin::define();

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'initialize' ), 10 );
