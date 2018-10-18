<?php

/**
 * WooCommerce APA compatibility with Subscribe to Newsletter extension.
 *
 * @since 1.6.0
 */
class WC_Amazon_Payments_Advanced_Subscribe_To_Newsletter_Compat {

	public function __construct() {
		if ( class_exists( 'WC_Subscribe_To_Newsletter' ) ) {
			add_action( 'wc_amazon_pa_scripts_enqueued', array( $this, 'compat_scripts' ) );
		}
	}

	public function compat_scripts() {
		$js_suffix = '.min.js';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$js_suffix = '.js';
		}

		$url = wc_apa()->plugin_url . '/assets/js/amazon-wc-subscribe-to-newsletter-compat' . $js_suffix;
		wp_enqueue_script( 'amazon_pa_subscribe_to_newsletter_compat', $url, array( 'amazon_payments_advanced' ), wc_apa()->version, true );
	}

}