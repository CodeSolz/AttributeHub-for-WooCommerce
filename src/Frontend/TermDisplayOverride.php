<?php
/**
 * AttributeHub — Term Display Override
 *
 * @package AttributeHub\Free\Frontend
 */

namespace AttributeHub\Free\Frontend;

use AttributeHub\Free\Mapping\MappingEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Overrides the display of WooCommerce attribute terms in:
 * 1. Layered nav widget HTML (woocommerce_layered_nav_term_html)
 * 2. Generic term_name filter (fallback for other contexts)
 */
class TermDisplayOverride {

	/**
	 * Overrides the layered nav term HTML to show master label.
	 * Hooked to woocommerce_layered_nav_term_html (filter, priority 10).
	 *
	 * @param string   $html     The original term link HTML.
	 * @param \WP_Term $term     The attribute term.
	 * @param int      $count    Product count for this term.
	 * @param string   $taxonomy The taxonomy slug.
	 * @return string Modified HTML.
	 */
	public function override_term_html( string $html, \WP_Term $term, int $count, string $taxonomy ): string {
		if ( ! $this->is_attribute_taxonomy( $taxonomy ) ) {
			return $html;
		}

		$engine       = MappingEngine::instance();
		$master_label = $engine->get_master_label( $term->term_id, $taxonomy );

		if ( null !== $master_label ) {
			// Replace the raw term name in the HTML with the master label
			// The HTML may have esc_html'd or raw name — try both
			$html = str_replace(
				esc_html( $term->name ),
				esc_html( $master_label ),
				$html
			);

			// Also replace unescaped (in case theme uses raw name)
			if ( $term->name !== esc_html( $term->name ) ) {
				$html = str_replace( $term->name, esc_html( $master_label ), $html );
			}

			// Add master group data attribute for JS targeting (analytics, etc.)
			$html = str_replace(
				'class="',
				'data-ah-master="' . esc_attr( sanitize_title( $master_label ) ) . '" class="ah-master-term ',
				$html
			);
		} elseif ( $this->should_hide_unmapped() ) {
			return ''; // Suppress unmapped terms from filter list
		} else {
			// Mark unmapped term for styling
			$html = str_replace( 'class="', 'class="ah-unmapped-term ', $html );
		}

		return $html;
	}

	/**
	 * Overrides term names in generic WordPress contexts (not just layered nav).
	 * Hooked to term_name (filter, priority 10).
	 *
	 * @param string   $name The current term name.
	 * @param \WP_Term $term The term object.
	 * @return string
	 */
	public function override_term_name( string $name, \WP_Term $term ): string {
		// Only override if setting is enabled and this is an attribute taxonomy
		$settings = get_option( 'attributehub_settings', array() );

		if ( empty( $settings['override_term_name'] ) ) {
			return $name;
		}

		if ( ! $this->is_attribute_taxonomy( $term->taxonomy ) ) {
			return $name;
		}

		// Don't override in admin context
		if ( is_admin() ) {
			return $name;
		}

		$master_label = MappingEngine::instance()->get_master_label( $term->term_id, $term->taxonomy );

		return $master_label ?? $name;
	}

	/**
	 * Checks if a taxonomy is a WooCommerce attribute taxonomy.
	 *
	 * @param string $taxonomy
	 * @return bool
	 */
	private function is_attribute_taxonomy( string $taxonomy ): bool {
		return str_starts_with( $taxonomy, 'pa_' );
	}

	/**
	 * Returns whether the "hide unmapped" setting is on.
	 */
	private function should_hide_unmapped(): bool {
		$settings = get_option( 'attributehub_settings', array() );
		return ! empty( $settings['hide_unmapped'] );
	}
}
