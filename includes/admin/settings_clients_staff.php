<?php

if ( isset( $_POST['update_settings'] ) ) {

    if ( isset( $_POST['wpc_clients_staff'] ) ) {
        $settings = $_POST['wpc_clients_staff'];

        if ( isset( $settings['role_all'] ) && 'yes' == $settings['role_all'] && isset( $settings['auto_convert_role'] ) )
            unset( $settings['auto_convert_role'] );

        $settings['auto_client_approve']        = ( isset( $settings['auto_client_approve'] ) ) ? 'yes' : 'no';
        $settings['new_client_admin_notify']    = ( isset( $settings['new_client_admin_notify'] ) ) ? 'yes' : 'no';
        $settings['send_approval_email']        = ( isset( $settings['send_approval_email'] ) ) ? 'yes' : 'no';
        $settings['captcha_publickey']          = ( isset( $settings['captcha_publickey'] ) && '' != $settings['captcha_publickey'] && isset( $settings['captcha_privatekey'] ) && '' != $settings['captcha_privatekey'] ) ? $settings['captcha_publickey'] : '';
        $settings['captcha_privatekey']         = ( isset( $settings['captcha_publickey'] ) && '' != $settings['captcha_publickey'] && isset( $settings['captcha_privatekey'] ) && '' != $settings['captcha_privatekey'] ) ? $settings['captcha_privatekey'] : '';
    } else {
        $settings = array();
    }

    do_action( 'wp_client_settings_update', $settings, 'clients_staff' );
    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=clients_staff&msg=u' );
    exit;
}

$wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );

?>

