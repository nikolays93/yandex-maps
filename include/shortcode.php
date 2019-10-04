<?php

namespace NikolayS93\YandexMaps;

function register_yamaps_shortcode($args = array(), $content = '') {
	$args = shortcode_atts(
		array_merge(array('id' => ''), Map::getDefaults()),
		$args,
		'yamap'
	);

	$Map = new Map($args['id'], $args);

	add_shortcode('bullet', function( $args = array(), $content = '' ) use ($Map) {
		$Bullet = new Bullet( $args );
		$Map->addBullet($Bullet);
	});

	do_shortcode( $content, $ignore_html = true );
	remove_shortcode('bullet');

	Plugin()->getCollection()->add( $Map );

	$container = sprintf('<div id="%s" style="width: %s;height: %s;"></div>',
		esc_attr( $args['id'] ),
		esc_attr( $args['width'] ),
		esc_attr( $args['height'] )
	);

	return apply_filters( 'yamaps_shortcode_container', $container );
}
