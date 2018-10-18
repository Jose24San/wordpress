<?php
/**
 * Plugin Name: WooCommerce ShippingEasy Integration
 * Plugin URI: http://woothemes.com/products/woocommerce-shippingeasy/
 * Description: Send WooCommerce orders to your ShippingEasy account. When an order is shipped in ShippingEasy, the WooCommerce order will be updated.
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: ShippingEasy
 * Developer URI: http://shippingeasy.com/
 * Text Domain: woocommere-shippingeasy
 * Domain Path: /i18n/languages/
 * Version: 3.4.1
 *
 * Copyright (c) 2016 WooThemes
 * Copyright (c) 2016 ShippingEasy
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '2334d09bd4cc1ca0f3fd5e05f0aff5b0', '794783' );

function load_shippingeasy_textdomain() {
	load_plugin_textdomain('woocommerce-shippingeasy', FALSE, basename(dirname(__FILE__)).'/i18n/languages/');
} add_action( 'plugins_loaded', 'load_shippingeasy_textdomain' );

if ( is_woocommerce_active() ) {
	include_once( 'wc-shippingeasy-functions.php' );

	class WC_Integration_ShippingEasy {

		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		public function init() {
			include_once('classes/class-wc-shippingeasy-integration.php');
			add_filter('woocommerce_integrations', array($this, 'add_integration'));
		}

		public function add_integration($integrations) {
			$integrations[] = 'WC_ShippingEasy_Integration';
			return $integrations;
		}

	} // End Class

	$WC_Integration_ShippingEasy = new WC_Integration_ShippingEasy();

	function wc_shippingeasy_pugs_endpoint() {
		add_rewrite_rule('^shipment/callback', 'index.php?shipment=1&callback=1', 'top');
		include_once('classes/class-wc-shippingeasy-pugs-api-endpoint.php');
		new Pugs_API_Endpoint();
	}

	function wc_shippingeasy_pugs_query_vars($vars) {
		$vars[] = 'pugs';
		$vars[] = 'shipment';
		$vars[] = 'callback';
		return $vars;
	}

	function wc_shippingeasy_activate() {
		wc_shippingeasy_pugs_endpoint();
		flush_rewrite_rules();
	}

	function wc_shippingeasy_deactivate() {
		flush_rewrite_rules();
	}

	register_activation_hook( __FILE__, 'wc_shippingeasy_activate' );
	register_deactivation_hook( __FILE__, 'wc_shippingeasy_deactivate' );
	add_action( 'init', 'wc_shippingeasy_pugs_endpoint' );
	add_filter( 'query_vars', 'wc_shippingeasy_pugs_query_vars' );

	// Don't copy shipped status when automatically re-ordering subscribed products
	function se_reship_on_renew($order_meta_query, $original_order_id, $renewal_order_id, $new_order_role) {
		//$order_meta_query .= " AND `meta_key` NOT IN ('meta_val_1', 'meta_val_2')"; // ex
		$order_meta_query .= " AND `meta_key` NOT IN ('se_order_created')";
		return $order_meta_query;
	} 
	add_filter('woocommerce_subscriptions_renewal_order_meta_query', 'se_reship_on_renew', 10, 4);	

}

?>