jQuery(document).ready(function ($) {

    if( $("select#gateway").val() == 'myCred' ){
        
        $('select[name="gateway_environment"]').parents('tr').hide();

        $('select[name="currency"]').parents('tr').prev('tr').hide();      //Hiding Tax Settings Tab

        $('select[name="currency"]').parents('tr').nextAll('tr').hide();
    }



    $("select#gateway").on('change',function(){

        if(this.value == 'myCred'){
            $('select[name="gateway_environment"]').parents('tr').hide();   //Hiding Gateway Options (Sandbox/Live)

            $('select[name="currency"]').parents('tr').prev('tr').hide();      //Hiding Tax Settings Tab

            $('select[name="currency"]').parents('tr').nextAll('tr').hide();

        }

        else{
            $('select[name="gateway_environment"]').parents('tr').show();

            $('select[name="currency"]').parents('tr').prev('tr').show();

            $('select[name="currency"]').parents('tr').nextAll('tr').show();

        }
    
    
    })






});