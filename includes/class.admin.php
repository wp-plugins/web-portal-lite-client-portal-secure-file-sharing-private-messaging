<?php
if ( !class_exists( "WPC_Client_Admin" ) ) {

    class WPC_Client_Admin extends WPC_Client_Admin_Meta_Boxes  {

        var $extension_install_pages = false;
        var $mce_shortcodes = array(
            'general' => array(
                'title' => 'Placeholders: General',
                'items' => array(
                    array(
                        'title' => '{site_title}',
                        'value' => '{site_title}'
                    ),
                    array(
                        'title' => '{contact_name}',
                        'value' => '{contact_name}'
                    ),
                    array(
                        'title' => '{client_business_name}',
                        'value' => '{client_business_name}'
                    ),
                    array(
                        'title' => '{client_name}',
                        'value' => '{client_name}'
                    ),
                    array(
                        'title' => '{client_phone}',
                        'value' => '{client_phone}'
                    ),
                    array(
                        'title' => '{client_email}',
                        'value' => '{client_email}'
                    ),
                    array(
                        'title' => '{client_registration_date}',
                        'value' => '{client_registration_date}'
                    ),
                    array(
                        'title' => '{user_name}',
                        'value' => '{user_name}'
                    ),
                    array(
                        'title' => '{login_url}',
                        'value' => '{login_url}'
                    ),
                    array(
                        'title' => '{logout_url}',
                        'value' => '{logout_url}'
                    ),
                    array(
                        'title' => '{manager_name}',
                        'value' => '{manager_name}'
                    )
                )
            ),
            'business' => array(
                'title' => 'Placeholders: Business',
                'items' => array(
                    array(
                        'title' => '{business_logo_url}',
                        'value' => '{business_logo_url}'
                    ),
                    array(
                        'title' => '{business_name}',
                        'value' => '{business_name}'
                    ),
                    array(
                        'title' => '{business_address}',
                        'value' => '{business_address}'
                    ),
                    array(
                        'title' => '{business_mailing_address}',
                        'value' => '{business_mailing_address}'
                    ),
                    array(
                        'title' => '{business_website}',
                        'value' => '{business_website}'
                    ),
                    array(
                        'title' => '{business_email}',
                        'value' => '{business_email}'
                    ),
                    array(
                        'title' => '{business_phone}',
                        'value' => '{business_phone}'
                    ),
                    array(
                        'title' => '{business_fax}',
                        'value' => '{business_fax}'
                    )
                )
            ),

        );

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->common_construct();
            $this->admin_common_construct();
            $this->menu_construct();
            $this->meta_construct();

            register_activation_hook( $this->plugin_dir . 'web-portal-lite-client-portal-secure-file-sharing-private-messaging.php', array( &$this, 'activation' ), 100 );

            add_action( 'admin_enqueue_scripts', array( &$this, 'include_css_js' ), 99 );

            add_action( 'admin_head', array( &$this, 'style_for_logo' ) );


            //add uninstall link
            add_filter( 'plugin_action_links_web-portal-lite-client-portal-secure-file-sharing-private-messaging/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php', array( &$this, 'add_action_links' ), 99 );

            add_action( 'admin_init', array( &$this, 'request_action' ) );
            add_action( 'init', array( &$this, 'parent_page_func' ) );

            if(isset($_GET['page']) && $_GET['page'] == 'wpclients_templates') {
                add_filter( 'tiny_mce_before_init', array( &$this, 'remove_autop_template_content'), 100, 2 );
                add_filter( 'teeny_mce_before_init', array( &$this, 'remove_autop_template_content'), 100, 2 );
                add_filter( 'the_editor_content', array( &$this, 'filter_template_content'), 9 );
            }

            add_action( 'admin_init', array( &$this, 'add_mce_button_shortcodes' ), 99 );

            //change view link for HUB post type table
            add_action( 'manage_hubpage_posts_custom_column', array( &$this, 'custom_hubpage_columns' ), 2 );
            add_action( 'manage_edit-hubpage_columns', array( &$this, 'hub_columns' ), 2 );

            add_filter( 'manage_edit-clientspage_columns', array( &$this, 'portalpage_columns' ) );
            add_action( 'manage_clientspage_posts_custom_column', array( &$this, 'custom_portalpage_columns' ) );


            add_filter( 'get_sample_permalink_html',  array( &$this, 'hub_edit_sample_permalink_html' ), 99, 4 );

            add_action( 'delete_user', array( &$this, 'delete_client' ) );

            //add_action( 'load-edit.php', array( &$this, 'add_buttons_to_head' ) );
            add_action( 'in_admin_footer', array( &$this, 'add_buttons_to_head' ) );

            //hide buttons add new HUB\Portal pages
            add_action( 'admin_head', array( &$this, 'hide_that_stuff' ) );

            //deny open add new Portal page
            add_action( 'admin_menu', array( &$this, 'permissions_admin_redirect' ) );
            add_action( 'admin_init', array( &$this, 'permissions_show_notice' ) );

            add_action( 'delete_post', array( &$this, 'delete_post' ), 99 );

            add_action( 'restrict_manage_posts', array( &$this, 'add_custom_portal_page_filter' ) );

            add_filter( 'parse_query', array( &$this, 'handler_custom_portal_page_filter' ) );
            add_filter( 'wpc_assign_popup_after_list', array( &$this, 'assign_popup_after_list_content' ), 10, 4 );


            add_action( 'in_admin_header', array( &$this, 'remove_other_notices' ), 10000 );

            add_action( 'wpc_admin_notices', array( &$this, 'admin_notices' ) ) ;


        }


        function remove_other_notices() {
            global $parent_file;
            if ( isset( $parent_file ) && 'wpclients' == $parent_file ) {
                remove_all_actions( 'admin_notices' );
                add_action( 'admin_notices', array( &$this, 'admin_notices_hook' ) );
            } else{
                add_action( 'admin_notices', array( &$this, 'admin_notices_hook_all_pages' ) );
            }
        }

        function admin_notices_hook_all_pages() {
            do_action( 'wpc_admin_notices_all_pages' ) ;
        }

        function admin_notices_hook() {
            do_action( 'wpc_admin_notices' ) ;
        }

        function assign_popup_after_list_content( $html, $type, $current_page, $args ) {
            return '';
        }


        function add_custom_portal_page_filter() {
            global $wpdb;

            $type = 'post';
            if ( isset( $_GET['post_type'] ) ) {
                $type = $_GET['post_type'];
            }

            //only add filter to post type you want
            if ('clientspage' == $type){ ?>
                <select name="wpc_pp_category">
                <option value=""><?php _e( 'View all categories ', WPC_CLIENT_TEXT_DOMAIN ); ?></option>
                </select>
            <?php }
        }


        /**
         * if submitted filter by post meta
         *
         * make sure to change META_KEY to the actual meta key
         * and POST_TYPE to the name of your custom post type
         * @author Ohad Raz
         * @param  (wp_query object) $query
         *
         * @return Void
         */
        function handler_custom_portal_page_filter( $query ){
            global $pagenow;
            $type = 'post';

            if( isset( $_GET['post_type'] ) ) {
                $type = $_GET['post_type'];
            }

            if( 'clientspage' == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET['wpc_pp_category'] ) && $_GET['wpc_pp_category'] != '' ) {
                $query->query_vars['meta_key'] = '_wpc_category_id';
                $query->query_vars['meta_value'] = $_GET['wpc_pp_category'];
            }
        }



        function delete_post( $post_id ) {
            $post = get_post( $post_id );
            if ( 'clientspage' == $post->post_type ) {

                $this->cc_delete_all_object_assigns( 'portal_page', $post_id );

            }
        }


        function add_buttons_to_head() {
            global $current_user;

            if( isset( $_GET['post_type'] ) && 'clientspage' == $_GET['post_type'] && !isset( $_GET['pre'] ) ) {
                if( current_user_can( 'administrator' ) ) {
                    $add_button = '<a href="admin.php?page=add_client_page" class="add-new-h2">Add New</a>&nbsp<a href="admin.php?page=wpclients_pro_features#portal_page_categories" class="add-new-h2 wpc_pro_portal_category_link">Categories <span>Pro</span></a>';
                } else {
                    $add_button = '';
                }
                echo "<script type='text/javascript'>
                    jQuery( document ).ready( function() {
                        jQuery('div.wrap').find( 'h2' ).append('" . $add_button . "');

                        //jQuery('span.view a').attr('target','_blank');
                    });
                </script>";
            }

        }


        /**
        * Function to archive wpc-client
        *
        * @param int $user_id id of archiving user.
        */
        function archive_client( $user_id ) {
            global $wpdb;
            $user = get_userdata( $user_id );
            if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
                update_user_meta( $user_id, 'archive', '1' );
            }
        }


        /**
        * Function to restore wpc-client
        *
        * @param int $user_id id of restoring user.
        */
        function restore_client( $user_id ) {
            $user = get_userdata( $user_id );
            if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
                delete_user_meta( $user_id, 'archive' );
            }
        }


        /**
        * Function to delete wpc-client
        *
        * @param int $user_id id of deleting user.
        */
        function delete_client( $user_id ) {
            global $wpdb;
            $user = get_userdata( $user_id );
            if ( isset( $user ) && in_array ( 'wpc_client', $user->roles ) ) {
                $user_data  = get_userdata( $user_id );
                //delete redirect rules for client
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value=%s", $user_data->user_login ) );

                //delete client from Client Circle
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id=%d ", $user_id ) );

                //delete client from Payment History
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_payments WHERE client_id=%d ", $user_id ) );

                //delete client from Login Logs
                $isset_table = $wpdb->get_results( "SELECT * FROM information_schema.tables WHERE table_name = '{$wpdb->prefix}wpc_client_login_logs' LIMIT 1" );
                if ( 0 < count( $isset_table ) )
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_login_logs WHERE user_id=%d ", $user_id ) );

                //delete HUB
                $hub_page_id = get_user_meta( $user_id, 'wpc_cl_hubpage_id', true );
                if ( 0 < $hub_page_id ) {
                    wp_delete_post( $hub_page_id );
                }


                //delete options in `usermeta`
                $wpdb->delete( "{$wpdb->prefix}usermeta", array( 'user_id' => $user_id ) );

                /*our_hook_
                hook_name: wpc_client_delete_client
                hook_title: Delete Client
                hook_description: Hook runs when Client account is deleted.
                hook_type: action
                hook_in: wp-client
                hook_location class.admin.php
                hook_param: int $client_id
                hook_since: 3.5.9
                */
                //action delete client
                do_action( 'wpc_client_delete_client', $user_id );

                //for delete assigns
                $this->cc_delete_all_assign_assigns( 'client', $user_id );

            }

        }


        /*
        *  Preview link on edit HUB page
        */
        function hub_edit_sample_permalink_html( $return, $id, $new_title, $new_slug ) {
            $post = get_post( $id );
            if ( $post && 'hubpage' == $post->post_type ) {
                $return = '<strong>' . __( 'Permalink:' ) . '</strong> ' . '<span id="sample-permalink" tabindex="-1">' . $this->cc_get_slug( 'hub_page_id' ) . '</span>';

                //make link
                if ( $this->permalinks ) {
                    $hub_preview_url = $this->cc_get_slug( 'hub_page_id' ) . $post->ID;
                } else {
                    $hub_preview_url = add_query_arg( array( 'wpc_page' => 'hub_preview', 'wpc_page_value' => $post->ID ), $this->cc_get_slug( 'hub_page_id', false ) );
                }

                $return .= ' <span id="view-post-btn"><a href="'. $hub_preview_url .'" target="_blank" class="button button-small">Preview</a></span>';
            }
            return $return;
        }


        /*
        *  Add new columns to HUB post type
        */
        function hub_columns( $columns ) {
            $columns['hub_title'] = __('HUB Title', WPC_CLIENT_TEXT_DOMAIN );
            $columns['client'] = $this->custom_titles['client']['s'];

            unset( $columns['title'] );
            unset( $columns['date'] );

            $columns['date'] = 'Date';

            return $columns;
        }


        /*
        * HUB page columns content
        */
        function custom_hubpage_columns( $column ) {
            global $post;

            switch ( $column ) {
                case "hub_title" :
                    $edit_link = get_edit_post_link( $post->ID );
                    $title = _draft_or_post_title();
                    $post_type_object = get_post_type_object( $post->post_type );
                    $can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

                    echo '<strong><a class="row-title" href="'.$edit_link.'">' . $title.'</a>';

                    _post_states( $post );

                    echo '</strong>';

                    if ( $post->post_parent > 0 )
                        echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link($post->post_parent) .'">'. get_the_title($post->post_parent) .'</a>';

                    // Excerpt view
                    if (isset($_GET['mode']) && $_GET['mode']=='excerpt') echo apply_filters('the_excerpt', $post->post_excerpt);

                    // Get actions
                    $actions = array();
                    $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';

                    if ( $can_edit_post && 'trash' != $post->post_status ) {
                        $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline', WPC_CLIENT_TEXT_DOMAIN ) ) . '">' . __( 'Quick&nbsp;Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    }

                    if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
                        if ( 'trash' == $post->post_status )
                            $actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                        elseif ( EMPTY_TRASH_DAYS )
                            $actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                        if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
                            $actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                    }
                    if ( $post_type_object->public ) {
                        if ( 'trash' != $post->post_status ) {
                            //make link
                            if ( $this->permalinks ) {
                                $hub_preview_url = $this->cc_get_slug( 'hub_page_id' ) . $post->ID;
                            } else {
                                $hub_preview_url = add_query_arg( array( 'wpc_page' => 'hub_preview', 'wpc_page_value' => $post->ID ), $this->cc_get_slug( 'hub_page_id', false ) );
                            }
                            $actions['view'] = '<a href="' . $hub_preview_url . '" target="_blank" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', WPC_CLIENT_TEXT_DOMAIN ), $title ) ) . '" rel="permalink">' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                        }
                    }
                    $actions = apply_filters( 'post_row_actions', $actions, $post );

                    echo '<div class="row-actions">';

                    $i = 0;
                    $action_count = sizeof($actions);

                    foreach ( $actions as $action => $link ) {
                        ++$i;
                        ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                        echo "<span class='$action'>$link$sep</span>";
                    }
                    echo '</div>';

                    get_inline_data( $post );

                break;

                case "client":
                    $client = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'wpc_cl_hubpage_id', 'meta_value' => $post->ID ) );

                    if ( $client ) {
                        echo $client[0]->user_login;
                    }

                break;

            }
        }


        /*
        *  Add new columns to clientspage Posttype
        */
        function portalpage_columns( $columns ) {
            $columns['portal_title'] = __( 'Title', WPC_CLIENT_TEXT_DOMAIN );
            $columns['clients']  = $this->custom_titles['client']['p'] ;
            $columns['groups']   = $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'];
            $columns['category'] = __( 'Category', WPC_CLIENT_TEXT_DOMAIN );
            $columns['order']    = __( 'Order', WPC_CLIENT_TEXT_DOMAIN );

            unset( $columns['title'] );
            ob_start();
            ?>
            <script type='text/javascript'>
            function update_order( post_id ) {
                clientpage_order = jQuery( '#clientpage_order_' + post_id ).val();
                jQuery( '#order_' + post_id ).css( 'display', 'inline-block' );
                jQuery.ajax({
                    type: 'POST',
                    dataType    : 'json',
                    url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                    data: 'action=wpc_portal_pages_update_order&post_id='+post_id+'&clientpage_order='+clientpage_order,
                    success: function( json_data ){
                                jQuery( '#order_' + post_id ).css( 'display', 'none' );
                                jQuery( '#clientpage_order_' + post_id ).val( json_data.my_value );
                            }
                 });
            }
            </script>
            <?php
            $out = ob_get_contents();

            if( ob_get_length() ) {
                ob_end_clean();
            }
            echo $out;
            // url: " echo get_admin_url().'admin-ajax.php' ",

            unset( $columns['date']);
            $columns['date']    = 'Date';

            return $columns;
        }



        /*
        *  Add values for new columns of clientspage Posttype
        */
        function custom_portalpage_columns( $column_name ) {
            global $post, $wpdb;
            $current_page = 'wpclients_portal_pages';
            if ( $column_name == 'clients' ) {
                $users = $this->cc_get_assign_data_by_object('portal_page', $post->ID, 'client' );
                ?>
                <script type="text/javascript">
                    var site_url = '<?php echo site_url();?>';
                </script>

                            <div class="scroll_data">
                            <?php
                            $link_array = array(
                                'data-id' => $post->ID,
                                'data-ajax' => 1,
                                'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_clients_ajax[]',
                                'id'    => 'wpc_clients_' . $post->ID,
                                'value' => implode( ',', $users )
                            );
                            $additional_array = array(
                                'counter_value' => count( $users )
                            );

                            $this->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                            </div>
                <?php

            }

            if ( $column_name == 'groups' ) {
                    echo '<div class="scroll_data">';

                    $id_array = $this->cc_get_assign_data_by_object( 'portal_page', $post->ID, 'circle' );

                    $link_array = array(
                        'data-id' => $post->ID,
                        'data-ajax' => 1,
                        'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] )
                    );
                    $input_array = array(
                        'name'  => 'wpc_circles_ajax[]',
                        'id'    => 'wpc_circles_' . $post->ID,
                        'value' => implode( ',', $id_array )
                    );
                    $additional_array = array(
                        'counter_value' => count( $id_array )
                    );

                    $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                    echo '</div>';
            }

            if ( $column_name == 'order' ) {
                $order = get_post_meta( $post->ID, '_wpc_order_id', true );
                if ( isset( $order ) ) {
                    echo '<div class="scroll_data">';
                    echo '<input type="number" name="clientpage_order_' . $post->ID . '" id="clientpage_order_' . $post->ID . '" style="width: 70px;" value="' . $order . '" onblur="update_order(' . $post->ID . ')" />' ;
                    echo '<span class="wpc_ajax_loading" style="display:none" id="order_' . $post->ID . '"></span>' ;
                    echo '</div>' ;
                }
            }

            if( $column_name == 'portal_title' ) {
                $edit_link = get_edit_post_link( $post->ID );
                $title = _draft_or_post_title();
                $post_type_object = get_post_type_object( $post->post_type );
                $can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

                echo '<strong><a class="row-title" href="'.$edit_link.'">' . $title.'</a>';

                _post_states( $post );

                echo '</strong>';

                if ( $post->post_parent > 0 )
                    echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link($post->post_parent) .'">'. get_the_title($post->post_parent) .'</a>';

                // Excerpt view
                if (isset($_GET['mode']) && $_GET['mode']=='excerpt') echo apply_filters('the_excerpt', $post->post_excerpt);

                // Get actions
                $actions = array();

                $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';

                if ( $can_edit_post && 'trash' != $post->post_status ) {
                    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline', WPC_CLIENT_TEXT_DOMAIN ) ) . '">' . __( 'Quick&nbsp;Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                }
                if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
                    if ( 'trash' == $post->post_status )
                        $actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                    elseif ( EMPTY_TRASH_DAYS )
                        $actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                    if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
                        $actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', WPC_CLIENT_TEXT_DOMAIN ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN ) . "</a>";
                }
                if ( $post_type_object->public ) {
                    if ( 'trash' != $post->post_status ) {
                        //make link
                        if ( $this->permalinks ) {
                            $portal_page_preview_url = $this->cc_get_slug( 'portal_page_id' ) . $post->post_name;
                        } else {
                            $portal_page_preview_url = add_query_arg( array( 'wpc_page' => 'portal_page', 'wpc_page_value' => $post->post_name ), $this->cc_get_slug( 'portal_page_id', false ) );
                        }
                        $actions['view'] = '<a href="' . $portal_page_preview_url . '" target="_blank" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', WPC_CLIENT_TEXT_DOMAIN ), $title ) ) . '" rel="permalink">' . __( 'Preview', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
                    }
                }
                $actions = apply_filters( 'post_row_actions', $actions, $post );

                echo '<div class="row-actions">';

                $i = 0;
                $action_count = sizeof($actions);

                foreach ( $actions as $action => $link ) {
                    ++$i;
                    ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                    echo "<span class='$action'>$link$sep</span>";
                }
                echo '</div>';

                get_inline_data( $post );
            }

        }


        /*
        *
        */
        function style_for_logo() {
        ?>

        <style type="text/css">
            <?php echo $this->plugin['logo_style'] ?>
        </style>

        <style type="text/css">
            span.mce_wpc_client_button_shortcodes {
                background-image: url("<?php echo $this->plugin['icon_url'] ?>") !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
            }

            .mce-i-wpc_client_button_shortcodes {
                background-image: url("<?php echo $this->plugin_url ?>/images/mce_icon_v4.png") !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
            }
        </style>

        <?php
        }



        /*
        * Show admin notices
        */
        function admin_notices() {

            if ( current_user_can( 'administrator' ) ) {

                $wpc_client_flags = $this->cc_get_settings( 'client_flags' );

                if ( ( !isset( $wpc_client_flags['skip_install_pages'] ) || !$wpc_client_flags['skip_install_pages'] )
                    && ( '' == $this->cc_get_slug( 'hub_page_id' ) || '' == $this->cc_get_slug( 'payment_process_page_id' ) )
                    && !isset( $_GET['install_pages'] )
                    && !isset( $_GET['skip_install_pages'] ) ) {

                    $messages = array(
                        'main_text'         => sprintf( __( "<strong>Welcome to %s</strong> - Plugin almost ready to start.", WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                        'install_href'      => add_query_arg( 'install_pages', 'true', admin_url( 'admin.php?page=wpclients_settings&tab=pages' ) ),
                        'button_install'    => sprintf( __( 'Install %s Pages', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                        'skip_href'         => add_query_arg( 'skip_install_pages', 'true', admin_url( 'admin.php?page=wpclients_settings&tab=pages' ) ),
                        'button_skip'       => __( 'Skip Install', WPC_CLIENT_TEXT_DOMAIN ),
                        'tip'               => sprintf( __( "When you click 'Install %s Pages', the plugin will automatically create the necessary pages for the plugin's operation & populate those pages with the correct shortcodes. This is the default configuration, but can be changed later. Advanced & experienced admins can also choose to skip this default configuration process & manually build your portal by creating / assigning pages and then adding the appropriate shortcode.", WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                    );
                    wp_enqueue_script( 'jquery-ui-tooltip', false, array(), false, true );
                } elseif ( $this->extension_install_pages ) {
                    $messages = array(
                        'main_text'         => sprintf( __( "<strong>Welcome to %s</strong> -  Extension(s) almost ready to start.", WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                        'install_href'      => add_query_arg( 'install_pages', 'true', admin_url( 'admin.php?page=wpclients_settings&tab=pages' ) ),
                        'button_install'    => sprintf( __( 'Install %s Pages', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                        'skip_href'         => add_query_arg( array( 'skip_install_pages' => 'true', 'pages' => 'invoicing' ), admin_url( 'admin.php?page=wpclients_settings&tab=pages' ) ),
                        'button_skip'       => __( 'Skip Install', WPC_CLIENT_TEXT_DOMAIN ),
                        'tip'               => __( "When you click 'Install Pages', the plugin will automatically create the necessary pages for the plugin's operation & populate those pages with the correct shortcodes. This is the default configuration, but can be changed later. Advanced & experienced admins can also choose to skip this default configuration process & manually build your portal by creating / assigning pages and then adding the appropriate shortcode.", WPC_CLIENT_TEXT_DOMAIN ),
                    );
                    wp_enqueue_script( 'jquery-ui-tooltip', false, array(), false, true );
                }


                if ( isset( $messages ) ) {
                    ?>
                    <div id="message" class="updated wpc_notice">
                        <div class="squeezer">
                            <p>
                                <span style="font-weight: bold; margin: 0px 15px 0px 0px;" ><?php echo $messages['main_text'] ?></span>

                                <a href="<?php echo $messages['install_href'] ?>" class="button-primary">
                                <?php echo $messages['button_install'] ?>
                                </a>
                                <a class="skip button-primary" href="<?php echo $messages['skip_href'] ?>">
                                <?php echo $messages['button_skip'] ?>
                                </a>

                                <span style="padding: 15px 0 0 30px; vertical-align: bottom;" >
                                    <?php echo $this->tooltip( $messages['tip'] ) ?>
                                </span>
                            </p>
                        </div>

                    </div>
                    <style>

                        .ui-priority-secondary,
                        .ui-widget-content .ui-priority-secondary,
                        .ui-widget-header .ui-priority-secondary {
                            opacity: 0;
                            filter:Alpha(Opacity=0);
                            font-weight: normal;
                        }
                        .ui-state-disabled,
                        .ui-widget-content .ui-state-disabled,
                        .ui-widget-header .ui-state-disabled {
                            opacity: .0;
                            filter:Alpha(Opacity=0);
                            background-image: none;
                        }
                        .ui-state-disabled .ui-icon {
                            filter:Alpha(Opacity=0); /* For IE8 - See #6059 */
                        }

                    </style>
                    <?php
                }

            }


        }


        /*
        * Add MCE button for plugin's shortcodes
        */
        function add_mce_button_shortcodes() {
            if ( current_user_can( 'administrator' ) && 'true' == get_user_option( 'rich_editing' ) ) {
                add_filter( 'mce_external_plugins', array( &$this, 'create_mce_button_shortcodes' ) );
                add_filter( 'mce_buttons', array( &$this, 'register_mce_button_shortcodes' ) );
            }
        }


        /*
        * Create MCE button for plugin's shortcodes
        */
        function create_mce_button_shortcodes( $plugin_array ) {
            global $wp_version;

            if( version_compare( $wp_version, '3.9', '>=' ) ) {
                $plugin_array['WPC_Client_Shortcodes'] = $this->plugin_url . 'js/mce_shortcodes_v4.js';
            } else {
                $plugin_array['WPC_Client_Shortcodes'] = $this->plugin_url . 'js/mce_shortcodes.js';
            }
            return $plugin_array;
        }


        /*
        * Register MCE button for plugin's shortcodes
        */
        function register_mce_button_shortcodes( $buttons ) {
            array_push( $buttons, '|', 'wpc_client_button_shortcodes' );
            return $buttons;
        }


        function parent_page_func() {
            global $current_user;

            $sender_name    = get_option("sender_name");
            $sender_email   = get_option("sender_email");

            if(empty($sender_name)) {
                update_option("sender_name", get_bloginfo('name'));
            }

            if(empty($sender_email)) {
                update_option("sender_email", get_bloginfo("admin_email"));
            }

        }


        /*
        * Function for actions
        */
        function request_action() {
            //skip this function for AJAX
            if ( defined( 'DOING_AJAX' ) )
                return '';

            //hide dashbord/backend - redirect Client and Staff to my-hub page
            if ( current_user_can( 'wpc_client'  ) && !current_user_can( 'manage_network_options' ) )  {
                $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );
                //hide dashbord/backend
                if ( isset( $wpc_clients_staff['hide_dashboard'] ) && 'yes' == $wpc_clients_staff['hide_dashboard'] ) {
                    wp_redirect( $this->cc_get_slug( 'hub_page_id' ) );
                    exit();
                }
            }

            //check admin capability and add if admin haven't
            if ( current_user_can( 'manage_options' ) && !current_user_can( 'manage_network_options' ) && !( current_user_can( 'edit_clientpages' ) || current_user_can( 'edit_hubpages' ) ) )  {
                global $wp_roles;

                $capability_map = array(
                    'read_clientpages'               => true,
                    'publish_clientpages'            => true,
                    'edit_clientpages'               => true,
                    'edit_published_clientpages'     => true,
                    'edit_hubpages'                  => true,
                    'edit_published_hubpages'        => true,
                    'delete_hubpages'                => true,
                    'delete_published_clientpages'   => true,
                    'edit_others_clientpages'        => true,
                    'delete_others_clientpages'      => true,
                    'edit_others_hubpages'           => true,
                    'delete_others_hubpages'         => true
                );

                //set capability for Portal Pages to Admin
                foreach ( array_keys( $capability_map ) as $capability ) {
                    $wp_roles->add_cap( 'administrator', $capability );
                }
            }


            //Uninstall plugin - delete all plugin data
            if ( isset( $_GET['action'] ) && 'wpclient_uninstall' == $_GET['action'] ) {
                define( 'WP_UNINSTALL_PLUGIN', '1' );

                //deactivate the plugin
                $plugins = get_option( 'active_plugins' );
                if ( is_array( $plugins ) && 0 < count( $plugins ) ) {
                    $new_plugins = array();
                    foreach( $plugins as $plugin )
                        if ( 'web-portal-lite-client-portal-secure-file-sharing-private-messaging/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php' != $plugin )
                            $new_plugins[] = $plugin;
                }
                update_option( 'active_plugins', $new_plugins );

                //uninstall
                include 'wp-client-uninstall.php';

                wp_redirect( get_admin_url() . 'plugins.php' );
                exit;
            }

            //private actions of the plugin
            if ( isset( $_REQUEST['wpc_action'] ) && ( current_user_can( 'administrator' ) || current_user_can( 'manage_network_options' ) ) ) {
                switch( $_REQUEST['wpc_action'] ) {
                    //action for delete Client Circle
                    case 'delete_group':
                        $this->delete_group( $_REQUEST['group_id'] );
                    break;

                    //action for assign clients to Client Circle
                    case 'save_group_clients':
                        $this->assign_clients_group();
                    break;

                    case 'relogin':
                        if( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
                            $id = $_GET['id'];
                        } else {
                            do_action( 'wp_client_redirect', add_query_arg( 'msg', 'id', get_admin_url(). 'admin.php?page=wpclient_clients' ) );
                        }

                        $key = wp_generate_password(20);

                        update_user_meta( get_current_user_id(), 'wpc_client_admin_secure_data', array(
                            'key' => md5( $key ),
                            'end_date' => time() + 1800
                        ) );

                        wp_set_auth_cookie( $id, true );
                        $secure_logged_in_cookie = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
                        setcookie( "wpc_key", $key, time() + 1860, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );

                        do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
                        break;

                }
            }

        }

        function added_role( $role, $wpc_capabilities ) {
            if( is_string( $role ) && '' != $role && isset( $wpc_capabilities[$role] ) ) {

                global $wp_roles;

                switch( $role ) {

                    case 'wpc_client':

                        $default_caps = array(
                            'read'              => true,
                            'upload_files'      => true,
                        );

                        $caps = array_merge( $default_caps, $wpc_capabilities[$role] );

                        //remore role for update capabilities
                        $wp_roles->remove_role( $role, $wpc_capabilities );
                        //add role for manager
                        $wp_roles->add_role( $role, 'WPC Client', $caps );

                        break;

                }
            }

        }


        /**
         * Create/Edit new Client Circle
         **/
        function create_group( $args ) {
            global $wpdb;

            //checking that Client Circle not exist other ID
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT group_id
                FROM {$wpdb->prefix}wpc_client_groups
                WHERE LOWER(group_name) = '%s'",
                strtolower( $args['group_name'] )
            ), ARRAY_A );

            if ( $result ) {
                if ( "0" != $args['group_id'] && $result['group_id'] == $args['group_id'] ) {

                } else {
                    //if Client Circle exist with other ID
                    //wp_redirect( add_query_arg( array( 'page' => 'wpclients_groups', 'updated' => 'true', 'msg' => 'ae' ), 'admin.php' ) );
                    do_action( 'wp_client_redirect', add_query_arg( 'msg', 'ae', get_admin_url(). 'admin.php?page=wpclients_groups' ) );
                    exit;

                }
            }


            if ( '0' != $args['group_id'] ) {
                //update when edit Client Circle
                $result = $wpdb->query( $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}wpc_client_groups SET
                        group_name = '%s',
                        auto_select = '%s',
                        auto_add_files = '%s',
                        auto_add_pps = '%s',
                        auto_add_manual = '%s',
                        auto_add_self = '%s'
                    WHERE group_id = %d",
                        trim( $args['group_name'] ),
                        $args['auto_select'],
                        $args['auto_add_files'],
                        $args['auto_add_pps'],
                        $args['auto_add_manual'],
                        $args['auto_add_self'],
                        $args['group_id'] )
                );
                //wp_redirect( add_query_arg( array( 'page' => 'wpclients_groups', 'updated' => 'true', 'dmsg' => urlencode( __( 'The changes of the group are saved!', WPC_CLIENT_TEXT_DOMAIN )  ) ), 'admin.php' ) );
                //exit;
            } else {
                //create new Client Circle
                $result = $wpdb->query( $wpdb->prepare(
                    "INSERT INTO {$wpdb->prefix}wpc_client_groups
                    SET group_name = '%s',
                        auto_select = '%s',
                        auto_add_files = '%s',
                        auto_add_pps = '%s',
                        auto_add_manual = '%s',
                        auto_add_self = '%s'
                        ",
                    trim( $args['group_name'] ),
                    $args['auto_select'],
                    $args['auto_add_files'],
                    $args['auto_add_pps'],
                    $args['auto_add_manual'],
                    $args['auto_add_self']
                ) );


                if( isset( $args['assign'] ) && !empty( $args['assign'] ) ) {
                    $new_group_id = $wpdb->insert_id;

                    if ( 'all' == $args['assign'] ) {
                        $excluded_clients  = $this->cc_get_excluded_clients();
                        $args = array(
                            'role' => 'wpc_client',
                            'exclude'   => $excluded_clients,
                        );

                        $clients = get_users( $args );

                        if ( is_array( $clients ) && 0 < count( $clients ) ) {
                            foreach ( $clients as $client ) {
                                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $new_group_id,  $client->ID ) );
                            }
                        }
                    } else {

                        $clients = explode( ',', $args['assign'] );
                        if ( is_array( $clients ) && 0 < count( $clients ) ) {
                            foreach ( $clients as $client_id ) {
                                $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $new_group_id,  $client_id ) );
                            }
                        }
                    }
                }


                //wp_redirect( add_query_arg( array( 'page' => 'wpclients_groups', 'updated' => 'true', 'dmsg' => urlencode( __( 'Client Circle is created!', WPC_CLIENT_TEXT_DOMAIN ) ) ), 'admin.php' ) );
                //exit;
            }

        }


        /**
         * Delete Client Circle
         **/
        function delete_group( $group_id ) {
            global $wpdb;
            //delete Client Circle
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_groups WHERE group_id = %d", $group_id ) );

            //delete all clients from Client Circle
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ) );

            //for delete assigns
            $this->cc_delete_all_assign_assigns( 'circle', $group_id );

        }


        /**
         * Assign Clients to Client Circle
         **/
        function assign_clients_group() {
            global $wpdb;

            $group_id           = $_POST['group_id'];
            $group_clients_id   = ( isset( $_POST['group_clients_id'] ) ) ? $_POST['group_clients_id'] : array();

            //delete clients from Client Circle
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ) );


            //Add clients to the Client Circle
            if ( is_array( $group_clients_id ) && 0 < count( $group_clients_id ) )
                foreach ( $group_clients_id as $client_id ) {
                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $client_id ) );
                }

            wp_redirect( add_query_arg( array( 'page' => 'wpclients_groups', 'updated' => 'true', 'dmsg' => urlencode( __( 'Clients are assigned!', WPC_CLIENT_TEXT_DOMAIN ) ) ), 'admin.php' ) );
            exit;
        }

        /**
         * Update rewrite_rules
         */
        function update_rewrite_rules() {
            global $wp_roles;

            //remore old role
            $wp_roles->remove_role( "pcc_client" );

            //get capabilities
            $wpc_capabilities = $this->cc_get_settings( 'capabilities' );


            $wpc_caps['wpc_client'] = array();

            $capabilities_maps = $this->acc_get_capabilities_maps();

            foreach ( $capabilities_maps as $key_role => $capabilities_map ) {
                foreach ( $capabilities_map as $cap_key=>$cap_val ) {
                    $cap = ( isset( $wpc_capabilities[ $key_role ][ $cap_key ] ) && true == $wpc_capabilities[ $key_role ][ $cap_key ] ) ? true : false;
                    $wpc_caps[ $key_role ][ $cap_key ] = $cap;
                }
            }

            $default_caps = array(
                    'read' => true,
                    'upload_files' => true,
            );
            $caps = array_merge( $default_caps, $wpc_caps['wpc_client'] );
            //remore role for update capabilities
            $wp_roles->remove_role( "wpc_client" );
            //add role for clients
            $wp_roles->add_role( "wpc_client", 'WPC Client', $caps );

            $capability_map = array(
                'read_clientpages'               => true,
                'publish_clientpages'            => true,
                'edit_clientpages'               => true,
                'edit_published_clientpages'     => true,
                'delete_published_clientpages'   => true,
                'edit_others_clientpages'        => true,
                'delete_others_clientpages'      => true,

                'read_hubpage'                   => true,
                'read_private_hubpages'          => true,
                'publish_hubpages'               => true,

                'edit_hubpage'                   => true,
                'edit_hubpages'                  => true,
                'edit_published_hubpages'        => true,
                'edit_others_hubpages'           => true,

                'delete_hubpage'                 => true,
                'delete_hubpages'                => true,
                'delete_others_hubpages'         => true,
                'wpc_admin_user_login'           => true
            );

            //set capability for Portal Pages to Admin
            foreach ( array_keys( $capability_map ) as $capability ) {
                $wp_roles->add_cap( 'administrator', $capability );
            }

            //update rewrite rules
            flush_rewrite_rules( false );
        }


        /**
         * Reset plugin data
         */
        function add_action_links( $links ) {
             $links['delete'] = '<a onclick="return confirm(\'' . sprintf( __( 'Are you sure? You will lose all Clients, HUB Pages, %s, Private Messages & Files', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['p'] ) . '\');"  href="' . get_admin_url() . 'plugins.php?action=wpclient_uninstall" class="delete" >Nuclear Option</a>';
             return $links;
        }


        /**
         * Run Activated funtions
         */
        function activation() {
            global $wpdb;

            if ( defined( 'WPC_CLOUDS' ) ) {

                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

                if ( is_array( $blog_ids ) ) {
                    foreach( $blog_ids as $blog_id ) {
                        switch_to_blog( $blog_id );


                        add_option( 'wp_client_lite_ver', WPC_CLIENT_LITE_VER );
                        $ver = get_option( 'wp_client_lite_ver' );

                        //include installation class
                        include_once $this->plugin_dir . 'includes/class.install.php';
                        $wpc_install = new WPC_Client_Install();
                        $wpc_install->creating_db();
                        $wpc_install->default_settings();
                        $wpc_install->default_templates();
                        $wpc_install->updating( $ver );

                        $this->update_rewrite_rules();

                        restore_current_blog();
                    }
                }

            } else {

                add_option( 'wp_client_lite_ver', WPC_CLIENT_LITE_VER );
                $ver = get_option( 'wp_client_lite_ver' );

                add_option( 'wpc_client_sync_key', md5( time() . uniqid() ) );
                //include installation class
                include_once $this->plugin_dir . 'includes/class.install.php';
                $wpc_install = new WPC_Client_Install();
                $wpc_install->creating_db();
                $wpc_install->default_settings();
                $wpc_install->default_templates();
                $wpc_install->updating( $ver );

                $this->update_rewrite_rules();
            }


        }
        /*
        * fix for wpautop in templates
        */
        function remove_autop_template_content( $init, $editor_id = -1 ) {
            $init['apply_source_formatting'] = true;
            //$init['wpautop'] = false;
            $init['remove_linebreaks'] = false;
            return $init;
        }


        /*
        * fix for wpautop in templates
        */
        function filter_template_content( $content ) {
            $content = addslashes( htmlspecialchars( $content, ENT_NOQUOTES ) );
            $func = "return stripslashes('$content');";
            add_filter( 'the_editor_content', create_function( '', $func ), 90 );
            return $content;
        }


        /*
        * Include JS/CSS files
        */
        function include_css_js() {
            global $parent_file;


            wp_register_style( 'web-portal-lite-style', $this->plugin_url . 'css/lite-style.css' );
            wp_enqueue_style( 'web-portal-lite-style' );

            $this->password_protect_css_js();
            //wp_localize_script( 'wpc-pm-mce-shortcodes', 'wpc_pm_mce_shortcodes', $this->mce_shortcodes_array );
            //tooltip just for Install pages links (admin messages box)
            $wpc_client_flags = $this->cc_get_settings( 'client_flags' );


            wp_register_style( 'wp-client-style-for-menu', $this->plugin_url . 'css/style_menu.css' );
            wp_enqueue_style( 'wp-client-style-for-menu', false, array(), false, true );

            if ( 'clientspage' == get_post_type() ) {
                wp_register_style( 'wp-client-style', $this->plugin_url . 'css/style.css' );
                wp_enqueue_style( 'wp-client-style', false, array(), false, true );
                wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                wp_enqueue_style( 'wpc-fancybox-style', false, array(), false, true );
                wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                wp_enqueue_script( 'wpc-fancybox-js', false, array(), false, true );
            }

            if ( 'hubpage' == get_post_type() || 'clientspage' == get_post_type() ) {
                wp_register_style( 'wp-client-style', $this->plugin_url . 'css/style.css' );
                wp_enqueue_style( 'wp-client-style' );
                wp_enqueue_script('jquery');
            }

            if ( isset( $parent_file ) && 'wpclients' == $parent_file ) {
                wp_enqueue_script('jquery');

                global $wp_version;

                wp_register_style( 'wp-client-style', $this->plugin_url . 'css/style.css' );
                wp_enqueue_style( 'wp-client-style' );

                if( version_compare( $wp_version, '3.8', '>=' ) ) {
                    wp_register_style( 'wp-client-additional-style', $this->plugin_url . 'css/additional_style.css' );
                    wp_enqueue_style( 'wp-client-additional-style' );
                }

                wp_enqueue_script( 'jquery-ui-sortable' );

                //vertical tabs
                if ( defined( 'WPC_CLIENT_PAYMENTS' ) && isset( $_GET['tab'] ) && 'gateways' == $_GET['tab'] ) {
                    wp_enqueue_script( 'jquery-ui-tabs' );
                }
            }

            if( 'clientspage' == get_post_type() && isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {

                //remove fancy 1.3.4 from wp-editor plugin
                wp_deregister_script( 'fancybox' );

                wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                wp_enqueue_style( 'wpc-fancybox-style' );
                wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                wp_enqueue_script( 'wpc-fancybox-js' );
            }

            if ( isset( $_GET['page'] ) ) {
                switch( $_GET['page'] ) {
                    case 'wpclients_permissions':
                    {
                        wp_register_style( 'wpc-chosen-style', $this->plugin_url . 'js/chosen/chosen.css' );
                        wp_enqueue_style( 'wpc-chosen-style' );
                        wp_register_script( 'wpc-chosen-js', $this->plugin_url . 'js/chosen/chosen.jquery.min.js' );
                        wp_enqueue_script( 'wpc-chosen-js' );
                    }
                    case 'wpclients_settings':
                    {

                        wp_enqueue_script( 'jquery-ui-tabs' );

                        wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                        wp_enqueue_style( 'wpc-fancybox-style' );
                        wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                        wp_enqueue_script( 'wpc-fancybox-js' );
                        wp_register_style( 'wpc-chosen-style', $this->plugin_url . 'js/chosen/chosen.css' );
                        wp_enqueue_style( 'wpc-chosen-style' );
                        wp_register_script( 'wpc-chosen-js', $this->plugin_url . 'js/chosen/chosen.jquery.min.js' );
                        wp_enqueue_script( 'wpc-chosen-js' );
                        wp_register_style( 'wpc-checkboxes-css', $this->plugin_url . 'js/jquery.ibutton.css' );
                        wp_enqueue_style( 'wpc-checkboxes-css' );
                        wp_register_script( 'wpc-checkboxes-js', $this->plugin_url . 'js/jquery.ibutton.js' );
                        wp_enqueue_script( 'wpc-checkboxes-js' );


                        break;
                    }
                    case 'wpclients_templates':
                    {
                        wp_enqueue_script( 'postbox' );

                        wp_enqueue_script( 'jquery-ui-button' );
                        wp_register_style( 'wpc-ui-style', $this->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css' );
                        wp_enqueue_style( 'wpc-ui-style' );

                        wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                        wp_enqueue_style( 'wpc-fancybox-style' );
                        wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                        wp_enqueue_script( 'wpc-fancybox-js' );
                        wp_register_script( 'wpc-diff-js', $this->plugin_url . 'js/diff_match_patch.js' );
                        wp_enqueue_script( 'wpc-diff-js' );
                        wp_enqueue_script( 'jquery-ui-tabs' );
                        wp_enqueue_script( 'jquery-ui' );
                        wp_enqueue_script( 'jquery-base64', $this->plugin_url . 'js/jquery.b_64.min.js', array( 'jquery' ) );


                        wp_register_script( 'wpc-zeroclipboard-js', $this->plugin_url . 'js/zeroclipboard/zeroclipboard.js' );
                        wp_enqueue_script( 'wpc-zeroclipboard-js' );

                        break;
                    }
                    case 'add_client_page':
                    {
                        wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                        wp_enqueue_style( 'wpc-fancybox-style' );
                        wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                        wp_enqueue_script( 'wpc-fancybox-js' );
                        wp_register_style( 'wpc-chosen-style', $this->plugin_url . 'js/chosen/chosen.css' );
                        wp_enqueue_style( 'wpc-chosen-style' );
                        wp_register_script( 'wpc-chosen-js', $this->plugin_url . 'js/chosen/chosen.jquery.min.js' );
                        wp_enqueue_script( 'wpc-chosen-js' );
                    }

                    case 'wpclients_groups':
                    {

                        wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                        wp_enqueue_style( 'wpc-fancybox-style' );
                        wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                        wp_enqueue_script( 'wpc-fancybox-js' );
                        wp_register_style( 'wpc-chosen-style', $this->plugin_url . 'js/chosen/chosen.css' );
                        wp_enqueue_style( 'wpc-chosen-style' );
                        wp_register_script( 'wpc-chosen-js', $this->plugin_url . 'js/chosen/chosen.jquery.min.js' );
                        wp_enqueue_script( 'wpc-chosen-js' );

                        break;
                    }
                    case 'wpclient_clients':
                    {
                        wp_enqueue_script( 'jquery-base64', $this->plugin_url . 'js/jquery.b_64.min.js', array( 'jquery' ) );

                        wp_register_style( 'wpc-admin-clients-style', $this->plugin_url . 'css/admin/clients.css' );
                        wp_enqueue_style( 'wpc-admin-clients-style' );

                        wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
                        wp_enqueue_style( 'wpc-fancybox-style' );
                        wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
                        wp_enqueue_script( 'wpc-fancybox-js' );
                        break;
                    }

                }
            }


        }

        /*
        * Hide buttons for add new portalpage
        */
        function hide_that_stuff() {
            global $pagenow;

            if ( ( ( $pagenow == 'edit.php' ) && isset( $_REQUEST['post_type'] ) && ( $_REQUEST['post_type'] == 'clientspage' || $_REQUEST['post_type'] ==  'hubpage' ) ) ||
                ( $pagenow == 'post.php' && isset( $_REQUEST['action'] ) && isset( $_REQUEST['post'] ) && $_REQUEST['action'] == 'edit' ) && ( 'clientspage' == get_post_type( $_REQUEST['post'] ) || 'hubpage' == get_post_type( $_REQUEST['post'] ) ) ) {
                echo '<style type="text/css">
                .add-new-h2:first-child {
                    display:none;
                }
                #wp-admin-bar-new-clientspage {display:none;}
                #wp-admin-bar-new-hubpage{display:none;}

                .add-new-button:active {
                    background: #f1f1f1;
                }
                .icon32-posts-clientspage {
                    background-image: url(css/../images/icons32.png?ver=20121105);
                    background-position: -312px -5px;
                }
                .icon32-posts-hubpage {
                    background-image: url(css/../images/icons32.png?ver=20121105);
                    background-position: -312px -5px;
                }
                </style>';
            }
        }


        /*
        * deny add new HUB\Portal page
        */
        function permissions_admin_redirect() {

            if ( stripos( $_SERVER['REQUEST_URI'], 'post-new.php?post_type=clientspage' ) !== false || stripos( $_SERVER['REQUEST_URI'], 'post-new.php?post_type=hubpage' ) !== false ) {
                if ( !isset( $_GET['pre'] ) ) {
                    wp_redirect( get_admin_url() . 'index.php?permissions_error=true' );
                }
            }

        }


        /*
        * Show notice about you cant add new HUB\Portal
        */
        function permissions_show_notice() {
            if ( isset( $_GET['permissions_error'] ) )
                add_action( 'admin_notices_hook_all_pages', array( &$this, 'permissions_admin_notice' ) );
        }


        /*
        * Display notice about you cant add new HUB\Portal
        */
        function permissions_admin_notice() {
            // use the class "error wpc_notice" for red notices, and "update" for yellow notices
            echo "<div id='permissions-warning' class='error wpc_notice fade'><p><strong>" . __( 'You do not have permission to access that page.', WPC_CLIENT_TEXT_DOMAIN ) . "</strong></p></div>";
        }


        /*
        * Render tooltip
        */
        function tooltip( $message ) {

            wp_enqueue_script( 'jquery-ui-tooltip' );

            ob_start();

            ?>

            <a href="javascript:;" class="wpc_tooltip_icon" title="<?php echo $message ?>">
                <img src="<?php echo $this->plugin_url . 'images/icon_q.png' ?>" width="15" height="15"  alt="" />
            </a>

            <script type="text/javascript">
                jQuery( document ).ready( function() {
                    jQuery( '.wpc_tooltip_icon' ).tooltip();
                });
            </script>

            <style>
                .ui-tooltip {
                    padding: 8px;
                    background-color: #fff;
                    position: absolute;
                    z-index: 9999;
                    max-width: 300px;
                    -webkit-box-shadow: 0 0 5px #aaa;
                    box-shadow: 0 0 5px #aaa;
                }

            </style>

            <?php

                $tooltip = ob_get_contents();
            ob_end_clean();

            return $tooltip;
        }


    //end class
    }

    $wpc_client = new WPC_Client_Admin();
}

?>