/* global tinyMCE */

(function($) {

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

        // Set after open modal only
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
            this.$opened.prop('checked', true == placemark.options.get('opened') ? true : false);

            function htmlspecialchars(html) {
                return html
                    .replace(/ /g, "&nbsp;")
                    .replace(/=/g, "&eqal;")
                    .replace(/&/g, "&amp;")
                    .replace(/"/g, "&apos;")
                    .replace(/"/g, "&quot;")
                    .replace(/>/g, "&gt;")
                    .replace(/</g, "&lt;");
            }

            this.$header.on('keyup', function(event) {
                placemark.properties.set('balloonContentHeader', htmlspecialchars($(this).val()));
            });

            this.$body.on('keyup', function(event) {
                placemark.properties.set('balloonContentBody', htmlspecialchars($(this).val()));
            });

            this.$footer.on('keyup', function(event) {
                placemark.properties.set('balloonContentFooter', htmlspecialchars($(this).val()));
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
    };

    function OpenYandexMapWindow(handle, properties) {
        Modal.content( new ModalContent() );
        Modal.open();

        Sidebar.__construct('#EditYandexMapSidebar');

        ymaps.ready(function() {
            let WPYMap = new WPYandexMap(handle, properties);
            let ymap = WPYMap.mapInstance;

            // insert exists placemarks
            if( properties.placemarks ) {
                properties.placemarks.each(function(i, placemark) {
                    WPYMap.insertPlacemark( placemark );
                });
            }

            // insert new placemark on click
            ymap.events.add('click', function(event) {
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
                event.preventDefault();

                if( $(this).is(':checked') ) {
                    ymap.controls.add( $(this).attr('name') );
                }
                else {
                    ymap.controls.remove( $(this).attr('name') );
                }
            });

            let controls = $.map(WPYMap.mapInstance.controls[ '_controlKeys' ], function(item, index) {
                return item;
            });

            $controls.each(function(index, el) {
                if(-1 !== $.inArray($(this).attr('name'), controls) ) {
                    $(this).prop('checked', true);
                }
            });

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

    $('#insert-yandex-map').on('click', function(event) {
        event.preventDefault();

        $.each(yandex_maps || {}, function(handle, properties) {
            OpenYandexMapWindow(handle, properties);
        });
    });

}(jQuery));
