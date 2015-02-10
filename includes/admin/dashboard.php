<?php

 $current_tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'dashboard';
 ?>
<div class="wrap">

    <?php echo $this->get_plugin_logo_block() ?>

    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <?php
            $tabs = array(
                'dashboard' => __( 'Dashboard', 'WPC_CLIENT_TEXT_DOMAIN' ),
            );

            if ( current_user_can( 'administrator' ) ) {
                $tabs['system_status'] = __( 'System Status', 'WPC_CLIENT_TEXT_DOMAIN' );
            }


            foreach ( $tabs as $name => $label ) {
                echo '<a href="' . admin_url( 'admin.php?page=wpclients&tab=' . $name ) . '" class="nav-tab ';
                if ( $current_tab == $name ) echo 'nav-tab-active';
                echo '">' . $label . '</a>';
            }
        ?>
    </h2>

     <?php
        switch ( $current_tab ) {
            case "system_status":
                if ( current_user_can( 'administrator' ) ) {
                    include_once( $this->plugin_dir . 'includes/admin/dashboard_system_status.php' );
                } else {
                    do_action( 'wp_client_redirect', admin_url( 'admin.php?page=wpclients' ) );
                    exit;
                }
            break;
            default:
                if ( current_user_can( 'administrator' ) ) {
                    include_once( $this->plugin_dir . 'includes/admin/dashboard_dashboard.php' );
                } else {
                    include_once( $this->plugin_dir . 'includes/admin/dashboard_managers.php' );
                }

            break;
        }
     ?>


    <?php
        if ( false === get_option( 'whtlwpc_settings' ) ) {
    ?>
    <div class="wpc_banners">
        <a href="http://wp-client.com"><img src="<?php echo $this->plugin_url . 'images/banners/banner_top.png' ?>" width="250" /></a>
        <a href="http://translate.wp-client.com"><img src="<?php echo $this->plugin_url . 'images/banners/banner_translate.png' ?>" width="250" /></a>
        <a href="http://wp-client.com/support-please-work-through-checklist/"><img src="<?php echo $this->plugin_url . 'images/banners/banner_support.png' ?>" width="250" /></a>
    </div>
    <?php } ?>
</div>