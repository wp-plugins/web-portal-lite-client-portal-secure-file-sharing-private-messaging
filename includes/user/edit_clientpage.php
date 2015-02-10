<?php
global $wp_query, $wpdb;

$error = '';

if ( !current_user_can( 'wpc_client' ) )
   return __( 'Sorry, you do not have permission to see this page!', WPC_CLIENT_TEXT_DOMAIN );


//remove buttons for editor
//todelete?
//remove_all_filters( 'mce_external_plugins' );


$edit_page = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );
if ( !$edit_page ) {
    do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
    exit;
}


$user_ids       = $this->cc_get_assign_data_by_object( 'portal_page', $edit_page->ID, 'client' );
$groups_id      = $this->cc_get_assign_data_by_object( 'portal_page', $edit_page->ID, 'circle' );

$user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

//get clients from Client Circles
if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
    foreach( $groups_id as $group_id ) {
        $user_ids = array_merge ( $user_ids, $this->cc_get_group_clients_id( $group_id ) );
    }

if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
    $user_ids = array_unique( $user_ids );

//client hasn't access to this page
if ( ( empty( $user_ids ) || !in_array( $user_id, $user_ids ) ) ) {
    do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
    exit;
}

//portal page cann't be edited
if ( 1 != get_post_meta( $edit_page->ID, 'allow_edit_clientpage', true ) ) {
    do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
    exit;
}


//get portal page
$clientpage = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts WHERE ID = %d AND post_type = 'clientspage' ", $edit_page->ID ), "ARRAY_A" );

if ( !is_array( $clientpage ) )
    printf( __( "Wrong %s.", WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] );

?>

<div class='registration_form'>

    <div id="message" class="updated wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

    <form method="post" name="edit_clientpage" id="edit_clientpage" >
        <input type="hidden" name="wpc_action" id="wpc_action" value="" />
        <input type="hidden" name="wpc_wpnonce" id="wpc_wpnonce" value="<?php echo wp_create_nonce( 'wpc_edit_clientpage' . $clientpage['ID'] ) ?>" />

        <div id="titlewrap">
            <input type="text" name="clientpage_title" autocomplete="off"  value="<?php echo ( isset( $_POST['clientpage_title'] ) ) ? $_POST['clientpage_title'] : $clientpage['post_title'] ?>" style="width: 100%;" >
        </div>

        <div class="postarea" id="postdivrich">
            <?php
            $clientpage_content = ( isset( $_POST['clientpage_content'] ) ) ? $_POST['clientpage_content'] : $clientpage['post_content'];
            $show = ( current_user_can( 'wpc_add_media' ) ) ? true : false ;
            wp_editor($clientpage_content, 'clientpage_content', array( 'media_buttons' => $show ) );
            ?>
        </div>

        <br clear="all" />
        <br clear="all" />

        <div>
           <input type="button" name="" id="update" value="<?php _e( 'Update', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
           <input type="button" name="" id="cancel" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
           <input type="button" name="" id="delete" value="<?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
        </div>

    </form>
</div>
<?php

?>
