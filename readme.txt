=== AttributeHub for WooCommerce ===
Contributors: codesolz, m.tuhin
Tags: woocommerce, attributes, product filters, layered navigation, attribute mapping
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.0.0
Requires PHP: 8.0
WC requires at least: 6.0
WC tested up to: 9.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean WooCommerce product filters without changing your backend data. Map messy supplier color codes to customer-friendly filter labels.

== Description ==

**AttributeHub for WooCommerce** is a master attribute mapping plugin that creates a safe display layer between your messy backend attribute values and clean, customer-friendly filters — without touching your original product data.

= The Problem =

Many WooCommerce stores import products from suppliers, ERPs, CSV files, or dropshipping sources. The backend product data often contains supplier abbreviations, color codes, and internal values that look terrible in customer-facing filters:

**Your current filter might look like this:**
- 14KGD
- RHCRY
- BK
- 1GOLD
- 2SILVER
- BLK
- BCK
- MULTIBK

**Customers should see:**
- 14K Gold
- Rhodium Crystal
- Black
- Gold
- Silver

= The Solution =

AttributeHub creates a master attribute directory where you map many backend values to one clean master label. The frontend filter shows only master labels, and filtering by "Black" automatically includes all products with any of the mapped raw values (BK, BLK, BCK, MULTIBK, etc.).

Your backend data is never modified — safe for inventory systems, ERP syncing, and future imports.

= Features =

* **Attribute Scanner** — Detects ugly, abbreviated, and duplicate attribute values across all your WooCommerce attributes
* **Master Attribute Directory** — Create clean master labels and map multiple backend values to each
* **Clean Frontend Filters** — Your layered nav shows master labels instead of supplier codes
* **Smart Filter Query Expansion** — Filtering by "Black" returns ALL products mapped to Black, regardless of which code they use
* **Preview Mode** — See exactly what your filters will look like before going live
* **Product Metabox** — See the mapping status of each attribute on any product
* **Hide Unmapped Values** — Optionally suppress unmapped/ugly values from frontend filters
* **Duplicate Detection** — Groups near-identical attribute values for batch mapping
* **CSV Export** — Export your mapping configuration for backup or staging workflows
* **WooCommerce HPOS Compatible** — Works with High Performance Order Storage

= Pro Version =

[AttributeHub Pro](https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce) adds:

* **Secondary Color Tags** — A gold earring with black stones appears in BOTH Gold and Black filters
* **AI Label Suggestions** — AI suggests clean names for messy supplier codes (98% accuracy on abbreviations)
* **Auto-Map New Imports** — Rules engine automatically maps new product attributes on import
* **CSV Import** — Import mapping configurations from CSV (perfect for agencies)
* **Advanced Rules Engine** — Pattern-based auto-mapping: "starts with BK → Black"
* **Bulk Editor** — Spreadsheet-like interface for managing hundreds of mappings at once
* **Filter Analytics** — Track which filters customers click and which convert best
* **Scheduled Scans** — Automatically detect new unmapped values from nightly imports
* **Email Reports** — Get notified when new unmapped attribute values appear
* **FacetWP, YITH, Elementor Compatibility** — Works with popular filter plugins

= Perfect For =

* Jewelry stores with metal/stone codes
* Fashion and apparel stores with vendor-specific size/color naming
* Wholesale and B2B stores with ERP-exported attributes
* Dropshipping stores with inconsistent supplier data
* Agencies managing WooCommerce stores with imported catalogs

== Installation ==

1. Upload the `attributehub-for-woocommerce` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **AttributeHub** in your WordPress admin menu
4. Click **Scan Now** to detect your existing attribute values
5. Create master groups (e.g., "Black", "Gold") and map your backend values to them
6. Your frontend filters will immediately show clean master labels

== Frequently Asked Questions ==

= Will this modify my original WooCommerce attribute terms? =

No. AttributeHub never modifies, renames, or deletes your original attribute terms. It creates a separate mapping layer that only affects how terms are displayed on the frontend and how filter queries work. Your backend data remains exactly as-is.

= Will this break my inventory sync or ERP integration? =

No. Since AttributeHub doesn't touch your actual attribute terms, any inventory system, ERP, or supplier integration that uses the original attribute values will continue to work normally.

= Does this work with my existing filter plugin? =

AttributeHub works natively with WooCommerce's built-in Layered Navigation widget. For other filter plugins (FacetWP, YITH, etc.), compatibility bridges are available in AttributeHub Pro.

= What happens to unmapped attribute values? =

By default, unmapped values are shown as-is in your filters. You can enable "Hide Unmapped Values" in Settings to suppress them from the frontend until they're mapped.

= Can I import my mappings from CSV? =

CSV export is available in the free version. CSV import (for staging-to-production workflows) is a Pro feature.

= How does the Filter Query Expansion work? =

When a customer clicks "Black" in your filter, AttributeHub intercepts the WooCommerce product query and automatically expands it to include all products with any of the mapped raw values (BK, BLK, BCK, MULTIBK, etc.). Products are returned correctly without the customer needing to know about the backend codes.

== Screenshots ==

1. Dashboard overview with per-taxonomy mapping stats
2. Attribute Scanner showing ugly/duplicate detection
3. Master Attribute Directory — create and manage master labels
4. Mapping Editor — drag-and-drop interface for mapping values
5. Preview mode — before/after filter comparison
6. Product metabox showing mapping status per attribute
7. Frontend filter: before (ugly codes) vs after (clean master labels)

== External Services ==

This plugin loads **SweetAlert2** from the jsDelivr CDN for admin modal dialogs and notifications. This library is only loaded on AttributeHub admin pages and is never loaded on the frontend of your site.

* Service: [jsDelivr CDN](https://www.jsdelivr.com/)
* Library: [SweetAlert2](https://sweetalert2.github.io/) (MIT License)
* URL: `https://cdn.jsdelivr.net/npm/sweetalert2@11/`
* When: Admin pages only (AttributeHub menu pages and WooCommerce product edit screen)
* Data sent: No personal data is transmitted. The browser fetches the library file from jsDelivr's servers; jsDelivr may log standard access data (IP, user agent) per their [privacy policy](https://www.jsdelivr.com/terms/privacy-policy-jsdelivr-net).

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release.
