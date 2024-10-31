<?php
if ( ! defined( 'MYCRED_PMP_SLUG' ) ) exit;

/**
* myCRED_Addons_Module class
**/
if ( ! class_exists( 'myCred_expired_membership_hook' ) ) :
	class myCred_expired_membership_hook extends myCRED_Hook {

		/**
		* Construct
		**/
		function __construct( $hook_prefs, $type = MYCRED_DEFAULT_TYPE_KEY ) {
			parent::__construct( array(
				'id'       => 'mycred_pmp_expired_membership',
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
			add_action( 'pmpro_membership_post_membership_expiry', array($this, 'get_order_by_id_expiry'), 10, 2); 
			 /* add_action( 'init', array($this, 'get_order_by_id')); */
		}
		
		
		
		public function get_order_by_id_expiry( $user_id, $membership_id ) { 
			
			
			global $wpdb;
			
			$pmpro_memberships_users = $wpdb->prefix . 'pmpro_memberships_users';			
			$pmpro_membership_levels = $wpdb->prefix . 'pmpro_membership_levels';			
			$expiry_result = $wpdb->get_results("SELECT u.id,u.user_id,u.membership_id,u.status,l.name FROM $pmpro_memberships_users as u INNER JOIN $pmpro_membership_levels as l ON u.membership_id = l.id WHERE u.user_id = '" . esc_sql($user_id) . "' AND u.membership_id = '" . esc_sql($membership_id) . "' ORDER BY u.id DESC LIMIT 1", ARRAY_A );
			
			if( !empty($expiry_result) ){
				$user_id = $expiry_result[0]['user_id'];
				$membership_id = $expiry_result[0]['membership_id'];
				$membership_name = $expiry_result[0]['name'];
				$status = $expiry_result[0]['status'];
				$this->myCred_expired_membership_save_entry($user_id,$membership_id,$membership_name,$status);
			}else{
				//must be recurring
				return true;
			}
		}
		
		
		/** 
		*	myCred save entry
		**/
		public function myCred_expired_membership_save_entry($user_id,$membership_id,$membership_name,$status){
			
			if($status == 'expired'){
				$give_form_title = $membership_name;
				$form_id = $membership_id;
				$pmp_form_id = $this->prefs['pmp_form_id'];
				$ref_type  = array( 'ref_type' => 'post');
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
									mycred_add('mycred_pmp_expired_membership',$user_id, $creds, $log.' '.$give_form_title,$form_id,$ref_type,$type);
								}
							}else if($val == 999999){
								$response = $this->get_user_limit($limit,$user_id,$type);
								if($response == true){	
									mycred_add('mycred_pmp_expired_membership',$user_id, $creds, $log.' '.$give_form_title,$form_id,$ref_type,$type);
								}
							}
						endforeach;
					endif; 
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
				'ref' => array('ids' => 'mycred_pmp_expired_membership','compare' => '='),
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
		* Preference for expired membership hook
		**/
		public function preferences() {
			$prefs = $this->prefs;
			if ( isset($prefs['creds']) && count( $prefs['creds'] ) > 0 ) {
				$hooks = myCred_pmp_expired_arrange_data( $prefs );
				myCred_pmp_expired_hook_setting( $hooks, $this );
			}
			else {
				$default_data = array(
					array(
						'creds' => 10,
						'limit' => 'x',
						'log' => '%plural% for expired membership',
						'pmp_form_id' => '0',
					)
				);
				myCred_pmp_expired_hook_setting( $default_data, $this );
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
						$new_data[$data_key][$key] = ( !empty( $value ) ) ? sanitize_text_field( $value ) : '%plural% for expired membership';
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