<?php
if ( ! defined( 'MYCRED_PMP_SLUG' ) ) exit;

/**
 * Check Page
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'is_mycred_hook_page' ) ) :
	function is_mycred_hook_page( $page ){
		return ( strpos( $page, 'mycred' ) !== false && strpos( $page, 'hook' ) !== false );
	}
endif;

if ( ! function_exists( 'myCred_pmp_purchase_field_name' ) ) :
	function myCred_pmp_purchase_field_name( $type, $attr ){

		$hook_prefs_key = 'mycred_pref_hooks';

		if ( $type != MYCRED_DEFAULT_TYPE_KEY ) {
			$hook_prefs_key = 'mycred_pref_hooks_'.$type;
		}

		return "{$hook_prefs_key}[hook_prefs][mycred_pmp_purchase_membership][{$attr}][]";
	}
endif;

if ( ! function_exists( 'myCred_pmp_hook_setting' ) ) :
	function myCred_pmp_hook_setting( $data, $obj ){
		foreach ( $data as $hook ) {
			
			$form_data = pmp_content_posts();
			$pmp_form_options = '<option value="999999">ALL</option>';
			if ( ! empty( $form_data ) ) {
				foreach ( $form_data as $forms) {
					$pmp_form_options .= '<option value="'.$forms['form_id'].'" '.selected( $hook['pmp_form_id'], $forms['form_id'], false ).' >'.$forms['form_title'].'</option>';
				}
			}

			$select_parm = array(
				'div' => array(
					'class' => array(),
				),
				'input' => array(
					'class' => array(),
					'type' => array(),
					'name' => array(),
					'id' => array(),
					'size' => array(),
					'value' => array()
				),
				'select' => array(
					'name'	=> array(),
					'class' => array(),
					'id' => array(),
				),
				'option' => array(
					'value' => array()
				),
			);
			
			
		?>
		<div class="hook-instance">
			<div class="row">
				<h3 class="description">Points for new purchase membership</h3>
				<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
					<div class="form-group">
						<label><?php echo esc_html($obj->core->plural()); ?></label>
						<input type="text" name="<?php echo esc_attr(myCred_pmp_purchase_field_name( $obj->mycred_type, 'creds' )); ?>" value="<?php echo esc_attr($obj->core->number( $hook['creds'] )); ?>" class="form-control mycred-purchase-creds" />
					</div>
				</div>
				<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
					<div class="form-group">
						<label for="<?php echo esc_attr($obj->field_id( 'limit' )); ?>"><?php esc_html_e( 'Limit', 'myCred_pmp' ); ?></label>
						<?php 
						$limit_name = myCred_pmp_purchase_field_name($obj->mycred_type, 'limit');
						echo wp_kses( $obj->hook_limit_setting( $limit_name, $obj->field_id( 'limit' ), $hook['limit']),$select_parm); ?>
					</div>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
					<div class="form-group">
						<label><?php esc_html_e( 'Log Template', 'myCred_pmp' ); ?></label>
						<input type="text" name="<?php echo esc_attr(myCred_pmp_purchase_field_name( $obj->mycred_type, 'log' )); ?>" placeholder="<?php esc_html_e( 'required', 'myCred_pmp' ); ?>" value="<?php echo esc_attr( $hook['log'] ); ?>" class="form-control mycred-pmp-purchase-log" />
						<span class="description"><?php echo wp_kses_post($obj->available_template_tags( array( 'general' ) )); ?></span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
					<div class="form-group">
						<label><?php esc_html_e( 'Select Specific Membership', 'myCred_pmp' ); ?></label>
						<select class="form-control mycred-purchase-form-id" name="<?php echo esc_attr(myCred_pmp_purchase_field_name( $obj->mycred_type, 'pmp_form_id' )); ?>">
							<?php echo $pmp_form_options; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group pmp-purchase-specific-hook-actions textright">
						<button class="button button-small mycred-add-pmp-purchase-specific-hook" type="button">Add More</button>
						<button class="button button-small mycred-pmp-purchase-remove-specific-hook" type="button">Remove</button>
					</div>
				</div>
			</div>
		</div>
	<?php
		}
	}
endif;
if ( ! function_exists( 'pmp_content_posts' ) ) :
	function pmp_content_posts(){
			/**
			* Select membership levels Forms 
			**/
			global $wpdb;
			$pmpro_membership_levels = $wpdb->prefix."pmpro_membership_levels";
			$list_of_meeting_post_ids = $wpdb->get_results("SELECT id, name FROM $pmpro_membership_levels");
			$form_data = [];
			 if(!empty($list_of_meeting_post_ids)){
				foreach($list_of_meeting_post_ids as $id){
					$post_id = $id->id;	
					$post_title = $id->name;	
					$form_data[] = array('form_id'=>$post_id,'form_title'=>$post_title);
				}
			}
    	return $form_data;
	}
