=== Inventory Sync for WooCommerce ===
Contributors: rudrastyh
Tags: woocommerce, woocommerce stock, shared stock, stock sync, stock management
Requires at least: 3.1
Tested up to: 6.4.3
Stable tag: 1.2
Requires PHP: 5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows to sync the stock quantity of products with the same SKU between two WooCommerce stores.

== Description ==

Inventory Sync for WooCommerce allows to sync the stock of the products with the same SKUs between two WooCommerce stores.

= Features =

* Allows to sync not only **Stock quantity**, but also **Stock Status** and **Stock Management** checkbox value.
* Product variations are supported (must have the same SKU as well).
* Instantly syncs stock changes when a product is purchased or edited via WordPress or an order is refunded or cancelled.
* Two-directional product stock sync is supported.

= Pro features =

* Unlimited amount of WooCommerce stores is supported.
* Works with both regular WordPress sites and Multisite networks.

[Upgrade to Pro](https://rudrastyh.com/plugins/simple-product-stock-sync-for-woocommerce)

== Installation ==

= Automatic Install =

1. Log into your WordPress dashboard and go to Plugins &rarr; Add New
2. Search for "Inventory Sync for WooCommerce"
3. Click "Install Now" under the "Inventory Sync for WooCommerce" plugin
4. Click "Activate Now"

= Manual Install =

1. Download the plugin from the download button on this page
2. Unzip the file, and upload the resulting `inventory-sync-for-woocommerce` folder to your `/wp-content/plugins` directory
3. Log into your WordPress dashboard and go to Plugins
4. Click "Activate" under the "Inventory Sync for WooCommerce" plugin

== Frequently Asked Questions ==

= Does it work on localhost? =
Yes. The inventory sync is going to work great between localhost websites or from the localhost to a remote site. Do not forget though that in order to create an application password on the localhost you need to set `WP_ENVIRONMENT_TYPE` to `local` in your `wp-config.php` file.

= Does it support two-directional inventory sync? =
Yes. But in this case you need to install the plugin on both sites and add each one in the plugin settings.

== Screenshots ==
1. Inventory sync happens automatically and plugin doesn't even have any settings except the REST API authentication data of a site you are about to sync stock info with.
2. Stock status, Stock management and Quantity are the fields that will be synced.

== Changelog ==

= 1.2 =
* Added support for cancelled and refunded orders

= 1.1 =
* Bug fixes

= 1.0 =
* Initial release
