<?php
/**
 * About Us – the CodeSolz plugin family.
 *
 * Same page as Better Find & Replace's About Us screen: a short intro to
 * CodeSolz, a card for this plugin, and cards for the other CodeSolz plugins.
 * Every icon is bundled with the plugin, so the page makes no external request.
 *
 * @package AttributeHub
 */

defined( 'ABSPATH' ) || exit;

$attributehub_about_icons = ATTRIBUTEHUB_URL . 'assets/img/plugins/';

/*
 * The other CodeSolz plugins. `icon_file` is the plugin's WordPress.org icon,
 * bundled next to this plugin's other images.
 */
$attributehub_about_plugins = array(
	array(
		'title'       => 'Better Find & Replace',
		'subtitle'    => 'Change any text across your whole site at once',
		'description' => 'Replace text on the fly as pages load, or permanently in the database. Works with plain text and regular expressions, and swaps media files in your Media Library.',
		'icon_file'   => 'better-find-and-replace.png',
		'features'    => array(
			'Real-time replacement without touching the database',
			'Permanent find & replace in database tables',
			'Plain text and regex rules',
			'Replace media files in the Media Library',
		),
		'pro_url'     => 'https://codesolz.net/our-products/wordpress-plugin/real-time-auto-find-and-replace/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/real-time-auto-find-and-replace/',
	),
	array(
		'title'       => 'Aisleza – 3D Virtual Store for WooCommerce',
		'subtitle'    => 'Let shoppers walk through your store',
		'description' => 'Turn your WooCommerce catalog into a walk-in 3D store. Categories become aisles, products sit on real shelves with price tags, and shoppers fill a trolley that is your real WooCommerce cart.',
		'icon_file'   => 'aisleza.png',
		'features'    => array(
			'Build aisles from your product categories',
			'Products on shelves with live prices & sale tags',
			'Uses your real WooCommerce cart & checkout',
			'Works with keyboard, mouse, and touch joystick',
		),
		'pro_url'     => 'https://aisleza.com/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/aisleza-3d-virtual-store-for-woocommerce/',
	),
	array(
		'title'       => 'PressHelm AI Site Developer',
		'subtitle'    => 'Your AI developer for WordPress content',
		'description' => 'Give your WordPress site its own AI developer. Ask in plain words from Telegram, Claude, or ChatGPT, and it builds pages, posts, products, menus, forms, and SEO on your real site, with approvals and one-tap undo.',
		'icon_file'   => 'presshelm-ai.png',
		'features'    => array(
			'Edit your site from Telegram, Claude, or ChatGPT',
			'Pages, products, images, menus, forms & SEO',
			'Approve important changes before they run',
			'Undo any change with one tap',
		),
		'pro_url'     => 'https://presshelmai.com/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/presshelm-ai/',
	),
	array(
		'title'       => 'WatchSpire',
		'subtitle'    => 'Your WordPress early warning system',
		'description' => 'Catch silent WordPress failures before they cost you leads or sales. WatchSpire monitors forms, checkout, email delivery, uptime, SSL, and more, then alerts you when something goes wrong.',
		'icon_file'   => 'watchspire.png',
		'features'    => array(
			'Catch failed forms & checkout issues',
			'Know what changed before a failure',
			'Get alerts before problems cost you leads',
			'See which update may have caused a failure',
		),
		'pro_url'     => 'https://codesolz.net/our-products/wordpress-plugin/watchspire/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/watchspire/',
	),
	array(
		'title'       => 'AI Store Signals for WooCommerce',
		'subtitle'    => 'Get your products ready for AI shopping',
		'description' => 'See how well AI systems can understand your WooCommerce store. Find visibility gaps, improve product data, generate AI-friendly feeds and llms.txt, and track AI bot activity.',
		'icon_file'   => 'ai-store-signals.png',
		'features'    => array(
			'Get an AI readiness score for your store',
			'Find product & schema visibility gaps',
			'Generate AI-friendly feeds & llms.txt',
		),
		'pro_url'     => 'https://codesolz.net/our-products/wordpress-plugin/ai-store-signals-for-woocommerce/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/ai-store-signals-for-woocommerce/',
	),
	array(
		'title'       => 'WearGraph – AI Virtual Try-On for WooCommerce',
		'subtitle'    => 'Let shoppers see clothes on themselves',
		'description' => 'Shoppers add a photo and see the garment on themselves, with Add to Cart right beside the preview. Runs on your own Gemini, OpenAI, OpenRouter, FLUX or xAI key, with no WearGraph account or subscription.',
		'icon_file'   => 'weargraph.png',
		'features'    => array(
			'AI fitting room on product pages',
			'On-model product images from your own photos',
			'See which products are ready for try-on',
		),
		'pro_url'     => 'https://codesolz.net/our-products/wordpress-plugin/weargraph/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/weargraph/',
	),
	array(
		'title'       => 'Merchant Feed Booster for WooCommerce',
		'subtitle'    => 'Fix Google Shopping feed problems before they cost sales',
		'description' => 'Find exactly why your WooCommerce products may be rejected or underperforming on Google Shopping. Audit your catalog, get a health score, and see exactly what needs fixing.',
		'icon_file'   => 'merchant-feed-booster.png',
		'features'    => array(
			'Audit products against 27 feed rules',
			'Get a 0–100 feed health score',
			'See exactly what needs fixing',
		),
		'pro_url'     => 'https://codesolz.net/our-products/wordpress-plugin/merchant-feed-booster-lite-for-woocommerce/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce',
		'wporg_url'   => 'https://wordpress.org/plugins/merchant-feed-booster-lite-for-woocommerce/',
	),
);

