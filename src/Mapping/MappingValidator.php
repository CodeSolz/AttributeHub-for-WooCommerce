<?php
/**
 * AttributeHub — Mapping Validator
 *
 * @package AttributeHub\Free\Mapping
 */

namespace AttributeHub\Free\Mapping;

use AttributeHub\Free\Database\MasterGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Validates master group data before saving.
 * Critical: checks for slug collisions with existing WC terms.
 */
class MappingValidator {

	/** @var MasterGroupRepository */
	private MasterGroupRepository $master_repo;

	public function __construct() {
		$this->master_repo = new MasterGroupRepository();
	}

	/**
	 * Validates that a master group slug is safe to use.
	 *
	 * Rules:
	 * 1. Must not already exist in ah_master_groups for this taxonomy.
	 * 2. Must not match an existing WC term slug in the same taxonomy
	 *    (collision would break the filter query expansion).
	 *
	 * @param string   $slug       The proposed slug.
	 * @param string   $taxonomy   The taxonomy.
	 * @param int|null $exclude_id Exclude this master group ID (for updates).
	 * @return bool True if slug is valid and safe.
	 */
	public function validate_master_slug( string $slug, string $taxonomy, ?int $exclude_id = null ): bool {
		// Check existing master groups
		if ( $this->slug_exists_in_masters( $slug, $taxonomy, $exclude_id ) ) {
			return false;
		}

		// Check collision with WC terms
		if ( $this->slug_collides_with_wc_term( $slug, $taxonomy ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if a slug already exists in ah_master_groups for this taxonomy.
	 *
	 * @param string   $slug
	 * @param string   $taxonomy
	 * @param int|null $exclude_id
	 * @return bool
	 */
	public function slug_exists_in_masters( string $slug, string $taxonomy, ?int $exclude_id = null ): bool {
		$existing = $this->master_repo->find_by_slug( $slug, $taxonomy );

		if ( ! $existing ) {
			return false;
		}

		if ( null !== $exclude_id && (int) $existing->id === $exclude_id ) {
			return false; // It's the same record we're updating — not a collision
		}

		return true;
	}

	/**
	 * Checks if a slug collides with an existing WC term slug in the taxonomy.
	 * Collision would cause infinite loop in filter query expansion.
	 *
	 * @param string $slug
	 * @param string $taxonomy
	 * @return bool True = collision exists (bad).
	 */
	public function slug_collides_with_wc_term( string $slug, string $taxonomy ): bool {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		return (bool) $term;
	}

	/**
	 * Suggests a safe slug, adding the 'ahm-' prefix if needed.
	 *
	 * @param string $slug
	 * @param string $taxonomy
	 * @return string A safe slug to use.
	 */
	public function suggest_safe_slug( string $slug, string $taxonomy ): string {
		// If slug is safe as-is, return it
		if ( $this->validate_master_slug( $slug, $taxonomy ) ) {
			return $slug;
		}

		// Try with 'ahm-' prefix
		$prefixed = 'ahm-' . $slug;
		if ( $this->validate_master_slug( $prefixed, $taxonomy ) ) {
			return $prefixed;
		}

		// Append an incrementing number
		$counter = 2;
		while ( ! $this->validate_master_slug( $slug . '-' . $counter, $taxonomy ) && $counter < 100 ) {
			$counter++;
		}

		return $slug . '-' . $counter;
	}

	/**
	 * Validates a complete master group data array before create/update.
	 *
	 * @param array  $data
	 * @param string $taxonomy
	 * @param int|null $exclude_id For updates.
	 * @return array [ 'valid' => bool, 'errors' => string[] ]
	 */
	public function validate_master_group( array $data, string $taxonomy, ?int $exclude_id = null ): array {
		$errors = array();

		if ( empty( $data['label'] ) ) {
			$errors[] = __( 'Label is required.', 'attributehub-for-woocommerce' );
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$errors[] = __( 'Invalid attribute taxonomy.', 'attributehub-for-woocommerce' );
		}

		if ( ! empty( $data['slug'] ) ) {
			$slug = sanitize_title( $data['slug'] );

			if ( $this->slug_exists_in_masters( $slug, $taxonomy, $exclude_id ) ) {
				$errors[] = __( 'A master group with this slug already exists for this attribute.', 'attributehub-for-woocommerce' );
			}

			if ( $this->slug_collides_with_wc_term( $slug, $taxonomy ) ) {
				$suggested = $this->suggest_safe_slug( $slug, $taxonomy );
				$errors[]  = sprintf(
					/* translators: 1: collision slug 2: suggested safe slug */
					__( 'Slug "%1$s" conflicts with an existing product attribute term. Try "%2$s" instead.', 'attributehub-for-woocommerce' ),
					$slug,
					$suggested
				);
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}
}
