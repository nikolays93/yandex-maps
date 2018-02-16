<?php

namespace CDevelopers\Yandex\Map;

class WP_Customize_Yandex_Maps_Control extends \WP_Customize_Control
{
    public function __construct($manager, $id, $args = array(), $options = array())
    {
        parent::__construct( $manager, $id, $args );

        add_action( 'customize_controls_enqueue_scripts', array(__NAMESPACE__ . '\Utils', 'enqueue_scripts') );
    }

    public function render_content()
    {
        $selector = str_replace('company_map', 'contact', $this->id);
        ?>
        <label class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
        <div id="<?php echo $this->id;?>" style="height: 180px"></div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var arrYMaps = arrYMaps || [];
                $('#accordion-section-company_contacts, #accordion-section-<?=$selector;?>').on('click', function(event) {
                    ymaps.ready(function() {
                        var id = '<?php echo $this->id;?>';
                        if( !arrYMaps[id] ) {
                            var mark = false,
                                values = [],
                                value = $('[data-id="'+id+'"]').val(),
                                props = {
                                    center: [56.852593, 53.204843],
                                    zoom: 10,
                                    controls: ['zoomControl', 'searchControl']
                                };

                            if( value ) {
                                values = value.split('|');
                                props.center = values[0].split(',');
                                props.zoom = values[1];
                                mark = new ymaps.Placemark(props.center);
                            }

                            arrYMaps[id] = new ymaps.Map("<?php echo $this->id;?>", props);
                            if( mark ) arrYMaps[id].geoObjects.add(mark);


                            // change coords with update zoom
                            arrYMaps[id].events.add('balloonopen', function(e) {
                                var coords = e.get( 'target' ).geometry.getCoordinates();
                                $('[data-id="'+id+'"]').val( coords + '|' + arrYMaps[id].getZoom() ).trigger('change');
                            });

                            // create new ballon on click
                            arrYMaps[id].events.add('click', function (e) {
                                arrYMaps[id].geoObjects.removeAll();
                                var placemark = new ymaps.Placemark(e.get('coords'));
                                arrYMaps[id].geoObjects.add( placemark );
                                placemark.balloon.open();
                            });

                            // change zoom
                            arrYMaps[id].events.add('boundschange', function(e) {
                                var newZoom = e.get('newZoom'), oldZoom = e.get('oldZoom');
                                if (newZoom != oldZoom) {
                                    var val = $('[data-id="'+id+'"]').val();
                                    var coords = val.split('|')[0];
                                    if( coords )
                                        $('[data-id="'+id+'"]').val(coords + '|' + arrYMaps[id].getZoom() )
                                            .trigger('change');
                                }
                            });

                            // var geocoder = ymaps.geocode("Ижевск");
                            // geocoder.then(function (res) {
                            //     if (res.geoObjects.getLength()) {
                            //         var point = res.geoObjects.get(0);
                            //         ya_map.geoObjects.add(point);
                            //         ya_map.panTo(point.geometry.getCoordinates());
                            //     }
                            // });
                        }
                    });
                });
            });
        </script>
        <?php $type = WP_DEBUG ? 'text' : 'hidden'; ?>
        <input type="<?php echo $type;?>" data-id="<?php echo $this->id;?>" value="<?php echo $this->value();?>" <?php $this->link();?>>
        <?php
    }
}
