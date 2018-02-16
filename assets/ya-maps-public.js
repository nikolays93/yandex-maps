(function($) {
    $(document).ready( function() {
        var arrYMaps = [];
        var placemarks = [];

        $.each(yamaps_props, function(handle, val) {
            ymaps.ready(function() {
                arrYMaps[ handle ] = new ymaps.Map(handle, {
                    center: val.center,
                    zoom: val.zoom,
                    controls: ['zoomControl', 'searchControl']
                });

                $.each(val.bullets, function(index, bullet) {
                    placemarks[ index ] = new ymaps.Placemark(bullet.coords, {
                        balloonContent: bullet.title
                    });

                    arrYMaps[ handle ].geoObjects.add( placemarks[ index ] );
                });
            });
        });
    });
})(jQuery);