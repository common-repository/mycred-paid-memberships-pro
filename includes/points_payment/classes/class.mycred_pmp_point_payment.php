<?php
	
	//load classes init method
	// add_action('init', array('PMProGateway_myCred', 'init'));

	/**
	 * PMProGateway_gatewayname Class
	 *
	 * Handles myCred integration.
	 *
	 */
	class PMProGateway_myCred extends PMProGateway
	{
		// function __construct($gateway = NULL)
		// {
		// 	$this->gateway = $gateway;
		// 	return $this->gateway;
		// }
		
		public function __construct($gateway = NULL){
			
			$this->init();

			$this->gateway = $gateway;
			return $this->gateway;
			
		}

		
		/**
		 * Run on WP init
		 *
		 * @since 1.8
		 */
		static function init()
		{

			//make sure myCred is a gateway option
			add_filter('pmpro_gateways', array('PMProGateway_myCred', 'pmpro_gateways'));

			//add fields to payment settings
			add_filter('pmpro_payment_options', array('PMProGateway_myCred', 'pmpro_payment_options'));
			add_filter('pmpro_payment_option_fields', array('PMProGateway_myCred', 'pmpro_payment_option_fields'), 10, 2);

			//add some fields to edit user page (Updates)
			add_action('pmpro_after_membership_level_profile_fields', array('PMProGateway_myCred', 'user_profile_fields'));
			add_action('profile_update', array('PMProGateway_myCred', 'user_profile_fields_save'));

			//updates cron
			add_action('pmpro_activation', array('PMProGateway_myCred', 'pmpro_activation'));
			add_action('pmpro_deactivation', array('PMProGateway_myCred', 'pmpro_deactivation'));
			
			add_action('pmpro_cron_myCred_subscription_updates', array('PMProGateway_myCred', 'pmpro_cron_myCred_subscription_updates'));
			
			add_action('pmpro_myCred_subscription_charges', array('PMProGateway_myCred', 'pmpro_myCred_subscription_charges'),10,2);


			/**For Expiration */
			add_action( 'pmpro_membership_post_membership_expiry', array('PMProGateway_myCred','cancel_membership'), 10, 2 );


			//code to add at checkout if example is the current gateway
			$gateway = pmpro_getOption("gateway");
			if($gateway == "myCred")
			{
				// add_action('pmpro_checkout_preheader', array('PMProGateway_myCred', 'pmpro_checkout_preheader'));
				add_filter('pmpro_checkout_order', array('PMProGateway_myCred', 'pmpro_checkout_order'));
				
				// add_filter('pmpro_include_billing_address_fields', array('PMProGateway_myCred', 'pmpro_include_billing_address_fields'));
				// add_filter('pmpro_include_cardtype_field', array('PMProGateway_myCred', 'pmpro_include_billing_address_fields'));
				
				add_filter('pmpro_include_payment_information_fields', array('PMProGateway_myCred', 'pmpro_include_payment_information_fields'));

				add_filter('pmpro_required_billing_fields', array('PMProGateway_myCred', 'pmpro_required_billing_fields'));

				// add_action( 'pmpro_after_checkout', array('PMProGateway_myCred', 'pmpro_after_checkout_callback'), 10, 2 );



			}
		}

		// static function pmpro_after_checkout_callback($user_id, $morder){

		// 	$args = get_user_meta( $user_id, 'pmpro_mycred_order' , true);
			
		// 	$args[$morder->membership_id]["order_id"] = $morder->id;
			
		// 	update_user_meta($user_id,'pmpro_mycred_order',$args);


		// }

		/**
		 * Make sure myCred is in the gateways list
		 *
		 * @since 1.8
		 */
		static function pmpro_gateways($gateways)
		{
			if(empty($gateways['myCred']))
				$gateways['myCred'] = __('myCred', 'myCred_pmp');

			return $gateways;
		}

		/**
		 * Get a list of payment options that the myCred gateway needs/supports.
		 *
		 * @since 1.8
		 */
		static function getGatewayOptions()
		{
			$options = array(
				// 'sslseal',
				// 'nuclear_HTTPS',
				// // 'gateway_environment',
				// 'currency',
				// 'use_ssl',
				// 'tax_state',
				// 'tax_rate',
				// 'accepted_credit_cards',
				// 'creditcards',

				'mycred_title',
				'mycred_custom_message',
				'mycred_log_template',
				'mycred_point_type',
				'mycred_exchange_rate',
				'mycred_show_total',
				'mycred_total_label',
				'mycred_balance_label'

			);

			return $options;
		}

		/**
		 * Set payment options for payment settings page.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_options($options)
		{
			//get myCred options
			$myCred_options = PMProGateway_myCred::getGatewayOptions();

			//merge with others.
			$options = array_merge($myCred_options, $options);

			return $options;
		}

		/**
		 * Display fields for myCred options.
		 *
		 * @since 1.8
		 */
		static function pmpro_payment_option_fields($values, $gateway)
		{
		?>
		<tr class="pmpro_settings_divider gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<td colspan="2">
				<hr>
				<h2 class="title">
					<?php esc_html_e('myCred Settings', 'myCred_pmp'); ?>
				</h2>
			</td>
		</tr>
		

		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_title"><?php esc_html_e('Title', 'myCred_pmp'); ?></label>
            </th>
            <td>
                <input type="text" id="mycred_title" name="mycred_title" value="<?php echo esc_attr($values['mycred_title'])?>" class="regular-text code">
			</td>
		</tr>
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_custom_message"><?php esc_html_e('Custom Message:', 'myCred_pmp'); ?></label>
            </th>
            <td>
				<textarea id="mycred_custom_message" name="mycred_custom_message" rows="3" cols="50" class="regular-text"><?php echo esc_attr( ( $values['mycred_custom_message'] ) ); ?></textarea>
			</td>
		</tr>


		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_log_template"><?php esc_html_e('Log Template:', 'myCred_pmp'); ?></label>
            </th>
            <td>
                <input type="text" id="mycred_log_template" name="mycred_log_template" value="<?php echo esc_attr($values['mycred_log_template'])?>" class="regular-text code">
			</td>
		</tr>
		
		
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_point_type"><?php esc_html_e('Point Type:', 'myCred_pmp'); ?></label>
            </th>
            <td>
				<select id="mycred_point_type" name="mycred_point_type">
					<?php 
		
					$point_type = mycred_get_types();
					foreach ($point_type as $point_slug => $point_name) {
						?>
						
						<option value="<?php esc_html_e($point_slug, 'myCred_pmp'); ?>" <?php echo ( (esc_attr($values['mycred_point_type']) == $point_slug)?'selected':''); ?> ><?php esc_html_e($point_name, 'myCred_pmp'); ?></option>
						
						<?php
					}	
					
					?>
                </select>
			</td>
		</tr>
		
		
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_exchange_rate"><?php esc_html_e('Exchange Rate:', 'myCred_pmp'); ?></label>
            </th>
            <td>
                <input type="text" id="mycred_exchange_rate" name="mycred_exchange_rate" value="<?php echo esc_attr($values['mycred_exchange_rate'])?>" class="regular-text code">
			</td>
		</tr>
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_show_total"><?php esc_html_e('Show Total:', 'myCred_pmp'); ?></label>
            </th>
            <td>
				<select id="mycred_show_total" name="mycred_show_total">
					
					<option value="0" <?php echo ( (esc_attr($values['mycred_show_total']) == '0') ? 'selected':'' ); ?> > <?php esc_html_e('Do not show', 'myCred_pmp'); ?></option>
					<option value="1" <?php echo ( (esc_attr($values['mycred_show_total']) == '1') ? 'selected':'' ); ?> > <?php esc_html_e('Show on Membership Checkout', 'myCred_pmp'); ?></option>

                </select>
			</td>
		</tr>
		
		
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_total_label"><?php esc_html_e('Exchange Label:', 'myCred_pmp'); ?></label>
            </th>
            <td>
                <input type="text" id="mycred_total_label" name="mycred_total_label" value="<?php echo esc_attr($values['mycred_total_label'])?>" class="regular-text code">
			</td>
		</tr>
		
		
		<tr class="gateway gateway_myCred" <?php if($gateway != "myCred") { ?>style="display: none;"<?php } ?>>
			<?php // myCred custom pamyment settings here ?>
			<th scope="row" valign="top">
                <label for="mycred_balance_label"><?php esc_html_e('Balance Label:', 'myCred_pmp'); ?></label>
            </th>
            <td>
                <input type="text" id="mycred_balance_label" name="mycred_balance_label" value="<?php echo esc_attr($values['mycred_balance_label'])?>" class="regular-text code">
			</td>
		</tr>
		
		
		
		
		
		
		
		
		
		
		
		
		
		<?php
		}

		/**
		 * Filtering orders at checkout.
		 *
		 * @since 1.8
		 */
		static function pmpro_checkout_order($morder)
		{
			return $morder;
		}

		/**
		 * Code to run after checkout
		 *
		 * @since 1.8
		 */
		static function pmpro_after_checkout($user_id, $morder)
		{
		}
		
		/**
		 * Use our own payment fields at checkout. (Remove the name attributes.)		
		 * @since 1.8
		 */
		static function pmpro_include_payment_information_fields($include){
			?>
			<div id="pmpro_payment_information_fields" class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_checkout', 'pmpro_payment_information_fields' )); ?>" >
				
				<h3>
					<span class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_checkout-h3-name' )); ?>"><?php esc_html_e( 'Payment Information', 'paid-memberships-pro' ); ?></span>
				</h3>
				<?php $sslseal = pmpro_getOption( "sslseal" ); ?>
				<?php if ( ! empty( $sslseal ) ) { ?>
				<div class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_checkout-fields-display-seal' )); ?>">
					<?php } ?>
			
			
				<div class="pmpro_checkout-fields<?php if ( ! empty( $sslseal ) ) { ?> pmpro_checkout-fields-leftcol<?php } ?>">

					<h3 class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_mycred_title' )); ?>"><?php esc_html_e( pmpro_getOption("mycred_title"), 'myCred_pmp' ); ?></h3>
					
					<div class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_mycred_custom_message' )) ?>"><?php esc_html_e( wpautop(wp_unslash( pmpro_getOption("mycred_custom_message") )), 'myCred_pmp' ) ?></div>

					<div class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_mycred_balance_label' )) ?>">
						
						<h3><?php esc_html_e( pmpro_getOption("mycred_balance_label"), 'myCred_pmp' ); ?></h3>
						
						<?php 
							
							$point_type = pmpro_getOption("mycred_point_type");
							$user_balance = mycred_get_users_balance(get_current_user_id(), $point_type);
							$exchange_rate = pmpro_getOption("mycred_exchange_rate");

							$membership_price = pmpro_getLevelAtCheckout()->initial_payment ;
							$membership_price = number_format($membership_price,2,'.',',');	/**This is String of Price */
						
							/**Total Deductible Amount in Store Currency */
							$deductible_amount = $user_balance * $exchange_rate;

							if($user_balance == '1' || $user_balance == '0'){
								$point_name = mycred_get_point_type_name($point_type,true) ;
							}
							else{$point_name = mycred_get_point_type_name($point_type,false) ;}

							// echo $point_name;
							// wp_die();
						?>
						
						<p><?php echo esc_html($point_name). ' = ' .esc_html($user_balance) ; ?></p>
					
					</div>

					<div class="<?php echo esc_attr(pmpro_get_element_class( 'pmpro_mycred_exchange_rate' )) ?>">
				
						<h4 class="pmpro_mycred_exchange_rate_heading"><?php esc_html_e("Exchange Rate", "myCred_pmp") ?></h4>
						<p class="pmpro_mycred_exchange_rate_details">1 <?php echo esc_html(mycred_get_point_type_name($point_type,true)). " = ". esc_html(function_exists('get_woocommerce_currency_symbol')?get_woocommerce_currency_symbol():'$') . esc_html($exchange_rate) ?></p>

					</div>
					
				
					
					
					
					<?php if(pmpro_getOption("mycred_show_total")){	/**If Show Total Option is Enabled */
					?>
						
						<div class="pmpro_mycred_total_amount">
											
						<h4 class="pmpro_mycred_total_heading"><?php esc_html_e( pmpro_getOption("mycred_total_label"), 'myCred_pmp' ); ?></h4>
						<p class="pmpro_mycred_total_details"><?php echo esc_html(function_exists('get_woocommerce_currency_symbol')?get_woocommerce_currency_symbol():'$').esc_html($deductible_amount) ?></p>

						</div>


					<?php } ?>
					
					


					<?php	/**Low Points Warning */
					
						if($deductible_amount < $membership_price){

							?>
								<div class="pmpro_mycred_low_alert" style="color:red">
								<p><?php esc_html_e("Alert!","myCred_pmp"); ?>
								<br><?php echo sprintf( __( "You don't have enough %s to purchase this membership", "myCred_pmp" ), $point_name); ?></p>
								</div>

							
							<?php

						}

					?>


					</div> <!-- end pmpro_checkout-fields -->
					<?php if ( ! empty( $sslseal ) ) { ?>
					<div class="<?php echo esc_html(pmpro_get_element_class( 'pmpro_checkout-fields-rightcol pmpro_sslseal', 'pmpro_sslseal' )); ?>"><?php echo esc_html(stripslashes( $sslseal )); ?></div>
				</div> <!-- end pmpro_checkout-fields-display-seal -->
			<?php } ?>
			</div> <!-- end pmpro_payment_information_fields -->
			<?php

			//don't include the default
			return false;
		}



		/**
		 * Remove required billing fields
		 *		 
		 * @since 1.8
		 */
		static function pmpro_required_billing_fields($fields)
		{
			// unset($fields['bfirstname']);
			// unset($fields['blastname']);
			// unset($fields['baddress1']);
			// unset($fields['bcity']);
			// unset($fields['bstate']);
			// unset($fields['bzipcode']);
			// unset($fields['bphone']);
			// unset($fields['bemail']);
			// unset($fields['bcountry']);
			unset($fields['CardType']);
			unset($fields['AccountNumber']);
			unset($fields['ExpirationMonth']);
			unset($fields['ExpirationYear']);
			unset($fields['CVV']);
			
			return $fields;
		}


		/**
		 * Fields shown on edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields($user)
		{
		}

		/**
		 * Process fields from the edit user page
		 *
		 * @since 1.8
		 */
		static function user_profile_fields_save($user_id)
		{
		}

		/**
		 * Cron activation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_activation()
		{
			wp_schedule_event(time(), 'daily', 'pmpro_cron_myCred_subscription_updates');
		}

		/**
		 * Cron deactivation for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_deactivation()
		{
			wp_clear_scheduled_hook('pmpro_cron_myCred_subscription_updates');
		}

		/**
		 * Cron job for subscription updates.
		 *
		 * @since 1.8
		 */
		static function pmpro_cron_myCred_subscription_updates()
		{
		}


		// static function pmpro_myCred_subscription_charges($membership_id, $mycred_point_type, $mycred_deduct_subscription_points, $user_id){
		static function pmpro_myCred_subscription_charges($user_id, $membership_id){
			
			$order_details = get_user_meta($user_id,'pmpro_mycred_order', true);

			if(!isset($order_details) || empty($order_details) || !isset($order_details[$membership_id])){
				return false;
			}

			$mycred_deduct_subscription_points = $order_details[$membership_id]['mycred_deduct_subscription_points'];
			$mycred_deduct_subscription_points_trial = $order_details[$membership_id]['mycred_deduct_subscription_points_trial'];
			$mycred_point_type = $order_details[$membership_id]['mycred_point_type'];
			$trial_count = (isset($order_details[$membership_id]['trial_count'])?$order_details[$membership_id]['trial_count']:0);

			$user_balance = mycred_get_users_balance($user_id, $mycred_point_type);

			
			// code to setup a recurring subscription with the gateway and test results would go here
			if($user_balance < $mycred_deduct_subscription_points){
				
				self::cancel_membership($user_id, $membership_id);

				return false;
			}

			if($mycred_deduct_subscription_points <=0 && $mycred_deduct_subscription_points_trial <=0){
				return false;
			}

			if($trial_count == 0 && $mycred_deduct_subscription_points > 0){	/**If trial period has ended, if there ever was */
				
				$transaction = 	mycred_subtract(
					'mycred_pmp_fee',
					$user_id,
					$mycred_deduct_subscription_points,
					pmpro_getOption("mycred_log_template"),
					'',
					'0',
					$mycred_point_type
				);

				return;
			}
			
			else{
				/**Its's Trial period and deduct points accordingly */
			
				$transaction = 	mycred_subtract(
					'mycred_pmp_fee',
					$user_id,
					$mycred_deduct_subscription_points_trial,
					pmpro_getOption("mycred_log_template"),
					'',
					'0',
					$mycred_point_type
				);
				
				
				$order_details[$membership_id]['trial_count'] = $trial_count - 1;

				update_user_meta( $user_id, 'pmpro_mycred_order', $order_details );

				return;
				
			}

			
		}

		
		function process(&$order)
		{
		
			$order->point_type = pmpro_getOption("mycred_point_type");
			$order->user_balance = mycred_get_users_balance(get_current_user_id(), $order->point_type);
			$order->exchange_rate = pmpro_getOption("mycred_exchange_rate");
			
			$membership_price = pmpro_getLevelAtCheckout()->initial_payment ;
			$membership_price = number_format($membership_price,2,'.',',');	/**This is String of Price */
			
			/**Total Deductible Amount in Store Currency */
			$order->deductible_amount = $order->user_balance * $order->exchange_rate;

			if($order->deductible_amount < $membership_price){

				/** Throw Error */
				$order->error = __("Not Enough Points: Payment failed.", "myCred_pmp");
				return false;

			}
			else{	/**Points for initial Amount */
				$order->mycred_deduct_points = $order->user_balance - (($order->deductible_amount - $membership_price) / (($order->exchange_rate==0)?1:$order->exchange_rate));
				$order->mycred_point_type = $order->point_type;
			}

			
			//check for initial payment
			if(floatval($order->InitialPayment) == 0){

				//auth first, then process
				if($this->authorize($order))
				{						
					$this->void($order);										
					if(!pmpro_isLevelTrial($order->membership_level))
					{
						//subscription will start today with a 1 period trial (initial payment charged separately)
						$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";
						$order->TrialBillingPeriod = $order->BillingPeriod;
						$order->TrialBillingFrequency = $order->BillingFrequency;													
						$order->TrialBillingCycles = 1;
						$order->TrialAmount = 0;
						
						//add a billing cycle to make up for the trial, if applicable
						if(!empty($order->TotalBillingCycles))
							$order->TotalBillingCycles++;
					}
					elseif($order->InitialPayment == 0 && $order->TrialAmount == 0)
					{
						//it has a trial, but the amount is the same as the initial payment, so we can squeeze it in there
						$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";														
						$order->TrialBillingCycles++;
						
						//add a billing cycle to make up for the trial, if applicable
						if($order->TotalBillingCycles)
							$order->TotalBillingCycles++;
					}
					else
					{
						//add a period to the start date to account for the initial payment
						$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod, current_time("timestamp"))) . "T0:0:0";
					}
					
					$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);
					return $this->subscribe($order);
				}
				else
				{
					if(empty($order->error))
						$order->error = __("Unknown error: Authorization failed.", "myCred_pmp");
					return false;
				}
			}
			
			else{

				//charge first payment
				if($this->charge($order))
				{							
					//set up recurring billing					
					if(pmpro_isLevelRecurring($order->membership_level))
					{						
						if(!pmpro_isLevelTrial($order->membership_level))
						{
							//subscription will start today with a 1 period trial
							$order->ProfileStartDate = date_i18n("Y-m-d") . "T0:0:0";
							$order->TrialBillingPeriod = $order->BillingPeriod;
							$order->TrialBillingFrequency = $order->BillingFrequency;													
							$order->TrialBillingCycles = 1;
							$order->TrialAmount = 0;
							
							//add a billing cycle to make up for the trial, if applicable
							if(!empty($order->TotalBillingCycles))
								$order->TotalBillingCycles++;
						}
						elseif($order->InitialPayment == 0 && $order->TrialAmount == 0)
						{
							//it has a trial, but the amount is the same as the initial payment, so we can squeeze it in there
							$order->ProfileStartDate = date_i18n("Y-m-d") . "T0:0:0";														
							$order->TrialBillingCycles++;
							
							//add a billing cycle to make up for the trial, if applicable
							if(!empty($order->TotalBillingCycles))
								$order->TotalBillingCycles++;
						}
						else
						{
							//add a period to the start date to account for the initial payment
							$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod, current_time("timestamp"))) . "T0:0:0";
						}
						
						$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);
						if($this->subscribe($order))
						{
							$order->status = apply_filters("pmpro_mycred_status_after_checkout", __("success","myCred_pmp") );	//saved on checkout page
							return true;
						}
						else
						{
							if($this->void($order))
							{
								if(!$order->error)
									$order->error = __("Unknown error: Payment failed.", "myCred_pmp");
							}
							else
							{
								if(!$order->error)
									$order->error = __("Unknown error: Payment failed.", "myCred_pmp");
								
								$order->error .= " " . __("A partial payment was made that we could not void. Please contact the site owner immediately to correct this.", "myCred_pmp");
							}
							
							return false;								
						}
					}
					else
					{
						//only a one time charge
						$order->status = apply_filters("pmpro_mycred_status_after_checkout", __("success","myCred_pmp") );	//saved on checkout page											
						return true;
					}
				}
				else
				{
					if(empty($order->error))
						$order->error = __("Unknown error: Payment failed.", "myCred_pmp");
					
					return false;
				}	
			}
			
			


		}
		
		/*
			Run an authorization at the gateway.

			Required if supporting recurring subscriptions
			since we'll authorize $1 for subscriptions
			with a $0 initial payment.
		*/
		function authorize(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//code to authorize with gateway and test results would go here

			//simulate a successful authorization
			$order->payment_transaction_id = "MYCRED" . $order->code;
			$order->updateStatus("authorized");													
			return true;					
		}
		
		/*
			Void a transaction at the gateway.

			Required if supporting recurring transactions
			as we void the authorization test on subs
			with a $0 initial payment and void the initial
			payment if subscription setup fails.
		*/
		function void(&$order)
		{
			//need a transaction id
			if(empty($order->payment_transaction_id))
				return false;
			
			//code to void an order at the gateway and test results would go here

			//simulate a successful void
			$order->payment_transaction_id = "MYCRED" . $order->code;
			$order->updateStatus("voided");					
			return true;
		}	
		
		/*
			Make a charge at the gateway.

			Required to charge initial payments.
		*/
		function charge(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//code to charge with gateway and test results would go here

			mycred_subtract(
				
				'mycred_pmp_fee',							/**Refrence */
				get_current_user_id(  ),					/**User ID */
				$order->mycred_deduct_points,				/**Amount */
				pmpro_getOption("mycred_log_template"),		/**entry */
				'',											/**Optional reference ID to save with the log entry. */
				'0',										/**Optional data to save with the log entry */
				$order->mycred_point_type					/**Point Type Key*/

			);

			//simulate a successful charge
			$order->payment_transaction_id = "MYCRED" . $order->code;
			$order->updateStatus("success");					
			return true;						
		}
		
		/*
			Setup a subscription at the gateway.

			Required if supporting recurring subscriptions.
		*/
		function subscribe(&$order)
		{

		
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//filter order before subscription. use with care.
			$order = apply_filters("pmpro_subscribe_order", $order, $this);


			

			if(isset($order->TrialAmount) && $order->TrialAmount > 0){	/**Points for Trial Amount */

				$order->mycred_deduct_subscription_points_trial = $order->user_balance - (($order->deductible_amount - $order->TrialAmount) / (($order->exchange_rate==0)?1:$order->exchange_rate));

				$args[$order->membership_id]['trial_count'] = $order->TrialBillingCycles;

			}
			if(isset($order->subscription_amount) && $order->subscription_amount > 0){	/**Points for Subscription amount */

				$order->mycred_deduct_subscription_points = $order->user_balance - (($order->deductible_amount - $order->subscription_amount) / (($order->exchange_rate==0)?1:$order->exchange_rate));

			}


			if(isset($order->mycred_deduct_subscription_points) || isset($order->mycred_deduct_subscription_points_trial)){	/*IF there is any subscription charges, register cron job only*/
				
				$user_id = get_current_user_id();
				// $args = get_user_meta( $user_id, 'pmpro_mycred_order', true );
				$args[$order->membership_id]['mycred_point_type'] = $order->mycred_point_type;
				$args[$order->membership_id]['mycred_deduct_subscription_points'] = (isset($order->mycred_deduct_subscription_points)?$order->mycred_deduct_subscription_points:0);
				$args[$order->membership_id]['mycred_deduct_subscription_points_trial'] = (isset($order->mycred_deduct_subscription_points_trial)?$order->mycred_deduct_subscription_points_trial:0);

				update_user_meta($user_id,'pmpro_mycred_order',$args);

				

				if($order->BillingPeriod == "Year"){
					$first_occurance =  (365*86400)/$order->BillingFrequency;	//annual
					$occurance = "yearly";
				}
				elseif($order->BillingPeriod == "Day"){
					$first_occurance =  (86400)/$order->BillingFrequency;		//daily
					$occurance = "daily";
				}
				elseif($order->BillingPeriod == "Week"){
					$first_occurance =  (86400*7)/$order->BillingFrequency;		//weekly
					$occurance = "weekly";
				}
				else{
					$first_occurance =  (86400*30)/$order->BillingFrequency;	//assume monthly
					$occurance = "monthly";
				}


				if($order->membership_level->trial_amount == 0){
					$trial_period = strtotime($order->membership_level->trial_limit . " " . $order->BillingPeriod);
					$trial_period = $trial_period-time();
				}

				if(!isset($trial_period) || $trial_period <= 0) $trial_period = 0;
				
				
				if ( ! wp_next_scheduled( 'pmpro_myCred_subscription_charges', array($user_id, $order->membership_id) ) ) {
					wp_schedule_event( ($trial_period) + (time() + $first_occurance), $occurance, 'pmpro_myCred_subscription_charges', array($user_id, $order->membership_id));
				}		
			}	

								
			//simulate a successful subscription processing
			$order->status = "success";		
			$order->subscription_transaction_id = "MYCRED" . $order->code;				
			return true;
		}	
		
		/*
			Update billing at the gateway.

			Required if supporting recurring subscriptions and
			processing credit cards on site.
		*/
		function update(&$order)
		{
			//code to update billing info on a recurring subscription at the gateway and test results would go here

			//simulate a successful billing update
			return true;
		}
		
		/*
			Cancel a subscription at the gateway.

			Required if supporting recurring subscriptions.
		*/
		function cancel(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//code to cancel a subscription at the gateway and test results would go here
			$user_id = get_current_user_id();
			$membership_id = $order->membership_id;
			
			self::cancel_membership($user_id, $membership_id);


			//simulate a successful cancel			
			$order->updateStatus("cancelled");					
			return true;
		}	
		
		/*
			Get subscription status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getSubscriptionStatus(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//code to get subscription status at the gateway and test results would go here

			//this looks different for each gateway, but generally an array of some sort
			return array();
		}

		/*
			Get transaction status at the gateway.

			Optional if you have code that needs this or
			want to support addons that use this.
		*/
		function getTransactionStatus(&$order)
		{			
			//code to get transaction status at the gateway and test results would go here

			//this looks different for each gateway, but generally an array of some sort
			return array();
		}

		static function cancel_membership($user_id, $membership_id){
			
			$args = get_user_meta( $user_id, 'pmpro_mycred_order', true );

			if(isset($args[$membership_id])){
				unset($args[$membership_id]);

				update_user_meta( $user_id, 'pmpro_mycred_order', $args );
			}
			$timestamp = wp_next_scheduled( 'pmpro_myCred_subscription_charges' , array($user_id, $membership_id));
						
			wp_unschedule_event( $timestamp, 'pmpro_myCred_subscription_charges',  array($user_id, $membership_id));
		}
	}

new PMProGateway_myCred();
