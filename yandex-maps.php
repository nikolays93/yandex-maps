<?php

/*
Plugin Name: Wordpress Yandex Maps
Plugin URI: https://github.com/nikolays93
Description:
Version: 0.0.1
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * @todo : change ajax name
 * @todo : add nonce
 */
namespace CDevelopers\Yandex\Map;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

const DOMAIN = '_plugin';

class Utils
{
    const OPTION = 'yamaps';

    private static $initialized;
    private static $settings;
    private function __construct() {}
    private function __clone() {}

    static function activate() { add_option( self::OPTION, array() ); }
    static function uninstall() { delete_option(self::OPTION); }

    static function get_shortcode_name()
    {

        return apply_filters( 'ya_maps_shortcodename', self::OPTION );
    }

    static function customize_register_custom_control()
    {
        self::load_file_if_exists(
            self::get_plugin_dir() . '/addons/customize-yandex-maps-control.php' );

        if (class_exists('\CDevelopers\Contacts\CustomControl')) {
            new \CDevelopers\Contacts\CustomControl('company_map', array(
                'label' => __('Your company map', DOMAIN),
                'priority' => 35,
            ),
            __NAMESPACE__ . '\WP_Customize_Yandex_Maps_Control' );
        }
    }

    static function register_public_scripts()
    {
        wp_register_script( 'api-maps-yandex', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), '', true );
    }

    private static function include_required_classes()
    {
        $dir_include = self::get_plugin_dir('includes');
        $dir_class = self::get_plugin_dir('classes');

        $classes = array(
            // __NAMESPACE__ . '\Example_List_Table' => $dir_include . '/wp-list-table.php',
            __NAMESPACE__ . '\WP_Admin_Page'      => $dir_class . '/wp-admin-page.php',
            __NAMESPACE__ . '\WP_Admin_Forms'     => $dir_class . '/wp-admin-forms.php',
            // __NAMESPACE__ . '\WP_Post_Boxes'      => $dir_class . '/wp-post-boxes.php',
            __NAMESPACE__ . '\yaMaps'             => $dir_class . '/ya-maps.php',
        );

        foreach ($classes as $classname => $dir) {
            if( ! class_exists($classname) ) {
                self::load_file_if_exists( $dir );
            }
        }

        // includes
        // self::load_file_if_exists( $dir_include . '/register-post-type.php' );
        self::load_file_if_exists( $dir_include . '/admin-page.php' );
        self::load_file_if_exists( $dir_include . '/shortcode.php' );
    }

    public static function initialize()
    {
        if( self::$initialized ) {
            return false;
        }

        load_plugin_textdomain( DOMAIN, false, DOMAIN . '/languages/' );
        self::include_required_classes();
        add_action( 'admin_init', array( __CLASS__, 'init_mce_plugin' ), 20 );
        add_action( 'wp_enqueue_scripts', array(__CLASS__, 'register_public_scripts') );
        add_action( 'customize_register', array(__CLASS__, 'customize_register_custom_control'), 7 );

        self::$initialized = true;
    }

    /**
     * Записываем ошибку
     */
    public static function write_debug( $msg, $dir )
    {
        if( ! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG )
            return;

        $dir = str_replace(__DIR__, '', $dir);
        $msg = str_replace(__DIR__, '', $msg);

        $date = new \DateTime();
        $date_str = $date->format(\DateTime::W3C);

        if( $handle = @fopen(__DIR__ . "/debug.log", "a+") ) {
            fwrite($handle, "[{$date_str}] {$msg} ({$dir})\r\n");
            fclose($handle);
        }
        elseif (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            echo sprintf( __('Can not have access the file %s (%s)', DOMAIN),
                __DIR__ . "/debug.log",
                $dir );
        }
    }

    /**
     * Загружаем файл если существует
     */
    public static function load_file_if_exists( $file_array, $args = array(), $once = false, $reqire = false )
    {
        $cant_be_loaded = __('The file %s can not be included', DOMAIN);
        if( is_array( $file_array ) ) {
            $result = array();
            foreach ( $file_array as $id => $path ) {
                if ( ! is_readable( $path ) ) {
                    self::write_debug(sprintf($cant_be_loaded, $path), __FILE__);
                    continue;
                }

                if( $reqire )
                    $result[] = ( $once ) ? require_once( $path ) : require( $path );
                else
                    $result[] = ( $once ) ? include_once( $path ) : include( $path );
            }
        }
        else {
            if ( ! is_readable( $file_array ) ) {
                self::write_debug(sprintf($cant_be_loaded, $file_array), __FILE__);
                return false;
            }

            if( $reqire )
                $result = ( $once ) ? require_once( $file_array ) : require( $file_array );
            else
                $result = ( $once ) ? include_once( $file_array ) : include( $file_array );
        }

        return $result;
    }

    public static function get_plugin_dir( $path = false )
    {
        $result = __DIR__;

        switch ( $path ) {
            case 'classes': $result .= '/includes/classes'; break;
            case 'settings': $result .= '/includes/settings'; break;
            default: $result .= '/' . $path;
        }

        return $result;
    }

    public static function get_plugin_url( $path = false )
    {
        $result = plugins_url(basename(__DIR__) );

        switch ( $path ) {
            default: $result .= '/' . $path;
        }

        return $result;
    }

    /**
     * Получает настройку из self::$settings или из кэша или из базы данных
     */
    public static function get( $prop_name, $default = false )
    {
        if( ! self::$settings )
            self::$settings = get_option( self::OPTION, array() );

        if( 'all' === $prop_name ) {
            if( is_array(self::$settings) && count(self::$settings) )
                return self::$settings;

            return $default;
        }

        return isset( self::$settings[ $prop_name ] ) ? self::$settings[ $prop_name ] : $default;
    }

    public static function get_settings( $filename, $args = array() )
    {

        return self::load_file_if_exists( self::get_plugin_dir('settings') . '/' . $filename, $args );
    }

    static function init_mce_plugin()
    {
        /** MCE Editor */
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }

        add_action('admin_head', array( __CLASS__, 'add_mce_script' ));

        add_filter("mce_external_plugins", array(__CLASS__, 'mce_plugin'));
        add_filter("mce_buttons", array(__CLASS__, 'mce_button'));
    }

    /** Register Shortcode Button MCE */
    static function mce_plugin($plugin_array)
    {
        $plugin_array['cd_ya_maps'] = plugins_url( 'assets/cd-ya-maps-button.js', __FILE__ );

        return $plugin_array;
    }

    static function mce_button($buttons)
    {
        $buttons[] = 'cd_ya_maps';

        return $buttons;
    }

    static function add_mce_script()
    {
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
            return;
        }

        wp_enqueue_script( 'cd_ya_maps', plugins_url( 'assets/cd-ya-maps.js', __FILE__ ),
            array( 'shortcode', 'wp-util', 'jquery' ), false, true );
    }
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'activate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Utils', 'initialize' ), 10 );

// $title = 'Воткинское шоссе, 12а';
// $coordinates = yaMaps::sanitize_geocode( yaMaps::_geocode( $title ) );
// $coords = implode(' ', $coordinates['coordinates']);
// echo do_shortcode( "" );
