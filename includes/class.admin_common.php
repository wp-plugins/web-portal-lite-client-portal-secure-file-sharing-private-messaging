<?php
//{{FUNC_NOT_ENC:widget_extra_control}}

if ( !class_exists( "WPC_Client_Admin_Common" ) ) {

    class WPC_Client_Admin_Common extends WPC_Client_Common {

        var $capabilities_maps;

        //set plugin roles
        var $wpc_roles = array(
            'wpc_client',
        );

        var $wpc_popup_flags = array(
            'client' => false,
            'circle' => false,
        );


        /**
        * constructor
        **/
        function admin_common_construct() {

            $this->capabilities_maps = array(
                'wpc_client' => array(
                    'wpc_delete_assigned_files' => array( 'cap' => false, 'label' => 'Delete Assigned Files' ),
                    'wpc_delete_uploaded_files' => array( 'cap' => true, 'label' => 'Delete Uploaded Files' ),
                    'wpc_reset_password'        => array( 'cap' => false, 'label' => 'Reset Password' ),
                    'wpc_view_profile'          => array( 'cap' => false, 'label' => 'View Profile' ),
                    'wpc_modify_profile'        => array( 'cap' => false, 'label' => 'Modify Profile' ),
                    'wpc_add_media'             => array( 'cap' => false, 'label' => 'Add Media' )
                    //'wpc_file_note'             => array( 'cap' => false, 'label' => 'Add note to upload file' ),
                ),
            );

            //Ajax action - should be here!!!!
            add_action( 'wp_ajax_get_popup_pagination_data', array( &$this, 'ajax_get_popup_pagination_data' ) );

            //update settings
            add_action( 'wp_client_settings_update', array( &$this, '_settings_update_func' ), 99, 2 );


            //add ez hub settings

            add_filter( 'wpc_client_ez_hub_pages_access', array( &$this, 'add_ez_hub_settings_pages_access' ), 12, 4 );
            add_filter( 'wpc_client_ez_hub_files_uploaded', array( &$this, 'add_ez_hub_settings_files_uploaded' ), 12, 4 );
            add_filter( 'wpc_client_ez_hub_files_access', array( &$this, 'add_ez_hub_settings_files_access' ), 12, 4 );
            add_filter( 'wpc_client_ez_hub_upload_files', array( &$this, 'add_ez_hub_settings_upload_files' ), 12, 4 );
            add_filter( 'wpc_client_ez_hub_private_messages', array( &$this, 'add_ez_hub_settings_private_messages' ), 12, 4 );
            add_filter( 'wpc_client_ez_hub_logout_link', array( &$this, 'add_ez_hub_settings_logout_link' ), 12, 4 );


            add_filter( 'wpc_client_get_ez_shortcode_pages_access', array( &$this, 'get_ez_shortcode_pages_access' ), 10, 2 );
            add_filter( 'wpc_client_get_ez_shortcode_files_uploaded', array( &$this, 'get_ez_shortcode_files_uploaded' ), 10, 2 );
            add_filter( 'wpc_client_get_ez_shortcode_files_access', array( &$this, 'get_ez_shortcode_files_access' ), 10, 2 );
            add_filter( 'wpc_client_get_ez_shortcode_upload_files', array( &$this, 'get_ez_shortcode_upload_files' ), 10, 2 );
            add_filter( 'wpc_client_get_ez_shortcode_private_messages', array( &$this, 'get_ez_shortcode_private_messages' ), 10, 2 );
            add_filter( 'wpc_client_get_ez_shortcode_logout_link', array( &$this, 'get_ez_shortcode_logout_link' ), 10, 2 );

            add_action( 'sidebar_admin_setup', array( &$this, 'widget_expand_control' ), 100 );               // before any HTML output save widget changes and add controls to each widget on the widget admin page
        }

        // CALLED VIA 'sidebar_admin_setup' ACTION
        // adds in the admin control per widget
        function widget_expand_control() {
            global $wp_registered_widgets, $wp_registered_widget_controls;
            foreach ( $wp_registered_widgets as $id => $widget ) {
                if ( !isset( $wp_registered_widget_controls[ $id ] ) ) {
                    wp_register_widget_control( $id, $widget['name'], array( &$this, 'widget_empty_control' ) );
                }
                $wp_registered_widget_controls[ $id ]['callback_wpc_redirect'] = $wp_registered_widget_controls[ $id ]['callback'];
                $wp_registered_widget_controls[ $id ]['callback'] = array( &$this, 'widget_extra_control' );
                array_push( $wp_registered_widget_controls[ $id ]['params'], $id );
            }

        }


        // added to widget functionality in 'widget_logic_expand_control' (above)
        function widget_empty_control() {
            return;
        }


        function widget_show_setting( $params ) {
            global $wp_registered_widget_controls;
            if( !is_array( $params ) ) {
                return;
            }
            $id = array_pop( $params );
            $callback = $wp_registered_widget_controls[ $id ]['callback_wpc_redirect'];
            if ( is_callable( $callback ) )
                call_user_func_array( $callback, $params );

            $options = get_option('wpc_widget_show_settings', array());

            // dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
            $id_disp = $id;
            if ( !empty( $params ) && isset( $params[0]['number'] ) ) {
                $number = $params[0]['number'];
                if ( $number == -1 ) {
                    $number = "__i__";
                    $value = "";
                }
                $id_disp = $wp_registered_widget_controls[ $id ]['id_base'] . '-' . $number;
            }
            $value = ( isset( $options[ $id_disp ] ) && !empty( $options[ $id_disp ] ) ) ? $options[ $id_disp ] : 'default';
            ?>
            <p>
                <label>
                    <b><?php printf( __( '%s Display Options', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ); ?></b>:<br />
                    <select name="wpc_show_page[<?php echo $id_disp; ?>]" style="width: 100%;">
                        <option value="default" <?php selected( $value, 'default' ); ?>><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                        <option value="hub" <?php selected( $value, 'hub' ); ?>><?php _e( 'Show on HUB Page', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                        <option value="portal" <?php selected( $value, 'portal' ); ?>><?php printf( __( 'Show on %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ); ?></option>
                        <option value="hub_portal" <?php selected( $value, 'hub_portal' ); ?>><?php printf( __( 'Show on both HUB and %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ); ?></option>
                        <option value="not_hub" <?php selected( $value, 'not_hub' ); ?>><?php _e( "Don't show on HUB Page", WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                        <option value="not_portal" <?php selected( $value, 'not_portal' ); ?>><?php printf( __( "Don't show on %s", WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ); ?></option>
                        <option value="not_hub_portal" <?php selected( $value, 'not_hub_portal' ); ?>><?php printf( __( "Don't show on either HUB or %s", WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ); ?></option>
                    </select>
                </label>
            </p>
            <?php
        }


        function widget_extra_control() {
            $params = func_get_args();
            $this->widget_show_setting( $params );
        }


        /**
        * Get plugin capabilities maps
        *
        */
        function acc_get_capabilities_maps() {
            $this->capabilities_maps = apply_filters( 'wp_client_capabilities_maps', $this->capabilities_maps );
            return $this->capabilities_maps;
        }


        function acc_get_client_ids() {
            //all clients
            $excluded_clients = $this->cc_get_excluded_clients();
            $args = array(
                'role'      => 'wpc_client',
                'exclude'   => $excluded_clients,
                'fields'    => array( 'ID' ),
                'orderby'   => 'user_login',
                'order'     => 'ASC',
            );

            $clients = get_users( $args );
            $clients_array = array();
            foreach( $clients as $client ) {
                $clients_array[] = $client->ID;
            }

            return $clients_array;
        }


         /**
         * Display assign client popup
         **/
        function acc_get_assign_clients_popup( $current_page = '', $echo = true, $params = array() ) {
            add_thickbox();
            wp_register_script( 'wpc-new-assign-popup-js', $this->plugin_url . 'js/new-assign-popup.js' );
            wp_enqueue_script( 'wpc-new-assign-popup-js', false, array(), false, true );

            if( $current_page == 'wpclients_staff_edit' ) {
                $input_type = 'radio';
            } else {
                $input_type = 'checkbox';
            }

            switch( $current_page ) {
                case 'wpclients_managers':
                    $title = sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['manager']['s'] );
                    break;
                case 'wpclients_groups':
                    $title = sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['s'] );
                    break;
                case 'add_client_page':
                    $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['portal']['s'] );
                    break;
                case 'wpclients_galleries':
                    $title = sprintf( __( 'Assign %s To Gallery:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] );
                    break;
                case 'wpclients_gallery_categories':
                    $title = sprintf( __( 'Assign %s To Gallery Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] );
                    break;
                case 'wpclients_files':
                    $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] );
                    break;
                case 'wpclients_filescat':
                    $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] );
                    break;
                case 'wpclientspage_categories':
                    $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['portal']['s'] );
                    break;
                default:
                    $title = sprintf( __( 'Assign %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] );
                    break;
            }

            $localize_array = array(
                'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    wpc_popup_title.client_popup_block = wpc_popup_var.data",
                'data'         => $title,
                'current_page' => $current_page,
                'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
                'site_url'     => get_site_url()
            );
            if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
                $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
            }
            wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );

            ob_start();
            ?>
            <div style="display: none;">
                <div id="client_popup_block" class="wpc_assign_popup" style="clear: both;">
                    <div class="postbox" style="margin-bottom: 0px;">
                        <h3 style="cursor: auto; padding: 8px 0 8px 8px;">
                            <?php if( $input_type == 'checkbox' ) { ?>
                                <span class="description" style="padding-left: 8px;">
                                    <label>
                                        <input type="checkbox" class="wpc_select_all_at_page" name="wpc_select_all_at_page_clients" id="wpc_select_all_at_page_clients" value="1" />
                                        <?php _e( 'Select all at this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </span>
                                <span class="description" style="padding-left: 8px;">
                                    <label>
                                        <input type="checkbox" class="wpc_select_all" name="wpc_select_all_clients" id="wpc_select_all_clients" value="1" />
                                        <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                    </label>
                                </span>
                            <?php } ?>
                            &nbsp;
                        </h3>
                        <select name="wpc_show" class="wpc_show">
                            <option value="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="display_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="wpc_cl_business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <select name="wpc_order_clients" class="wpc_order">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <input type="text" class="wpc_search_field" name="wpc_search_clients" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" />

                        <div class="wpc_inside">
                            <table>
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="wpc_assign_popup_after_list" style="clear: both; position: relative;"></div>
                        <div style="clear: both; text-align: center; position: relative;">
                                <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                                <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>

                            <?php if( $input_type == 'checkbox' ) { ?>
                                <div class="wpc_popup_statistic">
                                    <span class="wpc_total_count">0</span> item(s). <span class="wpc_selected_count">0</span> item(s) was selected.
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear: both; height: 15px;">
                            &nbsp;
                        </div>
                        <div style="clear: both; text-align: center;">
                            <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </div>
                </div>

            </div>

        <?php
            $out = ob_get_contents();

            ob_end_clean();
            if( $echo ) {
                echo $out;
            } else {
                return $out;
            }
        }


         /**
         * Display assign circles popup
         **/
        function acc_get_assign_circles_popup( $current_page = '', $echo = true, $params = array() ) {
            add_thickbox();
            wp_register_script( 'wpc-new-assign-popup-js', $this->plugin_url . 'js/new-assign-popup.js' );
            wp_enqueue_script( 'wpc-new-assign-popup-js', false, array(), false, true );

            switch( $current_page ) {
                case 'add_client':
                    $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'], $this->custom_titles['client']['s'] );
                    break;
                case 'add_client_page':
                    $title = sprintf( __( 'Assign %s To $s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'], $this->custom_titles['portal']['s'] );
                    break;
                case 'wpclients_galleries':
                    $title = sprintf( __( 'Assign %s To Gallery:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclients_gallery_categories':
                    $title = sprintf( __( 'Assign %s To Gallery Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclients_files':
                    $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclients_filescat':
                    $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclientspage_categories':
                    $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'], $this->custom_titles['portal']['s'] );
                    break;
                default:
                    $title = sprintf( __( 'Assign %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] );
                    break;
            }

            $localize_array = array(
                'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    wpc_popup_title.circle_popup_block = wpc_popup_var.data",
                'data'         => $title,
                'current_page' => $current_page,
                'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
                'site_url'     => get_site_url()
            );
            if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
                $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
            }
            wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );

            ob_start();
        ?>
            <div style="display: none;">
                <div id="circle_popup_block" class="wpc_assign_popup" style="clear: both;">
                    <div class="postbox" style="margin-bottom: 0px;">
                        <h3 style="cursor: auto; padding: 8px 0 8px 8px;">
                            <span class="description" style="padding-left: 8px;">
                                <label>
                                    <input type="checkbox" class="wpc_select_all_at_page" name="wpc_select_all_at_page_circles" id="wpc_select_all_at_page_circles" value="1" />
                                    <?php _e( 'Select all at this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                            <span class="description" style="padding-left: 8px;">
                                <label>
                                    <input type="checkbox" class="wpc_select_all" name="wpc_select_all_circles" id="select_all_circles" value="1" />
                                    <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                        </h3>
                        <input type="text" class="wpc_search_field" name="wpc_search_circles" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" />
                        <select name="wpc_order_circles" class="wpc_order">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <div class="wpc_inside">
                            <table>
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="wpc_assign_popup_after_list" style="clear: both; position: relative;"></div>
                        <div style="clear: both; text-align: center; position: relative;">
                            <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                            <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                            <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                            <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                            <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>
                            <div class="wpc_popup_statistic">
                                <span class="wpc_total_count">0</span> item(s). <span class="wpc_selected_count">0</span> item(s) was selected.
                            </div>
                        </div>
                        <div style="clear: both; height: 15px;">
                            &nbsp;
                        </div>
                        <div style="clear: both; text-align: center;">
                            <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </div>
                </div>

            </div>
        <?php
            $out = ob_get_contents();

            ob_end_clean();
            if( $echo ) {
                echo $out;
            } else {
                return $out;
            }
        }


        /**
         * Display assign circles popup
         **/
        function acc_get_assign_managers_popup( $current_page = '', $echo = true, $params = array() ) {
            add_thickbox();
            wp_register_script( 'wpc-new-assign-popup-js', $this->plugin_url . 'js/new-assign-popup.js' );
            wp_enqueue_script( 'wpc-new-assign-popup-js', false, array(), false, true );

            switch( $current_page ) {
                case 'add_client':
                    $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'], $this->custom_titles['client']['s'] );
                    break;
                case 'add_client_page':
                    $title = sprintf( __( 'Assign %s To %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'], $this->custom_titles['portal']['s'] );
                    break;
                case 'wpclients_files':
                    $title = sprintf( __( 'Assign %s To File:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclients_filescat':
                    $title = sprintf( __( 'Assign %s To File Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] );
                    break;
                case 'wpclientspage_categories':
                    $title = sprintf( __( 'Assign %s To %s Category:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'], $this->custom_titles['portal']['s'] );
                    break;
                default:
                    $title = sprintf( __( 'Assign %s:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['manager']['p'] );
                    break;
            }
            $localize_array = array(
                'l10n_print_after' => "
                    if( typeof wpc_popup_title == 'undefined' ) {
                        var wpc_popup_title = new Array();
                    }
                    wpc_popup_title.manager_popup_block = wpc_popup_var.data",
                'data'         => $title,
                'current_page' => $current_page,
                'search_text'  => __( 'Search', WPC_CLIENT_TEXT_DOMAIN ),
                'site_url'     => get_site_url()
            );
            if( isset( $params['wpc_ajax_prefix'] ) && !empty( $params['wpc_ajax_prefix'] ) ) {
                $localize_array['wpc_ajax_prefix'] = $params['wpc_ajax_prefix'];
            }
            wp_localize_script( "wpc-new-assign-popup-js", 'wpc_popup_var', $localize_array );
            ob_start();
        ?>
            <div style="display: none;">
                <div id="manager_popup_block" class="wpc_assign_popup" style="clear: both;">
                    <div class="postbox" style="margin-bottom: 0px;">
                        <h3 style="cursor: auto; padding: 8px 0 8px 8px;">
                            <span class="description" style="padding-left: 8px;">
                                <label>
                                    <input type="checkbox" class="wpc_select_all_at_page" name="wpc_select_all_at_page_managers" id="wpc_select_all_at_page_managers" value="1" />
                                    <?php _e( 'Select all at this page.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                            <span class="description" style="padding-left: 8px;">
                                <label>
                                    <input type="checkbox" class="wpc_select_all" name="wpc_select_all_managers" id="wpc_select_all_managers" value="1" />
                                    <?php _e( 'Select All.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                </label>
                            </span>
                        </h3>
                        <input type="text" class="wpc_search_field" name="wpc_search_managers" value="<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>" onfocus="if (this.value=='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>') this.value='';" onblur="if (this.value==''){this.value='<?php _e( 'Search', WPC_CLIENT_TEXT_DOMAIN ) ?>'}" />
                        <select name="wpc_order_managers" class="wpc_order">
                            <option value="show_asc"><?php _e( 'A to Z', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="show_desc"><?php _e( 'Z to A', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_asc"><?php _e( 'Date Added (Recent to Earlier)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="date_desc"><?php _e( 'Date Added (Earlier to Recent)', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="first_asc"><?php _e( 'Assigned show first', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                        <select name="wpc_show_managers" class="wpc_show">
                            <option value="user_login"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="user_nicename"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>

                        <div class="wpc_inside">
                            <table>
                                <tr>
                                    <td>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="wpc_assign_popup_after_list" style="clear: both; position: relative;"></div>
                        <div style="clear: both; text-align: center; position: relative;">
                                <a href="javascript:void(0);" rel="first" class="wpc_pagination_links"><<</a>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="prev" class="wpc_pagination_links"><</a>&nbsp;&nbsp;
                                <span class="wpc_page_num">1</span>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="next" class="wpc_pagination_links">></a>&nbsp;&nbsp;
                                <a href="javascript:void(0);" rel="last" class="wpc_pagination_links">>></a>
                                <div class="wpc_popup_statistic">
                                    <span class="wpc_total_count">0</span> item(s). <span class="wpc_selected_count">0</span> item(s) was selected.
                                </div>
                        </div>
                        <div style="clear: both; height: 15px;">
                            &nbsp;
                        </div>
                        <div style="clear: both; text-align: center;">
                            <input type="button" name="wpc_ok" value="<?php _e( 'Ok', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="wpc_ok_popup button-primary" />
                            <input type="button" name="wpc_cancel" class="wpc_cancel_popup button" value="<?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </div>
                </div>

            </div>

        <?php
            $out = ob_get_contents();

            ob_end_clean();
            if( $echo ) {
                echo $out;
            } else {
                return $out;
            }
        }


         /**
         * decode64 multidimensional array
         **/
         function acc_array_base64_decode($array = array()) {
             if(is_array($array)) {
                 foreach($array as $k=>$val) {
                    if(is_array($val)) {
                        $array[$k] = $this->acc_array_base64_decode($val);
                    } else if(is_string($val)) {
                        $array[$k] = base64_decode(str_replace( "-", "+",$val));
                    }
                 }
             }
            return $array;
         }


        /**
        * Function to
        *
        * @param array $export_settings array checked to export wp-client plugin settings.
        *
        * @return array/string array ready-to-convertation of *.xml.
        */
        function acc_do_settings_array( $export_settings ) {
            global $wpdb;
            $export_settings_array = array();
            $export_settings = array_keys( $export_settings );

            if( is_array( $export_settings ) && 0 < count( $export_settings ) ) {

                foreach( $export_settings as $value ) {
                    if( 'custom_redirects' == $value ) {
                        $rul_rules = $wpdb->get_results(
                            'SELECT rul_type, rul_value, rul_url, rul_url_logout, rul_order
                            FROM ' . $wpdb->prefix . 'wpc_client_login_redirects
                            ORDER BY rul_type, rul_order, rul_value',
                        ARRAY_A );

                        $redirect_rules = array();
                        foreach( $rul_rules as $rul_value ) {
                            $index =  $rul_value['rul_type'] . '_' . $rul_value['rul_value'];
                            $redirect_rules[$index] = $rul_value;
                        }

                        $export_settings_array['custom_redirects'] = $redirect_rules;
                    } else {
                        $export_settings_array[$value] = ( get_option( 'wpc_' . $value ) ) ? get_option( 'wpc_' . $value ) : '';
                    }
                }

                return $export_settings_array;
            }
            return '';
        }


        /**
        * Function to delete wpc-client
        *
        * @param array $settings_array array of wp-client plugin settings.
        * @param object $xml_info object of new *.xml file.
        */
        function acc_array_to_xml( $settings_array, &$xml_info ) {
            foreach( $settings_array as $key=>$value ) {
                if( is_array( $value ) ) {
                    if( range( 0, count( $value ) - 1 ) == array_keys( $value ) ) {
                        $arrays = 0;
                        foreach( $value as $not_range_value ) {
                            if( is_array( $not_range_value ) ) {
                                $arrays++;
                            }
                        }
                        if( 0 == $arrays ) {
                            $not_range_string = implode( '#|#', $value );
                            $xml_info->addChild( "$key", "$not_range_string" );
                        } else {
                            $subnode = $xml_info->addChild( "$key" );
                            $this->acc_array_to_xml( $value, $subnode );
                        }
                    } else {
                        if( !is_numeric( $key ) ) {
                            $subnode = $xml_info->addChild( "$key" );
                            $this->acc_array_to_xml( $value, $subnode );
                        } else {
                            $this->acc_array_to_xml( $value, $xml_info );
                        }
                    }
                } else {
                    $xml_info->addChild( "$key", "$value" );
                }
            }
        }


        function acc_assign_popup( $object, $current_page = '', $link_params = array(), $input_params = false, $additional_params = array(), $echo = true ) {
            global $wpdb;

            if( empty( $current_page ) ) {
                $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
            }

            switch( $object ) {
                 case 'client':
                    $default_link_params = array(
                        'href'       => '#client_popup_block',
                        'data-input' => 'wpc_clients',
                        'class'      => 'wpc_fancybox_link',
                        'data-type'  => 'wpc_clients',
                        'data-ajax'  => 0
                    );
                    $link_params = array_merge( $default_link_params, $link_params );
                    $params = '';
                    foreach( $link_params as $key=>$val ) {
                        if( $key != 'text' ) {
                            $params .= "$key=\"$val\"";
                        }
                    }
                    $html = '<span class="edit"><a ' . $params . '>' . ( isset( $link_params['text'] ) ? $link_params['text'] : __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ) ) . '</a></span>';

                    if( is_array( $input_params ) ) {
                        $default_input_params = array(
                            'type'      => 'hidden',
                            'name'      => 'wpc_clients',
                            'id'        => 'wpc_clients',
                            'class'     => 'clients_field'
                        );

                        $input_params = array_merge( $default_input_params, $input_params );

                        if( isset( $input_params['value'] ) && !empty( $input_params['value'] ) ) {

                            $new_array = $wpdb->get_col(
                                "SELECT u.ID
                                FROM {$wpdb->users} u, {$wpdb->usermeta} um
                                WHERE u.ID IN(" . $input_params['value'] . ") AND
                                    um.user_id = u.ID AND
                                    um.meta_key = '{$wpdb->prefix}capabilities' AND
                                    um.meta_value LIKE '%s:10:\"wpc_client\"%'"
                            );


                            if( !$new_array ) {
                                $input_params['value'] = implode( ',', $new_array );
                            }

                        }

                        $params = '';
                        foreach( $input_params as $key=>$val ) {
                            $params .= "$key=\"$val\"";
                        }
                        $html .= '<input ' . $params . ' />';
                    }
                    $default_additional_params = array(
                        'counter_value' => 0,
                        'only_link'     => 0
                    );
                    $additional_params = array_merge( $default_additional_params, $additional_params );
                    $html .= '&nbsp;<span class="edit counter_' . ( isset( $input_params['id'] ) ? $input_params['id'] : ( isset( $additional_params['input_ref'] ) ? $additional_params['input_ref'] : '' ) ) . '">(' . ( isset( $additional_params['counter_value'] ) ? $additional_params['counter_value'] : '' ) . ')</span>';

                    if( !$additional_params['only_link'] ) {
                        if( $echo ) {
                            echo $html;
                            if( !$this->wpc_popup_flags['client'] ) {
                                $this->acc_get_assign_clients_popup( $current_page, $echo, $additional_params );
                                $this->wpc_popup_flags['client'] = true;
                            }
                        } else {
                            if( !$this->wpc_popup_flags['client'] ) {
                                $html .= $this->acc_get_assign_clients_popup( $current_page, $echo, $additional_params );
                                $this->wpc_popup_flags['client'] = true;
                            }
                            return $html;
                        }
                    } else {
                        if( $echo ) {
                            echo $html;
                        } else {
                            return $html;
                        }
                    }

                    break;
                 case 'circle':
                    $default_link_params = array(
                        'href'       => '#circle_popup_block',
                        'data-input' => 'wpc_circles',
                        'class'      => 'wpc_fancybox_link',
                        'data-type'  => 'wpc_circles',
                        'data-ajax'  => 0
                    );
                    $link_params = array_merge( $default_link_params, $link_params );
                    $params = '';
                    foreach( $link_params as $key=>$val ) {
                        if( $key != 'text' ) {
                            $params .= "$key=\"$val\"";
                        }
                    }
                    $html = '<span class="edit"><a ' . $params . '>' . ( isset( $link_params['text'] ) ? $link_params['text'] : __( 'Assign', WPC_CLIENT_TEXT_DOMAIN ) ) . '</a></span>';

                    if( is_array( $input_params ) ) {
                        $default_input_params = array(
                            'type'      => 'hidden',
                            'name'      => 'wpc_circles',
                            'id'        => 'wpc_circles',
                            'class'     => 'circles_field'
                        );
                        $input_params = array_merge( $default_input_params, $input_params );
                        $params = '';
                        foreach( $input_params as $key=>$val ) {
                            $params .= "$key=\"$val\"";
                        }
                        $html .= '<input ' . $params . ' />';
                    }

                    $default_additional_params = array(
                        'counter_value' => 0,
                        'only_link'     => 0
                    );
                    $additional_params = array_merge( $default_additional_params, $additional_params );
                    $html .= '&nbsp;<span class="edit counter_' . ( isset( $input_params['id'] ) ? $input_params['id'] : ( isset( $additional_params['input_ref'] ) ? $additional_params['input_ref'] : '' ) ) . '">(' . ( isset( $additional_params['counter_value'] ) ? $additional_params['counter_value'] : '' ) . ')</span>';

                    if( !$additional_params['only_link'] ) {
                        if( $echo ) {
                            echo $html;
                            if( !$this->wpc_popup_flags['circle'] ) {
                                $this->acc_get_assign_circles_popup( $current_page, $echo, $additional_params );
                                $this->wpc_popup_flags['circle'] = true;
                            }
                        } else {
                            if( !$this->wpc_popup_flags['circle'] ) {
                                $html .= $this->acc_get_assign_circles_popup( $current_page, $echo, $additional_params );
                                $this->wpc_popup_flags['circle'] = true;
                            }
                            return $html;
                        }
                    } else {
                        if( $echo ) {
                            echo $html;
                        } else {
                            return $html;
                        }
                    }

                    break;
                 case 'manager':
                    return '';
                    break;
                 default:
                    do_action('wpc_assign_' . $object . '_popup', $current_page, $link_params, $input_params, $additional_params, $echo );
                    break;
             }
         }


        /**
         * AJAX popup pagination
         **/
         //todo
         function ajax_get_popup_pagination_data($datatype = '', $cur_page = '1', $goto = 'first') {
             global $wpdb;
             $per_page = 50;
             $new_page = 1;
             $limit = '';
             $buttons = array('first' => true, 'prev' => true, 'next' => true, 'last' =>true);
             $open_popup = isset( $_POST['open_popup'] ) && $_POST['open_popup'];
             if( (isset( $_POST['data_type'] ) && !empty( $_POST['data_type'] ) ) || !empty( $datatype ) ) {
                 $type = ( isset( $_POST['data_type'] ) && !empty( $_POST['data_type'] ) ) ? $_POST['data_type'] : $datatype;
                 $cur_page = ( isset( $_POST['page'] ) && !empty( $_POST['page'] ) ) ? $_POST['page'] : $cur_page;
                 $display = ( isset( $_POST['display'] ) && !empty( $_POST['display'] ) ) ? $_POST['display'] : 'user_login';
                 $marks_type = ( isset( $_POST['marks_type'] ) && in_array( $_POST['marks_type'], array( 'checkbox', 'radio' ) ) ) ? $_POST['marks_type'] : 'checkbox';

                 $send_ajax = ( isset( $_POST['send_ajax'] ) && !empty( $_POST['send_ajax'] ) ) ? $_POST['send_ajax'] : 0;
                 $input_ref = ( isset( $_POST['input_ref'] ) && !empty( $_POST['input_ref'] ) ) ? $_POST['input_ref'] : '';
                 $current_page = ( isset( $_POST['current_page'] ) && !empty( $_POST['current_page'] ) ) ? $_POST['current_page'] : '';

                 $id = '';
                 if( 'wpc_clients' != $type && 0 === strpos( $type, 'wpc_clients' ) ){
                    $type = "wpc_clients";
                 }else if( 'wpc_circles' != $type && 0 === strpos( $type, 'wpc_circles' ) ){
                    $type = "wpc_circles";
                 }

                 if( $open_popup ) {
                    $block_array = apply_filters('wpc_assign_popup_add_blocks', array(), array(
                        'data_type'    => $type,
                        'page' => $cur_page,
                        'marks_type'   => $marks_type,
                        'send_ajax'    => $send_ajax,
                        'input_ref'    => $input_ref,
                        'current_page' => $current_page
                    ) );
                 } else {
                    $block_array = array();
                 }

                 switch($type) {
                    case 'wpc_clients': case 'send_wpc_clients': case 'wpc_clients_return':
                    {
                         if ( isset( $_POST['included_ids'] ) && !empty( $_POST['included_ids'] ) ) {
                             $included_ids = explode( ',', $_POST['included_ids'] );
                             $included_ids = " AND u.ID IN ('" . implode( "','", $included_ids ) . "')";
                         } else {
                             $included_ids = '';
                         }

                        if( isset( $_POST['order'] ) && !empty( $_POST['order'] ) ) {
                             $temp_order_array = explode( "_", $_POST['order'] );
                             if( 2 == count( $temp_order_array ) ) {
                                 if( ( 'asc' == strtolower( $temp_order_array[1] ) || 'desc' == strtolower($temp_order_array[1]) ) && ( 'show' == strtolower($temp_order_array[0]) || 'date' == strtolower($temp_order_array[0]) || 'first' == strtolower($temp_order_array[0]) ) ) {
                                     if( 'user_login' != $display && 'display_name' != $display ) {
                                         $display2 = $display;
                                         $display = 'um.meta_value';
                                     } else {
                                         $display2 = '';
                                     }
                                     switch( $temp_order_array[0] ) {
                                        case 'show':
                                            if( strpos( $display, '.' ) ) {
                                                $order_type = $display;
                                            } else {
                                                $order_type = "u.".$display;
                                            }
                                            break;
                                        case 'date':
                                            $order_type = 'u.user_registered';
                                            break;
                                        case 'first':
                                            $order_type = 'u.user_login';
                                            break;
                                     }
                                     $order = $temp_order_array[1];
                                 } else {
                                    $order_type = 'user_login';
                                    $order = "ASC";
                                 }
                             } else {
                                $order_type = 'user_login';
                                $order = "ASC";
                             }
                         } else {
                             $order_type = 'user_login';
                             $order = "ASC";
                         }


                        $where = '';
                        $excluded_clients = $this->cc_get_excluded_clients();

                        if ( is_array( $excluded_clients ) && count( $excluded_clients ) ) {
                            $where .= " AND u.ID NOT IN (" . implode( ",", $excluded_clients ) . ")";
                        }

                        if( isset($_POST['search']) && !empty($_POST['search']) ) {
                            $s = $_POST['search'];
                            $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                    WHERE (u.user_login LIKE '%$s%'
                                              OR u.ID = '$s'
                                              OR u.user_email LIKE '%$s%'
                                              OR u.user_nicename LIKE '%$s%'
                                              OR u.display_name LIKE '%$s%'
                                              OR (um.meta_key = 'wpc_cl_business_name' AND CAST(um.meta_value AS CHAR) LIKE '%$s%')
                                              OR (mt1.meta_key = 'nickname' AND CAST(mt1.meta_value AS CHAR) LIKE '%$s%'))
                                          AND (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%')
                                            $included_ids
                                            $where
                                    ";

                        } else {
                            $sql = "SELECT DISTINCT u.ID
                                    FROM {$wpdb->users} u
                                    INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                    INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                    WHERE (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%')
                                          $included_ids
                                          $where
                                    ";

                        }
                        if( $open_popup ) {
                            $wpc_ids_array = $wpdb->get_col( $sql );
                            $clients_count = count( $wpc_ids_array );
                        } else {
                            $wpdb->query($sql);
                            $sql = "SELECT FOUND_ROWS()";
                            $clients_count = $wpdb->get_var($sql);
                        }

                        if ( $clients_count > 0 ) {
                            if( $clients_count > $per_page) {
                                $goto = ( isset($_POST['goto']) && !empty($_POST['goto']) ) ? $_POST['goto'] : $goto;

                                switch($goto) {
                                    case 'first':
                                        $offset = 0;
                                        $new_page = 1;
                                        $buttons['first'] = false;
                                        $buttons['prev'] = false;
                                        break;
                                    case 'prev':
                                        $offset = ($cur_page-2)*$per_page;
                                        $new_page = $cur_page - 1;
                                        if($new_page <= 1) {
                                            $buttons['first'] = false;
                                            $buttons['prev'] = false;
                                            $new_page = 1;
                                        }
                                        break;
                                    case 'next':
                                        $last_page = ceil($clients_count/$per_page);
                                        $offset = $cur_page*$per_page;
                                        $new_page = $cur_page + 1;
                                        if($new_page >= $last_page) {
                                            $buttons['next'] = false;
                                            $buttons['last'] = false;
                                            $new_page = $last_page;
                                        }
                                        break;
                                    case 'last':
                                        $last_page = ceil($clients_count/$per_page);
                                        $offset = ($last_page - 1)*$per_page;
                                        $new_page = $last_page;
                                        $buttons['next'] = false;
                                        $buttons['last'] = false;
                                        break;
                                    default:
                                        $offset = 0;
                                        $new_page = 1;
                                        $buttons['first'] = false;
                                        $buttons['prev'] = false;
                                        break;
                                }
                                /*if( isset($_POST['search']) && !empty($_POST['search']) ) {*/
                                    $limit = "LIMIT $offset, $per_page";
                                /*} else {
                                    $args = array_merge($args, array('number' => $per_page, 'offset' => $offset));
                                }*/
                            } else {
                                $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                            }

                            $order_by_sql = $order_type." ".$order;

                            if('um.meta_value' == $display) {
                                $sql_inner_part = " AND um.meta_key = '$display2'";
                            } else {
                                $sql_inner_part = '';
                            }

                            if( isset( $_POST['search'] ) && !empty( $_POST['search'] ) ) {
                                if( isset( $_POST['order'] ) && 'first_asc' == $_POST['order'] && isset( $_POST['already_assinged'] ) && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {

                                    $assigned_users_str = $_POST['already_assinged'];
                                    $assigned_users = " AND u.ID IN ($assigned_users_str) ";
                                    $not_assigned_users = " AND u.ID NOT IN ($assigned_users_str) ";

                                    $sql = "(
                                                SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                                FROM {$wpdb->users} u
                                                INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                                INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                                WHERE (u.user_login LIKE '%$s%'
                                                          OR u.ID = '$s'
                                                          OR u.user_email LIKE '%$s%'
                                                          OR u.user_nicename LIKE '%$s%'
                                                          OR u.display_name LIKE '%$s%'
                                                          OR (um.meta_key = 'wpc_cl_business_name' AND CAST(um.meta_value AS CHAR) LIKE '%$s%')
                                                          OR (mt1.meta_key = 'nickname' AND CAST(mt1.meta_value AS CHAR) LIKE '%$s%'))
                                                      $assigned_users
                                                      $included_ids
                                                      AND (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                                FROM {$wpdb->users} u
                                                INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                                INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                                WHERE (u.user_login LIKE '%$s%'
                                                          OR u.ID = '$s'
                                                          OR u.user_email LIKE '%$s%'
                                                          OR u.user_nicename LIKE '%$s%'
                                                          OR u.display_name LIKE '%$s%'
                                                          OR (um.meta_key = 'wpc_cl_business_name' AND CAST(um.meta_value AS CHAR) LIKE '%$s%')
                                                          OR (mt1.meta_key = 'nickname' AND CAST(mt1.meta_value AS CHAR) LIKE '%$s%'))
                                                      $not_assigned_users
                                                      $included_ids
                                                      AND (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                                } else {
                                    $sql = "SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                            FROM {$wpdb->users} u
                                            INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                            INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                            INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                            INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                            WHERE (u.user_login LIKE '%$s%'
                                                      OR u.ID = '$s'
                                                      OR u.user_email LIKE '%$s%'
                                                      OR u.user_nicename LIKE '%$s%'
                                                      OR u.display_name LIKE '%$s%'
                                                      OR (um.meta_key = 'wpc_cl_business_name' AND CAST(um.meta_value AS CHAR) LIKE '%$s%')
                                                      OR (mt1.meta_key = 'nickname' AND CAST(mt1.meta_value AS CHAR) LIKE '%$s%'))
                                                  $included_ids
                                                  AND (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where
                                            ORDER BY $order_by_sql
                                            $limit";
                                }
                                $clients = $wpdb->get_results($sql);
                            } else {
                                if( isset( $_POST['order'] ) && 'first_asc' == $_POST['order'] && isset( $_POST['already_assinged'] ) && !empty( $_POST['already_assinged'] ) && $_POST['already_assinged'] != 'all' ) {

                                    $assigned_users_str = $_POST['already_assinged'];
                                    $assigned_users = " AND u.ID IN ($assigned_users_str) ";
                                    $not_assigned_users = " AND u.ID NOT IN ($assigned_users_str) ";

                                    $sql = "(
                                                SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                                FROM {$wpdb->users} u
                                                INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                                INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                                WHERE (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where $assigned_users $included_ids
                                                ORDER BY $order_by_sql
                                            ) UNION (
                                                SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                                FROM {$wpdb->users} u
                                                INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                                INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                                INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                                WHERE (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where $not_assigned_users $included_ids
                                                ORDER BY $order_by_sql
                                            )
                                            $limit";
                                } else {
                                    $sql = "SELECT DISTINCT u.ID, $display as user_login, u.user_login as data_name
                                            FROM {$wpdb->users} u
                                            INNER JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id $sql_inner_part)
                                            INNER JOIN {$wpdb->usermeta} AS mt1 ON (u.ID = mt1.user_id)
                                            INNER JOIN {$wpdb->usermeta} AS mt2 ON (u.ID = mt2.user_id)
                                            INNER JOIN {$wpdb->usermeta} AS mt3 ON (u.ID = mt3.user_id)
                                            WHERE (mt2.meta_key = '{$wpdb->prefix}capabilities' AND CAST(mt2.meta_value AS CHAR) LIKE '%\"wpc_client\"%') $where $included_ids
                                            ORDER BY $order_by_sql
                                            $limit";
                                }
                                $clients = $wpdb->get_results($sql);
                            }
                            $i = 0;
                            if($clients_count > $per_page) {
                                $n = 0;
                                for($j = 5; $j > 1; $j--) {
                                    if($per_page%$j == 0) {
                                        $n = $j;
                                        break;
                                    }
                                }
                                if($n == 0) {
                                    $n = ceil( $clients_count / 5 / $per_page );
                                } else {
                                    $n = $per_page/$n;
                                }
                            } else {
                                $n = ceil( $clients_count / 5 );
                            }

                            $html = '';
                            $html .= '<ul class="clients_list">';


                            foreach ( $clients as $client ) {
                                if ( $i%$n == 0 && 0 != $i )
                                    $html .= '</ul><ul class="clients_list">';

                                $html .= '<li><label title="' . addslashes( $client->user_login ) . '">';
                                $html .= '<input type="' . $marks_type . '" name="nfile_client_id[]" value="' . $client->ID . '" ' . ( $marks_type == 'radio' ? 'data-name="' . $client->data_name .'"' : '' ) . ' /> ';
                                $html .= $client->user_login;
                                $html .= '</label></li>';

                                $i++;
                            }
                            $html .= '</ul>';
                        } else {
                            $html = __( 'No Clients For Assign.', WPC_CLIENT_TEXT_DOMAIN );
                            $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                        }

                        $result_array = array(
                            'html' => $html,
                            'page' => $new_page,
                            'buttons' => $buttons,
                            'per_page' => $per_page,
                            'count' => $clients_count,
                            'blocks' => $block_array
                        );

                        if( $open_popup ) {
                            $result_array['ids_list'] = $wpc_ids_array;
                        }
                        if($type == 'wpc_clients_return')
                            return json_encode( $result_array );
                        else
                            echo json_encode( $result_array );
                        break;
                    }
                    case 'wpc_circles': case 'send_wpc_circles': case 'wpc_circles_return':
                    {

                         if ( isset( $_POST['included_ids'] ) && !empty( $_POST['included_ids'] ) ) {
                             $included_ids = explode( ',', $_POST['included_ids'] );
                             $included_ids = " AND group_id IN ('" . implode( "','", $included_ids ) . "')";
                         } else {
                             $included_ids = '';
                         }

                        if( isset($_POST['order']) && !empty($_POST['order']) ) {
                             $temp_order_array = explode("_", $_POST['order']);
                             if( 2 == count($temp_order_array) ) {
                                 if( ( 'asc' == strtolower($temp_order_array[1]) || 'desc' == strtolower($temp_order_array[1]) ) && ( 'show' == strtolower($temp_order_array[0]) || 'date' == strtolower($temp_order_array[0]) || 'first' == strtolower($temp_order_array[0]) ) ) {
                                     switch($temp_order_array[0]) {
                                        case 'show':
                                            $order_type = "group_name";
                                            break;
                                        case 'date':
                                            $order_type = 'group_id';
                                            break;
                                        case 'first':
                                            $order_type = 'group_name';
                                            break;
                                     }
                                     $order = $temp_order_array[1];
                                 } else {
                                    $order_type = 'group_name';
                                    $order = "ASC";
                                 }
                             } else {
                                $order_type = 'group_name';
                                $order = "ASC";
                             }
                         } else {
                             $order_type = 'group_name';
                             $order = "ASC";
                         }

                        $where = '';

                        if( isset($_POST['search']) && !empty($_POST['search']) ) {
                            $where .= " AND group_name LIKE '%".$_POST['search']."%'";
                        }
                        $sql = "SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $where $included_ids ORDER BY $order_type $order";
                        if( $open_popup ) {
                            $wpc_ids_array = $wpdb->get_col( $sql );
                            $circles_count = count( $wpc_ids_array );
                        } else {
                            $wpdb->query($sql);
                            $sql_count = "SELECT FOUND_ROWS()";
                            $circles_count = $wpdb->get_var($sql_count);
                        }

                        if( isset($_POST['order']) && 'first_asc' == $_POST['order'] ) {
                            if( isset( $_POST['already_assinged'] ) && !empty( $_POST['already_assinged'] ) ) {
                                $assigned_users_str = $_POST['already_assinged'];
                                if( $assigned_users_str != 'all' ) {
                                    $assigned_users = " AND group_id IN ($assigned_users_str) ";
                                    $not_assigned_users = " AND group_id NOT IN ($assigned_users_str) ";
                                } else {
                                    $assigned_users = '';
                                    $not_assigned_users = '';
                                }
                            } else {
                                $assigned_users = '';
                                $not_assigned_users = '';
                            }
                            if( isset( $assigned_users ) && !empty( $assigned_users ) ) {
                                $sql = "(SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $assigned_users $where $included_ids ORDER BY $order_type $order)
                                        UNION
                                        (SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE 1=1 $not_assigned_users $where $included_ids ORDER BY $order_type $order)";
                            }
                        }

                        if ( $circles_count > 0 ) {
                            if($circles_count > $per_page) {
                                $goto = ( isset($_POST['goto']) && !empty($_POST['goto']) ) ? $_POST['goto'] : $goto;
                                switch($goto) {
                                    case 'first':
                                        $offset = 0;
                                        $new_page = 1;
                                        $buttons['first'] = false;
                                        $buttons['prev'] = false;
                                        break;
                                    case 'prev':
                                        $offset = ($cur_page-2)*$per_page;
                                        $new_page = $cur_page - 1;
                                        if($new_page <= 1) {
                                            $buttons['first'] = false;
                                            $buttons['prev'] = false;
                                            $new_page = 1;
                                        }
                                        break;
                                    case 'next':
                                        $last_page = ceil($circles_count/$per_page);
                                        $offset = $cur_page*$per_page;
                                        $new_page = $cur_page + 1;
                                        if($new_page >= $last_page) {
                                            $buttons['next'] = false;
                                            $buttons['last'] = false;
                                            $new_page = $last_page;
                                        }
                                        break;
                                    case 'last':
                                        $last_page = ceil($circles_count/$per_page);
                                        $offset = ($last_page - 1)*$per_page;
                                        $new_page = $last_page;
                                        $buttons['next'] = false;
                                        $buttons['last'] = false;
                                        break;
                                    default:
                                        $offset = 0;
                                        $new_page = 1;
                                        $buttons['first'] = false;
                                        $buttons['prev'] = false;
                                        break;
                                }
                                $limit = " LIMIT $offset, $per_page";
                            } else {
                                $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                            }
                        } else {
                            $buttons = array('first' => false, 'prev' => false, 'next' => false, 'last' =>false);
                        }

                        $sql = $sql.$limit;
                        $groups = $wpdb->get_results( $sql, "ARRAY_A");

                        if ( is_array( $groups ) && 0 < count( $groups ) ) {

                            $i = 0;
                            if($circles_count > $per_page) {
                                $n = 0;
                                for($j = 5; $j > 1; $j--) {
                                    if($per_page%$j == 0) {
                                        $n = $j;
                                        break;
                                    }
                                }
                                if($n == 0) {
                                    $n = ceil( $circles_count / 5 / $per_page );
                                } else {
                                    $n = $per_page/$n;
                                }
                            } else {
                                $n = ceil( $circles_count / 5 );
                            }
                            $html = '';
                            $html .= '<ul class="clients_list">';

                            foreach ( $groups as $group ) {
                                if ( $i%$n == 0 && 0 != $i )
                                    $html .= '</ul><ul class="clients_list">';

                                $html .= '<li><label title="' . addslashes( $group['group_name'] ) . '">';
                                $html .= '<input type="' . $marks_type . '" name="nfile_groups_id[]" value="' . $group['group_id'] . '" ' . ( $marks_type == 'radio' ? 'data-name="' . $client->data_name .'"' : '' ) . ' /> ';
                                $html .= $group['group_name'];
                                $html .= '</label></li>';

                                $i++;
                            }

                            $html .= '</ul>';
                        } else {
                            $html = __( 'No Client Circles For Assign.', WPC_CLIENT_TEXT_DOMAIN );
                        }

                        $result_array = array(
                            'html' => $html,
                            'page' => $new_page,
                            'buttons' => $buttons,
                            'per_page' => $per_page,
                            'count' => $circles_count,
                            'blocks' => $block_array
                        );
                        if( $open_popup ) {
                            $result_array['ids_list'] = $wpc_ids_array;
                        }
                        if($type == 'wpc_circles_return')
                            return json_encode( $result_array );
                        else
                            echo json_encode( $result_array );
                        break;
                    }

                 }
             }
             exit;
         }


         /*
        * called from submit settings
        */
        function _settings_update_func( $settings, $key ) {

            if ( empty( $key ) )
                return '';

            update_option( 'wpc_' . $key, $settings );

            return '';

        }


        /*
        * Add ez hub settings - pages_access
        */
        function add_ez_hub_settings_pages_access( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {

            $title = __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN );
            $text_copy = '{pages_access_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td>
                                        <label for="pages_access_text_<?php echo $item_number ?>"><?php _e( 'Text: "Pages you have access to"',WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][pages_access][text]" id="pages_access_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td>
                                    <label for="pages_access_show_current_page_<?php echo $item_number ?>"><?php _e( 'Show Current Page', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][pages_access][show_current_page]" id="pages_access_show_current_page_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_current_page'] ) || 'yes' == $hub_settings['show_current_page'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_current_page'] ) && 'no' == $hub_settings['show_current_page'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="pages_access_sort_type_<?php echo $item_number ?>"><?php _e( 'Sort Type', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][pages_access][sort_type]" id="pages_access_sort_type_<?php echo $item_number ?>">
                                        <option value="date" <?php echo ( !isset( $hub_settings['sort_type'] ) || 'date' == $hub_settings['sort_type'] ) ? 'selected' : '' ?>><?php _e( 'Date', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="title" <?php echo ( isset( $hub_settings['sort_type'] ) && 'title' == $hub_settings['sort_type'] ) ? 'selected' : '' ?>><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="pages_access_sort_<?php echo $item_number ?>"><?php _e( 'Sort', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][pages_access][sort]" id="pages_access_sort_<?php echo $item_number ?>">
                                        <option value="asc" <?php echo ( !isset( $hub_settings['sort'] ) || 'asc' == $hub_settings['sort'] ) ? 'selected' : '' ?>><?php _e( 'ASC', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="desc" <?php echo ( isset( $hub_settings['sort'] ) && 'desc' == $hub_settings['sort'] ) ? 'selected' : '' ?>><?php _e( 'DESC', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="pages_access_show_categories_titles_<?php echo $item_number ?>"><?php _e( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][pages_access][show_categories_titles]" id="pages_access_show_categories_titles_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_categories_titles'] ) || 'yes' == $hub_settings['show_categories_titles'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_categories_titles'] ) && 'no' == $hub_settings['show_categories_titles'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="pages_access_categories_<?php echo $item_number ?>"><?php _e( 'Categories',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <input type="text" name="hub_settings[<?php echo $item_number ?>][pages_access][categories]" id="pages_access_categories_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['categories'] ) ) ? $hub_settings['categories'] : '' ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="pages_access_show_sort_<?php echo $item_number ?>"><?php _e( 'Show Sort', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][pages_access][show_sort]" id="pages_access_show_sort_<?php echo $item_number ?>">
                                        <option value="no" <?php echo ( !isset( $hub_settings['show_sort'] ) || 'no' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="yes" <?php echo ( isset( $hub_settings['show_sort'] ) && 'yes' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );

        }


        /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_pages_access( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();
            $temp_arr['menu_items']['pages_access'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if ( isset( $hub_settings['show_current_page'] ) && 'no' == $hub_settings['show_current_page'] ) {
                $attrs .= ' show_current_page="no" ';
            } else {
                $attrs .= ' show_current_page="yes" ';
            }

            if ( isset( $hub_settings['sort_type'] ) && 'title' == $hub_settings['sort_type'] ) {
                $attrs .= ' sort_type="title" ';
            } else {
                $attrs .= ' sort_type="date" ';
            }

            if ( isset( $hub_settings['sort'] ) && 'desc' == $hub_settings['sort'] ) {
                $attrs .= ' sort="desc" ';
            } else {
                $attrs .= ' sort="asc" ';
            }

            if ( isset( $hub_settings['categories'] ) && '' != $hub_settings['categories'] ) {
                $attrs .= ' categories="' . $hub_settings['categories'] . '" ';
            } else {
                $attrs .= '';
            }

            if ( isset( $hub_settings['show_categories_titles'] ) && 'yes' == $hub_settings['show_categories_titles'] ) {
                $attrs .= ' show_categories_titles="yes" ';
            } else {
                $attrs .= ' show_categories_titles="no" ';
            }

            if ( isset( $hub_settings['show_sort'] ) && 'yes' == $hub_settings['show_sort'] ) {
                $attrs .= ' show_sort="yes" ';
            } else {
                $attrs .= ' show_sort="no" ';
            }

            $temp_arr['page_body'] = '[wpc_client_pagel ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * Add ez hub settings - files_uploaded
        */
        function add_ez_hub_settings_files_uploaded( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            global $wpdb;

            $title = '<span class="wpc_pro_settings_link">' . sprintf( __( 'Files from %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . ' <span>Pro</span></span>';
            $text_copy = '{files_uploaded_' . $item_number . '}' ;

            ob_start();
            ?>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="files_uploaded_text_<?php echo $item_number ?>"><?php printf( __( 'Text: Files from %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][files_uploaded][text]" id="files_uploaded_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Files you have uploaded', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="files_uploaded_no_text_<?php echo $item_number ?>"><?php _e( 'Text: "You don\'t have any files"',WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][files_uploaded][no_text]" id="files_uploaded_no_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['no_text'] ) ) ? $hub_settings['no_text'] : __( 'You don\'t have any files', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="files_uploaded_show_sort_<?php echo $item_number ?>"><?php _e( 'Show Sort', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_sort]" id="files_uploaded_show_sort_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_sort'] ) || 'yes' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_sort'] ) && 'no' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_date_<?php echo $item_number ?>"><?php _e( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_date]" id="files_uploaded_show_date_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_date'] ) || 'yes' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_date'] ) && 'no' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_tags_<?php echo $item_number ?>"><?php _e( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_tags]" id="files_uploaded_show_tags_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_tags'] ) || 'yes' == $hub_settings['show_tags'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_tags'] ) && 'yes' != $hub_settings['show_tags'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_size_<?php echo $item_number ?>"><?php _e( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_size]" id="files_uploaded_show_size_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_size'] ) || 'yes' == $hub_settings['show_size'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_size'] ) && 'no' == $hub_settings['show_size'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_last_download_date_<?php echo $item_number ?>"><?php _e( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_last_download_date]" id="files_uploaded_show_last_download_date_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_last_download_date'] ) || 'yes' == $hub_settings['show_last_download_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_last_download_date'] ) && 'no' == $hub_settings['show_last_download_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <?php
                                $wpc_file_sharing = $this->cc_get_settings( 'file_sharing' );
                                if( !isset( $hub_settings['show_file_cats'] ) ) {
                                    if ( isset( $wpc_file_sharing['show_file_cats'] ) && 'no' == $wpc_file_sharing['show_file_cats'] ) {
                                        $hub_settings['show_file_cats'] = 'no';
                                    } else {
                                        $hub_settings['show_file_cats'] = 'yes';
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_file_cats_<?php echo $item_number ?>"><?php _e( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_file_cats]" id="files_uploaded_show_file_cats_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_file_cats'] ) || 'yes' == $hub_settings['show_file_cats'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_file_cats'] ) && 'no' == $hub_settings['show_file_cats'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_category_<?php echo $item_number ?>"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][category]" id="files_uploaded_category_<?php echo $item_number ?>">
                                        <option value="" >---</option>
                                        <?php $parent_categories = $wpdb->get_results(
                                            "SELECT cat_id,
                                                cat_name
                                            FROM {$wpdb->prefix}wpc_client_file_categories
                                            WHERE parent_id='0'
                                            ORDER BY cat_order",
                                        ARRAY_A );

                                        $depth = 0;
                                        //change structure of array for display cat name in row in table and selectbox
                                        foreach( $parent_categories as $category ) {
                                            $categories[$category['cat_id']] = array(
                                                'category_name'=>$category['cat_name'],
                                                'depth' => $depth
                                            );

                                            $children_categories = array();
                                            $categories += $children_categories;
                                        }
                                        if ( isset( $categories ) && is_array( $categories ) ) {
                                            foreach( $categories as $key => $value ) { ?>
                                                <option value="<?php echo $key ?>" <?php echo ( isset( $hub_settings['category'] ) && $hub_settings['category'] == $key ) ? 'selected' : '' ?>>
                                                    <?php if( $value['depth'] > 0 ) {

                                                        for( $var = 0; $var < $value['depth']; $var++ ) {
                                                            echo '&nbsp;';
                                                        }

                                                        echo '&mdash;';
                                                    }
                                                    echo ' ' . $value['category_name'] . '( ID #' . $key . ')'; ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_with_subcategories_<?php echo $item_number ?>"><?php _e( 'With sub-Categories', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][with_subcategories]" id="files_uploaded_with_subcategories_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['with_subcategories'] ) || 'yes' == $hub_settings['with_subcategories'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['with_subcategories'] ) && 'no' == $hub_settings['with_subcategories'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_view_type_<?php echo $item_number ?>"><?php _e( 'View Type', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][view_type]" id="files_uploaded_view_type_<?php echo $item_number ?>">
                                        <option value="list" <?php echo ( !isset( $hub_settings['view_type'] ) || 'list' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'List', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="table" <?php echo ( isset( $hub_settings['view_type'] ) && 'table' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'Table', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="tree" <?php echo ( isset( $hub_settings['view_type'] ) && 'tree' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'Tree', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_actions_<?php echo $item_number ?>"><?php _e( 'Show Actions (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_actions]" id="files_uploaded_show_actions_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_actions'] ) || 'yes' == $hub_settings['show_actions'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_actions'] ) && 'no' == $hub_settings['show_actions'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_thumbnails_<?php echo $item_number ?>"><?php _e( 'Show Thumbnails (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_thumbnails]" id="files_uploaded_show_thumbnails_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_thumbnails'] ) || 'yes' == $hub_settings['show_thumbnails'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_thumbnails'] ) && 'no' == $hub_settings['show_thumbnails'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_search_<?php echo $item_number ?>"><?php _e( 'Show Search (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_search]" id="files_uploaded_show_search_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_search'] ) || 'yes' == $hub_settings['show_search'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_search'] ) && 'no' == $hub_settings['show_search'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_filters_<?php echo $item_number ?>"><?php _e( 'Show Filters (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_filters]" id="files_uploaded_show_filters_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_filters'] ) || 'yes' == $hub_settings['show_filters'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_filters'] ) && 'no' == $hub_settings['show_filters'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_pagination_<?php echo $item_number ?>"><?php _e( 'Show Pagination (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_pagination]" id="files_uploaded_show_pagination_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_pagination'] ) || 'yes' == $hub_settings['show_pagination'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_pagination'] ) && 'no' == $hub_settings['show_pagination'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_uploaded_show_pagination_by_<?php echo $item_number ?>"><?php _e( 'Show Pagination By (For table with pagination only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_uploaded][show_pagination_by]" id="files_uploaded_show_pagination_by_<?php echo $item_number ?>">
                                        <option value="5" <?php echo ( !isset( $hub_settings['show_pagination_by'] ) || '5' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '5', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="10" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '10' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '10', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="15" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '15' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '15', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="20" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '20' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '20', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_files_uploaded( $tabs_items, $hub_settings = array() ) {

            //uploded files
            $temp_arr = array();

            $temp_arr['menu_items']['files_uploaded'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';
            $attrs = '';

            if ( isset( $hub_settings['show_sort'] ) && 'no' == $hub_settings['show_sort'] ) {
                $attrs .= ' show_sort="no" ';
            } else {
                $attrs .= ' show_sort="yes" ';
            }

            if ( isset( $hub_settings['show_date'] ) && 'no' == $hub_settings['show_date'] ) {
                $attrs .= ' show_date="no" ';
            } else {
                $attrs .= ' show_date="yes" ';
            }

            if ( isset( $hub_settings['show_size'] ) && 'no' == $hub_settings['show_size'] ) {
                $attrs .= ' show_size="no" ';
            } else {
                $attrs .= ' show_size="yes" ';
            }

            $no_text = ( isset( $hub_settings['no_text'] ) ) ? $hub_settings['no_text'] : '';
            $attrs .= ' no_text="' . $no_text . '"';

            if ( isset( $hub_settings['category'] ) && '' != $hub_settings['category'] ) {
                $attrs .= ' category="' . $hub_settings['category'] . '"';
            } else {
                $attrs .= ' category=""';
            }

            if ( isset( $hub_settings['with_subcategories'] ) && '' != $hub_settings['with_subcategories'] ) {
                $attrs .= ' with_subcategories="' . $hub_settings['with_subcategories'] . '" ';
            } else {
                $attrs .= ' with_subcategories="" ';
            }

            if ( isset( $hub_settings['view_type'] ) && 'table' == $hub_settings['view_type'] ) {
                $attrs .= ' view_type="' . $hub_settings['view_type'] . '" ';
            } elseif ( isset( $hub_settings['view_type'] ) && 'tree' == $hub_settings['view_type'] ) {
                $attrs .= ' view_type="' . $hub_settings['view_type'] . '" ';
            } else {
                $attrs .= ' view_type="list" ';
            }

            if ( isset( $hub_settings['show_tags'] ) && 'yes' == $hub_settings['show_tags'] ) {
                $attrs .= ' show_tags="yes" ';
            } else {
                $attrs .= ' show_tags="no" ';
            }

            if ( isset( $hub_settings['show_file_cats'] ) && 'yes' == $hub_settings['show_file_cats'] ) {
                $attrs .= ' show_file_cats="yes" ';
            } else {
                $attrs .= ' show_file_cats="no" ';
            }

            if ( isset( $hub_settings['show_last_download_date'] ) && 'yes' == $hub_settings['show_last_download_date'] ) {
                $attrs .= ' show_last_download_date="yes" ';
            } else {
                $attrs .= ' show_last_download_date="no" ';
            }

            if ( isset( $hub_settings['show_thumbnails'] ) && 'yes' == $hub_settings['show_thumbnails'] ) {
                $attrs .= ' show_thumbnails="yes" ';
            } else {
                $attrs .= ' show_thumbnails="no" ';
            }

            if ( isset( $hub_settings['show_actions'] ) && 'yes' == $hub_settings['show_actions'] ) {
                $attrs .= ' show_actions="yes" ';
            } else {
                $attrs .= ' show_actions="no" ';
            }

            if ( isset( $hub_settings['show_search'] ) && 'yes' == $hub_settings['show_search'] ) {
                $attrs .= ' show_search="yes" ';
            } else {
                $attrs .= ' show_search="no" ';
            }

            if ( isset( $hub_settings['show_filters'] ) && 'yes' == $hub_settings['show_filters'] ) {
                $attrs .= ' show_filters="yes" ';
            } else {
                $attrs .= ' show_filters="no" ';
            }

            if ( isset( $hub_settings['show_pagination'] ) && 'yes' == $hub_settings['show_pagination'] ) {
                $attrs .= ' show_pagination="yes" ';
            } else {
                $attrs .= ' show_pagination="no" ';
            }

            if ( isset( $hub_settings['show_pagination_by'] ) && !empty( $hub_settings['show_pagination_by'] ) ) {
                $attrs .= ' show_pagination_by="' . $hub_settings['show_pagination_by'] . '" ';
            } else {
                $attrs .= ' show_pagination_by="5" ';
            }

            $temp_arr['page_body'] = '[wpc_client_fileslu ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * Add ez hub settings - files_access
        */
        function add_ez_hub_settings_files_access( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            global $wpdb;

            $title = '<span class="wpc_pro_settings_link">' . sprintf( __( 'Files to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . ' <span>Pro</span></span>';
            $text_copy = '{files_access_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="files_access_text_<?php echo $item_number ?>"><?php printf( __( 'Text:Files to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][files_access][text]" id="files_access_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Files you have access to', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="files_access_no_text_<?php echo $item_number ?>"><?php _e( 'Text: "You don\'t have any files"',WPC_CLIENT_TEXT_DOMAIN ); ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][files_access][no_text]" id="files_access_no_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['no_text'] ) ) ? $hub_settings['no_text'] : __( 'You don\'t have any files', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="files_access_show_sort_<?php echo $item_number ?>"><?php _e( 'Show Sort', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_sort]" id="files_access_show_sort_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_sort'] ) || 'yes' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_sort'] ) && 'no' == $hub_settings['show_sort'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_date_<?php echo $item_number ?>"><?php _e( 'Show Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_date]" id="files_access_show_date_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_date'] ) || 'yes' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_date'] ) && 'no' == $hub_settings['show_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_size_<?php echo $item_number ?>"><?php _e( 'Show Size', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_size]" id="files_access_show_size_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_size'] ) || 'yes' == $hub_settings['show_size'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_size'] ) && 'no' == $hub_settings['show_size'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_tags_<?php echo $item_number ?>"><?php _e( 'Show Tags', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_tags]" id="files_access_show_tags_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_tags'] ) || 'yes' == $hub_settings['show_tags'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_tags'] ) && 'no' == $hub_settings['show_tags'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_last_download_date_<?php echo $item_number ?>"><?php _e( 'Show Last Download Date', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_last_download_date]" id="files_access_show_last_download_date_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_last_download_date'] ) || 'yes' == $hub_settings['show_last_download_date'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_last_download_date'] ) && 'no' == $hub_settings['show_last_download_date'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <?php $wpc_file_sharing = $this->cc_get_settings( 'file_sharing' );
                            if( !isset( $hub_settings['show_file_cats'] ) ) {
                                if ( isset( $wpc_file_sharing['show_file_cats'] ) && 'no' == $wpc_file_sharing['show_file_cats'] ) {
                                    $hub_settings['show_file_cats'] = 'no';
                                } else {
                                    $hub_settings['show_file_cats'] = 'yes';
                                }
                            } ?>
                            <tr>
                                <td>
                                    <label for="files_access_show_file_cats_<?php echo $item_number ?>"><?php _e( 'Show Categories Titles', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_file_cats]" id="files_access_show_file_cats_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_file_cats'] ) || 'yes' == $hub_settings['show_file_cats'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_file_cats'] ) && 'no' == $hub_settings['show_file_cats'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_category_<?php echo $item_number ?>"><?php _e( 'Category', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][category]" id="files_access_category_<?php echo $item_number ?>">
                                        <option value="" >---</option>
                                        <?php $parent_categories = $wpdb->get_results(
                                            "SELECT cat_id,
                                                cat_name
                                            FROM {$wpdb->prefix}wpc_client_file_categories
                                            WHERE parent_id='0'
                                            ORDER BY cat_order",
                                        ARRAY_A );

                                        $depth = 0;
                                        //change structure of array for display cat name in row in table and selectbox
                                        foreach( $parent_categories as $category ) {
                                            $categories[$category['cat_id']] = array(
                                                'category_name'=>$category['cat_name'],
                                                'depth' => $depth
                                            );

                                            $children_categories = array();
                                            $categories += $children_categories;
                                        }
                                        if ( isset( $categories ) && is_array( $categories ) ) {
                                            foreach( $categories as $key => $value ) { ?>
                                                <option value="<?php echo $key ?>" <?php echo ( isset( $hub_settings['category'] ) && $hub_settings['category'] == $key ) ? 'selected' : '' ?>>
                                                    <?php if( $value['depth'] > 0 ) {

                                                        for( $var = 0; $var < $value['depth']; $var++ ) {
                                                            echo '&nbsp;';
                                                        }

                                                        echo '&mdash;';
                                                    }
                                                    echo ' ' . $value['category_name'] . '( ID #' . $key . ')'; ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_with_subcategories_<?php echo $item_number ?>"><?php _e( 'With sub-Categories', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][with_subcategories]" id="files_access_with_subcategories_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['with_subcategories'] ) || 'yes' == $hub_settings['with_subcategories'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['with_subcategories'] ) && 'no' == $hub_settings['with_subcategories'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_exclude_author_<?php echo $item_number ?>"><?php _e( 'Exclude Author', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][exclude_author]" id="files_access_exclude_author_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['exclude_author'] ) || 'yes' == $hub_settings['exclude_author'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['exclude_author'] ) && 'no' == $hub_settings['exclude_author'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_view_type_<?php echo $item_number ?>"><?php _e( 'View Type', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][view_type]" id="files_access_view_type_<?php echo $item_number ?>">
                                        <option value="list" <?php echo ( !isset( $hub_settings['view_type'] ) || 'list' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'List', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="table" <?php echo ( isset( $hub_settings['view_type'] ) && 'table' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'Table', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="tree" <?php echo ( isset( $hub_settings['view_type'] ) && 'tree' == $hub_settings['view_type'] ) ? 'selected' : '' ?>><?php _e( 'Tree', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_actions_<?php echo $item_number ?>"><?php _e( 'Show Actions (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_actions]" id="files_access_show_actions_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_actions'] ) || 'yes' == $hub_settings['show_actions'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_actions'] ) && 'no' == $hub_settings['show_actions'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_thumbnails_<?php echo $item_number ?>"><?php _e( 'Show Thumbnails (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_thumbnails]" id="files_access_show_thumbnails_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_thumbnails'] ) || 'yes' == $hub_settings['show_thumbnails'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_thumbnails'] ) && 'no' == $hub_settings['show_thumbnails'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_search_<?php echo $item_number ?>"><?php _e( 'Show Search (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_search]" id="files_access_show_search_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_search'] ) || 'yes' == $hub_settings['show_search'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_search'] ) && 'no' == $hub_settings['show_search'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_filters_<?php echo $item_number ?>"><?php _e( 'Show Filters (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_filters]" id="files_access_show_filters_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_filters'] ) || 'yes' == $hub_settings['show_filters'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_filters'] ) && 'no' == $hub_settings['show_filters'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_pagination_<?php echo $item_number ?>"><?php _e( 'Show Pagination (For table only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_pagination]" id="files_access_show_pagination_<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( !isset( $hub_settings['show_pagination'] ) || 'yes' == $hub_settings['show_pagination'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( isset( $hub_settings['show_pagination'] ) && 'no' == $hub_settings['show_pagination'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="files_access_show_pagination_by_<?php echo $item_number ?>"><?php _e( 'Show Pagination By (For table with pagination only)', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][files_access][show_pagination_by]" id="files_access_show_pagination_by_<?php echo $item_number ?>">
                                        <option value="5" <?php echo ( !isset( $hub_settings['show_pagination_by'] ) || '5' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '5', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="10" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '10' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '10', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="15" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '15' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '15', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="20" <?php echo ( isset( $hub_settings['show_pagination_by'] ) && '20' == $hub_settings['show_pagination_by'] ) ? 'selected' : '' ?>><?php _e( '20', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_files_access( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();

            $temp_arr['menu_items']['files_access'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if ( isset( $hub_settings['show_sort'] ) && 'no' == $hub_settings['show_sort'] ) {
                $attrs .= ' show_sort="no" ';
            } else {
                $attrs .= ' show_sort="yes" ';
            }

            if ( isset( $hub_settings['show_date'] ) && 'no' == $hub_settings['show_date'] ) {
                $attrs .= ' show_date="no" ';
            } else {
                $attrs .= ' show_date="yes" ';
            }

            if ( isset( $hub_settings['show_size'] ) && 'no' == $hub_settings['show_size'] ) {
                $attrs .= ' show_size="no" ';
            } else {
                $attrs .= ' show_size="yes" ';
            }

            if ( isset( $hub_settings['show_tags'] ) && 'no' == $hub_settings['show_tags'] ) {
                $attrs .= ' show_tags="no" ';
            } else {
                $attrs .= ' show_tags="yes" ';
            }

            $no_text = ( isset( $hub_settings['no_text'] ) ) ? $hub_settings['no_text'] : '';
            $attrs .= ' no_text="' . $no_text . '"';

            if ( isset( $hub_settings['category'] ) && '' != $hub_settings['category'] ) {
                $attrs .= ' category="' . $hub_settings['category'] . '" ';
            } else {
                $attrs .= ' category="" ';
            }

            if ( isset( $hub_settings['with_subcategories'] ) && '' != $hub_settings['with_subcategories'] ) {
                $attrs .= ' with_subcategories="' . $hub_settings['with_subcategories'] . '" ';
            } else {
                $attrs .= ' with_subcategories="" ';
            }

            if ( isset( $hub_settings['exclude_author'] ) && 'no' == $hub_settings['exclude_author'] ) {
                $attrs .= ' exclude_author="no" ';
            } else {
                $attrs .= ' exclude_author="yes" ';
            }
            if ( isset( $hub_settings['view_type'] ) && 'table' == $hub_settings['view_type'] ) {
                $attrs .= ' view_type="' . $hub_settings['view_type'] . '" ';
            } elseif ( isset( $hub_settings['view_type'] ) && 'tree' == $hub_settings['view_type'] ) {
                $attrs .= ' view_type="' . $hub_settings['view_type'] . '" ';
            } else {
                $attrs .= ' view_type="list" ';
            }


            if ( isset( $hub_settings['show_file_cats'] ) && 'yes' == $hub_settings['show_file_cats'] ) {
                $attrs .= ' show_file_cats="yes" ';
            } else {
                $attrs .= ' show_file_cats="no" ';
            }

            if ( isset( $hub_settings['show_last_download_date'] ) && 'yes' == $hub_settings['show_last_download_date'] ) {
                $attrs .= ' show_last_download_date="yes" ';
            } else {
                $attrs .= ' show_last_download_date="no" ';
            }

            if ( isset( $hub_settings['show_thumbnails'] ) && 'yes' == $hub_settings['show_thumbnails'] ) {
                $attrs .= ' show_thumbnails="yes" ';
            } else {
                $attrs .= ' show_thumbnails="no" ';
            }

            if ( isset( $hub_settings['show_actions'] ) && 'yes' == $hub_settings['show_actions'] ) {
                $attrs .= ' show_actions="yes" ';
            } else {
                $attrs .= ' show_actions="no" ';
            }

            if ( isset( $hub_settings['show_search'] ) && 'yes' == $hub_settings['show_search'] ) {
                $attrs .= ' show_search="yes" ';
            } else {
                $attrs .= ' show_search="no" ';
            }

            if ( isset( $hub_settings['show_filters'] ) && 'yes' == $hub_settings['show_filters'] ) {
                $attrs .= ' show_filters="yes" ';
            } else {
                $attrs .= ' show_filters="no" ';
            }

            if ( isset( $hub_settings['show_pagination'] ) && 'yes' == $hub_settings['show_pagination'] ) {
                $attrs .= ' show_pagination="yes" ';
            } else {
                $attrs .= ' show_pagination="no" ';
            }

            if ( isset( $hub_settings['show_pagination_by'] ) && !empty( $hub_settings['show_pagination_by'] ) ) {
                $attrs .= ' show_pagination_by="' . $hub_settings['show_pagination_by'] . '" ';
            } else {
                $attrs .= ' show_pagination_by="5" ';
            }

            $temp_arr['page_body'] = '[wpc_client_filesla ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * Add ez hub settings - upload_files
        */
        function add_ez_hub_settings_upload_files( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {
            global $wpdb;

            $title = '<span class="wpc_pro_settings_link">' . __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>';
            $text_copy = '{upload_files_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="upload_files_text_<?php echo $item_number ?>"><?php _e( 'Text: "Upload Files"',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][upload_files][text]" id="upload_files_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="upload_files_categories_<?php echo $item_number ?>"><?php _e( 'Categories', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select multiple name="hub_settings[<?php echo $item_number ?>][upload_files][categories][]" id="upload_files_categories_<?php echo $item_number ?>">
                                        <option value="all" <?php echo ( isset( $hub_settings['categories'] ) && in_array( 'all', $hub_settings['categories'] ) ) ? 'selected' : '' ?> ><?php _e( 'All', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <?php $parent_categories = $wpdb->get_results(
                                            "SELECT cat_id,
                                                cat_name
                                            FROM {$wpdb->prefix}wpc_client_file_categories
                                            WHERE parent_id='0'
                                            ORDER BY cat_order",
                                        ARRAY_A );

                                        $depth = 0;
                                        //change structure of array for display cat name in row in table and selectbox
                                        foreach( $parent_categories as $category ) {
                                            $categories[$category['cat_id']] = array(
                                                'category_name'=>$category['cat_name'],
                                                'depth' => $depth
                                            );

                                            $children_categories = array();
                                            $categories += $children_categories;
                                        }
                                        if ( isset( $categories ) && is_array( $categories ) ) {
                                            foreach( $categories as $key => $value ) { ?>
                                                <option value="<?php echo $key ?>" <?php echo ( isset( $hub_settings['categories'] ) && in_array( $key, $hub_settings['categories'] ) ) ? 'selected' : '' ?>>
                                                    <?php if( $value['depth'] > 0 ) {

                                                        for( $var = 0; $var < $value['depth']; $var++ ) {
                                                            echo '&nbsp;';
                                                        }

                                                        echo '&mdash;';
                                                    }
                                                    echo ' ' . $value['category_name'] . '( ID #' . $key . ')'; ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="upload_files_auto_upload<?php echo $item_number ?>"><?php _e( 'Auto-Upload After Select', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][upload_files][auto_upload]" id="upload_files_auto_upload<?php echo $item_number ?>">
                                        <option value="yes" <?php echo ( isset( $hub_settings['auto_upload'] ) && 'yes' == $hub_settings['auto_upload'] ) ? 'selected' : '' ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php echo ( !isset( $hub_settings['auto_upload'] ) || 'no' == $hub_settings['auto_upload'] ) ? 'selected' : '' ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="upload_files_include_<?php echo $item_number ?>"><?php _e( 'Include Filetypes',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <input type="text" name="hub_settings[<?php echo $item_number ?>][upload_files][include]" id="upload_files_include_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['include'] ) ) ? $hub_settings['include'] : '' ?>">
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="upload_files_exclude_<?php echo $item_number ?>"><?php _e( 'Exclude Filetypes',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <input type="text" name="hub_settings[<?php echo $item_number ?>][upload_files][exclude]" id="upload_files_exclude_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['exclude'] ) ) ? $hub_settings['exclude'] : '' ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_upload_files( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();

            $temp_arr['menu_items']['upload_files'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if ( isset( $hub_settings['categories'] ) && is_array( $hub_settings['categories'] ) && 0 < count ( $hub_settings['categories'] ) ) {
                $attrs .= ' categories="' . implode( ',', $hub_settings['categories'] ) . '" ';
            } else {
                $attrs .= ' categories="" ';
            }

            if ( isset( $hub_settings['auto_upload'] ) && '' != $hub_settings['auto_upload'] ) {
                $attrs .= ' auto_upload="' . $hub_settings['auto_upload'] . '" ';
            } else {
                $attrs .= ' auto_upload="" ';
            }

            if ( isset( $hub_settings['exclude'] ) && '' != $hub_settings['exclude'] ) {
                $attrs .= ' exclude="' . $hub_settings['exclude'] . '" ';
            } else {
                $attrs .= ' exclude="" ';
            }

            if ( isset( $hub_settings['include'] ) && '' != $hub_settings['include'] ) {
                $attrs .= ' include="' . $hub_settings['include'] . '" ';
            } else {
                $attrs .= ' include="" ';
            }

            $temp_arr['page_body'] = '[wpc_client_uploadf ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * Add ez hub settings - private_messages
        */
        function add_ez_hub_settings_private_messages( $return, $hub_settings = array(), $item_number = 0, $type = 'ez' ) {

            $title = '<span class="wpc_pro_settings_link">' . __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ) . ' <span>Pro</span></span>';
            $text_copy = '{private_messages_' . $item_number . '}' ;

            ob_start();
            ?>

               <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( isset( $type ) && 'ez' == $type ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label for="private_messages_text_<?php echo $item_number ?>"><?php _e( 'Text: "Private Messages"',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <input type="text" name="hub_settings[<?php echo $item_number ?>][private_messages][text]" id="private_messages_text_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : __( ' Private Messages', WPC_CLIENT_TEXT_DOMAIN ) ?>">
                                    </td>
                                </tr>
                            <?php } else { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td style="width:250px;">
                                    <label for="private_messages_show_number_<?php echo $item_number ?>"><?php _e( 'Start Show Number of Messages',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <input type="text" name="hub_settings[<?php echo $item_number ?>][private_messages][show_number]" id="private_messages_show_number_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['show_number'] ) ) ? $hub_settings['show_number'] : 10 ?>">
                                </td>
                            </tr>
                            <tr>
                                <td style="width:250px;">
                                    <label for="private_messages_show_more_number_<?php echo $item_number ?>"><?php _e( 'Show More Number of Messages',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <input type="text" name="hub_settings[<?php echo $item_number ?>][private_messages][show_more_number]" id="private_messages_show_more_number_<?php echo $item_number ?>" style="width: 300px;" value="<?php echo ( isset( $hub_settings['show_more_number'] ) ) ? $hub_settings['show_more_number'] : 10 ?>">
                                </td>
                            </tr>

                            <tr>
                                <td style="width:250px;">
                                    <label for="private_messages_show_filters_<?php echo $item_number ?>"><?php _e( 'Show Filters',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                </td>
                                <td>
                                    <select name="hub_settings[<?php echo $item_number ?>][private_messages][show_filters]" id="private_messages_show_filters_<?php echo $item_number ?>">
                                        <option value="yes" <?php if( isset( $hub_settings['show_filters'] ) && 'yes' == $hub_settings['show_filters'] ) { ?> selected="selected" <?php } ?>><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                        <option value="no" <?php if( ( isset( $hub_settings['show_filters'] ) && 'no' == $hub_settings['show_filters'] ) || !isset( $hub_settings['show_filters'] ) ) { ?> selected="selected" <?php } ?>><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


        /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_private_messages( $tabs_items, $hub_settings = array() ) {

            $temp_arr = array();

            $temp_arr['menu_items']['private_messages'] = ( isset( $hub_settings['text'] ) ) ? $hub_settings['text'] : '';

            $attrs = '';

            if( isset( $hub_settings['show_number'] ) && !empty( $hub_settings['show_number'] ) ) {
                $attrs .= ' show_number="' . $hub_settings['show_number'] . '"';
            }

            if( isset( $hub_settings['show_more_number'] ) && !empty( $hub_settings['show_more_number'] ) ) {
                $attrs .= ' show_more_number="' . $hub_settings['show_more_number'] . '"';
            }

            if( isset( $hub_settings['show_filters'] ) && 'yes' == $hub_settings['show_filters'] ) {
                $attrs .= ' show_filters="' . $hub_settings['show_filters'] . '"';
            }

            $temp_arr['page_body'] = '[wpc_client_com ' . $attrs . ' /]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * Add ez hub settings - logout_link
        */
        function add_ez_hub_settings_logout_link( $return, $hub_settings = array(), $item_number = 0, $type = 'ez') {

            $title = __( 'Logout Link', WPC_CLIENT_TEXT_DOMAIN );
            $text_copy = '{logout_link_' . $item_number . '}' ;

            ob_start();
            ?>

                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php if( !( isset( $type ) && 'ez' == $type ) ) { ?>
                                <tr>
                                    <td style="width:250px;">
                                        <label><?php _e( 'Placeholder',WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                                    </td>
                                    <td>
                                        <?php echo $text_copy ?><a class="wpc_shortcode_clip_button" href="javascript:void(0);" title="<?php _e( 'Click to copy', WPC_CLIENT_TEXT_DOMAIN ) ?>" data-clipboard-text="<?php echo $text_copy ?>"><img src="<?php echo $this->plugin_url . "images/zero_copy.png"; ?>" border="0" width="16" height="16" alt="copy_button.png (3 687 bytes)"></a><br><span class="wpc_complete_copy"><?php _e( 'Placeholder was copied', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                                        <input type="hidden" name="hub_settings[<?php echo $item_number ?>][logout_link][hidden]">
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
        <?php
            $content = ob_get_contents();
            ob_end_clean();

            return array( 'title' => $title, 'content' => $content, 'text_copy' => $text_copy );
        }


         /*
        * Add ez hub shortcode
        */
        function get_ez_shortcode_logout_link( $tabs_items, $hub_settings = array() ) {
            $attrs = '';
            //private messages
            $temp_arr = array();

            $temp_arr['page_body'] = '[wpc_client_logoutb ' . $attrs . '/]';

            $tabs_items[] = $temp_arr;

            return $tabs_items;
        }


        /*
        * get clients ids from popups
        */
        function get_clients_from_popups( $clients = '', $circles = '' ) {

            //for clients
            if( 'all' == $clients )     {
                $selected_clients = $this->acc_get_client_ids();
            } elseif ( '' == $clients ) {
                $selected_clients = array();
            } else {
                $selected_clients = explode( ',', $clients );
            }

            //for circle
            if( 'all' == $circles ) {
                $selected_circles = $this->cc_get_group_ids();
            } elseif ( '' == $circles ) {
                $selected_circles = array();
            } else {
                $selected_circles = explode( ',', $circles );
            }

            //get client from circles
            if ( count( $selected_circles ) ) {
                $clients_from_circles = array();
                foreach ( $selected_circles as $id_group ) {
                    $add_client = $this->cc_get_group_clients_id( $id_group );
                    $clients_from_circles = array_merge( $clients_from_circles, $add_client );
                }
                $clients_from_circles = array_unique( $clients_from_circles );
            }


            if ( isset( $clients_from_circles ) && count( $clients_from_circles )  ) {
                $selected_clients = array_merge( $clients_from_circles, $selected_clients );
            }

            $selected_clients = array_unique( $selected_clients );

            return $selected_clients;
        }


        /*
        *
        */
        function get_plugin_logo_block() {
            $html = '<div class="wpc_logo">' . $this->plugin['logo_content'] . '</div><hr />';

            return $html;
        }

    //end class
    }
}

?>