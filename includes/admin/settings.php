<?php

?>

<style type="text/css">
    .wrap input[type=text] {
        width:400px;
    }

    .wrap input[type=password] {
        width:400px;
    }

    #captcha_warning,
    #filesize_warning {
        background-color: #FFFFE0;
        border-color: #E6DB55;
        border-radius: 3px 3px 3px 3px;
        border-style: solid;
        border-width: 1px;
        color: #000000;
        font-family: sans-serif;
        font-size: 12px;
        line-height: 1.4em;
        padding: 12px;
    }

</style>

<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <?php if ( isset( $_GET['msg'] ) && !empty( $_GET['msg'] ) ) { ?>
        <div id="message" class="<?php echo ( 'cl_url' == $_GET['msg'] || 'f' == $_GET['msg'] || 'pr_ng' == $_GET['msg'] || 'pr_f' == $_GET['msg'] ) ? 'error' : 'updated' ?> wpc_notice fade">
            <p>
            <?php
                switch( $_GET['msg'] ) {
                    case 'u':
                        _e( 'Settings Updated.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'cl_url':
                        _e( 'Login URL used default names of Wordpress. Settings are not updated.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pu':
                        _e( 'Pages Updated Successfully.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pc':
                        _e( 'Pages Created Successfully', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'ps':
                        _e( 'You are skipped auto-install pages - please do it manually.', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 't':
                        _e( 'Import was successful', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'f':
                        _e( 'Invalid *.xml file', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_ng':
                        _e( 'Note: The registration will not work until you select "Payment Gateways". Clients will see a message that "Registration temporarily unavailable".', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_f':
                        _e( 'Invalid settings', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                    case 'pr_na':
                        _e( 'Not all registration levels was saved', WPC_CLIENT_TEXT_DOMAIN );
                        break;
                }
            ?>
            </p>
        </div>
    <?php } ?>

    <div class="icon32" id="icon-options-general"></div>
    <h2><?php printf( __( '%s Settings', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ) ?></h2>

    <p><?php printf( __( 'From here you can manage a variety of options for the %s plugin.', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ) ?></p>

    <div style="width: 100%;">
        <ul id="tab-headers">
            <?php
                $tabs = array(
                    'business_info'     => __( 'Business Info', WPC_CLIENT_TEXT_DOMAIN ),
                    'clients_staff'     => sprintf( __( '%s/%s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['staff']['p'] ),
                    'default_redirects' => __( 'Custom Redirects', WPC_CLIENT_TEXT_DOMAIN ),
                    'skins'             => __( 'Skins', WPC_CLIENT_TEXT_DOMAIN ),
                    'pages'             => __( 'Theme Link Pages', WPC_CLIENT_TEXT_DOMAIN ), );

                $pro_tabs = array(
                    'general'           => '<span class="wpc_pro_settings_link">' . __( 'General', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'file_sharing'      => '<span class="wpc_pro_settings_link">' . __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'custom_titles'     => '<span class="wpc_pro_settings_link">' . __( 'Custom Titles', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'capabilities'      => '<span class="wpc_pro_settings_link">' . __( 'Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'custom_login'      => '<span class="wpc_pro_settings_link">' . __( 'Custom Login', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'login_alerts'      => '<span class="wpc_pro_settings_link">' . __( 'Login Alerts', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'smtp'              => '<span class="wpc_pro_settings_link">' . __( 'SMTP', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'import_export'     => '<span class="wpc_pro_settings_link">' . __( 'Import/Export Settings', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    'limit_ips'         => '<span class="wpc_pro_settings_link">' . __( 'IP Access Restriction', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                );

                $tabs = apply_filters( 'wpc_client_settings_tabs_array', $tabs );

                if ( !$this->plugin['hide_about_tab'] ) {
                    $tabs['about'] = __( 'About', WPC_CLIENT_TEXT_DOMAIN );
                }


                $current_tab = ( empty( $_GET['tab'] ) ) ? 'business_info' : urldecode( $_GET['tab'] );

                foreach ( $tabs as $name => $label ) {
                    $active = ( $current_tab == $name ) ? 'class="active"' : '';
                    echo '<li ' . $active . '><a href="' . admin_url( 'admin.php?page=wpclients_settings&tab=' . $name ) . '" >' . $label . '</a></li>';
                }

                foreach ( $pro_tabs as $name => $label ) {
                    $active = ( $current_tab == $name ) ? 'class="active"' : '';
                    echo '<li ' . $active . '><a href="' . admin_url( 'admin.php?page=wpclients_pro_features#settings' ) . '" >' . $label . '</a></li>';
                }

                do_action( 'wpc_client_settings_tabs' );
            ?>
            </ul>
        <div id="tab-container">
            <?php
                switch ( $current_tab ) {
                    case "clients_staff":
                        include_once( $this->plugin_dir . 'includes/admin/settings_clients_staff.php' );
                    break;
                    case "about":
                        include_once( $this->plugin_dir . 'includes/admin/settings_about.php' );
                    break;
                    case "business_info":
                        include_once( $this->plugin_dir . 'includes/admin/settings_business_info.php' );
                    break;
                    case "skins":
                        include_once( $this->plugin_dir . 'includes/admin/settings_skins.php' );
                    break;
                    case "pages":
                        include_once( $this->plugin_dir . 'includes/admin/settings_pages.php' );
                    break;
                    case "default_redirects":
                        include_once( $this->plugin_dir . 'includes/admin/settings_login_logout.php' );
                    break;
                    default:
                        do_action( 'wpc_client_settings_tabs_' . $current_tab );
                    break;
                }
            ?>
        </div>
    </div>
</div>