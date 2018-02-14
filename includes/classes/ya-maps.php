<?php

namespace CDevelopers\Yandex\Map;

class yaMaps
{
    public $atts = array();

    function get_id()
    {
        return $this->atts['id'];
    }

    function __construct( $atts )
    {
        $this->atts = array(
            'id'     => esc_attr( self::sanitize_cyr_url( sanitize_text_field( $atts['title'] ) ) ),
            'title'  => sanitize_text_field( $atts['title'] ),
            'coords' => implode(',', array_map('floatval', explode(' ', $atts['coords']))),
            'zoom'   => intval( $atts['zoom'] ),
            'height' => intval( $atts['height'] ),
        );

        add_action( 'wp_footer', array($this, 'shortcode_callback') );
    }

    static function sanitize_cyr_url($s){
        $s = strip_tags( (string) $s);
        $s = str_replace(array("\n", "\r"), " ", $s);
        $s = preg_replace("/\s+/", ' ', $s);
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
        $s = str_replace(" ", "-", $s);
        return $s;
    }

    function shortcode_callback() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                ymaps.ready(function(){
                    var ya_map = new ymaps.Map("ya-maps-<?=$this->atts['id'];?>", {
                        center: [<?=$this->atts['coords'];?>],
                        zoom: <?=$this->atts['zoom'];?>,
                    });

                    // var geocoder = ymaps.geocode("Ижевск");
                    // geocoder.then(function (res) {
                    //     if (res.geoObjects.getLength()) {
                    //         var point = res.geoObjects.get(0);
                    //         ya_map.geoObjects.add(point);
                    //         ya_map.panTo(point.geometry.getCoordinates());
                    //     }
                    // });

                    var myPlacemark = new ymaps.Placemark([<?=$this->atts['coords'];?>], {
                        balloonContent: '<?=$this->atts['title'];?>'
                    });
                    ya_map.geoObjects.add(myPlacemark);

                    // ya_map.events.add('boundschange', function(e) {
                    //     console.log( ya_map.getZoom(), ya_map.getCenter() );
                    // });

                    // ya_map.events.add('click', function (e) {
                    //     ya_map.geoObjects.add( new ymaps.Placemark(e.get('coords')) );
                    //     ya_map.geoObjects.each(function(geoObject){
                    //         console.log( geoObject.geometry.getCoordinates() );
                    //     });
                    // });
                });
            });
        </script>
        <?php
    }

    static function load_api_maps(){
        wp_enqueue_script( 'api-maps-yandex' );
    }
}