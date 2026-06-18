<?php
/**
 * AttributeHub — Mapping Editor Template (Premium UI)
 *
 * @var array  $taxonomies    All WC attribute taxonomies.
 * @var string $active_tax    Currently selected taxonomy.
 * @var array  $masters       Master group rows for active taxonomy.
 * @var array  $unmapped_terms Unmapped raw WC terms.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">&#128193;</div>
			<div>
				<div class="ah-page-title"><?php esc_html_e( 'Mapping Editor', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Drag raw attribute values onto master labels to create clean filter groups.', 'attributehub-for-woocommerce' ); ?></div>
			</div>
		</div>
		<div class="ah-header-actions">
			<?php if ( ! attributehub()->is_pro() ) : ?>
			<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-header" target="_blank">
				&#11088; <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
			</a>
			<?php endif; ?>
			<button class="ah-btn ah-btn--secondary ah-export-csv-btn"
			        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
			        type="button">
				&#8615; <?php esc_html_e( 'Export CSV', 'attributehub-for-woocommerce' ); ?>
			</button>
		</div>
	</div>

	<!-- Taxonomy Tabs -->
	<nav class="ah-taxonomy-nav">
		<?php foreach ( $taxonomies as $tax ) :
			$tax_obj = get_taxonomy( $tax );
			$label   = $tax_obj ? $tax_obj->labels->name : $tax;
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $tax ) ); ?>"
		   class="<?php echo esc_attr( $tax === $active_tax ? 'active' : '' ); ?>">
			<?php echo esc_html( $label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<?php if ( empty( $masters ) ) : ?>
	<div class="ah-card">
		<div class="ah-card-body">
			<div class="ah-empty-state">
				<span class="ah-empty-icon dashicons dashicons-tag"></span>
				<p><?php esc_html_e( 'No master labels yet. Create master labels first before mapping values.', 'attributehub-for-woocommerce' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-masters&taxonomy=' . $active_tax ) ); ?>"
				   class="ah-btn ah-btn--primary">
					<?php esc_html_e( 'Create Master Labels', 'attributehub-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php else : ?>

	<!-- Editor Layout -->
	<div class="ah-editor-layout">

		<!-- Left Panel: Unmapped Source Terms -->
		<div class="ah-panel">
			<div class="ah-panel-head">
				<span>&#128165; <?php esc_html_e( 'Unmapped Values', 'attributehub-for-woocommerce' ); ?></span>
				<span class="ah-panel-badge"><?php echo esc_html( count( $unmapped_terms ) ); ?></span>
			</div>
			<div class="ah-panel-body">
				<input type="text" id="ah-search-unmapped" class="ah-search-input"
				       placeholder="<?php esc_attr_e( 'Search values&hellip;', 'attributehub-for-woocommerce' ); ?>">
				<?php if ( empty( $unmapped_terms ) ) : ?>
				<div class="ah-empty-state" style="padding:32px 16px;">
					<span class="ah-empty-icon dashicons dashicons-yes-alt" style="color:var(--ah-success);font-size:32px;width:auto;"></span>
					<p style="font-size:13px;"><?php esc_html_e( 'All values are mapped.', 'attributehub-for-woocommerce' ); ?></p>
				</div>
				<?php else : ?>
				<div class="ah-chips-list" id="ah-unmapped-pool">
					<?php foreach ( $unmapped_terms as $term ) : ?>
					<div class="ah-term-chip ah-draggable"
					     draggable="true"
					     data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
					     data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
					     title="<?php echo esc_attr( sprintf( 'ID: %d — %d products', $term->term_id, $term->count ) ); ?>">
						<span class="ah-chip-label"><?php echo esc_html( $term->name ); ?></span>
						<span class="ah-chip-count"><?php echo esc_html( $term->count ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Right Panel: Master Groups -->
		<div class="ah-panel">
			<div class="ah-panel-head">
				<span>&#127919; <?php esc_html_e( 'Master Labels', 'attributehub-for-woocommerce' ); ?></span>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-masters&taxonomy=' . $active_tax ) ); ?>"
				   class="ah-btn ah-btn--secondary ah-btn--xs">
					+ <?php esc_html_e( 'Manage', 'attributehub-for-woocommerce' ); ?>
				</a>
			</div>
			<div class="ah-panel-body ah-masters-zones">
				<?php foreach ( $masters as $master ) :
					$mapped = $master->mapped_terms ?? array();
				?>
				<div class="ah-master-zone ah-master-dropzone"
				     data-master-id="<?php echo esc_attr( $master->id ); ?>">
					<div class="ah-master-zone-header">
						<span class="ah-master-zone-label"><?php echo esc_html( $master->label ); ?></span>
						<?php if ( $master->is_hidden ) : ?>
						<span class="ah-badge ah-badge--hidden" style="font-size:10px;">&#128683; <?php esc_html_e( 'Hidden', 'attributehub-for-woocommerce' ); ?></span>
						<?php endif; ?>
						<button class="ah-btn ah-btn--ghost ah-btn--xs ah-unmap-all-btn"
						        data-master-id="<?php echo esc_attr( $master->id ); ?>"
						        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
						        title="<?php esc_attr_e( 'Unmap all from this group', 'attributehub-for-woocommerce' ); ?>"
						        type="button">&#215; All</button>
					</div>
					<div class="ah-mapped-list">
						<?php if ( empty( $mapped ) ) : ?>
						<div class="ah-drop-hint"><?php esc_html_e( 'Drop values here', 'attributehub-for-woocommerce' ); ?></div>
						<?php else : ?>
						<?php foreach ( $mapped as $mt ) :
							// mapped_terms is an array of arrays from MappingEditorPage
							$mt_id    = is_array( $mt ) ? ( $mt['term_id'] ?? 0 ) : ( $mt->term_id ?? 0 );
							$mt_name  = is_array( $mt ) ? ( $mt['name'] ?? '' ) : ( $mt->raw_value ?? $mt->name ?? '' );
							$mt_count = is_array( $mt ) ? ( $mt['count'] ?? 0 ) : ( $mt->count ?? 0 );
						?>
						<div class="ah-mapped-chip"
						     data-term-id="<?php echo esc_attr( $mt_id ); ?>"
						     data-taxonomy="<?php echo esc_attr( $active_tax ); ?>">
							<span><?php echo esc_html( $mt_name ); ?></span>
							<span class="ah-chip-count"><?php echo esc_html( $mt_count ); ?></span>
							<button class="ah-unmap-btn"
							        data-term-id="<?php echo esc_attr( $mt_id ); ?>"
							        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
							        title="<?php esc_attr_e( 'Unmap', 'attributehub-for-woocommerce' ); ?>"
							        type="button">&times;</button>
						</div>
						<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div><!-- .ah-editor-layout -->

	<?php if ( ! attributehub()->is_pro() ) : ?>
	<div class="ah-card" style="margin-top:16px;border:2px dashed var(--ah-primary-light);">
		<div class="ah-card-body" style="display:flex;align-items:center;gap:16px;">
			<div style="font-size:32px;">&#128274;</div>
			<div>
				<div style="font-weight:700;color:var(--ah-gray-900);margin-bottom:4px;"><?php esc_html_e( 'Pro: Auto-Map with Pattern Rules', 'attributehub-for-woocommerce' ); ?></div>
				<div style="font-size:13px;color:var(--ah-gray-600);"><?php esc_html_e( 'Set rules like "starts with BK → Black" and new imported products auto-map instantly.', 'attributehub-for-woocommerce' ); ?></div>
			</div>
			<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn ah-btn--primary" style="margin-left:auto;white-space:nowrap;" target="_blank">
				<?php esc_html_e( 'Upgrade', 'attributehub-for-woocommerce' ); ?>
			</a>
		</div>
	</div>
	<?php endif; ?>

	<?php endif; ?>

</div>
