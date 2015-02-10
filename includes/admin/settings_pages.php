<?php


//include installation class for get default values
include_once $this->plugin_dir . 'includes/class.install.php';
$wpc_install = new WPC_Client_Install();


//install plugin's pages
if ( isset( $_GET['install_pages'] ) && $_GET['install_pages'] ) {

    $wpc_install->create_pages();

    //flush rewrite rules due to slugs
    flush_rewrite_rules( false );

    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=pages&msg=pc' );
    exit;
}


//skip install plugin's pages
if ( isset( $_GET['skip_install_pages'] ) && $_GET['skip_install_pages'] ) {

    $wpc_client_flags = $this->cc_get_settings( 'client_flags' );
    $wpc_client_flags['skip_install_pages'] = true;

    do_action( 'wp_client_settings_update', $wpc_client_flags, 'client_flags' );
    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=pages&msg=ps' );
    exit;
}



$wpc_client_flags = $this->cc_get_settings( 'client_flags' );


//reset skip install pages
if ( isset( $wpc_client_flags['skip_install_pages'] ) && $wpc_client_flags['skip_install_pages'] && isset( $_GET['reset_skip'] ) && $_GET['reset_skip'] ) {
    $wpc_client_flags = $this->cc_get_settings( 'client_flags' );
    $wpc_client_flags['skip_install_pages'] = false;

    do_action( 'wp_client_settings_update', $wpc_client_flags, 'client_flags' );
    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=pages' );
    exit;
}




//save pages
if ( isset( $_REQUEST['update'] ) ) {

    //add shortcodes in page content
    if ( isset( $_REQUEST['wpc_add_shortcodes'] ) && count( $_REQUEST['wpc_add_shortcodes'] ) ) {

        $wpc_pre_pages = $wpc_install->pre_set_pages();
        foreach( $_REQUEST['wpc_add_shortcodes'] as $key => $value ) {
            if ( isset( $_REQUEST['wpc_pages'][$key] ) && $_REQUEST['wpc_pages'][$key] ) {
                foreach( $wpc_pre_pages as $wpc_page ) {
                    if ( $key == $wpc_page['id'] && '' != $wpc_page['content'] ) {
                        $page = get_page( $_REQUEST['wpc_pages'][$key] );
                        //check pages on content and uptating this content
                        if ( $page && false === strpos( $page->post_content, $wpc_page['content'] ) ) {
                            $new_content['ID']              = $page->ID;
                            $new_content['post_content']    = $wpc_page['content'] . $page->post_content;
                            wp_update_post( $new_content );
                        }
                    }
                }
            }
        }
    }

    $wpc_pages = $this->cc_get_settings( 'pages' );
    $wpc_pages = array_merge( $wpc_pages, $_REQUEST['wpc_pages'] );

    do_action( 'wp_client_settings_update', $wpc_pages, 'pages' );

    //flush rewrite rules due to slugs
    flush_rewrite_rules( false );

    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&tab=pages&msg=pu' );
    exit;
}

$wpc_pages = $this->cc_get_settings( 'pages' );

$wpc_pre_pages = $wpc_install->pre_set_pages();

