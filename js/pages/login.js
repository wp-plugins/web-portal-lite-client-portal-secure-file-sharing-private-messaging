jQuery( document ).ready( function( $ ) {
    jQuery( "#wp-submit" ).on( 'click', function() {

        if ( jQuery( "#pass1" ).val() == '' ) {
            msg += "Password required.<br/>";
        } else if ( jQuery( "#pass2" ).val() == '' ) {
            msg += "Confirm Password required.<br/>";
        } else if ( jQuery( "#pass1" ).val() != jQuery( "#pass2" ).val() ) {
            msg += "Passwords are not matched.<br/>";
        }

        if ( msg != '' ) {
            return false;
        }
    });
    
    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );
        
    $( 'body' ).on( 'keyup', '#pass1, #pass2',
        function( event ) {
            checkPasswordStrength(
                $('#pass1'),        
                $('#pass2'), 
                $('#pass-strength-result'),           
                $('#wp-submit'),    
                wpc_password_protect.blackList
            );
        }
    );
}); 