<?php

/**
 * WF_USPS_Stamps class.
 *
 * @extends WC_Shipping_Method
 */
class WF_USPS_Stamps extends WC_Shipping_Method {
	private $domestic        = array( "US", "PR", "VI" );
	private $found_rates;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = WF_USPS_STAMPS_ID;
		$this->method_title       = __( 'Stamps.com - USPS (BASIC)', 'wf-usps-stamps-woocommerce' );
		$this->method_description = __( 'The <strong>Stamps.com USPS</strong> plugin obtains rates dynamically from the Stamps.com API during cart/checkout.', 'wf-usps-stamps-woocommerce' );
		$this->services           = include( 'data-wf-services.php' );
		$this->init();
	}

    /**
     * init function.
     *
     * @access public
     * @return void
     */
    private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->enabled                  = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : $this->enabled;
		$this->title                    = isset( $this->settings['title'] ) ? $this->settings['title'] : $this->method_title;
		$this->availability             = isset( $this->settings['availability'] ) ? $this->settings['availability'] : 'all';
		$this->countries                = isset( $this->settings['countries'] ) ? $this->settings['countries'] : array();
		$this->origin                   = isset( $this->settings['origin'] ) ? $this->settings['origin'] : '';
		$this->user_id                  = isset( $this->settings['user_id'] ) ? $this->settings['user_id'] : '';
		$this->password        			= isset( $this->settings['password'] ) ? $this->settings['password'] : '';
		$this->access_key      			= WF_USPS_STAMPS_ACCESS_KEY;
		$this->packing_method           = 'per_item';
		$this->custom_services          = isset( $this->settings['services'] ) ? $this->settings['services'] : array();
		$this->offer_rates              = isset( $this->settings['offer_rates'] ) ? $this->settings['offer_rates'] : 'all';
		$this->fallback                 = ! empty( $this->settings['fallback'] ) ? $this->settings['fallback'] : '';
		$this->mediamail_restriction    = isset( $this->settings['mediamail_restriction'] ) ? $this->settings['mediamail_restriction'] : array();
		$this->mediamail_restriction    = array_filter( (array) $this->mediamail_restriction );
		$this->enable_standard_services = true;
		$this->debug                    = isset( $this->settings['debug_mode'] ) && $this->settings['debug_mode'] == 'yes' ? true : false;

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );
	}

	/**
	 * environment_check function.
	 *
	 * @access public
	 * @return void
	 */
	private function environment_check() {
		global $woocommerce;

		$admin_page = version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ? 'wc-settings' : 'woocommerce_settings';

		if ( get_woocommerce_currency() != "USD" ) {
			echo '<div class="error">
				<p>' . sprintf( __( 'Stamps.com requires that the <a href="%s">currency</a> is set to US Dollars.', 'wf-usps-stamps-woocommerce' ), admin_url( 'admin.php?page=' . $admin_page . '&tab=general' ) ) . '</p>
			</div>';
		}
		elseif ( ! in_array( $woocommerce->countries->get_base_country(), $this->domestic ) ) {
			echo '<div class="error">
				<p>' . sprintf( __( 'Stamps.com requires that the <a href="%s">base country/region</a> is the United States.', 'wf-usps-stamps-woocommerce' ), admin_url( 'admin.php?page=' . $admin_page . '&tab=general' ) ) . '</p>
			</div>';
		}
		elseif ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'Stamps.com is enabled, but the origin postcode has not been set.', 'wf-usps-stamps-woocommerce' ) . '</p>
			</div>';
		}
		
		$error_message = '';
		
		// Check for Stamps.com User ID
		if ( ! $this->user_id && $this->enabled == 'yes' ) {
			$error_message .= '<p>' . __( 'Stamps.com is enabled, but the Stamps.com User ID has not been set.', 'wf-usps-stamps-woocommerce' ) . '</p>';
		}

		// Check for Stamps.com Password
		if ( ! $this->password && $this->enabled == 'yes' ) {
			$error_message .= '<p>' . __( 'Stamps.com is enabled, but the Stamps.com Password has not been set.', 'wf-usps-stamps-woocommerce' ) . '</p>';
		}
		
		if ( ! $error_message == '' ) {
			echo '<div class="error">';
			echo $error_message;
			echo '</div>';
		}
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		?>
		<div class="wf-banner updated below-h2">
  			<p class="main">
			<ul>
				<li style='color:red;'><strong>Your Business is precious! Go Premium!</li></strong>
				<li><strong>WooForce Stamps.com (USPS) Extension Premium version for WooCommerce streamlines your complete shipping process and saves time.</strong></li>
				<li><strong>- Timely compatibility updates and bug fixes.</strong ></li>
				<li><strong>- Premium Support:</strong> Faster and time bound response for support requests.</li>
				<li><strong>- More Features:</strong> Label Printing with Postage, Extended Package Types, Automatic Shipment Tracking, Box Packing, Weight based Packing and Many More..</li>
			</ul>
			</p>
			<p><a href="https://www.xadapter.com/product/woocommerce-stamps-com-shipping-plugin-with-usps-postage/" target="_blank" class="button button-primary">Upgrade to Premium Version</a> <a href="http://stampswoodemo.wooforce.com/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wf_usps_stamps" target="_blank" class="button">Live Demo</a></p>
		</div>
		<style>
		.wf-banner img {
			float: right;
			margin-left: 1em;
			padding: 15px 0
		}
		</style>
		<?php
		
		// Show settings
		parent::admin_options();
	}

	/**
	 * generate_services_html function.
	 */
	public function generate_services_html() {
		ob_start();
		include( 'html-wf-services.php' );
		return ob_get_clean();
	}

	/**
	 * validate_services_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['usps_service'];

		foreach ( $posted_services as $code => $settings ) {

			$services[ $code ] = array(
				'name'               => wc_clean( $settings['name'] ),
				'order'              => wc_clean( $settings['order'] )
			);

			foreach ( $this->services[$code]['services'] as $key => $name ) {
				$services[ $code ][ $key ]['enabled'] = isset( $settings[ $key ]['enabled'] ) ? true : false;
			}

		}

		return $services;
	}

	/**
	 * clear_transients function.
	 *
	 * @access public
	 * @return void
	 */
	public function clear_transients() {
		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_usps_quote_%') OR `option_name` LIKE ('_transient_timeout_usps_quote_%')" );
	}

    /**
     * init_form_fields function.
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
	    global $woocommerce;

	    $shipping_classes = array();
	    $classes = ( $classes = get_terms( 'product_shipping_class', array( 'hide_empty' => '0' ) ) ) ? $classes : array();

	    foreach ( $classes as $class )
	    	$shipping_classes[ $class->term_id ] = $class->name;
    	$this->form_fields  = array(
			'enabled'          			=> array(
				'title'           		=> __( 'Realtime Rates', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'checkbox',
				'label'           		=> __( 'Enable', 'wf-usps-stamps-woocommerce' ),
				'default'         		=> 'no'
			),
			'title'            			=> array(
				'title'           		=> __( 'Method Title', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'text',
				'description'     		=> __( 'This controls the title which the user sees during checkout.', 'wf-usps-stamps-woocommerce' ),
				'default'         		=> __( $this->method_title, 'wf-usps-stamps-woocommerce' ),
				'placeholder'       	=> __( $this->method_title, 'wf-usps-stamps-woocommerce' ),
			),
			'origin'           			=> array(
				'title'           		=> __( 'Origin Postcode', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'text',
				'description'     		=> __( 'Enter the postcode for the <strong>sender</strong>.', 'wf-usps-stamps-woocommerce' ),
				'default'         		=> ''
		    ),
		    'availability'  			=> array(
				'title'           		=> __( 'Method Available to', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'select',
				'default'         		=> 'all',
				'class'           		=> 'availability',
				'options'         		=> array(
					'all'            	=> __( 'All Countries', 'wf-usps-stamps-woocommerce' ),
					'specific'       	=> __( 'Specific Countries', 'wf-usps-stamps-woocommerce' ),
				),
			),
			'countries'        			=> array(
				'title'           		=> __( 'Specific Countries', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'multiselect',
				'class'           		=> 'chosen_select',
				'css'             		=> 'width: 450px;',
				'default'         		=> '',
				'options'         		=> $woocommerce->countries->get_allowed_countries(),
			),
		    'api'           			=> array(
				'title'           		=> __( 'API Settings:', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'title',
				'description'     		=> sprintf( __( 'You can obtain a Stamps.com user ID by %s.', 'wf-usps-stamps-woocommerce' ), '<a href="http://www.stamps.com/wooforce">' . __( 'signing up on the Stamps.com website', 'wf-usps-stamps-woocommerce' ) . '</a>' ),
		    ),
		    'user_id'           		=> array(
				'title'           		=> __( 'User ID', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'text',
				'description'     		=> __( 'Obtained from <a href="http://www.stamps.com/wooforce" target="_blank">Stamps.com</a> after getting an account.', 'wf-usps-stamps-woocommerce' ),
				'default'         		=> '',
		    ),
			'password'            		=> array(
				'title'           		=> __( 'Password', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'text',
				'description'     		=> __( 'Obtained from <a href="http://www.stamps.com/wooforce" target="_blank">Stamps.com</a> after getting an account.', 'wf-usps-stamps-woocommerce' ),
				'default'         		=> '',
		    ),
		    'debug_mode'  				=> array(
				'title'           		=> __( 'Debug', 'wf-usps-stamps-woocommerce' ),
				'label'           		=> __( 'Enable debug mode', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'checkbox',
				'default'         		=> 'no',
				'description'     		=> __( 'Enable debug mode to show debugging information on your cart/checkout. Not recommended to enable this in live site with traffic.', 'wf-usps-stamps-woocommerce' )
			),
		    'rates'           			=> array(
				'title'           		=> __( 'Rates:', 'wf-usps-stamps-woocommerce' ),
				'type'            		=> 'title',
				'description'     		=> __( 'The following settings determine the rates you offer your customers.', 'wf-usps-stamps-woocommerce' ),
		    ),
			'fallback' 					=> array(
				'title'       			=> __( 'Fallback', 'wf-usps-stamps-woocommerce' ),
				'type'        			=> 'text',
				'description' 			=> __( 'If Stamps.com returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'wf-usps-stamps-woocommerce' ),
				'default'     			=> ''
			),
			'services'  				=> array(
				'type'            		=> 'services'
			),
		);
    }

	public function get_stamps_authenticate_response() {
		$stamps_settings	= get_option( 'woocommerce_'.WF_USPS_STAMPS_ID.'_settings', null );
		$stamps_user_id     = isset( $stamps_settings['user_id'] ) ? $stamps_settings['user_id'] : '';
		$stamps_password    = isset( $stamps_settings['password'] ) ? $stamps_settings['password'] : '';
		$stamps_access_key  = WF_USPS_STAMPS_ACCESS_KEY;

		$request['Credentials'] =    array(
			'IntegrationID' => $stamps_access_key,
			'Username' => $stamps_user_id,
			'Password' => $stamps_password,
		);

		$client = new SoapClient( plugin_dir_path( dirname( __FILE__ ) ) . $this->get_stamps_endpoint(), array( 'trace' => 1, 'connection_timeout' => 10 ) );
		$result = $client->AuthenticateUser( $request );

		$this->debug( 'Stamps.com AUTH RESPONSE: <pre>' . print_r( $result, true ) . '</pre>' );

		return $result;
	}

	public function wf_build_obj_from_xml( $input_xml ) {
		$response_simple_xml 	= str_replace( "soap:Body", "soapBody", $input_xml );
		$response_simple_xml 	= str_replace( "soap:Envelope", "soapEnvelope", $response_simple_xml );
		$response_simple_xml 	= str_replace( "soap:Fault", "soapFault", $response_simple_xml );
		
		$response_obj 			= simplexml_load_string( '<root>' . preg_replace('/<\?xml.*\?>/','', $response_simple_xml ) . '</root>' );
		return $response_obj;
	}

	public function get_stamps_get_rates_response( $package_request, $stamps_authenticator ) {
		
		$request					= array();
		$request['Authenticator'] 	= $stamps_authenticator;
		$request['Rate'] 			= $package_request['Rate'];
		
		$this->debug( 'Stamps.com RATES REQUEST: <pre>' . print_r( $request, true ) . '</pre>' );
		
		$client = new SoapClient( plugin_dir_path( dirname( __FILE__ ) ) . $this->get_stamps_endpoint(), array( 'trace' => 1 ) );
		$result = $client->GetRates( $request );

		$this->debug( 'Stamps.com RATES RESPONSE: <pre>' . print_r( $result, true ) . '</pre>' );

		return $result;
	}

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
    	global $woocommerce;

		$this->rates               = array();
		$this->unpacked_item_costs = 0;
		$domestic                  = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

    	$this->debug( __( 'Stamps.com debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-usps-stamps-woocommerce' ) );

    	if ( $this->enable_standard_services ) {
			// Get cart package details and proceed with GetRates.
	    	$package_requests = $this->get_package_requests( $package );

	    	libxml_use_internal_errors( true );

	    	if ( $package_requests ) {

				$responses	= array();
				foreach ( $package_requests as $key => $package_request ) {
					// Authenticate with Stamps.com.
					$stamps_authenticator	= '';
					try {
						$response_obj			= $this->get_stamps_authenticate_response();
					} catch ( Exception $e ) {
						$this->debug( __('Stamps.com - Unable to Get Auth: ', 'wf-usps-stamps-woocommerce').$e->getMessage() );
						if ( WF_ADV_DEBUG_MODE == "on" ) { $this->debug( print_r( $e, true ) ); }
						return;
					}
					
					if( isset( $response_obj->Authenticator ) ) {
						$stamps_authenticator 	= $response_obj->Authenticator;
					}
					else {
						$this->debug( __('Stamps.com Unknown error while Auth.', 'wf-usps-stamps-woocommerce') );
						return;
					}
					
					// Get rates.
					try {
						$response = $this->get_stamps_get_rates_response( $package_request['request'], $stamps_authenticator );
			
						$response_ele				= array();
						$response_ele['response']	= $response;
						$response_ele['quantity'] 	= $package_request['quantity'];
						$responses[] 				= $response_ele;
					} catch ( Exception $e ) {
						$this->debug( __('Stamps.com - Unable to Get Rates: ', 'wf-usps-stamps-woocommerce').$e->getMessage() );
						if ( WF_ADV_DEBUG_MODE == "on" ) { $this->debug( print_r( $e, true ) ); }
						return false;
					}
				}

				$found_rates = array();
				
				foreach ( $responses as $response_ele ) 
				{
					$response_obj = $response_ele['response'];
					
					if( isset( $response_obj->Rates ) ) 
					{
						$stamps_rates	= $response_obj->Rates->Rate;

						foreach ( $stamps_rates as $stamps_rate ) 
						{
							$service_type = (string) $stamps_rate->ServiceType;
							
                            $service_name = (string) ( isset( $this->custom_services[ $service_type ]['name'] ) && !empty( $this->custom_services[ $service_type ]['name'] ) ) ? $this->custom_services[ $service_type ]['name'] : $this->services[$service_type]["name"];
							$total_amount = $response_ele['quantity'] * $stamps_rate->Amount;

							if( isset( $found_rates[$service_type] ) ) 
							{
								$found_rates[$service_type]['cost']		= $found_rates[$service_type]['cost'] + $total_amount;
							}
							else 
							{
								$found_rates[$service_type]['label']	= $service_name;
								$found_rates[$service_type]['cost']		= $total_amount;
							}
						}
					}
					else {
						$this->debug( __('Stamps.com - Unknown error while processing Rates.', 'wf-usps-stamps-woocommerce') );
						return;
					}
				}

				if( $found_rates ) {
					
					foreach ( $found_rates as $service_type => $found_rate ) {
						
						// Enabled check
						if ( isset( $this->custom_services[ $service_type ][ $service_type ] ) && empty( $this->custom_services[ $service_type ][ $service_type ]['enabled'] ) ) {
							continue;
						}
						
						$total_amount = $found_rate['cost'];
						// Cost adjustment %
						if ( ! empty( $this->custom_services[ $service_type ][ $service_type ]['adjustment_percent'] ) ) {
							$total_amount = $total_amount + ( $total_amount * ( floatval( $this->custom_services[ $service_type ][ $service_type ]['adjustment_percent'] ) / 100 ) );
						}

						// Cost adjustment
						if ( ! empty( $this->custom_services[ $service_type ][ $service_type ]['adjustment'] ) ) {
							$total_amount = $total_amount + floatval( $this->custom_services[ $service_type ][ $service_type ]['adjustment'] );
						}
						
						$rate = array(
							'id' 		=> (string)$this->id.':'.$service_type,
							'label' 	=> (string) $found_rate['label'],
							'cost' 		=> (string) $total_amount,
							'calc_tax' 	=> 'per_item',
						);
						
						// Register the rate
						$this->add_rate( $rate );
					}
				}
				// Fallback
				elseif ( $this->fallback ) {
					$this->add_rate( array(
						'id' 	=> $this->id . '_fallback',
						'label' => $this->title,
						'cost' 	=> $this->fallback,
						'sort'  => 0
					) );
				}
			}
		}
    }

    /**
     * prepare_rate function.
     *
     * @access private
     * @param mixed $rate_code
     * @param mixed $rate_id
     * @param mixed $rate_name
     * @param mixed $rate_cost
     * @return void
     */
    private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost ) {

	    // Name adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) )
			$rate_name = $this->custom_services[ $rate_code ]['name'];

		// Merging
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages = 1;
		}

		// Sort
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'       => $rate_id,
			'label'    => $rate_name,
			'cost'     => $rate_cost,
			'sort'     => $sort,
			'packages' => $packages
		);
    }

    /**
     * sort_rates function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
    }

    /**
     * get_request function.
     *
     * @access private
     * @return void
     */
	// WF - Changing function to public.
    public function get_package_requests( $package ) {

	    // Choose selected packing
		$this->packing_method = 'per_item';
    	switch ( $this->packing_method ) {
	    	case 'box_packing' :
	    		$requests = $this->box_shipping( $package );
	    	break;
	    	case 'per_item' :
	    	default :
	    		$requests = $this->per_item_shipping( $package );
	    	break;
    	}

    	return $requests;
    }

    /**
     * per_item_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function per_item_shipping( $package ) {
	    global $woocommerce;

	    $requests = array();
	    $domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

    	// Get weight of order
    	foreach ( $package['contents'] as $item_id => $values ) {

    		if ( ! $values['data']->needs_shipping() ) {
    			$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-usps-stamps-woocommerce' ), $item_id ) );
    			continue;
    		}

    		if ( ! $values['data']->get_weight() ) {
	    		$this->debug( sprintf( __( 'Product # is missing weight. Using 1lb.', 'wf-usps-stamps-woocommerce' ), $item_id ) );

	    		$weight = 1;
    		} else {
    			$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
    		}

    		$size   = 'REGULAR';

    		if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( wc_get_dimension( $values['data']->length, 'in' ), wc_get_dimension( $values['data']->height, 'in' ), wc_get_dimension( $values['data']->width, 'in' ) );

				sort( $dimensions );

				if ( max( $dimensions ) > 12 ) {
					$size   = 'LARGE';
				}

				$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
			} else {
				$dimensions = array( 0, 0, 0 );
				$girth      = 0;
			}

			$quantity = $values['quantity'];
			
			if ( 'LARGE' === $size ) {
				$rectangular_shaped  = 'true';
			} else {
				$rectangular_shaped  = 'false';
			}

			if ( $domestic ) {
				$request['Rate'] = array(
					'FromZIPCode'			=> str_replace( ' ', '', strtoupper( $this->origin ) ),
					'ToZIPCode'				=> strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ),
					'WeightLb'				=> floor( $weight ),
					'WeightOz'				=> number_format( ( $weight - floor( $weight ) ) * 16, 2 ),
					'PackageType'			=> 'Package',
					'Length'				=> $dimensions[2],
					'Width'					=> $dimensions[1],
					'Height'				=> $dimensions[0],
					'ShipDate'				=> date( "Y-m-d", ( current_time('timestamp') + (60 * 60 * 24) ) ),
					'InsuredValue'			=> '0',
					'RectangularShaped'		=> $rectangular_shaped
				);

			} else {
				$request['Rate'] = array(
					'FromZIPCode'			=> str_replace( ' ', '', strtoupper( $this->origin ) ),
					'ToZIPCode'				=> strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ),
					'ToCountry'				=> $package['destination']['country'],
					'Amount'				=> $values['data']->get_price(),
					'WeightLb'				=> floor( $weight ),
					'WeightOz'				=> number_format( ( $weight - floor( $weight ) ) * 16, 2 ),
					'PackageType'			=> 'Package',
					'Length'				=> $dimensions[2],
					'Width'					=> $dimensions[1],
					'Height'				=> $dimensions[0],
					'ShipDate'				=> date( "Y-m-d", ( current_time('timestamp') + (60 * 60 * 24) ) ),
					'InsuredValue'			=> '0',
					'RectangularShaped'		=> $rectangular_shaped
				);
			}

			$request_ele				= array();
			$request_ele['request'] 	= $request;
			$request_ele['quantity'] 	= $quantity;
			
			$requests[] = $request_ele;
    	}

		return $requests;
    }
	
    public function debug( $message, $type = 'notice' ) {
    	if ( $this->debug && !is_admin()) { //WF: is_admin check added.
    		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
    			wc_add_notice( $message, $type );
    		} else {
    			global $woocommerce;
    			$woocommerce->add_message( $message );
    		}
		}
    }

	function get_stamps_endpoint () 
	{
		$stamps_settings 	= get_option( 'woocommerce_'.WF_USPS_STAMPS_ID.'_settings', null ); 
		$api_mode      		= 'Live';

		if( 'Test' == $api_mode ) 
		{
			$stamps_uri = 'includes/wsdl/testing-swsimv45.wsdl';
		}
		else 
		{
			$stamps_uri = 'includes/wsdl/swsimv45.wsdl';
		}

		return $stamps_uri;
	}
}
