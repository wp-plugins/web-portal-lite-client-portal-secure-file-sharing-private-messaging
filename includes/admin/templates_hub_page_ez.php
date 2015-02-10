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

        $wpc_ez_hub_settings = ( isset( $_REQUEST['hub_settings'] ) ) ? $_REQUEST['hub_settings'] : array();

        do_action( 'wp_client_settings_update', $wpc_ez_hub_settings, 'ez_hub_' . $tmp_id );

        $wpc_ez_hub_templates[$tmp_id]['name']              = $_REQUEST['hub_template']['name'];
        //$wpc_ez_hub_templates[$tmp_id]['content']           = $tmp_id . '_hub_content';
        $wpc_ez_hub_templates[$tmp_id]['general']           = $_REQUEST['hub_template']['general'];
        $wpc_ez_hub_templates[$tmp_id]['type']              = 'ez';
        //$wpc_ez_hub_templates[$tmp_id]['tabs_content']      = $tmp_id . '_hub_tabs_content';

        $content = $_REQUEST['hub_template']['content'] ;

        $target_path = $this->get_upload_dir( 'wpclient/_hub_templates/' );

        if ( is_dir( $target_path ) ) {

            $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
            fwrite( $content_file, $content );
            fclose( $content_file );

            $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
            fwrite( $tabs_content_file, wpc_get_ez_hub_tabs_content( $tmp_id, $content ) );
            fclose( $tabs_content_file );
        }

        do_action( 'wp_client_settings_update', $wpc_ez_hub_templates, 'ez_hub_templates' );

        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_ez_template&id=' . $tmp_id . '&msg=u' );
        exit;
    }

}

