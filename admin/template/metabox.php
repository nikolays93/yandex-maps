<?php

namespace NikolayS93\PluginName;

use NikolayS93\WPAdminForm\Form as Form;

$data = array(
    // id or name - required
    array(
        'id'    => 'example_0',
        'type'  => 'text',
        'label' => 'TextField',
        'desc'  => 'This is example text field',
    ),
    array(
        'id'      => 'example_1',
        'type'    => 'select',
        'label'   => 'Select',
        'options' => array(
            // simples first (not else)
            'key_option5' => 'option5',
            'option1'     => array(
                'key_option2' => 'option2',
                'key_option3' => 'option3',
                'key_option4' => 'option4'
            ),
        ),
    ),
    array(
        'id'    => 'example_2',
        'type'  => 'checkbox',
        'label' => 'Checkbox',
    ),
);

$form = new Form( $data, $is_table = true );
$form->display();

echo "<pre>";
print_r( Plugin()->get_setting( null, array() ) );
echo "</pre>";

submit_button( 'Сохранить', 'primary right', 'save_changes' );
echo '<div class="clear"></div>';