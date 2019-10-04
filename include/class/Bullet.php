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

	function __construct( $args ) {
		if ( empty( $args['coords'] ) ) {
			return new \WP_Error( 'MISSING', 'Coords is empty' );
		}

		$Coords = new Coords( $args['coords'] );
		if ( ! $Coords->getCoords() ) {
			return new \WP_Error( 'INCORRECT', 'Coords is incorrect' );
		}

		$this->coords = $Coords->getCoords();

		if ( ! empty( $args['title'] ) ) {
			$this->title = sanitize_text_field( $args['title'] );
		}

		if ( ! empty( $args['body'] ) ) {
			$this->body = sanitize_text_field( $args['body'] );
		}

		if ( ! empty( $args['footer'] ) ) {
			$this->footer = sanitize_text_field( $args['footer'] );
		}

		if ( ! empty( $args['opened'] ) ) {
			$this->opened = (bool) $args['opened'];
		}

		if ( ! empty( $args['color'] ) ) {
			if ( '#' !== substr( $args['color'], 0, 1 ) ) {
				$args['color'] = '#' . $args['color'];
			}

			$this->color = esc_attr( $args['color'] );
		}
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
}
