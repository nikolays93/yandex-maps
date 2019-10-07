(function($) {
    const shortcode_string = 'yamap';

    wp.mce = wp.mce || {};
    wp.mce[ shortcode_string ] = {
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
            var existsProperties = shortcode_data.shortcode.attrs.named;

            var handle = 'EditYandexMapContainer';
            var mapProperties = yandex_maps[ handle ] || {placemarks: []};
            if( !mapProperties.placemarks ) {
                mapProperties.placemarks = [];
            }

            // parse placemarks
            $.each(this.findSubShortcodes('bullet', shortcode_data.shortcode.content), function(index, el) {
                mapProperties.placemarks.push( {
                    coords: el.shortcode.attrs.named.coords.split(':'),
                    title: el.shortcode.attrs.named.title || '',
                    body: el.shortcode.content,
                    footer: el.shortcode.attrs.named.footer || ''
                } );
            });

            if( existsProperties.center ) mapProperties.center = existsProperties.center.split(':');
            if( existsProperties.zoom ) mapProperties.zoom = existsProperties.zoom;
            if( existsProperties.height ) mapProperties.height = existsProperties.height;
            if( existsProperties.controls ) mapProperties.controls = existsProperties.controls;

            // init modal
            OpenYandexMapWindow(handle, mapProperties);
        },

        // todo refactor
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

            var controls = $.map(ymap.controls[ '_controlKeys' ], function(item, index) {
                return item;
            });

            // setup controls when controls not default
            if( controls.join(',') != 'zoomControl,searchControl' ) {
                args.attrs.controls = controls.join(',');
            }

            ymap.geoObjects.each(function(geoObject) {
                let shortcode = {
                    tag: 'bullet',
                    type: 'closed',
                    attrs : {
                        coords: geoObject.geometry.getCoordinates().join(':'),
                    }
                };

                if( geoObject.properties.get('balloonContentHeader') ) {
                    shortcode.attrs.title = geoObject.properties.get('balloonContentHeader');
                }

                if( geoObject.properties.get('balloonContentBody') ) {
                    shortcode.content = geoObject.properties.get('balloonContentBody');
                }

                if( geoObject.properties.get('balloonContentFooter') ) {
                    shortcode.attrs.footer = geoObject.properties.get('balloonContentFooter');
                }

                if( geoObject.options.get('iconColor') ) {
                    shortcode.attrs.color = geoObject.options.get('iconColor');
                }

                if( 1 == geoObject.options.get('opened') ) {
                    shortcode.attrs.opened = 'true';
                }

                content += wp.shortcode.string( shortcode );
            });

            args.content = content;

            var content = wp.shortcode.string( args );
            if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
            if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

            if ( $('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
                return tinyMCE.get(editor_id).insertContent(content);
            } else {
                var $textarea = $('#' + textarea_id);
                return $textarea.val($textarea.val() + content);
            }
        }
    };

    wp.mce.views.register( shortcode_string, wp.mce[ shortcode_string ] );
}(jQuery));