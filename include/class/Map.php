<?php

namespace NikolayS93\YandexMaps;

use NikolayS93\YandexMaps\Creational\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // disable direct access

class Map {
	private $id;

	public $center = array();
	public $zoom;
	public $width;
	public $height;
	public $controls = array();
	public $bullets = array();

	function __construct( $map_id = '', $args = array() ) {
		if ( ! $map_id ) {
			$map_id = 'singleton';
		}

		$this->setId( $map_id );

		$defaults = static::getDefaults();
		$args     = wp_parse_args( $args, $defaults );

		$this
			->setCenter( $args['center'] )
			->setZoom( $args['zoom'] )
			->setWidth( $args['width'] )
			->setHeight( $args['height'] )
			->setControls( $args['controls'] );
	}

	static function getAllowedControls() {
		$controls = apply_filters( 'Map::getAllowedControls', array(
			'GeolocationControl',
			'SearchControl',
			'RouteButton',
			'TrafficControl',
			'TypeSelector',
			'FullscreenControl',
			'ZoomControl',
			'RulerControl',
			'Button',
			'ListBox',
		) );

		return $controls;
	}

	/**
	 * Default properties for a simple create yandex map
	 */
	static function getDefaults() {
		$defaults = array(
			'center'   => array( '56.852593', '53.204843' ), // Izhevsk
			'zoom'     => 10,
			'width'    => '100%',
			'height'   => '400px',
			'controls' => array( 'zoomControl', 'searchControl' ),
		);

		return apply_filters( 'Map::getDefaults', $defaults );
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId( $id ) {
		$this->id = (string) esc_attr( $id );

		return $this;
	}

	/**
	 * @param Bullet $Bullet
	 *
	 * @return $this
	 */
	function addBullet( Bullet $Bullet ) {
		if ( $Bullet->getCoords() ) {
			$this->bullets[] = $Bullet;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	function getBullets() {
		return $this->bullets;
	}

	/**
	 * @return array|bool
	 */
	public function getCenter() {
		return $this->center;
	}

	/**
	 * @param array|bool $center
	 *
	 * @return $this
	 */
	public function setCenter( $center ) {
		$Coords       = new Coords( $center );
		$this->center = $Coords->getCoords();

		return $this;
	}

	/**
	 * @return int
	 */
	public function getZoom() {
		return $this->zoom;
	}

	/**
	 * @param int $zoom
	 */
	public function setZoom( $zoom ) {
		$this->zoom = (int) $zoom;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param mixed $width
	 *
	 * @todo add sanitize
	 */
	public function setWidth( $width ) {
		$this->width = $width;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param mixed $height
	 *
	 * @todo add sanitize
	 */
	public function setHeight( $height ) {
		$this->height = $height;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getControls() {
		return $this->controls;
	}

	/**
	 * @param array $controls
	 */
	public function setControls( $controls ) {

		if ( is_string( $controls ) ) {
			$controls = explode( ',', $controls );
		}

		// new WP_Error('INCORRECT', 'Incorrect control names type')
		if ( ! is_array( $controls ) ) {
			return $this;
		}

		$allowed = $this->getAllowedControls();

		$controls = array_filter( $controls, function ( $name ) use ( $allowed ) {
			if ( $name = trim( $name ) ) {
				return in_array( $name, $allowed ) ? $name : false;
			}

			return false;
		} );

		$this->controls = implode( ',', $controls );

		return $this;
	}


}