$attributehub_about_docs_url     = 'https://docs.codesolz.net/attributehub-for-woocommerce/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_product_url  = 'https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_wporg_url    = 'https://wordpress.org/plugins/attributehub-for-woocommerce/';
$attributehub_about_support_url  = 'https://wordpress.org/support/plugin/attributehub-for-woocommerce/';
$attributehub_about_upgrade_url  = 'https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_products_url = 'https://codesolz.net/our-products/wordpress-plugins/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_website_url  = 'https://codesolz.net/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_forum_url    = 'https://codesolz.net/forum/?utm_source=plugin-about&utm_medium=wp-admin&utm_campaign=about-page&utm_content=attributehub-for-woocommerce';
$attributehub_about_has_pro      = defined( 'ATTRIBUTEHUB_PRO_VERSION' );
?>
<div class="wrap">
<div class="attributehub-about-wrap">

	<!-- ================================================
		HERO SECTION
		================================================ -->
	<div class="attributehub-about-hero">
		<div class="attributehub-about-hero-grid">
			<div class="attributehub-about-hero-copy">
				<div class="attributehub-about-hero-badge"><?php esc_html_e( 'CodeSolz · WordPress plugins since 2016', 'attributehub-for-woocommerce' ); ?></div>
				<h1 class="attributehub-about-hero-title"><?php esc_html_e( 'WordPress Tools That Each Fix One Real Problem', 'attributehub-for-woocommerce' ); ?></h1>
				<p class="attributehub-about-hero-subtitle">
					<?php esc_html_e( 'A contact form that stops sending without any error. A Google Shopping product rejected because its title is too long. Supplier codes like BLK showing up in your shop filters. CodeSolz plugins find problems like these on your own site, show you exactly what is wrong, and help you fix it.', 'attributehub-for-woocommerce' ); ?>
				</p>
				<div class="attributehub-about-hero-points">
					<span class="attributehub-about-hero-point"><?php esc_html_e( 'Every plugin starts free on WordPress.org', 'attributehub-for-woocommerce' ); ?></span>
					<span class="attributehub-about-hero-point"><?php esc_html_e( 'This page loads nothing from outside your site', 'attributehub-for-woocommerce' ); ?></span>
					<span class="attributehub-about-hero-point"><?php esc_html_e( 'Plugins for WordPress and WooCommerce', 'attributehub-for-woocommerce' ); ?></span>
				</div>
				<div class="attributehub-about-hero-actions">
					<a href="<?php echo esc_url( $attributehub_about_products_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-hero-cta">
						<?php esc_html_e( 'Browse Our Plugins', 'attributehub-for-woocommerce' ); ?> <span aria-hidden="true">&#8599;</span>
					</a>
					<a href="<?php echo esc_url( $attributehub_about_website_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-hero-link">
						<?php esc_html_e( 'Visit CodeSolz', 'attributehub-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
			<div class="attributehub-about-hero-showcase">
				<div class="attributehub-about-hero-panel">
					<div class="attributehub-about-hero-panel-label"><?php esc_html_e( 'Find the right tool', 'attributehub-for-woocommerce' ); ?></div>
					<h2 class="attributehub-about-hero-panel-title"><?php esc_html_e( 'Start with the problem you have', 'attributehub-for-woocommerce' ); ?></h2>
					<div class="attributehub-about-hero-feature-list">
						<div class="attributehub-about-hero-feature">
							<strong><?php esc_html_e( 'Sales you may be losing', 'attributehub-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'WatchSpire catches forms and checkouts that fail without an error. Merchant Feed Booster checks every product against 27 Google Shopping rules.', 'attributehub-for-woocommerce' ); ?></span>
						</div>
						<div class="attributehub-about-hero-feature">
							<strong><?php esc_html_e( 'A messy catalog or site', 'attributehub-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'AttributeHub turns codes like BK into Black in your filters. Better Find & Replace changes a word or link on every page at once.', 'attributehub-for-woocommerce' ); ?></span>
						</div>
						<div class="attributehub-about-hero-feature">
							<strong><?php esc_html_e( 'New ways for shoppers to buy', 'attributehub-for-woocommerce' ); ?></strong>
							<span><?php esc_html_e( 'Aisleza turns your catalog into a walk-in 3D store, WearGraph adds virtual try-on, and AI Store Signals gets products ready for AI shopping assistants.', 'attributehub-for-woocommerce' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- ================================================
		FEATURED PLUGIN – THIS PLUGIN
		================================================ -->
	<div class="attributehub-about-section-header">
		<div class="attributehub-about-section-label"><?php esc_html_e( 'Currently Installed', 'attributehub-for-woocommerce' ); ?></div>
		<h2 class="attributehub-about-section-title"><?php esc_html_e( 'Your Active Plugin', 'attributehub-for-woocommerce' ); ?></h2>
		<p class="attributehub-about-section-desc"><?php esc_html_e( 'What the plugin you are running does, and where to get help with it.', 'attributehub-for-woocommerce' ); ?></p>
	</div>

	<div class="attributehub-about-featured-card">
		<div class="attributehub-about-featured-inner">

			<div class="attributehub-about-featured-icon-wrap" aria-hidden="true">
				<img src="<?php echo esc_url( $attributehub_about_icons . 'attributehub.png' ); ?>" alt="" width="72" height="72" />
			</div>

			<div class="attributehub-about-featured-body">
				<div class="attributehub-about-featured-meta">
					<span class="attributehub-about-badge featured-label"><?php esc_html_e( 'Installed', 'attributehub-for-woocommerce' ); ?></span>
					<span class="attributehub-about-badge active"><?php esc_html_e( 'Active', 'attributehub-for-woocommerce' ); ?></span>
					<?php if ( $attributehub_about_has_pro ) : ?>
						<span class="attributehub-about-badge premium"><?php esc_html_e( 'Pro', 'attributehub-for-woocommerce' ); ?></span>
					<?php endif; ?>
				</div>

				<h2 class="attributehub-about-featured-title"><?php esc_html_e( 'AttributeHub for WooCommerce', 'attributehub-for-woocommerce' ); ?></h2>

				<p class="attributehub-about-featured-desc">
					<?php esc_html_e( 'Maps messy supplier and imported attribute values to one clean label, so BK, BLK and Black all show up as "Black" in your shop filters. Your original product data is never changed.', 'attributehub-for-woocommerce' ); ?>
				</p>

				<ul class="attributehub-about-feature-list">
							<li><?php esc_html_e( 'Scanner finds abbreviated and duplicate values', 'attributehub-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'One master label per attribute value', 'attributehub-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Drag-and-drop mapping editor', 'attributehub-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Filtering by a label includes every mapped value', 'attributehub-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Preview your filters before going live', 'attributehub-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Mapping status on the product edit screen', 'attributehub-for-woocommerce' ); ?></li>
				</ul>

				<div class="attributehub-about-btn-group">
					<?php if ( '' !== $attributehub_about_docs_url ) : ?>
						<a href="<?php echo esc_url( $attributehub_about_docs_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn primary">
							<?php esc_html_e( 'Documentation', 'attributehub-for-woocommerce' ); ?>
						</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $attributehub_about_product_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn <?php echo '' !== $attributehub_about_docs_url ? 'secondary' : 'primary'; ?>">
						<?php esc_html_e( 'Product Page', 'attributehub-for-woocommerce' ); ?>
					</a>
					<a href="<?php echo esc_url( $attributehub_about_wporg_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn secondary">
						<?php esc_html_e( 'WordPress.org Page', 'attributehub-for-woocommerce' ); ?>
					</a>
					<a href="<?php echo esc_url( $attributehub_about_support_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn secondary">
						<?php esc_html_e( 'Get Support', 'attributehub-for-woocommerce' ); ?>
					</a>
					<?php if ( ! $attributehub_about_has_pro ) : ?>
						<a href="<?php echo esc_url( $attributehub_about_upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn outline-purple">
							&#9733; <?php esc_html_e( 'Upgrade to Pro', 'attributehub-for-woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- ================================================
		PLUGIN GRID
		================================================ -->
	<div class="attributehub-about-section-header">
		<div class="attributehub-about-section-label"><?php esc_html_e( 'Plugin Family', 'attributehub-for-woocommerce' ); ?></div>
		<h2 class="attributehub-about-section-title"><?php esc_html_e( 'More from CodeSolz', 'attributehub-for-woocommerce' ); ?></h2>
		<p class="attributehub-about-section-desc"><?php esc_html_e( 'Each one solves a different problem, and each one has a free version on WordPress.org you can try first.', 'attributehub-for-woocommerce' ); ?></p>
	</div>

	<div class="attributehub-about-plugins-grid">
		<?php foreach ( $attributehub_about_plugins as $attributehub_about_plugin ) : ?>
		<div class="attributehub-about-plugin-card">
			<div class="attributehub-about-card-top">
				<div class="attributehub-about-card-icon" aria-hidden="true">
					<img src="<?php echo esc_url( $attributehub_about_icons . $attributehub_about_plugin['icon_file'] ); ?>" alt="" width="48" height="48" loading="lazy" />
				</div>
				<div class="attributehub-about-card-heading">
					<h3 class="attributehub-about-card-title"><?php echo esc_html( $attributehub_about_plugin['title'] ); ?></h3>
					<span class="attributehub-about-card-subtitle"><?php echo esc_html( $attributehub_about_plugin['subtitle'] ); ?></span>
				</div>
			</div>
			<p class="attributehub-about-card-desc"><?php echo esc_html( $attributehub_about_plugin['description'] ); ?></p>
			<?php if ( ! empty( $attributehub_about_plugin['features'] ) ) : ?>
			<ul class="attributehub-about-card-features">
				<?php foreach ( $attributehub_about_plugin['features'] as $attributehub_about_feature ) : ?>
				<li><?php echo esc_html( $attributehub_about_feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<div class="attributehub-about-card-footer">
				<a href="<?php echo esc_url( $attributehub_about_plugin['pro_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-card-link pro">
					<?php esc_html_e( 'Learn More', 'attributehub-for-woocommerce' ); ?>
				</a>
				<a href="<?php echo esc_url( $attributehub_about_plugin['wporg_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-card-link wporg">
					<?php esc_html_e( 'Get It Free on WordPress.org', 'attributehub-for-woocommerce' ); ?> &#8599;
				</a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<!-- ================================================
		FOOTER SECTION
		================================================ -->
	<div class="attributehub-about-trust-section">
		<div class="attributehub-about-trust-logo">Code<span>Solz</span></div>
		<h2 class="attributehub-about-trust-tagline"><?php esc_html_e( 'Making WordPress plugins since 2016', 'attributehub-for-woocommerce' ); ?></h2>
		<p class="attributehub-about-trust-desc">
			<?php esc_html_e( 'Every plugin on this page starts free on WordPress.org, so you can try it on your own site before you pay for anything.', 'attributehub-for-woocommerce' ); ?>
		</p>
		<div class="attributehub-about-trust-links">
			<a href="<?php echo esc_url( $attributehub_about_website_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn primary">
				<?php esc_html_e( 'Visit Website', 'attributehub-for-woocommerce' ); ?>
			</a>
			<?php if ( '' !== $attributehub_about_docs_url ) : ?>
				<a href="<?php echo esc_url( $attributehub_about_docs_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn secondary">
					<?php esc_html_e( 'Documentation', 'attributehub-for-woocommerce' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( $attributehub_about_support_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn secondary">
				<?php esc_html_e( 'Get Support', 'attributehub-for-woocommerce' ); ?>
			</a>
			<a href="<?php echo esc_url( $attributehub_about_forum_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn secondary">
				<?php esc_html_e( 'Our Community', 'attributehub-for-woocommerce' ); ?>
			</a>
			<a href="<?php echo esc_url( $attributehub_about_products_url ); ?>" target="_blank" rel="noopener noreferrer" class="attributehub-about-btn outline-purple">
				<?php esc_html_e( 'All Our Plugins', 'attributehub-for-woocommerce' ); ?> &#8599;
			</a>
		</div>
	</div>

</div><!-- .attributehub-about-wrap -->
</div><!-- .wrap -->
