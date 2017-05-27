<?php

namespace Grav\Plugin;

use Grav\Common\GravTrait;

class CascadeFilters {
	use GravTrait;

	protected $filters;
	protected $taxonomylist;
	protected $taxCollections;

	public function __construct( $filters ) {
		$this->filters        = $filters;
		$this->taxCollections = [];
		$collection = self::getGrav()['page']->collection(); // Don't use children(), children != collection
		foreach ( $collection as $item ) {
			$taxs = $item->taxonomy();
			$temp = array_filter( $taxs, function ( $k ) {
				return in_array( $k, $this->filters );
			}, ARRAY_FILTER_USE_KEY );
			if ( ! empty( $temp ) ) {
				$this->taxCollections[] = $temp;
			}
		}
	}

	public function isDisabled( $queries ) {
		$result  = false;
		$queries = array_filter( $queries, function ( $v ) {
			return $v !== null;
		} );
		if ( ! empty( $queries ) ) {
			$filtedArr = array_filter( $this->taxCollections, function ( $taxs ) use ( $queries ) {
				$exArr = array_filter( $taxs, function ( $v, $k ) use ( $queries ) {
					// fix the lowercase issues
					return isset( $queries[ $k ] ) && in_array( $queries[ $k ], $v );
				}, ARRAY_FILTER_USE_BOTH );

				return count( $exArr ) === count( $queries ) ? true : false;
			} );

			if ( empty( $filtedArr ) ) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Get taxonomy list.
	 *
	 * @return array
	 */
	public function get() {
		if ( ! $this->taxonomylist ) {
			$this->build();
		}

		return $this->taxonomylist;
	}

	/**
	 * @internal
	 */
	protected function build() {
		$taxonomylist = self::getGrav()['taxonomy']->taxonomy();
		$cache        = self::getGrav()['cache'];
		$hash         = hash( 'md5', serialize( $taxonomylist ) );

		if ( $taxonomy = $cache->fetch( $hash ) ) {
			$this->taxonomylist = $taxonomy;
		} else {
			$newlist = [];
			foreach ( $taxonomylist as $x => $y ) {
				if ( in_array( $x, $this->filters ) ) {
					$partial = [];
					foreach ( $taxonomylist[ $x ] as $key => $value ) {
						$taxonomylist[ $x ][ strval( $key ) ] = count( $value );
						$partial[ strval( $key ) ]            = count( $value );
					}
					arsort( $partial );
					$newlist[ $x ] = $partial;
				}
			}
			$cache->save( $hash, $newlist );
			$this->taxonomylist = $newlist;
		}
	}
}
