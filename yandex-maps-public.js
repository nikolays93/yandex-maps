(function($) {
    $(document).ready( function() {
        var arrYMaps = [];
        var placemarks = [];

        $.each(yandex_maps, function(handle, val) {
            ymaps.ready(function() {
                arrYMaps[ handle ] = new ymaps.Map(handle, {
                    center: val.center,
                    zoom: val.zoom,
                    controls: [],
                    // controls: ['fullscreenControl', 'geolocationControl', 'routeEditor', 'rulerControl', 'searchControl', 'trafficControl', 'routeEditor', 'typeSelector', 'zoomControl']
                });

                $.each(val.bullets, function(index, bullet) {

                    var properties = {};
                    var options = {};

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
            });
        });
    });
})(jQuery);