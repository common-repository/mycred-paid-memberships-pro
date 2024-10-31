<?php
/**
 * Plugin Name: myCred Paid Membership Pro Addon
 * Plugin URI: https://mycred.me
 * Description: myCred Membership Pro Addon is an integration for Paid Membership Pro to award or deduct myCred points for Paid Membership Pro Events.
 * Version: 1.0.7
 * Tags: myCred, Paid Membership Pro, membership, rewards, points
 * Author Email: support@mycred.me
 * Author: myCred
 * Author URI: http://mycred.me
 * Requires at least: WP 4.8
 * Tested up to: WP 6.6.1
 * License: GPLv2 or later
 * Text Domain: myCred_pmp
**/

if ( ! class_exists( 'myCred_paid_membership_pro' )){
	
	/**
	* myCRED paid membership pro Addons class
	**/
	class myCred_paid_membership_pro {
		
		// Plugin Version
		public $version = '1.0.7';	
		
		/**
		* Construct
		**/
		public function __construct() {
			$this->pmp_define_constants();
			$this->pmp_init();
		}
		
		/**
		* Check Required Files
		**/
		public function file( $required_file ) {
			if ( file_exists( $required_file ) )
				require_once $required_file;
		}
		/**
		* Check Define Path
		**/
		private function define( $name, $value ) {
			if ( ! defined( $name ) )
				define( $name, $value );
		}
		
		/**
		* PMP Initialize
		**/
		private function pmp_init() {

			$this->file( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active('mycred/mycred.php') && is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) {
				add_action( 'admin_enqueue_scripts', array( $this, 'pmp_admin_scripts' ) );
				add_action( 'init',array( $this, 'pmp_includes')); 
				add_action( 'mycred_load_hooks',     array( $this, 'pmp_load_hooks'));
				add_filter( 'mycred_setup_hooks',    array( $this, 'pmp_register_hooks' ), 10, 2 );
				add_filter( 'mycred_all_references', array( $this, 'pmp_register_refrences' ) ); 
				
				//Use Badge Addon filters for Purchase Membership
				add_filter( 'mycred_badge_requirement','pmp_purchase_membership_badge_requirement', 10, 5 );
				add_filter( 'mycred_badge_requirement_specific_template', 'pmp_purchase_membership_badge_template', 10, 5 );
	            add_action( 'admin_head','pmp_purchase_membership_admin_header' );
				
				//Use Badge Addon filters for Renew Membership
				 add_filter( 'mycred_badge_requirement','pmp_renew_membership_badge_requirement', 10, 5 );
				add_filter( 'mycred_badge_requirement_specific_template', 'pmp_renew_membership_badge_template', 10, 5 );
	            add_action( 'admin_head','pmp_renew_membership_admin_header' );
				
				//Use Badge Addon filters for Cancel Membership
				 add_filter( 'mycred_badge_requirement','pmp_cancel_membership_badge_requirement', 10, 5 );
				add_filter( 'mycred_badge_requirement_specific_template', 'pmp_cancel_membership_badge_template', 10, 5 );
	            add_action( 'admin_head','pmp_cancel_membership_admin_header' ); 
				
				//Use Badge Addon filters for Expired Membership
				add_filter( 'mycred_badge_requirement','pmp_expired_membership_badge_requirement', 10, 5 );
				add_filter( 'mycred_badge_requirement_specific_template', 'pmp_expired_membership_badge_template', 10, 5 );
	            add_action( 'admin_head','pmp_expired_membership_admin_header' );
				
				
			}
			add_action( 'admin_notices',array( $this, 'pmp_required_plugin_notices' ) ); 
		}
		
		/**
		* PMP define constants
		**/ 
		private function pmp_define_constants() {
			$this->define( 'MYCRED_PMP_VERSION',$this->version);
			$this->define( 'MYCRED_PMP_SLUG',           'myCred_pmp');
			$this->define( 'MYCRED_PMP',__FILE__ );
			$this->define( 'MYCRED_PMP_ROOT_DIR',       plugin_dir_path(MYCRED_PMP) );
			$this->define( 'MYCRED_PMP_ASSETS_DIR_URL', plugin_dir_url(MYCRED_PMP) . 'assets/' );
			$this->define( 'MYCRED_PMP_INCLUDES_DIR',   MYCRED_PMP_ROOT_DIR . 'includes/' );
		}
		/**
		* Load Admin Scripts 
		**/
		public function pmp_admin_scripts(){
			//Script
			wp_enqueue_script( 
				'mycred_pmp_purchase_membership_script', 
				MYCRED_PMP_ASSETS_DIR_URL . 'js/pmp_purchase_membership_script.js', 
				array('jquery'), 
				'1.0' 
			);
			 wp_enqueue_script( 
				'mycred_pmp_renew_membership_script', 
				MYCRED_PMP_ASSETS_DIR_URL . 'js/pmp_renew_membership_script.js', 
				array('jquery'), 
				'1.0' 
			);
			wp_enqueue_script( 
				'mycred_pmp_cancel_membership_script', 
				MYCRED_PMP_ASSETS_DIR_URL . 'js/pmp_cancel_membership_script.js', 
				array('jquery'), 
				'1.0' 
			);
			wp_enqueue_script( 
				'mycred_pmp_expired_membership_script', 
				MYCRED_PMP_ASSETS_DIR_URL . 'js/pmp_expired_membership_script.js', 
				array('jquery'), 
				'1.0' 
			);
			wp_enqueue_script( 
				'pmp_point_payment_admin_script', 
				MYCRED_PMP_ASSETS_DIR_URL . 'js/pmp_point_payment_admin_script.js', 
				array('jquery'), 
				'1.0' 
			);
			//CSS
			wp_enqueue_style( 
				'mycred_pmp_purchase_membership_style', 
				MYCRED_PMP_ASSETS_DIR_URL . 'css/pmp_purchase_membership_style.css', 
				array(), 
				'1.0' 
			);
			wp_enqueue_style( 
				'mycred_pmp_renew_membership_style', 
				MYCRED_PMP_ASSETS_DIR_URL . 'css/pmp_renew_membership_style.css', 
				array(), 
				'1.0' 
			);
			wp_enqueue_style( 
				'mycred_pmp_cancel_membership_style', 
				MYCRED_PMP_ASSETS_DIR_URL . 'css/pmp_cancel_membership_style.css', 
				array(), 
				'1.0' 
			);
			wp_enqueue_style( 
				'mycred_pmp_expired_membership_style', 
				MYCRED_PMP_ASSETS_DIR_URL . 'css/pmp_expired_membership_style.css', 
				array(), 
				'1.0' 
			);
		}
		
		
		/**
		* Load Includes File 
		**/
		public function pmp_includes() {
			$this->file(MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_purchase_membership_functions.php');
			$this->file(MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_renew_membership_functions.php');
			$this->file(MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_cancel_membership_functions.php');
			$this->file(MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_expired_membership_functions.php');
			
			/**Including Points Payment files */
			$this->file(MYCRED_PMP_INCLUDES_DIR . 'points_payment/mycred_pmp_point_payment.php');
		}
		
		/**
		* PMP hooks file
		**/ 
		public function pmp_load_hooks() {
			$this->file( MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_purchase_membership_hook.php' );
			$this->file( MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_renew_membership_hook.php' );
			$this->file( MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_cancel_membership_hook.php' );
			$this->file( MYCRED_PMP_INCLUDES_DIR . 'mycred_pmp_expired_membership_hook.php' );
		}
		
		/**
		* PMP register hooks
		**/
		public function pmp_register_hooks( $installed ) {
			$installed['mycred_pmp_purchase_membership'] = array(
				'title'       => __('Points for new purchase membership', 'myCred_pmp'),
				'description' => __('This is new paid purchase membership addon', 'myCred_pmp'),
				'callback'    => array('myCred_purchase_membership_hook')
			);
			$installed['mycred_pmp_renew_membership'] = array(
				'title'       => __('Points for renew membership', 'myCred_pmp'),
				'description' => __('This is renew membership addon', 'myCred_pmp'),
				'callback'    => array('myCred_renew_membership_hook')
			);
			$installed['mycred_pmp_cancel_membership'] = array(
				'title'       => __('Points for cancel membership', 'myCred_pmp'),
				'description' => __('This is cancel membership addon', 'myCred_pmp'),
				'callback'    => array('myCred_cancel_membership_hook')
			);
			$installed['mycred_pmp_expired_membership'] = array(
				'title'       => __('Points for expired membership', 'myCred_pmp'),
				'description' => __('This is expired membership addon', 'myCred_pmp'),
				'callback'    => array('myCred_expired_membership_hook')
			);
			return $installed;
		}
		
		/**
		* PMP register refrences
		**/
		public function pmp_register_refrences($list) {
			$list['mycred_pmp_purchase_membership'] = __('Points for new purchase membership', 'myCred_pmp');
			$list['mycred_pmp_renew_membership'] = __('Points for renew membership', 'myCred_pmp');
			$list['mycred_pmp_cancel_membership'] = __('Points for cancel membership', 'myCred_pmp');
			$list['mycred_pmp_expired_membership'] = __('Points for expired membership', 'myCred_pmp');
			return $list;
		}
		
		/**
		* PMP required plugin notices
		**/
		public function pmp_required_plugin_notices() {
 
			$msg = __( 'need to be active and installed to use myCred plugin.', 'myCred_pmp' );
			$msg_pmp = __( 'need to be active and installed to use myCred Paid Membership Pro Addon.', 'myCred_pmp' );
			if ( !is_plugin_active('mycred/mycred.php') ) {
				printf( '<div class="notice notice-error"><p><a href="https://wordpress.org/plugins/mycred/">%1$s</a> %2$s</p></div>', esc_html_e( 'myCred', 'myCred_pmp' ), esc_html( $msg ) );
			} 
			if(!is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')){
				printf( '<div class="notice notice-error"><p><a href="https://www.paidmembershipspro.com/">%1$s</a> %2$s</p></div>', esc_html_e( 'Paid Memberships Pro', 'myCred_pmp' ), esc_html( $msg_pmp ) );
			} 
		}
	} //end class
	
} // Check class
new myCred_paid_membership_pro();