function wpc_get_ez_hub_tabs_content( $template_id, $hub_templates ) {
    global $wpc_client;

    $hub_settings = $wpc_client->cc_get_settings( 'ez_hub_' . $template_id );

    if ( !is_array( $hub_settings ) || !count( $hub_settings ) ) {
        $ez_hub_default = $this->get_id_simple_temlate() ;
        $hub_settings = $wpc_client->cc_get_settings( 'ez_hub_' . $ez_hub_default );
    }

    $menu = '';
    $tabs_items = array();

    //class for tabs
//    $menu_class = ( isset( $hub_settings['general']['menu_type'] ) && 'vtab' == $hub_settings['general']['menu_type'] ) ? 'ui-tabs-vertical' : '';


    //filter tabs
    if ( is_array( $hub_settings ) && count( $hub_settings ) ) {
        foreach( $hub_settings as $setting ) {
            if ( !is_array( $setting ) )
                continue;

            $key = array_keys( $setting );
            $setting = array_values( $setting );

            $tabs_items = apply_filters( 'wpc_client_get_ez_shortcode_' . $key[0], $tabs_items, $setting[0] );
        }
    }


//    $tabs_items = apply_filters( 'wpc_client_ez_hub_tabs_items', $tabs_items, $hub_settings );

    if ( count( $tabs_items ) ) {
        ob_start();
        $f_tab =  current( $tabs_items[0] );
        $f_tab = is_array( $f_tab ) ? $f_tab : array();
        $f_tab_key = array_keys( $f_tab );
        $f_tab_value = array_values( $f_tab );
        ?>

        <div class="wpc-toolbar clearfix">
            <ul class="nav nav-pills pull-left">
                <li class="wpc-hub-toolbar-dropdown dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle">
                    <?php
                    if ( !isset( $hub_templates['general']['enable_icons'] ) || 'yes' == $hub_templates['general']['enable_icons'] ) {
                        $icon_url = $wpc_client->plugin_dir . 'images/hub_icons/icon_' . $f_tab_key[0] . '.png';
                        if ( file_exists( $icon_url ) ) {
                            $icon_url = $wpc_client->plugin_url . 'images/hub_icons/icon_' . $f_tab_key[0] . '.png';
                            echo '<img alt="' . $f_tab_value[0] . '" src="' . $icon_url . ' ">';
                        } else {
                            $icon_url = $wpc_client->plugin_url . 'images/hub_icons/icon_option.png';
                            echo '<img alt="' . $f_tab_value[0] . '" src="' . $icon_url . ' ">';
                        }
                    }
                    ?>
                        <?php echo $f_tab_value[0] ?>
                        <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu">
                    <?php
                    $i = 0;
                    foreach( $tabs_items as $item ) {

                        if( !isset( $item['menu_items'] ) || !is_array( $item['menu_items'] ) ) {
                            continue;
                        }

                        $key = array_keys( $item['menu_items'] );
                        $value = array_values( $item['menu_items'] );
                    ?>
                        <div class="">
                            <a href="javascript:void(0);" rel="#bar_item_<?php echo $i ?>" class="bar-link">
                                <?php
                                if ( !isset( $hub_templates['general']['enable_icons'] ) || 'yes' == $hub_templates['general']['enable_icons'] ) {
                                    $icon_url = $wpc_client->plugin_dir . 'images/hub_icons/icon_' . $key[0] . '.png';
                                    if ( file_exists( $icon_url ) ) {
                                        $icon_url = $wpc_client->plugin_url . 'images/hub_icons/icon_' . $key[0] . '.png';
                                        echo '<img alt="' . $value[0] . '" src="' . $icon_url . ' ">';
                                    } else {
                                        $icon_url = $wpc_client->plugin_url . 'images/hub_icons/icon_option.png';
                                        echo '<img alt="' . $value[0] . '" src="' . $icon_url . ' ">';
                                    }
                                }
                                ?>
                                <?php echo $value[0] ?>
                            </a>
                        </div>
                    <?php
                        $i++;
                    }
                    ?>
                    </div>
                </li>
            </ul>

            <?php
            if ( !isset( $hub_templates['general']['logout_link'] ) || 'yes' == $hub_templates['general']['logout_link'] ) {
            ?>
            <ul class="nav nav-pills pull-right">
              <li>[wpc_client_logoutb/]</li>
            </ul>
            <?php
            }
            ?>
        </div>

        <?php
        $i = 0;
        foreach( $tabs_items as $item ) {
        ?>
        <div id="bar_item_<?php echo $i ?>" class="hub_content" style="display: none;">
            <?php echo $item['page_body'] ?>
        </div>

        <?php
            $i++;
        }
        ?>


        <?php
        $menu = ob_get_contents();
        if( ob_get_length() ) {
            ob_end_clean();
        }
    }

    return $menu;
}



//change text
if ( 'add_ez_template' == $_GET['action'] )
    $button_text = __( 'Add EZ HUB Template', WPC_CLIENT_TEXT_DOMAIN );
else
    $button_text =  __( 'Update EZ HUB Template', WPC_CLIENT_TEXT_DOMAIN );


if ( isset( $_REQUEST['hub_template'] ) ) {
    $hub_template      = $_REQUEST['hub_template'];
    $hub_settings      = $_REQUEST['hub_settings'];
} elseif ( isset( $_REQUEST['id'] ) && '' != $_REQUEST['id'] ) {

    $wpc_ez_hub_templates   = $this->cc_get_settings( 'ez_hub_templates' );
    $hub_template           = isset( $wpc_ez_hub_templates[$_REQUEST['id']] ) ? $wpc_ez_hub_templates[$_REQUEST['id']]: array();
    $hub_settings           = $this->cc_get_settings( 'ez_hub_' . $_REQUEST['id'] );

    if( ( 'advanced' == $hub_template['type'] || !isset( $hub_template['type'] ) ) && 'edit_ez_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $_REQUEST['id'] );
        exit;
    } elseif( ( 'simple' == $hub_template['type'] && isset( $hub_template['type'] ) ) && 'edit_ez_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_simple_template&id=' . $_REQUEST['id'] );
        exit;
    }

} else {

    $hub_settings = array(
        0 => array(
            'pages_access' => array(),
        ),
        1 => array(
            'files_uploaded' => array(),
        ),
        2 => array(
            'files_access' => array(),
        ),
        3 => array(
            'upload_files' => array(),
        ),
        4 => array(
            'private_messages' => array(),
        ),


    );
}


