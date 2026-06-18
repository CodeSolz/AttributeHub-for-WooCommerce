<?php
/**
 * AttributeHub — Scanner Template (Premium UI)
 *
 * @var array  $taxonomies    All WC attribute taxonomies.
 * @var string $active_tax    Currently selected taxonomy.
 * @var array  $results       Scan result rows.
 * @var array  $summary       ['total','mapped','unmapped','ugly','duplicate']
 * @var string $last_scan     Last scan timestamp.
 * @var string $latest_run_id UUID of latest scan run.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">&#128269;</div>
			<div>
				<div class="ah-page-title"><?php esc_html_e( 'Attribute Scanner', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Detect messy codes, near-duplicates, and unmapped attribute values across your catalog.', 'attributehub-for-woocommerce' ); ?></div>
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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-scanner&taxonomy=' . $tax ) ); ?>"
		   class="<?php echo esc_attr( $tax === $active_tax ? 'active' : '' ); ?>">
			<?php echo esc_html( $label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<!-- Scan Controls Card -->
	<div class="ah-card" style="margin-bottom:20px;">
		<div class="ah-card-body" style="padding:16px 20px;">
			<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
				<button class="ah-btn ah-btn--primary ah-run-scan-btn"
				        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>">
					&#128269; <?php esc_html_e( 'Run Scan Now', 'attributehub-for-woocommerce' ); ?>
				</button>

				<?php if ( $last_scan ) : ?>
				<span class="ah-last-scan-text">
					<?php printf(
						/* translators: %s = human time diff */
						esc_html__( 'Last scanned %s ago', 'attributehub-for-woocommerce' ),
						esc_html( human_time_diff( strtotime( $last_scan ), time() ) )
					); ?>
				</span>
				<?php else : ?>
				<span class="ah-muted ah-text-sm"><?php esc_html_e( 'Never scanned — run a scan to detect values.', 'attributehub-for-woocommerce' ); ?></span>
				<?php endif; ?>

				<div class="ah-scan-progress-wrap" style="display:none;">
					<div class="ah-scan-progress-track">
						<div class="ah-scan-progress-bar"></div>
					</div>
					<span class="ah-scan-progress-text"><?php esc_html_e( 'Scanning&hellip;', 'attributehub-for-woocommerce' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $results ) ) : ?>

	<!-- Summary Stats -->
	<div class="ah-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px;">
		<div class="ah-stat-card ah-stat-card--blue">
			<div class="ah-stat-icon ah-stat-icon--blue">&#128202;</div>
			<div class="ah-stat-number"><?php echo esc_html( $summary['total'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Total Values', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--green">
			<div class="ah-stat-icon ah-stat-icon--green">&#9989;</div>
			<div class="ah-stat-number"><?php echo esc_html( $summary['mapped'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Mapped', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--orange">
			<div class="ah-stat-icon ah-stat-icon--orange">&#9888;&#65039;</div>
			<div class="ah-stat-number"><?php echo esc_html( $summary['unmapped'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--red">
			<div class="ah-stat-icon ah-stat-icon--red">&#128165;</div>
			<div class="ah-stat-number"><?php echo esc_html( $summary['ugly'] ?? 0 ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Ugly Codes', 'attributehub-for-woocommerce' ); ?></div>
		</div>
	</div>

	<!-- Results Table Card -->
	<div class="ah-table-wrap">

		<!-- Filter Tabs -->
		<div class="ah-filter-tabs" style="padding:0 16px;background:#fff;border-bottom:1px solid var(--ah-gray-200);">
			<a href="#" class="ah-filter-tab active" data-filter="all">
				<?php esc_html_e( 'All', 'attributehub-for-woocommerce' ); ?>
				<span class="ah-tab-count"><?php echo esc_html( $summary['total'] ?? 0 ); ?></span>
			</a>
			<a href="#" class="ah-filter-tab" data-filter="unmapped">
				<?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?>
				<span class="ah-tab-count ah-tab-count--warning"><?php echo esc_html( $summary['unmapped'] ?? 0 ); ?></span>
			</a>
			<a href="#" class="ah-filter-tab" data-filter="duplicate">
				<?php esc_html_e( 'Duplicates', 'attributehub-for-woocommerce' ); ?>
				<span class="ah-tab-count ah-tab-count--warning"><?php echo esc_html( $summary['duplicate'] ?? 0 ); ?></span>
			</a>
			<a href="#" class="ah-filter-tab" data-filter="ugly">
				<?php esc_html_e( 'Ugly Codes', 'attributehub-for-woocommerce' ); ?>
				<span class="ah-tab-count ah-tab-count--danger"><?php echo esc_html( $summary['ugly'] ?? 0 ); ?></span>
			</a>
		</div>

		<table class="ah-table">
			<thead>
				<tr>
					<th class="check-column" style="padding-left:16px;"><input type="checkbox" id="ah-select-all"></th>
					<th><?php esc_html_e( 'Raw Value', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Products', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Mapping Status', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Issue', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Action', 'attributehub-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody id="ah-scan-results">
			<?php foreach ( $results as $row ) : ?>
				<tr class="ah-result-row"
				    data-term-id="<?php echo esc_attr( $row->term_id ); ?>"
				    data-issue="<?php echo esc_attr( $row->issue_type ?? '' ); ?>"
				    data-mapped="<?php echo esc_attr( $row->is_mapped ); ?>">
					<td style="padding-left:16px;">
						<input type="checkbox" class="ah-row-check" value="<?php echo esc_attr( $row->term_id ); ?>">
					</td>
					<td>
						<span class="ah-cell-primary"><?php echo esc_html( $row->raw_value ); ?></span>
						<?php if ( $row->similar_to ) : ?>
							<br><span class="ah-text-sm ah-muted">
								<?php printf(
									/* translators: %d = term ID */
									esc_html__( 'Similar to #%d', 'attributehub-for-woocommerce' ),
									(int) $row->similar_to
								); ?>
							</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $row->product_count ); ?></td>
					<td>
						<?php if ( $row->is_mapped ) : ?>
							<span class="ah-badge ah-badge--success">&#10003; <?php esc_html_e( 'Mapped', 'attributehub-for-woocommerce' ); ?></span>
						<?php else : ?>
							<span class="ah-badge ah-badge--warning"><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( 'ugly' === $row->issue_type ) : ?>
							<span class="ah-badge ah-badge--danger"><?php esc_html_e( 'Ugly Code', 'attributehub-for-woocommerce' ); ?></span>
						<?php elseif ( 'duplicate' === $row->issue_type ) : ?>
							<span class="ah-badge ah-badge--info"><?php esc_html_e( 'Duplicate', 'attributehub-for-woocommerce' ); ?></span>
						<?php else : ?>
							<span class="ah-muted">—</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( ! $row->is_mapped ) : ?>
						<button class="ah-btn ah-btn--primary ah-btn--sm ah-map-now-btn"
						        data-term-id="<?php echo esc_attr( $row->term_id ); ?>"
						        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
						        data-raw="<?php echo esc_attr( $row->raw_value ); ?>">
							<?php esc_html_e( 'Map Now', 'attributehub-for-woocommerce' ); ?>
						</button>
						<?php else : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $active_tax ) ); ?>"
						   class="ah-btn ah-btn--secondary ah-btn--sm">
							<?php esc_html_e( 'Edit', 'attributehub-for-woocommerce' ); ?>
						</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Bulk Actions -->
		<div class="ah-bulk-bar">
			<select id="ah-bulk-master-select">
				<option value=""><?php esc_html_e( 'Select master group&hellip;', 'attributehub-for-woocommerce' ); ?></option>
			</select>
			<button class="ah-btn ah-btn--secondary ah-btn--sm ah-bulk-map-btn"
			        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>">
				<?php esc_html_e( 'Map Selected', 'attributehub-for-woocommerce' ); ?>
			</button>
			<span class="ah-muted ah-text-sm"><?php esc_html_e( 'Select rows above then choose a master group.', 'attributehub-for-woocommerce' ); ?></span>
		</div>
	</div>

	<?php else : ?>

	<div class="ah-card">
		<div class="ah-card-body">
			<div class="ah-empty-state">
				<span class="ah-empty-icon dashicons dashicons-search"></span>
				<p><?php esc_html_e( 'No scan results yet for this attribute. Click "Run Scan Now" above to detect your attribute values.', 'attributehub-for-woocommerce' ); ?></p>
			</div>
		</div>
	</div>

	<?php endif; ?>

</div>

<!-- Map Now Modal -->
<div id="ah-map-modal" class="ah-modal" style="display:none;">
	<div class="ah-modal-box">
		<div class="ah-modal-header">
			<h3><?php esc_html_e( 'Map Value to Master Label', 'attributehub-for-woocommerce' ); ?></h3>
			<button class="ah-modal-close-x" id="ah-modal-cancel" type="button">&times;</button>
		</div>
		<div class="ah-modal-body">
			<p style="margin:0 0 16px;color:var(--ah-gray-600,#475569);font-size:13px;">
				<?php esc_html_e( 'Mapping raw value:', 'attributehub-for-woocommerce' ); ?>
				<strong id="ah-modal-raw-value" style="color:var(--ah-gray-900);"></strong>
			</p>
			<label style="display:block;font-size:11px;font-weight:700;color:var(--ah-gray-500);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
				<?php esc_html_e( 'Select existing master', 'attributehub-for-woocommerce' ); ?>
			</label>
			<select id="ah-modal-master-select">
				<option value=""><?php esc_html_e( 'Choose a master label&hellip;', 'attributehub-for-woocommerce' ); ?></option>
			</select>
			<div class="ah-modal-or"><?php esc_html_e( 'OR', 'attributehub-for-woocommerce' ); ?></div>
			<label style="display:block;font-size:11px;font-weight:700;color:var(--ah-gray-500);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
				<?php esc_html_e( 'Create new master label', 'attributehub-for-woocommerce' ); ?>
			</label>
			<input type="text" id="ah-modal-new-master"
			       placeholder="<?php esc_attr_e( 'e.g. Black, Blue, Large&hellip;', 'attributehub-for-woocommerce' ); ?>">
		</div>
		<div class="ah-modal-footer">
			<button class="ah-btn ah-btn--secondary" id="ah-modal-cancel-2" type="button"><?php esc_html_e( 'Cancel', 'attributehub-for-woocommerce' ); ?></button>
			<button class="ah-btn ah-btn--primary" id="ah-modal-save" type="button"><?php esc_html_e( 'Save Mapping', 'attributehub-for-woocommerce' ); ?></button>
		</div>
	</div>
</div>
