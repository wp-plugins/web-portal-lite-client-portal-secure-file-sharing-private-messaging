<?php

if ( isset( $_POST['update_settings'] ) ) {

    if ( isset( $_POST['wpc_skins'] ) ) {
        $settings = $_POST['wpc_skins'];
    } else {
        $settings = 'light';
    }

    do_action( 'wp_client_settings_update', $settings, 'skins' );
    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=skins&msg=u' );
    exit;
}

$wpc_skins = $this->cc_get_settings( 'skins' );

?>


<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'Change Skins | Changes the color of the default images used in HUB Page', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="wpc_skins"><?php _e( 'Select Skin Style', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_skins" id="wpc_skins" style="width: 100px;">
                            <option value="light" <?php echo ( 'light' == $wpc_skins ) ? 'selected' : '' ; ?> ><?php _e( 'Light', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="dark" <?php echo ( 'dark' == $wpc_skins ) ? 'selected' : '' ; ?> ><?php _e( 'Dark', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    <input type='submit' name='update_settings' class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>