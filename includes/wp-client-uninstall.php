<?php
// If uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
   exit();

global $wpdb;


/*
* Delete "uploads/wpclient/" folder and files
*/
$uploads = wp_upload_dir();
wpc_client_rrmdir( $uploads['basedir'] . '/wpclient/' );

function wpc_client_rrmdir( $dir ) {
    if ( is_dir( $dir ) ) {
        $objects = scandir( $dir );
        foreach ( $objects as $object ) {
            if ( $object != '.' && $object != '..' ) {
                if ( is_dir( $dir . '/' . $object ) ) {
                    wpc_client_rrmdir( $dir . '/' . $object );
                } else {
                    unlink( $dir . '/' . $object );
                }
            }
        }
        rmdir( $dir );
    }
}


/*
* Delete all options
*/
delete_option( 'parent_page_id' );
delete_option( 'parent_title' );
delete_option( 'hub_template' );
delete_option( 'client_template' );
delete_option( 'wpc_show_link' );
delete_option( 'wpc_create_client' );
delete_option( 'wpc_link_text' );
delete_option( 'wpc_custom_menu' );
delete_option( 'wpc_notify_message' );
delete_option( 'wpc_login_alerts' );
delete_option( 'wpc_custom_menu_logged_out' );
delete_option( 'wpc_custom_menu_logged_in' );
delete_option( 'wpc_show_custom_menu' );
delete_option( 'wpc_graphic' );
delete_option( 'wpc_notify_message2' );
delete_option( 'clients_page' );
delete_option( 'wpc_settings' );
delete_option( 'client_com' );
delete_option( 'hub_com' );
delete_option( 'custom_login_options' );
delete_option( 'wp_client_lite_ver' );
delete_option( 'sender_email' );
delete_option( 'sender_name' );
delete_option( 'new_subject' );
delete_option( 'update_subject' );
delete_option( 'new_email_client_template' );
delete_option( 'update_client_page_email_template' );
delete_option( 'show_sort' );
delete_option( 'wpclients_theme' );
delete_option( 'wp-password-generator-opts' );
delete_option( 'wpc_new_ver' );
delete_option( 'wpc_new_ver_check' );
delete_option( 'wpc_templates' );
delete_option( 'wpc_disable_jquery' );
delete_option( 'wpc_custom_fields' );
delete_option( 'wpc_activated_addons' );
delete_option( 'widget_wpc_client_widget' );
delete_option( 'wpc_p_registration_settings' );
delete_option( 'wpc_client_sync_key' );
delete_option( 'wpc_currency' );
delete_option( 'wpc_limit_ips' );
delete_option( 'wpc_admin_notices' );

//Feedback Wizard options
delete_option( 'wpc_feedback_types' );
delete_option( 'wpc_fbw_templates' );



/*
* Delete all plugin users
*/
$clients_id = get_users( array( 'role' => 'wpc_client', 'fields' => 'ID', ) );
if ( is_array( $clients_id ) && 0 < count( $clients_id ) )
    foreach( $clients_id as $user_id )
        wp_delete_user( $user_id );


/*
* Remove all plugin roles
*/
global $wp_roles;
//remore roles
$wp_roles->remove_role( "pcc_client" );
$wp_roles->remove_role( "wpc_client" );



/*
* Remove all hub pages
*/
$args = array(
    'numberposts'   => -1,
    'post_type'     => 'hubpage',
);
$hub_pages = get_posts( $args );
if ( is_array( $hub_pages ) && 0 < count( $hub_pages ) ) {
    foreach( $hub_pages as $hub_page )
        wp_delete_post( $hub_page->ID );
}



/*
* Remove all clients pages
*/
$args = array(
    'numberposts' => -1,
    'post_type' => 'clientspage',
);
$clint_pages = get_posts( $args );
if ( is_array( $clint_pages ) && 0 < count( $clint_pages ) ) {
    foreach( $clint_pages as $clint_page )
        wp_delete_post( $clint_page->ID );
}



/*
* Remove all plugin pages
*/
$args = array(
    'hierarchical'  => 0,
    'meta_key'      => 'wpc_client_page',
    'post_type'     => 'page',
    'post_status'   => 'publish,trash,pending,draft,auto-draft,future,private,inherit',
);
$wpc_client_pages = get_pages( $args );
if ( is_array( $wpc_client_pages ) && 0 < count( $wpc_client_pages ) ) {
    foreach( $wpc_client_pages as $wpc_client_page )
        wp_delete_post( $wpc_client_page->ID, true );
}







/*
* Delete all tables
*/
//tables name
$tables = array(
    'wpc_client_clients_page',
    'wpc_client_login_redirects',
    'wpc_client_comments',
    'wpc_client_file_categories',
    'wpc_client_objects_assigns',
    'wpc_client_files',
    'wpc_client_groups',
    'wpc_client_group_clients',
    'wpc_client_categories',
    'wpc_client_files_download_log',

    //payments table
    'wpc_client_payments',

);

//remove all tables
foreach( $tables as $key ) {
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$key}'" ) == "{$wpdb->prefix}{$key}" ) {
        $wpdb->query( "DROP TABLE {$wpdb->prefix}{$key}" );
    }
}




//delete pages
$wpc_pages = $this->cc_get_settings( 'pages' );
if ( is_array( $wpc_pages ) && count( $wpc_pages ) ) {
    foreach( $wpc_pages as $key => $val ) {
        if ( is_int( $val ) )
            wp_delete_post( $val, true );

    }

}


$this->cc_delete_settings( 'pages' );
$this->cc_delete_settings( 'client_flags' );



$this->cc_delete_settings( 'general' );
$this->cc_delete_settings( 'clients_staff' );
$this->cc_delete_settings( 'file_sharing' );
$this->cc_delete_settings( 'custom_login' );
$this->cc_delete_settings( 'business_info' );
$this->cc_delete_settings( 'enable_custom_redirects' );
$this->cc_delete_settings( 'default_redirects' );
$this->cc_delete_settings( 'capabilities' );
$this->cc_delete_settings( 'gateways' );
$this->cc_delete_settings( 'custom_titles' );
$this->cc_delete_settings( 'login_alerts' );
$this->cc_delete_settings( 'skins' );
$this->cc_delete_settings( 'smtp' );
$this->cc_delete_settings( 'reply_email' );


//templates
$this->cc_delete_settings( 'templates_hubpage' );
$this->cc_delete_settings( 'templates_clientpage' );
$this->cc_delete_settings( 'templates_emails' );
$this->cc_delete_settings( 'templates_shortcodes' );

$ez_hubs = $this->cc_get_settings('ez_hub_templates');

if( is_array( $ez_hubs ) && count( $ez_hubs ) ) {
    foreach( $ez_hubs as $key=>$value ) {
        $this->cc_delete_settings( 'ez_hub_' . $key );
    }
}
$this->cc_delete_settings('ez_hub_templates');


//action for extensions
do_action( 'wp_client_uninstall' );

?>