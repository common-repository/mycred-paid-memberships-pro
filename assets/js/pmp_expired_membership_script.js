jQuery(document).ready(function () {
	
    jQuery('.widget-liquid-right select.mycred-expired-form-id').each(function(){
		if(jQuery(this).val() ==999999) 
		jQuery('.mycred-add-pmp-expired-specific-hook').attr('disabled', 'disabled');
	}) 
	
	
	jQuery(document).on( 'click', '.mycred-add-pmp-expired-specific-hook', function() {
        var hook = jQuery(this).closest('.hook-instance').clone();
        hook.find('input.mycred-expired-creds').val('10');
		hook.find('input.mycred-pmp-expired-log').val('%plural% for new expired membership');
		hook.find('select.mycred-expired-form-id').val('0');
		hook.find('select.mycred-expired-form-id').find('option[value="999999"]').remove();
        jQuery(this).closest('.widget-content').append( hook );
	}); 
	
	
    jQuery(document).on( 'click', '.mycred-pmp-expired-remove-specific-hook', function() {
        var container = jQuery(this).closest('.widget-content');
        if ( container.find('.hook-instance').length > 1 ) {
            var dialog = confirm("Are you sure you want to remove this hook?");
            if (dialog == true) {
                jQuery(this).closest('.hook-instance').remove();
            } 
        }
    }); 
	
	
	jQuery(document).on('change', 'select.mycred-expired-form-id', function(){
		jQuery('select.mycred-expired-form-id').not(jQuery(this)).find('option[value="'+jQuery(this).val()+'"]').attr('disabled', 'disabled');
		if(jQuery(this).val() == 999999){
			jQuery('.mycred-add-pmp-expired-specific-hook').attr('disabled', 'disabled');
		}else{
			jQuery('.mycred-add-pmp-expired-specific-hook').removeAttr('disabled');
		}
		
	});
	
});
