
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

(function($) {
    $(document).ready( function() {
        ymaps.ready(function() {
            var yandex_maps = yandex_maps || {};
            $.each(yandex_maps || [], YandexMapInit);
        });
    });
})(jQuery);