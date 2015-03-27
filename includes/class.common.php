<?php
//{{FUNC_NOT_ENC:cc_recursive_strip_slashes}}


if ( !class_exists( "WPC_Client_Common" ) ) {

    class WPC_Client_Common {

        var $plugin_dir;
        var $plugin_url;
        var $plugin;
        var $custom_titles;
        var $mail_sender = false;
        var $cache_settings = array();
        var $permalinks = false;
        var $upload_dir = null;

        var $default_titles = array(
            'client'    => array( 's' => 'Client', 'p' => 'Clients' ),
            'circle'    => array( 's' => 'Circle', 'p' => 'Circles' ),
            'manager'   => array( 's' => 'Manager', 'p' => 'Managers' ),
            'staff'     => array( 's' => 'Staff', 'p' => 'Staff' ),
            'admin'     => array( 's' => 'Admin', 'p' => 'Admins' ),
            'portal'     => array( 's' => 'Portal Page', 'p' => 'Portal Pages' )
        );


        /**
        * Main constructor
        **/
        function common_construct() {

            //setup proper directories
            if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php' ) ) {
                $this->plugin_dir = WPMU_PLUGIN_DIR . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging/';
                $this->plugin_url = WPMU_PLUGIN_URL . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging/';
            } else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php' ) ) {
                $this->plugin_dir = WP_PLUGIN_DIR . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging/';
                $this->plugin_url = WP_PLUGIN_URL . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging/';
            } else if ( defined('WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php' ) ) {
                $this->plugin_dir = WP_PLUGIN_DIR;
                $this->plugin_url = WP_PLUGIN_URL;
            }

            if ( get_option( 'permalink_structure' ) )
                $this->permalinks = true;

            if( !defined( 'WPC_CLIENT_EXTERNAL_FONTS_DIR' ) ) {
                 define('WPC_CLIENT_EXTERNAL_FONTS_DIR', 'wpclient/fonts/' );
            }

            //check on SSL
            if ( function_exists( 'set_url_scheme' ) ) {
                $this->plugin_url = set_url_scheme( $this->plugin_url );
            }

            //get custom titles
            //tocheck
            $wpc_custom_titles = $this->cc_get_settings( 'custom_titles' );
            $this->custom_titles = ( is_array( $wpc_custom_titles ) ) ? array_merge( $this->default_titles, $wpc_custom_titles ) : $this->default_titles;
            unset( $wpc_custom_titles );

            //set plugin data
            $this->_set_plugin_data();


            add_action( 'init', array( &$this, '_create_post_type' ) );

            //add/update client
            add_action( 'wp_clients_update', array( &$this, 'cc_client_update_func' ) );

            add_action( 'plugins_loaded', array( &$this, 'include_payment_core' ), 10 );

            add_action( 'wp_client_redirect', array( &$this, 'cc_js_redirect' ) );

            add_action( 'plugins_loaded', array( &$this, '_load_textdomain' ) );

            //login\logout redirect
            add_filter( 'login_redirect', array( &$this, 'cc_login_redirect_rules' ), 100, 3 );
            add_action( 'wp_logout', array( &$this, 'cc_logout_redirect_rules' ), 10 );


            //add query vars
            add_filter( 'query_vars', array( &$this, '_insert_query_vars' ) );

            //add rewrite rules
            add_filter( 'rewrite_rules_array', array( &$this, '_insert_rewrite_rules' ) );


            add_filter( 'woocommerce_disable_admin_bar', array( &$this, 'cc_woocommerce_admin_access_fix' ), 10, 1 );
            add_filter( 'woocommerce_prevent_admin_access', array( &$this, 'cc_woocommerce_admin_prevent_access_fix' ), 10, 1 );

            add_action( 'admin_enqueue_scripts', array( &$this, 'include_admin_login_js' ), 99 );
            add_action( 'wp_enqueue_scripts', array( &$this, 'include_admin_login_js' ), 99 );

            add_action( 'clear_auth_cookie', array( &$this, 'clear_login_key_cookie' ) );
        }


        function clear_login_key_cookie() {
            $secure_logged_in_cookie = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
            setcookie( "wpc_key", '', time() - 1, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
        }

        function include_payment_core() {

            //include payments core
            if ( defined( 'WPC_CLIENT_PAYMENTS' ) ) {
                include_once $this->plugin_dir . 'includes/payments_core.php';
            }
        }


        function include_admin_login_js() {
            global $wpdb;
            if( !empty( $_COOKIE['wpc_key'] ) && is_user_logged_in() ) {
                $key = $_COOKIE['wpc_key'];
                $user_data = $wpdb->get_row( $wpdb->prepare( "SELECT umeta_id, user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'wpc_client_admin_secure_data' AND meta_value LIKE '%s'", '%"' . md5( $key ) . '"%' ), ARRAY_A );
                if( isset( $user_data['user_id'] ) && user_can( $user_data['user_id'], 'wpc_admin_user_login') ) {
                    if( !empty( $user_data['meta_value'] ) ) {
                        $secure_array = unserialize( $user_data['meta_value'] );
                        if( isset( $secure_array['end_date'] ) && $secure_array['end_date'] > time() ) {
                            wp_enqueue_script( 'wpc_client_admin_login', $this->plugin_url . 'js/admin_relogin.js' );
                            wp_localize_script( 'wpc_client_admin_login', 'wpc_var', array(
                                'message' => sprintf( __( "Remaining %d minutes", WPC_CLIENT_TEXT_DOMAIN ), round( ( $secure_array['end_date'] - time() ) / 60 ) ),
                                'button_value' => __( "Return to admin panel", WPC_CLIENT_TEXT_DOMAIN ),
                                'ajax_url' => admin_url('admin-ajax.php'),
                                'secure_key' => wp_create_nonce( get_current_user_id() . $user_data['user_id'] )
                            ));
                        } else {
                            $wpdb->delete( $wpdb->usermeta,
                                array(
                                    'umeta_id' => $user_data['umeta_id']
                                )
                            );
                            $secure_logged_in_cookie = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
                            setcookie( "wpc_key", '', time() - 1, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
                        }
                    }
                }
            }
        }


        function password_protect_css_js() {
            global $wp_scripts;
            if( ( isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) && in_array( 'user-profile', $wp_scripts->queue ) ) ||
                ( isset( $_GET['page'] ) && 'wpclient_clients' == $_GET['page'] ) ||
                ( isset( $_GET['page'] ) && 'wpclients_admins' == $_GET['page'] ) ||
                ( isset( $_GET['page'] ) && 'wpclients_managers' == $_GET['page'] ) ||
                ( !defined( 'DOING_AJAX' ) && !is_admin()  )
            ) {
                wp_enqueue_script( 'password-strength-meter', false, array(), false, true );

                wp_register_script( 'wp-client-password-protect', $this->plugin_url . 'js/password_protect.js', array(), false, true );
                wp_enqueue_script( 'wp-client-password-protect' );
                wp_localize_script( 'wp-client-password-protect', 'wpc_text_var', array( 'pwsL10n' => array(
                    'empty' => __( "Strength Indicator", WPC_CLIENT_TEXT_DOMAIN ),
                    'short' => __("Too Short", WPC_CLIENT_TEXT_DOMAIN ),
                    'bad' => __("Bad Password", WPC_CLIENT_TEXT_DOMAIN ),
                    'good' => __("Good Password", WPC_CLIENT_TEXT_DOMAIN ),
                    'strong' => __("Strong Password", WPC_CLIENT_TEXT_DOMAIN ),
                    'mismatch' => __("Password Mismatch", WPC_CLIENT_TEXT_DOMAIN ),
                    'mixed_case' => __("Needs Mixed Case", WPC_CLIENT_TEXT_DOMAIN ),
                    'numbers' => __("Needs Numbers", WPC_CLIENT_TEXT_DOMAIN ),
                    'special_chars' => __("Needs Special Chars", WPC_CLIENT_TEXT_DOMAIN ),
                    'blacklist' => __("Password in Blacklist", WPC_CLIENT_TEXT_DOMAIN )
                )));

                $settings = $this->cc_get_settings( 'clients_staff' );

                if( isset( $settings['password_black_list'] ) && !empty( $settings['password_black_list'] ) ) {
                    $black_list = explode( "\n", str_replace( array( "\n\r", "\r\n", "\r" ), "\n", $settings['password_black_list'] ) );
                } else {
                    $black_list = array();
                }

                $min_length = ( isset( $settings['password_minimal_length'] ) && is_numeric( $settings['password_minimal_length'] ) && $settings['password_minimal_length'] > 0 ) ? $settings['password_minimal_length'] : 1;
                $hint_message = __( 'Hint - The password', WPC_CLIENT_TEXT_DOMAIN ) . ':<br />';
                if( $min_length > 1 ) {
                    $hint_message .= '<span class="wpc_requirement_min_length">- ' . sprintf( __( 'Should be at least %d characters long.', WPC_CLIENT_TEXT_DOMAIN ), $min_length ) . '</span><br />';
                }

                $strength = ( isset( $settings['password_strength'] ) && is_numeric( $settings['password_strength'] ) ) ? $settings['password_strength'] : 5;
                switch( $strength ) {
                    case '2':
                        $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Weak') ) . '</span><br />';
                        break;
                    case '3':
                        $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), _x('Medium', 'password strength') ) . '</span><br />';
                        break;
                    case '4':
                        $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Strong') ) . '</span><br />';
                        break;
                    default:
                        $hint_message .= '<span class="wpc_requirement_level">- ' . sprintf( __( 'Must trigger the %s level on the Strength indicator.', WPC_CLIENT_TEXT_DOMAIN ), __('Very weak') ) . '</span><br />';
                        break;
                }

                $mixed_case = isset( $settings['password_mixed_case'] ) ? $settings['password_mixed_case'] : 0;
                if( $mixed_case ) {
                    $hint_message .= '<span class="wpc_requirement_mixed_case">- ' . __( 'Should contain upper and lower case letters.', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
                }

                $numeric_digits = isset( $settings['password_numeric_digits'] ) ? $settings['password_numeric_digits'] : 0;
                if( $numeric_digits ) {
                    $hint_message .= '<span class="wpc_requirement_numeric_digits">- ' . __( 'Should contain numbers.', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
                }

                $special_chars = isset( $settings['password_special_chars'] ) ? $settings['password_special_chars'] : 0;
                if( $special_chars ) {
                    $hint_message .= '<span class="wpc_requirement_special_chars">- ' . __( 'Should contain special characters like ! " ? $ % ^ & ).', WPC_CLIENT_TEXT_DOMAIN ) . '</span><br />';
                }

                wp_localize_script( 'wp-client-password-protect', 'wpc_password_protect', array(
                    'blackList' => $black_list,
                    'min_length' => $min_length,
                    'strength' => $strength,
                    'mixed_case' => $mixed_case,
                    'numeric_digits' => isset( $settings['password_numeric_digits'] ) ? $settings['password_numeric_digits'] : 0,
                    'special_chars' => isset( $settings['password_special_chars'] ) ? $settings['password_special_chars'] : 0,
                    'hint_message' => $hint_message
                ));
            }
        }


        function cc_set_currency( $price, $currency, $echo = true ) {
            $wpc_currency = $this->cc_get_settings( 'currency' );
            $result = '';
            if( isset( $wpc_currency[ $currency ] ) && is_array( $wpc_currency[ $currency ] ) ) {
                if( isset( $wpc_currency[ $currency ]['align'] ) && 'right' == $wpc_currency[ $currency ]['align'] ) {
                    $result = $price . ( isset( $wpc_currency[ $currency ]['symbol'] ) ? $wpc_currency[ $currency ]['symbol'] : '' );
                } else {
                    $result = ( isset( $wpc_currency[ $currency ]['symbol'] ) ? $wpc_currency[ $currency ]['symbol'] : '' ) . $price;
                }
            }

            if( $echo ) {
                echo $result;
            } else {
                return $result;
            }
        }


        function cc_woocommerce_admin_access_fix( $option ) {

            return false;

        }

        function cc_woocommerce_admin_prevent_access_fix( $prevent_access ) {

            return false;

        }



        /*
        *  return label for file sizes
        */
        function format_bytes( $a_bytes) {

            if ($a_bytes < 1024) {
                return $a_bytes .' B';
            } elseif ($a_bytes < 1048576) {
                return round($a_bytes / 1024, 2) .' K';
            } elseif ($a_bytes < 1073741824) {
                return round($a_bytes / 1048576, 2) . ' MB';
            } elseif ($a_bytes < 1099511627776) {
                return round($a_bytes / 1073741824, 2) . ' GB';
            } elseif ($a_bytes < 1125899906842624) {
                return round($a_bytes / 1099511627776, 2) .' TB';
            } elseif ($a_bytes < 1152921504606846976) {
                return round($a_bytes / 1125899906842624, 2) .' PB';
            } elseif ($a_bytes < 1180591620717411303424) {
                return round($a_bytes / 1152921504606846976, 2) .' EB';
            } elseif ($a_bytes < 1208925819614629174706176) {
                return round($a_bytes / 1180591620717411303424, 2) .' ZB';
            } else {
                return round($a_bytes / 1208925819614629174706176, 2) .' YB';
            }
        }


        /*
        * get content
        */
        function remote_download( $url ) {

            $response = wp_remote_post( $url,
                array(
                    'method'        => 'POST',
                    'timeout'       => 45,
                    'redirection'   => 5,
                    'httpversion'   => '1.0',
                    'blocking'      => true,
                    'sslverify'     => false,
                    'headers'       => array(),
                    'cookies'       => array()
                )
            );

            if ( is_wp_error( $response ) ) {
                return 'Error #30303: ' . $response->get_error_message();
            }

            if ( isset( $response['body'] ) ) {
                return $response['body'];
            }

            return false;
        }


        /*
        *
        */
        function _set_plugin_data() {
            global $wp_version;

            if( version_compare( $wp_version, '3.8', '>=' ) ) {
                $this->plugin['logo_style'] = ".wpc_logo {
                    background: url( '" . $this->plugin_url . "images/page_header_gray.png' ) no-repeat transparent;
                    width: 625px;
                    height: 60px;
                }";
            } else {
                $this->plugin['logo_style'] = ".wpc_logo {
                    background: url( '" . $this->plugin_url . "images/page_header.png' ) no-repeat transparent;
                    width: 625px;
                    height: 60px;
                }";
            }
            //default values
            $this->plugin['title'] = 'Web Portal';
            $this->plugin['old_title'] = $this->plugin['title'];
            $this->plugin['logo_content'] = '';

            $this->plugin['icon_url'] = $this->plugin_url . 'client-icon.png';
            $this->plugin['hide_about_tab'] = 0;
            $this->plugin['hide_help_menu'] = 0;
            $this->plugin['hide_extensions_menu'] = 0;

        }

        /**
         * Adding the var for my-hub page
         **/
        function _insert_query_vars( $vars ) {

            array_push( $vars, 'wpc_page' );
            array_push( $vars, 'wpc_page_value' );

            if ( defined( 'WPC_CLIENT_PAYMENTS' ) ) {
                array_push( $vars, 'wpc_order_id' );
            }

            return $vars;
        }


        /**
         * Adding a new rule
         **/
        function _insert_rewrite_rules( $rules ) {
            $newrules = array();

            //varify email
            $newrules['portal/acc-activation/(.+?)/?$'] = 'index.php?wpc_page=acc_activation&wpc_page_value=$matches[1]';

            //edit portal page
            $newrules[$this->cc_get_slug( 'edit_portal_page_id', false, false ) . '/(.+?)/?$'] = 'index.php?wpc_page=edit_portal_page&wpc_page_value=$matches[1]';

            //portal page with pages
            $newrules[$this->cc_get_slug( 'portal_page_id', false, false ) . '/(.+?)/(\d*)/?$'] = 'index.php?wpc_page=portal_page&wpc_page_value=$matches[1]&page=$matches[2]';
            //portal page with pages
            $newrules[$this->cc_get_slug( 'portal_page_id', false, false ) . '/(.+?)/?$'] = 'index.php?wpc_page=portal_page&wpc_page_value=$matches[1]';

            //edit staff
            $newrules[$this->cc_get_slug( 'edit_staff_page_id', false, false ) . '/(\d*)/?$'] = 'index.php?wpc_page=edit_staff&wpc_page_value=$matches[1]';

            //preview for HUB
            $newrules[$this->cc_get_slug( 'hub_page_id', false, false ) . '/(\d*)/?$'] = 'index.php?wpc_page=hub_preview&wpc_page_value=$matches[1]';

            if ( defined( 'WPC_CLIENT_PAYMENTS' ) ) {
                //ipn handling for payment gateways
                $newrules['wpc-ipn-handler-url/(.+?)/?$'] = 'index.php?wpc_page=payment_ipn&wpc_page_value=$matches[1]';
                $newrules[$this->cc_get_slug( 'payment_process_page_id', false, false ) . '/(.+?)/step-(.+?)/?$'] = 'index.php?wpc_page=payment_process&wpc_order_id=$matches[1]&wpc_page_value=$matches[2]';
            }

            return $newrules + $rules;
        }


        /*
        * Get slug for wpc_page
        *
        * return slug for wpc_page
        */
        function cc_get_slug( $page = '', $with_end_slash = true, $full_url = true ) {

            if ( '' != $page ) {
                $wpc_pages = $this->cc_get_settings( 'pages' );

                if ( isset( $wpc_pages[$page] ) && 0 < $wpc_pages[$page] ) {
                    $post = get_post( $wpc_pages[$page] );
                    if ( isset( $post->post_name ) && '' != $post->post_name ) {
                        $url = '';
                        //parent exist
                        if ( 0 < $post->post_parent ) {
                            $parent = get_post( $post->post_parent );
                            $url = $parent->post_name . '/';
                        }

                        $url .= $post->post_name;

                        if ( $full_url ) {
                            if ( is_multisite() ) {
                                $url = get_home_url( get_current_blog_id() ) . '/' . $url;
                            } else {
                                if ( $this->permalinks ) {
                                    $url = get_home_url() . '/' . $url;
                                } else {
                                    $url = _get_page_link( $post );
                                }

                            }
                        }

                        $url = rtrim( $url, '/' );
                        if ( $with_end_slash && $this->permalinks ) {
                            $url = $url . '/';
                        }

                        return $url;

                    }
                }

            }

            return '';
        }


        /*
        * Register post types
        */
        function _create_post_type() {
            register_taxonomy( 'wpc_file_tags', 'file' );

            //hide admin bar for client\staff
            if ( ( current_user_can( 'wpc_client' ) ) && !current_user_can( 'manage_network_options' ) )  {
                $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );
                if ( !isset( $wpc_clients_staff['hide_admin_bar'] ) || 'yes' == $wpc_clients_staff['hide_admin_bar'] ) {
                    add_filter( 'show_admin_bar', create_function( '', 'return false;' ) );
                }
            }


            $portal_page_base = $this->cc_get_slug( 'portal_page_id', false, false );
            if ( '' == $portal_page_base ) {
                $portal_page_base = 'portal/portal-page';
            }

            //Clientpage (Portal page) post type
            $labels = array(
                'name'                  => $this->custom_titles['portal']['p'],
                'singular_name'         => $this->custom_titles['portal']['s'],
                'edit_item'             => sprintf( __('Edit %s Item', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                'view_item'             => sprintf( __('View %s Item', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                'search_items'          => sprintf( __('Search %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                'not_found'             => __('Nothing found', WPC_CLIENT_TEXT_DOMAIN ),
                'not_found_in_archive'    => __('Nothing found in Archive', WPC_CLIENT_TEXT_DOMAIN ),
                'parent_item_colon'     => ''
            );

            $args = array(
                'labels'                => $labels,
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'query_var'             => true,
                'show_in_menu'          => false,
                'show_in_admin_bar'     => false,
                'rewrite'               => false,
                'capability_type'       => 'clientpage',
                'capabilities'          => array( 'edit_posts' => 'edit_published_clientpages' ),
                'map_meta_cap'          => true,
                'hierarchical'          => true,
                'exclude_from_search'   => true,
                'menu_position'         => 145,
                'supports'              => array('title', 'editor', 'thumbnail', 'meta'),
                'rewrite'               => array( 'slug' => $portal_page_base, 'with_front' => false, 'pages' => false, ),
            );

            register_post_type('clientspage', $args);

            $hub_page_base = $this->cc_get_slug( 'hub_page_id', false, false );
            if ( '' == $hub_page_base ) {
                $hub_page_base = 'hub-page';
            }

            //HUB post type
            $labels = array(
                'name'                  => _x('HUB Pages', 'post type general name'),
                'singular_name'         => _x('HUB Page', 'post type singular name'),
                //'add_new'             => _x('Add New', 'Services item'),
                //'add_new_item'        => __('Add New Service'),
                'edit_item'             => __('Edit HUB Page Item', WPC_CLIENT_TEXT_DOMAIN ),
                //'new_item'            => __('New Service Item'),
                'view_item'             => __('View HUB Page Item', WPC_CLIENT_TEXT_DOMAIN ),
                'search_items'          => __('Search HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                'not_found'             => __('Nothing found', WPC_CLIENT_TEXT_DOMAIN ),
                'not_found_in_archive'  => __('Nothing found in Archive', WPC_CLIENT_TEXT_DOMAIN ),
                'parent_item_colon'     => ''
            );

            $capability_type = 'hubpage';
            $capabilities = array(
                'read_post'             => 'read_' . $capability_type,
                'publish_posts'         => 'publish_' . $capability_type . 's',
                'edit_post'             => 'edit_' . $capability_type,
                'edit_posts'            => 'edit_' . $capability_type . 's',
                'edit_others_posts'     => 'edit_others_' . $capability_type . 's',
                'read_private_posts'    => 'read_private_' . $capability_type . 's',
                'delete_post'           => 'delete_' . $capability_type
            );

            $args = array(
                'labels'                => $labels,
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'query_var'             => true,
                'show_in_menu'          => false,
                'show_in_admin_bar'     => false,
                'rewrite'               => false,
                'capability_type'       => 'hubpage',
                'capabilities'          => $capabilities,
                'hierarchical'          => true,
                'exclude_from_search'   => true,
                'supports'              => array('title', 'editor', 'thumbnail', 'meta'),
                'rewrite'               => array( 'slug' => $hub_page_base, 'with_front' => false, 'pages' => false ),
            );

            register_post_type( 'hubpage', $args );


        }


        /*
        * Checking access for page
        *
        * return int $client_id - client ID
        */
        function cc_checking_page_access() {
            global $wpc_client;

            //block not logged clients
            if ( !is_user_logged_in() )  {
                //Sorry, you do not have permission to see this page
                do_action( 'wp_client_redirect', $this->cc_get_login_url() );
                exit;
            }

            if ( isset( $this->current_plugin_page['client_id'] ) && 0 < $this->current_plugin_page['client_id'] )
                $client_id = $this->current_plugin_page['client_id'];
            else
                $client_id = get_current_user_id();

            //block not verify email
            $wpc_clients_staff = $wpc_client->cc_get_settings( 'clients_staff' );
            if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] && get_user_meta( $client_id, 'verify_email_key', true ) ) {
                do_action( 'wp_client_redirect', add_query_arg( array( 'type' => 'verify_email' ), $this->cc_get_slug( 'error_page_id' ) ) );
                exit;
            }

            //block not paid clients
            if ( '1' == get_user_meta( $client_id, 'wpc_need_pay', true ) ) {
                $wpc_ams_level = get_user_meta( $client_id, 'wpc_ams_level', true );

                if ( $wpc_ams_level && isset( $wpc_ams_level['redirect'] ) && '' != $wpc_ams_level['redirect'] ) {

                    do_action( 'wp_client_redirect', $wpc_ams_level['redirect'] );
                    exit;
                } else {

                    /*our_hook_
                        hook_name: wpc_client_need_pay_for_access
                        hook_title: Need Pay For Get Access
                        hook_description: Can be used for checking access to portals, and redirecting to payment page if np access.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.common.php
                        hook_param: int $client_id
                        hook_since: 3.5.4
                    */

                    do_action( 'wpc_client_need_pay_for_access', $client_id );
                }
            }

            return $client_id;
        }


        /*
        * Get Hub link
        *
        * return hub URL
        */
        //todelete
        function cc_get_hub_link( $client_id = 0 ) {
            //todo tochange this function
            if ( 0 == $client_id ) {
                    $client_id = get_current_user_id();
            }

            //get URL for HUB page
            if( get_option( 'permalink_structure' ) ) {
                $hub_url = $this->cc_get_slug( 'hub_page_id' );
            } else {
                $hub_id     = get_user_meta( $client_id, 'wpc_cl_hubpage_id', true );
                $hub_url    = get_permalink( $hub_id );
            }

            return $hub_url;
        }


        /*
        * Remove our shortcodes
        */
        function cc_remove_shortcodes() {
             $shortcodes = array(
             'wpc_client',
             'wpc_client_private',
             'wpc_client_theme',
             'wpc_client_loginf',
             'wpc_client_logoutb',
             'wpc_client_filesla',
             'wpc_client_uploadf',
             'wpc_client_fileslu',
             'wpc_client_pagel',
             'wpc_client_com',
             'wpc_client_graphic',
             'wpc_client_registration_form',
             'wpc_client_registration_successful',
             'wpc_client_add_staff_form',
             'wpc_client_staff_directory',
             'wpc_client_business_name',
             'wpc_client_contact_name',
             'wpc_client_hub_page',
             'wpc_client_portal_page',
             'wpc_client_get_page_link',
             'wpc_client_edit_portal_page',
             'wpc_redirect_on_login_hub',
             'wpc_client_error_image',
             );

             foreach( $shortcodes as $shortcode ) {
                remove_shortcode( $shortcode );
             }
        }


        /**
         * Get all clients for Client Circle
         **/
         function cc_get_group_clients_id( $group_id ) {
            global $wpdb;

            if ( 0 >= $group_id )
                return array();

            $group_clients      = $wpdb->get_results( $wpdb->prepare( "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d", $group_id ), "ARRAY_A" );
            $group_clients_id   = array();

            foreach( $group_clients as $group_client )
                $group_clients_id[] = $group_client['client_id'];

            return $group_clients_id;
         }



        /*
        * Replace placeholders
        */
        function cc_replace_placeholders( $content, $args = '', $label = '' ) {

            $content = stripslashes( $content );

            $user = false;
            $staff = false;
            $client_id = '';
            if ( isset( $args['client_id'] ) && 0 < $args['client_id'] ) {
                $client_id = $args['client_id'];

                $user = get_userdata( $client_id );
            }


            if ( isset( $args['client_id'] ) && 0 < $args['client_id'] && strpos( $content, '{manager_name}' ) !== false ) {
                $user_manager_ids = $this->cc_get_assign_data_by_assign( 'manager', 'client', $client_id );

                if( is_array( $user_manager_ids ) && count( $user_manager_ids ) ) {
                    $managers = array();
                    foreach( $user_manager_ids as $key=>$user_manager_id ) {
                        $manager = get_userdata( $user_manager_id );
                        if ( $manager ) {
                            $managers[$key] = $manager->get( 'display_name' );
                        }
                    }

                    $managers = ( count( $managers ) ) ? implode( ', ', $managers ) : '';
                }
            }

            $wpc_business_info = $this->cc_get_settings( 'business_info' );

            $ph_data = array (
                '{site_title}'              => get_option( 'blogname' ),
                '{blog_name}'               => get_option( 'blogname' ),
                '{client_id}'               => ( $user ) ? $user->get( 'ID' ) : '',
                '{contact_name}'            => ( $user ) ? $user->get( 'display_name' ) : '',
                '{client_business_name}'    => ( $user ) ? get_user_meta( $client_id, 'wpc_cl_business_name', true ) : '',
                '{client_phone}'            => ( $user ) ? get_user_option( 'contact_phone', $client_id ) : '',
                '{client_email}'            => ( $user ) ? $user->get( 'user_email' ) : '',
                '{client_name}'             => ( $user ) ? $user->get( 'display_name' ) : '',
                '{user_name}'               => ( $user ) ? $user->get( 'user_login' ) : '',
                '{login_url}'               => ( '' != $this->cc_get_slug( 'login_page_id' ) ) ? $this->cc_get_slug( 'login_page_id' ) : wp_login_url(),
                '{logout_url}'              => $this->cc_get_logout_url(),
                '{admin_url}'               => $this->cc_get_login_url( true ),

                '{business_logo_url}'           => ( isset( $wpc_business_info['business_logo_url'] ) ) ? $wpc_business_info['business_logo_url'] : '',
                '{business_name}'               => ( isset( $wpc_business_info['business_name'] ) ) ? $wpc_business_info['business_name'] : '',
                '{business_address}'            => ( isset( $wpc_business_info['business_address'] ) ) ? $wpc_business_info['business_address'] : '',
                '{business_mailing_address}'    => ( isset( $wpc_business_info['business_mailing_address'] ) ) ? $wpc_business_info['business_mailing_address'] : '',
                '{business_website}'            => ( isset( $wpc_business_info['business_website'] ) ) ? $wpc_business_info['business_website'] : '',
                '{business_email}'              => ( isset( $wpc_business_info['business_email'] ) ) ? $wpc_business_info['business_email'] : '',
                '{business_phone}'              => ( isset( $wpc_business_info['business_phone'] ) ) ? $wpc_business_info['business_phone'] : '',
                '{business_fax}'                => ( isset( $wpc_business_info['business_fax'] ) ) ? $wpc_business_info['business_fax'] : '',

                '{approve_url}'     => '',
                '{verify_url}'      => '',
                '{password}'        => '',
                '{page_title}'      => ( isset( $args['page_title'] ) ) ? $args['page_title'] : '',
                '{page_id}'         => '',
                '{admin_file_url}'  => '',
                '{message}'         => '',
                '{file_name}'       => '',
                '{file_category}'   => '',
                '{reset_address}'   => '',

                '{manager_name}'   => ( isset( $managers ) && !empty( $managers ) ) ? $managers : __( 'No manager', WPC_CLIENT_TEXT_DOMAIN ),
                '{client_registration_date}'   => ( $user ) ? $user->get( 'user_registered' ) : '',


                '{invoice_content}'   => ( isset( $args['invoice_content'] ) ) ? $args['invoice_content'] : '',


                '{staff_display_name}'  => ( $staff ) ? $staff->get( 'display_name' ) : '',
                '{staff_first_name}'    => ( $staff ) ? get_user_meta( $staff->get( 'ID' ), 'first_name', true ) : '',
                '{staff_last_name}'     => ( $staff ) ? get_user_meta( $staff->get( 'ID' ), 'last_name', true ) : '',
                '{staff_email}'         => ( $staff ) ? $staff->get( 'user_email' ) : '',
                '{staff_login}'         => ( $staff ) ? $staff->get( 'user_login' ) : '',



            );

            if ( '' != $label ) {
                switch( $label ) {
                    case 'notify_client_about_message':
                    case 'notify_cc_about_message':
                    case 'notify_admin_about_message':
                        $ph_data['{user_name}'] = ( isset( $args['user_name'] ) ) ? $args['user_name'] : '';
                        $ph_data['{message}'] = ( isset( $args['message'] ) ) ? $args['message'] : '';
                        break;

                    case 'new_client':
                    case 'client_updated':
                    case 'manager_created':
                    case 'staff_created':
                    case 'staff_registered':
                        $ph_data['{user_name}']     = ( $user ) ? $user->get( 'user_login' ) : '';
                        $ph_data['{password}']      = ( isset( $args['user_password'] ) ) ? $args['user_password'] : '';
                        $ph_data['{user_password}'] = ( isset( $args['user_password'] ) ) ? $args['user_password'] : '';
                        $ph_data['{page_id}']       = ( isset( $args['page_id'] ) ) ? $args['page_id'] : '';
                        $ph_data['{verify_url}']    = ( isset( $args['verify_url'] ) ) ? $args['verify_url'] : '';
                        break;

                    case 'portal_page_updated':
                    case 'private_post_type':
                        $ph_data['{page_id}']       = ( isset( $args['page_id'] ) ) ? $args['page_id'] : '';
                        break;

                    case 'new_file_for_client_staff':
                    case 'client_uploaded_file':
                        $ph_data['{admin_file_url}'] = get_admin_url() . "admin.php?page=wpclients_files&filter=" . $client_id;
                        $ph_data['{file_name}']     = ( isset( $args['file_name'] ) ) ? $args['file_name'] : '';
                        $ph_data['{file_category}'] = ( isset( $args['file_category'] ) ) ? $args['file_category'] : '';
                        break;

                    case 'client_downloaded_file':
                        $ph_data['{file_name}']     = ( isset( $args['file_name'] ) ) ? $args['file_name'] : '';
                        break;

                    case 'wizard_notify':
                        $ph_data['{wizard_name}']   = ( isset( $args['wizard_name'] ) ) ? $args['wizard_name'] : '';
                        $ph_data['{wizard_url}']    = ( isset( $args['wizard_url'] ) ) ? $args['wizard_url'] : '';
                        break;

                    case 'invoice_notify_admin':
                    case 'invoice_notify':
                    case 'estimate_notify':
                    case 'invoice_reminder':
                    case 'invoice_thank_you':
                        //invoicing
                        $ph_data['{invoice_number}']    = ( isset( $args['inv_number'] ) && '' != $args['inv_number'] ) ? $args['inv_number'] : '';
                        $ph_data['{estimate_number}']   = ( isset( $args['inv_number'] ) && '' != $args['inv_number'] ) ? $args['inv_number'] : '';
                        $ph_data['{decline_note}']   = ( isset( $args['decline_note'] ) && '' != $args['decline_note'] ) ? $args['decline_note'] : __( ' without reason', WPC_CLIENT_TEXT_DOMAIN );
                        break;

                    case 'reset_password':
                        $ph_data['{reset_address}'] = ( isset( $args['reset_address'] ) ) ? $args['reset_address'] : '';
                        break;

                }
            }


            /*our_hook_
                hook_name: wpc_client_replace_placeholders
                hook_title: Replace Placeholders
                hook_description: Hook runs before placeholders are replaced. You can use it to add\edit\remove placeholders and their values.
                hook_type: filter
                hook_in: wp-client
                hook_location class.common.php
                hook_param: array $placeholders, array $args, string $label
                hook_since: 3.3.5
            */
            $ph_data = apply_filters( "wpc_client_replace_placeholders", $ph_data, $args, $label );

            $content = str_replace( array_keys( $ph_data ), array_values( $ph_data ), $content );

            return $content;
        }


        /*
        * Compile templates
        */
        function cc_getTemplateContent( $template_name, $data = array(), $client_id ='', $template = '', $templates_dir = '' ) {
            $wpc_templates_shortcodes           = $this->cc_get_settings( 'templates_shortcodes' );
            $wpc_templates_shortcodes_settings  = $this->cc_get_settings( 'templates_shortcodes_settings' );
            $templates_dir                      = ( '' != $templates_dir ) ? $templates_dir : $this->plugin_dir . 'includes/templates/';

            if ( '' == $template ) {
                if ( isset( $wpc_templates_shortcodes[$template_name] ) && '' != $wpc_templates_shortcodes[$template_name] ) {
                    //get custom template
                    $template = $wpc_templates_shortcodes[$template_name];
                } else {
                    //get default template
                    $template = file_get_contents( $templates_dir . $template_name . '.tpl' );
                    $wpc_templates_shortcodes[$template_name] = $template;
                    do_action( 'wp_client_settings_update', $wpc_templates_shortcodes, 'templates_shortcodes' );
                }
            }

            /*
            * Smarty
            */
            //allow php tags in template but not for CLOUDS
            if ( !defined( 'WPC_CLOUDS' ) && isset( $wpc_templates_shortcodes_settings[$template_name]['allow_php_tag'] ) && 'yes' == $wpc_templates_shortcodes_settings[$template_name]['allow_php_tag'] ) {
                if( !class_exists( 'SmartyBC' ) )
                    include( $this->plugin_dir . 'includes/libs/smarty/smartybc.class.php' );

                $smarty = new SmartyBC();
            } else {
                if( !class_exists( 'Smarty' ) )
                    include( $this->plugin_dir . 'includes/libs/smarty/smarty.class.php' );

                $smarty = new Smarty();

                //remove {php}
                $template = preg_replace ("!{php}(.*?){/php}!si", "",  $template);
            }

            $smarty->force_compile = true;
            if ( count( $data ) ) {
                foreach( $data as $k => $val ) {
                    $smarty->assign( $k, $val );
                }
            }

            //catch fatal errors if not correct template
            try {
                $args = array( 'client_id' => $client_id );
                $result = $smarty->fetch( 'eval:' . $this->cc_replace_placeholders( $template, $args ) );
            } catch ( Exception $e ) {
                return __( 'Note: Some problem with template.', WPC_CLIENT_TEXT_DOMAIN );
            }

            unset( $smarty );

            return $result;
        }


        /*
        * add & update the wp client as users
        */
        function cc_client_update_func( $userdata ) {
            global $rul_db_addresses, $wpdb;

            //import: get client circles
            $import_circles = array();
            if ( isset( $userdata['client_circles'] ) ) {
                $import_circles = $userdata['client_circles'];
                unset( $userdata['client_circles'] );
            }

            if ( !isset( $userdata['ID'] ) ) {
                $wpc_clients_staff = $this->cc_get_settings( 'clients_staff' );

                // insert new user
                $new_user = wp_insert_user($userdata);


                //add Client Circles auto assign
                $add_groups = array();
                if ( isset( $userdata['self_registered'] ) && 1 == $userdata['self_registered'] ) {
                    $add_groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_self = 1" );
                } else {
                    $add_groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups WHERE auto_add_manual = 1" );
                }

                $import_circles = array_merge( $import_circles, $add_groups ) ;

                if ( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) && 0 < $new_user && '' != $_REQUEST['wpc_circles'] ) {
                    $import_circles = array_merge( $import_circles, explode( ',', $_REQUEST['wpc_circles'] ) );
                }

                $import_circles = array_unique( $import_circles ) ;

                //import: add client circles
                if ( 0 < $new_user ) {
                    foreach ( $import_circles as $circle_id ) {
                        $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $circle_id,  $new_user ) );
                    }
                }

                update_user_option( $new_user, 'contact_phone', $userdata['contact_phone'], false );
                update_user_option( $new_user, 'unqiue', md5( time() ) );

                if ( isset( $userdata['admin_manager'] ) && '' != $userdata['admin_manager'] ) {
                    $assign_data = array();
                    if( $userdata['admin_manager'] == 'all' ) {
                        $args = array(
                            'role'      => 'wpc_manager',
                            'orderby'   => 'user_login',
                            'order'     => 'ASC',
                            'fields'    => array( 'ID' ),
                        );

                        $userdata['admin_manager'] = get_users( $args );
                        foreach( $userdata['admin_manager'] as $key=>$value) {
                            $assign_data[] = $value->ID;
                        }
                    } else {
                        $assign_data = explode( ',', $userdata['admin_manager'] );
                    }
                    $this->cc_set_reverse_assigned_data( 'manager', $assign_data, 'client', $new_user );
                    /* to delete
                    foreach( $assign_data as $value ) {
                        $this->cc_set_assigned_data( 'manager', $value, 'client', array( $new_user ) );
                    }*/
                }

                //set business name
                if ( isset( $userdata['business_name'] ) ) {
                     update_user_meta( $new_user, 'wpc_cl_business_name', $userdata['business_name'] );
                }

                //for client registered from registration form
                if ( isset( $userdata['to_approve'] ) ) {

                    //send email to admin
                    if ( !isset( $wpc_clients_staff['new_client_admin_notify'] ) || 'yes' == $wpc_clients_staff['new_client_admin_notify'] ) {


                        $emails_array = array();

                        $emails_array[] = get_option( 'admin_email' );

                        $args = array( 'client_id' => $new_user );
                        foreach( $emails_array as $to_email ) {
                            $this->cc_mail( 'new_client_registered', $to_email, $args, 'to_approve' );
                        }

                    }

                }

                $this->cc_remove_shortcodes();

                $wpc_templates_hubpage = $this->cc_get_settings( 'templates_hubpage', '' );

                //create hub page for the user
                $post = array();
                $post['post_type']      = 'hubpage'; //could be 'page' for example
                $post['post_content']   = html_entity_decode( $wpc_templates_hubpage );
                $post['post_author']    = 1;
                $post['post_status']    = 'publish'; //draft
                $post['comment_status'] = 'closed';
                $post['post_title']     = $userdata['business_name'];
                $post['post_parent']    = 0;
                $post['post_status']    = "publish";

                $postid = wp_insert_post($post);

                if ( 0 < $postid )
                    update_user_meta( $new_user, 'wpc_cl_hubpage_id', $postid );


                // add Portal Page for this user
                $wpc_templates_clientpage = $this->cc_get_settings( 'templates_clientpage', '' );
                $wpc_templates_clientpage = html_entity_decode( $wpc_templates_clientpage );
                $wpc_templates_clientpage = str_replace( "{client_business_name}", $userdata['business_name'], $wpc_templates_clientpage );
                $wpc_templates_clientpage = str_replace( "{page_title}", $userdata['business_name'], $wpc_templates_clientpage );

                if ( !isset( $wpc_clients_staff['create_portal_page'] ) || 'yes' == $wpc_clients_staff['create_portal_page'] ) {

                    $clients = array(
                        'comment_status'    => 'closed',
                        'ping_status'       => 'closed',
                        'post_author'       => get_current_user_id(),
                        'post_content'      => $wpc_templates_clientpage,
                        'post_name'         => $userdata['business_name'],
                        'post_status'       => 'publish',
                        'post_title'        => $userdata['business_name'],
                        'post_type'         => 'clientspage'
                    );

                    $client_page_id = wp_insert_post( $clients );

                    //update Ignore Theme Link Pages option
                    if( isset( $wpc_clients_staff['use_portal_page_settings'] ) && '1' == $wpc_clients_staff['use_portal_page_settings'] )
                        update_post_meta( $client_page_id, '_wpc_use_page_settings', 1 );
                    else
                        update_post_meta( $client_page_id, '_wpc_use_page_settings', 0 );

                    $user_ids = array();
                    $user_ids[] = $new_user ;
                    $this->cc_set_assigned_data( 'portal_page', $client_page_id, 'client', array( $new_user ) );
                }


                if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] ) {
                    $key = md5( $new_user . time() );
                    update_user_meta( $new_user, 'verify_email_key', $key );

                    //make link
                    if ( $this->permalinks ) {
                        $link = get_home_url() . '/portal/acc-activation/' . $key ;
                    } else {
                        $link = add_query_arg( array( 'wpc_page' => 'acc_activation', 'wpc_page_value' => $key ), get_home_url() );
                    }
                    $args = array( 'client_id' => $new_user, 'verify_url' => $link );
                    //send email
                    $this->cc_mail( 'new_client_verify_email', $userdata['user_email'], $args, 'new_client' );

                }


                $link = get_permalink($postid);

                if( isset( $userdata['send_password'] ) && ( $userdata['send_password'] == 'on' || $userdata['send_password'] == '1' ) ) {

                    $args = array(
                        'client_id' => $new_user,
                        'user_password' => $userdata['user_pass'],
                        'page_id' => $link,
                        'page_title' => $userdata['business_name']
                    );

                    //send email
                    if ( isset( $userdata['to_approve'] ) && !empty( $userdata['to_approve'] ) ) {
                        $this->cc_mail( 'self_client_registration', $userdata['user_email'], $args, 'new_client' );
                    } else {
                        $this->cc_mail( 'new_client_password', $userdata['user_email'], $args, 'new_client' );
                    }
                }


                $client_id = $new_user;

                if ( isset( $userdata['to_approve'] ) && 'auto' == $userdata['to_approve'] ) {
                    /*our_hook_
                        hook_name: wpc_client_new_client_registered
                        hook_title: New Client Registered
                        hook_description: Hook runs when Client account is registered.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.common.php
                        hook_param: int $client_id, array $userdata
                        hook_since: 3.4.1
                    */
                    do_action( 'wpc_client_new_client_registered', $new_user, $userdata );
                } else {
                    /*our_hook_
                        hook_name: wpc_client_new_client_added
                        hook_title: New Client Added by Admin
                        hook_description: Hook runs when Client account is added by Admin.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.common.php
                        hook_param: int $client_id, array $userdata
                        hook_since: 3.4.1
                    */
                    do_action( 'wpc_client_new_client_added', $new_user, $userdata );
                }

            } else {
                wp_update_user( $userdata );
                //sending email to client for updated password information
                if ( '1' == $userdata['send_password'] ) {

                    $args = array( 'client_id' => $userdata['ID'], 'user_password' => $userdata['user_pass'] );

                    //send email
                    $this->cc_mail( 'client_updated', $userdata['user_email'], $args, 'client_updated' );
                }

                //sending email to client for updated password information
                if( isset( $userdata['contact_phone'] ) && !empty( $userdata['contact_phone'] ) ) {
                    update_user_option( $userdata['ID'], 'contact_phone', $userdata['contact_phone'], false );
                }

                //set business name
                if ( isset( $userdata['business_name'] ) ) {
                    update_user_meta( $userdata['ID'], 'wpc_cl_business_name', $userdata['business_name'] );
                }


                if ( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) && '' != $_REQUEST['wpc_circles'] ) {
                    if( $_REQUEST['wpc_circles'] == 'all' ) {
                        $group_ids = $this->cc_get_group_ids();
                    } else {
                        $group_ids = explode( ',', $_REQUEST['wpc_circles'] );
                    }
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = '%d'", $userdata['ID'] ) );
                    foreach ( $group_ids as $group_id ) {
                        $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $group_id,  $userdata['ID'] ) );
                    }
                } elseif( isset( $_REQUEST['wpc_circles'] ) && is_string( $_REQUEST['wpc_circles'] ) && '' == $_REQUEST['wpc_circles'] ) {
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = '%d'", $userdata['ID'] ) );
                }

                $client_id = $userdata['ID'];

                /*our_hook_
                    hook_name: wpc_client_client_updated
                    hook_title: Client Updated
                    hook_description: Hook runs when Client account is updated.
                    hook_type: action
                    hook_in: wp-client
                    hook_location class.common.php
                    hook_param: int $client_id, array $userdata
                    hook_since: 3.4.1
                */
                do_action( 'wpc_client_client_updated', $userdata['ID'], $userdata );
            }

            /*our_hook_
                hook_name: wpc_client_client_saved
                hook_title: Client Saved
                hook_description: Hook runs when Client account is registered or added by admin or updated.
                hook_type: action
                hook_in: wp-client
                hook_location class.common.php
                hook_param: int $client_id, array $userdata
                hook_since: 3.4.1
            */
            do_action( 'wpc_client_client_saved', $client_id, $userdata );


        }


        /*
        * JS redirect
        */
        function cc_js_redirect( $url ) {

            //for blank redirects
            if ( '' == $url ) {
                $url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            }

            $funtext="echo \"<script type='text/javascript'>window.location = '" . $url . "'</script>\";";
            register_shutdown_function(create_function('',$funtext));

            if ( 1 < ob_get_level() ) {
                while ( ob_get_level() > 1 ) {
                    ob_end_clean();
                }
            }

            ?>
                <script type="text/javascript">
                    window.location = '<?php echo $url; ?>';
                </script>
            <?php
            exit;
        }


        /**
         * Load translate textdomain file.
         */
        function _load_textdomain() {
            load_plugin_textdomain( WPC_CLIENT_TEXT_DOMAIN, false, dirname( 'web-portal-lite-client-portal-secure-file-sharing-private-messaging/web-portal-lite-client-portal-secure-file-sharing-private-messaging.php' ) . '/languages/' );
        }

        /*
        * Get login URL
        */
        function cc_get_login_url( $from_placeholders = false ) {
            global $wp_query;
            $login_url = ( '' != $this->cc_get_slug( 'login_page_id' ) ) ? $this->cc_get_slug( 'login_page_id' ) : wp_login_url();
            $server_url = ( is_ssl() ? "https://" : "http://" ) . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

            $is_login_url = strpos( $server_url, $login_url );
            $is_wp_login_url = strpos( $server_url, wp_login_url() );

            if( $is_login_url !== false || $is_wp_login_url !== false || $from_placeholders ) {
                return $login_url;
            } else if ( isset( $wp_query->query_vars['wpc_page'] ) && 'acc_activation' == $wp_query->query_vars['wpc_page'] ) {
                return add_query_arg( array( 'msg' => 've' ), $login_url  );
            } else {
                $wpc_enable_custom_redirects = $this->cc_get_settings( 'enable_custom_redirects', 'no' );
                $default_non_login_redirects = $this->cc_get_settings( 'default_non_login_redirects' ) ;

                if ( isset( $default_non_login_redirects['url'] ) && '' != $default_non_login_redirects['url'] && 'yes' == $wpc_enable_custom_redirects )
                    return $default_non_login_redirects['url'] ;
                else
                    return add_query_arg( array( 'wpc_to_redirect' => urlencode( $server_url ) ), $login_url ) ;
            }
        }


        /*
        * Get logout URL
        */
        function cc_get_logout_url() {
            $logout_url = ( '' != $this->cc_get_slug( 'login_page_id' ) ) ? add_query_arg( array( 'logout' => 'true' ), $this->cc_get_slug( 'login_page_id' ) ) : wp_logout_url();
            return $logout_url;
        }


        /**
         * Get all data of all Client Circles
         **/
         function cc_get_groups() {
            global $wpdb;
            $groups = $wpdb->get_results( "SELECT wcg.*, count(wcgc.client_id) - count(um.umeta_id) as clients_count FROM {$wpdb->prefix}wpc_client_groups wcg LEFT JOIN {$wpdb->prefix}wpc_client_group_clients wcgc ON wcgc.group_id = wcg.group_id LEFT JOIN {$wpdb->prefix}usermeta um ON wcgc.client_id = um.user_id AND um.meta_key = 'archive' AND um.meta_value = '1' GROUP BY wcg.group_id", "ARRAY_A");
            return $groups;
         }


        /**
         * Get Client Circle by ID
         **/
         function cc_get_group( $id ) {
            global $wpdb;
            return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_groups WHERE group_id = %d", $id ), "ARRAY_A");
         }


        /**
         * Get all Client Circles for client
         **/
         function cc_get_client_groups_id( $client_id ) {
            global $wpdb;

            $client_groups_id   = $wpdb->get_col( $wpdb->prepare( "SELECT group_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = %d", $client_id ) );

            if ( !is_array( $client_groups_id ) )
                $client_groups_id   = array();

            return $client_groups_id;
         }


         /**
         * get keys from multidimensional array
         **/
         function cc_show_keys($ar) {
            $temp = array();
            foreach ($ar as $k => $v ) {
                $temp[] = $k;
                if (is_array($v)) {
                    $temp = array_merge($temp, $this->cc_show_keys($v));
                }
            }
            return $temp;
        }


        /**
         * Get date/time with timezone
         */
        function cc_date_timezone( $format, $timestamp ) {
            $gmt_offset =  get_option( 'gmt_offset' );
            if ( false === $gmt_offset ) {
                //$timestamp = $timestamp;
                $timestamp = $timestamp - ( time() - current_time( 'timestamp' ) );
            } else {
                $timestamp = $timestamp + $gmt_offset * 3600;
            }
            return date_i18n( $format, $timestamp );
        }


        /*
        * login redirect rules
        */
        function cc_login_redirect_rules( $redirect_to, $requested_redirect_to, $user ) {
            global $wp_roles;

            // If they're on the login page, don't do anything
            if( !isset( $user->user_login ) ) {
                return $redirect_to;
            }

            if(  isset( $_GET['wpc_to_redirect'] ) && !empty( $_GET['wpc_to_redirect'] ) ) {
                return $redirect_to;
            }

            //redirect by login/logout redirect table
            $wpc_enable_custom_redirects = $this->cc_get_settings( 'enable_custom_redirects', 'no' );

            if ( 'yes' == $wpc_enable_custom_redirects ) {
                global $wpdb;
                //get individual redirect for users
                $new_redirect_to = $wpdb->get_var( $wpdb->prepare( "SELECT rul_url FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value = '%s' AND rul_type='user'", $user->user_login ) );

                if ( $new_redirect_to ) {
                    return $new_redirect_to;
                } else {
                    //redirects for circles
                    $client_groups = $this->cc_get_client_groups_id( $user->ID );
                    if ( 0 < count( $client_groups ) ) {
                        $new_redirect_to = $wpdb->get_var( "SELECT rul_url FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type='circle' AND rul_url != '' AND rul_value IN('" . implode( "','", $client_groups ) . "') ORDER BY rul_order DESC LIMIT 1" );
                        if ( $new_redirect_to )
                            return $new_redirect_to;
                    }


                    //redirects for roles
                    $userdata = get_userdata( $user->ID );
                    $userroles = $userdata->roles;

                    foreach( $userroles as $key=>$userrole ) {
                        $userroles[$key] = "'" . $userrole . "'";
                    }
                    $userroles = implode( ',', $userroles );

                    $new_role_redirect_to = $wpdb->get_var( "SELECT rul_url FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value IN(" . $userroles . ") AND rul_type='role' AND rul_url != '' ORDER BY rul_order DESC LIMIT 1" );

                    if ( $new_role_redirect_to ) {
                        return $new_role_redirect_to;
                    }
                    //if not find redirect for user, circle and role use default redirect
                    $wpc_default_redirects = $this->cc_get_settings( 'default_redirects' );
                    if ( isset( $wpc_default_redirects['login'] ) && '' != $wpc_default_redirects['login'] ) {
                        return $wpc_default_redirects['login'];
                    } else {
                        //redirection for administrators
                        if ( user_can( $user, 'administrator' ) && !user_can( $user, 'manage_network_options' ) ) {
                            return admin_url();
                        }


                        //redirect Client and Staff to my-hub page
                        if ( ( user_can( $user, 'wpc_client' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                            return $this->cc_get_slug( 'hub_page_id' );
                        }

                        if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
                            // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                            if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
                                $redirect_to = user_admin_url();
                            elseif ( is_multisite() && !$user->has_cap('read') )
                                $redirect_to = get_dashboard_url( $user->ID );
                            elseif ( !$user->has_cap('edit_posts') )
                                $redirect_to = admin_url('profile.php');
                        }

                        //redirect for another users
                        return $redirect_to;
                    }
                }
            } else {
                //redirection for administrators
                if ( user_can( $user, 'administrator' ) && !user_can( $user, 'manage_network_options' ) ) {
                    return admin_url();
                }


                //redirect Client and Staff to my-hub page
                if ( ( user_can( $user, 'wpc_client' ) ) && !user_can( $user, 'manage_network_options' ) )  {
                    return $this->cc_get_slug( 'hub_page_id' );
                }

                if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
                    // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                    if ( is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin( $user->ID ) )
                        $redirect_to = user_admin_url();
                    elseif ( is_multisite() && !$user->has_cap('read') )
                        $redirect_to = get_dashboard_url( $user->ID );
                    elseif ( !$user->has_cap('edit_posts') )
                        $redirect_to = admin_url('profile.php');
                }

                //redirect for another users
                return $redirect_to;
            }

        }


        /*
        * logout redirect rules
        */
        function cc_logout_redirect_rules() {
            global $current_user;
            //Compatibility  with Duo Two-Factor Authentication plugin
            if ( false === has_action( 'authenticate', 'wp_authenticate_username_password' ) ) {
                return '';
            }

            //for widget - doing redirect if it set in parameter
            if ( isset( $_REQUEST['logout'] ) && 'true' == $_REQUEST['logout'] ) {
                if ( isset( $_REQUEST['redirect_to'] ) && '' != $_REQUEST['redirect_to'] ) {
                    wp_redirect( $_REQUEST['redirect_to'] );
                    die();
                }
            }

            //redirect by login/logout redirect table
            $wpc_enable_custom_redirects = $this->cc_get_settings( 'enable_custom_redirects', 'no' );
            if ( isset( $wpc_enable_custom_redirects ) && 'yes' == $wpc_enable_custom_redirects ) {
                global $wpdb;

                $redirect_to = $wpdb->get_var( $wpdb->prepare( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value = '%s' AND rul_type='user'", $current_user->user_login ) );

                if ( $redirect_to ) {
                    wp_redirect( $redirect_to );
                    die();
                } else {
                    //redirects for circles
                    $client_groups = $this->cc_get_client_groups_id( $current_user->ID );
                    if ( 0 < count( $client_groups ) ) {
                        $redirect_to = $wpdb->get_var( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_type='circle' AND rul_url_logout != '' AND rul_value IN('" . implode( "','", $client_groups ) . "') ORDER BY rul_order DESC LIMIT 1" );
                        if ( $redirect_to ) {
                            wp_redirect( $redirect_to );
                            die();
                        }
                    }

                    //redirects for roles
                    $userroles = $current_user->roles;

                    foreach( $userroles as $key=>$userrole ) {
                        $userroles[$key] = "'" . $userrole . "'";
                    }
                    $userroles = implode( ',', $userroles );

                    $redirect_to = $wpdb->get_var( "SELECT rul_url_logout FROM {$wpdb->prefix}wpc_client_login_redirects WHERE rul_value IN(" . $userroles . ") AND rul_type='role' AND rul_url_logout != '' ORDER BY rul_order DESC LIMIT 1" );

                    if ( $redirect_to ) {
                        wp_redirect( $redirect_to );
                        die();
                    }

                    $wpc_default_redirects = $this->cc_get_settings( 'default_redirects' );
                    if ( isset( $wpc_default_redirects['logout'] ) && '' != $wpc_default_redirects['logout'] ) {
                        wp_redirect( $wpc_default_redirects['logout'] );
                        die();
                    } else {
                        //redirection for administrators
                        if ( ( current_user_can( 'administrator' ) ) && !current_user_can( 'manage_network_options' ) )  {
                            wp_redirect( wp_login_url() );
                            die();
                        }


                        //redirect Client and Staff to my-hub page
                        if ( ( current_user_can( 'wpc_client' ) ) && !current_user_can( 'manage_network_options' ) )  {
                            wp_redirect( $this->cc_get_login_url() );
                            die();
                        }

                        wp_redirect( wp_login_url() );
                        die();
                    }
                }
            } else {
                //redirection for administrators
                if ( ( current_user_can( 'administrator' ) ) && !current_user_can( 'manage_network_options' ) )  {

                    wp_redirect( wp_login_url() );
                    die();
                }

                //redirect Client and Staff to my-hub page
                if ( ( current_user_can( 'wpc_client' ) ) && !current_user_can( 'manage_network_options' ) )  {
                    wp_redirect( $this->cc_get_login_url() );
                    die();
                }


                wp_redirect( wp_login_url() );
                die();
            }
        }


        /**
         * Get settings
         */
        function cc_get_settings( $key, $defaul_value = array() ) {

            if ( empty( $key ) ) {
                return false;
            }

            //cache settings
            if ( in_array( $key, array( 'pages', 'general', 'file_sharing', 'business_info' ) ) ){
                if ( isset( $this->cache_settings[$key] ) ) {
                    $s = $this->cache_settings[$key];
                } else {
                    $s = $this->cc_recursive_strip_slashes( get_option( 'wpc_' . $key, $defaul_value ) );
                    $this->cache_settings[$key] = $s;
                }

            } else {
                $s = $this->cc_recursive_strip_slashes( get_option( 'wpc_' . $key, $defaul_value ) );
            }

            return $s;
        }


        function cc_recursive_strip_slashes( $data ) {
            if( is_string( $data ) ) {
                return stripslashes( $data );
            } else if( is_array( $data ) ) {
                $result = array();
                foreach( $data as $k=>$val ) {
                    $result[ $k ] = self::cc_recursive_strip_slashes( $val );
                }
                return $result;
            } else {
                return $data;
            }
        }


        /**
         * Get settings
         */
        function cc_delete_settings( $key ) {

            if ( empty( $key ) ) {
                return false;
            }

            return delete_option( 'wpc_' . $key );
        }


        function get_id_simple_temlate() {
            $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );
            foreach( $wpc_ez_hub_templates as $key => $template ) {
                if( isset( $template['not_delete'] ) ) {
                    return $key;
                }
            }
            return '';
        }


        /**
         * Send mail
         */
        function cc_mail( $key, $to, $args = array(), $placeholders_label = '', $attachments = array() ) {
            if( isset( $args['client_id'] ) && 0 < $args['client_id'] ) {
                $excluded_clients  = $this->cc_get_excluded_clients( 'archive' );
                if( in_array( $args['client_id'], $excluded_clients ) ) {
                    return false;
                }
            }
            do_action( 'wpc_client_send_email', $key, $to, $args, $placeholders_label, $attachments );

            $wpc_templates_emails = $this->cc_get_settings( 'templates_emails' );

            //no template
            if ( !isset( $wpc_templates_emails[$key] ) )
                return false;

            //notification is disabled
            if ( isset( $wpc_templates_emails[$key]['enable'] ) && 0 == $wpc_templates_emails[$key]['enable'] )
                return false;

            if ( !is_email( $to ) )
                return false;

            $headers = "From: =?UTF-8?B?" . base64_encode( stripslashes( get_option( 'sender_name' ) ) ) . "?= <" . get_option( 'sender_email' ) . "> \r\n";
            $headers .= "Reply-To: " . ( get_option( 'wpc_reply_email' ) ) ? get_option( 'wpc_reply_email' ) . "\r\n" : get_option( 'admin_email' ) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $subject = $this->cc_replace_placeholders( $wpc_templates_emails[$key]['subject'], $args, $placeholders_label );
            $subject = str_replace( "_", '-', $subject );

            if( function_exists( 'mb_encode_mimeheader' ) )
                $subject = mb_encode_mimeheader( $subject,"utf-8", 'B');
            else
                $subject = "=?UTF-8?B?" . base64_encode( $subject ) . "?=";
            $message = $this->cc_replace_placeholders( $wpc_templates_emails[$key]['body'], $args, $placeholders_label );

            return wp_mail( $to, $subject, $message, $headers, $attachments );
        }


        /**
        *
        **/
        function cc_get_excluded_clients( $what = false ) {
            $excluded_clients = array();
            if ( 'to_approve' == $what ) {
                $excluded_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'to_approve', 'meta_value' => '1', 'fields' => 'ID' ) );
            } else if ( 'archive' == $what ) {
                $excluded_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'archive', 'meta_value' => '1', 'fields' => 'ID' ) );
            } else if ( !$what ) {
                $archive_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'archive', 'meta_value' => '1', 'fields' => 'ID' ) );
                $not_approve_clients = get_users( array( 'role' => 'wpc_client', 'meta_key' => 'to_approve', 'meta_value' => '1', 'fields' => 'ID' ) );
                $excluded_clients = array_merge( $archive_clients, $not_approve_clients );
            }
            return $excluded_clients;
        }


        /**
        * Function to set assigned data
        */
        function cc_set_assigned_data( $object_type, $object_id, $assign_type = 'client', $assign_data = array() ) {
            global $wpdb, $current_user;

            if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {

                if( 'client' == $assign_type ) {
                    $excluded_clients = $this->cc_get_excluded_clients( 'archive' );
                    $not_in = implode( "','", $excluded_clients );
                    $wpdb->query(
                        "DELETE
                        FROM {$wpdb->prefix}wpc_client_objects_assigns
                        WHERE object_type='{$object_type}' AND
                            object_id='{$object_id}' AND
                            assign_type='{$assign_type}' AND
                            assign_id NOT IN ('{$not_in}')"
                    );
                } else {
                    $wpdb->delete(
                        "{$wpdb->prefix}wpc_client_objects_assigns",
                        array(
                            'object_type'   => $object_type,
                            'object_id'     => $object_id,
                            'assign_type'   => $assign_type,
                        )
                    );
                }


                if( is_array( $assign_data ) && 0 < count( $assign_data ) ) {
                    $values = '';
                    foreach( $assign_data as $assign_id ) {
                        $values .= "( '$object_type', '$object_id', '$assign_type', '$assign_id' ),";
                    }
                    $values = substr( $values, 0, -1 );
                    $wpdb->query( "INSERT INTO `{$wpdb->prefix}wpc_client_objects_assigns`(`object_type`,`object_id`,`assign_type`,`assign_id`) VALUES $values" );
                }

            }

        }


        function cc_set_reverse_assigned_data( $object_type, $object_data = array(), $assign_type = 'client', $assign_id ) {
            global $wpdb, $current_user;

            if( isset( $object_type ) && !empty( $object_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

               $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_objects_assigns",
                    array(
                        'object_type'   => $object_type,
                        'assign_type'   => $assign_type,
                        'assign_id'     => $assign_id,
                    )
                );
                if( is_array( $object_data ) && 0 < count( $object_data ) ) {
                    $values = '';
                    foreach( $object_data as $object_id ) {
                        $values .= "( '$object_type', '$object_id', '$assign_type', '$assign_id' ),";
                    }
                    $values = substr( $values, 0, -1 );
                    $wpdb->query( "INSERT INTO `{$wpdb->prefix}wpc_client_objects_assigns`(`object_type`,`object_id`,`assign_type`,`assign_id`) VALUES $values" );
                }

            }

        }


        function cc_get_assign_data_by_object( $object_type, $object_id, $assign_type = 'client' ) {
            global $wpdb;
            if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {
                $response = $wpdb->get_col( $wpdb->prepare(
                    "SELECT assign_id
                    FROM {$wpdb->prefix}wpc_client_objects_assigns
                    WHERE object_type='%s' AND
                        object_id=%d AND
                        assign_type='%s'",
                    $object_type,
                    $object_id,
                    $assign_type
                ) );

                if( 'client' == $assign_type && 0 < count( $response ) ) {
                    $excluded_clients = $this->cc_get_excluded_clients( 'archive' );
                    $response = array_diff( $response, $excluded_clients );
                }

                return ( isset( $response ) && !empty( $response ) ) ? $response : array();
            }
            return array();
        }


        function cc_get_assign_data_by_assign( $object_type, $assign_type = 'client', $assign_id ) {
            global $wpdb;
            $assign_id = is_array( $assign_id ) ? $assign_id : array( $assign_id );

            if( 'client' == $assign_type && 0 < count( $assign_id ) ) {
                $excluded_clients = $this->cc_get_excluded_clients( 'archive' );
                $assign_id = array_diff( $assign_id, $excluded_clients );
            }

            $response = array();
            if( isset( $object_type ) && !empty( $object_type ) ) {

                if( 0 < count( $assign_id ) ) {
                    $assign_id = implode( ',', $assign_id );


                    $response = $wpdb->get_col( $wpdb->prepare(
                        "SELECT DISTINCT object_id
                        FROM {$wpdb->prefix}wpc_client_objects_assigns
                        WHERE object_type='%s' AND
                            assign_id IN(" . $assign_id . ") AND
                            assign_type='%s'",
                        $object_type,
                        $assign_type
                    ) );
                }

                if ( 'manager' == $object_type && 'client' == $assign_type ) {
                    $groups_client = array();
                    $assign_id = explode(',', $assign_id );
                    foreach ( $assign_id as $client_id ) {
                        $add_groups_client = $this->cc_get_client_groups_id( $client_id );
                        $groups_client = array_merge( $groups_client, $add_groups_client );
                    }
                    if ( 0 < count( $groups_client ) ) {
                        $add_assign_id = $this->cc_get_assign_data_by_assign( 'manager', 'circle', $groups_client );
                        $response = array_merge( $response, $add_assign_id );
                    }
                }

                $response = array_unique( $response );

                return $response;
            }
            return array();
        }


        function cc_get_assign_data_by_object_assign( $object_type, $assign_type = 'client' ) {
            global $wpdb;

            if( isset( $object_type ) && !empty( $object_type ) && isset( $assign_type ) && !empty( $assign_type ) ) {
                $response = $wpdb->get_col( $wpdb->prepare(
                    "SELECT DISTINCT assign_id
                    FROM {$wpdb->prefix}wpc_client_objects_assigns
                    WHERE object_type='%s' AND
                        assign_type='%s'",
                    $object_type,
                    $assign_type
                ) );

                if( 'client' == $assign_type && 0 < count( $response ) ) {
                    $excluded_clients = $this->cc_get_excluded_clients( 'archive' );
                    $response = array_diff( $response, $excluded_clients );
                }

                return $response;
            }
            return array();
        }


        function cc_delete_all_object_assigns( $object_type, $object_id ) {
            global $wpdb;

            if( isset( $object_type ) && !empty( $object_type ) && isset( $object_id ) && !empty( $object_id ) ) {

                $response = $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_objects_assigns",
                    array(
                        'object_type'   => $object_type,
                        'object_id'     => $object_id
                    )
                );
            }
        }


        function cc_delete_all_assign_assigns( $assign_type, $assign_id ) {
            global $wpdb;

            if( isset( $assign_type ) && !empty( $assign_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

                $response = $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_objects_assigns",
                    array(
                        'assign_type' => $assign_type,
                        'assign_id' => $assign_id
                    )
                );
            }
        }


        function cc_delete_assign_data_by_assign( $object_type, $assign_type, $assign_id ) {
            global $wpdb;

            if( isset( $assign_type ) && !empty( $assign_type ) && isset( $assign_id ) && !empty( $assign_id ) ) {

                $response = $wpdb->delete(
                    "{$wpdb->prefix}wpc_client_objects_assigns",
                    array(
                        'object_type' => $object_type,
                        'assign_type' => $assign_type,
                        'assign_id' => $assign_id
                    )
                );
            }
        }


        /**
         * get all circles IDs
         **/
        function cc_get_group_ids() {
            global $wpdb;
            $groups = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_groups");
            return $groups;
        }


        /**
        * temp function for - get all circles IDs
        * added after moved this acc_get_group_ids from class.admin_common.php to class.common.php ( cc_get_group_ids )
        * to delete soon
        */
        function acc_get_group_ids() {
            return $this->cc_get_group_ids();
        }





        /**
        *  Function for getting wpc_client categories:

        *
        * ============= default struct ==============
        * $args = array(
        *       'type'
        *       'order_by'
        *       'order'
        *       'limit'
        *       'search'
        * );
        * ===========================================
        */
        function cc_get_categories( $args ) {
            global $wpdb;

            $categories = array();

            $types = array(
                'file',
                'portal_page',
                'shutter'
            );

            if( isset( $args['type'] ) && !empty( $args['type'] ) && in_array( $args['type'], $types ) ) {

                $args['order_by'] = ( isset( $args['order_by'] ) && !empty( $args['order_by'] ) ) ? $args['order_by'] : 'id';
                $args['order'] = ( isset( $args['order'] ) && !empty( $args['order'] ) ) ? $args['order'] : 'ASC';
                $args['search'] = ( isset( $args['search'] ) && !empty( $args['search'] ) ) ? $args['search'] : '';
                $args['limit'] = ( isset( $args['limit'] ) && !empty( $args['limit'] ) ) ? $args['limit'] : '';

                $categories = $wpdb->get_results(
                    "SELECT *
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE type='{$args['type']}' {$args['search']}
                    ORDER BY " . $args['order_by'] . ' ' . $args['order'] .
                    ' ' . $args['limit'],
                ARRAY_A );
            }

            return $categories;
        }


        /**
        *  Function for create wpc_client categories:
        *  1) File;
        *  2) Portal_page,
        *  3) Shutter (only in Shutter extension)
        *
        * ============= default struct ==============
        * $args = array(
        *       'id'
        *       'name'
        *       'parent_id'
        *       'type'
        *       'cat_clients'
        *       'cat_circles'
        *       'page'
        *       'tab'
        * );
        * ===========================================
        */
        function cc_create_category( $args ) {
            global $wpdb;

            if( !( isset( $args['type'] ) && !empty( $args['type'] ) ) ) {
                wp_redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'invalid' ), 'admin.php' ) );
                exit;
            }

            //if new or edit category name is empty
            if( '' == $args['name'] ) {
                wp_redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'null' ), 'admin.php' ) );
                exit;
            }

            //checking that new category not exist with other ID
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT id
                FROM {$wpdb->prefix}wpc_client_categories
                WHERE LOWER(name) = '%s' AND
                    type='%s'",
                strtolower( $args['name'] ),
                $args['type']
            ), ARRAY_A );


            //if new category exist with other ID
            if( isset( $result ) && !empty( $result ) && !( "0" != $args['id'] && $result['id'] == $args['id'] ) ) {
                wp_redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'ce' ), 'admin.php' ) );
                exit;
            }


            if ( '0' != $args['id'] ) {

                if( isset( $args['type'] ) && $args['type'] == 'file' ) {
                    $old_path = $this->cc_get_category_path( $args['id'] );
                }

                //update when edit category
                $wpdb->update(
                    "{$wpdb->prefix}wpc_client_categories",
                    array( 'name' => trim( $args['name'] ) ),
                    array( 'id' => $args['id'] )
                );

                if( isset( $args['type'] ) && $args['type'] == 'file' ) {
                    $new_path = $this->cc_get_category_path( $args['id'] );

                    //rename folder on FTP
                    if( is_dir( $old_path ) ) {
                        rename( $old_path, $new_path );
                    }
                }

                wp_redirect( add_query_arg( array( 'page' => $args['page'], 'tab' => $args['tab'], 'msg' => 'u' ), 'admin.php' ) );
                exit;

            } else {
                //create new category

                //get order number for new category
                $cat_order = $wpdb->get_var( $wpdb->prepare(
                    "SELECT
                    COUNT(id)
                    FROM {$wpdb->prefix}wpc_client_categories
                    WHERE parent_id=%d AND
                        type='%s'",
                    $args['parent_id'],
                    $args['type']
                ) );
                $cat_order++;

                //insert when add new category
                $wpdb->insert(
                    "{$wpdb->prefix}wpc_client_categories",
                    array(
                        'name'      => trim( $args['name'] ),
                        'type'      => $args['type'],
                        'parent_id' => $args['parent_id'],
                        'cat_order' => $cat_order
                    ),
                    array( '%s', '%s', '%d', '%d' )
                );

                $category_id = $wpdb->insert_id;



                //assigned process
                if( isset( $category_id ) && !empty( $category_id ) ) {
                    //set clients
                    $clients_array = array();
                    if ( isset( $args['cat_clients'] ) && !empty( $args['cat_clients'] ) )  {
                        if( $args['cat_clients'] == 'all' ) {
                            $clients_array = $this->acc_get_client_ids();
                        } else {
                            $clients_array = explode( ',', $args['cat_clients'] );
                        }
                    }

                    $this->cc_set_assigned_data( $args['type'] . '_category', $category_id, 'client', $clients_array );

                    //set Client Circle
                    $circles_array = array();
                    if ( isset( $args['cat_circles'] ) && !empty( $args['cat_circles'] ) )  {
                        if( $args['cat_circles'] == 'all' ) {
                            $circles_array = $this->cc_get_group_ids();
                        } else {
                            $circles_array = explode( ',', $args['cat_circles'] );
                        }
                    }
                    $this->cc_set_assigned_data( $args['type'] . '_category', $category_id, 'circle', $circles_array );
                }

            }

        }


        /**
        *  Function for reassign objects:
        *       1) Files;
        *       2) Portal_pages,
        *       3) Galleries (only in Shutter extension)
        *  in wpc_client categories with types:
        *       1) File;
        *       2) Portal_page,
        *       3) Shutter (only in Shutter extension)
        */
        function cc_reassign_object_from_category( $type, $old_id, $new_id ) {
            global $wpdb;

            if( isset( $type ) && 'shutter' == $type ) {

                $args = array(
                    'post_type' => 'wps-gallery',
                    'meta_query' => array(
                        array(
                            'key' => '_wpc_category_id',
                            'value' => $old_id
                        )
                    )
                );

                $postslist = get_posts( $args );

                foreach( $postslist as $post ) {
                    update_post_meta( $post->ID, '_wpc_category_id', $new_id );
                }

            } elseif( isset( $type ) && 'portal_page' == $type ) {

                $args = array(
                    'post_type' => 'clientspage',
                    'meta_query' => array(
                        array(
                            'key' => '_wpc_category_id',
                            'value' => $old_id
                        )
                    )
                );

                $postslist = get_posts( $args );

                foreach( $postslist as $post ) {
                    update_post_meta( $post->ID, '_wpc_category_id', $new_id );
                }

            } elseif( isset( $type ) && 'file' == $type ) {

            }
        }


        /**
        *  Function for delete wpc_client categories with types:
        *       1) File;
        *       2) Portal_page,
        *       3) Shutter (only in Shutter extension)
        */
        function cc_delete_category( $id, $type ) {
            global $wpdb;

            //delete category from database
            $wpdb->delete(
                "{$wpdb->prefix}wpc_client_categories",
                array(
                    'id'    =>  $id
                )
            );

            //find all objects of category
            switch( $type ) {
                case 'file':
                break;
                case 'portal_page':
                    $args = array(
                        'post_type' => 'clientspage',
                        'post_status' => 'publish',
                        'meta_key' => '_wpc_category_id',
                        'meta_value' => $id
                     );

                    $postslist = get_posts( $args );

                    if( isset( $postslist ) && !empty( $postslist ) ) {
                        foreach( $postslist as $post ) {
                            wp_delete_post( $post->ID, true );

                            //delete all assigns for category objects
                            $this->cc_delete_all_object_assigns( $type, $post->ID );
                        }
                    }
                break;
                case 'shutter':
                    $args = array(
                        'post_type' => 'wps-gallery',
                        'post_status' => 'publish',
                        'meta_key' => '_wpc_category_id',
                        'meta_value' => $id
                     );

                    $postslist = get_posts( $args );

                    if( isset( $postslist ) && !empty( $postslist ) ) {
                        foreach( $postslist as $post ) {
                            wp_delete_post( $post->ID, true );

                            $attachment_ids = get_posts( array(
                                'post_type'         => 'wpc-sht-attachment',
                                'posts_per_page'    => -1,
                                'post_status'       => 'inherit',
                                'post_parent'       =>  $post->ID,
                                'fields'            => 'ids'
                            ) );

                            if( is_array( $attachment_ids ) && 0 < count( $attachment_ids ) ) {
                                foreach( $attachment_ids as $attachment_id ) {
                                    //delete attachment
                                    wp_delete_post( $attachment_id, true );

                                    //deleting from users shopping carts & items
                                    $shopping_carts_data = $wpdb->get_results(
                                        "SELECT *
                                        FROM {$wpdb->usermeta}
                                        WHERE meta_key='wpc_sht_shopping_cart'",
                                    ARRAY_A );

                                    if( isset( $shopping_carts_data ) && !empty( $shopping_carts_data ) ) {
                                        foreach( $shopping_carts_data as $usermeta ) {
                                            $usermeta['meta_value'] = unserialize( $usermeta['meta_value'] );

                                            if( isset( $usermeta['meta_value']['attachment_ids'] ) && !empty( $usermeta['meta_value']['attachment_ids'] ) ) {
                                                foreach( $usermeta['meta_value']['attachment_ids'] as $key=>$attachment ) {
                                                    if( $attachment['item'] == $attachment_id ) {
                                                        unset( $usermeta['meta_value']['attachment_ids'][$key] );
                                                    }
                                                }
                                            }

                                            update_user_meta( $usermeta['user_id'], 'wpc_sht_shopping_cart', $usermeta['meta_value'] );

                                        }
                                    }

                                    $my_items_data = $wpdb->get_results(
                                        "SELECT *
                                        FROM {$wpdb->usermeta}
                                        WHERE meta_key='wpc_sht_my_items'",
                                    ARRAY_A );

                                    if( isset( $my_items_data ) && !empty( $my_items_data ) ) {
                                        foreach( $my_items_data as $usermeta ) {
                                            $usermeta['meta_value'] = unserialize( $usermeta['meta_value'] );

                                            foreach( $usermeta['meta_value'] as $key=>$attachment ) {
                                                if( $attachment == $attachment_id ) {
                                                    unset( $usermeta['meta_value'][$key] );
                                                }
                                            }

                                            update_user_meta( $usermeta['user_id'], 'wpc_sht_my_items', $usermeta['meta_value'] );

                                        }
                                    }

                                }
                            }

                            //delete all assigns for category objects
                            $this->cc_delete_all_object_assigns( $type, $post->ID );
                        }
                    }

                break;
            }

            //delete all assigns for category
            $this->cc_delete_all_object_assigns( $type . '_category', $id );
        }


        /*
        *  Function for get and create uploads dir
        *
        */
        function get_upload_dir( $dir = '', $dir_access = false ) {

            if ( empty( $this->upload_dir ) ) {
                $uploads            = wp_upload_dir();
                $this->upload_dir   = $uploads['basedir'] . '/';
            }

            $dir = str_replace( '/', DIRECTORY_SEPARATOR, $dir );

            //check and create folder
            if ( !empty( $dir ) ) {
                $folders = explode( DIRECTORY_SEPARATOR, $dir );
                $cur_folder = '';
                foreach( $folders as $folder ) {
                    $cur_folder .= $folder . '/';
                    if ( !is_dir( $this->upload_dir . $cur_folder ) ) {
                        mkdir( $this->upload_dir . $cur_folder, 0777 );
                        if ( $dir_access || 'wpclient' == $folder ) {
                             $htp = fopen( $this->upload_dir . $cur_folder . '/.htaccess', 'w' );
                             fputs( $htp, 'deny from all' ); // $file being the .htpasswd file
                        }
                    }
                }
            }

            //return dir path
            return $this->upload_dir . $dir;

        }


        /**
        * function for getting price string
        */
        function cc_get_price_string( $price, $currency_id ) {

            $price_string = '';

            $currencies = $this->cc_get_settings( 'currency' );

            if( isset( $currencies[$currency_id] ) && !empty( $currencies[$currency_id] ) ) {
                $current_currency = $currencies[$currency_id];
                if( isset( $current_currency['symbol'] ) && !empty( $current_currency['symbol'] ) && isset( $current_currency['align'] ) && !empty( $current_currency['align'] ) ) {

                    switch( $current_currency['align'] ) {
                        case 'left':
                            $price_string = $current_currency['symbol'] . $price;
                        break;
                        case 'right':
                            $price_string = $price . $current_currency['symbol'];
                        break;
                        default:
                            $price_string = $current_currency['symbol'] . $price;
                        break;
                    }

                }
            }

            return $price_string;
        }


        function cc_get_default_currency() {
            global $wpdb;

            $currencies = $this->cc_get_settings( 'currency' );

            if( isset( $currencies ) && !empty( $currencies ) ) {
                foreach( $currencies as $key=>$currency ) {
                    if( $currency['default'] == '1' ) {
                        return $key;
                    }
                }
            }
        }


        function datetimeformat_php_to_js( $php_format ) {
            $SYMBOLS_MATCHING = array(
                // Day
                'd' => 'dd',
                'D' => 'D',
                'j' => 'd',
                'l' => 'DD',
                'N' => '',
                'S' => '',
                'w' => '',
                'z' => 'o',
                // Week
                'W' => '',
                // Month
                'F' => 'MM',
                'm' => 'mm',
                'M' => 'M',
                'n' => 'm',
                't' => '',
                // Year
                'L' => '',
                'o' => '',
                'Y' => 'yy',
                'y' => 'y',
                // Time
                'a' => 'tt',
                'A' => 'TT',
                'B' => '',
                'g' => 'h',
                'G' => 'H',
                'h' => 'hh',
                'H' => 'HH',
                'i' => 'mm',
                's' => 'ss',
                'u' => 'c'
            );
            $jqueryui_format = "";
            $escaping = false;
            for($i = 0; $i < strlen($php_format); $i++) {
                $char = $php_format[$i];
                if($char === '\\') { // PHP date format escaping character
                    $i++;
                    if($escaping) $jqueryui_format .= $php_format[$i];
                    else $jqueryui_format .= '\'' . $php_format[$i];
                    $escaping = true;
                } else {
                    if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
                    if(isset($SYMBOLS_MATCHING[$char]))
                        $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                    else
                        $jqueryui_format .= $char;
                }
            }
            return $jqueryui_format;
        }
    //end class
    }
}

?>
