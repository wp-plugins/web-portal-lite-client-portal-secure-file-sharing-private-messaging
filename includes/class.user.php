<?php

//{{FUNC_NOT_ENC:get_portal_page_link}}

if ( !class_exists( "WPC_Client" ) ) {

    class WPC_Client extends WPC_Client_User_Shortcodes {

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->common_construct();
            $this->shortcodes_construct();

            add_filter ( 'authenticate', array( &$this, 'verification_archive_user' ), 120, 3 );

            //change template
            add_filter( 'page_template', array( &$this, 'get_page_template' ) ) ;

            add_filter( 'posts_request', array( &$this, 'query_for_wpc_client_pages' ) );

            //protect clientpage and hubpage
            add_filter( 'the_posts', array( &$this, 'filter_posts' ), 99 );

            //get template for Portal Page \ HUB
            add_filter( 'template_include',  array( &$this, 'get_clientpage_template' ), 99 );

            add_action( 'init', array( &$this, 'client_login_from_' ) );

            add_filter( 'wp_list_pages_excludes',  array( &$this, 'exclude_portal_page' ),  10, 1 );

            add_action( 'wp_login', array( &$this, 'alert_login_successful') );
            add_action( 'wp_login_failed', array( &$this, 'alert_login_failed') );

            //tocheck
            add_filter( 'wp_nav_menu_args',  array( &$this, 'custom_menu' ),  99 );

            //hub link
            add_filter( 'wp_list_pages', array( &$this, 'add_hub_link_to_menu' ), 1, 2 );
            add_filter( 'wp_nav_menu_items', array( &$this, 'add_hub_link_to_menu' ), 1, 2 );



            add_action( 'wp_enqueue_scripts', array( &$this, 'wp_css_js' ), 99 );
            add_action( 'login_enqueue_scripts', array( &$this, 'password_protect_css_js' ), 99 );

            add_filter( 'body_class', array( &$this, 'body_class_for_clientpages' ), 99 );

            //custom login
            add_action( 'login_head', array( &$this, 'custom_login_bm' ), 99 );
            add_filter( 'login_headerurl', array( &$this, 'custom_login_logo_url' ), 99 );
            add_filter( 'login_headertitle', array( &$this, 'custom_login_logo_title' ), 99 );

            add_action( 'wp_footer', array( &$this, 'translator_js_scripts' ) );


            add_action( 'init', array( &$this, 'checking_for_set_global_vars' ) );

            add_filter( 'post_link', array( &$this, 'get_portal_page_link' ), 1, 3 );
            add_filter( 'page_link', array( &$this, 'get_portal_page_link' ), 1, 3 );

            add_filter( 'sidebars_widgets', array( &$this, 'widget_filter_sidebars_widgets' ), 10);
        }


        // CALLED ON 'sidebars_widgets' FILTER
        function widget_filter_sidebars_widgets( $sidebars_widgets ) {
            global $post;
            if( empty( $post ) || !isset( $post->ID ) ) {
                return $sidebars_widgets;
            }

            $options = get_option( 'wpc_widget_show_settings', array() );
            $wpc_pages = $this->cc_get_settings( 'pages' );

            foreach( $sidebars_widgets as $widget_area => $widget_list ) {
                if ( $widget_area == 'wp_inactive_widgets' || empty( $widget_list ) ) continue;

                foreach($widget_list as $pos => $widget_id) {
                    if ( !isset( $options[ $widget_id ] ) )  continue;

                    $value = $options[ $widget_id ];
                    if ( empty( $value ) )  continue;

                    switch( $value ) {
                        case 'hub':
                            if( $post->ID != $wpc_pages['hub_page_id'] ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                        case 'portal':
                            if( $post->ID != $wpc_pages['portal_page_id'] ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                        case 'hub_portal':
                            if( !( $post->ID == $wpc_pages['hub_page_id'] || $post->ID == $wpc_pages['portal_page_id'] ) ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                        case 'not_hub':
                            if( $post->ID == $wpc_pages['hub_page_id'] ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                        case 'not_portal':
                            if( $post->ID == $wpc_pages['portal_page_id'] ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                        case 'not_hub_portal':
                            if( $post->ID == $wpc_pages['hub_page_id'] || $post->ID == $wpc_pages['portal_page_id'] ) {
                                unset( $sidebars_widgets[ $widget_area ][ $pos ] );
                                continue;
                            }
                            break;
                    }
                }
            }
            return $sidebars_widgets;
        }


        /*
        * Check whether the user is not archive
        */
        function verification_archive_user( $user, $login, $password ) {
            if ( '' == $login && ''  == $password ) {
                return $user;
            } else {
                $client = get_user_by( 'login', $login );
                if ( !isset( $client->ID ) || !is_numeric( $client->ID ) || get_user_meta( $client->ID, 'archive' ) ) {
                    return null;
                } else return $user;
            }
        }


        /*
        * Send alert when login successful
        */
        function get_portal_page_link( $link, $post_id, $leavename ) {

            if ( isset( $this->current_plugin_page['portal_page_id'] ) ) {
                global $wp_query, $post;

                if( is_object( $post ) ) {
                    if( is_numeric( $post_id ) ) {
                        if ( isset( $wp_query->query_vars['wpc_page'] ) && 'portal_page' == $wp_query->query_vars['wpc_page'] ) {
                            if ( isset( $this->current_plugin_page['portal_page_id'] ) && $post_id == $this->current_plugin_page['portal_page_id']  ) {
                                $mypage = get_post( $post_id, 'ARRAY_A' );
                            } elseif( $post_id == $post->ID ) {
                                $mypage = get_post( $this->current_plugin_page['portal_page_id'], 'ARRAY_A' );
                            }

                            if ( isset( $mypage ) ) {
                                //make link
                                if ( $this->permalinks ) {
                                    $portal_page_url = $this->cc_get_slug( 'portal_page_id' ) . $mypage['post_name'];
                                } else {
                                    $portal_page_url = add_query_arg( array( 'wpc_page' => 'portal_page', 'wpc_page_value' => $mypage['post_name'] ), $this->cc_get_slug( 'portal_page_id', false ) );
                                }

                                return $portal_page_url;
                            }

                        }
                    }
                }
            }

            return $link;
        }


        /*
        * Send alert when login successful
        */
        function alert_login_successful( $username ) {

            $wpc_login_alerts = $this->cc_get_settings( 'login_alerts' );

            if ( isset( $wpc_login_alerts['successful'] ) && '1' == $wpc_login_alerts['successful'] ) {

                if ( isset( $wpc_login_alerts['email'] ) && '' != $wpc_login_alerts['email'] ) {

                    $subject    = 'Login Successful';
                    $body       = "
                        User Name: " . $username . "\n
                        Description: Was Logged Successfully\n
                        Alert From: " . get_option( 'siteurl' ) . "\n
                        IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n
                        Date: " . current_time( 'mysql' ) . "\n";
                    wp_mail( $wpc_login_alerts['email'], $subject, $body );
                }
            }

        }


        /*
        * Send alert when login failed
        */
        function alert_login_failed( $username ) {

            $wpc_login_alerts = $this->cc_get_settings( 'login_alerts' );

            if ( isset( $wpc_login_alerts['failed'] ) && '1' == $wpc_login_alerts['failed'] ) {
                if ( isset( $wpc_login_alerts['email'] ) && '' != $wpc_login_alerts['email'] ) {
                    if ( username_exists( $username ) )
                        $status = 'Incorrect Password';
                    else
                        $status = 'Unknown User';

                    $subject    = 'Login Failed';
                    $body       = "
                        User Name: " . $username . "\n
                        Description: " . $status . "\n
                        Alert From: " . get_option( 'siteurl' ) . "\n
                        IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n
                        Date: " . current_time( 'mysql' ) . "\n";
                    wp_mail( $wpc_login_alerts['email'], $subject, $body );
                }
            }
        }


        /*
        * Filter the template path to page{}.php templates.
        */
        function get_page_template( $template ) {
            global $wp_query;

            if ( isset( $wp_query->query_vars['wpc_page'] ) && '' != $wp_query->query_vars['wpc_page'] ) {
                if ( file_exists( get_template_directory() . "/page-wpc_{$wp_query->query_vars['wpc_page']}.php" ) )
                    return get_template_directory() . "/page-wpc_{$wp_query->query_vars['wpc_page']}.php";
            }

            return $template;
        }


        /**
         * Change query for show wpc pages
         **/
        function query_for_wpc_client_pages( $q ) {
            global $wp_query, $wpdb;
            if ( $q == $wp_query->request ) {
                //for portal page
                if ( isset( $wp_query->query_vars['wpc_page'] ) && 'portal_page' == $wp_query->query_vars['wpc_page'] ) {
                    if ( is_user_logged_in() ) {
                        $wpc_pages = $this->cc_get_settings( 'pages' );

                        if ( isset( $wpc_pages['portal_page_id'] ) && 0 < $wpc_pages['portal_page_id'] ) {
                            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['portal_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                        }
                    } else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }

                }
                //for edit portal page
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'edit_portal_page' == $wp_query->query_vars['wpc_page'] ) {
                    if ( is_user_logged_in() ) {

                        $wpc_pages = $this->cc_get_settings( 'pages' );

                        if ( isset( $wpc_pages['edit_portal_page_id'] ) && 0 < $wpc_pages['edit_portal_page_id'] ) {
                            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['edit_portal_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                        }
                    } else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }
                }
                //for verify email
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'acc_activation' == $wp_query->query_vars['wpc_page'] ) {
                    $key = ( isset( $wp_query->query_vars['wpc_page_value'] ) ) ? $wp_query->query_vars['wpc_page_value'] : '';
                    $user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'verify_email_key' AND meta_value = '%s'", $key ) );
                    if ( $user ) {
                        delete_user_meta( $user, 'verify_email_key', $key );
                    }

                    if( is_user_logged_in() ) {
                        wp_redirect( add_query_arg( array( 'msg' => 've' ), $this->cc_get_hub_link( get_current_user_id() ) ) );
                    } else {
                        wp_redirect( $this->cc_get_login_url() );
                    }
                    exit;
                }
                //for HUB previev
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'hub_preview' == $wp_query->query_vars['wpc_page'] ) {
                    if ( is_user_logged_in() ) {
                        if ( current_user_can( 'administrator' ) ) {
                             //for admin
                            $wpc_pages = $this->cc_get_settings( 'pages' );

                            if ( isset( $wpc_pages['hub_page_id'] ) && 0 < $wpc_pages['hub_page_id'] ) {
                                $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['hub_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                            }
                        } else {
                            //for clients
                            wp_redirect( $this->cc_get_slug( 'hub_page_id' ) );
                            exit;
                        }
                    }  else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }
                }
                //for edit staff page
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'edit_staff' == $wp_query->query_vars['wpc_page'] ) {
                    if ( is_user_logged_in() ) {

                    }  else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }
                }
                //for feedback wizard page
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'feedback_wizard' == $wp_query->query_vars['wpc_page'] ) {
                    if ( is_user_logged_in() ) {
                        $wpc_pages = $this->cc_get_settings( 'pages' );

                        if ( isset( $wpc_pages['feedback_wizard_page_id'] ) && 0 < $wpc_pages['feedback_wizard_page_id'] ) {
                            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['feedback_wizard_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                        }
                    }  else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }
                }
                //for invoicing/invoicing payment pages
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && ( 'invoicing' == $wp_query->query_vars['wpc_page'] || 'invoicing_payment' == $wp_query->query_vars['wpc_page'] ) ) {
                    if ( is_user_logged_in() ) {
                        $wpc_pages = $this->cc_get_settings( 'pages' );

                        if ( isset( $wpc_pages['invoicing_page_id'] ) && 0 < $wpc_pages['invoicing_page_id'] ) {
                            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['invoicing_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                        }
                    }  else {
                        wp_redirect( $this->cc_get_login_url() );
                        exit;
                    }
                }
                //for paid registration payment pages
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'paid_registration' == $wp_query->query_vars['wpc_page'] ) {

                    if ( !is_user_logged_in() || get_user_meta( get_current_user_id(), 'wpc_need_pay', true ) ) {
                        $wpc_pages = $this->cc_get_settings( 'pages' );

                        if ( isset( $wpc_pages['client_registration_page_id'] ) && 0 < $wpc_pages['client_registration_page_id'] ) {
                            $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['client_registration_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                        }
                    } else {
                        wp_redirect( get_home_url() );
                        exit;
                    }
                }
                //for payment process pages
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'payment_process' == $wp_query->query_vars['wpc_page'] ) {

                    $wpc_pages = $this->cc_get_settings( 'pages' );

                    if ( isset( $wpc_pages['payment_process_page_id'] ) && 0 < $wpc_pages['payment_process_page_id'] ) {
                        $q = "SELECT * FROM {$wpdb->prefix}posts WHERE 1=1 AND ID = '" . $wpc_pages['payment_process_page_id'] . "' AND post_type = 'page' ORDER BY post_date DESC ";
                    }
                }
                //start IPN
                elseif ( isset( $wp_query->query_vars['wpc_page'] ) && 'payment_ipn' == $wp_query->query_vars['wpc_page'] ) {
                    if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {
                        global $wpc_payments_core;

                        $order = $wpc_payments_core->get_order_by( $wp_query->query_vars['wpc_page_value'], 'order_id' );

                        $wpc_payments_core->handle_ipn( $order, $wp_query->query_vars['wpc_page_value'] );
                    }
                }
            }

            return $q;
        }


        /**
         * Protect Cleint page and HUB from not logged user and Search Engine
         */
        function filter_posts( $posts ) {
            global $wp_query, $wpdb;

            if ( '' == session_id() )
                    session_start();

            $filtered_posts = array();

            //if empty
            if ( empty( $posts ) )
                return $posts;

            $wpc_pages = $this->cc_get_settings( 'pages' );
            $post_ids = array();
            foreach( $posts as $post ) {
                $post_ids[] = $post->ID;
            }

            $sticky_posts_array = array();
            if( ( isset( $wpc_pages['login_page_id'] ) && in_array( $wpc_pages['login_page_id'], $post_ids ) ) ||
                ( isset( $wpc_pages['portal_page_id'] ) && in_array( $wpc_pages['portal_page_id'], $post_ids ) ) ||
                ( isset( $wpc_pages['hub_page_id'] ) && in_array( $wpc_pages['hub_page_id'], $post_ids ) ) ||
                ( isset( $wpc_pages['edit_portal_page_id'] ) && in_array( $wpc_pages['edit_portal_page_id'], $post_ids ) ) ||
                ( isset( $wpc_pages['edit_staff_page_id'] ) && in_array( $wpc_pages['edit_staff_page_id'], $post_ids ) ) ) {
                    $sticky_posts_array = get_option( 'sticky_posts' );
                    $sticky_posts_array = ( is_array( $sticky_posts_array ) && 0 < count( $sticky_posts_array ) ) ? $sticky_posts_array : array();
            }

            foreach( $posts as $post ) {

                if( in_array( $post->ID, $sticky_posts_array ) ) {
                    continue;
                }

                //add no follow, no index on plugin pages
                if ( isset( $wpc_pages )
                    && is_array( $wpc_pages )
                    && in_array( $post->ID, array_values( $wpc_pages ) ) ) {

                    add_action( 'wp_head', array( &$this, 'add_meta_to_plugin_pages' ), 99 );
                }

                //for logout
                if ( isset( $wpc_pages['login_page_id'] ) && $post->ID == $wpc_pages['login_page_id'] ) {
                    //make logout
                    if ( isset( $_REQUEST['logout'] ) && 'true' == $_REQUEST['logout'] ) {
                        wp_logout();
                    }
                }

                //for Portal Page
                if ( isset( $wpc_pages['portal_page_id'] ) && $post->ID == $wpc_pages['portal_page_id'] ) {

                    if ( is_user_logged_in() ) {
                        if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                            $new_post = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );

                            if ( $new_post ) {

                                $this->current_plugin_page['portal_page_id'] = $new_post->ID;

                                //var_dump($this->current_plugin_page['portal_page_id']);
                                $scheme_key = get_post_meta( $this->current_plugin_page['portal_page_id'], '_wpc_style_scheme', true );
                                if( !( isset( $scheme_key ) && !empty( $scheme_key ) ) || $scheme_key == '__use_same_as_portal_page' ) {
                                    $scheme_key = get_post_meta( $post->ID, '_wpc_style_scheme', true );
                                }
                                $uploads = wp_upload_dir();
                                if ( file_exists( $uploads['basedir'] . '/wpc_custom_style_' . $scheme_key . '.css' ) ) {
                                    wp_register_style( 'wpc_custom_style_' . $scheme_key, $uploads['baseurl'] . '/wpc_custom_style_' . $scheme_key . '.css' );
                                    wp_enqueue_style( 'wpc_custom_style_' . $scheme_key, false, array(), false, true );
                                }

                                if( !(  current_user_can( 'administrator' ) ) ) {
                                    //block not appoved clients
                                    $user_id = $this->current_plugin_page['client_id'];
                                }

                                $category_id = get_post_meta( $new_post->ID, '_wpc_category_id', true );
                                //Portal Pages in Portal Pages Categories with Clients access
                                $users_category = ( isset( $category_id ) ) ? $this->cc_get_assign_data_by_object( 'portal_page_category', $category_id, 'client' ) : array();

                                //Portal Pages with Clients access
                                $user_ids = $this->cc_get_assign_data_by_object( 'portal_page', $new_post->ID, 'client' );
                                $user_ids = array_merge( $users_category, $user_ids );

                                //Portal Pages in Portal Pages Categories with Client Circles access
                                $groups_category = ( isset( $category_id ) ) ? $this->cc_get_assign_data_by_object( 'portal_page_category', $category_id, 'circle' ) : array();

                                //Portal Pages with Client Circles access
                                $groups_id = $this->cc_get_assign_data_by_object( 'portal_page', $new_post->ID, 'circle' );
                                $groups_id = ( is_array( $groups_id ) ) ? array_merge( $groups_category, $groups_id ) : $groups_id;

                                //get clients from Client Circles
                                if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                                    foreach( $groups_id as $group_id ) {
                                        $user_ids = array_merge( $user_ids, $this->cc_get_group_clients_id( $group_id ) );
                                    }

                                if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                                    $user_ids = array_unique( $user_ids );



                                if( current_user_can( 'administrator' ) ) {
                                    if ( is_array( $user_ids ) && count( $user_ids ) ) {

                                        $temp_user_id = 0;

                                        if ( isset( $_SESSION['wpc_preview_client'] ) &&
                                            in_array( $_SESSION['wpc_preview_client'], $user_ids ) ) {

                                            $temp_user_id = $_SESSION['wpc_preview_client'];
                                        }

                                        $user_id = ( $temp_user_id ) ? $temp_user_id : $user_ids[0];
                                        $new_post->post_content = $this->portal_page_select_client( $user_ids, $user_id ) . $new_post->post_content;
                                        $this->current_plugin_page['client_id'] = $user_id;
                                        $_SESSION['wpc_preview_client'] = $user_id;
                                    }
                                }


                                if ( ( !empty( $user_ids ) && in_array( $user_id, $user_ids ) ) ) {

                                    //replace placeholders in content
                                    if ( isset( $new_post->post_content ) ) {
                                        $args = array( 'client_id' => $user_id );
                                        $new_post->post_content = $this->cc_replace_placeholders( $new_post->post_content, $args, 'portal_page' );
                                    }

                                    $wp_query->is_page      = true;
                                    $wp_query->is_home      = false;
                                    $wp_query->is_singular  = true;

                                    //set title and content for PP
                                    $post->post_title   = $new_post->post_title;
                                    $post->post_content = $new_post->post_content;

                                    //Ignore Theme Link Page options - use page settings
                                    if ( 1 == get_post_meta( $new_post->ID, '_wpc_use_page_settings', true ) ) {
                                        $post = $new_post;
                                    }

                                    //set title for PP needs for some themes
                                    $this->current_plugin_page['title'] = $new_post->post_title;
                                    //replace title for PP needs for some themes
                                    add_filter( 'the_title', array( &$this, 'change_portal_page_title' ), 99, 2 );

                                    $filtered_posts[] = $post;
                                    continue;
                                }

                                wp_redirect( $this->cc_get_slug( 'error_page_id' ) );
                                exit;
                            }
                        } else {
                            $filtered_posts[] = $post;
                            continue;
                        }
                    }
                    continue;

                }
                //for HUB page
                elseif ( isset( $wpc_pages['hub_page_id'] ) && $post->ID == $wpc_pages['hub_page_id'] ) {

                    if ( is_user_logged_in() ) {

                        $wpc_cl_hubpage_id = $this->current_plugin_page['hub_id'];
                        //for hub preview
                        if ( current_user_can( 'administrator' ) ) {
                            if ( isset( $wp_query->query_vars['wpc_page'] ) && 'hub_preview' == $wp_query->query_vars['wpc_page'] ) {
                                if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {
                                    $client = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'wpc_cl_hubpage_id', 'meta_value' => $wp_query->query_vars['wpc_page_value'], 'fields' => 'ID' ) );

                                    if ( isset( $client[0] ) && $client[0] ) {
                                        $this->current_plugin_page['client_id'] = $client[0];
                                        $wpc_cl_hubpage_id  = $wp_query->query_vars['wpc_page_value'];
                                    }
                                }
                            } else {
                                if ( isset( $_SESSION['wpc_preview_client'] ) ) {

                                    $this->current_plugin_page['client_id'] = $_SESSION['wpc_preview_client'];
                                    $wpc_cl_hubpage_id = get_user_meta( $this->current_plugin_page['client_id'], 'wpc_cl_hubpage_id', true );
                                }
                            }

                        }

                        if ( 0 < $wpc_cl_hubpage_id ) {
                            $hub_page = get_post( $wpc_cl_hubpage_id );
                            if ( $hub_page ) {

                                //set title for PP needs for some themes
                                $this->current_plugin_page['post_id']   = $post->ID;
                                $this->current_plugin_page['title']     = $hub_page->post_title;

                                //Ignore Theme Link Page options - use page settings
                                if ( 1 == get_post_meta( $wpc_cl_hubpage_id, '_wpc_use_page_settings', true ) ) {
                                    $post = get_post( $wpc_cl_hubpage_id );
                                }

                                $wpc_general = $this->cc_get_settings( 'general' );

                                //change HUB title
                                if ( !isset( $wpc_general['show_hub_title'] ) || 'yes' == $wpc_general['show_hub_title'] ) {
                                    //set title and content for PP
                                    $post->post_title = $hub_page->post_title;
                                    //replace title for HUB needs for some themes
                                    add_filter( 'the_title', array( &$this, 'change_hub_page_title' ), 99, 2 );
                                }

                            }
                        }

                        $wp_query->is_page      = true;
                        $wp_query->is_home      = false;
                        $wp_query->is_singular  = true;


                        $filtered_posts[] = $post;
                        continue;
                    } else {
                        if ( strpos( $this->cc_get_slug( 'hub_page_id', false, false ), $_SERVER['REQUEST_URI'] ) ) {
                            do_action( 'wp_client_redirect', $this->cc_get_login_url() );
                            exit;
                        }
                    }
                }
                elseif ( isset( $wpc_pages['edit_portal_page_id'] ) && $post->ID == $wpc_pages['edit_portal_page_id'] ) {

                    if ( is_user_logged_in() ) {

                        if ( isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {

                            $edit_page = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );
                            if ( !$edit_page ) {
                                wp_redirect( $this->cc_get_slug( 'hub_page_id' ) );
                                exit;
                            }

                                $user_id = get_current_user_id();

                            $user_ids       = $this->cc_get_assign_data_by_object( 'portal_page', $edit_page->ID, 'client' );
                            $groups_id      = $this->cc_get_assign_data_by_object( 'portal_page', $edit_page->ID, 'circle' );

                            $user_ids = ( is_array( $user_ids ) && 0 < count( $user_ids ) ) ? $user_ids : array();

                            //get clients from Client Circles
                            if ( is_array( $groups_id ) && 0 < count( $groups_id ) )
                                foreach( $groups_id as $group_id ) {
                                    $user_ids = array_merge( $user_ids, $this->cc_get_group_clients_id( $group_id ) );
                                }

                            if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                                $user_ids = array_unique( $user_ids );

                            //actions from Edit ClientPage
                            if ( isset( $_POST['wpc_wpnonce'] ) && wp_verify_nonce( $_POST['wpc_wpnonce'], 'wpc_edit_clientpage' . $edit_page->ID ) ) {
                                //update ClientPage
                                if ( isset( $_POST['wpc_action'] ) && 'update' == $_POST['wpc_action'] ) {
                                    $arg = array (
                                        'ID'            => $edit_page->ID,
                                        'post_title'    => $_POST['clientpage_title'],
                                        'post_content'  => $_POST['clientpage_content'],
                                    );

                                    define( 'WPC_CLIENT_NOT_SAVE_META', '1' );
                                    wp_update_post( $arg );

                                    //make link
                                    if ( $this->permalinks ) {
                                        $redirect_link = $this->cc_get_slug( 'edit_portal_page_id' ) . $edit_page->post_name ;
                                    } else {
                                        $redirect_link = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $edit_page->post_name ), $this->cc_get_slug( 'edit_portal_page_id', false ) );
                                    }

                                    wp_redirect( $redirect_link );
                                    exit;

                                }
                                //Delete ClientPage
                                elseif ( isset( $_POST['wpc_action'] ) && 'delete' == $_POST['wpc_action'] )  {
                                    wp_delete_post( $edit_page->ID );
                                    wp_redirect( $this->cc_get_slug( 'hub_page_id' ) );
                                    exit;
                                }
                                //Cancel = return to HUB page
                                elseif ( isset( $_POST['wpc_action'] ) && 'cancel' == $_POST['wpc_action'] )  {
                                    wp_redirect( $this->cc_get_slug( 'hub_page_id' ) );
                                    exit;
                                }
                            }

                            $wp_query->is_page      = true;
                            $wp_query->is_home      = false;
                            $wp_query->is_singular  = true;
                            $filtered_posts[] = $post;
                            continue;
                        }
                    }
                    continue;
                }
                elseif ( isset( $wpc_pages['payment_process_page_id'] ) && $post->ID == $wpc_pages['payment_process_page_id'] ) {
                    $wp_query->is_page      = true;
                    $wp_query->is_home      = false;
                    $wp_query->is_singular  = true;
                    $filtered_posts[] = $post;

                    continue;
                }
                //add all other posts
                $filtered_posts[] = $post;
            }

            return $filtered_posts;
        }


        /**
         * Get template for Portal/HUB Pages from the assigned WP pages
         */
        function get_clientpage_template( $template ) {
            global $post, $wp_query;

            //for portal page
            if ( isset( $wp_query->query_vars['wpc_page'] ) && 'portal_page' == $wp_query->query_vars['wpc_page'] ) {

                //get PP values
                $new_post = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );
                if ( $new_post ) {
                    //get template for current PP
                    $page_template = get_post_meta( $new_post->ID, '_wp_page_template', true );

                    if ( !$page_template || '__use_same_as_portal_page' == $page_template ) {
                        //get template for WP page for PP
                        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
                        if ( $page_template ) {
                            if ( file_exists( get_stylesheet_directory() . "/{$page_template}" ) ) {
                                //set PP ID and Template
                                $this->current_plugin_page['post_id']   = $post->ID;
                                $this->current_plugin_page['template']  = $page_template;
                                $this->current_plugin_page['thumbnail_id']  = get_post_meta( $new_post->ID, '_thumbnail_id', true );

                                //use filter for change template - for some themes
                                add_filter( 'get_post_metadata', array( &$this, 'change_portal_page_template' ), 99, 4 );

                                return get_stylesheet_directory() . "/{$page_template}";
                            }
                        }
                        return $template;
                    } else {
                        //use PP template
                        if ( file_exists( get_stylesheet_directory() . "/{$page_template}" ) ) {
                            //set PP  ID and Template
                            $this->current_plugin_page['post_id']   = $post->ID;
                            $this->current_plugin_page['template']  = $page_template;
                            $this->current_plugin_page['thumbnail_id']  = get_post_meta( $new_post->ID, '_thumbnail_id', true );

                            //use filter for change template - for some themes
                            add_filter( 'get_post_metadata', array( &$this, 'change_portal_page_template' ), 99, 4 );

                            return get_stylesheet_directory() . "/{$page_template}";
                        } else {
                            return $template;
                        }
                    }
                }
            }
            elseif ( isset( $this->current_plugin_page['post_id'] ) && $post->ID == $this->current_plugin_page['post_id']  ) {

                if ( isset( $this->current_plugin_page['hub_id'] ) ) {
                    //get template for current PP
                    $page_template = get_post_meta( $this->current_plugin_page['hub_id'], '_wp_page_template', true );
                    if ( !$page_template || '__use_same_as_hub_page' == $page_template ) {
                        //get template for WP page for PP
                        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
                        if ( $page_template ) {
                            if ( file_exists( get_stylesheet_directory() . "/{$page_template}" ) ) {
                                //set PP ID and Template
                                $this->current_plugin_page['template']  = $page_template;

                                //use filter for change template - for some themes
                                add_filter( 'get_post_metadata', array( &$this, 'change_portal_page_template' ), 99, 4 );

                                return get_stylesheet_directory() . "/{$page_template}";
                            }
                        }
                        return $template;
                    } else {
                        //use PP template

                        if ( file_exists( get_stylesheet_directory() . "/{$page_template}" ) ) {
                            //set PP  ID and Template
                            $this->current_plugin_page['template']  = $page_template;

                            //use filter for change template - for some themes
                            add_filter( 'get_post_metadata', array( &$this, 'change_portal_page_template' ), 99, 4 );

                            return get_stylesheet_directory() . "/{$page_template}";
                        } else {
                            return $template;
                        }
                    }
                }

            }

            return $template;
        }


        /**
         * filter for change portal page template (for some themes)
         */
        function change_portal_page_template( $meta_type, $object_id, $meta_key = '', $single = false ) {
            if ( isset( $this->current_plugin_page['post_id'] ) && $object_id == $this->current_plugin_page['post_id'] && '_wp_page_template' == $meta_key ) {
                return $this->current_plugin_page['template'];
            } else {
                $wpc_pages = $this->cc_get_settings( 'pages' );
                if ( isset( $wpc_pages['portal_page_id'] ) && $object_id == $wpc_pages['portal_page_id'] && '_thumbnail_id' == $meta_key ) {
                    return isset( $this->current_plugin_page['thumbnail_id'] ) ? $this->current_plugin_page['thumbnail_id'] : null;
                }
            }
            return null;
        }


        /**
         * filter for change title for portal page (for some themes)
         */
        function change_portal_page_title( $title, $id = -1 ) {
            $wpc_pages = $this->cc_get_settings( 'pages' );
            if ( isset( $wpc_pages['portal_page_id'] ) && $id == $wpc_pages['portal_page_id'] ) {
                return $this->current_plugin_page['title'];
            }
            return $title;
        }


        /**
         * filter for change title for HUB page (for some themes)
         */
        function change_hub_page_title( $title, $id = -1 ) {
            $wpc_pages = $this->cc_get_settings( 'pages' );
            if ( isset( $wpc_pages['hub_page_id'] ) && $id == $wpc_pages['hub_page_id'] ) {
                return $this->current_plugin_page['title'];
            }
            return $title;
        }


        /*
        * add meta on plughin pages
        */
        function add_meta_to_plugin_pages() {
            echo '<meta name="robots" content="noindex"/>';
            echo '<meta name="robots" content="nofollow"/>';
            echo '<meta name="Cache-Control" content="no-cache"/>';
            echo '<meta name="Pragma" content="no-cache"/>';
            echo '<meta name="Expires" content="0"/>';
        }


        /**
         * Client Login from widget
         */
        function client_login_from_() {

            if ( !is_user_logged_in() ) {

                $ip_settings = $this->cc_get_settings( 'limit_ips' );

                if ( isset( $_POST['wpclient_login_button'] ) ) {
                    //login from widget
                    if ( !isset( $_POST['wpclient_login'] ) || '' == $_POST['wpclient_login'] ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Please enter your username!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    if ( !isset( $_POST['wpclient_pass'] ) || '' == $_POST['wpclient_pass'] ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Please enter your password!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    $user = get_user_by( 'login', $_POST['wpclient_login'] );

                    if( user_can( $user, 'wpc_client' ) && isset( $ip_settings['enable_limit'] ) && $ip_settings['enable_limit'] == 'yes' && !in_array( $_SERVER['REMOTE_ADDR'], $ip_settings['ips'] ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid IP address!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    $args = array(
                        'user_login'    => $_POST['wpclient_login'],
                        'user_password' => $_POST['wpclient_pass'],
                        'remember'      => isset( $_POST['wpclient_rememberme'] ) ? $_POST['wpclient_rememberme'] : false,
                    );

                    $user = wp_signon( $args );

                    if ( isset( $user->errors ) ) {

                        $errors = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                        $GLOBALS['wpclient_login_msg'] = apply_filters( 'login_errors', $errors );

                        if ( isset( $user->errors['invalid_username'] ) || isset( $user->errors['incorrect_password'] ) ) {
                            $GLOBALS['wpclient_login_msg'] = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                        }

                        return '';

                    }

                    if( isset( $_POST['wpclient_disable_redirect'] ) && '1' == $_POST['wpclient_disable_redirect'] ) {
                        $redirect_to = $_SERVER['HTTP_REFERER'];
                    } else {
                        $redirect_to = $this->cc_login_redirect_rules( site_url() , '', $user );
                    }

                    wp_redirect( $redirect_to );
                    exit;

                } elseif ( isset( $_POST['wpc_login'] ) && 'login_form' == $_POST['wpc_login'] ) {
                    //login from login form
                    if ( !isset( $_POST['log'] ) || '' == $_POST['log'] ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Please enter your username!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    if ( !isset( $_POST['pwd'] ) || '' == $_POST['pwd'] ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Please enter your password!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    $user = get_user_by( 'login', $_POST['log'] );

                    if( user_can( $user, 'wpc_client' ) && isset( $ip_settings['enable_limit'] ) && $ip_settings['enable_limit'] == 'yes' && !in_array( $_SERVER['REMOTE_ADDR'], $ip_settings['ips'] ) ) {
                        $GLOBALS['wpclient_login_msg'] = __( 'Invalid IP address!', WPC_CLIENT_TEXT_DOMAIN );
                        return;
                    }

                    $args = array(
                        'user_login'    => isset( $_POST['log'] ) ? $_POST['log'] : '',
                        'user_password' => isset( $_POST['pwd'] ) ? $_POST['pwd'] : '',
                        'remember'      => isset( $_POST['rememberme'] ) ? $_POST['rememberme'] : false,
                    );

                    $user = wp_signon( $args );

                    if ( isset( $user->errors ) ) {

                        $errors = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                        $GLOBALS['wpclient_login_msg'] = apply_filters( 'login_errors', $errors );

                        if ( isset( $user->errors['invalid_username'] ) || isset( $user->errors['incorrect_password'] ) ) {
                            $GLOBALS['wpclient_login_msg'] = __( 'Invalid Login or Password!', WPC_CLIENT_TEXT_DOMAIN );
                            return;
                        } else {
                            return;
                        }
                    }

                    $wpc_to_redirect = ( isset( $_GET['wpc_to_redirect'] ) && !empty( $_GET['wpc_to_redirect'] ) ) ? $_GET['wpc_to_redirect'] : '';

                    $redirect_to = apply_filters( 'login_redirect', $wpc_to_redirect , '', $user );
                    wp_redirect( $redirect_to );
                    exit();

                }
            }
        }


        function translator_js_scripts() { ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('#submit').click( function() {
                    if( jQuery('textarea[name="private_message"]').val() == '' ) {
                        jQuery('textarea[name="private_message"]').focus();
                        return false;
                    } else {
                        return true;
                    }
                });
            });
        </script>
        <?php }


        /* Add js script for js sort files */
        function js_scripts() {?>
            <style>
                .active_sort {
                    color: #000;
                }

                .hub_content {
                    min-height:200px;
                }

                .wpc-toolbar ul.nav {
                    padding: 0;
                }

                .wpc_link_current {
                    font-weight: 700;
                }

                .subsubsub li{
                    display: inline;
                }
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('.sort_time_asc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_files');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_file_category').each( function( indx, element ) {
                            jQuery(element).find('span.file_item').tsort('a:eq(0)',{ order:'asc', data:'timestamp' });
                        });
                    });
                    jQuery('.sort_time_desc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_files');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_file_category').each( function( indx, element ) {
                            jQuery(element).find('span.file_item').tsort('a:eq(0)',{ order:'desc', data:'timestamp' });
                        });
                    });

                    jQuery('.sort_name_asc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_files');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_file_category').each( function( indx, element ) {
                            jQuery(element).find('span.file_item').tsort('a:eq(0)',{order:'asc'});
                        });
                    });
                    jQuery('.sort_name_desc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_files');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_file_category').each( function( indx, element ) {
                            jQuery(element).find('span.file_item').tsort('a:eq(0)',{order:'desc'});
                        });
                    });
                });
            </script>
        <?php }


        /*
        * Exclude current portal page from list of portal pages
        */
        function exclude_portal_page( $exclude_array ) {
            $wpc_pages = $this->cc_get_settings( 'pages' );
            if ( isset( $wpc_pages['portal_page_id'] ) && !empty( $wpc_pages['portal_page_id'] ) ) {
                $exclude_array[] = $wpc_pages['portal_page_id'];
            }
            return $exclude_array;
        }


        /*
        * Set custom menu
        */
        function custom_menu(  $args ) {
            $wpc_general = $this->cc_get_settings( 'general' );
            if ( isset( $wpc_general['show_custom_menu'] ) && 'yes' == $wpc_general['show_custom_menu'] ) {
                if ( is_user_logged_in() && ( current_user_can( 'wpc_client' ) ) ) {
                    //only for clients and staff
                    if ( '' != $args['theme_location'] && isset( $wpc_general['custom_menu_logged_in'][$args['theme_location']] ) && '' != $wpc_general['custom_menu_logged_in'][$args['theme_location']] ) {
                        $menu = get_term( $wpc_general['custom_menu_logged_in'][$args['theme_location']], 'nav_menu' );
                        $args['menu']  = $menu->name;
                    }
                }
                elseif ( '' != $args['theme_location'] && isset( $wpc_general['custom_menu_logged_out'][$args['theme_location']] ) && '' != $wpc_general['custom_menu_logged_out'][$args['theme_location']] ) {
                    $menu = get_term( $wpc_general['custom_menu_logged_out'][$args['theme_location']], 'nav_menu' );
                    $args['menu']  = $menu->name;
                }
            }

            return $args;

        }


        /*
        * Add HUB page link to menu
        */
        function add_hub_link_to_menu( $items, $args = '' ) {

            $wpc_general = $this->cc_get_settings( 'general' );

            if ( isset( $wpc_general['show_hub_link'] ) && 'yes' == $wpc_general['show_hub_link'] ) {
                if ( is_user_logged_in() && !current_user_can( 'manage_options' ) ) {


                        $client_id = get_current_user_id();

                    $hub_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                    $page       = get_post( $hub_id );
                    $link       = get_permalink( $page->ID );

                    if ( $this->permalinks ) {
                        return $items . '<li><a href="' . $this->cc_get_slug( 'hub_page_id' ) . '">' . $wpc_general['hub_link_text'] . '</a></li>';
                    } else {
                        return $items . '<li><a href="' . $link .  '">' . $wpc_general['hub_link_text'] . '</a></li>';
                    }
                }
            }

            return $items;
        }


        /*
        * Include JS\CSS
        */
        function wp_css_js() {
            global $wp_query, $post;

            if ( !get_option( 'wpc_disable_jquery' ) ) {
                wp_enqueue_script( 'jquery' );
            }

            $this->password_protect_css_js();

            //custom style
            $uploads        = wp_upload_dir();
            if ( file_exists( $uploads['basedir'] . '/wpc_custom_style.css' ) ) {
                wp_register_style( 'wpc_custom_style', $uploads['baseurl'] . '/wpc_custom_style.css' );
                wp_enqueue_style( 'wpc_custom_style' );
            }


            wp_register_style( 'wpc_user_style', $this->plugin_url . 'css/user_style.css' );
            wp_enqueue_style( 'wpc_user_style' );

        }


        /*
        * Filter for full-width for Portal pages (may not work for some themes)
        */
        function body_class_for_clientpages( $classes ) {
            global $post;

            if ( is_single() && 'clientspage' == $post->post_type ) {
                $page_template = get_post_meta( $post->ID, '_wp_page_template', true );

                if ( !$page_template || '__use_same_as_portal_page' == $page_template ) {
                    $wpc_pages = $this->cc_get_settings( 'pages' );
                    if ( isset( $wpc_pages['portal_page_id'] ) && 0 < $wpc_pages['portal_page_id'] ) {
                        $page_template = get_post_meta( $wpc_pages['portal_page_id'], '_wp_page_template', true );
                    }
                }

                if ( 'page-templates/full-width.php' == $page_template )
                    $classes[] = 'full-width';

            }

            return $classes;

        }


        /*
        * Custom login - CSS
        */
        function custom_login_bm () {

            $wpc_custom_login = $this->cc_get_settings( 'custom_login' );

            if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
                // output styles
                echo '<link rel="stylesheet" type="text/css" href="' . $this->plugin_url . 'css/custom-login.css' . '" />';
                echo '<style>';

                if ( !empty( $wpc_custom_login['cl_background'] ) ) {
                    ?>
                    #login {
                        background:url(<?php echo $wpc_custom_login['cl_background'] ?>) top center no-repeat;
                        padding: 114px 0px 0px 0px !important;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_color'] ) ) {
                    ?>
                    #login,
                    #login label {
                        color:#<?php echo $wpc_custom_login['cl_color'] ?>;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_backgroundColor'] ) ) {
                    ?>
                    html,
                    body.login {
                        background:#<?php echo $wpc_custom_login['cl_backgroundColor'] ?> !important;
                    }
                    <?php
                }

                // text colour
                if ( !empty( $wpc_custom_login['cl_linkColor'] ) ) {
                    ?>
                    .login #login a {
                        color:#<?php echo $wpc_custom_login['cl_linkColor'] ?> !important;
                    }
                <?php
                }

                echo '</style>';
            }
        }


        /*
        * Custom login - link
        */
        function custom_login_logo_url() {
            $wpc_custom_login = $this->cc_get_settings( 'custom_login' );

            if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
                //logo link
                if ( !empty ( $wpc_custom_login['cl_logo_link'] ) ) {
                    return $wpc_custom_login['cl_logo_link'];
                }
            }

        }


        /*
        * Custom login - text
        */
        function custom_login_logo_title() {
            $wpc_custom_login = $this->cc_get_settings( 'custom_login' );

            if ( !isset( $wpc_custom_login['cl_enable'] ) || 'yes' == $wpc_custom_login['cl_enable'] ) {
                //logo text
                if ( !empty( $wpc_custom_login['cl_logo_title'] ) ) {
                    return $wpc_custom_login['cl_logo_title'];
                }
            }

        }



        /*
        * portal page select client
        */
        function portal_page_select_client( $clients_id, $user_id ) {

            if ( !count( $clients_id ) )
                return '';

            ob_start();
            ?>
            <label for="wpc_select_client_for_preview"><b><?php printf( __( 'Select %s for preview his %s: ', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['portal']['s'] ) ?></b></label><select name="wpc_select_client_for_preview" id="wpc_select_client_for_preview">
                <?php
                foreach( $clients_id as $id ) {
                    $user = get_userdata( $id );

                    if ( !$user )
                        continue;
                    $selected = ( $id == $user_id ) ? 'selected' : '';

                    echo '<option value="' . $id . '" ' . $selected . ' >' . $user->get( 'user_login' ) . '</option>';
                }
                ?>
            </select><hr />

            <?php
            $new_content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            wp_enqueue_script( 'jquery', false, array(), false, true );

            wp_register_script( 'wpc_preview_portal_page', $this->plugin_url . 'js/pages/preview_portal_page.js' );
            wp_enqueue_script( 'wpc_preview_portal_page', false, array(), false, true );

            $scrypt_array = array( 'url' => get_admin_url() . 'admin-ajax.php' );
            wp_localize_script( 'wpc_preview_portal_page', 'wpc_preview', $scrypt_array );

            return $new_content;
        }


        /*
        * Checking for run set_global_vars
        */
        function checking_for_set_global_vars() {
            if ( current_user_can( 'administrator' ) ) {
                add_action( 'wp', array( &$this, 'set_global_vars' ) );
            } else {
                $this->set_global_vars();
            }
        }


        /*
        * set global vars for client
        */
        function set_global_vars() {
            //block not logged clients
            if ( !is_user_logged_in() )  {
                return '';
            }



            //for client
            if ( current_user_can( 'wpc_client' ) && !current_user_can( 'administrator' ) ) {
                $client_id = get_current_user_id();
                $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                $this->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
                $this->current_plugin_page['client_id'] = $client_id;
                return '';

            }
            //for
            elseif ( current_user_can( 'administrator' ) ) {
                if ( isset( $_SESSION['wpc_preview_client'] ) && 0 < $_SESSION['wpc_preview_client'] ) {
                    $client_id = $_SESSION['wpc_preview_client'];

                    $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                    $this->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
                    $this->current_plugin_page['client_id'] = $client_id;
                    return '';
                } else {
                    global $wp_query;
                    if ( isset( $wp_query->query_vars['wpc_page'] ) && 'hub_preview' == $wp_query->query_vars['wpc_page'] && isset( $wp_query->query_vars['wpc_page_value'] ) && '' != $wp_query->query_vars['wpc_page_value'] ) {
                        $client = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'wpc_cl_hubpage_id', 'meta_value' => $wp_query->query_vars['wpc_page_value'], 'fields' => 'ID' ) );
                        if ( isset( $client[0] ) && $client[0] ) {
                            $client_id = $client[0];

                            $wpc_cl_hubpage_id = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );

                            $this->current_plugin_page['hub_id']    = $wpc_cl_hubpage_id;
                            $this->current_plugin_page['client_id'] = $client_id;
                            return '';
                        }
                    }
                }
            }

            $this->current_plugin_page['hub_id']    = 0;
            $this->current_plugin_page['client_id'] = get_current_user_id();

        }







    //end class
    }

    $wpc_client = new WPC_Client();
}

?>
