<?php
/*
	Plugin Name: Stamps.com WooCommerce Extension (USPS) (Basic)
	Plugin URI: https://www.xadapter.com/product/woocommerce-stamps-com-shipping-plugin-with-usps-postage/
	Description: Using Stamps.com APIs, print USPS shipping labels with Postage & obtain USPS real time shipping rates.
	Version: 1.1.4
	Author: WooForce
	Author URI: https://www.xadapter.com/vendor/wooforce/
*/
//Dev Version: 1.4.1

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Required functions
if ( ! function_exists( 'wf_is_woocommerce_active' ) ) {
	require_once( 'wf-includes/wf-functions.php' );
}

// WC active check
if ( ! wf_is_woocommerce_active() ) {
	return;
}

define("WF_USPS_STAMPS_ID", "wf_usps_stamps");
define("WF_USPS_STAMPS_ACCESS_KEY", "570f77ac-5374-46f1-aee7-84375876174b");
define("WF_ADV_DEBUG_MODE", "off"); // Turn "on" to get more logs.

function wf_stamps_activation_check(){
	if ( is_plugin_active('usps-stamps-woocommerce-shipping/usps-stamps-woocommerce-shipping.php') ){
        deactivate_plugins( basename( __FILE__ ) );
		wp_die( __("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='https://support.xadapter.com/'>support.xadapter.com</a>","wf-usps-stamps-woocommerce"), "", array('back_link' => 1 ));
	}

	if ( ! class_exists( 'SoapClient' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( __('Sorry, but you cannot run this plugin, it requires the <a href="http://php.net/manual/en/class.soapclient.php">SOAP</a> support on your server/hosting to function.', 'wf-usps-stamps-woocommerce' ) );
	}
}

register_activation_hook( __FILE__, 'wf_stamps_activation_check' );

/**
 * WC_USPS class
 */
if(!class_exists('USPS_Stamps_WooCommerce_Shipping')){
	class USPS_Stamps_WooCommerce_Shipping {
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Localisation
		 */
		public function init() {
			load_plugin_textdomain( 'wf-usps-stamps-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Plugin page links
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wf_usps_stamps' ) . '">' . __( 'Settings', 'wf-usps-stamps-woocommerce' ) . '</a>',

				'<a href="https://www.xadapter.com/product/woocommerce-stamps-com-shipping-plugin-with-usps-postage/" target="_blank">' . __( 'Premium Upgrade', 'wf-shipping-canada-post' ) . '</a>',

				'<a href="https://wordpress.org/support/plugin/woo-stamps-com-shipping-method" target="_blank">' . __( 'Support', 'wf-usps-stamps-woocommerce' ) . '</a>',

			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Load gateway class
		 */
		public function shipping_init() {
			include_once( 'includes/class-wf-shipping-stamps.php' );
		}

		/**
		 * Add method to WC
		 */
		public function add_method( $methods ) {
			$methods[] = 'WF_USPS_Stamps';
			return $methods;
		}

		/**
		 * Enqueue scripts
		 */
		public function scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-sortable' );
		}
	}
	new USPS_Stamps_WooCommerce_Shipping();
}
