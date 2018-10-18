<?php

class Pugs_API_Endpoint {

	/** Hook WordPress
	* 	@return void
	*/
	public function __construct() {
		add_action('parse_request', array($this, 'sniff_requests'), 0);
	}

	/** Sniff Requests
	* 	This is where we hijack all API requests
	* 	@return die if API request
	*/
	public function sniff_requests($query) {
		if (isset($query->query_vars['shipment'])) {
			$this->handle_request();
			exit;
		}
	}

	/** Handle Requests
	* 	@return void
	*/
	protected function handle_request() {

		global $wp;
		global $wpdb;

		$pugs = $wp->query_vars['pugs'];
		$values = file_get_contents('php://input');
		$output = json_decode($values, true);

		if (!empty($output)) { se_wc_log_ok(sprintf(__('Callback received data: %s'), json_encode($output))); }
		else { se_wc_log_error(sprintf(__('Callback received invalid data: %s'), $values)); }

		// Store the values of shipped order which we are getting from ShippingEasy.
		$id = $output['shipment']['orders'][0]['ext_order_reference_id'];
		$shipping_id = $output['shipment']['id'];
		$tracking_number = $output['shipment']['tracking_number'];
		$carrier_key = $output['shipment']['carrier_key'];
		$carrier_service_key = $output['shipment']['carrier_service_key'];
		$shipment_cost_cents = $output['shipment']['shipment_cost'];
		$shipment_cost = ($shipment_cost_cents / 100);
		$line_subtotal = 0;
		$total_tax = 0;
		$cart_discount = 0;
		$order_discount = 0;

		$order = new WC_Order($id);
		if (is_object($order->post)) { se_wc_log_ok(sprintf(__('Callback order lookup matched WordPress post ID %s'), $order->post->ID)); }
		else { se_wc_log_error(sprintf(__('Callback order lookup failed for order %s'), $id)); }

		$comment_update = 'Shipping Tracking Number: ' . $tracking_number . '<br/> Carrier Key: ' . $carrier_key . '<br/> Carrier Service Key: ' . $carrier_service_key . '<br/> Cost: ' . $shipment_cost;
		$order->add_order_note($comment_update);

		// Support for Shipment Tracking plugin:
		if (function_exists('wc_st_add_tracking_number')) {

			// Note: Shipment Tracking plugin expects all-lowercase carrier IDs!
			$se_carrier_id = strtolower($carrier_key);

			$supported_carriers = array('usps', 'ups', 'fedex'); $custom_carriers = array();
			$custom_carriers['apc'] = array('APC', 'http://dm.mytracking.net/apc/dmportalv2/externaltracking.aspx?track='.$tracking_number);
			$custom_carriers['dhl_express'] = array('DHL Express', 'http://www.dhl.com/content/g0/en/express/tracking.shtml?AWB='.$tracking_number.'&brand=DHL');
			$custom_carriers['dhlgm'] = array('DHL eCommerce', 'http://webtrack.dhlglobalmail.com/?trackingnumber='.$tracking_number);
			$custom_carriers['globegistics'] = array('Globegistics', 'http://dm.mytracking.net/GLOBEGISTICS/track/TrackDetails.aspx?t='.$tracking_number);
			$custom_carriers['rrd'] = array('RR Donnelley', 'http://www.ppxtrack.com/t/parceltracking/'.$tracking_number);

			if (in_array($se_carrier_id, $supported_carriers)) {
				$shipping_provider = $se_carrier_id;
				$tracking_url = false;
			} else {
				if (isset($custom_carriers[$se_carrier_id])) {
					$shipping_provider = $custom_carriers[$se_carrier_id][0];
					$tracking_url = $custom_carriers[$se_carrier_id][1];
				} else {
					$shipping_provider = 'Other';
					$tracking_url = false;
				}
			}
			wc_st_add_tracking_number($id, $tracking_number, $shipping_provider, current_time('timestamp'), $tracking_url);

		}

		$status_update = $order->update_status('completed');
		if ($status_update) { se_wc_log_ok(sprintf(__('Order status updated to complete'))); }
		else { se_wc_log_error(sprintf(__('Order status not updated'), $values)); }

		$this->send_response('Order has been updated successfully ' . $comment_update, json_decode($pugs));

	}

	/** Response Handler
	* 	This sends a JSON response to the browser
	*/
	protected function send_response($msg, $pugs = '') {
		$response['message'] = $msg;
		header('content-type: application/json; charset=utf-8');
		echo json_encode($response) . "\n";
		exit;
	}

}

?>