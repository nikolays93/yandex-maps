<?php

add_action( 'customize_register', 'customize_register_custom_control', 7 );
function customize_register_custom_control()
{
    Utils::load_file_if_exists(
        Utils::get_plugin_dir() . '/addons/customize-yandex-maps-control.php' );

    if (class_exists('\CDevelopers\Contacts\CustomControl')) {
        new \CDevelopers\Contacts\CustomControl('company_map', array(
            'label' => __('Your company map', DOMAIN),
            'priority' => 35,
            ),
        __NAMESPACE__ . '\WP_Customize_Yandex_Maps_Control' );
    }
}
