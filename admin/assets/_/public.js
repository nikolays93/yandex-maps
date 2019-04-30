
(function() {
    window.WPYandexMap = function( args ) {
        this.placemarks = [];
        this.args = {
            center: null,
            zoom: null
        };

        this.__init( args );
    }

    window.WPYandexMap.prototype =
    {
        __init: function( args )
        {
            if( args.center ) this.args.center = this.setCoords( args.center );
            if( args.zoom )   this.args.zoom = parseInt( args.zoom );

            if( args.placemarks ) {
                args.placemarks.each(function(i, placemark) {
                    this.insertPlacemark( placemark );
                });
            }
        },

        setCoords: function( coords )
        {
            return coords;
        },

        insertPlacemark: function( placemark )
        {
            let bullet = {};

            if( placemark.title ) bullet.balloonContentHeader = placemark.title;
            if( placemark.title ) bullet.balloonContentBody   = placemark.body;
            if( placemark.title ) bullet.balloonContentFooter = placemark.footer;
            // let properties = {};
            // let options = {};

            // if( bullet.title ) properties.balloonContentHeader = bullet.title;
            // if( bullet.body ) properties.balloonContentBody = bullet.body;
            // if( bullet.footer ) properties.balloonContentFooter = bullet.footer;

            // if( bullet.color ) options.iconColor = bullet.color;

            // placemarks[ index ] = new ymaps.Placemark(bullet.coords, properties, options);

            // arrYMaps[ handle ].geoObjects.add( placemarks[ index ] );

            // if( bullet.title || bullet.body || bullet.footer ) {
            //     placemarks[ index ].balloon.open();
            // }
        }
    }
});

var YandexMap = new WPYandexMap();

let arrYMaps = [];

function YandexMapInit(handle, properties) {
    properties = properties || {};

    let placemarks = [];
    let yargs = {
        center: properties.center,
        zoom: properties.zoom,
    }

    if( typeof(properties.controls) == 'string' )
        yargs.controls = properties.controls.split(',');

    arrYMaps[ handle ] = new ymaps.Map(handle, yargs);

    if( properties.bullets ) {
        $.each(properties.bullets, function(index, bullet) {
            let properties = {};
            let options = {};

            if( bullet.title ) properties.balloonContentHeader = bullet.title;
            if( bullet.body ) properties.balloonContentBody = bullet.body;
            if( bullet.footer ) properties.balloonContentFooter = bullet.footer;

            if( bullet.color ) options.iconColor = bullet.color;

            placemarks[ index ] = new ymaps.Placemark(bullet.coords, properties, options);

            arrYMaps[ handle ].geoObjects.add( placemarks[ index ] );

            if( bullet.title || bullet.body || bullet.footer ) {
                placemarks[ index ].balloon.open();
            }
        });
    }

    return arrYMaps[ handle ];
}

// jQuery(document).ready(function($) {
//     ymaps.ready(function() {
//         $.each(yandex_maps || [], YandexMapInit);
//     });
// });
