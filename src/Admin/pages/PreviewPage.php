<?php
/**
 * AttributeHub — Preview Page
 *
 * @package AttributeHub\Free\Admin\pages
 */

namespace AttributeHub\Free\Admin\pages;

use AttributeHub\Free\Scanner\AttributeScanner;
use AttributeHub\Free\Mapping\MappingEngine;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the before/after filter preview admin page.
 */
class PreviewPage {

	public static function render(): void {
		$page = new self();
		$page->output();
	}

	public function output(): void {
		$scanner    = AttributeScanner::instance();
		$taxonomies = $scanner->get_all_attribute_taxonomies();

		$taxonomy   = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		$active_tax = in_array( $taxonomy, $taxonomies, true ) ? $taxonomy : ( $taxonomies[0] ?? '' );

		$preview_data = $active_tax ? $this->build_preview( $active_tax ) : array();
		$split        = $this->split_preview( $preview_data );

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/preview.php',
			array(
				'taxonomies' => $taxonomies,
				'active_tax' => $active_tax,
				'before'     => $split['before'],
				'after'      => $split['after'],
				'coverage'   => $split['coverage'],
			)
		);
	}

	/**
	 * Splits flat preview_data into before/after/coverage arrays for the template.
	 */
	private function split_preview( array $rows ): array {
		$before   = array();
		$after    = array();  // keyed by master_label
		$mapped   = 0;
		$unmapped = 0;

		foreach ( $rows as $row ) {
			// Before: every visible term
			if ( 'hidden' !== $row['status'] ) {
				$before[] = (object) array(
					'name'  => $row['raw_name'],
					'slug'  => $row['raw_slug'],
					'count' => $row['count'],
				);
			}

			if ( 'mapped' === $row['status'] ) {
				$mapped++;
				$label = $row['master_label'];
				if ( ! isset( $after[ $label ] ) ) {
					$after[ $label ] = (object) array(
						'label'       => $label,
						'total_count' => 0,
						'raw_values'  => array(),
					);
				}
				$after[ $label ]->total_count += (int) $row['count'];
				$after[ $label ]->raw_values[] = $row['raw_name'];
			} else {
				$unmapped++;
				// Unmapped terms appear in after as-is
				$after[ $row['raw_name'] ] = (object) array(
					'label'       => $row['raw_name'],
					'total_count' => (int) $row['count'],
					'raw_values'  => array(),
				);
			}
		}

		$total      = count( $rows );
		$mapped_pct = $total > 0 ? (int) round( $mapped / $total * 100 ) : 0;

		return array(
			'before'   => $before,
			'after'    => array_values( $after ),
			'coverage' => array(
				'total'      => $total,
				'mapped'     => $mapped,
				'unmapped'   => $unmapped,
				'mapped_pct' => $mapped_pct,
			),
		);
	}

	/**
	 * Builds preview data for a taxonomy.
	 *
	 * Returns array of rows: each row has raw term info + master mapping info.
	 * Used to render before/after comparison table.
	 */
	private function build_preview( string $taxonomy ): array {
		$engine = MappingEngine::instance();
		$terms  = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$settings    = get_option( 'attributehub_settings', array() );
		$hide_unmapped = ! empty( $settings['hide_unmapped'] );

		$rows = array();

		foreach ( $terms as $term ) {
			$master_label = $engine->get_master_label( $term->term_id, $taxonomy );
			$is_mapped    = null !== $master_label;

			if ( ! $is_mapped && $hide_unmapped ) {
				$status = 'hidden';
			} elseif ( $is_mapped ) {
				$status = 'mapped';
			} else {
				$status = 'unmapped';
			}

			$rows[] = array(
				'term_id'      => $term->term_id,
				'raw_name'     => $term->name,
				'raw_slug'     => $term->slug,
				'count'        => $term->count,
				'master_label' => $master_label,
				'status'       => $status,
			);
		}

		// Sort: mapped first, then unmapped, then hidden
		usort( $rows, function( $a, $b ) {
			$order = array( 'mapped' => 0, 'unmapped' => 1, 'hidden' => 2 );
			return ( $order[ $a['status'] ] ?? 3 ) <=> ( $order[ $b['status'] ] ?? 3 );
		} );

		return $rows;
	}
}
