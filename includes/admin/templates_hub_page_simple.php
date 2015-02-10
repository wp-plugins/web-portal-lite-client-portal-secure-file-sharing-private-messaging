<?php
$error = "";

//save user data
if ( isset( $_REQUEST['update_hub_template'] ) ) {
    // validate at php side

    if ( !isset( $_REQUEST['hub_template']['name'] ) || empty( $_REQUEST['hub_template']['name'] ) ) {
        $error .= __('A Template Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);
    }

    if ( empty( $error ) ) {
        $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );

        if ( isset( $_REQUEST['id'] ) && '' != $_REQUEST['id'] ) {
            $tmp_id = $_REQUEST['id'];
        } else {
            $tmp_id = time();
        }

        $wpc_ez_hub_templates[$tmp_id]['name']              = $_REQUEST['hub_template']['name'];
        //$wpc_ez_hub_templates[$tmp_id]['content']           = $tmp_id . '_hub_content';
        $wpc_ez_hub_templates[$tmp_id]['general']           = $_REQUEST['hub_template']['general'];
        $wpc_ez_hub_templates[$tmp_id]['type']              = 'simple';
        //$wpc_ez_hub_templates[$tmp_id]['tabs_content']      = $tmp_id . '_hub_tabs_content';

        $content = $_REQUEST['hub_template']['content'] ;

        $target_path = $this->get_upload_dir( 'wpclient/_hub_templates/' );

        if ( is_dir( $target_path ) ) {

            $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
            fwrite( $content_file, $content );
            fclose( $content_file );

            $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
            fwrite( $tabs_content_file, $content );
            fclose( $tabs_content_file );
        }

        do_action( 'wp_client_settings_update', $wpc_ez_hub_templates, 'ez_hub_templates' );

        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_simple_template&id=' . $tmp_id . '&msg=u' );
        exit;
    }

}

//change text
if ( 'add_simple_template' == $_GET['action'] )
    $button_text = __( 'Add Simple HUB Template', WPC_CLIENT_TEXT_DOMAIN );
else
    $button_text =  __( 'Update Simple HUB Template', WPC_CLIENT_TEXT_DOMAIN );


if ( isset( $_REQUEST['hub_template'] ) ) {
    $hub_template      = $_REQUEST['hub_template'];

} elseif ( isset( $_REQUEST['id'] ) && '' != $_REQUEST['id'] ) {

    $wpc_ez_hub_templates   = $this->cc_get_settings( 'ez_hub_templates' );
    $hub_template           = isset( $wpc_ez_hub_templates[$_REQUEST['id']] ) ? $wpc_ez_hub_templates[$_REQUEST['id']]: array();

    if( isset( $hub_template['type'] ) && 'ez' == $hub_template['type'] && 'edit_simple_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_ez_template&id=' . $_REQUEST['id'] );
        exit;
    } elseif( isset( $hub_template['type'] ) && 'advanced' == $hub_template['type'] && 'edit_simple_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $_REQUEST['id'] );
        exit;
    }

} ?>


<h2><?php echo $button_text ?></h2>

<div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>


<form name="edit_hub_template" id="edit_hub_template" method="post" >

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2 not_bold">
            <div id="post-body-content">
                <div id="titlediv">
                    <div id="titlewrap">
                        <label for="hub_template_name"><strong><?php _e( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>:</strong></label>
                        <br>
                        <input type="text" name="hub_template[name]" id="hub_template_name" value="<?php echo ( isset( $hub_template['name'] ) ? stripslashes( html_entity_decode( $hub_template['name'] ) ) : '' )?>"  class="max_width" />
                    </div>
                </div>
                <br />
                <div id="postdivrich" class="postarea edit-form-section">
                    <label for="hub_template_content"><strong><?php _e( 'HUB Content', WPC_CLIENT_TEXT_DOMAIN ) ?>:</strong></label>
                    <div>
                        <div style="float: left; margin: 0px 20px 0px 0px;" class="validate_page_icon_attention"></div>
                        <span class="description">
                            <?php printf( __( '<b>NOTE:</b> You can use the Visual Editor, or write HTML in the Text Editor. When finished, simply assign the newly created HUB Template to the desired %s, and place the shortcode [wpc_client_hub_page_template /] in the HUB Content tab in the Templates Menu.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?>
                        </span>
                    </div>
                    <br>
                    <div class="postarea">
                        <span class="description"><?php printf ( __( 'Use the space below to design a HUB Template in the same manner you would design a standard WordPress page. You can implement %s functionality by inserting the corresponding shortcodes and placeholders into the Template. All available %s placeholders and shortcodes can be found by clicking the shortcode/placeholder browser button in the Visual Editor menu.', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'], $this->plugin['title'] ) ?></span>

                        <?php
                        //$hub_template_content = isset( $hub_template['content'] ) ? stripslashes( html_entity_decode( $hub_template['content'] ) ) : '';

                        $hub_template_content = '';

                        if ( isset( $_GET['id'] ) ) {
                            $id_hub = $_GET['id'] ;
                            $handle = fopen( $this->get_upload_dir( 'wpclient/_hub_templates/' ) . $id_hub . '_hub_content.txt', 'rb' );
                            if ( $handle !== false ) {
                                rewind( $handle ) ;
                                while ( !feof( $handle ) ) {
                                    $hub_template_content .= fread( $handle, 8192 );
                                }
                            }
                            fclose( $handle );
                            $hub_template_content = stripslashes( html_entity_decode( $hub_template_content ) ) ;
                        } elseif ( isset( $_REQUEST['hub_template']['content'] ) ) {
                            $hub_template_content = $_REQUEST['hub_template']['content'];
                        }
                        wp_editor( $hub_template_content, 'hub_template_content', array( 'textarea_name' => 'hub_template[content]', 'textarea_rows' => 15, 'media_buttons' => false  ) ) ?>

                    </div>
                </div>
            </div><!-- #post-body-content -->
            <div id="postbox-container-1" class="postbox-container">

                <div id="side-info-column" class="inner-sidebar">
                    <?php
                        do_meta_boxes( 'wp_client_edit_simple_hub', 'side', ( isset( $hub_template ) ) ? $hub_template : array() ) ;
                    ?>
                </div>
             </div>
        </div><!-- #post-body -->
    </div> <!-- #poststuff -->
</form>


<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery( document ).ready( function( $ ) {

    });

</script>