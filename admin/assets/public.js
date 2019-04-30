
(function() {
    if( window.WPYandexMap ) return;

    window.WPYandexMap = function( args ) {

        this.mapInstance = new ymaps.Map(handle, this.args);

        this.placemarks = [];

        this.args = {
            center: null,
            zoom: null,
            controls: []
        };

        if( args.center ) this.args.center = this.escCoords( args.center );
        if( args.zoom )   this.args.zoom = parseInt( args.zoom );

        if( args.placemarks ) {
            args.placemarks.each(function(i, placemark) {
                this.insertPlacemark( placemark );
            });
        }
    }

    window.WPYandexMap.prototype =
    {
        escCoords: function( coords )
        {
            if( 'string' == typeof(coords) ) {
                coords.split(':');
            }

            return coords;
        },

        insertPlacemark: function( placemark )
        {
            placemark = placemark || {};
            let coords = this.escCoords( placemark.coords || false );
            let bullet = {};
            let options = {};

            if( coords ) {
                if( placemark.title )  bullet.balloonContentHeader = placemark.title;
                if( placemark.body )   bullet.balloonContentBody   = placemark.body;
                if( placemark.footer ) bullet.balloonContentFooter = placemark.footer;

                if( placemark.color ) options.iconColor           = placemark.color;

                let newPlacemark = new ymaps.Placemark(coords, bullet, options);

                this.mapInstance.geoObjects.add( newPlacemark );
                if( placemark.title || placemark.body || placemark.footer ) {
                    newPlacemark.balloon.open();
                }
            }
        }
    }
});

var YandexMap = new WPYandexMap();

// jQuery(document).ready(function($) {
//     ymaps.ready(function() {
//         $.each(yandex_maps || [], YandexMapInit);
//     });
// });
