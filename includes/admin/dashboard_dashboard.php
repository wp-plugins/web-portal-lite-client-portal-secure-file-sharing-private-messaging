<?php
global $wpdb;

$wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );
$wpc_general = $this->cc_get_settings( 'general' );
$wpc_custom_login = $this->cc_get_settings( 'custom_login' );

?>
<table width="70%" style="float: left;">
    <tr>
        <td valign="top">
            <table class="wc_status_table widefat" cellspacing="0">

                <thead>
                    <tr>
                        <th colspan="2"><?php _e( 'General Information', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    </tr>
                </thead>

                <tbody>
                    <tr id="key_toggle" style="display:none;">
                        <td colspan="2">
                        <strong><?php
                        echo $wpdb->get_var( "SELECT option_value FROM `".$wpdb->prefix."options` WHERE option_name LIKE '%_license_code';" );
                        ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?>:</td>
                        <td>
                            <?php
                            $total_clients = get_users( array( 'role' => 'wpc_client', 'fields' => 'ID' ) );
                            echo count( $total_clients );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s waiting for Approval', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?>:</td>
                        <td>
                            <?php
                            $not_approved_clients = $this->cc_get_excluded_clients( 'to_approve' );
                            echo count( $not_approved_clients );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . "'s " . $this->custom_titles['staff']['s'] ) ?>:</td>
                        <td>
                            <?php
                            $total_staff = get_users( array( 'role' => 'wpc_client_staff', 'fields' => 'ID' ) );
                            echo count( $total_staff );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s waiting for Approval', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . "'s " . $this->custom_titles['staff']['s'] ) ?>:</td>
                        <td>
                            <?php
                            $not_approved_staff = get_users( array( 'role' => 'wpc_client_staff', 'meta_key' => 'to_approve', 'fields' => 'ID' ) );
                            echo count( $not_approved_staff );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'No. of Unread Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            -
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['manager']['p'] ) ?>:</td>
                        <td>
                            -
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ) ?>:</td>
                        <td>
                            <?php echo $wpdb->get_var( "SELECT count(group_id) FROM {$wpdb->prefix}wpc_client_groups" ) ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php printf( __( 'No. of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['p'] ) ?>:</td>
                        <td>
                            <?php echo $wpdb->get_var( "SELECT count(ID) FROM {$wpdb->posts} WHERE post_type = 'clientspage' AND post_status = 'publish' " ) ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'No. of Files', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            -
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Total Size of all Files', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            0
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Avg. File Size', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>0</td>
                    </tr>
                </tbody>

                <thead>
                    <tr>
                        <th colspan="2"><?php _e( 'Settings Summary', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td id="labels"><?php _e( 'Client Self-Registration Enabled?', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            <?php echo ( isset( $wpc_clients_staff['client_registration'] ) && 'yes' == $wpc_clients_staff['client_registration'] ) ? 'yes' : 'no' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Client Staff Registration Enabled?', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            <?php echo ( isset( $wpc_clients_staff['staff_registration'] ) && 'yes' == $wpc_clients_staff['staff_registration'] ) ? 'yes' : 'no' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Custom Menus Enabled?', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            <?php echo ( isset( $wpc_general['show_custom_menu'] ) && 'yes' == $wpc_general['show_custom_menu'] ) ? 'yes' : 'no' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e( 'Custom Login at /wp-admin Enabled?', WPC_CLIENT_TEXT_DOMAIN ) ?>:</td>
                        <td>
                            <?php echo ( !isset( $wpc_custom_login['cl_enable'] ) || 'no' == $wpc_custom_login['cl_enable'] ) ? 'no' : 'yes' ?>
                        </td>
                    </tr>
                </tbody>

            </table>

            <?php
                do_action( 'wpc_client_dashboard_tables' );
            ?>

        </td>



    </tr>
</table>