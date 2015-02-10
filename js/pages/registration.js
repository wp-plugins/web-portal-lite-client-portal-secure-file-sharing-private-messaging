jQuery( document ).ready( function( $ ) {
    jQuery( "#btnAdd" ).click ( function() {

        var msg = '';

        var emailReg = /^([\w-+\.]+@([\w-]+\.)+[\w-]{2,})?$/;

        if ( jQuery( "#business_name" ).val() == '' ) {
            msg += "Business Name required.<br/>";
        }

        if ( jQuery( "#contact_name" ).val() == '' ) {
            msg += "Contact Name required.<br/>";
        }

        if ( jQuery( "#contact_email" ).val() == '' ) {
            msg += "Contact Email required.<br/>";
        } else if ( !emailReg.test( jQuery( "#contact_email" ).val() ) ) {
            msg += "Invalid Contact Email.<br/>";
        }

        if ( jQuery( "#contact_password" ).val() == '' ) {
            msg += "Password required.<br/>";
        } else if ( jQuery( "#contact_password2" ).val() == '' ) {
            msg += "Confirm Password required.<br/>";
        } else if ( jQuery( "#contact_password" ).val() != jQuery( "#contact_password2" ).val() ) {
            msg += "Passwords are not matched.<br/>";
        }

        if ( jQuery( "#recaptcha_response_field" ).length > 0 && jQuery( "#recaptcha_response_field" ).val() == '' ) {
            msg += "Captcha required.<br/>";
        }

        if( terms_conditions.registration_using_terms == 'yes' ) {
            if ( jQuery( "#terms_agree:checked" ).length == 0 ) {
                msg += terms_conditions.terms_notice + "<br/>";
            }
        }


        if ( msg != '' ) {
            if( jQuery( "#wpc_registration_message" ).hasClass('message_green') ) {
                jQuery( "#wpc_registration_message" ).removeClass('message_green');
                jQuery( "#wpc_registration_message" ).addClass('message_red');
            } else {
                jQuery( "#wpc_registration_message" ).addClass('message_red');
            }
            jQuery( "#wpc_registration_message" ).html( msg );
            jQuery( "#wpc_registration_message" ).show();
            jQuery( "#business_name" ).focus();
            return false;
        }
    });
    
    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );
        
    $( 'body' ).on( 'keyup', '#contact_password, #contact_password2',
        function( event ) {
            checkPasswordStrength(
                $('#contact_password'),        
                $('#contact_password2'), 
                $('#pass-strength-result'),           
                $('#btnAdd'),    
                wpc_password_protect.blackList
            );
        }
    );
    
});