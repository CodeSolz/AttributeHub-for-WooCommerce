<?php
/**
 * AttributeHub — Filter Query Modifier
 *
 * @package AttributeHub\Free\Frontend
 */

namespace AttributeHub\Free\Frontend;

use AttributeHub\Free\Mapping\MappingEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Expands WooCommerce layered nav tax_query to include all raw term_ids
 * that are mapped to the active master group slug.
 *
 * Core mechanism: When a customer filters by "black" (master group slug),
 * the tax_query is rewritten to include all raw term_ids mapped to Black
 * (BK, BLK, BCK, MULTIBK, etc.) so products are returned correctly.
 */
class FilterQueryModifier {

	/**
	 * Intercepts the WooCommerce product query and expands mapped filter terms.
	 * Hooked to woocommerce_product_query (action).
	 *
	 * @param \WP_Query   $query    The main WP_Query.
	 * @param \WC_Query   $wc_query The WooCommerce query object.
	 */
	public function expand_product_query( \WP_Query $query, \WC_Query $wc_query ): void {
		if ( ! $query->is_main_query() ) {
			return;
		}

		$tax_query = $query->get( 'tax_query' );

		if ( empty( $tax_query ) || ! is_array( $tax_query ) ) {
			return;
		}

		$engine   = MappingEngine::instance();
		$modified = false;

		foreach ( $tax_query as $i => $clause ) {
			if ( ! is_array( $clause ) || empty( $clause['taxonomy'] ) ) {
				continue;
			}

			$taxonomy = $clause['taxonomy'];

			if ( ! $this->is_attribute_taxonomy( $taxonomy ) ) {
				continue;
			}

			$terms    = (array) ( $clause['terms'] ?? array() );
			$field    = $clause['field'] ?? 'slug';
			$expanded = array();

			foreach ( $terms as $term_identifier ) {
				// Resolve to a WP_Term object regardless of whether it's a slug, name, or ID
				$term = null;

				if ( $field === 'term_id' || is_numeric( $term_identifier ) ) {
					$term = get_term( (int) $term_identifier, $taxonomy );
				} else {
					$term = get_term_by( $field, $term_identifier, $taxonomy );
				}

				if ( ! $term || is_wp_error( $term ) ) {
					// Keep original identifier if we can't resolve it
					$expanded[] = $term_identifier;
					continue;
				}

				// Check if this term's slug matches a master group slug
				$master = $engine->resolve_master_by_slug( $term->slug, $taxonomy );

				if ( $master ) {
					// Expand: get all raw term_ids mapped to this master group
					$raw_ids = $engine->get_term_ids_for_master( (int) $master->id, $taxonomy );

					if ( ! empty( $raw_ids ) ) {
						$expanded = array_merge( $expanded, $raw_ids );
						$modified = true;
					} else {
						// Master exists but has no mapped terms — keep original
						$expanded[] = $term->term_id;
					}
				} else {
					// Not a master group — check if this term itself is a mapped raw term
					// and keep it as-is (it will display as the mapped term)
					$expanded[] = $term->term_id;
				}
			}

			if ( $modified && ! empty( $expanded ) ) {
				$tax_query[ $i ]['terms'] = array_unique( array_map( 'intval', array_filter( $expanded ) ) );
				$tax_query[ $i ]['field'] = 'term_id'; // Force term_id field after expansion
			}
		}

		if ( $modified ) {
			$query->set( 'tax_query', $tax_query );
		}
	}

	/**
	 * Detects which master filter groups are currently active in the URL.
	 * Parses $_GET['filter_pa_*'] parameters.
	 *
	 * @return array<string, int> [taxonomy => master_group_id]
	 */
	public function detect_active_master_filters(): array {
		$engine  = MappingEngine::instance();
		$active  = array();

		foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! str_starts_with( $key, 'filter_' ) ) {
				continue;
			}

			$taxonomy = substr( $key, strlen( 'filter_' ) );

			if ( ! taxonomy_exists( $taxonomy ) || ! $this->is_attribute_taxonomy( $taxonomy ) ) {
				continue;
			}

			$slug   = sanitize_title( wp_unslash( $value ) );
			$master = $engine->resolve_master_by_slug( $slug, $taxonomy );

			if ( $master ) {
				$active[ $taxonomy ] = (int) $master->id;
			}
		}

		return $active;
	}

	/**
	 * Returns term_ids for a master group's mapped raw terms.
	 *
	 * @param int    $master_group_id
	 * @param string $taxonomy
	 * @return int[]
	 */
	public function expand_tax_query_terms( int $master_group_id, string $taxonomy ): array {
		return MappingEngine::instance()->get_term_ids_for_master( $master_group_id, $taxonomy );
	}

	/**
	 * Returns whether the "hide unmapped values" setting is enabled.
	 */
	public function should_hide_unmapped(): bool {
		$settings = get_option( 'attributehub_settings', array() );
		return ! empty( $settings['hide_unmapped'] );
	}

	/**
	 * Checks if a taxonomy is a WooCommerce attribute taxonomy (pa_* prefix).
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	private function is_attribute_taxonomy( string $taxonomy ): bool {
		return str_starts_with( $taxonomy, 'pa_' );
	}
}
