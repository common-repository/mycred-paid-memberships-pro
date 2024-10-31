<?php
if ( ! defined( 'MYCRED_PMP_SLUG' ) ) exit;

/**
* myCRED_Addons_Module class
**/
if ( ! class_exists( 'myCred_renew_membership_hook' ) ) :
	class myCred_renew_membership_hook extends myCRED_Hook {

		/**
		* Construct
		**/
		function __construct( $hook_prefs, $type = MYCRED_DEFAULT_TYPE_KEY ) {
			parent::__construct( array(
				'id'       => 'mycred_pmp_renew_membership',
				'defaults' => array(
					'creds'    => array(),
					'limit'    => array(),
					'log'      => array(),
					'type' 	   => $type,
					'pmp_form_id'  => array(),
				)
			), $hook_prefs, $type );
		}

		/**
		* Run Function
		**/
		public function run() {
			/* add_action( 'pmpro_after_checkout', array($this, 'myCred_renew_membership_save_entry'),10,2); */
			add_action( 'pmpro_after_checkout', array($this, 'is_order_renewal'),10,2);
		}
		
		
		public function  is_order_renewal($user_id,$order){
			
			if($this->my_pmpro_is_order_renewal($order))
				$this->myCred_renew_membership_save_entry($user_id,$order);
			else
				return "No";
			 
		}
		
		
		
		public function my_pmpro_is_order_renewal( $order ) {
			global $wpdb;
			//check for earlier orders with the same user_id and membership_id
			$sqlQuery = "SELECT id FROM $wpdb->pmpro_membership_orders WHERE 
					user_id = '" . esc_sql($order->user_id) . "' AND 
					membership_id = '" . esc_sql($order->membership_id) . "' AND 
					id <> '" . esc_sql($order->id) . "' AND
					timestamp < '" . date("Y-m-d H:i:s", $order->timestamp) . "' 
					LIMIT 1";

			$earlier_order = $wpdb->get_var($sqlQuery);				
			if(empty($earlier_order))
				return false;

			//must be recurring
			return true;
		}
		
		
		/** 
		*	myCred save entry
		**/
		public function myCred_renew_membership_save_entry($user_id, $order){
				
			if( $order->status == 'success' ){	
				$give_form_title = $order->membership_name;
				$form_id = $order->membership_id;
				$pmp_form_id = $this->prefs['pmp_form_id'];
				$ref_type  = array( 'ref_type' => 'post');
				
				
				// Make sure user is not excluded
				if(is_user_logged_in()){
					$user = wp_get_current_user();
					$user_id = $user->ID;
					if ( ! $this->core->exclude_user( $user_id ) ) {
						
						if(!empty($pmp_form_id)):
							foreach($pmp_form_id as $key => $val):
								$limit 	= 	$this->prefs['limit'][$key];
								$type  	= 	$this->prefs['type'];
								$creds 	= 	$this->prefs['creds'][$key];
								$log	=	$this->prefs['log'][$key];
								//Remove comma form amount
								if($val == $form_id){
									$response = $this->get_user_limit($limit,$user_id,$type);
									if($response == true){
										mycred_add('mycred_pmp_renew_membership',$user_id, $creds, $log.' '.$give_form_title,$form_id,$ref_type,$type);
									}
								}else if($val == 999999){
									$response = $this->get_user_limit($limit,$user_id,$type);
									if($response == true){	
										mycred_add('mycred_pmp_renew_membership',$user_id, $creds, $log.' '.$give_form_title,$form_id,$ref_type,$type);
									}
								}
							endforeach;
						endif; 
					}
				}
			}
		} 
		
		/**
		* $limit = 2/d , 3/w, 5/m, 10/t
		* $user_id = current user id
		* $ctype = point type
		**/
		public function get_user_limit( $limit, $user_id, $ctype ) {
			$limit_period = explode( '/', $limit);
			$time = $limit_period[0]; //
			$period = $limit_period[1]; // d,m,w,t
			$date_to_check = ''; // no limit
			if( $period == 'm' )
				$date_to_check = 'thismonth';
			else if( $period == 'w' )
				$date_to_check = 'thisweek';
			else if( $period == 'd' )
				$date_to_check = 'today';
			else if( $period == 't' )
				$date_to_check = 'total';
			else // when no limit set
 				return true;
			
			$args = array(
				'ref' => array('ids' => 'mycred_pmp_renew_membership','compare' => '='),
				'user_id'   => $user_id,
				'ctype'     => $ctype,
				'date'     => $date_to_check,
			);
			$log  = new myCRED_Query_Log( $args );
			$used_limit = $log->num_rows;
			
			if( $used_limit >= $time )
				return false;
			
			return true;
			
		}
		
		/**
		* Preference for renew membership hook
		**/
		public function preferences() {
			$prefs = $this->prefs;
			if ( isset($prefs['creds']) && count( $prefs['creds'] ) > 0 ) {
				$hooks = myCred_pmp_renew_arrange_data( $prefs );
				myCred_pmp_renew_hook_setting( $hooks, $this );
			}
			else {
				$default_data = array(
					array(
						'creds' => 10,
						'limit' => 'x',
						'log' => '%plural% for renew membership',
						'pmp_form_id' => '0',
					)
				);
				myCred_pmp_renew_hook_setting( $default_data, $this );
			}

		}


	   /**
	   * Sanitize Preferences
	   */
		public function sanitise_preferences( $data ) {
			foreach ( $data as $data_key => $data_value ) {
				foreach ( $data_value as $key => $value) {
					if ( $data_key == 'creds' ) {
						$new_data[$data_key][$key] = ( !empty( $value ) ) ? floatval( $value ) : 0;
					}
					else if ( $data_key == 'limit' ) {
						$limit = sanitize_text_field( $data[$data_key][$key]);
						if ( $limit == '' ) $limit = 0;
						$new_data[$data_key][$key] = $limit . '/' . $data['limit_by'][$key];
					}
					else if ( $data_key == 'log' ) {
						$new_data[$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '%plural% for renew membership';
					}
					else if ( $data_key == 'pmp_form_id' ) {
						$new_data[$data_key][$key] = ( !empty( $value ) ) ? intval( $value ) : 0;
					}
				}
			} 
			return $new_data;
		}

	}
endif;