<script type="text/javascript">
jQuery( document ).ready( function() {
    var plugin_url = '<?php echo $this->plugin_url ?>';

    switch ( jQuery('#wpc_clients_staff_captcha_theme').val() ) {
        case 'red':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_red.png');
            break;
        case 'blackglass':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_black.png');
            break;
        case 'white':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_white.png');
            break;
        case 'clean':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_clean.png');
            break;
    }

    if( jQuery('#wpc_clients_staff_registration_using_captcha').val() == 'no') {
        jQuery('#captcha_hiding_settings').css('display','none');
    } else if( jQuery('#wpc_clients_staff_registration_using_captcha').val() == 'yes') {
        jQuery('#captcha_hiding_settings').css('display','block');
    }

    if( jQuery('#wpc_clients_staff_registration_using_terms').val() == 'no') {
        jQuery('#terms_hiding_settings').css('display','none');
    } else if( jQuery('#wpc_clients_staff_registration_using_terms').val() == 'yes') {
        jQuery('#terms_hiding_settings').css('display','block');
    }


    jQuery('#wpc_clients_staff_auto_convert').change(function(){
        if( jQuery(this).val() == 'no') {
            jQuery('#block_for_auto_convert_role').slideUp('high');
        } else if( jQuery(this).val() == 'yes') {
            jQuery('#block_for_auto_convert_role').slideDown('high');
        }
    });

    jQuery('#wpc_clients_staff_role_all').change(function(){
        if ( jQuery(this).is(':checked') ) {
            jQuery('.role_item').attr( 'checked', true );
        } else{
            jQuery('.role_item').attr( 'checked', false );
        }
    });

    jQuery('.role_item').change(function(){
        if ( jQuery(this).is(':checked') ) {
            if( jQuery('.role_item').length == jQuery('.role_item:checked').length )
                jQuery('#wpc_clients_staff_role_all').attr( 'checked', true );
        } else{
            jQuery('#wpc_clients_staff_role_all').attr( 'checked', false );
        }

    });

    jQuery('#wpc_clients_staff_captcha_theme').change(function(){
        switch ( jQuery(this).val() ) {
        case 'red':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_red.png');
            break;
        case 'blackglass':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_black.png');
            break;
        case 'white':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_white.png');
            break;
        case 'clean':
            jQuery('#recaptcha_preview_theme').attr('src', plugin_url + 'images/recaptcha_clean.png');
            break;
        }
    });

    jQuery('#update_settings').click(function(){
        var errors = 0;
        if( !( ( jQuery('#wpc_clients_staff_captcha_privatekey').val() != '' && jQuery('#wpc_clients_staff_captcha_publickey').val() != '' ) || ( jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' && jQuery('#wpc_clients_staff_captcha_publickey').val() == '' ) ) ) {
            if( jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' ) {
                jQuery('#wpc_clients_staff_captcha_privatekey').focus();
            } else if( jQuery('#wpc_clients_staff_captcha_publickey').val() == '' ) {
                jQuery('#wpc_clients_staff_captcha_publickey').focus();
            }
            errors++;
        }

        if( errors == 0 ) {
            errors = 0;
            return true;
        } else {
            errors = 0;
            return false;
        }
    });

    jQuery('#wpc_clients_staff_registration_using_captcha').change(function(){
        if( jQuery(this).val() == 'no') {
            jQuery('#captcha_hiding_settings').slideUp('high');
        } else if( jQuery(this).val() == 'yes') {
            jQuery('#captcha_hiding_settings').slideDown('high');
        }
    });

    jQuery('#wpc_clients_staff_registration_using_terms').change(function(){
        if( jQuery(this).val() == 'no') {
            jQuery('#terms_hiding_settings').slideUp('high');
        } else if( jQuery(this).val() == 'yes') {
            jQuery('#terms_hiding_settings').slideDown('high');
        }
    });

    jQuery('#wpc_clients_staff_captcha_publickey').change(function(){
        if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() != '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() != '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        }
    });

    jQuery('#wpc_clients_staff_captcha_privatekey').change(function(){
        if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_publickey').val() == '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_publickey').val() != '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_publickey').val() != '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_publickey').val() == '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        }
    });

    jQuery('#wpc_clients_staff_captcha_publickey').keyup(function(e){
        if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() != '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() != '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_privatekey').val() == '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        }
    });

    jQuery('#wpc_clients_staff_captcha_privatekey').keyup(function(e){
        if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_publickey').val() == '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_publickey').val() != '' && jQuery('#captcha_warning').css('display') == 'none' ) {
            jQuery('#captcha_warning').slideDown('high');
        } else if( jQuery(this).val() != '' && jQuery('#wpc_clients_staff_captcha_publickey').val() != '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        } else if( jQuery(this).val() == '' && jQuery('#wpc_clients_staff_captcha_publickey').val() == '' && jQuery('#captcha_warning').css('display') == 'block' ) {
            jQuery('#captcha_warning').slideUp('high');
        }
    });

});
</script>
<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <div class="postbox">
        <h3 class='hndle'><span><?php printf( __( '%s/%s Settings', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['staff']['s'] ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_create_portal_page"><?php printf( __( 'Automatically create %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[create_portal_page]" id="wpc_clients_staff_create_portal_page" style="width: 100px;">
                            <option value="yes" <?php echo ( isset( $wpc_clients_staff['create_portal_page'] ) && 'yes' == $wpc_clients_staff['create_portal_page'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( isset( $wpc_clients_staff['create_portal_page'] ) && 'no' == $wpc_clients_staff['create_portal_page'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_use_portal_page_settings"><?php printf( __( 'Ignore Theme Link Page Options for Automatically created %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[use_portal_page_settings]" id="wpc_clients_staff_use_portal_page_settings" style="width: 100px;">
                            <option value="0" <?php echo ( isset( $wpc_clients_staff['use_portal_page_settings'] ) && '0' == $wpc_clients_staff['use_portal_page_settings'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="1" <?php echo ( isset( $wpc_clients_staff['use_portal_page_settings'] ) && '1' == $wpc_clients_staff['use_portal_page_settings'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_hide_dashboard"><?php _e( 'Hide dashboard/backend', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[hide_dashboard]" id="wpc_clients_staff_hide_dashboard" style="width: 100px;">
                            <option value="yes" <?php echo ( isset( $wpc_clients_staff['hide_dashboard'] ) && 'yes' == $wpc_clients_staff['hide_dashboard'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( !isset( $wpc_clients_staff['hide_dashboard'] ) || 'no' == $wpc_clients_staff['hide_dashboard'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Hide dashboard/backend from %s and %s.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['staff']['p'] ) ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_hide_admin_bar"><?php _e( 'Hide Admin Bar', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[hide_admin_bar]" id="wpc_clients_staff_hide_admin_bar" style="width: 100px;">
                            <option value="yes" <?php echo ( !isset( $wpc_clients_staff['hide_admin_bar'] ) || 'yes' == $wpc_clients_staff['hide_admin_bar'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( isset( $wpc_clients_staff['hide_admin_bar'] ) && 'no' == $wpc_clients_staff['hide_admin_bar'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Hide Admin Bar from %s and %s.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['staff']['p'] ) ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_lost_password"><?php _e( 'Allow "Lost your password"', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[lost_password]" id="wpc_clients_staff_lost_password" style="width: 100px;">
                            <option value="no" <?php echo ( isset( $wpc_clients_staff['lost_password'] ) && 'no' == $wpc_clients_staff['lost_password'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="yes" <?php echo ( isset( $wpc_clients_staff['lost_password'] ) && 'yes' == $wpc_clients_staff['lost_password'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php _e( 'Displays "Lost your password" link on login form.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_clients_staff_client_registration"><?php printf( __( 'Open %s Registration', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_clients_staff[client_registration]" id="wpc_clients_staff_client_registration" style="width: 100px;">
                            <option value="yes" <?php echo ( isset( $wpc_clients_staff['client_registration'] ) && 'yes' == $wpc_clients_staff['client_registration'] ) ? 'selected' : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( isset( $wpc_clients_staff['client_registration'] ) && 'no' == $wpc_clients_staff['client_registration'] ) ? 'selected' : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Allow registration %s. All %s require approval from the Administrator.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['client']['p'] ) ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="wpc_clients_staff[new_client_admin_notify]" id="wpc_clients_staff_new_client_admin_notify" value="yes" <?php echo ( !isset( $wpc_clients_staff['new_client_admin_notify'] ) || 'yes' == $wpc_clients_staff['new_client_admin_notify'] ) ? 'checked' : '' ?> />
                            <?php _e( 'Notify Admin about new registrations.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        </label>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row">
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="wpc_clients_staff[verify_email]" id="wpc_clients_staff_verify_email" value="yes" <?php echo ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] ) ? 'checked' : '' ?> />
                            <?php printf( __( 'Verify email %s.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?>
                        </label>
                    </td>
                </tr>


                <tr valign="top" class="wpc_pro_form">
                    <th scope="row">
                        <label for=""><?php printf( __( 'Open %s Registration', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['staff']['s'] ) ?>:</label>
                    </th>
                    <td>
                        <select style="width: 100px;" disabled>
                            <option value=""><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Allow %s to add %s. All %s requires approval from the Administrator.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['staff']['p'], $this->custom_titles['staff']['p'] ) ?></span>
                    </td>
                </tr>

                <tr valign="top" class="wpc_pro_form">
                    <th scope="row">
                        <label for=""><?php _e( 'Add CC Email for Private Messaging', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select style="width: 100px;" disabled>
                            <option value="" ><?php _e( 'NO', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="postbox wpc_pro_form">
        <h3 class='hndle'><span><?php _e( 'Password Requirements', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Minimum Length', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <input type="text" disabled style="width: 100px;" value="1" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Strength', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select style="width: 100px;" disabled>
                            <option value="5" ><?php _e( 'Very Weak', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Black List', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <textarea style="width: 300px;" rows="5" disabled><?php echo "password\nqwerty\n123456789" ?></textarea>
                        <br>
                        <span class="description"><?php _e( 'Enter values here to prevent a user from choosing them. One per line.', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Mixed Case', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <input type="checkbox" value="1" disabled />
                        <span class="description"><?php _e( 'Password must contain a mix of uppercase and lowercase characters.', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Numeric Digits', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <input type="checkbox" value="1" disabled />
                        <span class="description"><?php _e( 'Password must contain numeric digits (0-9).', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Password Special Characters', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <input type="checkbox" disabled />
                        <span class="description"><?php _e( 'Password must contain special characters (eg: .,!#$%_+).', WPC_CLIENT_TEXT_DOMAIN ); ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="postbox wpc_pro_form">
        <h3 class='hndle'><span><?php _e( 'Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Using Terms/Conditions', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select style="width: 100px;" disabled>
                            <option value="" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Using Terms/Conditions on %s Registration form.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <div class="postbox wpc_pro_form">
        <h3 class='hndle'><span><?php _e( 'Captcha', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for=""><?php _e( 'Using Captcha', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select style="width: 100px;" disabled>
                            <option value="" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <span class="description"><?php printf( __( 'Using captcha on %s Registration form.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <div class="postbox wpc_pro_form">
        <h3 class='hndle'><span><?php printf( __( 'Auto-Convert to %s Role', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php _e( 'Enable/Disable Auto-Convert', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    </th>
                    <td>
                        <label>
                            <select style="width: 100px;" disabled>
                                <option value="" ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            </select>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <input type='submit' name='update_settings' class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>