$elements = array(
    'pages_access' => __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN ),
    'files_uploaded' => __( 'Files you have uploaded', WPC_CLIENT_TEXT_DOMAIN ),
    'files_access' => __( 'Files you have access to', WPC_CLIENT_TEXT_DOMAIN ),
    'upload_files' => __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ),
    'private_messages' => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
);

$elements = apply_filters( 'wpc_client_get_shortcode_elements', $elements );

?>

    <h2><?php echo $button_text ?></h2>

    <div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>


    <form name="edit_hub_template" id="edit_hub_template" method="post" >

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2 not_bold">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label for="hub_template_name"><strong><?php _e( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ) ?><span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>:</strong></label><br />
                            <input type="text" name="hub_template[name]" id="hub_template_name" value="<?php echo ( isset( $hub_template['name'] ) ? stripslashes( html_entity_decode( $hub_template['name'] ) ) : '' ) ?>" class="max_width" />
                        </div>
                    </div>
                    <br />
                    <div id="postdivrich" class="postarea edit-form-section">
                        <label for="hub_template_content"><strong><?php _e( 'HUB Content', WPC_CLIENT_TEXT_DOMAIN ) ?>:</strong></label>
                        <div class="postarea">

                            <div>
                                <div style="float: left; margin: 0px 10px 0px 0px;" class="validate_page_icon_attention"></div>
                                <span class="description">
                                <?php printf ( __( '<b>NOTE:</b> You can use the following placeholder to display the EZ HUB Nav Bar wherever you choose: %s', WPC_CLIENT_TEXT_DOMAIN ), '<b>{ez_hub_bar}</b>' ) ?>
                                </span>
                            </div>

                        <?php
                            $settings = array( 'textarea_name' => 'hub_template[content]',  'media_buttons' => false, 'textarea_rows' => 15  );
                            //$hub_template_content = isset( $hub_template['content'] ) ? stripslashes( html_entity_decode( $hub_template['content'] ) ) : '{ez_hub_bar}';

                            $hub_template_content = '{ez_hub_bar}';

                            if ( isset( $_GET['id'] ) ) {
                                $id_hub = $_GET['id'] ;
                                $handle = fopen( $this->get_upload_dir( 'wpclient/_hub_templates/' ) . $id_hub . '_hub_content.txt', 'rb' );
                                if ( $handle !== false ) {
                                    rewind( $handle ) ;
                                    $hub_template_content = '';
                                    while ( !feof( $handle ) ) {
                                        $hub_template_content .= fread( $handle, 8192 );
                                    }
                                }
                                fclose( $handle );
                                $hub_template_content = stripslashes( html_entity_decode( $hub_template_content ) ) ;
                            } elseif ( isset( $_REQUEST['hub_template']['content'] ) ) {
                                $hub_template_content = $_REQUEST['hub_template']['content'];
                            }

                            wp_editor( $hub_template_content, 'hub_template_content', $settings );
                        ?>

                        </div>
                    </div>
                </div><!-- #post-body-content -->
                <div id="postbox-container-1" class="postbox-container">

                    <div id="side-info-column" class="inner-sidebar">
                        <?php
                            do_meta_boxes( 'wp_client_edit_ezhub', 'side', ( isset( $hub_template ) ) ? $hub_template : array() ) ;
                        ?>
                    </div>
                 </div>
                 <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes( 'wp_client_edit_ezhub', 'normal', array( 'hub_settings' => $hub_settings, 'elements' => $elements) ); ?>
                </div>
            </div><!-- #post-body -->
        </div> <!-- #poststuff -->

    </form>
