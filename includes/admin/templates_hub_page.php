<?php
if ( isset( $_GET['action'] ) ) {
    switch( $_GET['action'] ) {
       case 'delete':
            if ( wp_verify_nonce( $_GET['_wpnonce'], 'wpc_ez_hub_delete' . $_GET['id'] . get_current_user_id() ) && $this->get_id_simple_temlate() != $_GET['id'] ) {
                $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );

                unset( $wpc_ez_hub_templates[$_GET['id']] );
                do_action( 'wp_client_settings_update', $wpc_ez_hub_templates, 'ez_hub_templates' );

                $this->cc_delete_settings( 'ez_hub_' . $_GET['id'] );

                $path = $this->get_upload_dir( 'wpclient/_hub_templates/' ) ;
                if ( file_exists( $path . $_GET['id'] . '_hub_tabs_content.txt' ) )
                    unlink( $path . $_GET['id'] . '_hub_tabs_content.txt' ) ;
                if ( file_exists( $path . $_GET['id'] . '_hub_content.txt' ) )
                    unlink( $path . $_GET['id'] . '_hub_content.txt' ) ;

                do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&msg=d' );
                exit;
            } else {
                do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage' );
                exit;
            }
         break;

       case 'add_ez_template':
       case 'edit_ez_template':
            include $this->plugin_dir . 'includes/admin/templates_hub_page_ez.php';
         break;
       case 'add_advanced_template':
       case 'edit_advanced_template':
            include $this->plugin_dir . 'includes/admin/templates_hub_page_advanced.php';
         break;
       case 'add_simple_template':
       case 'edit_simple_template':
            include $this->plugin_dir . 'includes/admin/templates_hub_page_simple.php';
         break;
    }
} else {

$current_page = 'wpclients_templates_ez_hub';
$wpc_ez_hub_templtes = $this->cc_get_settings( 'ez_hub_templates' );
?>


<script type="text/javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery(document).ready(function(){

        jQuery(".over").hover(function(){
            jQuery(this).css("background-color","#bcbcbc");
            },function(){
            jQuery(this).css("background-color","transparent");
        });

        jQuery('.default_template_option').click(function() {
            var value = jQuery(this).val();
            jQuery(this).hide();
            jQuery( '#wpc_ajax_loading_' + value ).addClass( 'wpc_ajax_loading' );
            var obj = jQuery(this);
            jQuery.ajax({
                type: 'POST',
                dataType    : 'json',
                url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                data: 'action=wpc_ez_hub_set_default&id=' + value,
                success: function( data ){
                    jQuery( '#wpc_ajax_loading_' + value ).removeClass( 'wpc_ajax_loading' );
                    obj.show();
                    if( !data.status ) {
                        alert( data.message );
                    }
                }
             });
        });

    });

</script>


<?php
if ( isset( $_GET['msg'] ) ) {
    $msg = $_GET['msg'];
    switch( $msg ) {
        case 'a':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
        case 'd':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Template <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
            break;
    }
}
?>

