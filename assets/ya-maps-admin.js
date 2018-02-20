/* global tinyMCE */
(function($) {
    $(document).ready( function() {
        function YandexMapInit(handle, props, val) {
            val = val || [];
            arrYMaps[ handle ] = new ymaps.Map(handle, props);

            if( ! val.bullets ) return;
            $.each(val.bullets, function(index, bullet) {
                console.log(bullet.coords);
                placemarks[ index ] = new ymaps.Placemark(bullet.coords, {
                    balloonContent: bullet.title
                });

                arrYMaps[ handle ].geoObjects.add( placemarks[ index ] );
            });
        }

        function OpenYandexMapWindow(handle, props, val) {
            val = val || [];
            ymaps.ready(function() {
                Modal.content( new ModalContent() );
                Modal.open();

                YandexMapInit(handle, props, val);

                // create new ballon on click
                arrYMaps[ handle ].events.add('click', function (e) {
                    // arrYMaps[ handle ].geoObjects.removeAll();
                    var placemark = new ymaps.Placemark( e.get('coords') );
                    arrYMaps[ handle ].geoObjects.add( placemark );
                    // placemark.balloon.open();
                    // placemark.balloon.close();
                });

                // change zoom/coordinates
                arrYMaps[ handle ].events.add('boundschange', function(e) {
                    document.getElementsByName('center')[0].value = arrYMaps[ handle ].getCenter().join(':');
                    document.getElementsByName('zoom')[0].value = e.get('newZoom');
                });

                var center = props.center || YandexMap.defaults.center;
                document.getElementsByName('center')[0].value = center.join(':');
                document.getElementsByName('zoom')[0].value   = props.zoom || YandexMap.defaults.zoom;
                document.getElementsByName('height')[0].value = props.height || YandexMap.defaults.height;

                $('.button-insert-yandex-map').on('click', function(event) {
                    wp.mce.yamaps.submit( tinyMCE.activeEditor, {
                        center: document.getElementsByName('center')[0].value,
                        zoom: document.getElementsByName('zoom')[0].value,
                        height: document.getElementsByName('height')[0].value
                    } );
                    Modal.close();
                });
            });
        }

        var Modal = new wp.media.view.Modal({
            controller: { trigger: function() {} }
        });

        var ModalContent = wp.Backbone.View.extend({
            template: wp.template( 'yandex-map-modal-content' )
        });

        var arrYMaps = [];
        var placemarks = [];

        var shortcode_string = 'yamaps';

        wp.mce = wp.mce || {};
        wp.mce.yamaps = {
            findSubShortcodes: function(shortcode, content, subShortcodes) {
                subShortcodes = subShortcodes || [];
                var subShortcode = wp.shortcode.next(shortcode, content);

                if( subShortcode && subShortcode.content ) {
                    var before = content;
                    content = content.replace(subShortcode.content, '');
                    subShortcodes.push( subShortcode );

                    if( before != content )
                        return this.findSubShortcodes( shortcode, content, subShortcodes );
                }

                return subShortcodes;
            },
            getContent: function() {
                // Контент внутри объекта
                return '<p style="text-align: center;">[Yandex Карта]</p>';
            },
            edit: function( data ) {
                var shortcode_data = wp.shortcode.next(shortcode_string, data);
                var values = shortcode_data.shortcode.attrs.named;
                var val = {bullets: []};

                // parse bullets
                $.each(this.findSubShortcodes('ya_bullet', shortcode_data.shortcode.content), function(index, el) {
                    val.bullets.push( {
                        coords: el.shortcode.attrs.named.coords.split(':'),
                        title: el.shortcode.attrs.named.title || ''
                    } );
                });

                // init modal
                OpenYandexMapWindow('EditYandexMapContainer', {
                    center: values.center ? values.center.split(':') : YandexMap.defaults.center,
                    zoom: values.zoom || YandexMap.defaults.zoom,
                    height: values.height || YandexMap.defaults.height,
                    controls: YandexMap.defaults.controls
                }, val);
            },
            submit: function(editor, values) {
                values = values || [];

                var content = '';
                var args = {
                    tag : shortcode_string,
                    attrs : {
                        zoom : values.zoom,
                        center : values.center,
                        height: values.height,
                    }
                };

                arrYMaps[ 'EditYandexMapContainer' ].geoObjects.each(function(geoObject) {
                    content += wp.shortcode.string( {
                        tag: 'ya_bullet',
                        type: 'single',
                        attrs : {
                            title: geoObject.properties.get('balloonContent') || '',
                            coords: geoObject.geometry.getCoordinates().join(':'),
                        }
                    } );
                });

                args.content = content;
                editor.insertContent( wp.shortcode.string( args ) );
            }
        };


        // $.each(yamaps_props, YandexMapInit(handle, val) );

        $('#insert-yandex-map').on('click', function(event) {
            event.preventDefault();

            OpenYandexMapWindow('EditYandexMapContainer', YandexMap.defaults, {});
        });

        wp.mce.views.register( shortcode_string, wp.mce.yamaps );

        // change coords with update zoom
        // arrYMaps[ handle ].events.add('balloonopen', function(e) {
        //     var coords = e.get( 'target' ).geometry.getCoordinates();
        //     console.log(coords + '|' + arrYMaps[ handle ].getZoom());
        //     $( input )
        //     .val( coords + '|' + arrYMaps[ handle ].getZoom() )
        //     .trigger('change');
        // });
    });
}(jQuery));
