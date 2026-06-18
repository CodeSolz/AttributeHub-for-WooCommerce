<?php
/**
 * AttributeHub — Layered Nav Integration
 *
 * @package AttributeHub\Free\Frontend
 */

namespace AttributeHub\Free\Frontend;

use AttributeHub\Free\Mapping\MappingEngine;
use AttributeHub\Free\Database\MasterGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Main frontend integration class.
 * Registers all layered nav hooks and coordinates the display override,
 * term consolidation (collapse mapped raw terms into master rows),
 * and filter query expansion.
 */
class LayeredNavIntegration {

	/** @var LayeredNavIntegration|null */
	protected static ?LayeredNavIntegration $instance = null;

	/** @var FilterQueryModifier */
	private FilterQueryModifier $query_modifier;

	/** @var TermDisplayOverride */
	private TermDisplayOverride $display_override;

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __construct() {
		$this->query_modifier   = new FilterQueryModifier();
		$this->display_override = new TermDisplayOverride();
	}

	/**
	 * Registers all frontend hooks.
	 * Called from WooCommerceHooks when on frontend.
	 */
	public function register_hooks(): void {
		$settings = get_option( 'attributehub_settings', array() );

		if ( ! empty( $settings['override_layered_nav'] ) ) {
			// Override term HTML in layered nav
			add_filter(
				'woocommerce_layered_nav_term_html',
				array( $this->display_override, 'override_term_html' ),
				10, 4
			);

			// Consolidate: collapse mapped raw terms into master group rows
			add_filter( 'get_terms', array( $this, 'consolidate_terms_for_layered_nav' ), 10, 4 );

			// Expand product query when master filter is active
			add_action( 'woocommerce_product_query', array( $this->query_modifier, 'expand_product_query' ), 10, 2 );

			// Expand count query so product counts are correct for master groups
			add_filter(
				'woocommerce_get_filtered_term_product_counts_query',
				array( $this, 'expand_count_query' ),
				10, 1
			);
		}

		if ( ! empty( $settings['override_term_name'] ) ) {
			// Generic term name override (non-layered-nav contexts)
			add_filter( 'term_name', array( $this->display_override, 'override_term_name' ), 10, 2 );
		}

		// Allow Pro to inject secondary tags
		do_action( 'attributehub_register_frontend_hooks', $this );
	}

	/**
	 * Consolidates mapped raw terms into their master group rows in layered nav.
	 *
	 * When WC layered nav calls get_terms() for a pa_* taxonomy, this hook
	 * intercepts and replaces mapped raw terms with a single synthetic master
	 * term row (with summed product counts).
	 *
	 * @param \WP_Term[]|\WP_Error $terms
	 * @param string[]             $taxonomies
	 * @param array                $args
	 * @param \WP_Term_Query       $term_query
	 * @return \WP_Term[]|\WP_Error
	 */
	public function consolidate_terms_for_layered_nav( $terms, $taxonomies, $args, $term_query ) {
		// Only run on frontend and only for attribute taxonomies
		if ( is_admin() || is_wp_error( $terms ) || empty( $terms ) ) {
			return $terms;
		}

		// Only intercept calls from the layered nav context
		if ( ! $this->is_layered_nav_context() ) {
			return $terms;
		}

		$taxonomy = is_array( $taxonomies ) ? ( $taxonomies[0] ?? '' ) : $taxonomies;

		if ( ! str_starts_with( $taxonomy, 'pa_' ) ) {
			return $terms;
		}

		$engine      = MappingEngine::instance();
		$master_repo = new MasterGroupRepository();
		$tax_map     = $engine->get_taxonomy_map( $taxonomy );

		if ( empty( $tax_map ) ) {
			return $terms; // No mappings for this taxonomy — leave as-is
		}

		$master_rows    = array(); // [master_group_id => synthetic WP_Term]
		$unmapped_terms = array();
		$should_hide    = $this->query_modifier->should_hide_unmapped();

		foreach ( $terms as $term ) {
			if ( ! ( $term instanceof \WP_Term ) ) {
				continue;
			}

			$master_group_id = $engine->resolve_term( $term->term_id, $taxonomy );

			if ( null === $master_group_id ) {
				// Unmapped term
				if ( ! $should_hide ) {
					$unmapped_terms[] = $term;
				}
				continue;
			}

			if ( ! isset( $master_rows[ $master_group_id ] ) ) {
				$master_obj = $master_repo->find( $master_group_id );

				if ( ! $master_obj || $master_obj->is_hidden ) {
					continue;
				}

				// Create a synthetic WP_Term representing the master group
				$synthetic          = clone $term;
				$synthetic->term_id = $term->term_id; // Keeps first raw term_id (for link generation)
				$synthetic->name    = $master_obj->label;
				$synthetic->slug    = $master_obj->slug;
				$synthetic->count   = 0;

				$master_rows[ $master_group_id ] = $synthetic;
			}

			// Sum product counts across all mapped raw terms
			$master_rows[ $master_group_id ]->count += $term->count;
		}

		// Build final term list: master rows (sorted by sort_order) + unmapped
		$masters = $master_repo->find_all_by_taxonomy( $taxonomy );
		$result  = array();

		foreach ( $masters as $master_obj ) {
			if ( isset( $master_rows[ (int) $master_obj->id ] ) ) {
				$result[] = $master_rows[ (int) $master_obj->id ];
			}
		}

		// Apply Pro filter (Pro can inject secondary tag synthetic terms)
		$result = apply_filters( 'attributehub_filter_term_list', array_merge( $result, $unmapped_terms ), $taxonomy );

		return $result;
	}

	/**
	 * Modifies the WC filtered term product counts SQL to count correctly
	 * when a master group filter is active.
	 *
	 * @param array $query SQL query parts.
	 * @return array Modified query.
	 */
	public function expand_count_query( array $query ): array {
		// The count query is already modified by the tax_query expansion
		// in expand_product_query. This hook is a pass-through for now,
		// but Pro can hook here to inject secondary tag count adjustments.
		return apply_filters( 'attributehub_expand_count_query', $query );
	}

	/**
	 * Detects whether the current get_terms() call originates from WC layered nav.
	 * Uses the call stack to check for WC_Widget_Layered_Nav.
	 *
	 * @return bool
	 */
	private function is_layered_nav_context(): bool {
		// Check if this is a shop/archive page
		if ( ! ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) ) {
			return false;
		}

		// Check backtrace for WC layered nav widget (avoids modifying admin/other contexts)
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

		foreach ( $backtrace as $frame ) {
			$class = $frame['class'] ?? '';
			if ( in_array( $class, array( 'WC_Widget_Layered_Nav', 'Automattic\\WooCommerce\\Blocks\\BlockTypes\\ProductFilterAttribute' ), true ) ) {
				return true;
			}
		}

		return false;
	}
}
