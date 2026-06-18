<?php
/**
 * AttributeHub — Duplicate Detector
 *
 * @package AttributeHub\Free\Scanner
 */

namespace AttributeHub\Free\Scanner;

defined( 'ABSPATH' ) || exit;

/**
 * Detects near-duplicate attribute values using Levenshtein distance
 * and canonical form normalization.
 */
class DuplicateDetector {

	/**
	 * Detects groups of near-duplicate terms.
	 *
	 * Groups terms where:
	 * - Their canonical forms are identical, OR
	 * - Their Levenshtein distance is ≤ threshold
	 *
	 * @param array  $terms     Array of ['term_id', 'name', 'slug', 'count'].
	 * @param int    $threshold Levenshtein distance threshold.
	 * @return array[] Groups of similar terms. Each group is an array of term arrays.
	 */
	public function detect_duplicates( array $terms, int $threshold = 0 ): array {
		$threshold = apply_filters( 'attributehub_duplicate_levenshtein_threshold', $threshold > 0 ? $threshold : 2 );

		if ( empty( $terms ) ) {
			return array();
		}

		$groups    = array(); // Groups of duplicates
		$assigned  = array(); // term_ids already assigned to a group

		// First pass: group by canonical form (exact normalization match)
		$canonical_groups = array();
		foreach ( $terms as $term ) {
			$canonical = $this->normalize( $term['slug'] );
			$canonical_groups[ $canonical ][] = $term;
		}

		foreach ( $canonical_groups as $canonical => $group ) {
			if ( count( $group ) > 1 ) {
				// Sort by product count descending (highest count = canonical)
				usort( $group, fn( $a, $b ) => $b['count'] <=> $a['count'] );
				$groups[] = $group;
				foreach ( $group as $t ) {
					$assigned[ $t['term_id'] ] = true;
				}
			}
		}

		// Second pass: Levenshtein distance grouping on unassigned terms
		$unassigned = array_values( array_filter( $terms, fn( $t ) => ! isset( $assigned[ $t['term_id'] ] ) ) );

		for ( $i = 0; $i < count( $unassigned ); $i++ ) {
			if ( isset( $assigned[ $unassigned[ $i ]['term_id'] ] ) ) {
				continue;
			}

			$group = array( $unassigned[ $i ] );
			$assigned[ $unassigned[ $i ]['term_id'] ] = true;

			for ( $j = $i + 1; $j < count( $unassigned ); $j++ ) {
				if ( isset( $assigned[ $unassigned[ $j ]['term_id'] ] ) ) {
					continue;
				}

				$distance = levenshtein(
					$this->normalize( $unassigned[ $i ]['slug'] ),
					$this->normalize( $unassigned[ $j ]['slug'] )
				);

				if ( $distance <= $threshold ) {
					$group[] = $unassigned[ $j ];
					$assigned[ $unassigned[ $j ]['term_id'] ] = true;
				}
			}

			if ( count( $group ) > 1 ) {
				usort( $group, fn( $a, $b ) => $b['count'] <=> $a['count'] );
				$groups[] = $group;
			}
		}

		return $groups;
	}

	/**
	 * Normalizes a raw value for comparison.
	 *
	 * Steps:
	 * 1. Lowercase
	 * 2. Strip leading numbers (e.g. "14kgd" → "kgd", "1gold" → "gold")
	 * 3. Remove hyphens and underscores
	 * 4. Trim whitespace
	 *
	 * @param string $value
	 * @return string
	 */
	public function normalize( string $value ): string {
		$v = strtolower( $value );
		$v = preg_replace( '/^\d+/', '', $v );       // strip leading numbers
		$v = preg_replace( '/[-_\s]/', '', $v );     // strip hyphens, underscores, spaces
		return trim( $v );
	}

	/**
	 * Returns the Levenshtein distance between two normalized values.
	 *
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	public function distance( string $a, string $b ): int {
		return levenshtein( $this->normalize( $a ), $this->normalize( $b ) );
	}
}
