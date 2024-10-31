jQuery(document).ready(function () {
	jQuery(document).on( 'click', '.mycred-add-pmp-cancel-specific-hook', function() {
        var hook = jQuery(this).closest('.hook-instance').clone();
        hook.find('input.mycred-cancel-creds').val('10');
		hook.find('input.mycred-pmp-cancel-log').val('%plural% for new cancel membership');
		hook.find('select.mycred-cancel-form-id').val('0');
		hook.find('select.mycred-cancel-form-id').find('option[value="999999"]').remove();
        jQuery(this).closest('.widget-content').append( hook );
	}); 
    jQuery(document).on( 'click', '.mycred-pmp-cancel-remove-specific-hook', function() {
        var container = jQuery(this).closest('.widget-content');
        if ( container.find('.hook-instance').length > 1 ) {
            var dialog = confirm("Are you sure you want to remove this hook instance?");
            if (dialog == true) {
                jQuery(this).closest('.hook-instance').remove();
            } 
        }
    }); 	
});
