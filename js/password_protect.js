function checkPasswordStrength( $pass1,
                                $pass2,
                                $strengthResult,
                                $submitButton,
                                blacklistArray ) {
    var pass1 = $pass1.val();
    var pass2 = $pass2.val();

    jQuery(".wpc_requirement_min_length").css('text-decoration', '');
    jQuery(".wpc_requirement_mixed_case").css('text-decoration', '');
    jQuery(".wpc_requirement_numeric_digits").css('text-decoration', '');
    jQuery(".wpc_requirement_special_chars").css('text-decoration', '');
    jQuery(".wpc_requirement_level").css('text-decoration', '');

    if( pass1 == pass2 && pass2 == '' ) {
        $submitButton.removeAttr( 'disabled' );
        return false;
    }

    $submitButton.attr( 'disabled', 'disabled' );
    $strengthResult.removeClass( 'short bad good strong' );

    if( wpc_password_protect.min_length > 1 && pass1.length < wpc_password_protect.min_length ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.short );
        var strength = 6;
    } else if( wpc_password_protect.min_length > 1 && pass1.length >= wpc_password_protect.min_length ) {
        jQuery(".wpc_requirement_min_length").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.mixed_case == '1' && ( pass1.toUpperCase() == pass1 || pass1.toLowerCase() == pass1 ) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.mixed_case );
        var strength = 6;
    } else if( wpc_password_protect.mixed_case == '1' && pass1.toUpperCase() != pass1 && pass1.toLowerCase() != pass1 ) {
        jQuery(".wpc_requirement_mixed_case").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.numeric_digits == '1' && !/\d/.test(pass1) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.numbers );
        var strength = 6;
    } else if( wpc_password_protect.numeric_digits == '1' && /\d/.test(pass1) ) {
        jQuery(".wpc_requirement_numeric_digits").css('text-decoration', 'line-through');
    }

    if( wpc_password_protect.special_chars == '1' && /^[0-9A-Za-z ]+$/.test( pass1 ) ) {
        $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.special_chars );
        var strength = 6;
    } else if( wpc_password_protect.special_chars == '1' && !/^[0-9A-Za-z ]+$/.test( pass1 ) ) {
        jQuery(".wpc_requirement_special_chars").css('text-decoration', 'line-through');
    }

    if( typeof strength == 'undefined' ) {
        blacklistArray = blacklistArray.concat( wp.passwordStrength.userInputBlacklist() );
        var strength = wp.passwordStrength.meter( pass1, blacklistArray, pass2 );
        switch ( strength ) {
            case 2:
                $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.bad );
                break;
            case 3:
                $strengthResult.addClass( 'good' ).html( wpc_text_var.pwsL10n.good );
                break;
            case 4:
                $strengthResult.addClass( 'strong' ).html( wpc_text_var.pwsL10n.strong );
                break;
            case 5:
                $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.mismatch );
                break;
            default:
                if( in_array( pass1, wpc_password_protect.blackList ) ) {
                    $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.blacklist );
                } else if( pass1.length < 6 && wpc_password_protect.min_length == '1' ) {
                    $strengthResult.addClass( 'short' ).html( wpc_text_var.pwsL10n.short );
                } else if ( pass1.length < 6 && wpc_password_protect.min_length > 1 ) {
                    $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.bad );
                } else {
                    $strengthResult.addClass( 'bad' ).html( wpc_text_var.pwsL10n.weak );
                }
        }
    }
    //console.log(strength);

    switch( wpc_password_protect.strength ) {
        case '2':
            if ( ( 2 == strength || 3 == strength || 4 == strength ) && '' !== pass2.trim() ) {
                $submitButton.removeAttr( 'disabled' );
            }
            if( 2 == strength || 3 == strength || 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
        case '3':
            if ( ( 3 == strength || 4 == strength ) && '' !== pass2.trim() ) {
                $submitButton.removeAttr( 'disabled' );
            }
            if( 3 == strength || 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
        case '4':
            if ( 4 == strength && '' !== pass2.trim() ) {
                $submitButton.removeAttr( 'disabled' );
            }
            if( 4 == strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
        default:
            if ( !( 6 == strength || pass1 != pass2 ) && '' !== pass2.trim() ) {
                $submitButton.removeAttr( 'disabled' );
            }
            if( 6 != strength ) {
                jQuery(".wpc_requirement_level").css('text-decoration', 'line-through');
            }
            break;
    }

    return strength;
}

jQuery( document ).ready( function( $ ) {
    $( '#pass1, #pass2' ).off('keyup');

    $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

    $( 'body' ).on( 'keyup', '#pass1, #pass2',
        function( event ) {
            checkPasswordStrength(
                $('#pass1'),
                $('#pass2'),
                $('#pass-strength-result'),
                $('#submit, #wp-submit'),
                wpc_password_protect.blackList
            );
        }
    );
});

function in_array(needle, haystack, strict) {
    var found = false, key, strict = !!strict;

    for (key in haystack) {
        if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
            found = true;
            break;
        }
    }

    return found;
}