<div class="icon32" id="icon-link-manager"></div>
<h2><?php _e( 'HUB Page Templates', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

<div class="updated wpc_notice"  style="float: right; width: 360px; text-align: right; padding: 7px 7px 7px 7px; margin: 0px 0px 15px 0px">
    <div style="float: left; margin: 0px 20px 0px 0px;" class="validate_page_icon_attention"></div>
    <span class="description">
    <?php printf ( __( '<b>NOTE:</b> To use the HUB templates below, be sure to use the following shortcode in the HUB Content field: %s', WPC_CLIENT_TEXT_DOMAIN ), '<b>[wpc_client_hub_page_template /]</b>' ) ?>
    </span>
</div>

<p>
    <?php printf( __( 'You can create custom EZ HUB and Advanced HUB templates, and assign them to specific %s/Members and/or %s.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['p'] ) ?>
    <br />
    <?php printf( __( '%s/Members will see the default HUB template if no custom template is assigned to them.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?>
</p>
<br />

<div>
    <a href="admin.php?page=wpclients_templates&tab=hubpage&action=add_ez_template" class="add-new-h2"><?php _e( 'Create EZ HUB Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
    <a href="admin.php?page=wpclients_templates&tab=hubpage&action=add_advanced_template" class="add-new-h2"><?php _e( 'Create Advanced HUB Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
    <a href="admin.php?page=wpclients_templates&tab=hubpage&action=add_simple_template" class="add-new-h2"><?php _e( 'Create Simple HUB Template', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
</div>

<hr />

<div class="content23 news">
    <p>
    <?php
         printf( __( 'Default template users for %s who are not assigned to any HUB template.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] );
     ?>
     </p>

    <table class="widefat">
        <thead>
            <tr>
                <th style="width: 70px; text-align: center;"><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Template Type', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Clients', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Circles', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th style="width: 70px; text-align: center;"><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Template Type', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Clients', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
                <th><?php _e( 'Circles', WPC_CLIENT_TEXT_DOMAIN ) ?></th>
            </tr>
        </tfoot>
        <tbody>
    <?php
    if ( is_array( $wpc_ez_hub_templtes ) ) {

        $default_flag = false;
        foreach ( $wpc_ez_hub_templtes as $key => $template ) {
            if( isset( $template['is_default'] ) && 1 == $template['is_default'] ) $default_flag = true;
        }
        if( !$default_flag ) {
            $not_del = $this->get_id_simple_temlate() ;
            if( $not_del )
                $wpc_ez_hub_templtes[ $not_del ]['is_default'] = 1;
        }

        foreach ( $wpc_ez_hub_templtes as $key => $template ) {
    ?>
        <tr class='over'>
            <td style="width: 70px; text-align: center;">
                <span id="wpc_ajax_loading_<?php echo $key; ?>"></span>
                <input type="radio" name="default_template_option" class="default_template_option" value="<?php echo $key; ?>" <?php checked( isset( $template['is_default'] ) && 1 == $template['is_default'] ); ?> />
            </td>
            <td>
                <?php echo $template['name'] ?>
                <div class="row-actions">
                <?php if ( isset( $template['type'] ) && 'ez' == $template['type'] ) { ?>
                    <span class="edit"><a href="admin.php?page=wpclients_templates&tab=hubpage&action=edit_ez_template&id=<?php echo $key ?>"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
                <?php } elseif( ( isset( $template['type'] ) && 'advanced' == $template['type'] ) || !isset( $template['type'] ) ) { ?>
                    <span class="edit"><a href="admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=<?php echo $key ?>"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
                <?php } elseif( ( isset( $template['type'] ) && 'simple' == $template['type'] ) ) { ?>
                    <span class="edit"><a href="admin.php?page=wpclients_templates&tab=hubpage&action=edit_simple_template&id=<?php echo $key ?>"><?php _e( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
                <?php }
                    if ( !isset( $template['not_delete'] ) ) {
                    ?>
                    <span class="delete"> | <a onclick='return confirm("<?php _e( 'Are you sure to delete this Template', WPC_CLIENT_TEXT_DOMAIN ) ?>");' href="admin.php?page=wpclients_templates&tab=hubpage&action=delete&id=<?php echo $key ?>&_wpnonce=<?php echo wp_create_nonce( 'wpc_ez_hub_delete' . $key . get_current_user_id() ) ?>"><?php _e( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
                    <?php
                    }
                    ?>
                </div>
            </td>
            <td class="author column-author">
                <?php
                if ( isset( $template['type'] ) ) {
                    switch( $template['type'] ) {
                        case 'ez' :
                                echo 'EZ';
                                break;
                        case 'advanced' :
                                echo 'Advanced';
                                break;
                        case 'simple' :
                                echo 'Simple';
                                break;
                        default:
                                echo $template['type'];
                                break;
                    }
                }
                ?>
            </td>
            <td class="author column-author">
                <?php /*if ( 'default' == $key ) {
                    _e( 'All (if not assigned a custom HUB template)', WPC_CLIENT_TEXT_DOMAIN );
                } else {  */
                    $clients_ids = $this->cc_get_assign_data_by_object( 'ez_hub', $key, 'client' );
                    $link_array = array(
                        'title'   => sprintf( __( 'Assign clients to %s', WPC_CLIENT_TEXT_DOMAIN ), $template['name'] ),
                        'data-ajax' => true,
                        'data-id' => $key,
                    );
                    $input_array = array(
                        'name'  => 'wpc_clients_ajax[]',
                        'id'    => 'wpc_clients_' . $key,
                        'value' => implode( ',', $clients_ids )
                    );
                    $additional_array = array(
                        'counter_value' => count( $clients_ids )
                    );
                    $this->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                //} ?>
            </td>
            <td class="author column-author">
                <?php /*if ( 'default' == $key ) { ?>
                -
                <?php } else {  */
                    $groups_ids = $this->cc_get_assign_data_by_object( 'ez_hub', $key, 'circle' );
                    $link_array = array(
                        'data-id' => $key,
                        'data-ajax' => 1,
                        'title'   => sprintf( __( 'Assign %s to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'], $template['name'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_circles_ajax[]',
                        'id'    => 'wpc_circles_' . $key,
                        'value' => implode( ',', $groups_ids )
                    );
                    $additional_array = array(
                        'counter_value' => count( $groups_ids )
                    );
                    $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                //} ?>
            </td>
        </tr>
    <?php
        }
    }
    ?>
        </tbody>
    </table>

</div>

<?php

}

?>