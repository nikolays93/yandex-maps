<?php

namespace NikolayS93\YandexMaps\ORM;

use NikolayS93\YandexMaps\Map;

class MapsCollection implements \ArrayAccess, \Countable, \IteratorAggregate {

	/**
	 * Variables for wp_register_script, wp_enqueue_script, wp_localize_script
	 */
	const API_NAME = 'yandex-maps-api';
	const PUBLIC_NAME = 'yandex-maps-public';

	/**
	 * @var array $items
	 */
	protected $items = array();

	/**
	 * Class constructor.
	 *
	 * @param array $items
	 */
	public function __construct( $items = array() ) {
		$this->add( $items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->items );
	}

	/**
	 * Enqueue & Exchange props to scripts
	 */
	public function enqueue_scripts() {
		if ( empty( $this->items ) ) {
			return;
		}

		wp_enqueue_script( static::API_NAME );
		wp_enqueue_script( static::PUBLIC_NAME );

		wp_localize_script( static::PUBLIC_NAME, "yandex_maps",
			apply_filters( 'MapsCollection::enqueue_scripts', $this->items ) );
	}

	/**
	 * @param $callback
	 *
	 * @return $this
	 */
	public function usort( $callback ) {
		@usort( $this->items, $callback );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function first() {
		return reset( $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function last() {
		return end( $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function current() {
		return current( $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function next() {
		return next( $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function key() {
		return key( $this->items );
	}

	/**
	 * @return self
	 */
	public function filter( \Closure $p ) {
		return new static( array_filter( $this->items, $p ) );
	}

	/**
	 * Add item to collection.
	 *
	 * @param array|object $item
	 */
	public function add( $item ) {
		if ( $item instanceof \Traversable ) {
			foreach ( $item as $i ) {
				$this->add( $i );
			}

			return $this;
		}

		if ( $item instanceof Map ) {
			$this->items[ $item->getId() ] = $item;
		}

		return $this;
	}

	/**
	 * @param object $item
	 *
	 * @return bool|array
	 */
	public function has( $item ) {
		return array_search( $item, $this->items );
	}

	/**
	 * @param object[]|object $items
	 *
	 * @return $this
	 */
	public function replace( $items ) {
		if ( is_array( $items ) || $items instanceof \Traversable ) {
			foreach ( $items as $item ) {
				$this->replace( $item );
			}

			return $this;
		}

		$item = $items;

		if ( ( $position = $this->has( $item ) ) !== false ) {
			$this->offsetSet( $position, $item );
		} else {
			$this->add( $item );
		}

		return $this;
	}

	/**
	 * Return all items as array.
	 *
	 * @return array
	 */
	public function fetch() {
		return $this->items;
	}

	/**
	 * Check collection for empty.
	 */
	public function isEmpty() {
		return empty( $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists( $offset ) {
		return isset( $this->items[ $offset ] ) || array_key_exists( $offset, $this->items );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet( $offset ) {
		if ( isset( $this->items[ $offset ] ) ) {
			return $this->items[ $offset ];
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetSet( $offset, $value ) {
		if ( ! isset( $offset ) ) {
			return $this->add( $value );
		}

		return $this->items[ $offset ] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset( $offset ) {
		if ( isset( $this->items[ $offset ] ) || array_key_exists( $offset, $this->items ) ) {
			$removed = $this->items[ $offset ];
			unset( $this->items[ $offset ] );

			return $removed;
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Получить элемент по индексу
	 *
	 * @param $offset
	 *
	 * @return mixed
	 */
	public function get( $offset ) {
		if ( isset( $this->items[ $offset ] ) ) {
			return $this->items[ $offset ];
		}

		return null;
	}
}
