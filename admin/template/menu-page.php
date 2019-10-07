<?php

namespace NikolayS93\YandexMaps;

use NikolayS93\WPAdminForm\Form as Form;

$data = array(
    // id or name - required
    array(
        'id'    => 'YAMAPS_API',
        'type'  => 'text',
        'label' => 'API KEY',
        'desc'  => 'Set yandex maps api key for many requests.',
    ),
);

$form = new Form( $data, $is_table = true );
$form->display();

submit_button( 'Сохранить', 'primary left', 'save_changes' );
echo '<div class="clear"></div>';
