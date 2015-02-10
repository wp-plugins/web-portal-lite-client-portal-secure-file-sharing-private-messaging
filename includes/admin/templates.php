<?php

//remove buttons for editor
//todelete?
//remove_all_filters( 'mce_external_plugins' );
?>


<?php
if ( isset( $_GET['msg'] ) ) {
    switch( $_GET['msg'] ) {
        case 'u':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template Updated.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
        case 'hub_updated':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template and all HUB pages are Updated.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
    }
}
?>


<style type="text/css">
    .wp-editor-container {
        background-color: #fff;
    }

    .wpc_clear{
        clear: both;
        height: 0;
        visibility: hidden;
        display: block;
    }
    a{
        text-decoration: none;
    }
    /******* GENERAL RESET *******/

    /******* MENU *******/
    #container23{

        width: 99%;
    }
    #container23 ul{
        list-style: none;
        list-style-position: outside;
    }
    #container23 ul.menu li{
        float: left;
        margin-right: 5px;
        margin-bottom: -1px;
    }
    #container23 ul.menu li{
        font-weight: 700;
        display: block;
        padding: 5px 10px 5px 10px;
        background: #efefef;
        margin-bottom: -1px;
        border: 1px solid #d0ccc9;
        border-width: 1px 1px 1px 1px;
        position: relative;
        color: #898989;
        cursor: pointer;
    }
    #container23 ul.menu li.active{
        background: #fff;
        top: 1px;
        border-bottom: 0;
        color: #5f95ef;
    }
    /******* /MENU *******/

    /******* NEWS *******/
    .content23.news h1{
        background: transparent url(images/news.jpg) no-repeat scroll left top;
    }
    .content23.news{
        display: block;
    }
    /******* /NEWS *******/
    /******* TUTORIALS *******/
    .content23.tutorials h1{
        background: transparent url(images/tuts.jpg) no-repeat scroll left top;
    }
    .content23.tutorials{
        display: none;
    }
    /******* /TUTORIALS *******/
    /******* LINKS *******/
    .content23.links h1{
        background: transparent url(images/links.jpg) no-repeat scroll left top;
    }
    .content23.links{
        display: none;
    }

    .content23.links a{
        color: #5f95ef;
    }
    /******* /LINKS *******/
    /******* Feedback Wizard *******/
    .content23.fbw_tempaltes h1{
        background: transparent no-repeat scroll left top;
    }
    .content23.fbw_tempaltes{
        display: none;
    }
    .content23.fbw_tempaltes a{
        color: #5f95ef;
    }
    .other {
        display: none;
    }
    #tabs, #email_tabs {
        width: 100%;
        border: 0 !important;
        padding: 0 !important;
    }
    #tabs ul, #email_tabs ul {
        padding-right: 5px;
        background: #ccc;
    }
    #tabs > div, #email_tabs > div {
        float: right;
        padding: 0px;
        margin: 0px;
        /*padding-right: 8px;*/
        width: 70%;
    }
    /*.ui-tabs-vertical { width: 55em; }*/
    .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 28%; }
    .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
    .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
    .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right;}
    .ui-tabs .ui-tabs-hide {display: none;}
    .ui-tabs-nav li a {
        display: block;
        width: 99% !important;
        padding: 3% 2% !important;
        overflow: hidden;
        text-overflow: ellipsis;
        -o-text-overflow: ellipsis;
        white-space: nowrap;
    }

    /******* /LINKS *******/
    .db_template, .file_template {
        border:none;
        resize:none;
        height: 200px;
        width: 500px;
        margin: -14px 0 5px 0;
        overflow: scroll;
    }
    .compare_template {
        padding:5px 0px;
        margin:-14px 0 5px 0;
        height: 200px;
        width: 1020px;
        overflow: scroll;
        background-color: #fff;
    }
    .update_template {
        position: absolute;
        top: 4px;
        right: 10px;
        width: 60px;
        height: 22px;
    }
    .ui-state-default {
        font-size: 100% !important;
        font-family: Verdana,Arial,sans-serif !important/*{ffDefault}*/;
    }


    #email_tabs .wpc_templates_enable{
        margin: 0px 0px 0px 0px !important;
    }


</style>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function() {
        <?php if ( isset( $_GET['set_tab'] ) && '' != $_GET['set_tab'] ) { ?>
            jQuery("#other").trigger('click');
        <?php } ?>
    });

</script>

<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="container23">

        <ul class="menu">
                <?php
                    $tabs = array(
                        'hub_preset'        => __( 'HUB Content', WPC_CLIENT_TEXT_DOMAIN ),
                        'hubpage'           => __( 'HUB Page Templates', WPC_CLIENT_TEXT_DOMAIN ),
                        'portal_page'       => sprintf( __( '%s Template', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                        'emails'            => '<span class="wpc_pro_settings_link">' . __( 'Emails Templates', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                        'shortcodes'        => '<span class="wpc_pro_settings_link">' . __( 'Shortcodes Templates', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>',
                    );

                    $tabs = apply_filters( 'wpc_client_templates_tabs_array', $tabs );

                    $current_tab = ( empty( $_GET['tab'] ) ) ? 'hub_preset' : urldecode( $_GET['tab'] );

                    foreach ( $tabs as $name => $label ) {
                        $active = ( $current_tab == $name ) ? 'class="active"' : '';
                        echo '<li ' . $active . '><a href="' . admin_url( 'admin.php?page=wpclients_templates&tab=' . $name ) . '" >' . $label . '</a></li>';
                    }

                    do_action( 'wpc_client_templates_tabs' );
                ?>

        </ul>


        <span class="wpc_clear"></span>

        <div class="content23 news">
            <?php
                switch ( $current_tab ) {
                    case "hub_preset":
                        include_once( $this->plugin_dir . 'includes/admin/templates_hub_preset.php' );
                    break;
                    case "hubpage":
                        include_once( $this->plugin_dir . 'includes/admin/templates_hub_page.php' );
                    break;
                    case "portal_page":
                        include_once( $this->plugin_dir . 'includes/admin/templates_portal_page.php' );
                    break;
                    case "emails":
                        do_action( 'wp_client_redirect', get_admin_url(). 'admin.php?page=wpclients_pro_features#templates_emails' );
                        exit;
                    break;
                    case "shortcodes":
                        do_action( 'wp_client_redirect', get_admin_url(). 'admin.php?page=wpclients_pro_features#templates_shortcodes' );
                        exit;
                    break;
                    default:
                        do_action( 'wpc_client_templates_tabs_' . $current_tab );
                    break;
                }
            ?>
        </div>

    </div>
</div>