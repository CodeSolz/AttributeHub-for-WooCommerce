<?php
/**
 * AttributeHub — Product Metabox
 *
 * @package AttributeHub\Free\Admin
 */

namespace AttributeHub\Free\Admin;

use AttributeHub\Free\Database\ValueMappingRepository;
use AttributeHub\Free\Database\MasterGroupRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Adds an AttributeHub metabox to the WooCommerce product edit screen.
 * Shows the mapping status of the product's attribute terms (read-only).
 */
class ProductMetabox {

	/**
	 * Registers the metabox. Hooked to add_meta_boxes.
	 */
	public function register(): void {
		add_meta_box(
			'attributehub-product-mapping',
			__( 'AttributeHub — Attribute Mapping', 'attributehub-for-woocommerce' ),
			array( $this, 'render' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Renders the product metabox content.
	 *
	 * @param \WP_Post $post The product post object.
	 */
	public function render( \WP_Post $post ): void {
		$product = wc_get_product( $post->ID );

		if ( ! $product ) {
			return;
		}

		$attributes   = $product->get_attributes();
		$mapping_repo = new ValueMappingRepository();
		$master_repo  = new MasterGroupRepository();

		// Build a grouped structure keyed by taxonomy, matching what the template expects:
		// each group: [ 'taxonomy_label', 'terms' => WP_Term[], 'mapped' => [term_id => label] ]
		$mapping_data = array();

		foreach ( $attributes as $taxonomy => $attribute ) {
			if ( ! $attribute->is_taxonomy() ) {
				continue;
			}

			$term_ids = $attribute->get_term_ids();
			$tax_obj  = get_taxonomy( $taxonomy );
			$terms    = array();
			$mapped   = array();

			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id, $taxonomy );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}

				$mapping = $mapping_repo->get_master_for_term( $term_id, $taxonomy );
				$master  = $mapping ? $master_repo->find( (int) $mapping->master_group_id ) : null;

				$terms[] = $term;

				if ( $master ) {
					$mapped[ $term_id ] = $master->label;
				}
			}

			if ( ! empty( $terms ) ) {
				$mapping_data[] = array(
					'taxonomy_label' => $tax_obj ? $tax_obj->labels->singular_name : $taxonomy,
					'terms'          => $terms,
					'mapped'         => $mapped,
				);
			}
		}

		\AttributeHub\Free\Util\TemplateLoader::load(
			'metabox/product-mapping-visibility.php',
			array(
				'mapping_data' => $mapping_data,
				'product'      => $product,
			)
		);
	}
}
