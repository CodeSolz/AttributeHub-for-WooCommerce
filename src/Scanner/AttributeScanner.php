<?php
/**
 * AttributeHub — Attribute Scanner
 *
 * @package AttributeHub\Free\Scanner
 */

namespace AttributeHub\Free\Scanner;

use AttributeHub\Free\Database\ScanResultRepository;
use AttributeHub\Free\Database\ValueMappingRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Scans WooCommerce attribute taxonomies to detect ugly, duplicate, and unmapped terms.
 */
class AttributeScanner {

	/** @var AttributeScanner|null */
	protected static ?AttributeScanner $instance = null;

	/** @var ScanResultRepository */
	private ScanResultRepository $scan_repo;

	/** @var ValueMappingRepository */
	private ValueMappingRepository $mapping_repo;

	/** @var DuplicateDetector */
	private DuplicateDetector $duplicate_detector;

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __construct() {
		$this->scan_repo          = new ScanResultRepository();
		$this->mapping_repo       = new ValueMappingRepository();
		$this->duplicate_detector = new DuplicateDetector();
	}

	/**
	 * Runs a full scan (or single-taxonomy scan) and stores results.
	 *
	 * @param string $taxonomy Leave empty to scan all WC attribute taxonomies.
	 * @return string The scan_run_id UUID.
	 */
	public function run_scan( string $taxonomy = '' ): string {
		$scan_run_id = wp_generate_uuid4();
		$taxonomies  = $taxonomy ? array( sanitize_key( $taxonomy ) ) : $this->get_all_attribute_taxonomies();
		$threshold   = $this->get_ugliness_threshold();

		foreach ( $taxonomies as $tax ) {
			$terms   = $this->get_attribute_terms( $tax );
			$results = array();

			// Build set of mapped term_ids for this taxonomy
			$term_map = $this->mapping_repo->get_full_taxonomy_map( $tax );

			// Detect duplicates
			$duplicate_groups = $this->duplicate_detector->detect_duplicates( $terms );
			$duplicate_map    = array(); // term_id => canonical term_id

			foreach ( $duplicate_groups as $group ) {
				$canonical = $group[0]; // First in group = canonical
				foreach ( array_slice( $group, 1 ) as $dupe ) {
					$duplicate_map[ $dupe['term_id'] ] = $canonical['term_id'];
				}
			}

			foreach ( $terms as $term ) {
				$term_id       = (int) $term['term_id'];
				$raw_value     = $term['slug'];
				$is_mapped     = isset( $term_map[ $term_id ] );
				$ugliness      = $this->ugliness_score( $raw_value );
				$is_duplicate  = isset( $duplicate_map[ $term_id ] );

				// Determine issue type
				$issue_type = null;
				if ( $is_duplicate ) {
					$issue_type = 'duplicate';
				} elseif ( ! $is_mapped && $ugliness >= $threshold ) {
					$issue_type = 'ugly';
				} elseif ( ! $is_mapped ) {
					$issue_type = 'unmapped';
				}

				$results[] = array(
					'term_id'       => $term_id,
					'raw_value'     => $raw_value,
					'product_count' => (int) $term['count'],
					'is_mapped'     => $is_mapped,
					'issue_type'    => $issue_type,
					'similar_to'    => $duplicate_map[ $term_id ] ?? null,
				);
			}

			$this->scan_repo->store_results( $scan_run_id, $tax, $results );
		}

		/**
		 * Fires after a scan completes.
		 *
		 * @param string $scan_run_id
		 * @param string $taxonomy Empty = all.
		 * @param int    $total_terms Total terms scanned across all taxonomies.
		 */
		do_action( 'attributehub_scan_completed', $scan_run_id, $taxonomy, count( $taxonomies ) );

		return $scan_run_id;
	}

	/**
	 * Returns all WooCommerce attribute taxonomies (pa_* only).
	 *
	 * @return string[] Array of taxonomy slugs.
	 */
	public function get_all_attribute_taxonomies(): array {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$slugs                = array();

		foreach ( $attribute_taxonomies as $attribute ) {
			$slugs[] = wc_attribute_taxonomy_name( $attribute->attribute_name );
		}

		return apply_filters( 'attributehub_scannable_taxonomies', $slugs );
	}

	/**
	 * Gets all terms for a WC attribute taxonomy with product counts.
	 *
	 * @param string $taxonomy
	 * @return array[] Each item: ['term_id', 'name', 'slug', 'count']
	 */
	public function get_attribute_terms( string $taxonomy ): array {
		$terms = get_terms( array(
			'taxonomy'   => sanitize_key( $taxonomy ),
			'hide_empty' => false,
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		return array_map( function ( \WP_Term $term ) {
			return array(
				'term_id' => $term->term_id,
				'name'    => $term->name,
				'slug'    => $term->slug,
				'count'   => $term->count,
			);
		}, $terms );
	}

	/**
	 * Calculates an "ugliness score" for a raw attribute value.
	 * Higher = more likely to be an internal/supplier code.
	 *
	 * Score breakdown (max ~100):
	 * - All uppercase: +30
	 * - No vowels (consonant-only abbreviation): +20
	 * - Length ≤ 3: +25
	 * - Starts with number: +15
	 * - Contains only letters + digits + basic punctuation: -10 (looks intentional)
	 * - Contains underscore or hyphen: +5
	 *
	 * @param string $value The raw attribute value (usually the term slug).
	 * @return int Score from 0 to ~100.
	 */
	public function ugliness_score( string $value ): int {
		// Decode slug (hyphens replace spaces in WC slugs)
		$decoded = str_replace( '-', ' ', $value );
		$decoded = str_replace( '_', ' ', $decoded );
		$trimmed = trim( $decoded );

		if ( empty( $trimmed ) ) {
			return 0;
		}

		$score = 0;

		// All uppercase
		if ( ctype_upper( str_replace( array( ' ', '-', '_', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), '', $trimmed ) ) ) {
			$score += 30;
		}

		// No vowels in the alphabetic portion
		$alpha_only = preg_replace( '/[^a-zA-Z]/', '', $trimmed );
		if ( strlen( $alpha_only ) > 0 && ! preg_match( '/[aeiouAEIOU]/', $alpha_only ) ) {
			$score += 20;
		}

		// Very short (≤ 3 chars total)
		if ( strlen( $trimmed ) <= 3 ) {
			$score += 25;
		}

		// Starts with a number
		if ( preg_match( '/^\d/', $trimmed ) ) {
			$score += 15;
		}

		// Contains underscore (internal code convention)
		if ( str_contains( $value, '_' ) ) {
			$score += 5;
		}

		// Looks like a product code (letters+digits only)
		if ( preg_match( '/^[A-Z0-9\-_]+$/', $value ) ) {
			$score += 5;
		}

		return apply_filters( 'attributehub_ugliness_score', min( 100, $score ), $value );
	}

	/**
	 * Gets the configured ugliness score threshold.
	 * Terms scoring at or above this value are flagged as "ugly".
	 */
	private function get_ugliness_threshold(): int {
		$settings = get_option( 'attributehub_settings', array() );
		return (int) apply_filters( 'attributehub_ugliness_threshold', absint( $settings['ugliness_threshold'] ?? 40 ) );
	}
}
