<?php
/*
Plugin Name: Web Portal Lite
Plugin URI: http://www.WP-Client.com
Description:  Web Portal Lite is a Client Management Plugin that gives you the power to add a private and secure client portal to your existing WordPress site. Create clients yourself, or allow them to self-register, and give clients access to private pages and other resources. Upgrade to PRO for more features like file sharing, private messaging, invoicing, and much more.
Author: WP-Client.com
Version: 1.0.5
Author URI: http://www.WP-Client.com
*/


class wpcclientlite {}


if ( class_exists( "wpc_client_lite" ) ) {
    echo "You can not use Lite and Pro versions of the plugin at the same time.";
    exit;
} else {

    //current plugin version
    define( 'WPC_CLIENT_LITE_VER', '1.0.5' );
    define( 'WP_PASSWORD_GENERATOR_VERSION_WPCLIENT', '2.2' );

    // The text domain for strings localization
    define( 'WPC_CLIENT_TEXT_DOMAIN', 'wp-client' );

    require_once 'includes/class.common.php';

    if ( defined( 'DOING_AJAX' ) ) {
        require_once 'includes/class.admin_common.php';
        require_once 'includes/class.ajax.php';
    } elseif ( is_admin() ) {
        require_once 'includes/class.admin_common.php';
        require_once 'includes/class.admin_menu.php';
        require_once 'includes/class.admin_meta_boxes.php';
        require_once 'includes/class.admin.php';
    } else {
        require_once 'includes/class.user_common.php';
        require_once 'includes/class.user_shortcodes.php';
        require_once 'includes/class.user.php';
    }

    /////////// Add widget login/logout ///////////////
    require_once 'includes/widget.php';

    /////////// Add widget Portal Page list ///////////////
    require_once 'includes/widget_pp.php';

    //include payments core
    if ( defined( 'WPC_CLIENT_PAYMENTS' ) ) {
        include_once 'includes/payments_core.php';
    }




}

?>
