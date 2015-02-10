<?php

if ( isset( $_POST['update_settings'] ) ) {


    if ( isset( $_POST['wpc_business_info'] ) ) {
        $settings = $_POST['wpc_business_info'];
    } else {
        $settings = array();
    }

    do_action( 'wp_client_settings_update', $settings, 'business_info' );
    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=business_info&msg=u' );
    exit;
}

$wpc_business_info = $this->cc_get_settings( 'business_info' );

?>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'General Business Information', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_logo_url"><?php _e( 'Logo URL', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_logo_url}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_logo_url]" id="wpc_business_info_business_logo_url" value="<?php echo ( isset( $wpc_business_info['business_logo_url'] ) ) ? $wpc_business_info['business_logo_url'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_name"><?php _e( 'Official Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_name}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_name]" id="wpc_business_info_business_name" value="<?php echo ( isset( $wpc_business_info['business_name'] ) ) ? $wpc_business_info['business_name'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_address"><?php _e( 'Business Address', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_address}</span>
                    </th>
                    <td>
                        <textarea cols="71" rows="3" name="wpc_business_info[business_address]" id="wpc_business_info_business_address" ><?php echo ( isset( $wpc_business_info['business_address'] ) ) ? $wpc_business_info['business_address'] : '' ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_mailing_address"><?php _e( 'Mailing Address', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_mailing_address}</span>
                    </th>
                    <td>
                        <textarea cols="71" rows="3" name="wpc_business_info[business_mailing_address]" id="wpc_business_info_business_mailing_address" ><?php echo ( isset( $wpc_business_info['business_mailing_address'] ) ) ? $wpc_business_info['business_mailing_address'] : '' ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_website"><?php _e( 'Website', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_website}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_website]" id="wpc_business_info_business_website" value="<?php echo ( isset( $wpc_business_info['business_website'] ) ) ? $wpc_business_info['business_website'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_email}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_email]" id="wpc_business_info_business_email" value="<?php echo ( isset( $wpc_business_info['business_email'] ) ) ? $wpc_business_info['business_email'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_phone}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_phone]" id="wpc_business_info_business_phone" value="<?php echo ( isset( $wpc_business_info['business_phone'] ) ) ? $wpc_business_info['business_phone'] : '' ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_business_info_business_fax"><?php _e( 'Fax', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        <br>
                        <span class="description">{business_fax}</span>
                    </th>
                    <td>
                        <input type="text" name="wpc_business_info[business_fax]" id="wpc_business_info_business_fax" value="<?php echo ( isset( $wpc_business_info['business_fax'] ) ) ? $wpc_business_info['business_fax'] : '' ?>" />
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <input type='submit' name='update_settings' class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>