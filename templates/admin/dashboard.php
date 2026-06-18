<?php
/**
 * AttributeHub — Dashboard Template (Premium UI)
 *
 * @var array  $stats      Per-taxonomy stats rows.
 * @var array  $totals     Site-wide totals.
 * @var array  $taxonomies All WC attribute taxonomies.
 */
defined( 'ABSPATH' ) || exit;

$is_pro = attributehub()->is_pro();
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">🏷️</div>
			<div>
				<div class="ah-page-title">
					<?php esc_html_e( 'AttributeHub', 'attributehub-for-woocommerce' ); ?>
					<span class="ah-version-chip">v<?php echo esc_html( ATTRIBUTEHUB_VERSION ); ?></span>
				</div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Clean attribute labels for professional WooCommerce stores', 'attributehub-for-woocommerce' ); ?></div>
			</div>
		</div>
		<div class="ah-header-actions">
			<?php if ( ! $is_pro ) : ?>
			<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-header" target="_blank">
				&#11088; <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( empty( $taxonomies ) ) : ?>
	<div class="ah-card">
		<div class="ah-card-body">
			<div class="ah-empty-state">
				<span class="ah-empty-icon dashicons dashicons-tag"></span>
				<p><?php esc_html_e( 'No WooCommerce product attributes found. Create attributes under Products &rarr; Attributes first.', 'attributehub-for-woocommerce' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_attributes' ) ); ?>" class="ah-btn ah-btn--primary">
					<?php esc_html_e( 'Create Attributes', 'attributehub-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php else : ?>

	<!-- Stats Grid -->
	<div class="ah-stats-grid">
		<div class="ah-stat-card ah-stat-card--purple">
			<div class="ah-stat-icon ah-stat-icon--purple">&#127991;</div>
			<div class="ah-stat-number"><?php echo esc_html( $totals['taxonomies'] ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Attribute Types', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--blue">
			<div class="ah-stat-icon ah-stat-icon--blue">&#128202;</div>
			<div class="ah-stat-number"><?php echo esc_html( $totals['total_terms'] ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Total Values', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--green">
			<div class="ah-stat-icon ah-stat-icon--green">&#9989;</div>
			<div class="ah-stat-number"><?php echo esc_html( $totals['mapped_pct'] ); ?>%</div>
			<div class="ah-stat-label"><?php esc_html_e( 'Values Mapped', 'attributehub-for-woocommerce' ); ?></div>
			<div class="ah-progress">
				<div class="ah-progress-fill" style="width:<?php echo esc_attr( $totals['mapped_pct'] ); ?>%"></div>
			</div>
		</div>
		<div class="ah-stat-card ah-stat-card--orange">
			<div class="ah-stat-icon ah-stat-icon--orange">&#127919;</div>
			<div class="ah-stat-number"><?php echo esc_html( $totals['masters'] ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Master Labels', 'attributehub-for-woocommerce' ); ?></div>
		</div>
		<div class="ah-stat-card ah-stat-card--<?php echo esc_attr( $totals['unmapped'] > 0 ? 'red' : 'green' ); ?>">
			<div class="ah-stat-icon ah-stat-icon--<?php echo esc_attr( $totals['unmapped'] > 0 ? 'red' : 'green' ); ?>">
				<?php echo $totals['unmapped'] > 0 ? '&#9888;&#65039;' : '&#127881;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded HTML entities ?>
			</div>
			<div class="ah-stat-number"><?php echo esc_html( $totals['unmapped'] ); ?></div>
			<div class="ah-stat-label"><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></div>
		</div>
	</div>

	<?php do_action( 'attributehub_after_dashboard_stats', $totals ); ?>

	<!-- Quick Actions Bar -->
	<div class="ah-quick-bar">
		<button class="ah-btn ah-btn--primary ah-scan-all-btn" data-taxonomy="">
			&#128269; <?php esc_html_e( 'Scan All Attributes', 'attributehub-for-woocommerce' ); ?>
		</button>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings' ) ); ?>" class="ah-btn ah-btn--secondary">
			&#128193; <?php esc_html_e( 'Mapping Editor', 'attributehub-for-woocommerce' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-masters' ) ); ?>" class="ah-btn ah-btn--secondary">
			&#128203; <?php esc_html_e( 'Master Labels', 'attributehub-for-woocommerce' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-preview' ) ); ?>" class="ah-btn ah-btn--secondary">
			&#128065;&#65039; <?php esc_html_e( 'Preview', 'attributehub-for-woocommerce' ); ?>
		</a>
		<span class="ah-scan-status"></span>
		<div class="ah-scan-progress-wrap" style="display:none;">
			<div class="ah-scan-progress-track">
				<div class="ah-scan-progress-bar"></div>
			</div>
			<span class="ah-scan-progress-text"><?php esc_html_e( 'Scanning&hellip;', 'attributehub-for-woocommerce' ); ?></span>
		</div>
	</div>

	<!-- Per-Taxonomy Table -->
	<div class="ah-table-wrap">
		<table class="ah-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Attribute', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Total Values', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Mapped', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Masters', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Coverage', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Last Scan', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'attributehub-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $stats as $row ) : ?>
				<tr>
					<td>
						<div class="ah-cell-primary"><?php echo esc_html( $row['label'] ); ?></div>
						<code><?php echo esc_html( $row['taxonomy'] ); ?></code>
					</td>
					<td><?php echo esc_html( $row['total_terms'] ); ?></td>
					<td><span class="ah-badge ah-badge--success"><?php echo esc_html( $row['mapped'] ); ?></span></td>
					<td>
						<?php if ( $row['unmapped'] > 0 ) : ?>
						<span class="ah-badge ah-badge--warning"><?php echo esc_html( $row['unmapped'] ); ?></span>
						<?php else : ?>
						<span class="ah-badge ah-badge--success">0</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $row['masters'] ); ?></td>
					<td style="min-width:130px;">
						<div style="display:flex;align-items:center;gap:8px;">
							<div class="ah-progress ah-progress--sm" style="flex:1;margin-top:0;">
								<div class="ah-progress-fill" style="width:<?php echo esc_attr( $row['mapped_pct'] ); ?>%"></div>
							</div>
							<span class="ah-text-sm ah-muted"><?php echo esc_html( $row['mapped_pct'] ); ?>%</span>
						</div>
					</td>
					<td class="ah-text-sm ah-muted">
						<?php if ( $row['last_scan'] ) : ?>
							<?php echo esc_html( human_time_diff( strtotime( $row['last_scan'] ), time() ) . ' ' . __( 'ago', 'attributehub-for-woocommerce' ) ); ?>
						<?php else : ?>
							<em><?php esc_html_e( 'Never', 'attributehub-for-woocommerce' ); ?></em>
						<?php endif; ?>
					</td>
					<td style="white-space:nowrap;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-scanner&taxonomy=' . $row['taxonomy'] ) ); ?>"
						   class="ah-btn ah-btn--secondary ah-btn--sm">
							<?php esc_html_e( 'Scan', 'attributehub-for-woocommerce' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $row['taxonomy'] ) ); ?>"
						   class="ah-btn ah-btn--secondary ah-btn--sm" style="margin-left:4px;">
							<?php esc_html_e( 'Map', 'attributehub-for-woocommerce' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php endif; ?>
</div>
