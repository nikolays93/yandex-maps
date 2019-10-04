
(function(w) {
    w.WPYandexMap = function( handle, args ) {
        this.placemarks = [];

        this.args = {
            center: null,
            zoom: null,
            controls: []
        };

        if( args.center )   this.args.center = this.escCoords( args.center );
        if( args.zoom )     this.args.zoom = parseInt( args.zoom );
        if( args.controls ) this.args.controls = args.controls;

        this.mapInstance = new ymaps.Map(handle, this.args);
    };

    w.WPYandexMap.prototype.escCoords = function( coords ) {
        if( 'string' == typeof(coords) ) {
            coords.split(':');
        }

        return coords;
    };

    w.WPYandexMap.prototype.insertPlacemark = function( placemark ) {
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
    };
})(window);

if( typeof yandex_maps !== "undefined" ) {
    ymaps.ready(function() {
        Object.keys(yandex_maps || []).map(function(mapKey, index) {
            var WPYMap = new WPYandexMap(document.getElementById(mapKey), yandex_maps[mapKey]);
            var placemarks = yandex_maps[mapKey].bullets;

            Object.keys(placemarks || []).map(function(placemarkKey, index) {
                WPYMap.insertPlacemark( placemarks[placemarkKey] );
            });
        });
    });
}
