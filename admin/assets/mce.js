/* global tinyMCE */
(function($) {
    $(document).ready( function() {

        const shortcode_string = 'yamap';

        yandex_maps = yandex_maps || {};

        function tmce_insertContent(content, editor_id, textarea_id) {
            if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
            if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

            if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
                return tinyMCE.get(editor_id).insertContent(content);
            } else {
                return jQuery('#'+textarea_id).val(jQuery('#'+textarea_id).val() + content);
            }
        }

        function insertMark(ymap, event) {
            var placemark = new ymaps.Placemark( event.get('coords') );

            ymap.geoObjects.add( placemark );

            /**
             * New placemarks opened as default
             */
            placemark.options.set('opened', 1);

            Sidebar.open( ymap, placemark );
            placemark.events.add('click', function(e) {
                Sidebar.open( ymap, placemark );
            });
        }

        function getControls(ymap) {
            return $.map(ymap.controls[ '_controlKeys' ], function(item, index) {
                return item;
            });
        }

        function checkControls(ymap, $controls) {
            let controls = getControls( ymap );
            $controls.each(function(index, el) {
                let $self = $(this);

                if(-1 !== $.inArray($self.attr('name'), controls) ) {
                    $self.prop('checked', true);
                }
            });
        }

        function changeControls(ymap, e, $input) {
            e.preventDefault();

            let name = $input.attr('name');

            if( $input.is(':checked') ) {
                ymap.controls.add( name );
            }
            else {
                ymap.controls.remove( name );
            }
        }

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

                $('.media-sidebar-close').on('click', function(event) {
                    event.preventDefault();

                    Sidebar.close();
                    return false;
                });
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

        function OpenYandexMapWindow(handle, properties) {
            properties = properties || [];

            Modal.content( new ModalContent() );
            Modal.open();

            /**
             * Set after open modal only
             */
            Sidebar.__construct('#EditYandexMapSidebar');

            ymaps.ready(function() {
                console.log( handle, properties );
                let ymap = YandexMapInit(handle, properties);

                // create new ballon on click
                ymap.events.add('click', function(e) {
                    insertMark(ymap, e);
                });

                /**
                 * change zoom/coordinates
                 */
                ymap.events.add('boundschange', function(e) {
                    document.getElementsByName('center')[0].value = ymap.getCenter().join(':');
                    document.getElementsByName('zoom')[0].value = e.get('newZoom');
                });

                var center = properties.center || yandex_maps.defaults.center;
                document.getElementsByName('center')[0].value = center.join(':');
                document.getElementsByName('zoom')[0].value   = properties.zoom || yandex_maps.defaults.zoom;
                document.getElementsByName('height')[0].value = properties.height || yandex_maps.defaults.height;

                /**
                 * Controls constructor
                 */
                let $controlsPane = $('#controls-pane');
                let $controls = $('input', $controlsPane);

                $('#controls button').on('click', function(event) {
                    $controlsPane.fadeToggle();
                });

                $controls.on('change', function(event) {
                    let $self = $(this);

                    changeControls(ymap, event, $self);
                });

                checkControls(ymap, $controls);

                $('.button-insert-yandex-map').on('click', function(event) {
                    wp.mce.yamap.submit( tinyMCE.activeEditor, ymap, {
                        center: document.getElementsByName('center')[0].value,
                        zoom: document.getElementsByName('zoom')[0].value,
                        height: document.getElementsByName('height')[0].value
                    } );
                    Modal.close();
                });
            });
        }

        // $('#insert-yandex-map').on('click', function(event) {
            // event.preventDefault();

            $.each(yandex_maps, function(handle, properties) {
                OpenYandexMapWindow(handle, properties);
            });
        // });

        try {

        /**
         * Shortcode
         */
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
                var val = {};

                $.each(yandex_maps, function(handle, properties) {
                    val = properties;
                });

                // parse bullets
                $.each(this.findSubShortcodes('bullet', shortcode_data.shortcode.content), function(index, el) {
                    val.bullets.push( {
                        coords: el.shortcode.attrs.named.coords.split(':'),
                        title: el.shortcode.attrs.named.title || ''
                    } );
                });

                if( values.center ) val.center = values.center.split(':');
                if( values.zoom ) val.zoom = values.zoom;
                if( values.height ) val.height = values.height;
                if( values.controls ) val.controls = values.controls;

                // init modal
                OpenYandexMapWindow('EditYandexMapContainer', val);
            },
            submit: function(editor, ymap, values) {
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

                var controls = getControls( ymap );
                if( controls.join(',') != 'zoomControl,searchControl' ) {
                    args.attrs.controls = controls.join(',');
                }

                ymap.geoObjects.each(function(geoObject) {
                    let shortcode = {
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

                tmce_insertContent(wp.shortcode.string( args ));
            }
        };

        wp.mce.views.register( shortcode_string, wp.mce.yamap );

        } catch(e) {
            // statements
            console.log(e);
        }
    });
}(jQuery));
