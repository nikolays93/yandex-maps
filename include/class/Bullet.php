<?php

namespace NikolayS93\YandexMaps;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // disable direct access

class Bullet {
	public $title = '';
	public $body = '';
	public $footer = '';
	public $coords = false;

	public $opened = false;
	public $color = '#ff0000';

	function __construct( $args, $content ) {
		$args = wp_parse_args( $args, array_fill_keys( array(
			'title',
			'body',
			'footer',
			'opened',
			'color'
		), '' ) );

		if ( empty( $args['coords'] ) ) {
			return new \WP_Error( 'MISSING', 'Coords is empty' );
		}

		$Coords = new Coords( $args['coords'] );
		if ( ! $Coords->getCoords() ) {
			return new \WP_Error( 'INCORRECT', 'Coords is incorrect' );
		}

		$this
			->setCoords( $Coords->getCoords() )
			->setTitle( $args['title'] )
			->setBody( $content )
			->setFooter( $this->footer )
			->setOpened( $args['opened'] )
			->setColor( $args['color'] );
	}

	static function esc_html_entities( $str ) {
		return str_replace( array( '&#34;', '&#187;', '&#8243;', '&#8220;', '&#8221;', '&#8222;'), '', $str );
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @return string
	 */
	public function getFooter() {
		return $this->footer;
	}

	/**
	 * @return array|bool
	 */
	public function getCoords() {
		return $this->coords;
	}

	/**
	 * @return bool
	 */
	public function isOpened() {
		return $this->opened;
	}

	/**
	 * @return string|void
	 */
	public function getColor() {
		return $this->color;
	}

	/**
	 * @param mixed|string $title
	 */
	public function setTitle( $title ) {
		if ( $title = static::esc_html_entities( $title ) ) {
			$this->title = $title;
		}

		return $this;
	}

	/**
	 * @param string $body
	 */
	public function setBody( $body ) {
		if ( $body = static::esc_html_entities( $body ) ) {
			$this->body = $body;
		}

		return $this;
	}

	/**
	 * @param string $footer
	 */
	public function setFooter( $footer ) {
		if ( $footer = static::esc_html_entities( $footer ) ) {
			$this->footer = $footer;
		}

		return $this;
	}

	/**
	 * @param array|bool $coords
	 */
	public function setCoords( $coords ) {
		if ( $coords = static::esc_html_entities( $coords ) ) {
			$this->coords = $coords;
		}

		return $this;
	}

	/**
	 * @param bool $opened
	 */
	public function setOpened( $opened ) {
		$this->opened = "true" === static::esc_html_entities( $opened );

		return $this;
	}

	/**
	 * @param string|void $color
	 */
	public function setColor( $color ) {
		if ( $color = static::esc_html_entities( $color ) ) {
			if ( '#' !== substr( $color, 0, 1 ) ) {
				$color = '#' . $color;
			}

			$this->color = $color;
		}

		return $this;
	}


}
