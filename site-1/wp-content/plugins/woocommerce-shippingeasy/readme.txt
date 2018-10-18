=== ShippingEasy for WooCommerce ===
Contributors: ShippingEasy, mattyza
Tags: shipping, woocommerce
Requires at least: 3.0
Tested up to: 4.1
Stable tag: trunk
License: GPLv3+

Ship your WooCommerce orders with ShippingEasy!

== Description ==
Plugin to integrate WooCommerce with ShippingEasy. As orders are created in WooCommerce, they will be sent to ShippingEasy. When an order is shipped in ShippingEasy, the WooCommerce order will be updated.

== Installation ==
= Requirements =
* WordPress version 3.0 and later

= Installation =
1. Unpack the download package
1. Upload shippingeasy-woocommerce folder to the `/wp-content/plugins/` directory
1. Ensure WooCommerce is installed and active
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin through the 'Integrations' tab under WooCommerce settings

= License =
This plugin is released under the GPL. You can use it free of charge on your personal or commercial blog.

== Changelog ==

= v3.4.1 (2016-09-09) =
 * Custom user agent string added to ShippingEasy API calls.

= v3.4.0 (2016-06-04) =
 * Detailed logging on ShippingEasy callback to WooCommerce.
 * Support for SkyVerge Sequential Order Numbers Pro plugin.

= v3.3.0 (2016-05-19) =
 * Added support for WooCommerce Order Status Manager plugin.
 * Added support for WooCommerce Sequential Order Numbers plugin.

= v3.2.0 (2016-03-21) =
 * Support for WooCommerce v2.6 territory changes.
 * Added support for WooCommerce Shipment Tracking plugin.
 * Refactored handling of product attributes for wider compatibility. 
 * Better handling of errors due to incorrect API URL.
 * Minor code cleanup.

= v3.1.3 (2015-08-28) =
 * Added support for WooCommerce Subscriptions extension.

= v3.1.2 (2015-07-14) =
 * Fixed bug preventing some complex orders from submitting to ShippingEasy.

= v3.1.1 (2015-06-11) =
 * Fixed bug causing coupon codes to replace order notes in ShippingEasy.

= v3.1.0 (2015-05-20) =
 * Added function to include coupon codes in order.
 * Improved support for virtual/downloadable products.
 
= v3.0.0 (2015-05-12) =
 * First release with WooThemes!

= v2.1.0 (2015-02-06) =
* Fixed issue causing incorrect communication of weights of variable products.
* Cosmetic tweaks to settings page.

= v2.0.0 (2015-01-22) =
* Refactored plugin for listing in WordPress.org and Woo directories.
* Orders send to ShippingEasy whenever their status changes to a shippable status.
* Improved integration with WooCommerce.
* Improved support for variable products.