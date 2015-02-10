<?php

if ( !class_exists( "WPC_Client_User_Shortcodes" ) ) {

    class WPC_Client_User_Shortcodes extends WPC_Client_User_Common {

        /**
        * constructor
        **/
        function shortcodes_construct() {

            add_shortcode( 'wpc_client_profile', array( &$this, 'shortcode_editprof' ) );
            add_shortcode( 'wpc_client', array( &$this, 'shortcode_wpclients' ) );
            add_shortcode( 'wpc_client_private', array( &$this, 'shortcode_private' ) );
            add_shortcode( 'wpc_client_theme', array( &$this, 'shortcode_theme' ) );
            add_shortcode( 'wpc_client_loginf', array( &$this, 'shortcode_loginf' ) );
            add_shortcode( 'wpc_client_logoutb', array( &$this, 'shortcode_logoutb' ) );
            add_shortcode( 'wpc_client_filesla', array( &$this, 'shortcode_filesla' ) );
            add_shortcode( 'wpc_client_uploadf', array( &$this, 'shortcode_uploadf' ) );
            add_shortcode( 'wpc_client_fileslu', array( &$this, 'shortcode_fileslu' ) );
            add_shortcode( 'wpc_client_pagel', array( &$this, 'shortcode_pagel' ) );
            add_shortcode( 'wpc_client_com', array( &$this, 'shortcode_comments' ) );
            add_shortcode( 'wpc_client_graphic', array( &$this, 'shortcode_graphic' ) );
            add_shortcode( 'wpc_client_registration_form', array( &$this, 'shortcode_client_registration_form' ) );
            add_shortcode( 'wpc_client_error_image', array( &$this, 'shortcode_error_image' ) );
            add_shortcode( 'wpc_client_errors', array( &$this, 'shortcode_errors' ) );
            add_shortcode( 'wpc_client_registration_successful', array( &$this, 'shortcode_registration_successful' ) );
            add_shortcode( 'wpc_client_add_staff_form', array( &$this, 'shortcode_add_staff_form' ) );
            add_shortcode( 'wpc_client_edit_staff_form', array( &$this, 'shortcode_add_staff_form' ) );
            add_shortcode( 'wpc_client_staff_directory', array( &$this, 'shortcode_staff_directory' ) );
            add_shortcode( 'wpc_client_business_name', array( &$this, 'shortcode_business_name' ) );
            add_shortcode( 'wpc_client_contact_name', array( &$this, 'shortcode_contact_name' ) );
            add_shortcode( 'wpc_client_hub_page', array( &$this, 'shortcode_hub_page' ) );
            add_shortcode( 'wpc_client_portal_page', array( &$this, 'shortcode_portal_page' ) );
            add_shortcode( 'wpc_client_get_page_link', array( &$this, 'shortcode_get_page_link' ) );
            add_shortcode( 'wpc_client_edit_portal_page', array( &$this, 'shortcode_edit_portal_page' ) );
            add_shortcode( 'wpc_redirect_on_login_hub', array( &$this, 'shortcode_redirect_on_login_hub' ) );

            add_shortcode( 'wpc_client_hub_page_template', array( &$this, 'shortcode_hub_page_template' ) );

            add_shortcode( 'wpc_client_client_managers', array( &$this, 'shortcode_client_managers' ) );

            add_shortcode( 'wpc_client_payment_process', array( &$this, 'payment_process_func' ) );

            add_shortcode( 'wpc_client_custom_field', array( &$this, 'shortcode_custom_field' ) );

            add_shortcode( 'wpc_client_custom_field_value', array( &$this, 'shortcode_custom_field_value' ) );
        }


        /**
        * Function for shortcode which display custom field value
        */
        function shortcode_custom_field_value( $atts, $contents = null ) {

            return '';
        }


        /**
        * Function for shortcode which display custom field on forms
        */
        function shortcode_custom_field( $atts, $contents = null ) {

            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_editprof( $atts, $contents = null ) {
            $user_id = $this->cc_checking_page_access();

            if ( ( current_user_can( 'wpc_client' ) ) &&  !current_user_can( 'manage_network_options' ) ) {

                if ( current_user_can( 'wpc_view_profile' ) || current_user_can( 'wpc_modify_profile' ) ) {

                   return '';
                } else {
                    do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
                    exit;
                }
            } elseif ( current_user_can( 'administrator' ) ) {
                do_action( 'wp_client_redirect', get_admin_url() );
                exit;
            } else {
                do_action( 'wp_client_redirect', get_home_url() );
                exit;
            }
        }


        /*
        * Shortcode
        */
        function shortcode_wpclients($atts, $contents = null) {
            global $current_user;
            $contents .= "<style type='text/css'>.navigation .alignleft, .navigation .alignright {display:none;}</style>";
            //$contents = str_replace("{client_business_name}", $current_user->user_login, $contents);

            $args['client_id'] = get_current_user_id();

            $contents = $this->cc_replace_placeholders( $contents, $args );
            return do_shortcode($contents);
        }


        /*
        * Shortcode
        */
        function shortcode_private( $atts, $contents = null ) {
            global $current_user, $wpdb;
            extract( shortcode_atts( array(
                'for' => '',
                'for_circle' => '',
            ), $atts ) );

            if ( is_user_logged_in() ) {

                if ( isset( $this->current_plugin_page['client_id'] ) && 0 < $this->current_plugin_page['client_id'] ) {
                    $client_id = $this->current_plugin_page['client_id'];

                    if( isset( $client_id ) && !empty( $client_id ) ) {
                        $current_user = get_userdata( $client_id );
                    }
                }


                if ( 'all' == $for && ( current_user_can( 'administrator' ) || current_user_can( 'wpc_client' ) ) ) {
                    //for all clients
                    return do_shortcode( $contents );
                } elseif( $current_user->user_login == $for || ( current_user_can( 'administrator' ) ) ) {
                    //for some client
                    return do_shortcode( $contents );
                } elseif ( 'all' == $for_circle ) {
                    //for all groups
                    $client_groups_id = $this->cc_get_client_groups_id( $current_user->ID );
                    if ( ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) || ( current_user_can( 'administrator' ) ) ) {
                        //client in one of group
                        return do_shortcode( $contents );
                    }
                } elseif ( '' != $for_circle ) {
                    //for some group
                    $group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE group_name = '%s'", $for_circle ) );
                    if ( 0 < $group_id ) {
                        $client_groups_id = $this->cc_get_client_groups_id( $current_user->ID );
                        if ( ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) && in_array( $group_id, $client_groups_id ) ) || ( current_user_can( 'administrator' ) ) ) {
                            //client in this group
                            return do_shortcode( $contents );
                        }
                    }

                }

            }

            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_theme( $atts, $contents = null) {
            $url    = $this->plugin_url . 'images';
            $wpc_skins = $this->cc_get_settings( 'skins' );

            if( !$wpc_skins ) {
                $wpc_skins = 'light';
            }

            $url .= "/" . $wpc_skins;

            return $url;
        }


        /*
        * Shortcode
        */
        function shortcode_loginf( $atts, $contents=null ) {
            $no_redirect = false;
            if( isset( $atts['no_redirect'] ) && 'true' == $atts['no_redirect'] ) {
                $no_redirect = true;
            }

            if( !is_user_logged_in() || ( is_user_logged_in() && $no_redirect ) ) {
                wp_register_script( 'wpc_login_page', $this->plugin_url . 'js/pages/login.js' );
                wp_enqueue_script( 'wpc_login_page' );

                wp_register_style( 'wpc_login_page', $this->plugin_url . 'css/pages/login.css' );
                wp_enqueue_style( 'wpc_login_page' );

                return ( include $this->plugin_dir . 'includes/user/login_form.php' );
            } else {
                global $current_user;

                $url = $this->cc_login_redirect_rules( get_home_url(), '', $current_user );

                if ( !empty( $url ) ) {
                    do_action( 'wp_client_redirect', $url );
                    exit;
                } else {
                    do_action( 'wp_client_redirect', add_query_arg( array( 'msg' => 've' ), get_home_url() ) );
                    exit;
                }
            }
        }


        /*
        * Shortcode
        */
        function shortcode_logoutb($atts,$contents=null) {
            if ( !is_user_logged_in() )
                return "";
            else
                return ( include $this->plugin_dir . 'includes/user/logout.php' );
        }


        /*
        * Shortcode for display file upload form
        */
        function shortcode_uploadf( $atts, $contents = null ) {

            return '';
        }


        /*
        * Shortcode for upload file from hub - client area
        */
        function shortcode_fileslu($atts, $contents = null) {

            return '';
        }


        /*
        * Shortcode for display files for client
        */
        function shortcode_filesla($atts, $contents = null) {

            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_pagel($atts, $contents = null) {
            global $post, $wpdb, $wp_query;

            //checking access
            $user_id = $this->cc_checking_page_access();

            $post_contents  = '';
            /**
            *  $data - - - array which use in SMARTY as data array with texts and information about pages
            */
            $data           = array();

            //part of code for displaying staff directory for clients with staff
            if ( 'clientspage' != $post->post_type ) {

                //add some control pages
                if ( current_user_can( 'wpc_client' ) || current_user_can( 'administrator' ) ) {

                    //add status message
                    if ( isset( $_GET['staff'] ) ) {
                        switch( $_GET['staff'] ) {
                            case 'a':
                                $data['message'] = sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['staff']['s'] );
                                break;
                            case 'd':
                                $data['message'] = sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['staff']['s'] );
                                break;
                            case 'e':
                                $data['message'] = sprintf( __( '%s <strong>Changed</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['staff']['s'] );
                                break;
                        }
                    }

                }

            }

            /**
            * part of code for displaying jQuery sorting buttons.
            * this buttons are available then attr "show_sort" of shortcode are "yes"
            * sorting working only for pages without category titles therefore if
            * attr "show_categories_titles" of shortcode are "yes" then sorting will be not available
            */
            if ( isset( $atts['show_sort'] ) && 'yes' == strtolower( $atts['show_sort'] ) ) {
                add_action( 'wp_footer', array( &$this, 'portal_pages_sort_scripts' ) );

                $data['show_sort'] = true;
                $data['sort_by_text'] = __( 'Sort by:', WPC_CLIENT_TEXT_DOMAIN );
                $data['time_added_text'] = __( 'Time Added:', WPC_CLIENT_TEXT_DOMAIN );
                $data['asc_text'] = __( 'Asc', WPC_CLIENT_TEXT_DOMAIN );
                $data['desc_text'] = __( 'Desc', WPC_CLIENT_TEXT_DOMAIN );
                $data['name_text'] = __( 'Title:', WPC_CLIENT_TEXT_DOMAIN );
            }

            if( "[wpc_client_hub_page]" != $post->post_content ) {
                if ( isset( $atts['show_current_page'] ) && 'yes' == $atts['show_current_page'] ) {
                    //show current portal page
                    $show_current_page = '';
                } else {
                    if( isset( $wp_query->query_vars['preview'] ) && 'true' == $wp_query->query_vars['preview'] && isset( $_REQUEST['preview_id'] ) && !empty( $_REQUEST['preview_id'] ) ) {
                        $post_id = $_REQUEST['preview_id'];
                    } elseif ( isset( $wp_query->query_vars['wpc_page_value'] ) ) {
                        $new_post = get_page_by_path( $wp_query->query_vars['wpc_page_value'], object, 'clientspage' );

                        if ( $new_post ) {
                            $post_id = $new_post->ID;
                        }
                    } else {
                        $post_id = $post->ID;
                    }
                    //hide current portal page
                    $show_current_page = "$wpdb->posts.ID NOT LIKE '$post_id' AND";
                }
            } else {
                $show_current_page = '';
            }

            /**
            *  $mypages_id - - - array of pages which are available for client
            */
            $mypages_id = array();

            //Portal pages in categories with clients access
            $client_portal_page_category_ids = $this->cc_get_assign_data_by_assign( 'portal_page_category', 'client', $user_id );

            $results = $wpdb->get_col(
                "SELECT $wpdb->posts.ID
                FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta
                    ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                WHERE $show_current_page
                    $wpdb->posts.post_type = 'clientspage' AND
                    $wpdb->posts.post_status = 'publish' AND
                    $wpdb->postmeta.meta_key = '_wpc_category_id' AND
                    $wpdb->postmeta.meta_value IN('" . implode( "','", $client_portal_page_category_ids ) . "')"
            );

            if( isset( $results ) && 0 < count( $results ) ) {
                $mypages_id = array_merge( $mypages_id, $results );
            }

            //Portal pages with clients access
            $client_portal_page_ids = $this->cc_get_assign_data_by_assign( 'portal_page', 'client', $user_id );

            if( isset( $client_portal_page_ids ) && 0 < count( $client_portal_page_ids ) ) {
                $mypages_id = array_merge( $mypages_id, $client_portal_page_ids );
            }

            $client_groups_id = $this->cc_get_client_groups_id( $user_id );

            if ( is_array( $client_groups_id ) && 0 < count( $client_groups_id ) ) {
                foreach( $client_groups_id as $group_id ) {

                    //Portal pages in categories with group access
                    $group_portal_page_category_ids = $this->cc_get_assign_data_by_assign( 'portal_page_category', 'circle', $group_id );

                    $results = $wpdb->get_col(
                        "SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                            INNER JOIN $wpdb->postmeta
                            ON $wpdb->postmeta.post_id = $wpdb->posts.ID
                        WHERE $show_current_page
                            $wpdb->posts.post_type = 'clientspage' AND
                            $wpdb->posts.post_status = 'publish' AND
                            $wpdb->postmeta.meta_key = '_wpc_category_id' AND
                            $wpdb->postmeta.meta_value IN('" . implode( "','", $group_portal_page_category_ids ) . "')"
                    );

                    if ( 0 < count( $results ) ) {
                        $mypages_id = array_merge( $mypages_id, $results );
                    }

                    //Portal pages with group access
                    $group_portal_page_ids = $this->cc_get_assign_data_by_assign( 'portal_page', 'circle', $group_id );

                    if( isset( $group_portal_page_ids ) && 0 < count( $group_portal_page_ids ) ) {
                        $mypages_id = array_merge( $mypages_id, $group_portal_page_ids );
                    }

                }
            }
            $mypages_id = array_unique( $mypages_id );


            $numeric = 0;
            $atts_error = false;

            if ( isset( $atts['categories'] ) && '' != $atts['categories'] ) {
                $categories_ids = explode( ',', $atts['categories'] );

                //check atts categories its numeric(ID category) or string(title category)
                foreach( $categories_ids as $value ) {
                    if( is_numeric( $value ) ) {
                        $numeric++;
                    }
                }
                //if atts isn't numeric get categories ID of it names
                if( $numeric != count( $categories_ids ) && 0 == $numeric ) {

                    $category_names = $categories_ids;
                    $categories_ids = array();


                    //to lower case all elements of massive
                    foreach( $category_names as $key=>$value ) {
                        $category_names[$key] = strtolower( $value );
                    }

                    //find uncategory for view uncategories pages
                    if( in_array( 'uncategory', $category_names) ) {
                        $categories_ids = array('0');
                        unset( $category_names[array_search( 'uncategory', $category_names )] );
                    }

                } elseif( $numeric != count( $categories_ids ) && 0 != $numeric ) {
                    $atts_error = true;
                }
            } else {
                //get all categories with non categoties pages
                $categories_ids = array('0');
            }


            if( !$atts_error ) {
                $categories = array();
                if( is_array( $categories_ids ) && 0 < count( $categories_ids ) ) {

                    if( in_array( '0', $categories_ids ) ) {
                        $categories[] = array( 'cat_id' => '0', 'cat_name' => __( 'No Category', WPC_CLIENT_TEXT_DOMAIN ) );
                    }

                }

                $current_portal_pages = array();

                if ( isset( $atts['show_categories_titles'] ) && 'yes' ==  $atts['show_categories_titles'] ) {
                    //show category name
                    $data['show_category_name'] = true;

                    //show categorized pages
                    foreach( $categories as $category ) {
                        //get pages from category which client access true
                        //$mypages_id = $this->ucc_get_portalpages_ids_for_client( $user_id, $category, $show_current_page );
                        foreach( $mypages_id as $mypage_id ) {
                            $page_category = get_post_meta( $mypage_id, '_wpc_category_id', true );

                            $page_category = ( '' == $page_category ) ? '0' : $page_category;
                            if( $category['cat_id'] == $page_category ) {
                                $current_portal_pages[] = $mypage_id;
                            }
                        }
                        //sorting
                        if( isset( $atts['sort_type'] ) && isset( $atts['sort'] ) ) {
                            $current_portal_pages = $this->ucc_sort_portalpages_for_client( $current_portal_pages, $atts['sort_type'], $atts['sort'] );
                        } elseif( isset( $atts['sort_type'] ) && !isset( $atts['sort'] ) ) {
                            $current_portal_pages = $this->ucc_sort_portalpages_for_client( $current_portal_pages, $atts['sort_type'] );
                        }

                        if ( 0 < count( $current_portal_pages ) ) {
                            foreach( $current_portal_pages as $page_id ) {
                                $mypage = get_post( $page_id, 'ARRAY_A' );
                                if( 'publish' != $mypage['post_status'] ) continue;
                                $page = array();

                                $page['edit_link'] = '';

                                if ( 1 == get_post_meta( $mypage['ID'], 'allow_edit_clientpage', true ) ) {
                                    //make link
                                    if ( $this->permalinks ) {
                                        $page['edit_link'] = $this->cc_get_slug( 'edit_portal_page_id' ) . $mypage['post_name'];
                                    } else {
                                        $page['edit_link'] = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $mypage['post_name'] ), $this->cc_get_slug( 'edit_portal_page_id', false ) );
                                    }
                                }

                                //make link
                                if ( $this->permalinks ) {
                                    $page['url'] = $this->cc_get_slug( 'portal_page_id' ) . $mypage['post_name'];
                                } else {
                                    $page['url'] = add_query_arg( array( 'wpc_page' => 'portal_page', 'wpc_page_value' => $mypage['post_name'] ), $this->cc_get_slug( 'portal_page_id', false ) );
                                }

                                $page['title']      = nl2br( $mypage['post_title'] );
                                $page['creation_date'] = strtotime( $mypage['post_date'] );

                                $data['pages'][$category['cat_name']][]    = $page;
                            }
                        }
                        //reset data arrays for next circle
                        $current_portal_pages = array();
                    }

                } else {

                    $data['show_category_name'] = false;

                    $exit_array = array();
                    foreach( $categories as $category ) {

                        foreach( $mypages_id as $mypage_id ) {
                            $page_category = get_post_meta( $mypage_id, '_wpc_category_id', true );

                            $page_category = ( '' == $page_category ) ? '0' : $page_category;
                            if( $category['cat_id'] == $page_category ) {
                                $current_portal_pages[] = $mypage_id;
                            }
                        }


                        $exit_array = array_merge( $exit_array, $current_portal_pages );
                        $current_portal_pages = array();
                    }

                    $current_portal_pages = array_unique( $exit_array );

                    if( is_array( $current_portal_pages ) && 0 < count( $current_portal_pages ) ) {

                        //sorting
                        if( isset( $atts['sort_type'] ) && isset( $atts['sort'] ) ) {
                            $current_portal_pages = $this->ucc_sort_portalpages_for_client( $current_portal_pages, $atts['sort_type'], $atts['sort'] );
                        } elseif( isset( $atts['sort_type'] ) && !isset( $atts['sort'] ) ) {
                            $current_portal_pages = $this->ucc_sort_portalpages_for_client( $current_portal_pages, $atts['sort_type'] );
                        }

                        //sortin by order
                        foreach ( $current_portal_pages as $mypage_id ) {
                            $myorder = get_post_meta( $mypage_id, '_wpc_order_id', true );
                            if ( !isset( $myorder ) || '' == $myorder || 0 == $myorder ) $myorder = '999999999999999999999999999';
                            $mypage_orders[ $mypage_id ] = $myorder ;
                        }
                        asort( $mypage_orders, SORT_NUMERIC );
                        reset( $mypage_orders );

                        $current_portal_pages = array();
                        foreach ( $mypage_orders as $key => $value ) {
                             $current_portal_pages[] = '' . $key;
                        }

                        foreach( $current_portal_pages as $page_id ) {
                            $mypage = get_post( $page_id, 'ARRAY_A' );
                            if( 'publish' != $mypage['post_status'] ) continue;
                            $page = array();

                            $page['edit_link'] = '';

                            if ( 1 == get_post_meta( $mypage['ID'], 'allow_edit_clientpage', true ) ) {
                                //make link
                                if ( $this->permalinks ) {
                                    $page['edit_link'] = $this->cc_get_slug( 'edit_portal_page_id' ) . $mypage['post_name'];
                                } else {
                                    $page['edit_link'] = add_query_arg( array( 'wpc_page' => 'edit_portal_page', 'wpc_page_value' => $mypage['post_name'] ), $this->cc_get_slug( 'edit_portal_page_id', false ) );
                                }
                            }

                            //make link
                            if ( $this->permalinks ) {
                                $page['url'] = $this->cc_get_slug( 'portal_page_id' ) . $mypage['post_name'];
                            } else {
                                $page['url'] = add_query_arg( array( 'wpc_page' => 'portal_page', 'wpc_page_value' => $mypage['post_name'] ), $this->cc_get_slug( 'portal_page_id', false ) );
                            }

                            $page['title']      = nl2br( $mypage['post_title'] );
                            $page['creation_date'] = strtotime( $mypage['post_date'] );

                            $data['pages'][]    = $page;
                        }
                    } else {
                        $data['pages'] = array();
                    }

                }

                $post_contents = $this->cc_getTemplateContent( 'wpc_client_pagel', $data, $user_id );
            } else{
                $post_contents = 'Some error with shortcode attributes';
            }
            return do_shortcode( $post_contents );
        }


        /* Add js script for js sort pages */
        function portal_pages_sort_scripts() { ?>
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
                    jQuery('.sort_date_asc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_client_pages');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                            jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{ order:'asc', data:'timestamp' });
                        });
                    });

                    jQuery('.sort_date_desc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_client_pages');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                            jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{ order:'desc', data:'timestamp' });
                        });
                    });

                    jQuery('.sort_title_asc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_client_pages');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                            jQuery(element).find('span.wpc_page_item').tsort('a:eq(0)',{order:'asc'});
                        });
                    });
                    jQuery('.sort_title_desc').click(function() {
                        var obj = jQuery(this).parents('.wpc_client_client_pages');
                        obj.children().removeClass('active_sort');
                        jQuery(this).addClass('active_sort');

                        obj.children('.wpc_client_portal_page_category').each( function( indx, element ) {
                            jQuery(this).find('span.wpc_page_item').tsort('a:eq(0)',{order:'desc'});
                        });
                    });
                });
            </script>
        <?php }


        /*
        * Shortcode
        */
        function shortcode_comments($atts, $contents = null) {

            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_graphic() {
            $wpc_general = $this->cc_get_settings( 'general' );
            if ( isset( $wpc_general['graphic'] ) && '' != $wpc_general['graphic'] ) {
                return "<img class='wpc_client_graphic' src='{$wpc_general['graphic']}' />";
            }

            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_client_registration_form( $atts, $contents = null ) {
            $no_redirect = false;

            if( isset( $atts['no_redirect'] ) && $atts['no_redirect'] == 'true' ) {
                $no_redirect = true;
            }


            if( is_user_logged_in() && !$no_redirect ) {
                if( current_user_can( 'wpc_client' ) &&  !current_user_can( 'manage_network_options' ) ) {
                    do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
                    exit;
                } elseif ( current_user_can( 'administrator' ) ) {
                    do_action( 'wp_client_redirect', get_admin_url() );
                    exit;
                } else {
                    do_action( 'wp_client_redirect', get_home_url() );
                    exit;
                }
            } else {

                wp_enqueue_script( 'wpc_registration', $this->plugin_url . 'js/pages/registration.js', array(), '1.0.0', true );

                $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );

                $localized_data = array(
                    'registration_using_terms'  => ( isset( $wpc_clients_staff['registration_using_terms'] ) && 'yes' == $wpc_clients_staff['registration_using_terms'] ) ? 'yes' : 'no',
                    'terms_notice'              => ( isset( $wpc_clients_staff['terms_notice'] ) && !empty( $wpc_clients_staff['terms_notice'] ) ) ? $wpc_clients_staff['terms_notice'] : __( 'Sorry, you must agree to the Terms/Conditions to continue', WPC_CLIENT_TEXT_DOMAIN )
                );
                wp_localize_script( 'wpc_registration', 'terms_conditions', $localized_data );

                wp_register_style( 'wpc_registration', $this->plugin_url . 'css/pages/registration.css' );
                wp_enqueue_style( 'wpc_registration' );

                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_register_style( 'wpc-ui-style', $this->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css' );
                wp_enqueue_style( 'wpc-ui-style' );

                return ( include( $this->plugin_dir . 'includes/user/client_registration_form.php' ) );
            }
        }


        /*
        * Shortcode
        */
        function shortcode_registration_successful( $atts, $contents = null ) {
            if ( !is_user_logged_in() ) {
                $data['text'] = __( "<p>You have successfully registered.</p><p>After approval from the administrator, you will have full access to your account.</p>", WPC_CLIENT_TEXT_DOMAIN );
                $out2 =  $this->cc_getTemplateContent( 'wpc_client_registration_successful', $data );
                return do_shortcode( $out2 );
            } else {
                return "";
            }
        }


        /*
        * Shortcode
        */
        function shortcode_add_staff_form( $atts, $contents = null ) {
            return '';
        }


        /*
        * Shortcode
        */
        function shortcode_staff_directory( $atts, $contents = null ) {
            return '';
        }


        /*
        * Shortcode for show business name
        */
        function shortcode_business_name( $atts, $contents = null ) {
            if ( is_user_logged_in() ) {

                $client_id = ( isset( $this->current_plugin_page['client_id'] ) ) ? $this->current_plugin_page['client_id'] : get_current_user_id();

                return get_user_meta( $client_id, 'wpc_cl_business_name', true );
            }
            return '';
        }


        /*
        * Shortcode contact name
        */
        function shortcode_contact_name( $atts, $contents = null ) {
            if ( is_user_logged_in() ) {

                $client_id = ( isset( $this->current_plugin_page['client_id'] ) ) ? $this->current_plugin_page['client_id'] : get_current_user_id();

                $client = get_userdata( $client_id );

                if ( $client ) {
                    return $client->get( 'display_name' );
                }

            }
            return '';
        }


        /*
        * Shortcode HUB page
        */
        function shortcode_hub_page( $atts, $contents = null ) {
            global $wp_query;

            $client_id = $this->cc_checking_page_access();

            $wpc_cl_hubpage_id = isset( $this->current_plugin_page['hub_id'] ) ? $this->current_plugin_page['hub_id'] : 0;

            if ( isset( $_GET['msg'] ) && 've' == $_GET['msg'] ) {
                echo '<p class="message">' . __('Your e-mail address is verified.', WPC_CLIENT_TEXT_DOMAIN) . '</p>';
            }

            if ( 0 < $wpc_cl_hubpage_id ) {
                $hub_page = get_post( $wpc_cl_hubpage_id );

                if ( isset( $hub_page->post_content ) ) {
                    $args = array( 'client_id' => $client_id );
                    return do_shortcode( $this->cc_replace_placeholders( $hub_page->post_content, $args, 'hub_page' ) );
                }
            }

            return '';
        }


        /*
        * Shortcode for Portal page
        */
        function shortcode_portal_page( $atts, $contents = null ) {
            global $post;

            if ( is_user_logged_in() ) {
                $scheme_key = get_post_meta( $post->ID, '_wpc_style_scheme', true );
                $uploads = wp_upload_dir();
                if ( file_exists( $uploads['basedir'] . '/wpc_custom_style_' . $scheme_key . '.css' ) ) {
                    wp_register_style( 'wpc_custom_style_' . $scheme_key, $uploads['baseurl'] . '/wpc_custom_style_' . $scheme_key . '.css' );
                    wp_enqueue_style( 'wpc_custom_style_' . $scheme_key, false, array(), false, true );
                }

                $client_id = ( isset( $this->current_plugin_page['client_id'] ) ) ? $this->current_plugin_page['client_id'] : get_current_user_id();
            }
            return '';
        }


        /**
        * Shortcode for Get Page link
        **/
        function shortcode_get_page_link( $atts, $contents = null ) {
            if ( isset( $atts['page'] ) && '' != $atts['page'] ) {
                $url = $this->cc_get_slug( $atts['page'] . '_page_id' );
                if ( '' != $url ) {
                    $id     = ( isset( $atts['id'] ) && '' != $atts['id'] ) ?'id="' . $atts['id'] . '"' : '';
                    $class  = ( isset( $atts['class'] ) && '' != $atts['class'] ) ? 'class="' . $atts['class'] . '"' : '';
                    $style  = ( isset( $atts['style'] ) && '' != $atts['style'] ) ? 'style="' . $atts['style'] . '"' : '';
                    $text   = ( isset( $atts['text'] ) && '' != $atts['text'] ) ? $atts['text'] : $atts['page'] . ' link';
                    return '<a href="' . $url . '" ' . $id . ' ' . $class . ' ' . $style . '  >' . $text . '</a>';
                }
            }

            return '';
        }


        /**
        * Shortcode for Show Edit ClientPage content
        **/
        function shortcode_edit_portal_page( $atts, $contents = null ) {
            $user_id = $this->cc_checking_page_access();

            wp_register_script( 'wpc_edit_portal_page', $this->plugin_url . 'js/pages/wpc_edit_portal_page.js' );
            wp_enqueue_script( 'wpc_edit_portal_page', false, array(), false, true );

            ob_start();
            include( $this->plugin_dir . 'includes/user/edit_clientpage.php' );
            $new_content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            return $new_content;
        }


        /**
        * Shortcode for Show NoAccessPage content
        **/
        function shortcode_error_image() {
            ob_start();
            if( is_user_logged_in() ) {
                if( isset( $_GET['type'] ) && 'approval' == $_GET['type'] ) {
                    echo '<img id="wpc_error_image" class="wpc_no_approved_image" src="' . $this->plugin_url . 'images/NoApproved.png" alt="" >' ;
                } else if ( isset( $_GET['type'] ) && 'verify_email' == $_GET['type'] ) {
                    if ( isset( $_GET['send'] ) ) {
                        $user_id = get_current_user_id();
                        $key = get_user_meta( $user_id, 'verify_email_key', true );

                        //make link
                        if ( $this->permalinks ) {
                            $link = get_home_url() . '/portal/acc-activation/' . $key ;
                        } else {
                            $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                        }

                        $args = array( 'client_id' => $user_id, 'verify_url' => $link );
                        $userdata = get_userdata( $user_id );

                        //send email
                        $this->cc_mail( 'new_client_verify_email', $userdata->user_email, $args, 'new_client' );
                        do_action( 'wp_client_redirect', add_query_arg( array( 'type' => 'verify_email', 'sent' => 1 ), remove_query_arg( array( 'send' ) ) ) );
                    exit;
                    } elseif ( isset( $_GET['sent'] ) ) {
                        _e( 'Email sent successfully', WPC_CLIENT_TEXT_DOMAIN ) ;
                    } else {
                        _e( 'Please verify email address', WPC_CLIENT_TEXT_DOMAIN ) ;
                        echo '<br /><br /><a href="' . add_query_arg( array( 'send' => 1 ) ) . '">' . __( 'Resend email for verification', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
                    }
                } else {
                    echo '<img id="wpc_error_image" class="wpc_no_access_image" src="' . $this->plugin_url . 'images/NoAccess.png" alt="" >' ;
                }
            }
            $content_image = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            return $content_image;
        }


        /**
        * Shortcode for Show NoAccessPage content
        **/
        function shortcode_errors() {
            ob_start();
            if( is_user_logged_in() ) {
                if( isset( $_GET['type'] ) && 'approval' == $_GET['type'] ) {
                    echo '<img id="wpc_error_image" class="wpc_no_approved_image" src="' . $this->plugin_url . 'images/NoApproved.png" alt="" >' ;
                } else if ( isset( $_GET['type'] ) && 'verify_email' == $_GET['type'] ) {
                    if ( isset( $_GET['send'] ) ) {
                        $user_id = get_current_user_id();
                        $key = get_user_meta( $user_id, 'verify_email_key', true );

                        //make link
                        if ( $this->permalinks ) {
                            $link = get_home_url() . '/portal/acc-activation/' . $key ;
                        } else {
                            $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                        }
                        $args = array( 'client_id' => $user_id, 'verify_url' => $link );
                        $userdata = get_userdata( $user_id );

                        //send email
                        $this->cc_mail( 'new_client_verify_email', $userdata->user_email, $args, 'new_client' );
                        do_action( 'wp_client_redirect', add_query_arg( array( 'type' => 'verify_email', 'sent' => 1 ), remove_query_arg( array( 'send' ) ) ) );
                    exit;
                    } elseif ( isset( $_GET['sent'] ) ) {
                        _e( 'Email sent successfully', WPC_CLIENT_TEXT_DOMAIN ) ;
                    } else {
                        _e( 'Please verify email address', WPC_CLIENT_TEXT_DOMAIN ) ;
                        echo '<br /><br /><a href="' . add_query_arg( array( 'send' => 1 ) ) . '">' . __( 'Resend email for verification', WPC_CLIENT_TEXT_DOMAIN ) . '</a>' ;
                    }
                } else {
                    echo '<img id="wpc_error_image" class="wpc_no_access_image" src="' . $this->plugin_url . 'images/NoAccess.png" alt="" >' ;
                }
            }
            $content_image = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }
            return $content_image;
        }


        /**
        * Shortcode for Redirect to login or HUB page
        **/
        function shortcode_redirect_on_login_hub( $atts, $contents = null ) {
            if ( is_user_logged_in() ) {
                //on HUB
                do_action( 'wp_client_redirect', $this->cc_get_slug( 'hub_page_id' ) );
                exit;

            }
            //on login form
            do_action( 'wp_client_redirect', $this->cc_get_login_url() );
            exit;
        }


        /* Added By DAC */
        function shortcode_hub_page_template( $atts, $contents = null ) {

            if( is_user_logged_in() ) {
                $client_id = get_current_user_id();
            } else {
                return '';
            }
            $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );
            $default_template = $this->get_id_simple_temlate();
            $template = '';
            foreach ( $wpc_ez_hub_templates as $key => $tpl ) {
                if( isset( $tpl['is_default'] ) && 1 == $tpl['is_default'] ) $default_template = $key;
            }

            foreach( $wpc_ez_hub_templates  as $key => $values ) {

                //check individual assign
                //$user_ids = ( isset( $values['clients_ids'] ) && is_array( $values['clients_ids'] ) ) ? $values['clients_ids'] : array();
                $user_ids = $this->cc_get_assign_data_by_object( 'ez_hub', $key, 'client' );

                if ( in_array( $client_id, $user_ids ) ) {
                    $template = $key;
                    break;
                }

                //check Circles assign
                if ( '' == $template ) {
                    $user_ids = array();
                    //get clients from Client Circles
                    $groups_ids = $this->cc_get_assign_data_by_object( 'ez_hub', $key, 'circle' );
                    if ( is_array( $groups_ids ) && 0 < count( $groups_ids ) )
                        foreach( $groups_ids as $group_id ) {
                            $user_ids = array_merge( $user_ids, $this->cc_get_group_clients_id( $group_id ) );
                        }

                    if ( is_array( $user_ids ) && 0 < count( $user_ids ) )
                        $user_ids = array_unique( $user_ids );

                    if ( in_array( $client_id, $user_ids ) ) {
                        $template = $key;
                        continue;
                    }
                }

            }

            if ( '' == $template )
                $template = $default_template ;

            $hub_content = $tabs_content =  '' ;

            if( $template ) {

                $handle = fopen( $this->get_upload_dir( 'wpclient/_hub_templates/' ) . $template . '_hub_content.txt', 'rb' );
                if ( $handle !== false ) {
                    rewind( $handle ) ;
                    while ( !feof( $handle ) ) {
                        $hub_content .= fread( $handle, 8192 );
                    }
                }
                fclose( $handle );

                $handle = fopen( $this->get_upload_dir( 'wpclient/_hub_templates/' ) . $template . '_hub_tabs_content.txt', 'rb' );
                if ( $handle !== false ) {
                    rewind( $handle ) ;
                    while ( !feof( $handle ) ) {
                        $tabs_content .= fread( $handle, 8192 );
                    }
                }
                fclose( $handle );
            }


            if( !isset( $wpc_ez_hub_templates[$template]['type'] ) || ( isset( $wpc_ez_hub_templates[$template]['type'] ) && 'advanced' == $wpc_ez_hub_templates[$template]['type'] ) ) {
                $contents = $tabs_content;
            } elseif( isset( $wpc_ez_hub_templates[$template]['type'] ) && 'simple' == $wpc_ez_hub_templates[$template]['type'] ) {
                $contents = $tabs_content;
            } elseif( isset( $wpc_ez_hub_templates[$template]['type'] ) && 'ez' == $wpc_ez_hub_templates[$template]['type'] ) {
                if ( strpos( $hub_content, '{ez_hub_bar}') === false ) {
                    $contents = $hub_content . $tabs_content;
                } else {
                    $contents = str_replace( '{ez_hub_bar}', $tabs_content, $hub_content );
                }
            }




            if ( isset( $wpc_ez_hub_templates[$template]['general']['scheme'] ) && '' != $wpc_ez_hub_templates[$template]['general']['scheme'] ) {
                $scheme_key = $wpc_ez_hub_templates[$template]['general']['scheme'];
                $uploads = wp_upload_dir();
                if ( file_exists( $uploads['basedir'] . '/wpc_custom_style_' . $scheme_key . '.css' ) ) {
                    wp_register_style( 'wpc_custom_style_' . $scheme_key, $uploads['baseurl'] . '/wpc_custom_style_' . $scheme_key . '.css' );
                    wp_enqueue_style( 'wpc_custom_style_' . $scheme_key, false, array(), false, true );
                }
            }


            wp_enqueue_script( 'jquery-ui-tabs', false, array(), false, true );

            if( isset( $wpc_ez_hub_templates[$template]['type'] ) && 'ez' == $wpc_ez_hub_templates[$template]['type'] ) {

                wp_register_script( 'wp-client-dropdown', $this->plugin_url . 'js/bootstrap/js/bootstrap.min.js' );
                wp_enqueue_script( 'wp-client-dropdown', false, array(), false, true  );

                wp_register_script( 'wp-client-ez_hub_bar', $this->plugin_url . 'js/pages/ez_hub_bar.js' );
                wp_enqueue_script( 'wp-client-ez_hub_bar', false, array(), false, true );

                wp_register_style( 'wp-client-dropdown-style', $this->plugin_url . 'js/bootstrap/css/bootstrap.min.css' );
                wp_enqueue_style( 'wp-client-dropdown-style', false, array(), false, true );

                wp_register_style( 'wp-client-ez-hub-bar-style', $this->plugin_url . 'css/ez_hub_bar.css' );
                wp_enqueue_style( 'wp-client-ez-hub-bar-style', false, array(), false, true );

            }


            $args = array( 'client_id' => $client_id );
            return do_shortcode( $this->cc_replace_placeholders( $contents, $args, 'hub_page' ) );
        }


        /*
        * Shortcode
        */
        function shortcode_client_managers( $atts, $contents = null ) {

            return '';
        }



        /*
        * Shortcode start payment steps
        */
        function payment_process_func( $atts, $contents = null ) {

            global $wpc_payments_core, $wpc_gateway_active_plugins, $wpdb;

            //load gateways just on payment page
            $wpc_payments_core->load_gateway_plugins();

            add_filter( 'comments_open', create_function( '', 'return false;' ) , 99 );
            add_filter( 'comments_close_text', create_function( '', 'return "";' ) , 99 );
            add_filter( 'comments_array', create_function( '', 'return array();' ) , 99 );

            $order_id  = get_query_var( 'wpc_order_id' ) ? get_query_var( 'wpc_order_id' ) : 0;
            $step = get_query_var( 'wpc_page_value' ) ? get_query_var( 'wpc_page_value' ) : 2;

            if ( !$order_id ) {
                $this->cc_js_redirect( get_home_url() );
            }

            $order = $wpc_payments_core->get_order_by( $order_id, 'order_id' );

            switch( $step ) {
                case 2:

                    $function_activate_gateways = apply_filters( 'wpc_payment_get_activate_gateways_' . $order['function'], array() );

                    if ( is_array( $wpc_gateway_active_plugins ) && count( $wpc_gateway_active_plugins )  ) {
                        $i = 0;
                        foreach( $wpc_gateway_active_plugins as $gateway_plugin ) {
                            if ( !in_array( $gateway_plugin->plugin_name, $function_activate_gateways ) ) {
                                unset( $wpc_gateway_active_plugins[$i] );
                            } elseif( isset( $order['payment_type'] ) && 'recurring' == $order['payment_type'] ) {
                                //clear gateways without recurring
                                if ( !isset( $gateway_plugin->recurring ) || true !== $gateway_plugin->recurring ) {
                                    unset( $wpc_gateway_active_plugins[$i] );
                                }
                            }



                            $i++;
                        }
                    }

                    $selected_gateway = '';
                    if ( is_array( $wpc_gateway_active_plugins ) && 1 == count( $wpc_gateway_active_plugins ) ) {
                        $wpc_gateway_active_plugins = array_values( $wpc_gateway_active_plugins );
                        $selected_gateway = $wpc_gateway_active_plugins[0]->plugin_name;
                    } elseif( isset( $_POST['wpc_choose_gateway'] ) && !empty( $_POST['wpc_choose_gateway'] ) ) {
                        foreach( $wpc_gateway_active_plugins as $plugin ) {
                            if( $plugin->plugin_name == $_POST['wpc_choose_gateway'] ) {
                                $selected_gateway = $plugin->plugin_name;
                                break;
                            }
                        }
                    }


                    // gateway selected
                    if ( !empty( $selected_gateway ) ) {
                        $wpc_payments_core->update_order_gateway( $order['id'], $selected_gateway );

                        //make link
                        if ( $this->permalinks ) {
                            $url = $this->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-3/';
                        } else {
                            $url = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 3 ), get_home_url() );
                        }

                        $this->cc_js_redirect( $url );
                    }

                    break;

            }

            return $wpc_payments_core->payment_step_content( $order, $step );


        }





    //end class
    }
}

?>
