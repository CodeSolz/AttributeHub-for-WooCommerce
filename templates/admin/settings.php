<?php
/**
 * AttributeHub — Settings Template (Premium UI)
 *
 * @var array  $settings  Current option values.
 * @var array  $tabs      Tab definitions [slug => label].
 * @var string $active    Active tab slug.
 */
defined( 'ABSPATH' ) || exit;

$is_pro = attributehub()->is_pro();
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">⚙️</div>
			<div>
				<div class="ah-page-title">
					<?php esc_html_e( 'AttributeHub', 'attributehub-for-woocommerce' ); ?>
					<span class="ah-version-chip">v<?php echo esc_html( ATTRIBUTEHUB_VERSION ); ?></span>
				</div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Settings &amp; Preferences', 'attributehub-for-woocommerce' ); ?></div>
			</div>
		</div>
		<?php if ( ! $is_pro ) : ?>
		<div class="ah-header-actions">
			<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-header" target="_blank">
				⭐ <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>

	<!-- Success / Error Notices -->
	<?php settings_errors( 'attributehub_settings' ); ?>

	<!-- Settings Nav (pill tabs) -->
	<nav class="ah-settings-nav">
		<?php
		$icons = apply_filters( 'attributehub_settings_tab_icons', array(
			'general' => '🔧',
			'display' => '🎨',
			'scanner' => '🔍',
			'pro'     => '⭐',
		) );
		foreach ( $tabs as $slug => $label ) :
			$icon = $icons[ $slug ] ?? '⚙️';
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'attributehub-settings', 'tab' => $slug ), admin_url( 'admin.php' ) ) ); ?>"
		   class="<?php echo esc_attr( $slug === $active ? 'ah-tab-active' : '' ); ?>">
			<?php echo esc_html( $icon . ' ' . $label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<!-- Page Body -->
	<div class="ah-page-body">

		<!-- Main Content -->
		<div class="ah-page-main">

			<form method="post" action="">
				<?php wp_nonce_field( 'attributehub_save_settings', 'attributehub_settings_nonce' ); ?>
				<input type="hidden" name="ah_active_tab" value="<?php echo esc_attr( $active ); ?>">

				<?php if ( 'general' === $active ) : ?>

				<!-- General Settings -->
				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--purple">🔗</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Filter Integration', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Control how AttributeHub interacts with WooCommerce layered nav filters.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body">

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Override Layered Nav Filters', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Collapse all raw mapped terms into one master label row in the WooCommerce filter sidebar. Counts are summed across all mapped raw values.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<label class="ah-toggle">
									<input type="hidden" name="attributehub_settings[override_layered_nav]" value="0">
									<input type="checkbox" id="ah-override-layered-nav" name="attributehub_settings[override_layered_nav]" value="1"
									       <?php checked( $settings['override_layered_nav'] ?? true ); ?>>
									<span class="ah-toggle-track"></span>
									<span class="ah-toggle-thumb"></span>
								</label>
							</div>
						</div>

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Override Term Names Site-Wide', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Replace raw attribute term names with master labels everywhere — product pages, breadcrumbs, widget titles — not just in layered nav.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<label class="ah-toggle">
									<input type="hidden" name="attributehub_settings[override_term_name]" value="0">
									<input type="checkbox" id="ah-override-term-name" name="attributehub_settings[override_term_name]" value="1"
									       <?php checked( $settings['override_term_name'] ?? true ); ?>>
									<span class="ah-toggle-track"></span>
									<span class="ah-toggle-thumb"></span>
								</label>
							</div>
						</div>

					</div>
				</div>

				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--orange">👁️</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Unmapped Values', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Decide what happens to attribute values that have not yet been mapped.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body">

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Hide Unmapped Values from Filters', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Suppress raw attribute values that have no master label from appearing in the frontend filter sidebar. Useful for hiding supplier codes mid-migration. Does not affect admin views.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<label class="ah-toggle">
									<input type="hidden" name="attributehub_settings[hide_unmapped]" value="0">
									<input type="checkbox" id="ah-hide-unmapped" name="attributehub_settings[hide_unmapped]" value="1"
									       <?php checked( $settings['hide_unmapped'] ?? false ); ?>>
									<span class="ah-toggle-track"></span>
									<span class="ah-toggle-thumb"></span>
								</label>
							</div>
						</div>

					</div>
				</div>

				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--red">🗑️</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Data &amp; Privacy', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Control what happens to your mapping data when the plugin is removed.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body">

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label">
									<?php esc_html_e( 'Delete All Data on Uninstall', 'attributehub-for-woocommerce' ); ?>
								</div>
								<p class="ah-option-hint ah-danger-text">
									⚠️ <?php esc_html_e( 'When enabled, deleting this plugin from WordPress will permanently drop all AttributeHub database tables and remove all stored options. Your WooCommerce attribute data is never affected. Leave off to preserve mappings across reinstallation.', 'attributehub-for-woocommerce' ); ?>
								</p>
							</div>
							<div class="ah-option-control">
								<label class="ah-toggle">
									<input type="hidden" name="attributehub_settings[delete_data_on_uninstall]" value="0">
									<input type="checkbox" id="ah-delete-on-uninstall" name="attributehub_settings[delete_data_on_uninstall]" value="1"
									       <?php checked( $settings['delete_data_on_uninstall'] ?? false ); ?>>
									<span class="ah-toggle-track"></span>
									<span class="ah-toggle-thumb"></span>
								</label>
							</div>
						</div>

					</div>
				</div>

				<?php elseif ( 'display' === $active ) : ?>

				<!-- Display Settings -->
				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--blue">🎨</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Filter Display', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Control how attribute values appear in the customer-facing filter sidebar.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body">
						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Cache TTL', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'How long mapping data is cached in WordPress transients. Reducing this means filters update sooner after you edit mappings, at the cost of more database queries.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<div class="ah-field-group ah-input-md">
									<input type="number" name="attributehub_settings[cache_ttl]"
									       value="<?php echo esc_attr( $settings['cache_ttl'] ?? 86400 ); ?>"
									       min="300" max="604800" step="300">
									<span class="ah-unit"><?php esc_html_e( 'seconds', 'attributehub-for-woocommerce' ); ?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="ah-card-footer">
						<button class="ah-btn ah-btn--secondary ah-btn--sm" id="ah-flush-cache-btn" type="button">
							🗑️ <?php esc_html_e( 'Flush Cache Now', 'attributehub-for-woocommerce' ); ?>
						</button>
						<span class="ah-muted ah-text-sm"><?php esc_html_e( 'Forces all frontend filters to rebuild from the database on next page load.', 'attributehub-for-woocommerce' ); ?></span>
					</div>
				</div>

				<?php if ( ! $is_pro ) : ?>
				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--purple">⭐</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Pro Display Features', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Available in AttributeHub Pro.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body" style="opacity:.7; pointer-events:none;">
						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label">🔒 <?php esc_html_e( 'Smart Grouping / Hierarchical Filters', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Group master labels into parent categories (e.g. Gold → 14K Gold, Gold Plated, Yellow Gold).', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control"><label class="ah-toggle"><input type="checkbox" disabled><span class="ah-toggle-track"></span><span class="ah-toggle-thumb"></span></label></div>
						</div>
						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label">🔒 <?php esc_html_e( 'Secondary Tags in Filters', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Make products appear in additional filter categories without changing WooCommerce attribute data.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control"><label class="ah-toggle"><input type="checkbox" disabled><span class="ah-toggle-track"></span><span class="ah-toggle-thumb"></span></label></div>
						</div>
					</div>
					<div class="ah-card-footer">
						<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn ah-btn--primary ah-btn--sm" target="_blank">
							⭐ <?php esc_html_e( 'Unlock Pro Features', 'attributehub-for-woocommerce' ); ?>
						</a>
					</div>
				</div>
				<?php endif; ?>

				<?php elseif ( 'scanner' === $active ) : ?>

				<!-- Scanner Settings -->
				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--orange">🔍</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Ugliness Detection', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Configure how the scanner scores and flags messy attribute codes.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body">

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Ugliness Score Threshold', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint">
									<?php esc_html_e( 'Values with a score at or above this number are flagged as "ugly codes". Score factors: ALL-CAPS (+30), no vowels (+20), very short length (+25), starts with number (+15), underscores (+5). Higher = stricter.', 'attributehub-for-woocommerce' ); ?>
								</p>
							</div>
							<div class="ah-option-control">
								<div class="ah-field-group ah-input-sm">
									<input type="number" name="attributehub_settings[ugliness_threshold]"
									       value="<?php echo esc_attr( $settings['ugliness_threshold'] ?? 40 ); ?>"
									       min="0" max="100" step="5">
									<span class="ah-unit">/ 100</span>
								</div>
							</div>
						</div>

						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label"><?php esc_html_e( 'Duplicate Detection Distance', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Maximum Levenshtein edit distance for two values to be grouped as near-duplicates (BK / BLK / BCK). 1 = very strict, 5 = loose. Default: 2.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<div class="ah-field-group ah-input-sm">
									<input type="number" name="attributehub_settings[duplicate_threshold]"
									       value="<?php echo esc_attr( $settings['duplicate_threshold'] ?? 2 ); ?>"
									       min="1" max="5" step="1">
									<span class="ah-unit"><?php esc_html_e( 'edits', 'attributehub-for-woocommerce' ); ?></span>
								</div>
							</div>
						</div>

					</div>
				</div>

				<?php if ( ! $is_pro ) : ?>
				<div class="ah-card">
					<div class="ah-card-header">
						<div class="ah-card-icon ah-card-icon--purple">⭐</div>
						<div class="ah-card-titles">
							<div class="ah-card-title"><?php esc_html_e( 'Pro Scanner Features', 'attributehub-for-woocommerce' ); ?></div>
							<div class="ah-card-desc"><?php esc_html_e( 'Automate your scanning and mapping workflow with Pro.', 'attributehub-for-woocommerce' ); ?></div>
						</div>
					</div>
					<div class="ah-card-body" style="opacity:.7; pointer-events:none;">
						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label">🔒 <?php esc_html_e( 'Scheduled Scans', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Run daily or weekly scans automatically to detect new unmapped values from product imports.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control">
								<select disabled style="min-width:120px"><option><?php esc_html_e( 'Daily', 'attributehub-for-woocommerce' ); ?></option></select>
							</div>
						</div>
						<div class="ah-option-row">
							<div class="ah-option-info">
								<div class="ah-option-label">🔒 <?php esc_html_e( 'Email Digest for Unmapped Values', 'attributehub-for-woocommerce' ); ?></div>
								<p class="ah-option-hint"><?php esc_html_e( 'Receive a weekly email listing new unmapped codes found since the last scan.', 'attributehub-for-woocommerce' ); ?></p>
							</div>
							<div class="ah-option-control"><label class="ah-toggle"><input type="checkbox" disabled><span class="ah-toggle-track"></span><span class="ah-toggle-thumb"></span></label></div>
						</div>
					</div>
					<div class="ah-card-footer">
						<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn ah-btn--primary ah-btn--sm" target="_blank">
							⭐ <?php esc_html_e( 'Unlock Pro', 'attributehub-for-woocommerce' ); ?>
						</a>
					</div>
				</div>
				<?php endif; ?>

				<?php endif; ?>

				<?php do_action( 'attributehub_settings_tab_content', $active, $settings ); ?>

				<div class="ah-card-footer" style="border-radius: var(--ah-radius); border: 1px solid var(--ah-gray-200); background: var(--ah-white); padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
					<button type="submit" class="ah-btn ah-btn--primary">
						💾 <?php esc_html_e( 'Save Settings', 'attributehub-for-woocommerce' ); ?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub' ) ); ?>" class="ah-btn ah-btn--secondary">
						← <?php esc_html_e( 'Back to Dashboard', 'attributehub-for-woocommerce' ); ?>
					</a>
				</div>

			</form>
		</div>

		<!-- Sidebar -->
		<div class="ah-page-sidebar">

			<?php if ( ! $is_pro ) : ?>
			<!-- Pro Upgrade Card -->
			<div class="ah-sidebar-card ah-upgrade-card">
				<div class="ah-sidebar-head">⭐ <?php esc_html_e( 'AttributeHub Pro', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-sidebar-body">
					<p><?php esc_html_e( 'Turn your mapping workflow into a fully automated system.', 'attributehub-for-woocommerce' ); ?></p>
					<ul class="ah-upgrade-features">
						<li><?php esc_html_e( 'AI Label Suggestions (Claude / OpenAI)', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Auto-Map on Import with Rules Engine', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'CSV Bulk Import / Export', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Secondary Color Tags (multi-filter)', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Filter Click Analytics', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Scheduled Scans &amp; Email Digests', 'attributehub-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'FacetWP, YITH, Elementor Compatibility', 'attributehub-for-woocommerce' ); ?></li>
					</ul>
					<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-full" target="_blank">
						⭐ <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
			<?php endif; ?>

			<!-- Help Card -->
			<div class="ah-sidebar-card">
				<div class="ah-sidebar-head">📚 <?php esc_html_e( 'Resources', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-sidebar-body">
					<p>
						<a href="https://docs.codesolz.net/attributehub-for-woocommerce" target="_blank">📖 <?php esc_html_e( 'Documentation', 'attributehub-for-woocommerce' ); ?></a>
					</p>
					<p>
						<a href="https://wordpress.org/support/plugin/attributehub-for-woocommerce/" target="_blank">💬 <?php esc_html_e( 'Community Support', 'attributehub-for-woocommerce' ); ?></a>
					</p>
					<p>
						<a href="https://wordpress.org/support/plugin/attributehub-for-woocommerce/reviews/#new-post" target="_blank">⭐ <?php esc_html_e( 'Leave a Review', 'attributehub-for-woocommerce' ); ?></a>
					</p>
				</div>
			</div>

			<!-- Plugin Info Card -->
			<div class="ah-sidebar-card">
				<div class="ah-sidebar-head">ℹ️ <?php esc_html_e( 'Plugin Info', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-sidebar-body">
					<p>
						<strong><?php esc_html_e( 'Version:', 'attributehub-for-woocommerce' ); ?></strong>
						<?php echo esc_html( ATTRIBUTEHUB_VERSION ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'License:', 'attributehub-for-woocommerce' ); ?></strong>
						<?php if ( $is_pro ) : ?>
							<span class="ah-badge ah-badge--success"><?php esc_html_e( 'Pro Active', 'attributehub-for-woocommerce' ); ?></span>
						<?php else : ?>
							<span class="ah-badge ah-badge--muted"><?php esc_html_e( 'Free', 'attributehub-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</p>
				</div>
			</div>

		</div>
	</div>
</div>
