<?php
/**
 * AttributeHub — Master Directory Template (Premium UI)
 *
 * @var array  $taxonomies All WC attribute taxonomies.
 * @var string $active_tax Currently selected taxonomy.
 * @var array  $masters    Master group rows for active taxonomy.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ah-wrap">

	<!-- Page Header -->
	<div class="ah-page-header">
		<div class="ah-page-header-left">
			<div class="ah-page-logo">&#128203;</div>
			<div>
				<div class="ah-page-title"><?php esc_html_e( 'Master Labels', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-page-subtitle"><?php esc_html_e( 'Define the clean labels that replace messy backend attribute codes in your storefront.', 'attributehub-for-woocommerce' ); ?></div>
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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-masters&taxonomy=' . $tax ) ); ?>"
		   class="<?php echo esc_attr( $tax === $active_tax ? 'active' : '' ); ?>">
			<?php echo esc_html( $label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<div class="ah-page-body">
		<div class="ah-page-main">

			<!-- Add New Master Card -->
			<div class="ah-card" style="margin-bottom:20px;">
				<div class="ah-card-header">
					<div class="ah-card-title">&#43; <?php esc_html_e( 'Add New Master Label', 'attributehub-for-woocommerce' ); ?></div>
					<button class="ah-btn ah-btn--secondary ah-btn--sm ah-add-master-btn" type="button">
						<?php esc_html_e( 'Toggle Form', 'attributehub-for-woocommerce' ); ?>
					</button>
				</div>
				<div class="ah-card-body" id="ah-add-master-form" style="display:none;">
					<div class="ah-form-grid ah-form-grid--4">
						<div class="ah-field">
							<label><?php esc_html_e( 'Display Label', 'attributehub-for-woocommerce' ); ?> <span class="ah-required">*</span></label>
							<input type="text" id="ah-new-master-label"
							       placeholder="<?php esc_attr_e( 'e.g. Black', 'attributehub-for-woocommerce' ); ?>">
						</div>
						<div class="ah-field">
							<label><?php esc_html_e( 'Slug', 'attributehub-for-woocommerce' ); ?></label>
							<input type="text" id="ah-new-master-slug"
							       placeholder="<?php esc_attr_e( 'auto-generated', 'attributehub-for-woocommerce' ); ?>">
						</div>
						<div class="ah-field">
							<label><?php esc_html_e( 'Description', 'attributehub-for-woocommerce' ); ?></label>
							<input type="text" id="ah-new-master-desc"
							       placeholder="<?php esc_attr_e( 'Optional', 'attributehub-for-woocommerce' ); ?>">
						</div>
						<div class="ah-field ah-field--checkbox">
							<label class="ah-toggle-label">
								<label class="ah-toggle">
									<input type="hidden" id="ah-new-master-hidden" value="0">
									<input type="checkbox" id="ah-new-master-hidden-cb" value="1">
									<span class="ah-toggle-track"></span>
									<span class="ah-toggle-thumb"></span>
								</label>
								<span><?php esc_html_e( 'Hide from filters', 'attributehub-for-woocommerce' ); ?></span>
							</label>
						</div>
					</div>
					<div style="display:flex;gap:8px;margin-top:4px;">
						<button class="ah-btn ah-btn--primary" id="ah-save-master-btn" type="button"
						        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>">
							<?php esc_html_e( 'Save Label', 'attributehub-for-woocommerce' ); ?>
						</button>
						<button class="ah-btn ah-btn--secondary" id="ah-cancel-master-btn" type="button">
							<?php esc_html_e( 'Cancel', 'attributehub-for-woocommerce' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Masters List -->
			<div class="ah-table-wrap">
				<?php if ( empty( $masters ) ) : ?>
				<div class="ah-empty-state" style="padding:48px 24px;">
					<span class="ah-empty-icon dashicons dashicons-tag"></span>
					<p><?php esc_html_e( 'No master labels yet for this attribute. Add one above to get started.', 'attributehub-for-woocommerce' ); ?></p>
				</div>
				<?php else : ?>
				<table class="ah-table" id="ah-masters-sortable" data-taxonomy="<?php echo esc_attr( $active_tax ); ?>">
					<thead>
						<tr>
							<th style="width:32px;padding-left:16px;">
								<span class="dashicons dashicons-menu" style="color:var(--ah-gray-400);vertical-align:middle;" title="<?php esc_attr_e( 'Drag to reorder', 'attributehub-for-woocommerce' ); ?>"></span>
							</th>
							<th><?php esc_html_e( 'Label', 'attributehub-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'attributehub-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Mapped Values', 'attributehub-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Visibility', 'attributehub-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'attributehub-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $masters as $master ) : ?>
						<tr data-id="<?php echo esc_attr( $master->id ); ?>">
							<td style="padding-left:16px;cursor:grab;">
								<span class="dashicons dashicons-menu" style="color:var(--ah-gray-400);vertical-align:middle;"></span>
							</td>
							<td>
								<span class="ah-cell-primary"><?php echo esc_html( $master->label ); ?></span>
								<?php if ( $master->description ) : ?>
									<br><span class="ah-text-sm ah-muted"><?php echo esc_html( $master->description ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<code class="ah-code-chip"><?php echo esc_html( $master->slug ); ?></code>
							</td>
							<td>
								<?php if ( ! empty( $master->mapped_count ) ) : ?>
								<span class="ah-badge ah-badge--mapped"><?php echo esc_html( $master->mapped_count ); ?> <?php esc_html_e( 'values', 'attributehub-for-woocommerce' ); ?></span>
								<?php else : ?>
								<span class="ah-badge ah-badge--muted"><?php esc_html_e( 'None yet', 'attributehub-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $master->is_hidden ) : ?>
								<span class="ah-badge ah-badge--hidden">&#128683; <?php esc_html_e( 'Hidden', 'attributehub-for-woocommerce' ); ?></span>
								<?php else : ?>
								<span class="ah-badge ah-badge--success">&#128065;&#65039; <?php esc_html_e( 'Visible', 'attributehub-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</td>
							<td style="white-space:nowrap;">
								<button class="ah-btn ah-btn--secondary ah-btn--sm ah-edit-master-btn"
								        data-id="<?php echo esc_attr( $master->id ); ?>"
								        data-label="<?php echo esc_attr( $master->label ); ?>"
								        data-slug="<?php echo esc_attr( $master->slug ); ?>"
								        data-desc="<?php echo esc_attr( $master->description ); ?>"
								        data-hidden="<?php echo esc_attr( $master->is_hidden ); ?>"
								        type="button">
									<?php esc_html_e( 'Edit', 'attributehub-for-woocommerce' ); ?>
								</button>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $active_tax . '&master=' . $master->id ) ); ?>"
								   class="ah-btn ah-btn--secondary ah-btn--sm" style="margin-left:4px;">
									<?php esc_html_e( 'Map', 'attributehub-for-woocommerce' ); ?>
								</a>
								<button class="ah-btn ah-btn--danger ah-btn--sm ah-delete-master-btn"
								        data-id="<?php echo esc_attr( $master->id ); ?>"
								        data-label="<?php echo esc_attr( $master->label ); ?>"
								        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
								        style="margin-left:4px;"
								        type="button">
									<?php esc_html_e( 'Delete', 'attributehub-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>

		</div><!-- .ah-page-main -->

		<!-- Sidebar -->
		<div class="ah-page-sidebar">
			<?php if ( ! attributehub()->is_pro() ) : ?>
			<div class="ah-upgrade-card">
				<div class="ah-upgrade-badge">PRO</div>
				<div class="ah-upgrade-title"><?php esc_html_e( 'Unlock Smart Grouping', 'attributehub-for-woocommerce' ); ?></div>
				<div class="ah-upgrade-desc"><?php esc_html_e( 'AI label suggestions, hierarchical sub-labels, CSV import, and auto-map rules for new imports.', 'attributehub-for-woocommerce' ); ?></div>
				<ul class="ah-upgrade-features">
					<li><?php esc_html_e( 'AI Label Suggestions', 'attributehub-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'Hierarchical Sub-Labels', 'attributehub-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'CSV Import/Export', 'attributehub-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'Auto-Map New Imports', 'attributehub-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'Pattern-Based Rules', 'attributehub-for-woocommerce' ); ?></li>
				</ul>
				<a href="https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce" class="ah-btn-upgrade-full" target="_blank">
					<?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?> &rarr;
				</a>
			</div>
			<?php endif; ?>

			<div class="ah-card">
				<div class="ah-card-header">
					<div class="ah-card-title">&#128161; <?php esc_html_e( 'How It Works', 'attributehub-for-woocommerce' ); ?></div>
				</div>
				<div class="ah-card-body" style="font-size:13px;color:var(--ah-gray-600);line-height:1.6;">
					<p><?php esc_html_e( 'Master Labels are the clean names your customers see. Once created, map your messy backend codes to them using the Mapping Editor.', 'attributehub-for-woocommerce' ); ?></p>
					<p><?php esc_html_e( 'Drag rows to set the display order in your storefront filters.', 'attributehub-for-woocommerce' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings&taxonomy=' . $active_tax ) ); ?>"
					   class="ah-btn ah-btn--secondary ah-btn--sm" style="margin-top:4px;">
						<?php esc_html_e( 'Go to Mapping Editor', 'attributehub-for-woocommerce' ); ?> &rarr;
					</a>
				</div>
			</div>
		</div><!-- .ah-page-sidebar -->

	</div><!-- .ah-page-body -->
</div>

<!-- Edit Master Modal -->
<div id="ah-edit-master-modal" class="ah-modal" style="display:none;">
	<div class="ah-modal-box">
		<div class="ah-modal-header">
			<h3><?php esc_html_e( 'Edit Master Label', 'attributehub-for-woocommerce' ); ?></h3>
			<button class="ah-modal-close-x" type="button" onclick="document.getElementById('ah-edit-master-modal').style.display='none'">&times;</button>
		</div>
		<div class="ah-modal-body">
			<input type="hidden" id="ah-edit-master-id">
			<div class="ah-form-grid ah-form-grid--2">
				<div class="ah-field">
					<label><?php esc_html_e( 'Display Label', 'attributehub-for-woocommerce' ); ?> <span class="ah-required">*</span></label>
					<input type="text" id="ah-edit-master-label">
				</div>
				<div class="ah-field">
					<label><?php esc_html_e( 'Slug', 'attributehub-for-woocommerce' ); ?></label>
					<input type="text" id="ah-edit-master-slug">
				</div>
			</div>
			<div class="ah-field" style="margin-top:12px;">
				<label><?php esc_html_e( 'Description', 'attributehub-for-woocommerce' ); ?></label>
				<input type="text" id="ah-edit-master-desc">
			</div>
			<div class="ah-field" style="margin-top:12px;">
				<label class="ah-toggle-label">
					<label class="ah-toggle">
						<input type="hidden" id="ah-edit-master-hidden" value="0">
						<input type="checkbox" id="ah-edit-master-hidden-cb" value="1">
						<span class="ah-toggle-track"></span>
						<span class="ah-toggle-thumb"></span>
					</label>
					<span><?php esc_html_e( 'Hide from frontend filters', 'attributehub-for-woocommerce' ); ?></span>
				</label>
			</div>
		</div>
		<div class="ah-modal-footer">
			<button class="ah-btn ah-btn--secondary"
			        onclick="document.getElementById('ah-edit-master-modal').style.display='none'"
			        type="button">
				<?php esc_html_e( 'Cancel', 'attributehub-for-woocommerce' ); ?>
			</button>
			<button class="ah-btn ah-btn--primary" id="ah-update-master-btn"
			        data-taxonomy="<?php echo esc_attr( $active_tax ); ?>"
			        type="button">
				<?php esc_html_e( 'Update Label', 'attributehub-for-woocommerce' ); ?>
			</button>
		</div>
	</div>
</div>
