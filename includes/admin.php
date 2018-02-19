<?php

namespace CDevelopers\Yandex\Map;

add_action('admin_head', __NAMESPACE__ . '\add_mce_script');
function add_mce_script()
{
    if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
        return;
    }

    wp_enqueue_script( 'cd_ya_maps', Utils::get_plugin_url( 'assets/ya-maps-admin.js' ),
        array( 'shortcode', 'wp-util', 'jquery' ), false, true );
    wp_enqueue_script( 'api-maps-yandex' );
    wp_localize_script('api-maps-yandex', 'YandexMap', array('defaults' => Constructor::get_defaults()) );
}

add_action( 'media_buttons', __NAMESPACE__ . '\add_yandex_map', 12 );
function add_yandex_map()
{
    printf('<a href="#" class="button button-yandex-map" id="insert-yandex-map">%s</a>',
        __( "Добавить Яндекс карту", DOMAIN ) );

    Utils::load_file_if_exists(
        Utils::get_plugin_dir() . '/templates/tmpl-yandex-map-modal-content.html' );
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
