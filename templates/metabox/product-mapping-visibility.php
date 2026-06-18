<?php
/**
 * AttributeHub — Product Metabox Template
 *
 * @var array $mapping_data  [
 *   'taxonomy'       => string,
 *   'taxonomy_label' => string,
 *   'terms'          => WP_Term[],
 *   'mapped'         => [term_id => master_label],
 * ][]
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $mapping_data ) ) :
?>
<p class="ah-metabox-empty description">
	<?php esc_html_e( 'This product has no taxonomy-based attributes, or no WooCommerce attributes are configured.', 'attributehub-for-woocommerce' ); ?>
</p>
<?php return; endif; ?>

<div class="ah-metabox-wrap">
	<?php foreach ( $mapping_data as $group ) : ?>
	<div class="ah-metabox-group">
		<h4 class="ah-metabox-taxonomy-label"><?php echo esc_html( $group['taxonomy_label'] ); ?></h4>
		<table class="ah-metabox-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Raw Value', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Frontend Label', 'attributehub-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Status', 'attributehub-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $group['terms'] as $term ) : ?>
			<?php $master_label = $group['mapped'][ $term->term_id ] ?? null; ?>
			<tr>
				<td><code><?php echo esc_html( $term->name ); ?></code></td>
				<td>
					<?php if ( $master_label ) : ?>
						<strong><?php echo esc_html( $master_label ); ?></strong>
					<?php else : ?>
						<em class="ah-muted"><?php echo esc_html( $term->name ); ?></em>
					<?php endif; ?>
				</td>
				<td>
					<?php if ( $master_label ) : ?>
						<span class="ah-badge ah-badge--success">&#10003; <?php esc_html_e( 'Mapped', 'attributehub-for-woocommerce' ); ?></span>
					<?php else : ?>
						<span class="ah-badge ah-badge--warning"><?php esc_html_e( 'Unmapped', 'attributehub-for-woocommerce' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endforeach; ?>

	<p class="ah-metabox-footer">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=attributehub-mappings' ) ); ?>">
			<?php esc_html_e( 'Open Mapping Editor', 'attributehub-for-woocommerce' ); ?>
		</a>
	</p>
</div>
