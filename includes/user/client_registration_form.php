<?php
if( !$no_redirect ) {
        global $wpc_client;
        do_action( 'wpc_client_page_client_registration_form' );


        $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );
        $theme_name = ( isset( $wpc_clients_staff['captcha_theme'] ) && !empty( $wpc_clients_staff['captcha_theme'] ) ) ? $wpc_clients_staff['captcha_theme'] : 'red';
    ?>
    <script type="text/javascript">
        var theme_name = '<?php echo $theme_name ?>';
        var RecaptchaOptions = {
            theme : theme_name
        };
    </script>

    <?php


    if ( !function_exists( '_recaptcha_qsencode' ) )
        include $this->plugin_dir . '/includes/libs/recaptchalib.php';


    if( isset( $wpc_clients_staff['captcha_publickey'] ) && isset( $wpc_clients_staff['captcha_privatekey'] ) && '' != $wpc_clients_staff['captcha_publickey'] && '' != $wpc_clients_staff['captcha_privatekey'] ) {
        $publickey = $wpc_clients_staff['captcha_publickey'];
        $privatekey = $wpc_clients_staff['captcha_privatekey'];
    } else {
        $publickey = "6LepaeMSAAAAAJppWl-CnHrjUntX25aXSmM1gqbx"; // you got this from the signup page
        $privatekey = '6LepaeMSAAAAAO2oP2rq-CZ_e8kwZRgJ6i69v0Gd';
    }

    if ( !isset( $wpc_clients_staff['client_registration'] ) || 'yes' != $wpc_clients_staff['client_registration'] ) {
        return __( 'Registration is disabled!', WPC_CLIENT_TEXT_DOMAIN );
    }

    if( isset( $wpc_clients_staff['registration_using_captcha'] ) && 'yes' == $wpc_clients_staff['registration_using_captcha'] ) {

        $ssl = false;
        if( is_ssl() ) {
            $ssl = true;
        }

        $data['labels']['captcha'] = recaptcha_get_html( $publickey, null, $ssl );
    }

    $data['terms_used'] = false;
    if( isset( $wpc_clients_staff['registration_using_terms'] ) && 'yes' == $wpc_clients_staff['registration_using_terms'] ) {
        $data['terms_used'] = true;
        $data['vals']['terms_default_checked'] = ( isset( $wpc_clients_staff['terms_default_checked'] ) && 'yes' == $wpc_clients_staff['terms_default_checked'] ) ? ' checked="checked"' : '';

        $data['vals']['terms_hyperlink'] = ( isset( $wpc_clients_staff['terms_hyperlink'] ) && !empty( $wpc_clients_staff['terms_hyperlink'] ) ) ? $wpc_clients_staff['terms_hyperlink'] : '#';
        $data['labels']['terms_agree'] = __('I agree.', WPC_CLIENT_TEXT_DOMAIN);
    }


    extract($_REQUEST);

    $error = "";

    if( isset( $btnAdd ) ) {

	    // validate at php side
	    if ( empty( $contact_name ) ) // empty username
		    $error .= __('A Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

	    if ( empty( $contact_username ) ) // empty username
		    $error .= __('A username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

        if ( empty( $contact_email ) ) // empty email
            $error .= __('A email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

	    if ( username_exists( $contact_username ) ) //  already exsits user name
		    $error .= __('Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN);

	    if ( email_exists( $contact_email ) ) // email already exists
		    $error .= __('Email address is already in use by another user. Please use a unique email address.<br/>', WPC_CLIENT_TEXT_DOMAIN);

	    if ( empty( $contact_password ) || empty( $contact_password2 ) ) {
		    if ( empty( $contact_password ) ) // password
			    $error .= __("Sorry, password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
		    elseif ( empty( $contact_password2 ) ) // confirm password
			    $error .= __("Sorry, confirm password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
		    elseif ( $contact_password != $contact_password2 )
			    $error .= __("Sorry, Passwords are not matched! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
	    }

        if ( isset( $recaptcha_response_field ) && !empty( $recaptcha_response_field ) ) {
            $resp = recaptcha_check_answer ( $privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
            if ( !$resp->is_valid ) {
            // What happens when the CAPTCHA was entered incorrectly
                $error .= __("Incorrect Captcha! <br />", WPC_CLIENT_TEXT_DOMAIN);
            }
        } elseif( isset( $recaptcha_response_field ) && empty( $recaptcha_response_field ) ) {
            $error .= __("Captcha required! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
        }

        if( isset( $wpc_clients_staff['registration_using_terms'] ) && 'yes' == $wpc_clients_staff['registration_using_terms'] ) {
            if( empty( $terms_agree ) ) {
                $error .= ( isset( $wpc_clients_staff['terms_notice'] ) && !empty( $wpc_clients_staff['terms_notice'] ) ) ? $wpc_clients_staff['terms_notice'] : __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN );
            }
        }

	    if ( empty( $error ) ) {
		    $userdata = array(
			    'user_pass'         => esc_attr ( $contact_password2 ),
			    'user_login'        => esc_attr( $contact_username ),
			    'display_name'      => esc_attr( trim( $contact_name ) ),
			    'user_email'        => esc_attr( $contact_email ),
                'role'              => 'wpc_client',
			    'business_name'     => ( isset( $business_name ) ) ? esc_attr( trim( $business_name ) ) : esc_attr( trim( $contact_name ) ),
                'contact_phone'     => esc_attr( $contact_phone ),
                'send_password'     => ( isset( $_REQUEST['user_data']['send_password'] ) ) ? esc_attr( $_REQUEST['user_data']['send_password'] ) : '',
                'self_registered'   => ( isset( $wpc_self_registered ) && 1 == $wpc_self_registered ) ? 1 : 0,
		    );

            $userdata['to_approve'] = 'auto';

            do_action( 'wp_clients_update', $userdata );

            do_action( 'wp_client_redirect', $this->cc_get_slug( 'successful_client_registration_page_id' ) );
		    exit;
	    }
    }

    $data['error']          = $error;
    $data['required_text']  = __( ' <font color="red" title="This field is marked as required by the administrator.">*</font>', WPC_CLIENT_TEXT_DOMAIN );

    $data['labels']['business_name']        = __( 'Business or Client Name', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_name']         = __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_email']        = __( 'Email', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_phone']        = __( 'Phone', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_username']     = __( 'Username', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_password']     = __( 'Password', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['contact_password2']    = __( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['password_indicator']   = __( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['password_hint']        = __( '>> <strong>HINT:</strong> The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ & )</strong>.', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['send_password']        = __( 'Send this password to email?', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['send_password_desc']   = __( 'Check to Enable', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['send_button']          = __( 'Submit Registration', WPC_CLIENT_TEXT_DOMAIN );


    $data['vals']['business_name']        = isset( $_REQUEST['business_name'] ) ? esc_html( $_REQUEST['business_name'] ) : '';
    $data['vals']['contact_name']         = isset( $_REQUEST['contact_name'] ) ? esc_html( $_REQUEST['contact_name'] ) : '';
    $data['vals']['contact_email']        = isset( $_REQUEST['contact_email'] ) ? esc_html( $_REQUEST['contact_email'] ) : '';
    $data['vals']['contact_phone']        = isset( $_REQUEST['contact_phone'] ) ? esc_html( $_REQUEST['contact_phone'] ) : '';
    $data['vals']['contact_username']     = isset( $_REQUEST['contact_username'] ) ? esc_html( $_REQUEST['contact_username'] ) : '';
    $data['vals']['send_password']        = isset( $_REQUEST['send_password'] ) ? esc_html( $_REQUEST['send_password'] ) : '';

    $data['custom_fields'] = array();

    $html = '';

    /*our_hook_
        hook_name: wpc_client_registration_form_custom_html
        hook_title: Client Registration Form
        hook_description: Can be used for adding custom fields on Client Registration Form.
        hook_type: filter
        hook_in: wp-client
        hook_location client_registration_form.php
        hook_param: string $html
        hook_since: 3.4.8
    */
    $data['custom_html'] = apply_filters( 'wpc_client_registration_form_custom_html', $html );

    $out2 =  $this->cc_getTemplateContent( 'wpc_client_registration_form', $data );

    return do_shortcode( $out2 );
} else {
    return ( isset( $atts['no_redirect_text'] ) && !empty( $atts['no_redirect_text'] ) ) ? $atts['no_redirect_text'] : sprintf( __( '<p>%s already registered.</p>', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] );
}
?>