endif;
if ( ! function_exists( 'myCred_pmp_arrange_data' ) ) :
	function myCred_pmp_arrange_data( $data ){
		$hook_data = array();
		foreach ( $data['creds'] as $key => $value ) {
			$hook_data[$key]['creds']      = $value;
			$hook_data[$key]['limit'] = $data['limit'][$key];
			$hook_data[$key]['log'] = $data['log'][$key];
			$hook_data[$key]['pmp_form_id'] = $data['pmp_form_id'][$key] ?? null;
		}
		return $hook_data;
	}
endif;

/**
* GiveWP Badge Functions 
**/
if ( ! function_exists( 'pmp_purchase_membership_badge_requirement' ) ) :
	function pmp_purchase_membership_badge_requirement( $query, $requirement_id, $requirement, $having, $user_id ){
		global $wpdb, $mycred_log_table;
		if($requirement['reference'] == 'mycred_pmp_purchase_membership' && ! empty( $requirement['specific'] ) && $requirement['specific'] != 'Any'){ 
			$query = $wpdb->get_var( $wpdb->prepare( "SELECT {$having} FROM {$mycred_log_table} WHERE ctype = %s AND ref = %s OR ref = %s AND ref_id = %d AND user_id = %d;", $requirement['type'], $requirement['reference'], 'mycred_pmp_purchase_membership', $requirement['specific'], $user_id ) );
		}
		return $query;
	}
endif;

if ( ! function_exists( 'pmp_purchase_membership_badge_template' ) ) :
	function pmp_purchase_membership_badge_template( $data, $requirement_id, $requirement, $badge, $level ){
		if( $requirement['reference'] == 'mycred_pmp_purchase_membership' && ! empty( $requirement['specific'] ) ) { 
			
			$form_data = pmp_content_posts();
			$pmp_form_options = '<option value="999999">ALL</option>';
			foreach ( $form_data as $forms ) {
				$pmp_form_options .= '<option value="'.$forms['form_id'].'"'.selected( $requirement['specific'], $forms['form_id'], false ).'>'.$forms['form_title'].'</option>';
			}
			$data = '<div class="form-group"><select name="mycred_badge[levels]['.$level.'][requires]['.$requirement_id.'][specific]" class="form-control specific" data-row="'.$requirement_id.'" >'.$pmp_form_options.'</select></div>';

		}
		return $data;
	}
endif;

if ( ! function_exists( 'pmp_purchase_membership_admin_header' ) ) :
	function pmp_purchase_membership_admin_header(){
		$screen = get_current_screen();
		
		if ( defined('MYCRED_BADGE_KEY') && $screen->id == MYCRED_BADGE_KEY):?>
	    <script type="text/javascript">
	    <?php
			
	    	$form_data = pmp_content_posts();
			$pmp_form_options = '<option value="999999">ALL</option>';
			foreach ( $form_data as $forms ) {
				$pmp_form_options .= '<option value="'.$forms['form_id'].'">'.$forms['form_title'].'</option>';
			}
			
			$data = '<div class="form-group"><select name="{{element_name}}" class="form-control" data-row="{{reqlevel}}" >'.$pmp_form_options.'</select></div>';
			echo "var mycred_badge_mycred_pmp_purchase_membership = '".esc_js($data)."';";
	    ?>
	    </script>
		<?php endif;
	}
endif;

