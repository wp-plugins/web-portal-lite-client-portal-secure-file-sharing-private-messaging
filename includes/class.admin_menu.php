<?php


if ( !class_exists( "WPC_Client_Admin_Menu" ) ) {

    class WPC_Client_Admin_Menu extends WPC_Client_Admin_Common {

        var $plugin_submenus;

        /**
        * Menu constructor
        **/
        function menu_construct() {

            //admin menu
            add_action( 'admin_menu', array( &$this, 'adminmenu' ) );
            add_action( 'adminmenu', array( &$this, 'add_subsubmenu' ) );
            add_action( 'admin_menu', array( &$this, 'hide_add_new_custom_type' ) );
            add_action( 'in_admin_header', array( &$this, 'return_admin_submenu' ) );
            add_action( 'admin_body_class', array( &$this, 'hide_admin_submenu' ) );

            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'admin_menu_change_capabilities' ) );

        }


        function add_subsubmenu() {
            global $submenu;
            $array_parent_slug = array();
            if ( isset( $this->plugin_subsubmenus ) ) {
                foreach ( $this->plugin_subsubmenus as $key => $values ) {
                    if ( !isset( $values['capability'] ) || ( isset( $values['capability'] ) && 'yes' == $values['capability'] ) ) {
                        $array_parent_slug[ $values['parent_slug'] ][] = $values;
                    }
                }
            }
            wp_enqueue_script( 'add-subsubmenu', $this->plugin_url . 'js/subsubmenu.js' );
            wp_localize_script( 'add-subsubmenu', 'MySubsubmenu', $array_parent_slug );
        }


        function adminmenu() {
            global $current_user;

            $cap = "manage_options";
            $manager_cap = "manage_options";

            $this->plugin_submenus = array(
                'wpclients_clients'    => array(
                    'page_title'        => $this->custom_titles['client']['p'],
                    'menu_title'        => $this->custom_titles['client']['p'],
                    'slug'              => 'wpclient_clients',
                    'capability'        => $manager_cap,
                    'function'          => array( &$this, 'wpc_clients_func' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 5,
                ),
                'wpclients_managers'    => array(
                    'page_title'        => $this->custom_titles['manager']['p'],
                    'menu_title'        =>  '<span class="wpc_pro_menu_grey">' . $this->custom_titles['manager']['p'] . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'slug'              => 'wpclients_pro_features#managers',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_pro_features' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 10,
                ),
                'wpclients_admins'    => array(
                    'page_title'        => $this->custom_titles['admin']['p'],
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . $this->custom_titles['admin']['p'] . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'slug'              => 'wpclients_pro_features#admins',
                    'capability'        => "manage_options",
                    'function'          => array( &$this, 'wpclients_pro_features' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 10,
                ),
                'add_client_page'       => array(
                    'page_title'        => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'menu_title'        => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'slug'              => 'add_client_page',
                    'capability'        => $manager_cap,
                    'function'          => array( &$this, 'add_client_page_func' ),
                    'hidden'            => true,
                    'real'              => true,
                    'order'             => 60,
                ),
                'wpclients_templates'   => array(
                    'page_title'        => __( 'Templates', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'Templates', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_templates',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_templates' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 200,
                ),
                'wpclients_customize'   => array(
                    'page_title'        => __( 'Customize', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'Customize', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_customize',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_customize' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 195,
                ),
                'wpclients_files'       => array(
                    'page_title'        => __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'File Sharing', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'slug'              => 'wpclients_pro_features#file_sharing',
                    'capability'        => $manager_cap,
                    'function'          => array( &$this, 'wpclients_pro_features' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 70,
                ),
                'wpclients_permissions'       => array(
                    'page_title'        => __( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_permissions',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_permissions' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 0,
                ),
                'wpclients_groups'      => array(
                    'page_title'        => $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'],
                    'menu_title'        => $this->custom_titles['circle']['p'],
                    'slug'              => 'wpclients_groups',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_groups' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 20,
                ),
                'wpclients_extensions'  => array(
                    'page_title'        => __( 'Extensions', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'Extensions', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_extensions',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_extensions' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 209,
                ),
                'wpclients_settings'    => array(
                    'page_title'        => __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'Settings', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_settings',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_settings' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 210,
                ),
                'wpclients_help'        => array(
                    'page_title'        => sprintf( __( '%s Wordpress Client Management Portal | Documentation & Tips', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ),
                    'menu_title'        => __( 'Help', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'wpclients_help',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_help' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 220,
                ),
                'wpclients_pro_features' => array(
                    'page_title'        => __( 'Pro Features', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'],
                    'menu_title'        => '<span class="wpc_pro_features_menu_item"><span>Pro</span> Features</span>',
                    'slug'              => 'wpclients_pro_features',
                    'capability'        => $cap,
                    'function'          => array( &$this, 'wpclients_pro_features' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 219,
                ),
                'wpclients_messages'    => array(
                    'page_title'        => __( 'Private Messaging', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Private Messaging', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'slug'              => 'wpclients_pro_features#private_messaging',
                    'capability'        => $manager_cap,
                    'function'          => array( &$this, 'wpclients_pro_features' ),
                    'hidden'            => false,
                    'real'              => true,
                    'order'             => 80,
                ),
                'hub_pages'             => array(
                    'page_title'        => __( 'HUB Pages', WPC_CLIENT_TEXT_DOMAIN ),
                    'menu_title'        => __( 'HUB Pages', WPC_CLIENT_TEXT_DOMAIN ),
                    'slug'              => 'edit.php?post_type=hubpage',
                    'capability'        => $manager_cap,
                    'function'          => '',
                    'hidden'            => false,
                    'real'              => false,
                    'order'             => 40,
                ),
                'client_pages'          => array(
                    'page_title'        => $this->custom_titles['portal']['p'],
                    'menu_title'        => $this->custom_titles['portal']['p'],
                    'slug'              => 'edit.php?post_type=clientspage',
                    'capability'        => $manager_cap,
                    'function'          => '',
                    'hidden'            => false,
                    'real'              => false,
                    'order'             => 50,
                ),
                'separator_0'           => array(
                    'page_title'        => '',
                    'menu_title'        => '- - - - - - - - - -',
                    'slug'              => '#',
                    'capability'        => $cap,
                    'function'          => '',
                    'hidden'            => false,
                    'real'              => false,
                    'order'             => 1,
                ),
                'separator_1'           => array(
                    'page_title'        => '',
                    'menu_title'        => '- - - - - - - - - -',
                    'slug'              => '#',
                    'capability'        => $cap,
                    'function'          => '',
                    'hidden'            => false,
                    'real'              => false,
                    'order'             => 30,
                ),
                'separator_3'           => array(
                    'page_title'        => '',
                    'menu_title'        => '- - - - - - - - - -',
                    'slug'              => '#',
                    'capability'        => $cap,
                    'function'          => '',
                    'hidden'            => false,
                    'real'              => false,
                    'order'             => 190,
                ),

            );

            $subsubmenu = array(
                array(
                    'parent_slug'       => 'admin.php?page=wpclient_clients',
                    'menu_title'        => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ),
                    'capability'        => ( current_user_can( 'wpc_add_clients' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclient_clients&tab=add_client',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclient_clients',
                    'menu_title'        => __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_restore_clients' ) || current_user_can( 'wpc_delete_clients' ) || current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclient_clients&tab=archive',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclient_clients',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . sprintf( __( '%s\'s %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['staff']['s']  ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_pro_features#client_staff',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclient_clients',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . sprintf( __( '%s Add', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['staff']['s'] ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_pro_features#client_staff',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclient_clients',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_pro_features#custom_fields',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_settings',
                    'menu_title'        => sprintf( __( '%s/%s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['staff']['p'] ),
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_settings&tab=clients_staff',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_settings',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Capabilities', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_pro_features#settings',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_settings',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Custom Login', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => ( current_user_can( 'administrator' ) ) ? 'yes' : 'no',
                    'slug'              => 'admin.php?page=wpclients_pro_features#settings',
                    ),


                array(
                    'parent_slug'       => 'edit.php?post_type=clientspage',
                    'menu_title'        => sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=add_client_page',
                    ),
                array(
                    'parent_slug'       => 'edit.php?post_type=clientspage',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Categories', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=wpclients_pro_features#portal_category',
                    ),


                array(
                    'parent_slug'       => 'admin.php?page=wpclients_templates',
                    'menu_title'        => __( 'HUB Page Templates', WPC_CLIENT_TEXT_DOMAIN ),
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=wpclients_templates&tab=hubpage',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_templates',
                    'menu_title'        => sprintf( __( '%s Template', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=wpclients_templates&tab=portal_page',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_templates',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Emails Templates', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=wpclients_pro_features#templates',
                    ),
                array(
                    'parent_slug'       => 'admin.php?page=wpclients_templates',
                    'menu_title'        => '<span class="wpc_pro_menu_grey">' . __( 'Shortcodes Templates', WPC_CLIENT_TEXT_DOMAIN ) . '</span> <span class="wpc_pro_menu_text">Pro</span>',
                    'capability'        => 'yes',
                    'slug'              => 'admin.php?page=wpclients_pro_features#templates',
                    ),
                );



            $this->plugin_subsubmenus = apply_filters( 'wpc_client_add_subsubmenu', $subsubmenu );


            if ( $this->plugin['hide_extensions_menu'] ) {
                if ( isset( $this->plugin_submenus['wpclients_extensions'] ) ) {
                    unset( $this->plugin_submenus['wpclients_extensions'] );
                }
            }

            if ( $this->plugin['hide_help_menu'] ) {
                if ( isset( $this->plugin_submenus['wpclients_help'] ) ) {
                    unset( $this->plugin_submenus['wpclients_help'] );
                }
            }

            $this->plugin_submenus = apply_filters( 'wpc_client_admin_submenus', $this->plugin_submenus );

            @uasort( $this->plugin_submenus, array( &$this, 'sort_menu' ) );


            //add main menu and sub menu for WP Clients

            $client_hub = get_user_meta( $current_user->ID, 'wpc_cl_hubpage_id', true );

            if ( 0 < $client_hub && ( ( current_user_can( 'wpc_client' ) || current_user_can( 'wpc_staff' ) ) && !current_user_can( 'administrator' ) ) ) {
                add_menu_page( __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN ), __( 'My HUB Page', WPC_CLIENT_TEXT_DOMAIN ), 'wpc_client', 'wpclients2', array(&$this, 'wpclients_func2'), $this->plugin['icon_url'] );
            }

            //add main plugin menu
            add_menu_page( $this->plugin['title'] . ' Lite', $this->plugin['title']. ' <span style="color: #5FC2EE; font-style: italic;">Lite</span>', $manager_cap, 'wpclients', array(&$this, 'wpclients_func'), $this->plugin['icon_url'], '2,00000000002' );
            //add submenu
            if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {
                foreach ( $this->plugin_submenus as $key => $values ) {
                    if ( isset( $values['real'] ) && true == $values['real'] ) {
                        add_submenu_page( 'wpclients', $values['page_title'], $values['menu_title'], $values['capability'], $values['slug'], $values['function'] );
                    }
                }
            }
        }


        function hide_add_new_custom_type() {
            global $menu, $submenu;

            if ( isset( $submenu['wpclients'] ) ) {

                //temp menu for hide in future
                $GLOBALS['wpclients_temp_menu'] = array();
                if ( current_user_can( 'administrator' ) ) {
                    $submenu['wpclients'][0][0] = __( 'Dashboard', WPC_CLIENT_TEXT_DOMAIN );
                }

                $main_menu = $submenu['wpclients'][0];
                unset( $submenu['wpclients'] );
                $submenu['wpclients'][] = $main_menu;

                if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {
                    foreach ( $this->plugin_submenus as $key => $values ) {
                        if( $values['slug'] != $submenu['wpclients'][0][2] ) {
                            $submenu['wpclients'][] = array( $values['menu_title'], $values['capability'], $values['slug'], $values['page_title'] );
                        }
                    }
                }

                //add separaters
                $menu['2,00000000001'] = array( '', 'read', 'separator001', '', 'wp-menu-separator' );
                $menu['2,00000000003'] = array( '', 'read', 'separator003', '', 'wp-menu-separator02' );
            }


        }





        /*
        * Return admin submenu variable for display pages
        */
        function return_admin_submenu() {
            global $submenu;
            if ( isset( $GLOBALS['wpclients_temp_menu'] ) )
                $submenu['wpclients'] = $GLOBALS['wpclients_temp_menu'];
        }


        /*
        * Hide admin submenu from list of menu
        */
        function hide_admin_submenu() {
            global $menu, $submenu, $parent_file;

            //hide some menu
            if ( is_array( $this->plugin_submenus ) && count( $this->plugin_submenus ) ) {

                $n = count( $submenu['wpclients'] );

                foreach ( $this->plugin_submenus as $key => $values ) {
                    if ( isset( $values['hidden'] ) && true == $values['hidden'] ) {

                        for( $i = 0; $i < $n; $i++ ) {
                            if ( isset( $submenu['wpclients'][$i] ) && in_array( $values['slug'], $submenu['wpclients'][$i] ) )
                                unset( $submenu['wpclients'][$i] );
                        }
                    }
                }
            }

            if ( isset( $submenu['wpclients'] ) ) {
                if ( current_user_can( 'administrator' ) ) {

                    if ( ( isset( $_GET['post_type'] ) && ( 'hubpage' == $_GET['post_type'] || 'clientspage' == $_GET['post_type'] ) )
                            || 'edit.php?post_type=hubpage' == $parent_file
                            || 'edit.php?post_type=clientspage' == $parent_file ) {

                        add_filter( 'parent_file',  array( &$this, 'change_parent_file' ), 200 );
                    }

                }
            }
        }


        /*
        * Return admin submenu variable for display pages
        */
        function change_parent_file( $parent_file ) {
            global $pagenow;
            $pagenow = 'admin.php';
            $parent_file = 'wpclients';
            return $parent_file;
        }




        /*
        * sorting Menu array by order
        */
        function sort_menu( $a, $b ) {
            //name of key for sort
            $key = 'order';

            if ( strtolower( $a[$key] ) == strtolower( $b[$key] ) )
                return 0;

            return ( strtolower( $a[$key] ) < strtolower( $b[$key] ) ) ? -1 : 1;
        }



        /*
        * display extensions page
        */
        function wpclients_extensions() {
            include 'admin/extensions.php';
        }


        /*
        * display settings page
        */
        function wpclients_settings() {
            include 'admin/settings.php';
        }


        function wpclients_func() {
            include 'admin/dashboard.php';
        }


        function wpc_clients_func() {

            if ( isset( $_GET['tab'] ) )
                $tab = $_GET['tab'];
            else
                $tab = 'clients';

            switch( $tab ) {
                case 'clients':
                    include 'admin/clients.php';
                    break;

                case 'add_client':
                    include 'admin/addclient.php';
                    break;

                case 'edit_client':
                    include 'admin/editclient.php';
                    break;

                case 'archive':
                        include 'admin/clients_archive.php';
                    break;
            }
        }


        function managers_func() {
            if ( isset( $_GET['tab'] ) && ( 'add' == $_GET['tab'] || 'edit' == $_GET['tab'] ) )
                include 'admin/manager_edit.php';
            else
                include 'admin/managers.php';
        }

        function admins_func() {
            if ( isset( $_GET['tab'] ) && ( 'add' == $_GET['tab'] || 'edit' == $_GET['tab'] ) )
                include 'admin/admin_edit.php';
            else
                include 'admin/admins.php';
        }

        function wpclients_messages_func() {
            if ( isset( $_GET['tab'] ) && 'chain' == $_GET['tab'] )
                include 'admin/messages_chain.php';
            else
                include ('admin/messages.php');
        }

        function add_client_page_func() {
            include 'admin/addclientpage.php';
        }

        /*
        * templates functions
        */
        function wpclients_templates() {
            include 'admin/templates.php';
        }

        /*
        * templates functions
        */
        function wpclients_customize() {
            include 'admin/customize.php';
        }

        //page Files
        function wpclients_files() {

            if( isset( $_GET['tab'] ) && 'cat' == $_GET['tab'] ) {
                if( isset( $_GET['display'] ) && 'old' == $_GET['display'] ) {
                    include 'admin/files_cat_old.php';
                } else {
                    include 'admin/files_cat.php';
                }
            } elseif( isset( $_GET['tab'] ) && 'tags' == $_GET['tab'] ) {
                include 'admin/file_tags.php';
            }elseif( isset( $_GET['tab'] ) && 'download_log' == $_GET['tab'] ) {
                include 'admin/files_download_log.php';
            } else {
                include 'admin/files.php';
            }

        }

        //page Files
        function wpclients_permissions() {
            include 'admin/permissions.php';
        }

        //page Client Circles
        function wpclients_groups() {
            include 'admin/groups.php';
        }


        //page Pro features
        function wpclients_pro_features() {
            include 'admin/_pro_features.php';
        }


        //page Help
        function wpclients_help() {

            $url = $this->plugin_url . 'images/logo2.png';
            $content = $this->remote_download("https://webportalhq.com/_remote/clients/help.txt");

            echo '<div class="wpc_clear"></div>';

            echo $this->get_plugin_logo_block();

            echo "<h3>" . sprintf( __( '%s Wordpress Client Management Portal | Documentation & Tips', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ) . "</h3>";
            echo $content;
        }


        /*
        * redirect client on HUB from admin menu
        */
        function wpclients_func2() {
            global $current_user;

            $client_hub = get_user_meta( $current_user->ID, 'wpc_cl_hubpage_id', true );

            if( 0 < $client_hub ) {
                echo "You will be redirected to the page in a few seconds, if it doesn't redirect , please click <a href='" . $this->cc_get_slug( 'hub_page_id' ) . "'>here</a>";
                echo "<script type='text/javascript'>document.location='" . $this->cc_get_slug( 'hub_page_id' ) . "';</script>";
            }
        }


        /*
        *  Change capabilities for admin submenu
        */
        function admin_menu_change_capabilities( $plugin_submenus ) {

            return $plugin_submenus;
        }


        /**
         * Gen tabs manu
         */
        function gen_tabs_menu( $page = 'clients' ) {
            global $wpdb;

            $tabs = '';
            $active = '';

            switch( $page ) {

                case 'clients':
                    $not_approved_clients   = $this->cc_get_excluded_clients( 'to_approve' );
                    $not_approved_staff     = array();
                    $archive_client           = $this->cc_get_excluded_clients( 'archive' );

                    $active = ( !isset( $_GET['tab'] ) && isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] ) ? 'class="active"' : '';
                    $tabs .= '<li id="menu_clients" ' . $active . ' ><a href="admin.php?page=wpclient_clients" >' . $this->custom_titles['client']['p'] . '</a></li>';

                    if ( current_user_can( 'wpc_add_clients' ) || current_user_can( 'administrator' ) ) {
                        $active = ( isset( $_GET['tab'] ) && 'add_client' == $_GET['tab'] ) ? 'class="active"' : '';
                        $tabs .= '<li id="menu_add_client" ' . $active . ' ><a href="admin.php?page=wpclient_clients&tab=add_client" >' . sprintf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</a></li>';
                    }

                    if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'wpc_restore_clients' ) || current_user_can( 'wpc_delete_clients' ) || current_user_can( 'administrator' ) ) {
                        $active = ( isset( $_GET['tab'] ) && 'archive' == $_GET['tab'] ) ? 'class="active"' : '';
                        $tabs .= '<li id="menu_archive_clients" ' . $active . ' ><a href="admin.php?page=wpclient_clients&tab=archive" >' . __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) . ' ('. count( $archive_client) . ')</a></li>';
                    }

                    if ( current_user_can( 'wpc_approve_clients' ) || current_user_can( 'administrator' ) ) {
                        $active = ( isset( $_GET['tab'] ) && 'approve' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_approve_clients" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#client_approve" >' . sprintf( __( 'Approve %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) . ' ('. count( $not_approved_clients ) . ')<span class="wpc_pro_tab_text"> Pro</span></a></li>';
                    }

                     //just for admin
                    if ( current_user_can( 'administrator' ) ) {

                        $active = ( isset( $_GET['tab'] ) && 'convert' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_convert_users" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#convert" >' . __( 'Convert Users', WPC_CLIENT_TEXT_DOMAIN ). '<span class="wpc_pro_tab_text"> Pro</span></a></li>';

                        $active = ( isset( $_GET['tab'] ) && 'staff' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_staff" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#client_staff" >' . $this->custom_titles['client']['s'] . "'s " . $this->custom_titles['staff']['s'] . '<span class="wpc_pro_tab_text"> Pro</span></a></li>';

                        $active = ( isset( $_GET['tab'] ) && 'staff_add' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_add_staff" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#client_staff" >' . $this->custom_titles['staff']['s'] . ' ' . __( 'Add', WPC_CLIENT_TEXT_DOMAIN ) . '<span class="wpc_pro_tab_text"> Pro</span></a></li>';

                        $active = ( isset( $_GET['tab'] ) && 'staff_approve' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_staff_approve" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#client_staff" >' . $this->custom_titles['staff']['s'] . ' ' . __( 'Approve', WPC_CLIENT_TEXT_DOMAIN ) . ' ('. count( $not_approved_staff ) . ')<span class="wpc_pro_tab_text"> Pro</span></a></li>';

                        $active = ( isset( $_GET['tab'] ) && 'custom_fields' == $_GET['tab'] ) ? 'class="active' : 'class="';
                        $tabs .= '<li id="menu_custom_fields" ' . $active . ' wpc_pro_tab" ><a href="admin.php?page=wpclients_pro_features#custom_fields" >' . __( 'Custom Fields', WPC_CLIENT_TEXT_DOMAIN ) . '<span class="wpc_pro_tab_text"> Pro</span></a></li>';
                    }


                    break;
            }

            return $tabs;
        }

    //end class
    }

}

?>