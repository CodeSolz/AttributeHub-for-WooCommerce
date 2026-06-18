<?php
/**
 * AttributeHub — Preview Template (Premium UI)
 *
 * @var array  $taxonomies  All WC attribute taxonomies.
 * @var string $active_tax  Currently selected taxonomy.
 * @var array  $before      Raw WC terms (before mapping).
 * @var array  $after       Consolidated master terms (after mapping).
 * @var array  $coverage    ['mapped_pct','total','mapped','unmapped']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">&#128065;&#65039;</div>
			<div>
				<div class="ah-page-title"><?php esc_html_e( 'Filter Preview', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Compare your raw attribute values with the clean labels your customers will see in filters.', 'attributehub-for-woocommerce' ); ?></div>
			</div>
		</div>
		<?php if ( ! attributehub()->is_pro() ) : ?>
		<div class="ah-header-actions">
			<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-header" target="_blank">
				&#11088; <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>

	<!-- Taxonomy Tabs -->
	<nav class="ah-taxonomy-nav">
		<?php foreach ( $taxonomies as $tax ) :
			$tax_obj = get_taxonomy( $tax );
			$label   = $tax_obj ? $tax_obj->labels->name : $tax;
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-preview&taxonomy=' . $tax ) ); ?>"
		   class="<?php echo $tax === $active_tax ? 'active' : ''; ?>">
			<?php echo esc_html( $label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<!-- Coverage Stats -->
	<?php if ( ! empty( $coverage ) ) : ?>
	<div class="ah-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
		<div class="ah-stat-card ah-stat-card--blue">
			<div class="ah-stat-icon ah-stat-icon--blue">&#128202;</div>
			<div class="ah-stat-number"><?php echo esc_html( $coverage['total'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Raw Values', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--green">
			<div class="ah-stat-icon ah-stat-icon--green">&#9989;</div>
			<div class="ah-stat-number"><?php echo esc_html( $coverage['mapped'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Mapped', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--orange">
			<div class="ah-stat-icon ah-stat-icon--orange">&#9888;&#65039;</div>
			<div class="ah-stat-number"><?php echo esc_html( $coverage['unmapped'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--<?php echo ( $coverage['mapped_pct'] ?? 0 ) >= 100 ? 'green' : 'purple'; ?>">
			<div class="ah-stat-icon ah-stat-icon--purple">&#127919;</div>
			<div class="ah-stat-number"><?php echo esc_html( $coverage['mapped_pct'] ?? 0 ); ?>%</div>
			<div class="ah-stat-label"><?php esc_html_e( 'Coverage', 'attributehub-for-woocommerce' ); ?></div>
			<div class="ah-progress">
				<div class="ah-progress-fill" style="width:<?php echo esc_attr( $coverage['mapped_pct'] ?? 0 ); ?>%"></div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Preview Grid -->
	<div class="ah-preview-grid">

		<!-- BEFORE panel -->
		<div class="ah-card">
			<div class="ah-preview-panel-head ah-preview-panel-head--before">
				<span class="ah-preview-panel-icon">&#128683;</span>
				<div>
					<div class="ah-preview-panel-title"><?php esc_html_e( 'Before', 'attributehub-for-woocommerce' ); ?></div>
					<div class="ah-preview-panel-sub"><?php esc_html_e( 'Raw backend codes in filters', 'attributehub-for-woocommerce' ); ?></div>
				</div>
				<span class="ah-badge ah-badge--danger" style="margin-left:auto;"><?php echo count( $before ); ?></span>
			</div>
			<div class="ah-card-body" style="padding:0;">
				<?php if ( empty( $before ) ) : ?>
				<div class="ah-empty-state" style="padding:32px 24px;">
					<span class="ah-empty-icon dashicons dashicons-minus"></span>
					<p><?php esc_html_e( 'No terms found for this attribute.', 'attributehub-for-woocommerce' ); ?></p>
				</div>
				<?php else : ?>
				<ul class="ah-preview-list">
					<?php foreach ( $before as $term ) : ?>
					<li class="ah-preview-list-item ah-preview-list-item--before">
						<span class="ah-preview-term"><?php echo esc_html( $term->name ); ?></span>
						<span class="ah-preview-count"><?php echo esc_html( $term->count ); ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
		</div>

		<!-- Arrow column -->
		<div class="ah-preview-arrow-col">
			<span class="ah-preview-arrow">&#8594;</span>
		</div>

		<!-- AFTER panel -->
		<div class="ah-card">
			<div class="ah-preview-panel-head ah-preview-panel-head--after">
				<span class="ah-preview-panel-icon">&#9989;</span>
				<div>
					<div class="ah-preview-panel-title"><?php esc_html_e( 'After', 'attributehub-for-woocommerce' ); ?></div>
					<div class="ah-preview-panel-sub"><?php esc_html_e( 'Clean master labels customers see', 'attributehub-for-woocommerce' ); ?></div>
				</div>
				<span class="ah-badge ah-badge--success" style="margin-left:auto;"><?php echo count( $after ); ?></span>
			</div>
			<div class="ah-card-body" style="padding:0;">
				<?php if ( empty( $after ) ) : ?>
				<div class="ah-empty-state" style="padding:32px 24px;">
					<span class="ah-empty-icon dashicons dashicons-tag"></span>
					<p><?php esc_html_e( 'No master labels yet. Map values using the Mapping Editor.', 'attributehub-for-woocommerce' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $active_tax ) ); ?>"
					   class="ah-btn ah-btn--primary" style="margin-top:12px;">
						<?php esc_html_e( 'Open Mapping Editor', 'attributehub-for-woocommerce' ); ?>
					</a>
				</div>
				<?php else : ?>
				<ul class="ah-preview-list">
					<?php foreach ( $after as $master ) : ?>
					<li class="ah-preview-list-item ah-preview-list-item--after">
						<span class="ah-preview-term">
							<?php echo esc_html( $master->label ); ?>
							<?php if ( ! empty( $master->raw_values ) ) : ?>
							<span class="ah-preview-sources">
								<?php foreach ( $master->raw_values as $rv ) : ?>
								<code class="ah-code-chip ah-code-chip--xs"><?php echo esc_html( $rv ); ?></code>
								<?php endforeach; ?>
							</span>
							<?php endif; ?>
						</span>
						<span class="ah-preview-count"><?php echo esc_html( $master->total_count ?? 0 ); ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			</div>
		</div>

	</div><!-- .ah-preview-grid -->

	<!-- Unmapped in context -->
	<?php if ( ! empty( $coverage['unmapped'] ) && $coverage['unmapped'] > 0 ) : ?>
	<div class="ah-card" style="margin-top:16px;border-left:4px solid var(--ah-warning);">
		<div class="ah-card-body" style="display:flex;align-items:center;gap:14px;">
			<span style="font-size:22px;">&#9888;&#65039;</span>
			<div>
				<strong><?php printf(
					/* translators: %d = count */
					esc_html( _n( '%d value still unmapped', '%d values still unmapped', $coverage['unmapped'], 'attributehub-for-woocommerce' ) ),
					(int) $coverage['unmapped']
				); ?></strong>
				<br>
				<span style="font-size:13px;color:var(--ah-gray-600);"><?php esc_html_e( 'Unmapped values may appear as raw codes in customer filters.', 'attributehub-for-woocommerce' ); ?></span>
			</div>
			<div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-scanner&taxonomy=' . $active_tax ) ); ?>"
				   class="ah-btn ah-btn--secondary ah-btn--sm">
					<?php esc_html_e( 'Scan', 'attributehub-for-woocommerce' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $active_tax ) ); ?>"
				   class="ah-btn ah-btn--primary ah-btn--sm">
					<?php esc_html_e( 'Map Now', 'attributehub-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php endif; ?>

</div>
