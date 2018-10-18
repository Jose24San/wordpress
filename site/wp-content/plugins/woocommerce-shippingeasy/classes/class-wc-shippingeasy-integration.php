<?php

if (!class_exists('WC_ShippingEasy_Integration')) {

	class WC_ShippingEasy_Integration extends WC_Integration {

		public function __construct() {

			global $woocommerce;

			$this->id = 'woocommerce-shippingeasy';
			$this->method_title = __( 'ShippingEasy', 'woocommerce-shippingeasy' );
			$this->method_description = sprintf( __( 'ShippingEasy provides the easiest shipping app for online sellers. Its cloud-based shipping solution offers the cheapest USPS postage rates, plus the ability to plug in UPS and FedEx accounts. For help with using ShippingEasy with WooCommerce, %1$sview the documentation%2$s.', 'woocommerce-shippingeasy' ), '<a href="' . esc_url( 'https://support.shippingeasy.com/hc/en-us/articles/204115175-How-to-Integrate-your-WooCommerce-store-with-ShippingEasy-step-by-step-guide-with-pictures-' ) . '">', '</a>' );

			$this->init_form_fields();
			$this->init_settings();

			$this->api_key = $this->get_option('api_key');
			$this->secret_key = $this->get_option('secret_key');
			$this->store_api = $this->get_option('store_api');
			$this->base_url = $this->get_option('base_url');
			$this->shippable_statuses = $this->get_option('shippable_statuses');
			$this->add_coupon_notes = $this->get_option('add_coupon_notes');
			$this->debug_enabled = $this->get_option('debug_enabled');

			add_action('woocommerce_update_options_integration_'.$this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_thankyou', array($this, 'shipping_place_order'));
			add_action('woocommerce_payment_complete', array($this, 'shipping_place_order'));
			add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'));
			add_action('woocommerce_order_actions', array($this, 'add_manual_ship_action'));
			add_action('woocommerce_order_action_se_send_to_shippingeasy', array($this, 'handle_manual_ship_action'));

		} // End __construct()

		public function get_se_option($option) {
			$return = self::get_option($option);
			return $return;
		}

		public function init_form_fields() {
			$se_statuses = se_get_order_statuses();
			$se_statuses_array = array();
			foreach ($se_statuses as $id => $status) {
				$se_statuses_array[$status->name] = $status->name;
			}

			$log_link = 'admin.php?page=wc-status&tab=logs';

			$this->form_fields = array(
				'api_key' => array(
					'title'       => __('API Key', 'woocommerce-shippingeasy'),
					'type'        => 'text',
					'default'     => '',
					'description' => sprintf( __( 'Your API key, found in your %1$sShippingEasy Account Settings%2$s.', 'woocommerce-shippingeasy' ), '<a href="' . esc_url( 'https://app.shippingeasy.com/settings/api_credentials' ) . '">', '</a>' ),
				),
				'secret_key' => array(
					'title'       => __('Secret Key', 'woocommerce-shippingeasy'),
					'type'        => 'text',
					'default'     => '',
					'description' => sprintf( __( 'Your secret key, found in your %1$sShippingEasy Account Settings%2$s.', 'woocommerce-shippingeasy' ), '<a href="' . esc_url( 'https://app.shippingeasy.com/settings/api_credentials' ) . '">', '</a>' ),
				),
				'store_api' => array(
					'title'       => __('Store API Key', 'woocommerce-shippingeasy'),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Your Store API key, found in the "Stores and Orders" section of your ShippingEasy account.', 'woocommerce-shippingeasy' ),
				),
				'base_url' => array(
					'title'       => __('API URL', 'woocommerce-shippingeasy'),
					'type'        => 'text',
					'default'     => 'https://app.shippingeasy.com',
					'description' => __( 'The URL to the ShippingEasy API. Adjust this only if instructed to do so.', 'woocommerce-shippingeasy' )
				),
				'shippable_statuses' => array(
					'title'       => __('Shippable Statuses', 'woocommerce-shippingeasy'),
					'type'        => 'multiselect',
					'css'         => 'width: 25em; height: 130px;',
					'options'     => $se_statuses_array
				),
				'add_coupon_notes' => array(
					'title'       => __('Coupon Codes', 'woocommerce-shippingeasy'),
					'description' => __('Append coupon codes to order notes?', 'woocommerce-shippingeasy'),
					'label'       => __('Include Coupon Codes', 'woocommerce-shippingeasy'),
					'type'        => 'checkbox',
					'default'     => 'no'
				),
				'debug_enabled' => array(
					'title'       => __('Debug Log', 'woocommerce-shippingeasy'),
					'description' => sprintf(__('Log errors and API requests in the <a href="%s">WooCommerce logs</a> area?', 'woocommerce-shippingeasy'), $log_link),
					'label'       => __('Debug Enabled', 'woocommerce-shippingeasy'),
					'type'        => 'checkbox',
					'default'     => 'no'
				)				
			);
		}

		public function shipping_place_order($order_id, $is_backend_order = false) {

			$filtered_order_id = se_get_filtered_order_id($order_id);
			$real_post_id = se_get_raw_order_id($filtered_order_id);

			$already_created = get_post_meta($real_post_id, 'se_order_created', true);
			if ($already_created == true) {
				return true;
			}

			global $wpdb;
			global $woocommerce;
			global $post;

			include_once(plugin_dir_path(__FILE__).'../lib/shipping_easy-php/lib/ShippingEasy.php');

			ShippingEasy::setApiKey($this->get_option('api_key'));
			ShippingEasy::setApiSecret($this->get_option('secret_key'));
			ShippingEasy::setApiBase($this->get_option('base_url'));

			$order = new WC_Order($order_id);

			if ($is_backend_order == false) {
				$export_ok = false;
				if (!in_array($order->status, $this->get_option('shippable_statuses'))) {
					//se_wc_log_ok(sprintf(__('Order #%d not sent to ShippingEasy as %s orders are not designated as shippable.'), $order_id, $order->status), false);
					return true;
				}
			}

			$download_total = 0;
			$downloads_subtotal = 0;

			$total_products = count($order->get_items());
			$check_virtual = array(); $check_virtuals = 0;
			foreach ($order->get_items() as $item) {
				$product_id = $item['product_id'];
				$post_meta = get_post_meta($item['product_id']);
				$check_virtual[] = $post_meta['_virtual'][0];
				if ($post_meta['_virtual'][0] == 'yes') {
					$download_total = $item['line_subtotal'];
					$downloads_subtotal += $download_total;
				}
			}

			if (in_array("yes", $check_virtual)) {
				$check_virtuals += 1;
			}

			$total_download_product = $check_virtuals;

			if ($total_products > $total_download_product) {

				$billing_company = $order->billing_company;
				$billing_first_name = $order->billing_first_name;
				$billing_last_name = $order->billing_last_name;
				$billing_address = $order->billing_address_1;
				$billing_address2 = $order->billing_address_2;
				$billing_city = $order->billing_city;
				$billing_state = $order->billing_state;
				$billing_postcode = $order->billing_postcode;
				$billing_country = $order->billing_country;
				$billing_email = $order->billing_email;
				$billing_phone = $order->billing_phone;
				$shipping_company = $order->shipping_company;
				$shipping_first_name = $order->shipping_first_name;
				$shipping_last_name = $order->shipping_last_name;
				$shipping_address = $order->shipping_address_1;
				$shipping_address2 = $order->shipping_address_2;
				$shipping_city = $order->shipping_city;
				$shipping_state = $order->shipping_state;
				$shipping_postcode = $order->shipping_postcode;
				$shipping_country = $order->shipping_country;
				$order_cart_total = $order->order_total;
				$order_totals = $order_cart_total - $downloads_subtotal;
				$order_total = $order_totals;
				$order_tax = $order->order_tax;
				$order_shipping = $order->order_shipping;
				$order_shipping_tax = $order->order_shipping_tax;
				$cart_discount = $order->cart_discount;

				// Shipping method variable moved in WC 2.1
				$shipping_method = '';
				if (property_exists($order, 'shipping_method')) { $shipping_method = $order->shipping_method; }
				elseif (method_exists($order, 'get_shipping_method')) { $shipping_method = $order->get_shipping_method(); }

				$item_qty = 0;
				$line_subtotal = 0;
				foreach ($order->get_items() as $item) {
					$item_qty++;
					$line_subtotal += $item['line_subtotal'];
				}

				$total_excluding_tax = $line_subtotal;
				$shipping_cost_including_tax = ($order_shipping + $order_shipping_tax);

				$post_excerpt = $wpdb->get_var("SELECT post_excerpt FROM $wpdb->posts WHERE ID = '$real_post_id'");

				if (strtoupper($this->get_option('add_coupon_notes')) == 'YES') {
					$coupons_used = $order->get_used_coupons();
					if (!empty($coupons_used)) {
						if (!empty($post_excerpt)) { $post_excerpt .= ' '; }
						$post_excerpt .= 'Coupon(s): ';
						$post_excerpt .= implode(', ', $coupons_used);
					}
				}

				$wc_version = se_wc_version();
				if ($wc_version['Major'] > 2 || ($wc_version['Major'] == 2 && $wc_version['Minor'] >= 6)) {
					$new_country_codes = array('AS','FM','GU','MH','MP','PR','PW','UM','VI');
					if (in_array(strtoupper($shipping_country), $new_country_codes)) {
						$shipping_state = $shipping_country;
						$shipping_country = 'US';
					}
				}

				$values = array(
					"ext_order_reference_id" => "$real_post_id",
					"external_order_identifier" => "$filtered_order_id",
					"ordered_at" => date('Y-m-d H:i:s', time()),
					"order_status" => "awaiting_shipment",
					"subtotal_including_tax" => "$order_total",
					"total_including_tax" => "$order_total",
					"total_excluding_tax" => "$total_excluding_tax",
					"discount_amount" => "$cart_discount",
					"coupon_discount" => "$cart_discount",
					"subtotal_including_tax" => "$order_total",
					"subtotal_excluding_tax" => "$total_excluding_tax",
					"subtotal_excluding_tax" => "$total_excluding_tax",
					"subtotal_tax" => "$order_tax",
					"total_tax" => "$order_tax",
					"base_shipping_cost" => "$order_shipping",
					"shipping_cost_including_tax" => "$shipping_cost_including_tax",
					"shipping_cost_excluding_tax" => "$order_shipping",
					"shipping_cost_tax" => "$order_shipping_tax",
					"base_handling_cost" => "0.00",
					"handling_cost_excluding_tax" => "0.00",
					"handling_cost_including_tax" => "0.00",
					"handling_cost_tax" => "0.00",
					"base_wrapping_cost" => "0.00",
					"wrapping_cost_excluding_tax" => "0.00",
					"wrapping_cost_including_tax" => "0.00",
					"wrapping_cost_tax" => "0.00",
					"notes" => "$post_excerpt",
					"billing_company" => "$billing_company",
					"billing_first_name" => "$billing_first_name",
					"billing_last_name" => "$billing_last_name",
					"billing_address" => "$billing_address",
					"billing_address2" => "$billing_address2",
					"billing_city" => "$billing_city",
					"billing_state" => "$billing_state",
					"billing_postal_code" => "$billing_postcode",
					"billing_country" => "$billing_country",
					"billing_phone_number" => "$billing_phone",
					"billing_email" => "$billing_email",
					"recipients" => array(
						array(
							"first_name" => "$shipping_first_name",
							"last_name" => "$shipping_last_name",
							"company" => "$shipping_company",
							"email" => "$billing_email",
							"phone_number" => "$billing_phone",
							"residential" => "true",
							"address" => "$shipping_address",
							"address2" => "$shipping_address2",
							"province" => "",
							"state" => "$shipping_state",
							"city" => "$shipping_city",
							"postal_code" => "$shipping_postcode",
							"postal_code_plus_4" => "",
							"country" => "$shipping_country",
							"shipping_method" => "$shipping_method",
							"base_cost" => "10.00",
							"cost_excluding_tax" => "10.00",
							"cost_tax" => "0.00",
							"base_handling_cost" => "0.00",
							"handling_cost_excluding_tax" => "0.00",
							"handling_cost_including_tax" => "0.00",
							"handling_cost_tax" => "0.00",
							"shipping_zone_id" => "123",
							"shipping_zone_name" => "XYZ",
							"items_total" => "$item_qty",
							"items_shipped" => "0",
							"line_items" => $this->shipping_order_detail($order_id)
						)
					)
				);

				try {
					$order = new ShippingEasy_Order($this->get_option('store_api'), $values);
					$response = $order->create();
					if (!empty($response['order']['id'])) {
						update_post_meta($real_post_id, 'se_order_created', true);
						se_wc_log_ok(sprintf(__('Submitted order: %s'), json_encode($values)));
					} else {
						$error_message = $this->get_option('base_url').' '.json_encode($values);
						se_wc_log_error(sprintf(__('Sending to ShippingEasy: %s'), $error_message));
					}
				} catch (Exception $e) {
					$error_message = $e->getMessage().' '.json_encode($values);
					se_wc_log_error(sprintf(__('Sending to ShippingEasy: %s'), $error_message));
				}

			}

		}

		public function shipping_order_detail($order_id) {
			$order_details = array();
			$order = wc_get_order($order_id);
			foreach ($order->get_items() as $item) {
				$product  = $order->get_product_from_item($item);
				if ($product && $product->needs_shipping()) {
					$item_name = $product->get_title();
					$item_sku = $product->get_sku();
					$item_price = $order->get_item_subtotal($item, false, true);
					$item_weight = wc_get_weight($product->get_weight(), 'oz');
					$item_qty = $item['qty'];
					$item_options = array();
					if ($item['item_meta']) {
						$item_meta = new WC_Order_Item_Meta($item);
						$formatted_meta = $item_meta->get_formatted();
						if (!empty($formatted_meta)) {
							foreach ($formatted_meta as $meta_key => $meta) {
								$formatted_meta_label = $meta['label'];
								$formatted_meta_value = $meta['value']; 
								$item_options[$formatted_meta_label] = $formatted_meta_value;						
							} 
						}
					}
					$order_details[] = array(
						"item_name" => $item_name,
						"sku" => $item_sku,
						"bin_picking_number" => '0',
						"unit_price" => $item_price,
						"total_excluding_tax" => $item_price,
						"weight_in_ounces" => $item_weight,
						"quantity" => $item_qty,
						"product_options" => $item_options
					);
				}
				$product_count = count($order_details);
				for ($i = 0; $i < $product_count; $i++) {
					if ($order_details[$i]['weight_in_ounces'] == 0) {
						unset($order_details[$i]['weight_in_ounces']);
					}
				}
			}
			return $order_details;
		}

		public function handle_order_status_change($order_id) {
			$order = new WC_Order($order_id);
			$filtered_order_id = se_get_filtered_order_id($order_id);
			if ($order->status == 'cancelled') {
				/* Handle cancellation */
				include_once(plugin_dir_path(__FILE__).'../lib/shipping_easy-php/lib/ShippingEasy.php');
				ShippingEasy::setApiKey($this->get_option('api_key'));
				ShippingEasy::setApiSecret($this->get_option('secret_key'));
				ShippingEasy::setApiBase($this->get_option('base_url'));
				try {
					$cancellation = new ShippingEasy_Cancellation($this->get_option('store_api'), "$filtered_order_id");
					$cancellation->create();
					se_wc_log_ok(sprintf(__('Order #%d successfully cancelled.'), $filtered_order_id));
				} catch (Exception $e) {
					se_wc_log_error(sprintf(__('Order #%d could not be cancelled.'), $filtered_order_id));
				}
			} else {
				/* Send to ShippingEasy if status has changed to a shippable status */
				if (in_array($order->status, $this->get_option('shippable_statuses'))) {
					$this->shipping_place_order($order_id, true);
				}
			}
		}

		public function add_manual_ship_action($actions) {
			$actions['se_send_to_shippingeasy'] = __('Send to ShippingEasy', 'woocommerce-shippingeasy');
			return $actions;
		}

		public function handle_manual_ship_action($order) {
			$this->shipping_place_order($order->id, true);
		}

	}

}

?>