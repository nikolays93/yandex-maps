<?php

namespace CDevelopers\Yandex\Map;

function init_mce_plugin()
{
    /** MCE Editor */
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
        return;
    }

    add_action('admin_head', array( __CLASS__, 'add_mce_script' ));
}

function add_mce_script()
{
    if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
        return;
    }

    wp_enqueue_script( 'cd_ya_maps', plugins_url( 'assets/ya-maps-admin.js', __FILE__ ),
        array( 'shortcode', 'wp-util', 'jquery' ), false, true );
}

add_action( 'media_buttons', __NAMESPACE__ . '\add_yandex_map', 12 );
function add_yandex_map()
{
    printf('<a href="javascript:;" class="button button_yandex-map button_add-yandex-map">%s</a>',
        __( "Insert Yandex map", DOMAIN ) );
    insert_yandex_map_modal_template();
}

class yaMapsBulletsChanger
{
    private $handle;
    private $args;

    function __construct($handle)
    {
        $this->handle = sanitize_text_field( $handle );
        $this->args = $this->get_defaults();
    }

    private function get_defaults()
    {
        $defaults = array(
            'height' => '400px',
            'selector' => '',
            'inputSelector' => "[data-id=\"{$this->handle}\"]",
            'value' => '',
            'defaultProps' => (object) apply_filters( 'yaMapsBulletsChanger_default_props', array(
            'center' => array('56.852593', '53.204843'), // Ижевск
            'zoom'   => 10,
            'controls' => array('zoomControl', 'searchControl'),
            ) ),
        );

        return apply_filters( 'yaMapsBulletsChanger_defaults', $defaults );
    }

    public function set_args(Array $args)
    {

        $this->args = wp_parse_args( $args, $this->args );
    }

    public function get_container( $addInput = false )
    {
        $c = sprintf('<div id="%s" class="yandex-map-container" style="max-height: %s;height: %s;"></div>',
            $this->handle,
            '100%',
            $this->args['height']);

        if( true === $addInput ) {
            $addInput = sprintf('<input type="text" data-id="%s">', $this->handle);
        }

        $c .= "\r\n {$addInput}";

        return apply_filters( 'yaMapsBulletsChanger_container', $c, $addInput );
    }

    /**
     * @todo split js | php
     */
    public function the_script()
    {
        $args = $this->args;
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready( function() {
                // var arrYMaps = arrYMaps || [];
                // var BulletsChanger = {
                //     handle: 'string',
                //     map: {},
                //     init: function( handle ) {
                //         if( ! handle ) return false;
                //         this.handle = handle;

                //         if( this._isMapExists() ) return false;

                //         this.map = arrYMaps[ this.handle ];
                //     },
                //     _isMapExists: function() {
                //         return (arrYMaps[ this.handle ]) ? true : false;
                //     },
                //     createNewMap: function() {
                //     }
                // }
                var arrYMaps = arrYMaps || [];
                $('<?php echo $args['selector']; ?>').on('click', function(event) {
                    ymaps.ready(function() {
                        var handle = '<?php echo $this->handle; ?>';
                        // not restart initialized map (not worked with wp.Backbone.View)
                        // if( arrYMaps[ handle ] ) return;

                        var valueExists = false,
                            values = [],
                            value = '<?php echo $args['value']; ?>',
                            props = <?php echo json_encode($args[ 'defaultProps' ]); ?>,
                            input = '<?php echo $args['inputSelector']; ?>';

                        if( value ) {
                            values = value.split('|');
                            props.center = values[0].split(',');
                            props.zoom = values[1];
                            valueExists = new ymaps.Placemark(props.center);
                        }

                        console.log( 'initialize map: ' + handle );
                        arrYMaps[ handle ] = new ymaps.Map(handle, props);
                        if( valueExists ) arrYMaps[ handle ].geoObjects.add( valueExists );

                        if( ! $( input ).length ) {
                            console.log('map ' + handle + ' results input('+input+') not found.');
                            return;
                        }

                        // create new ballon on click
                        arrYMaps[ handle ].events.add('click', function (e) {
                            arrYMaps[ handle ].geoObjects.removeAll();
                            var placemark = new ymaps.Placemark( e.get('coords') );
                            arrYMaps[ handle ].geoObjects.add( placemark );
                            placemark.balloon.open();
                            placemark.balloon.close();
                        });

                        // change coords with update zoom
                        arrYMaps[ handle ].events.add('balloonopen', function(e) {
                            var coords = e.get( 'target' ).geometry.getCoordinates();
                            console.log(coords + '|' + arrYMaps[ handle ].getZoom());
                            $( input )
                                .val( coords + '|' + arrYMaps[ handle ].getZoom() )
                                .trigger('change');
                        });

                        // change zoom
                        arrYMaps[ handle ].events.add('boundschange', function(e) {
                            var newZoom = e.get('newZoom'),
                                oldZoom = e.get('oldZoom');
                            if (newZoom != oldZoom) {
                                var coords = $( input ).val().split('|')[0];
                                if( coords ) {
                                    $( input )
                                        .val(coords + '|' + arrYMaps[ handle ].getZoom() )
                                        .trigger('change');
                                }
                            }
                        });
                    });
                });
            });
        }(jQuery));
        </script>
        <?php
    }
}

function insert_yandex_map_modal_template() {
    $ya_map = new yaMapsBulletsChanger( 'yandex-map-bullets-changer' );
    $ya_map->set_args(array(
        'selector' => '.button_add-yandex-map',
    ));
    ?>
    <script type="text/template" id="tmpl-yandex-map-modal-content">
        <div class="content" style="padding: 0 15px;">
            <div class="media-frame-title" style="left: 0">
                <h1><?php _e('Insert Yandex Map', DOMAIN); ?></h1>
            </div>
            <div class="media-frame-content" style="left: 0;top: 50px;">
                <?php echo $ya_map->get_container( true ); ?>
            </div>
            <div class="media-frame-toolbar" style="left: 0">
                <div class="media-toolbar">
                    <div class="media-toolbar-primary search-form">
                        <button type="button" class="button media-button button-primary button-large button-insert-yandex-map">Вставить в страницу</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <?php
    $ya_map->the_script();
}

add_action( 'customize_register', 'customize_register_custom_control', 7 );
function customize_register_custom_control()
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
