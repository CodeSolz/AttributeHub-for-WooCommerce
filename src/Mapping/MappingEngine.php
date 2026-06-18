<?php
/**
 * AttributeHub — Mapping Engine
 *
 * @package AttributeHub\Free\Mapping
 */

namespace AttributeHub\Free\Mapping;

use AttributeHub\Free\Database\QueryCache;
use AttributeHub\Free\Database\MasterGroupRepository;
use AttributeHub\Free\Database\ValueMappingRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Core mapping engine: resolves raw WC term_ids to master labels.
 * Uses two-level cache: in-memory (request) + transients (24hr).
 */
class MappingEngine {

	/** @var MappingEngine|null */
	protected static ?MappingEngine $instance = null;

	/**
	 * In-memory request-level cache.
	 * Structure: [ taxonomy => [ term_id => master_group_id ] ]
	 *
	 * @var array<string, array<int, int>>
	 */
	private array $term_map_cache = array();

	/**
	 * In-memory master group cache.
	 * Structure: [ taxonomy => [ master_group_id => object ] ]
	 *
	 * @var array<string, array<int, object>>
	 */
	private array $master_cache = array();

	/** @var ValueMappingRepository */
	private ValueMappingRepository $mapping_repo;

	/** @var MasterGroupRepository */
	private MasterGroupRepository $master_repo;

	/**
	 * Returns the singleton instance.
	 */
	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __construct() {
		$this->mapping_repo = new ValueMappingRepository();
		$this->master_repo  = new MasterGroupRepository();
	}

	/**
	 * Gets the master group label for a WC term.
	 * Returns null if the term is not mapped.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @return string|null
	 */
	public function get_master_label( int $term_id, string $taxonomy ): ?string {
		$master_group_id = $this->resolve_term( $term_id, $taxonomy );

		if ( null === $master_group_id ) {
			return null;
		}

		$master = $this->get_master_object( $master_group_id, $taxonomy );
		$label  = $master ? $master->label : null;

		/**
		 * Filter the resolved master label. Pro can override with sub-label.
		 *
		 * @param string|null $label
		 * @param int         $term_id
		 * @param string      $taxonomy
		 */
		return apply_filters( 'attributehub_get_master_label', $label, $term_id, $taxonomy );
	}

	/**
	 * Gets all raw term_ids that map to a master group in a taxonomy.
	 *
	 * @param int    $master_group_id
	 * @param string $taxonomy
	 * @return int[]
	 */
	public function get_term_ids_for_master( int $master_group_id, string $taxonomy ): array {
		$map     = $this->get_taxonomy_term_map( $taxonomy );
		$term_ids = array();

		foreach ( $map as $term_id => $mgid ) {
			if ( $mgid === $master_group_id ) {
				$term_ids[] = $term_id;
			}
		}

		return $term_ids;
	}

	/**
	 * Gets the master_group_id for a term, or null if unmapped.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @return int|null
	 */
	public function resolve_term( int $term_id, string $taxonomy ): ?int {
		$map = $this->get_taxonomy_term_map( $taxonomy );
		return $map[ $term_id ] ?? null;
	}

	/**
	 * Gets a master group object by its slug in a taxonomy.
	 * Used for URL parameter resolution (filter_pa_color=black → master object).
	 *
	 * @param string $slug
	 * @param string $taxonomy
	 * @return object|null
	 */
	public function resolve_master_by_slug( string $slug, string $taxonomy ): ?object {
		return $this->master_repo->find_by_slug( $slug, $taxonomy );
	}

	/**
	 * Gets the full [term_id => master_label] map for a taxonomy.
	 * Used for bulk override in admin preview and filter consolidation.
	 *
	 * @param string $taxonomy
	 * @return array<int, string>
	 */
	public function get_taxonomy_map( string $taxonomy ): array {
		$map    = $this->get_taxonomy_term_map( $taxonomy );
		$result = array();

		foreach ( $map as $term_id => $master_group_id ) {
			$master = $this->get_master_object( $master_group_id, $taxonomy );
			if ( $master ) {
				$result[ $term_id ] = $master->label;
			}
		}

		return $result;
	}

	/**
	 * Flushes all caches for a taxonomy (or all taxonomies if empty).
	 * Call this after any mapping save/delete operation.
	 *
	 * @param string $taxonomy Leave empty to flush all.
	 */
	public function flush_cache( string $taxonomy = '' ): void {
		if ( $taxonomy ) {
			unset( $this->term_map_cache[ $taxonomy ] );
			unset( $this->master_cache[ $taxonomy ] );
			QueryCache::flush_taxonomy( $taxonomy );
		} else {
			$this->term_map_cache = array();
			$this->master_cache   = array();
			QueryCache::flush_all();
		}
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the [term_id => master_group_id] map for a taxonomy.
	 * Checks in-memory cache → transient → DB.
	 *
	 * @param string $taxonomy
	 * @return array<int, int>
	 */
	private function get_taxonomy_term_map( string $taxonomy ): array {
		// Level 1: in-memory
		if ( isset( $this->term_map_cache[ $taxonomy ] ) ) {
			return $this->term_map_cache[ $taxonomy ];
		}

		// Level 2: transient
		$cache_key = QueryCache::mapping_key( $taxonomy );
		$cached    = QueryCache::get( $cache_key );

		if ( false !== $cached ) {
			$this->term_map_cache[ $taxonomy ] = $cached;
			return $cached;
		}

		// Level 3: DB
		$map = $this->mapping_repo->get_full_taxonomy_map( $taxonomy );
		QueryCache::set( $cache_key, $map );
		$this->term_map_cache[ $taxonomy ] = $map;

		return $map;
	}

	/**
	 * Returns a master group object by ID, with in-memory caching.
	 *
	 * @param int    $master_group_id
	 * @param string $taxonomy Used as a cache bucket key.
	 * @return object|null
	 */
	private function get_master_object( int $master_group_id, string $taxonomy ): ?object {
		if ( ! isset( $this->master_cache[ $taxonomy ] ) ) {
			$this->master_cache[ $taxonomy ] = array();
		}

		if ( ! isset( $this->master_cache[ $taxonomy ][ $master_group_id ] ) ) {
			$this->master_cache[ $taxonomy ][ $master_group_id ] = $this->master_repo->find( $master_group_id );
		}

		return $this->master_cache[ $taxonomy ][ $master_group_id ];
	}
}