?>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'Theme Link Pages', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <span class="description"><?php printf( __( "%s uses Theme Link Pages to allow integration with a wide range of themes. This connection will allow you to use meta options provided by your theme to customize %s components. These pages should have been created upon installation of the plugin. If not, you will need to create & assign them.", WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'], $this->plugin['title'] ) ?></span>
            <hr />

            <br>
            <input type='submit' name='update' id="update" class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />

            <?php
            if ( isset( $wpc_client_flags['skip_install_pages'] ) && $wpc_client_flags['skip_install_pages'] ) {
            ?>
                <div style="float: right; width: 360px; text-align: right;">
                    <span class="description">
                    <?php printf( __( 'If you have reconsidered, and would now like %s to create the pages for you, click here to reset that option', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ) ?>
                    </span>
                    <br>
                    <a href="<?php echo admin_url() ?>admin.php?page=wpclients_settings&tab=pages&reset_skip=1"><?php _e( 'Reset Skip Install Pages', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                </div>
            <?php
            }
            ?>

            <table class="form-table">
            <?php foreach( $wpc_pre_pages as $page ) { ?>
                <tr valign="top">
                    <th scope="row">
                        <?php echo $page['title'] ?>
                    </th>
                    <td width="300">
                        <?php
                        $page_setting = '';
                        $page_setting = ( isset( $wpc_pages[$page['id']] ) ) ? $wpc_pages[$page['id']] : 0;

                        $args = array(
                           'name'                => 'wpc_pages['. $page['id'] . ']',
                           'id'                  => $page['id'],
                           'sort_column'         => 'menu_order',
                           'sort_order'          => 'ASC',
                           'show_option_none'    => ' ',
                           'echo'                => false,
                           'selected'            => $page_setting
                        );

                        $dropdown_pages = wp_dropdown_pages( $args );

                        if ( $dropdown_pages ) {
                            if ( $page['shortcode'] ) {
                                $for_check_content = "onchange='jQuery( this ).check_page_shortcode(\"" . $page['content'] . "\", \"" . $page['id'] . "\");'";
                            } else {
                                $for_check_content = "onchange='jQuery( this ).check_page_shortcode(\"\", \"" . $page['id'] . "\");'";
                            }

                            echo str_replace( ' id=', " data-placeholder='" . __( 'Select Page', WPC_CLIENT_TEXT_DOMAIN ) . "' style='width: 265px;' " . $for_check_content . " class='chzn-select' id=", wp_dropdown_pages( $args ) );
                        } else {
                            echo '<select data-placeholder="' . __( 'Select Page', WPC_CLIENT_TEXT_DOMAIN ) . '" class="chzn-select" style="width: 265px;"></select>';
                        }

                        ?>
                        <span class="wpc_ajax_loading" style="margin: 0px 0px 8px 5px; display: none;" id="<?php echo $page['id'] . '_loading' ?>"></span>
                        <br>
                        <span class="description" style="vertical-align: super;" ><?php echo isset( $page['desc'] ) ? $page['desc'] : '' ?></span>
                    </td>
                    <td width="30">
                        <?php
                        $warning = 0;
                        $style = '';
                        if ( 0 == $page_setting ) {
                            $style = "style='display: block;' class='validate_page_icon_attention'";
                        } else {
                            $page_content = get_page( $page_setting );

                            if ( !$page_content ) {
                                $style = "style='display: block;' class='validate_page_icon_attention'";
                            } elseif ( '' != $page['content'] && false === strpos( $page_content->post_content, $page['content'] ) && $page['shortcode'] ) {
                                $warning = 1;
                                $style = "style='display: block;' class='validate_page_icon_warning'";
                            } else {
                                $style = "style='display: block;' class='validate_page_icon_ok'";
                            }
                        }
                        ?>
                        <div <?php echo $style ?> id="<?php echo $page['id'] ?>_msg" onmouseout="jQuery( this ).hide_help_text('<?php echo $page['id'] ?>_msg');" onmouseover="jQuery( this ).show_help_text('<?php echo $page['id'] ?>_msg');" >
                            <div class="msg" id="<?php echo $page['id'] ?>_msg_hover" style="vertical-align: super;" ></div>
                        </div>
                    </td>
                    <td>
                        <div id="<?php echo $page['id'] ?>_checkbox_block" class="wpc_pages_checkbox_block" style="<?php echo( 0 == $warning ) ? 'display:none;' : '' ?>" >
                            <label>
                                <input type="checkbox" name="wpc_add_shortcodes[<?php echo $page['id'] ?>]" id="<?php echo $page['id'] ?>_checkbox" value="1" />
                                <?php _e( 'Add shortcode to start of page', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </label>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <input type='submit' name='update' id="update" class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>

<script type="text/javascript">

    jQuery( document ).ready( function() {
        jQuery( '.chzn-select' ).chosen({
            no_results_text: '<?php _e( 'No results matched', WPC_CLIENT_TEXT_DOMAIN ) ?>',
            allow_single_deselect: true,
        });



        //ajax-checked pages is consist shortcode
        jQuery.fn.check_page_shortcode = function ( shortcode, id ) {
            var page_id = jQuery( '#' + id ).val();
            jQuery( '#' + id + '_loading' ).show();
//            jQuery( '#' + id + '_msg' ).slideUp( 10 );
            jQuery( '.check_shortcode' ).hide( 10 );

            jQuery.ajax({
                type        : 'post',
                dataType    : 'json',
                url         : '<?php echo admin_url() ?>admin-ajax.php',
                data        : 'action=wpc_check_page_shortcode&shortcode_type=' + shortcode + '&page_id=' + page_id,
                success     : function( response ) {

                    jQuery( '#' + id + '_loading' ).hide();

                    if ( response ) {
                        jQuery( '#' + id + '_msg' ).focus();
                        if ( response.warning ) {
                            if( response.id == '' ) {
                                jQuery( '#' + id + '_msg' ).attr( 'class', 'validate_page_icon_attention' );
                                jQuery( '#' + id + '_checkbox_block' ).hide();
                                jQuery( '#' + id + '_checkbox' ).attr( 'checked', false );
                            } else {
                                jQuery( '#' + id + '_msg' ).attr( 'class', 'validate_page_icon_warning' );
                                jQuery( '#' + id + '_checkbox_block' ).show();
                                jQuery( '#' + id + '_checkbox' ).attr( 'checked', false );
                                jQuery( '.check_shortcode' ).show( 10 );
                            }
                        } else {
                            jQuery( '#' + id + '_msg' ).attr( 'class', 'validate_page_icon_ok' );
                            jQuery( '#' + id + '_checkbox_block' ).hide();
                            jQuery( '#' + id + '_checkbox' ).attr( 'checked', false );
                        }
                        jQuery( '#' + id + '_msg' ).slideDown( 10 );
                    }
                    else {
                        return false;
                    }
                }
            })
        }



    jQuery.fn.show_help_text = function ( obj_id ) {
        if( jQuery('#' + obj_id).attr('class') == 'validate_page_icon_warning' ) {
            jQuery('#'+ obj_id + '_hover.msg').slideDown(10);
            jQuery('#'+ obj_id + '_hover.msg').html( "<?php _e( 'Warning! This page does not contain the necessary shortcode. To add a shortcode do it manually or check the box when you save!', WPC_CLIENT_TEXT_DOMAIN ) ?>" );
        } else if ( jQuery('#' + obj_id).attr('class') == 'validate_page_icon_attention' ) {
            jQuery('#'+ obj_id + '_hover.msg').slideDown(10);
            jQuery('#'+ obj_id + '_hover.msg').html( "<?php _e( 'Warning! You must select a page in which you have placed the content.', WPC_CLIENT_TEXT_DOMAIN ) ?>" );
        }
    }

    jQuery.fn.hide_help_text = function ( obj_id ) {
        if(jQuery('#' + obj_id).attr('class') == 'validate_page_icon_warning' ) {
            jQuery('#'+ obj_id + '_hover.msg').slideUp(10);
            jQuery('#'+ obj_id + '_hover.msg').html( '' );
        } else if ( jQuery('#' + obj_id).attr('class') == 'validate_page_icon_attention' ) {
            jQuery('#'+ obj_id + '_hover.msg').slideUp(10);
            jQuery('#'+ obj_id + '_hover.msg').html( '' );
        }
    }




    });
</script>