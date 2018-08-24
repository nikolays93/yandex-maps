/* global tinyMCE */
(function($) {
    $(document).ready( function() {

        var Modal = new wp.media.view.Modal({
            controller: { trigger: function() {} }
        });

        var ModalContent = wp.Backbone.View.extend({
            template: wp.template( 'yandex-map-modal-content' )
        });

        var Sidebar = {
            $handle: null,
            $header: null,
            $body: null,
            $footer: null,
            $color: null,
            $opened: null,

            $delete_button: null,

            __construct: function( selector ) {
                this.$handle = $(selector);

                this.$header = this.$handle.find( '[name="balloonContentHeader"]' );
                this.$body = this.$handle.find( '[name="balloonContentBody"]' );
                this.$footer = this.$handle.find( '[name="balloonContentFooter"]' );
                this.$color = this.$handle.find( '[name="iconColor"]' );
                this.$opened = this.$handle.find( '[name="opened"]' );

                this.$delete_button = this.$handle.find( '.button-sidebar-delete' );
            },

            __clear: function() {
                this.$header.off('keyup');
                this.$body.off('keyup');
                this.$footer.off('keyup');
                this.$color.off('change');
                this.$opened.off('change');

                this.$delete_button.off('click');
            },

            open: function( ymap, placemark ) {
                var self = this;

                this.$handle.fadeIn('fast');

                if( !placemark )
                    return false;

                this.__clear();

                this.$header.val( placemark.properties.get('balloonContentHeader') );
                this.$body.val( placemark.properties.get('balloonContentBody') );
                this.$footer.val( placemark.properties.get('balloonContentFooter') );

                this.$color.val( placemark.options.get('iconColor') );
                console.log( placemark.options.get('opened') );
                this.$opened.prop('checked', 1 == placemark.options.get('opened') ? true : false);

                this.$header.on('keyup', function(event) {
                    placemark.properties.set('balloonContentHeader', $(this).val());
                });

                this.$body.on('keyup', function(event) {
                    placemark.properties.set('balloonContentBody', $(this).val());
                });

                this.$footer.on('keyup', function(event) {
                    placemark.properties.set('balloonContentFooter', $(this).val());
                });


                this.$color.on('change', function(event) {
                    placemark.options.set('iconColor', $(this).val());
                });

                this.$opened.on('change', function(event) {
                    if( $(this).is(':checked') ) {
                        placemark.options.set('opened', 1);
                        // placemark.balloon.open();
                    }
                    else {
                        placemark.options.set('opened', 0);
                        // placemark.balloon.close();
                    }
                });

                if( !ymap )
                    return false;

                this.$delete_button.on('click', function(event) {
                    event.preventDefault();

                    ymap.geoObjects.remove( placemark );
                    self.close();
                });

                this.$delete_button.show();
            },

            close: function() {
                this.$handle.fadeOut('fast');

                this.__clear();
                this.$delete_button.hide();
            }
        }

        function YandexMapInit(handle, props, val) {
            val = val || [];
            arrYMaps[ handle ] = new ymaps.Map(handle, props);

            if( ! val.bullets ) return;
            $.each(val.bullets, function(index, bullet) {
                placemarks[ index ] = new ymaps.Placemark(bullet.coords, {
                    balloonContent: bullet.title
                });

                arrYMaps[ handle ].geoObjects.add( placemarks[ index ] );
            });
        }

        function OpenYandexMapWindow(handle, props, val) {
            val = val || [];

            Modal.content( new ModalContent() );
            Modal.open();

            /**
             * Set after open modal only
             */
            Sidebar.__construct('#EditYandexMapSidebar');

            // Sidebar.open();


            $('.media-sidebar-close').on('click', function(event) {
                event.preventDefault();

                Sidebar.close();
                return false;
            });

            ymaps.ready(function() {
                YandexMapInit(handle, props, val);


                // create new ballon on click
                arrYMaps[ handle ].events.add('click', function(e) {

                    var placemark = new ymaps.Placemark( e.get('coords'), {
                        // iconContent: '',
                        // hintContent: '',
                        // balloonContent: '',
                        // balloonContentHeader: '',
                        // balloonContentBody: '',
                        // balloonContentFooter: '',
                    } );

                    arrYMaps[ handle ].geoObjects.add( placemark );

                    /**
                     * New placemarks opened as default
                     */
                    placemark.options.set('opened', 1);

                    placemark.events.add('click', function(e) {
                        Sidebar.open( arrYMaps[ handle ], placemark );
                    });
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

                var $controlsPane = $('#controls-pane');
                $('#controls').on('click', function(event) {
                    $controlsPane.fadeToggle();
                });

                var controls = $.map(arrYMaps[ handle ].controls[ '_controlKeys' ], function(item, index) {
                    return item;
                });

                $('input', $controlsPane).each(function(index, el) {
                    var $self = $(this);

                    console.log($self.attr('name'), $.inArray($self.attr('name'), controls));
                    if(-1 !== $.inArray($self.attr('name'), controls) ) {
                        $self.prop('checked', true);
                    }
                });

                console.log(controls);

                $('.button-insert-yandex-map').on('click', function(event) {
                    wp.mce.yamap.submit( tinyMCE.activeEditor, {
                        center: document.getElementsByName('center')[0].value,
                        zoom: document.getElementsByName('zoom')[0].value,
                        height: document.getElementsByName('height')[0].value
                    } );
                    Modal.close();
                });
            });
        }

        var arrYMaps = [];
        var placemarks = [];

        var shortcode_string = 'yamap';

        wp.mce = wp.mce || {};
        wp.mce.yamap = {
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
                $.each(this.findSubShortcodes('bullet', shortcode_data.shortcode.content), function(index, el) {
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

                    var shortcode = {
                        tag: 'bullet',
                        type: 'single',
                        attrs : {
                            coords: geoObject.geometry.getCoordinates().join(':'),
                        }
                    }

                    if( geoObject.properties.get('balloonContentHeader') )
                        shortcode.attrs.title = geoObject.properties.get('balloonContentHeader');

                    if( geoObject.properties.get('balloonContentBody') )
                        shortcode.attrs.body = geoObject.properties.get('balloonContentBody');

                    if( geoObject.properties.get('balloonContentFooter') )
                        shortcode.attrs.footer = geoObject.properties.get('balloonContentFooter');

                    if( geoObject.options.get('iconColor') )
                        shortcode.attrs.color = geoObject.options.get('iconColor');

                    if( 1 == geoObject.options.get('opened') )
                        shortcode.attrs.opened = 'true';

                    content += wp.shortcode.string( shortcode );
                });

                args.content = content;
                editor.insertContent( wp.shortcode.string( args ) );
            }
        };


        // $.each(yamap_props, YandexMapInit(handle, val) );

        $('#insert-yandex-map').on('click', function(event) {
            event.preventDefault();

            OpenYandexMapWindow('EditYandexMapContainer', YandexMap.defaults, {});
        });

        wp.mce.views.register( shortcode_string, wp.mce.yamap );

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
