<?php
if( !$no_redirect ) {
    global $wpdb;

    $data['login_url']  = '';
    $data['error_msg'] = '';

    $data['labels']['username'] = __( 'Username:', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['password'] = __( 'Password:', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['remember'] = __( 'Remember Me', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['email']    = __( 'Username or E-mail:', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['new_password'] = __( 'New password:', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['confirm_new_password'] = __( 'Confirm new password:', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['reset_password'] = __( 'Reset Password', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['get_new_password'] = __( 'Get New Password', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['hint_indicator'] = __( '<strong>Hint</strong>: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like <strong>! " ? $ % ^ &amp; ).</strong>', WPC_CLIENT_TEXT_DOMAIN );
    $data['labels']['strength_indicator'] = __( 'Strength Indicator', WPC_CLIENT_TEXT_DOMAIN );

    $data['somefields'] = '<input type="hidden" name="wpc_login" value="login_form">';

    $data['check_invalid'] = array( __( '<strong>ERROR</strong>: Invalid key. <a href="' . add_query_arg( array( 'action' => 'lostpassword' ), $this->cc_get_login_url() ) . '">Get New Password</a>', WPC_CLIENT_TEXT_DOMAIN ), __( 'Your password has been reset. <a href="' . $this->cc_get_login_url() . '">Log in</a>', WPC_CLIENT_TEXT_DOMAIN ) );

    $data['action'] = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
    $data['login_href'] = $this->cc_get_login_url();

    $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );
    //check reset password link
    if ( isset( $wpc_clients_staff['lost_password'] ) && 'yes' == $wpc_clients_staff['lost_password'] ) {
        $data['lostpassword_href'] = add_query_arg( array( 'action' => 'lostpassword' ), $this->cc_get_login_url() );
    }


    //errors of login
    if ( isset( $GLOBALS['wpclient_login_msg'] ) && '' != $GLOBALS['wpclient_login_msg'] )
        $data['error_msg'] = $GLOBALS['wpclient_login_msg'];




    /**
     * Handles sending password retrieval email to user.
     *
     * @uses $wpdb WordPress Database object
     *
     * @return bool|WP_Error True: when finish. WP_Error on error
     */
    function retrieve_password() {
        global $wpdb, $current_site, $wpc_client;

        if ( empty( $_POST['user_login'] ) ) {
            $data['error_msg'] = __( '<strong>ERROR</strong>: Enter a username or e-mail address.', WPC_CLIENT_TEXT_DOMAIN );
            return $data['error_msg'];
        } else if ( strpos( $_POST['user_login'], '@' ) ) {
            if ( !preg_match("/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/", trim( $_POST['user_login'] ) ) ) {
                $data['error_msg'] = __( '<strong>ERROR</strong>: Invalid E-mail.', WPC_CLIENT_TEXT_DOMAIN );
                return $data['error_msg'];
            } elseif ( !email_exists( trim( $_POST['user_login'] ) ) ) {
                $data['error_msg'] = __( '<strong>ERROR</strong>: There is no user registered with that E-mail address.', WPC_CLIENT_TEXT_DOMAIN );
                return $data['error_msg'];
            } else {
                $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
            }
        } else {
            $login = trim( $_POST['user_login'] );
            $user_data = get_user_by( 'login', $login );

            if ( empty( $user_data ) ) {
                $data['error_msg'] = __( '<strong>ERROR</strong>: There is no user registered with that Username.', WPC_CLIENT_TEXT_DOMAIN );
                return $data['error_msg'];
            }

        }

        //check permission for reset password
        if ( !user_can( $user_data, 'wpc_reset_password' ) ) {
            $data['error_msg'] = __( '<strong>ERROR</strong>:  You do not have permission to reset your password.', WPC_CLIENT_TEXT_DOMAIN );
            return $data['error_msg'];
        }


        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        $key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

        if ( empty( $key ) ) {
            // Generate something random for a key...
            $key = wp_generate_password( 20, false );
            // Now insert the new md5 key into the db
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
        }


        /*if ( is_multisite() )
            $blogname = $GLOBALS['current_site']->site_name;
        else
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );  */

        //$title = sprintf( __('[%s] Password Reset'), $blogname );

        $args = array( 'client_id' => $user_data->ID, 'reset_address' => htmlspecialchars( add_query_arg( array( 'action' => 'rp', 'key' => $key, 'login' => rawurlencode( $user_login )  ), $wpc_client->cc_get_login_url() ) ) );

        //send email
        if ( !$wpc_client->cc_mail( 'reset_password', $user_email, $args, 'reset_password' ) )
            wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

        return true;
    }


    /**
     * Retrieves a user row based on password reset key and login
     *
     * @uses $wpdb WordPress Database object
     *
     * @param string $key Hash to validate sending user's password
     * @param string $login The user login
     * @return object|WP_Error User's database row on success, error object for invalid keys
     */
    function wpc_check_password_reset_key( $key, $login ) {
        global $wpdb, $wpc_client;

        $key = preg_replace( '/[^a-z0-9]/i', '', $key );

        if ( empty( $key ) || !is_string( $key ) )
            return __( '<strong>ERROR</strong>: Invalid key. <a href="' . add_query_arg( array( 'action' => 'lostpassword' ), $wpc_client->cc_get_login_url() ) . '">Get New Password</a>', WPC_CLIENT_TEXT_DOMAIN );
        
        if ( empty( $login ) || !is_string( $login ) )
            return __( '<strong>ERROR</strong>: Invalid key. <a href="' . add_query_arg( array( 'action' => 'lostpassword' ), $wpc_client->cc_get_login_url() ) . '">Get New Password</a>', WPC_CLIENT_TEXT_DOMAIN );

        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $login ) );
        
        if ( preg_replace( '/[^a-z0-9]/i', '', $user->user_activation_key ) != $key )
            return __( '<strong>ERROR</strong>: Invalid key. <a href="' . add_query_arg( array( 'action' => 'lostpassword' ), $wpc_client->cc_get_login_url() ) . '">Get New Password</a>', WPC_CLIENT_TEXT_DOMAIN );

        return $user;
    }


    /**
     * Handles resetting the user's password.
     *
     * @param object $user The user
     * @param string $new_pass New password for the user in plaintext
     */
    function wpc_reset_password( $user, $new_pass ) {

        wp_set_password( $new_pass, $user->ID );

        wp_password_change_notification( $user );

    }
    




    if ( isset($_GET['key']) )
        $data['action'] = 'resetpass';

    // validate action so as to default to the login screen
    if ( !in_array( $data['action'], array( 'postpass', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'login' ), true ) )
        $data['action'] = 'login';
    
    switch ( $data['action'] ) {
        case 'login':
            break;
        case 'lostpassword':

            //lost password link is hidden
            if ( !isset( $wpc_clients_staff['lost_password'] ) || 'yes' != $wpc_clients_staff['lost_password'] ) {
                do_action( 'wp_client_redirect', $this->cc_get_login_url() );
                exit;
            }

            $data['error_msg'] = __( 'Please enter your username or email address. You will receive a link to create a new password via email.', WPC_CLIENT_TEXT_DOMAIN );

            if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                if( true === retrieve_password() ) {
                    do_action( 'wp_client_redirect', add_query_arg( array( 'checkemail' => 'confirm' ), $this->cc_get_login_url() ) );
                    exit;
                } else {
                    $data['error_msg'] = retrieve_password();
                }
            }

            $data['user_login'] = isset( $_POST['user_login'] ) ? stripslashes( $_POST['user_login'] ) : '';
            break;
        case 'rp':
        case 'resetpass':

            //lost password link is hidden
            if ( !isset( $wpc_clients_staff['lost_password'] ) || 'yes' != $wpc_clients_staff['lost_password'] ) {
                do_action( 'wp_client_redirect', $this->cc_get_login_url() );
                exit;
            }


            $user = wpc_check_password_reset_key( $_GET['key'], $_GET['login'] ); 
            if( is_string( $user ) ) {
                $data['error_msg'] = $user;
            } else {
                $data['error_msg'] = __('Enter your new password below.', WPC_CLIENT_TEXT_DOMAIN);
                $data['user_login'] = esc_attr( $_GET['login'] );

                if( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] ) {
                    $data['error_msg'] = __( 'The passwords do not match.', WPC_CLIENT_TEXT_DOMAIN );
                } elseif( isset( $_POST['pass1'] ) && isset( $_POST['pass2'] ) && $_POST['pass1'] == $_POST['pass2'] ) {
                    if( isset( $_POST['pass1'] ) && !empty( $_POST['pass1'] ) ) {
                        wpc_reset_password( $user, $_POST['pass1'] );
                        $message = __( 'Your password has been reset. <a href="' . $this->cc_get_login_url() . '">Log in</a>', WPC_CLIENT_TEXT_DOMAIN );
                        $data['error_msg'] = __( $message, WPC_CLIENT_TEXT_DOMAIN );
                    }
                }
            }
            break;
    }

    if ( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
        $data['error_msg'] = __('Check your e-mail for the confirmation link.', WPC_CLIENT_TEXT_DOMAIN);

    if ( isset( $_GET['msg'] ) && 've' == $_GET['msg'] )
        $data['msg_ve'] = __('Your e-mail address is verified.', WPC_CLIENT_TEXT_DOMAIN);
    
    $out2 = $this->cc_getTemplateContent( 'wpc_client_loginf', $data );

    return do_shortcode( $out2 );
} else {
    return ( isset( $atts['no_redirect_text'] ) && !empty( $atts['no_redirect_text'] ) ) ? $atts['no_redirect_text'] : sprintf( __( '<p>%s already logged in.</p>', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] );
}
?>