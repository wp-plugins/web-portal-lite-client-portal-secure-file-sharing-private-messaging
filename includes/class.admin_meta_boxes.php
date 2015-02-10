<?php


if ( !class_exists( "WPC_Client_Admin_Meta_Boxes" ) ) {

    class WPC_Client_Admin_Meta_Boxes extends WPC_Client_Admin_Menu {


        /**
        * Meta constructor
        **/
        function meta_construct() {

            add_action( 'save_post', array( &$this, 'save_meta' ) );
            add_action( 'admin_init', array( &$this, 'meta_init' ) );

        }


        /*
        * Add meta box
        */
        function meta_init() {

            //meta box for clientpage
            add_meta_box( 'wpc_client_portalpage', sprintf( __( '%s Settings', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),  array( &$this, 'portalpage_meta' ), 'clientspage', 'side', 'default' );

            if( ( isset( $_GET['post'] ) && !empty( $_GET['post'] ) ) || ( isset( $_POST['post_ID'] ) && !empty( $_POST['post_ID'] ) ) ) {
                $post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];

                $wpc_pages = $this->cc_get_settings( 'pages' );

                if( isset( $wpc_pages['portal_page_id'] ) && $wpc_pages['portal_page_id'] == $post_id ) {
                    add_meta_box( 'wpc_client_wordpress_portalpage', sprintf( __( '%s Settings', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),  array( &$this, 'wordpress_portalpage_meta' ), 'page', 'side', 'default' );
                }
            }
            //meta box for hubpage
            add_meta_box( 'wpc_client_hubpage', __( 'HUB Page Settings', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'hubpage_meta' ), 'hubpage', 'side', 'default' );


            //meta box for ez hub
            //add_meta_box( 'wpc_ezhub_publish', __( 'Publish', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'ezhub_publish' ), 'wp_client_edit_ezhub', 'side', 'default' );
            add_meta_box( 'wpc_ezhub_settings', __( 'General Settings', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'ezhub_settings' ), 'wp_client_edit_ezhub', 'side', 'default' );
            add_meta_box( 'wpc_ezhub_bar_elements', __( 'Bar Elements', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'ezhub_bar_elements' ), 'wp_client_edit_ezhub', 'normal', 'default' );

            add_meta_box( 'wpc_advanced_hub_settings', __( 'General Settings', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'advanced_hub_settings' ), 'wp_client_edit_advanced_hub', 'side', 'default' );
            add_meta_box( 'wpc_advanced_hub_bar_elements', __( 'Advanced HUB Elements', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'advanced_hub_bar_elements' ), 'wp_client_edit_advanced_hub', 'normal', 'default' );

            add_meta_box( 'wpc_simple_hub_settings', __( 'General Settings', WPC_CLIENT_TEXT_DOMAIN ),  array( &$this, 'simple_hub_settings' ), 'wp_client_edit_simple_hub', 'side', 'default' );

        }


        function ezhub_bar_elements( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

            $hub_settings = $data['hub_settings'];
            $elements = $data['elements'];
            ?>
            <table id="bar_elements">
                <tr>
                    <td class="left_td_for_hub_bar">
                        <ul id="wpc_sortable">
                        </ul>
                        <div class="wpc_clear"></div>
                        <div id="add_element_block">
                            <hr />

                            <select name="elemet_type" id="elemet_type">
                            <?php
                                if ( isset( $elements ) && is_array( $elements ) ) {
                                    foreach( $elements as $key => $value ) {
                                        echo ' <option value="' . $key . '">' . $value . '</option>';
                                    }
                                }
                            ?>
                            </select>
                            <br />
                            <input type="button" value="<?php _e( 'Add New Element', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button add_new_button" id="add_element" name="" />
                        </div>
                    </td>
                    <td id="wpc_settings_element"></td>
                </tr>
            </table>
            <div class="wpc_clear"></div>
            <?php
            $i = 0;

            $array_data = array();
            if ( is_array( $hub_settings ) ) {
                foreach( $hub_settings as $setting ) {
                    if ( !is_array( $setting ) )
                        continue;

                    $key = array_keys( $setting );
                    $setting = array_values( $setting );

                    $data = apply_filters( 'wpc_client_ez_hub_' . $key[0], array(), $setting[0], $i );
                    if( !empty( $data ) ) {
                        $array_data[] = $data;
                    } else {
                        continue;
                    }
                    //do_action( 'wpc_client_ez_hub_' . $key[0], $setting[0], $i );
                    $i++;
                }
            }
            ?>
            <script type="text/javascript">
                var tabCounter = 1,
                tabTemplate = '<li class="new_button hub_element"><span class="sorting_button2"></span><span data-href="#{href}" class="title_link width_shorter">#{label}</span><span class="hub_cancel_button" data-id="#{data-id}"></span></li>';

                jQuery( document ).ready( function() {
                    //jQuery( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
                    //jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
                    //var tabs = jQuery( "#tabs" ).tabs();

                    var doing = 0;

                    jQuery( '.title_link' ).live( 'click', function() {
                        if ( 0 == doing ) {
                            doing = 1;

                            var id = jQuery( this ).data( 'href' ).replace( /#element-/g, '' );
                            jQuery( 'li.hub_element' ).removeClass( 'new_button-primary' ).addClass( 'new_button' );
                            jQuery( this ).parent().addClass( 'new_button-primary' ).removeClass( 'new_button' );
                            jQuery( '.block_element' ).css( 'display', 'none' );
                            jQuery( '#wpc_settings_element #element' + id ).fadeIn(1000, function() {
                                jQuery(this).css( 'display', 'block' );
                                doing = 0;
                            });
                        }
                    });

                    jQuery( '.hub_cancel_button' ).live( 'click', function() {
                        var id = jQuery( this ).data( 'id' ) ;
                        jQuery( '#wpc_settings_element #element' + id ).remove();
                        jQuery( this ).parent().hide(1000, function () {
                            jQuery(this).remove();
                        });
                    });


                    function addTab( label, tabContent, text_copy ) {
                        var element = "element-" + tabCounter,
                        li = jQuery( tabTemplate.replace( /#\{label\}/g, label ).replace( /#\{href\}/g, "#" + element ).replace( /#\{data-id\}/g, tabCounter ) );
                        //li = jQuery( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) );
                        jQuery( "#wpc_sortable" ).append( li );
                        jQuery( "#wpc_settings_element" ).append( "<div id='element" + tabCounter + "' class='block_element' style='display: none;'>" + tabContent + "</div>" );
                        tabCounter++;
                    }

                    var array_data = <?php echo json_encode( $array_data ) ?>;
                    var firts_elem = 0;
                    array_data.forEach( function( elem ){
                        addTab( elem.title, elem.content, elem.text_copy );
                        if ( 0 == firts_elem ) {
                            jQuery( 'span[data-href = #element-' + ( tabCounter - 1 ) + ']' ).trigger( 'click' );
                            firts_elem = 1;
                        }
                    });


                        jQuery( '#wpc_sortable' ).sortable({
                            update: function( event, ui ) {
                                var id = jQuery(ui.item[0]).find('span.title_link').data('href').replace( /#element-/g, '' );

                                var position_old = jQuery( '.block_element' ).index(jQuery('#element' + id));
                                var position_new = jQuery( ui.item[0] ).parent().children().index(jQuery(ui.item[0]));

                                if( position_old < position_new ) {
                                    jQuery('.block_element').eq(position_new).after(jQuery('#element' + id));
                                } else {
                                    jQuery('.block_element').eq(position_new).before(jQuery('#element' + id));
                                }

                            }
                        });

                    jQuery( '#add_element' ).live( 'click', function() {
                        var key = jQuery( '#elemet_type' ).val();

                        jQuery( 'body' ).css( 'cursor', 'wait' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            dataType: 'json',
                            data: 'action=wpc_ez_get_shortcode_settings&key=' + key + '&i=' + tabCounter,
                            success: function( elem ) {
                                if( typeof elem.title != 'undefined' && typeof elem.content != 'undefined' && typeof elem.text_copy != 'undefined' ) {
                                    addTab( elem.title, elem.content, elem.text_copy );
                                    jQuery( 'span[data-href = #element-' + ( tabCounter - 1 ) + ']' ).trigger( 'click' );
                                }
                                jQuery( 'body' ).css( 'cursor', 'default' );
                            }
                         });
                    });
                });



            </script>
            <?php



            /*
            ?>
            <table>
                <tr>
                    <td style="vertical-align: top; border-right: 2px solid #000;">
                        <div id="wpc_sortable">
                            <?php
                            $i = 0;

                            if ( is_array( $hub_settings ) ) {
                                foreach( $hub_settings as $setting ) {
                                    if ( !is_array( $setting ) )
                                        continue;

                                    $key = array_keys( $setting );
                                    $setting = array_values( $setting );

                                    do_action( 'wpc_client_ez_hub_' . $key[0], $setting[0], $i );
                                    $i++;
                                }
                            }
                            ?>
                        </div>

                        <div id="add_element_block">
                            <hr />

                            <select name="elemet_type" id="elemet_type">
                            <?php
                                if ( isset( $elements ) && is_array( $elements ) ) {
                                    foreach( $elements as $key => $value ) {
                                        echo ' <option value="' . $key . '">' . $value . '</option>';
                                    }
                                }
                            ?>
                            </select>
                            <br />
                            <input type="button" value="<?php _e( 'Add New Element', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button" id="add_element" name="" />
                        </div>
                    </td>
                    <td>
                        <div id="wpc_settings_elemet">
                        </div>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                var site_url = '<?php echo site_url();?>';

                jQuery( document ).ready( function( $ ) {
                    var i = '<?php echo $i ?>' * 1;

                    jQuery( '#add_element' ).live( 'click', function() {
                        var key = jQuery( '#elemet_type' ).val();

                        jQuery( 'body' ).css( 'cursor', 'wait' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            data: 'action=wpc_ez_get_shortcode_settings&key=' + key + '&i=' + i,
                            success: function( html ) {
                                jQuery( '#wpc_sortable' ).append( html );
                                i++;
                                jQuery( 'body' ).css( 'cursor', 'default' );
                            }
                         });

                    });



                    jQuery( '#wpc_sortable .hndle' ).live( 'click', function() {
                        jQuery( '#wpc_settings_elemet' ).html( '' );
                        jQuery( '#wpc_settings_elemet' ).html( jQuery( this ).nextAll( '.inside' ).html() );
                    });


                    jQuery( '.ez_cancel_button' ).live( 'click', function() {
                        jQuery( this ).parent().parent().hide(1000, function () {
                            $(this).remove();
                        });

                        jQuery( '#wpc_sortable' ).sortable({
                            handle: 'h3',
                            items: 'div.postbox'

                        });
                    });

                });
            </script>
            <?php
            */
        }


        function ezhub_settings( $hub_template ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <!--div class="misc-pub-section">
                <label for="general_menu_type">
                    <?php _e( 'Menu Type', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][menu_type]" id="general_menu_type">
                        <option value="htab" <?php echo ( !isset( $hub_template['general']['menu_type'] ) || 'htab' == $hub_template['general']['menu_type'] ) ? 'selected' : '' ?>><?php _e( 'Horizontal Tabs', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="vtab" <?php echo ( isset( $hub_template['general']['menu_type'] ) && 'vtab' == $hub_template['general']['menu_type'] ) ? 'selected' : '' ?>><?php _e( 'Vertical Tabs', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>

                </label>
            </div-->

            <div class="misc-pub-section">
                <label for="general_theme">
                    <?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][scheme]" id="general_theme">
                        <option value=""> <?php _e( '- None -', WPC_CLIENT_TEXT_DOMAIN ) ?> </option>
                        <?php
                            $wpc_style_schemes = $this->cc_get_settings( 'style_schemes_settings' );

                            if ( count( $wpc_style_schemes ) ) {
                                foreach( $wpc_style_schemes as $key => $settings ) {
                                    $selected = ( isset( $hub_template['general']['scheme'] ) && $key == $hub_template['general']['scheme'] ) ? 'selected' : '';
                                    echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                                }
                            }
                        ?>
                    </select>

                </label>
                <span class="description"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> - <a href="<?php echo get_admin_url() . 'admin.php?page=wpclients_customize' ?>"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </div>


            <div class="misc-pub-section">
                <label for="general_logout_link"><?php _e( 'Enable Logout Link on Bar', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][logout_link]" id="general_logout_link">
                        <option value="yes" <?php echo ( !isset( $hub_template['general']['logout_link'] ) || 'yes' == $hub_template['general']['logout_link'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="no" <?php echo ( isset( $hub_template['general']['logout_link'] ) && 'no' == $hub_template['general']['logout_link'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                </label>
            </div>


            <div class="misc-pub-section">
                <label for="general_enable_icons"><?php _e( 'Enable Icons in Bar', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][enable_icons]" id="general_enable_icons">
                        <option value="yes" <?php echo ( !isset( $hub_template['general']['enable_icons'] ) || 'yes' == $hub_template['general']['enable_icons'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <option value="no" <?php echo ( isset( $hub_template['general']['enable_icons'] ) && 'no' == $hub_template['general']['enable_icons'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                    </select>
                </label>
                <input type="hidden" name="hub_template[is_default]" value="<?php echo ( isset( $hub_template['is_default'] ) && $hub_template['is_default'] ) ? $hub_template['is_default'] : '' ?>">
            </div>
            <?php
            if ( 'add_ez_template' == $_GET['action'] )
                $button_text = __( 'Add Template', WPC_CLIENT_TEXT_DOMAIN );
            else
                $button_text =  __( 'Update Template', WPC_CLIENT_TEXT_DOMAIN );
            ?>
            <div class="for_buttom">
                <?php if ( 'edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="id" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
                <?php } ?>
                <p class="submit">
                    <input type="submit" value="<?php echo $button_text ?>" class="button-primary" id="update_hub_template" name="update_hub_template" />
                </p>
            </div>
            <script type="text/javascript">
                jQuery(document).ready( function() {
                    postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                    var pozition_button = jQuery( '#update_hub_template' ).offset().top;

                    jQuery(document).scroll( function() {
                        var scroll = jQuery(window).scrollTop();
                        if( pozition_button < scroll ) {
                            jQuery( '#update_hub_template' ).addClass( 'hub_button_fixed' );
                        } else {
                            jQuery( '#update_hub_template' ).removeClass( 'hub_button_fixed' );
                        }
                    });

                });
            </script>
            <?php
        }


        function advanced_hub_settings( $hub_template ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <div class="misc-pub-section">
                <label for="general_theme">
                    <?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][scheme]" id="general_theme">
                        <option value=""> <?php _e( '- None -', WPC_CLIENT_TEXT_DOMAIN ) ?> </option>
                        <?php
                            $wpc_style_schemes = $this->cc_get_settings( 'style_schemes_settings' );

                            if ( count( $wpc_style_schemes ) ) {
                                foreach( $wpc_style_schemes as $key => $settings ) {
                                    $selected = ( isset( $hub_template['general']['scheme'] ) && $key == $hub_template['general']['scheme'] ) ? 'selected' : '';
                                    echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                                }
                            }
                        ?>
                    </select>

                </label>
                <input type="hidden" name="hub_template[not_delete]" value="<?php echo ( isset( $hub_template['not_delete'] ) && $hub_template['not_delete'] ) ? $hub_template['not_delete'] : '' ?>">
                <input type="hidden" name="hub_template[is_default]" value="<?php echo ( isset( $hub_template['is_default'] ) && $hub_template['is_default'] ) ? $hub_template['is_default'] : '' ?>">
                <span class="description"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> - <a href="<?php echo get_admin_url() . 'admin.php?page=wpclients_customize' ?>"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </div>
            <?php
            if ( 'add_advanced_template' == $_GET['action'] )
                $button_text = __( 'Add Template', WPC_CLIENT_TEXT_DOMAIN );
            else
                $button_text =  __( 'Update Template', WPC_CLIENT_TEXT_DOMAIN );
            ?>
            <div class="for_buttom">
                <?php if ( 'edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="id" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
                <?php } ?>
                <p class="submit">
                    <input type="submit" value="<?php echo $button_text ?>" class="button-primary" id="update_hub_template" name="update_hub_template" />
                    <?php if( isset( $_REQUEST['id'] ) && $this->get_id_simple_temlate() == $_REQUEST['id'] ) { ?>
                        <a onclick="return confirm('<?php _e( 'Are you sure want to reset the Simple template default ', WPC_CLIENT_TEXT_DOMAIN ) ?>');" href="<?php echo admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $_REQUEST['id'] . '&reset=true'; ?>" class="button"><?php _e( 'Reset to Default', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    <?php } ?>
                </p>
            </div>
            <script type="text/javascript">
                jQuery(document).ready( function() {
                    postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

                    var pozition_button = jQuery( '#update_hub_template' ).offset().top;

                    jQuery(document).scroll( function() {
                        var scroll = jQuery(window).scrollTop();
                        if( pozition_button < scroll ) {
                            jQuery( '#update_hub_template' ).addClass( 'hub_button_fixed' );
                        } else {
                            jQuery( '#update_hub_template' ).removeClass( 'hub_button_fixed' );
                        }
                    });
                });
            </script>
            <?php
        }


        function advanced_hub_bar_elements( $data ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

            $hub_settings = $data['hub_settings'];
            $elements = $data['elements'];
            ?>
            <span class="description"><?php printf ( __( 'The various settings below can be used to modify each %s function. For example, you can adjust the sort and display type for the files and %s listed.', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'], $this->custom_titles['portal']['p'] ) ?></span>
            <table id="bar_elements" style="border-top: 1px solid #ddd;">
                <tr>
                    <td class="left_td_for_hub_bar">
                        <ul id="wpc_sortable">
                        </ul>
                        <div class="wpc_clear"></div>
                        <div id="add_element_block">
                            <hr />

                            <select name="elemet_type" id="elemet_type">
                            <?php
                                if ( isset( $elements ) && is_array( $elements ) ) {
                                    foreach( $elements as $key => $value ) {
                                        echo ' <option value="' . $key . '">' . $value . '</option>';
                                    }
                                }
                            ?>
                            </select>
                            <br />
                            <input type="button" value="<?php _e( 'Add New Element', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button add_new_button" id="add_element" name="" />
                        </div>
                    </td>
                    <td id="wpc_settings_element"></td>
                </tr>
            </table>
            <div class="wpc_clear"></div>
            <?php
            $i = 0;

            $array_data = array();
            if ( is_array( $hub_settings ) ) {
                foreach( $hub_settings as $k=>$setting ) {
                    if ( !is_array( $setting ) )
                        continue;

                    $key = array_keys( $setting );
                    $setting = array_values( $setting );

                    $data = apply_filters( 'wpc_client_ez_hub_' . $key[0], array(), $setting[0], $k, 'advanced' );
                    if( !empty( $data ) ) {
                        $array_data[] = $data;
                    } else {
                        continue;
                    }
                    $i = $k+1;
                }
            }
            ?>
            <script type="text/javascript">
                var tabCounter = 1,
                tabTemplate = '<li class="new_button hub_element"><span data-href="#{href}" class="title_link">#{label}</span><span class="hub_cancel_button" data-id="#{data-id}"></span></li>';

                //<br><span style="margin-left: 20px;">#{text_copy}<a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="#{text_copy}"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a></span><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>


                jQuery( document ).ready( function() {
                    //jQuery( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
                    //jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
                    //var tabs = jQuery( "#tabs" ).tabs();

                    var doing = 0;
                    jQuery( '.title_link' ).live( 'click', function() {
                        if ( 0 == doing ) {
                            doing = 1;
                            var id = jQuery( this ).data( 'href' ).replace( /#element-/g, '' );
                            jQuery( 'li.hub_element' ).removeClass( 'new_button-primary' ).addClass( 'new_button' );
                            jQuery( this ).parent().addClass( 'new_button-primary' ).removeClass( 'new_button' );
                            jQuery( '.block_element' ).css( 'display', 'none' );
                            jQuery( '#wpc_settings_element #element' + id ).fadeIn(1000, function() {
                                jQuery(this).css( 'display', 'block' );
                                doing = 0;
                            });
                        }
                    });

                    jQuery( '.hub_cancel_button' ).live( 'click', function() {
                        var id = jQuery( this ).data( 'id' ) ;
                        jQuery( '#wpc_settings_element #element' + id ).remove();
                        jQuery( this ).parent().hide(1000, function () {
                            jQuery(this).remove();
                        });
                    });

                    function addTab( label, tabContent, text_copy ) {
                        tabCounter = text_copy.match( /\d+/g, '' );
                        var element = "element-" + tabCounter,
                        li = jQuery( tabTemplate.replace( /#\{label\}/g, label ).replace( /#\{text_copy\}/g, text_copy ).replace( /#\{href\}/g, "#" + element ).replace( /#\{data-id\}/g, tabCounter ) );
                        //li = jQuery( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) );
                        jQuery( "#wpc_sortable" ).append( li );
                        jQuery( "#wpc_settings_element" ).append( "<div id='element" + tabCounter + "' class='block_element' style='display: none;'>" + tabContent + "</div>" );
                        tabCounter++;
                    }

                    var array_data = <?php echo json_encode( $array_data ) ?>;
                    var firts_elem = 0;
                    array_data.forEach( function( elem ){
                        addTab( elem.title, elem.content, elem.text_copy );
                        if ( 0 == firts_elem ) {
                            jQuery( 'span[data-href = #element-' + ( tabCounter - 1 ) + ']' ).trigger( 'click' );
                            firts_elem = 1;
                        }
                    });

                    jQuery( '#add_element' ).live( 'click', function() {
                        var key = jQuery( '#elemet_type' ).val();

                        jQuery( 'body' ).css( 'cursor', 'wait' );
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            dataType: 'json',
                            data: 'action=wpc_ez_get_shortcode_settings&key=' + key + '&i=' + tabCounter + '&type=advanced',
                            success: function( elem ) {
                                if( typeof elem.title != 'undefined' && typeof elem.content != 'undefined' && typeof elem.text_copy != 'undefined' ) {
                                    addTab( elem.title, elem.content, elem.text_copy );
                                    jQuery( 'span[data-href = #element-' + ( tabCounter - 1 ) + ']' ).trigger( 'click' );
                                }
                                jQuery( 'body' ).css( 'cursor', 'default' );

                                var client = new ZeroClipboard( jQuery( ".wpc_shortcode_clip_button" ) );

                                client.on( "ready", function( readyEvent ) {

                                    client.on( "aftercopy", function( event ) {
                                        jQuery( event.target ).siblings('.wpc_complete_copy').fadeIn('slow');
                                        var obj = jQuery( event.target ).siblings( '.wpc_complete_copy' );
                                        setTimeout( function() {
                                            obj.fadeOut('slow');
                                        }, 2500 );
                                    });
                                });

                            }
                         });
                    });



                });

            </script>
            <?php

        }


        function simple_hub_settings( $hub_template ) {
            global $current_screen;
            $screen_id = $current_screen->id;
            wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
            ?>
            <div class="misc-pub-section">
                <label for="general_theme">
                    <?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <select name="hub_template[general][scheme]" id="general_theme">
                    <option value=""> <?php _e( '- None -', WPC_CLIENT_TEXT_DOMAIN ) ?> </option>
                    <?php
                        $wpc_style_schemes = $this->cc_get_settings( 'style_schemes_settings' );

                        if ( count( $wpc_style_schemes ) ) {
                            foreach( $wpc_style_schemes as $key => $settings ) {
                                $selected = ( isset( $hub_template['general']['scheme'] ) && $key == $hub_template['general']['scheme'] ) ? 'selected' : '';
                                echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                            }
                        }
                    ?>
                    </select>
                    <input type="hidden" name="hub_template[is_default]" value="<?php echo ( isset( $hub_template['is_default'] ) && $hub_template['is_default'] ) ? $hub_template['is_default'] : '' ?>">
                </label>
                <span class="description"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> - <a href="<?php echo get_admin_url() . 'admin.php?page=wpclients_customize' ?>"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </div>
            <?php
            if ( 'add_simple_template' == $_GET['action'] )
                $button_text = __( 'Add Template', WPC_CLIENT_TEXT_DOMAIN );
            else
                $button_text =  __( 'Update Template', WPC_CLIENT_TEXT_DOMAIN );
            ?>
            <div class="for_buttom">
                <?php if ( 'edit' == $_GET['tab'] ) { ?>
                    <input type="hidden" name="id" value="<?php echo ( isset( $_GET['id'] ) ) ? $_GET['id'] : '' ?>" />
                <?php } ?>
                <p class="submit">
                    <input type="submit" value="<?php echo $button_text ?>" class="button-primary" id="update_hub_template" name="update_hub_template" />
                    <?php if( isset( $_REQUEST['id'] ) && $this->get_id_simple_temlate() == $_REQUEST['id'] ) { ?>
                        <a onclick="return confirm('<?php _e( 'Are you sure want to reset the Simple template default ', WPC_CLIENT_TEXT_DOMAIN ) ?>');" href="<?php echo admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $_REQUEST['id'] . '&reset=true'; ?>" class="button"><?php _e( 'Reset to Default', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
                    <?php } ?>
                </p>
            </div>
            <script type="text/javascript">
                jQuery(document).ready( function() {
                    postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');
                });
            </script>
            <?php
        }


        //show metabox for hubpage
        function hubpage_meta() {
            global $post;

            if ( 'hubpage' == $post->post_type && 0 != count( get_page_templates() ) ) {
                $template = get_post_meta( $post->ID, '_wp_page_template', true );

                ?>
                <label for="hubpage_template"><?php _e( 'Template', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <select name="hubpage_template" id="hubpage_template">
                    <option value='__use_same_as_hub_page' <?php echo ( !isset( $template ) || '__use_same_as_hub_page' == $template ) ? 'selected' : '' ?> ><?php _e( 'Use same as /hub-page', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <option value='default' <?php echo ( isset( $template ) || 'default' == $template ) ? 'selected' : '' ?><?php echo ( !isset( $template ) || '__use_same_as_hub_page' == $template ) ? 'selected' : '' ?> ><?php _e( 'Default Template', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php page_template_dropdown( $template ); ?>
                </select>
                <?php
            }

            ?>

            <p>
                <?php
                $wpc_use_page_settings = get_post_meta( $post->ID, '_wpc_use_page_settings', true );
                ?>
                <input type='checkbox' name='wpc_use_page_settings' id='wpc_use_page_settings' value='1' <?php echo ( 1 == $wpc_use_page_settings ) ? 'checked' : '' ?> />
                <b>
                    <label for='wpc_use_page_settings'><?php _e( 'Ignore Theme Link Page options', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </b>
                <br />
                <span class="description" style="margin: 0px 0px 0px 15px; display: block;"><?php _e( 'This will allow you to use options provided by your framework theme on an individual page level.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </p>

            <?php
        }



        //show metabox for select template for clientpage
        function portalpage_meta() {
            global $post, $wpc_client;
            $current_page = 'add_client_page';

            $order = get_post_meta( $post->ID, '_wpc_order_id', true );

            $user_ids = $this->cc_get_assign_data_by_object( 'portal_page', $post->ID, 'client' );

            $groups_id = $this->cc_get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );

            $wpc_use_page_settings = get_post_meta( $post->ID, '_wpc_use_page_settings', true );

            $allow_edit_clientpage = get_post_meta( $post->ID, 'allow_edit_clientpage', true );

            ?>
            <p>
                <?php if ( 'clientspage' == $post->post_type && 0 != count( get_page_templates() ) ) {
                    $template = get_post_meta( $post->ID, '_wp_page_template', true ); ?>
                    <label for="clientpage_template"><?php _e( 'Template', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                    <select name="clientpage_template" id="clientpage_template">
                        <option value='__use_same_as_portal_page' <?php echo ( !isset( $template ) || '__use_same_as_portal_page' == $template ) ? 'selected' : '' ?> ><?php _e( 'Use same as /portal-page', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                        <option value='default' <?php echo ( isset( $template ) && 'default' == $template ) ? 'selected' : '' ?> ><?php _e( 'Default Template', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                        <?php page_template_dropdown( $template ); ?>
                    </select>
                <?php } ?>
            </p>
            <p>
                <label for=""><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <select name="" id="" style="width:238px !important;">
                    <option value="">(<?php _e( 'None' , WPC_CLIENT_TEXT_DOMAIN ); ?>)</option>
                </select>
            </p>

            <?php $wpc_style_schemes = $this->cc_get_settings( 'style_schemes_settings' );
            $current_style_scheme = get_post_meta( $post->ID, '_wpc_style_scheme', true ); ?>
            <p>
                <label for="clientpage_style_scheme"><?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <select name="clientpage_style_scheme" id="clientpage_style_scheme" style="width:238px !important;">
                    <option value="__use_same_as_portal_page" <?php echo ( !isset( $current_style_scheme ) || '__use_same_as_portal_page' == $current_style_scheme ) ? 'selected' : '' ?> ><?php _e( '- None -', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php if ( count( $wpc_style_schemes ) ) {
                        foreach( $wpc_style_schemes as $key => $settings ) {
                            $selected = ( isset( $current_style_scheme ) && $key == $current_style_scheme ) ? 'selected' : '';
                            echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                        }
                    } ?>
                </select>
                <span class="description" style="margin: 0px 0px 0px 15px; display: block;"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> - <a href="<?php echo admin_url('/') ?>admin.php?page=wpclients_customize"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </p>

            <p>
                <label for="clientpage_order"><?php _e( 'Order', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input type="number" name="clientpage_order" id="clientpage_order" size="4" value="<?php echo ( isset( $order ) ) ? $order : 0 ?>" />
            </p>

            <p>
                <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ),
                        'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_clients',
                        'id'    => 'wpc_clients',
                        'value' => implode( ',', $user_ids )
                    );
                    $additional_array = array(
                        'counter_value' => count( $user_ids )
                    );
                    $this->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>

                <script type="text/javascript">
                    var site_url = '<?php echo site_url();?>';

                    jQuery(document).ready(function(){
                        //
                        jQuery( "#send_update" ).change( function() {
                            if(jQuery(this).attr("checked")){
                                jQuery( "#block_send" ).css( 'display', 'block' );
                            } else {
                                jQuery( "#block_send" ).css( 'display', 'none' );
                            }
                            return true;
                        });

                        jQuery('#wpc_clients').change(function() {
                            var value = jQuery(this).val()
                            if ( '' == value )
                                value = -1
                            jQuery('#send_wpc_clients').data('include', value);
                            jQuery('#send_wpc_clients').val( jQuery(this).val() );
                            var count = jQuery(this).val().split(",");
                            if( '' != count)
                                count = count.length
                            else
                                count = 0
                            jQuery('#send_wpc_clients').next().text( '(' + count + ')' );
                        });

                        jQuery('#wpc_circles').change(function() {
                            var value = jQuery(this).val()
                            if ( '' == value )
                                value = -1
                            jQuery('#send_wpc_circles').data('include', value);
                            jQuery('#send_wpc_circles').val( jQuery(this).val() );
                            var count = jQuery(this).val().split(",");
                            if( '' != count)
                                count = count.length
                            else
                                count = 0
                            jQuery('#send_wpc_circles').next().text( '(' + count + ')' );
                        });
                    });
                </script>
            </p>

            <p>
                <?php
                    $link_array = array(
                        'title'   => sprintf( __( 'assign %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Allowed %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_circles',
                        'id'    => 'wpc_circles',
                        'value' => implode( ',', $groups_id )
                    );
                    $additional_array = array(
                        'counter_value' => count( $groups_id )
                    );
                    $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>
            </p>

            <p>
                <input type='checkbox' name='wpc_use_page_settings' id='wpc_use_page_settings' value='1' <?php echo ( 1 == $wpc_use_page_settings ) ? 'checked' : '' ?> />
                <b>
                    <label for='wpc_use_page_settings'><?php _e( 'Ignore Theme Link Page options', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                </b>
                <br />
                <span class="description" style="margin: 0px 0px 0px 15px; display: block;"><?php _e( 'This will allow you to use options provided by your framework theme on an individual page level.', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
            </p>

            <p>
                <input type='checkbox' name='allow_edit_clientpage' id='allow_edit_clientpage' value='1' <?php echo ( 1 == $allow_edit_clientpage ) ? 'checked' : '' ?> />
                <b>
                    <label for='allow_edit_clientpage'><?php printf( __( 'Allow the %s to edit this page', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?></label>
                </b>
            </p>

            <p>
                <input name="send_update" id="send_update" type="checkbox" value="1" />
                <b>
                    <label for="send_update"><?php printf( __( 'Send Update to selected %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?></label>
                </b>
            </p>

            <p id="block_send" style="display: none;" >
                <?php
                    $link_array = array(
                        'data-input' => 'send_wpc_clients',
                        'id'      => 'send_a_wpc_clients',
                        'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ),
                        'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'send_wpc_clients',
                        'id'    => 'send_wpc_clients',
                        'value' => implode( ',', $user_ids ),
                        'data-include' => implode( ',', $user_ids )
                    );
                    $additional_array = array(
                        'counter_value' => count( $user_ids )
                    );

                    $this->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>

                <br />

                <?php
                    $link_array = array(
                        'data-input' => 'send_wpc_circles',
                        'title'   => sprintf( __( 'Send to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ),
                        'text'    => sprintf( __( 'Send %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'send_wpc_circles',
                        'id'    => 'send_wpc_circles',
                        'value' => implode( ',', $groups_id ),
                        'data-include' => implode( ',', $groups_id )
                    );
                    $additional_array = array(
                        'counter_value' => count( $groups_id )
                    );
                    $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                ?>

            </p>

        <?php }

        //show this metabox in wordpress page which was selected as Portal Page
        function wordpress_portalpage_meta() {
            global $post;

            $wpc_style_schemes = $this->cc_get_settings( 'style_schemes_settings' );
            $current_style_scheme = get_post_meta( $post->ID, '_wpc_style_scheme', true ); ?>
            <p>
                <label for="clientpage_style_scheme"><?php _e( 'Style Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <select name="clientpage_style_scheme" id="clientpage_style_scheme" style="width:238px !important;">
                    <option value="" <?php echo ( !isset( $current_style_scheme ) || '' == $current_style_scheme ) ? 'selected' : '' ?> ><?php _e( '- None -', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                    <?php if ( count( $wpc_style_schemes ) ) {
                        foreach( $wpc_style_schemes as $key => $settings ) {
                            $selected = ( isset( $current_style_scheme ) && $key == $current_style_scheme ) ? 'selected' : '';
                            echo '<option value="' . $key . '" ' . $selected . ' >' . $settings['title'] . '</option>';
                        }
                    } ?>
                </select>
                <span class="description" style="margin: 0px 0px 0px 15px; display: block;"><?php _e( 'you can update or create your scheme', WPC_CLIENT_TEXT_DOMAIN ) ?> - <a href="<?php echo admin_url('/') ?>admin.php?page=wpclients_customize"><?php _e( 'here', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
            </p>
        <?php }


        function save_meta( $post_id ) {
            global $wpdb;
            //for quick edit

            if(defined('DOING_AJAX') && DOING_AJAX) {
                return $post_id;
            }

            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            if( defined('WPC_CLIENT_NOT_SAVE_META') && WPC_CLIENT_NOT_SAVE_META ) {
                return $post_id;
            }

            //query only from meta box then edit
            if( !isset( $_POST['create_clientpage'] ) ) {

                if ( isset( $_POST ) && 0 < count( $_POST ) ) {
                    $post = get_post( $post_id );

                    if ( 'clientspage' == $post->post_type ) {

                        $selected_clients = array();

                        //updating from admin
                        if( isset( $_POST['wpc_clients'] ) ) {
                            if( 'all' == $_POST['wpc_clients'] ) {
                                $selected_clients = $this->acc_get_client_ids();
                            } else if ( '' != $_POST['wpc_clients']) {
                                $selected_clients = explode( ',', $_POST['wpc_clients'] );
                            }
                            $this->cc_set_assigned_data( 'portal_page', $post_id, 'client', $selected_clients );
                        }

                        //update clientpage file template
                        if ( isset( $_POST['clientpage_template'] ) && '' != $_POST['clientpage_template'] ) {
                            update_post_meta( $post_id, '_wp_page_template', $_POST['clientpage_template'] );
                        } else {
                            delete_post_meta( $post_id, '_wp_page_template' );
                        }

                        //update clientpage style scheme
                        if ( isset( $_POST['clientpage_style_scheme'] ) && '' != $_POST['clientpage_style_scheme'] ) {
                            update_post_meta( $post_id, '_wpc_style_scheme', $_POST['clientpage_style_scheme'] );
                        } else {
                            delete_post_meta( $post_id, '_wpc_style_scheme' );
                        }


                        //update clientpage file order
                        if ( isset( $_POST['clientpage_order'] ) && '' != (int) $_POST['clientpage_order'] && 0 <= (int) $_POST['clientpage_order'] ) {
                            update_post_meta( $post_id, '_wpc_order_id', $_POST['clientpage_order'] );
                        } else {
                            update_post_meta( $post_id, '_wpc_order_id', 0 );
                        }


                        //save client Client Circles for Portal Page
                        if ( isset( $_POST['wpc_circles'] ) && '' != $_POST['wpc_circles'] ) {
                            $selected_circles = explode( ',', $_POST['wpc_circles'] );

                            if ( is_array( $selected_circles ) && count( $selected_circles ) ) {
                                $this->cc_set_assigned_data( 'portal_page', $post_id, 'circle', $selected_circles );
                            }
                        }


                        //save option Allow Edit Portal Page
                        if ( isset( $_POST['allow_edit_clientpage'] ) && '1' == $_POST['allow_edit_clientpage'] )
                            update_post_meta( $post_id, 'allow_edit_clientpage', 1 );
                        else
                            update_post_meta( $post_id, 'allow_edit_clientpage', 0 );


                        //save option Ignore Theme Link Page options
                        if ( isset( $_POST['wpc_use_page_settings'] ) && '1' == $_POST['wpc_use_page_settings'] )
                            update_post_meta( $post_id, '_wpc_use_page_settings', 1 );
                        else
                            update_post_meta( $post_id, '_wpc_use_page_settings', 0 );


                        // send updates to client
                        if ( isset( $_POST['send_update'] ) && '1' == $_POST['send_update'] ) {

                            $user_ids = ( isset( $_POST['send_wpc_clients'] ) ) ? explode( ',', $_POST['send_wpc_clients'] ) : array();

                            $groups_id = ( isset( $_POST['send_wpc_circles'] ) ) ? explode( ',', $_POST['send_wpc_circles'] ) : array();

                            //get clients from Client Circles
                            if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                                foreach( $groups_id as $group_id ) {
                                    $user_ids = array_merge( $user_ids, $this->cc_get_group_clients_id( $group_id ) );
                                }

                            $user_ids = array_unique( $user_ids );
                        } else {
                            $user_ids = array();
                        }

                        //add clients staff to list
                        if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
                            $not_approved_staff = array();
                            $staff_ids = array();
                            foreach( $user_ids as $user_id ) {

                                $args = array(
                                    'role'          => 'wpc_client_staff',
                                    'orderby'       => 'ID',
                                    'order'         => 'ASC',
                                    'meta_key'      => 'parent_client_id',
                                    'meta_value'    => $user_id,
                                    'exclude'       => $not_approved_staff,
                                    'fields'        => 'ID',
                                );

                                $user_ids = array_merge( $user_ids, get_users( $args ) );

                            }
                        }

                        $user_ids = array_unique( $user_ids );

                        //send update email to selected clients
                        foreach ( $user_ids as $user_id ) {

                            $userdata   = (array) get_userdata( $user_id );
                            if ( !$userdata )
                                continue;
                            $link       = get_permalink( $post_id );

                            $args = array(
                                'client_id' => $user_id,
                                'page_id' => $link,
                                'page_title' => get_the_title( $post_id )
                            );

                            //send email
                            $this->cc_mail( 'client_page_updated', $userdata['data']->user_email, $args, 'portal_page_updated' );
                        }

                    } elseif ( 'hubpage' == $post->post_type ) {

                        //update hubpage file template
                        if ( isset( $_POST['hubpage_template'] ) && '' != $_POST['hubpage_template'] ) {
                            update_post_meta( $post_id, '_wp_page_template', $_POST['hubpage_template'] );
                        } else {
                            delete_post_meta( $post_id, '_wp_page_template' );
                        }

                        //save option Ignore Theme Link Page options
                        if ( isset( $_POST['wpc_use_page_settings'] ) && '1' == $_POST['wpc_use_page_settings'] )
                            update_post_meta( $post_id, '_wpc_use_page_settings', 1 );
                        else
                            update_post_meta( $post_id, '_wpc_use_page_settings', 0 );


                    } elseif( 'page' == $post->post_type ) {

                        if( ( isset( $_GET['post'] ) && !empty( $_GET['post'] ) ) || ( isset( $_POST['post_ID'] ) && !empty( $_POST['post_ID'] ) ) ) {
                            $post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];

                            $wpc_pages = $this->cc_get_settings( 'pages' );

                            if( isset( $wpc_pages['portal_page_id'] ) && $wpc_pages['portal_page_id'] == $post_id ) {
                                //update clientpage style scheme
                                if ( isset( $_POST['clientpage_style_scheme'] ) && '' != $_POST['clientpage_style_scheme'] ) {
                                    update_post_meta( $post_id, '_wpc_style_scheme', $_POST['clientpage_style_scheme'] );
                                } else {
                                    delete_post_meta( $post_id, '_wpc_style_scheme' );
                                }
                            }
                        }

                    }

                }

            }
        }

    //end class
    }

}

?>
