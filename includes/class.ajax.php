<?php


if ( !class_exists( "WPC_Client_Ajax" ) ) {

    class WPC_Client_Ajax extends WPC_Client_Admin_Common {


        var $test_SMTP = false;

        /**
        * PHP 5 constructor
        **/
        function __construct() {

            $this->common_construct();
            $this->admin_common_construct();

            // run wp_password_generator_load() during admin_print_scripts
            add_action( 'admin_print_scripts', array( &$this, 'wp_password_generator_load' ) );

            add_action( 'wp_ajax_generate_password', array( &$this, 'wp_password_generator_generate' ) );

            //ajax actions
            add_action( 'wp_ajax_wpc_view_client', array( &$this, 'ajax_view_client' ) );
            add_action( 'wp_ajax_wpc_get_client_internal_notes', array( &$this, 'ajax_get_client_internal_notes' ) );
            add_action( 'wp_ajax_wpc_update_client_internal_notes', array( &$this, 'ajax_update_client_internal_notes' ) );
            add_action( 'wp_ajax_wpc_check_page_shortcode', array( &$this, 'ajax_check_page_shortcode' ) );

            add_action( 'wp_ajax_wpc_portal_pages_update_order', array( &$this, 'ajax_portal_pages_update_order' ) );

            add_action( 'wp_ajax_get_all_groups', array( &$this, 'ajax_get_all_groups' ) );
            add_action( 'wp_ajax_get_name', array( &$this, 'ajax_get_name' ) );


            //admin save template
            add_action( 'wp_ajax_wpc_save_template', array( &$this, 'ajax_admin_save_template' ) );

            //get capabilities for role
            add_action( 'wp_ajax_wpc_get_capabilities', array( &$this, 'ajax_get_capabilities' ) );

            //assign clients/circles
            add_action( 'wp_ajax_update_assigned_data', array( &$this, 'update_assigned_data' ) );

            //save enable custom redirects
            add_action( 'wp_ajax_wpc_save_enable_custom_redirects', array( &$this, 'ajax_save_enable_custom_redirects' ) );

            add_action( 'wp_ajax_wpc_ez_get_shortcode_settings', array( &$this, 'ajax_ez_get_shortcode_settings' ) );

            //set portal page client for preview
            add_action( 'wp_ajax_wpc_set_portal_page_client', array( &$this, 'ajax_set_portal_page_client' ) );


            //get options filter for payment history
            add_action( 'wp_ajax_wpc_get_options_filter_for_payments', array( &$this, 'ajax_get_options_filter_for_payments' ) );

            //get options filter for permissions report
            add_action( 'wp_ajax_wpc_get_options_filter_for_permissions', array( &$this, 'ajax_get_options_filter_for_permissions' ) );

            //get report for permissions report
            add_action( 'wp_ajax_wpc_get_report_for_permissions', array( &$this, 'ajax_get_report_for_permissions' ) );

            //get sections for style scheme
            add_action( 'wp_ajax_wpc_customizer_get_sections', array( &$this, 'ajax_customizer_get_sections' ) );

            //save allowed gateways
            add_action( 'wp_ajax_wpc_save_allow_gateways', array( &$this, 'ajax_save_allow_gateways' ) );

            //get gateway settings
            add_action( 'wp_ajax_wpc_get_gateway_setting', array( &$this, 'ajax_get_gateway_setting' ) );

            //dismiss admin notice
            add_action( 'wp_ajax_wpc_dismiss_admin_notice', array( &$this, 'ajax_dismiss_admin_notice' ) );


            add_action( 'wp_ajax_wpc_settings', array( &$this, 'ajax_settings' ) );

            add_filter( 'widget_update_callback', array( &$this, 'widget_ajax_update_callback' ), 11, 4);                 // widget changes submitted by ajax method

            add_filter( 'wp_ajax_wpc_ez_hub_set_default', array( &$this, 'ajax_ez_hub_set_default' ), 11, 4);                 // widget changes submitted by ajax method
            add_filter( 'wp_ajax_wpc_return_to_admin_panel', array( &$this, 'ajax_return_to_admin_panel' ) );



        }


        function ajax_return_to_admin_panel() {
            global $wpdb;
            if( !empty( $_POST['secure_key'] ) ) {
                $verify = $_POST['secure_key'];
            } else {
                exit( json_encode( array( 'status' => false, 'message' => __( "Wrong data", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            if( !empty( $_COOKIE['wpc_key'] ) && is_user_logged_in() ) {
                $key = $_COOKIE['wpc_key'];
                $user_data = $wpdb->get_row( $wpdb->prepare( "SELECT umeta_id, user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'wpc_client_admin_secure_data' AND meta_value LIKE '%s'", '%"' . md5( $key ) . '"%' ), ARRAY_A );
                if( isset( $user_data['user_id'] ) && user_can( $user_data['user_id'], 'wpc_admin_user_login') && wp_verify_nonce( $verify, get_current_user_id() . $user_data['user_id'] ) ) {
                    if( !empty( $user_data['meta_value'] ) ) {
                        $secure_array = unserialize( $user_data['meta_value'] );
                        if( isset( $secure_array['end_date'] ) && $secure_array['end_date'] > time() ) {
                            wp_set_auth_cookie( $user_data['user_id'], true );
                            $wpdb->delete( $wpdb->usermeta,
                                array(
                                    'umeta_id' => $user_data['umeta_id']
                                )
                            );
                            $secure_logged_in_cookie = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
                            setcookie( "wpc_key", '', time() - 1, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
                            exit( json_encode( array( 'status' => true, 'message' => admin_url('admin.php?page=wpclient_clients') ) ) );
                        }
                    }
                }
            }

            exit( json_encode( array( 'status' => false, 'message' => __( "Wrong data", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }


        function ajax_ez_hub_set_default() {
            if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                $id = $_POST['id'];
                $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );
                if( isset( $wpc_ez_hub_templates[ $id ] ) ) {
                    foreach( $wpc_ez_hub_templates as $k=>$val ) {
                        if( isset( $wpc_ez_hub_templates[ $k ]['is_default'] ) ) {
                            unset( $wpc_ez_hub_templates[ $k ]['is_default'] );
                        }
                    }
                    $wpc_ez_hub_templates[ $id ]['is_default'] = 1;
                    do_action( 'wp_client_settings_update', $wpc_ez_hub_templates, 'ez_hub_templates' );
                    exit( json_encode( array( 'status' => true, 'message' => __( "Success", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                } else {
                    exit( json_encode( array( 'status' => false, 'message' => __( "Hub template does not exists.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                }
            } else {
                exit( json_encode( array( 'status' => false, 'message' => __( "Hub ID does not exists.", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }
        }


        // CALLED VIA 'widget_update_callback' FILTER (ajax update of a widget)
        function widget_ajax_update_callback( $instance, $new_instance, $this_widget, $obj ) {
            $widget_id = $obj->id;
            if ( isset( $_POST['wpc_show_page'] ) && is_array( $_POST['wpc_show_page'] ) ) {
                $options = get_option('wpc_widget_show_settings', array());
                $options = array_merge( $options, $_POST['wpc_show_page'] );
                update_option( 'wpc_widget_show_settings', $options );
            }
            return $instance;
        }



        function ajax_settings() {
            if( isset( $_POST['tab'] ) && !empty( $_POST['tab'] ) ) {
                $tab = $_POST['tab'];
                $action = isset( $_POST['act'] ) ? $_POST['act'] : '';
                switch( $_POST['act'] ) {
                    case 'add':
                        $default = isset( $_POST['default'] ) ? $_POST['default'] : 0;
                        if( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) {
                            $title = $_POST['title'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        if( isset( $_POST['code'] ) && !empty( $_POST['code'] ) ) {
                            $code = $_POST['code'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        if( isset( $_POST['symbol'] ) && !empty( $_POST['symbol'] ) ) {
                            $symbol = $_POST['symbol'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        $align = isset( $_POST['align'] ) ? $_POST['align'] : 'left';

                        $wpc_currency = $this->cc_get_settings( $tab );
                        if( $default ) {
                            foreach( $wpc_currency as $k=>$val ) {
                                $wpc_currency[ $k ]['default'] = 0;
                            }
                        }

                        $key = uniqid();
                        $wpc_currency[ $key ] = array(
                            'default' => $default,
                            'title' => $title,
                            'code' => $code,
                            'symbol' => $symbol,
                            'align' => $align
                        );

                        do_action( 'wp_client_settings_update', $wpc_currency, $tab );
                        exit( json_encode( array( 'status' => true, 'message' => $key ) ) );
                        break;
                    case 'edit':
                        if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                            $id = $_POST['id'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Code does not exists.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        $default = isset( $_POST['default'] ) ? $_POST['default'] : 0;
                        if( isset( $_POST['title'] ) && !empty( $_POST['title'] ) ) {
                            $title = $_POST['title'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Title is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        if( isset( $_POST['code'] ) && !empty( $_POST['code'] ) ) {
                            $code = $_POST['code'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        if( isset( $_POST['symbol'] ) && !empty( $_POST['symbol'] ) ) {
                            $symbol = $_POST['symbol'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }
                        $align = isset( $_POST['align'] ) ? $_POST['align'] : 'left';

                        $wpc_currency = $this->cc_get_settings( $tab );

                        if( $default ) {
                            foreach( $wpc_currency as $k=>$val ) {
                                $wpc_currency[ $k ]['default'] = 0;
                            }
                        }

                        $wpc_currency[ $id ] = array(
                            'default' => $default,
                            'title' => $title,
                            'code' => $code,
                            'symbol' => $symbol,
                            'align' => $align
                        );

                        do_action( 'wp_client_settings_update', $wpc_currency, $tab );
                        exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                        break;
                    case 'delete':
                        if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                            $id = $_POST['id'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        $wpc_currency = $this->cc_get_settings( $tab );
                        if( isset( $wpc_currency[ $id ]['default'] ) && $wpc_currency[ $id ]['default'] == 1 ) {
                            exit( json_encode( array( 'status' =>false, 'message' => __("You can't remove currency with default mark", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        $permission = apply_filters( 'wpc_currency_permission', $id );
                        if( isset( $permission ) && $permission != $id && !$permission ) {
                            exit( json_encode( array( 'status' =>false, 'message' => __("Currency already used", WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        if( isset( $wpc_currency[ $id ] ) ) {
                            unset( $wpc_currency[ $id ] );
                        }

                        do_action( 'wp_client_settings_update', $wpc_currency, $tab );
                        exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                        break;
                    case 'set_default':
                        if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                            $id = $_POST['id'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        $wpc_currency = $this->cc_get_settings( $tab );
                        foreach( $wpc_currency as $k=>$val ) {
                            $wpc_currency[ $k ]['default'] = 0;
                        }
                        if( isset( $wpc_currency[ $id ] ) ) {
                            $wpc_currency[ $id ]['default'] = 1;
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Key is wrong', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        do_action( 'wp_client_settings_update', $wpc_currency, $tab );
                        exit( json_encode( array( 'status' => true, 'message' => 'Success' ) ) );
                        break;
                    case 'get_data':
                        if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
                            $id = $_POST['id'];
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Key is empty', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        $wpc_currency = $this->cc_get_settings( $tab );
                        if( isset( $wpc_currency[ $id ] ) ) {
                            exit( json_encode( array( 'status' => true, 'message' => $wpc_currency[ $id ] ) ) );
                        } else {
                            exit( json_encode( array( 'status' =>false, 'message' => __('Key is wrong', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                        }

                        break;
                }
            }
            exit;
        }

        function wp_password_generator_load() {

            if ( isset( $_GET['tab'] ) && $_REQUEST['tab'] == 'add_client' ) {
                wp_enqueue_script('wp-password-generator', WP_PLUGIN_URL . '/wp-client/wp-password-generator.js', array('jquery'), '2.1', true);
            }

            return true;
        }


        function wp_password_generator_generate() {

            $opts = get_option('wp-password-generator-opts', false);

            if(!$opts || $opts['version'] < WP_PASSWORD_GENERATOR_VERSION_WPCLIENT) { // No options or an older version
                $this->wp_password_generator_install();
                $opts = get_option('wp-password-generator-opts', false);
            }

            $len = mt_rand($opts['min-length'], $opts['max-length']); // Min/max password lengths

            echo wp_generate_password($len, true, false);

            return true;
        }


        function wp_password_generator_install() {

            $defaults   = array('version' => WP_PASSWORD_GENERATOR_VERSION_WPCLIENT, 'min-length' => 7, 'max-length' => 16);
            $opts       = get_option('wp-password-generator-opts');

            if($opts) {
                // Remove 'characters', which was only used in version 2.1. We'll use whatever is defined in wp_generate_password()
                if(isset($opts['characters'])) {
                    unset($opts['characters']);
                }

                if(isset($opts['min-length']) && intval($opts['min-length']) > 0) {
                    $defaults['min-length'] = intval($opts['min-length']);
                }

                if(isset($opts['max-length']) && intval($opts['max-length']) >= $defaults['min-length']) {
                    $defaults['min-length'] = intval($opts['max-length']);
                }

                /*
                We've checked what we need to. If there are other items in $stored, let them stay ($defaults won't overwrite them)
                as some dev has probably spent some time adding custom functionality to the plugin.
                */
                $defaults = array_merge($opts, $defaults);
            }

            update_option('wp-password-generator-opts', $defaults);

            return true;
        }





        /*
        * Ajax function for get client details
        *
        * @return array json answer to js
        */
        function ajax_view_client() {

            if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
                echo json_encode( array( 'id' => '', 'warning' => true ) );
                exit;
            }

            $id = explode( '_', $_POST['id'] );

            //check id and hash
            if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientview_' . $id[0] ) == $id[1] ) {
                $client = get_userdata( $id[0] );


                $current_manager_ids = $this->cc_get_assign_data_by_assign( 'manager', 'client', $id[0] );

                $managers = array();
                if ( is_array( $current_manager_ids ) && count( $current_manager_ids ) ) {
                    foreach( $current_manager_ids as $key=>$current_manager_id ) {
                        $managers[$key] = get_userdata( $current_manager_id );
                        $managers[$key] = ( isset( $managers[$key] ) ) ? $managers[$key]->user_login : '';
                    }
                }

                $client_groups = $this->cc_get_client_groups_id( $id[0] );

                $groups = array();
                if ( is_array( $client_groups ) && count( $client_groups ) ) {
                    foreach ( $client_groups as $key=>$group_id ) {
                        $groups[$key] = $this->cc_get_group( $group_id );
                        $groups[$key] = ( isset( $groups[$key] ) ) ? $groups[$key]['group_name'] : '';
                    }
                }

                $business_name = get_user_meta( $id[0], 'wpc_cl_business_name', true );

                ob_start();
                ?>

                <style type="text/css">

                    #wpc_client_details_content input[type=text] {
                        width:400px;
                    }

                    #wpc_client_details_content input[type=password] {
                        width:400px;
                    }

                </style>

                <h2><?php _e( 'View Client', WPC_CLIENT_TEXT_DOMAIN ) ?>: <?php echo $client->user_login?></h2>

                <table class="form-table">
                    <tr>
                        <td>
                            <label><?php _e( 'Client Managers', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br />
                            <?php echo ( is_array( $managers ) && count( $managers ) ) ? '<span>' . implode( ', ', $managers ) . '</span> ' : __( 'None', WPC_CLIENT_TEXT_DOMAIN ); ?>
                       </td>
                    </tr>
                    <tr>
                        <td>
                            <label><?php _e( 'Client Circles', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label><br/>
                            <?php echo ( is_array( $groups ) && count( $groups ) ) ? '<span>' . implode( ', ', $groups ) . '</span> ' : __( 'None', WPC_CLIENT_TEXT_DOMAIN ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?>: </label> <br/>
                            <input type="text" readonly="readonly" value="<?php echo $business_name;?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" readonly="readonly" value="<?php echo $client->display_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" readonly="readonly" value="<?php echo $client->user_email ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" readonly="readonly" value="<?php echo $client->wp_contact_phone?>" />
                        </td>
                    </tr>

                    <?php
                    /*our_hook_
                        hook_name: wpc_client_view_client_after_custom_fields
                        hook_title: View Client Form
                        hook_description: Can be used for adding custom html on View Client Form.
                        hook_type: action
                        hook_in: wp-client
                        hook_location class.ajax.php
                        hook_param: int $client_id
                        hook_since: 3.3.5
                    */
                    do_action( 'wpc_client_view_client_after_custom_fields', $id[0] );
                    ?>
                </table>

                <?php

                $content = ob_get_contents();
                if( ob_get_length() ) {
                    ob_end_clean();
                }
                echo json_encode( array( 'content' => $content ) );
                exit;
            }

            echo json_encode( array( 'content' => '' ) );
            exit;
        }


        /*
        * Ajax function for get client internal notes
        *
        * @return array json answer to js
        */
        function ajax_get_client_internal_notes() {

            if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
                echo json_encode( array( 'id' => '', 'warning' => true ) );
                exit;
            }

            $id = explode( '_', $_POST['id'] );

            //check id and hash
            if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientinternalnote_' . $id[0] ) == $id[1] ) {
                $client = get_userdata( $id[0] );

                $internal_notes     = get_user_meta( $id[0], 'wpc__internal_notes', true );
                if ( $internal_notes ) {
                    echo json_encode( array( 'client_name' => $client->user_login, 'internal_notes' => $internal_notes ) );
                } else {
                    echo json_encode( array( 'client_name' => $client->user_login, 'internal_notes' => '' ) );
                }
                exit;
            }

            echo json_encode( array( 'internal_notes' => '' ) );
            exit;
        }


        /*
        * Ajax function for get client internal notes
        *
        * @return array json answer to js
        */
        function ajax_update_client_internal_notes() {

            if ( !isset( $_POST['id'] ) || !$_REQUEST['id'] ) {
                die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
            }

            $id = explode( '_', $_POST['id'] );

            //check id and hash
            if ( isset( $id[0] ) && $id[0] && isset( $id[1] ) && md5( 'wpcclientinternalnote_' . $id[0] ) == $id[1] ) {
                $client = get_userdata( $id[0] );

                if ( $client ) {
                    $internal_notes = ( isset( $_POST['notes'] ) ) ? base64_decode( str_replace( '-', '+', $_POST['notes'] ) ) : '';

                    update_user_meta( $id[0], 'wpc__internal_notes', $internal_notes );
                    die( json_encode( array('status' => true, 'message' => __( 'Notes is updated.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
                }
            }

            die( json_encode( array('status' => false, 'message' => __( 'Some problem with update.', WPC_CLIENT_TEXT_DOMAIN ) ) ) );
        }


        /*
        * Ajax function for checked content of page is consist shortcode
        *
        * @return array json answer to js
        */
        function ajax_check_page_shortcode() {

            if ( !isset( $_REQUEST['page_id'] ) || !$_REQUEST['page_id'] ) {
                echo json_encode( array( 'id' => '', 'warning' => true ) );
                exit;
            }

            if ( !isset( $_REQUEST['shortcode_type'] ) || !$_REQUEST['shortcode_type'] ) {
                echo json_encode( array( 'warning' => false ) );
                exit;
            }

            $shortcode_type = $_REQUEST['shortcode_type'];
            $page_id = $_REQUEST['page_id'];

            $page = get_page( $page_id );

            if ( false === strpos( $page->post_content, $shortcode_type ) ) {
                echo json_encode( array( 'nes_shortcode' => $shortcode_type, 'id' => $page_id, 'warning' => true ) );
            } else {
                echo json_encode( array( 'id' => $page_id, 'warning' => false ) );
            }
            exit;

        }


         /*
        * Ajax function for update order of "Portal Pages" page
        */
        function ajax_portal_pages_update_order() {
            if ( isset( $_POST['post_id'] ) ) {
                $order =  ( isset( $_POST['clientpage_order'] ) && '' != (int) $_POST['clientpage_order']  && 0 < (int) $_POST['clientpage_order'] ) ? (int) $_POST['clientpage_order'] : 0;
                update_post_meta( $_POST['post_id'], '_wpc_order_id', $order );
                $value = get_post_meta(  $_POST['post_id'], '_wpc_order_id', true );
                echo json_encode( array( 'my_value' => $value ) );
                exit;
            }
        }


         /**
         * AJAX - Get all Client Circles
         **/
         function ajax_get_all_groups() {
            global $wpdb;

            $groups = $this->cc_get_groups();

            if ( is_array( $groups ) && 0 < count( $groups ) ) {

                $i = 0;
                $n = ceil( count( $groups ) / 5 );

                $html = '';
                $html .= '<ul class="clients_list">';



                foreach ( $groups as $group ) {
                    if ( $i%$n == 0 && 0 != $i )
                        $html .= '</ul><ul class="clients_list">';

                    $html .= '<li><label>';
                    $html .= '<input type="checkbox" name="groups_id[]" value="' . $group['group_id'] . '" /> ';
                    $html .= $group['group_id'] . ' - ' . $group['group_name'];
                    $html .= '</label></li>';

                    $i++;
                }

                $html .= '</ul>';
            } else {
                $html = 'false';
            }

            die( $html );

         }


         /*
         *Get client login or  circle name by ajax request
         */
         function ajax_get_name() {
             if( isset( $_POST['type'] ) && isset( $_POST['id'] ) ) {
                 switch( $_POST['type'] ) {
                     case 'wpc_clients':
                        $userdata = get_userdata( $_POST['id'] );
                        echo json_encode( array( 'status' => true, 'name' => $userdata->get('user_login') ) );
                        break;
                     case 'wpc_circles':
                        $res = $this->cc_get_group( $_POST['id'] );
                        echo json_encode( array( 'status' => true, 'name' => $res['group_name'] ) );
                        break;
                     default:
                        echo json_encode( array( 'status' => false, 'message' => __( 'Wrong type', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        break;
                 }
             }
             exit;
         }


        /**
        * AJAX save template
        **/
        function ajax_admin_save_template() {
            global $wpdb;

            if ( isset( $_POST['wpc_templates'] ) && is_array( $_POST['wpc_templates'] ) ) {
                $opt_key = array_keys( $_POST['wpc_templates'] );
                $opt_name = $opt_key[0];
                $template = $_POST['wpc_templates'][$opt_name];

                //update settings
                if ( 'wpc_templates_shortcodes' == $opt_name ) {
                    $temp_key = array_keys( $template );
                    $temp_key = $temp_key[0];

                    $wpc_templates_shortcodes_settings = $this->cc_get_settings( 'templates_shortcodes_settings' );

                    if ( isset( $_POST['wpc_templates_settings']['wpc_templates_shortcodes'][$temp_key]['allow_php_tag'] ) && 'yes' == $_POST['wpc_templates_settings']['wpc_templates_shortcodes'][$temp_key]['allow_php_tag'] ) {
                        $wpc_templates_shortcodes_settings[$temp_key]['allow_php_tag'] = 'yes';
                    } else {
                        $wpc_templates_shortcodes_settings[$temp_key]['allow_php_tag'] = 'no';
                    }

                    do_action( 'wp_client_settings_update', $wpc_templates_shortcodes_settings, 'templates_shortcodes_settings' );
                }


            }


            if ( isset( $template ) ) {

                $templates_data = get_option( $opt_name );
                $res    = $this->acc_array_base64_decode( $template );
                $keys   = $this->cc_show_keys( $res );
                switch( count( $keys ) ) {
                    case 1:
                        $templates_data[$keys[0]] = $res[$keys[0]];
                        break;
                    case 2:
                        $templates_data[$keys[0]][$keys[1]] = $res[$keys[0]][$keys[1]];
                        break;
                    case 3:
                //                        $templates_data[$keys[0]][$keys[1]][$keys[2]] = $res[$keys[0]][$keys[1]][$keys[2]];
                        $templates_data[$keys[0]][$keys[1]] = $res[$keys[0]][$keys[1]];
                        $templates_data[$keys[0]][$keys[2]] = $res[$keys[0]][$keys[2]];
                        break;
                }

                update_option( $opt_name, $templates_data );
                echo json_encode( array( 'status' => true, 'message' => __( 'Template success updated.', WPC_CLIENT_TEXT_DOMAIN ) ) );

                }

                exit;
            }




         /**
         * AJAX get options filter for permissions report
         **/
         function ajax_get_options_filter_for_permissions() {
             global $wpdb;
             if ( isset( $_POST['left_select'] ) ) {
                 switch( $_POST['left_select'] ) {
                    case 'client':
                        $excluded_clients = "'" . implode( "','", $this->cc_get_excluded_clients() ) . "'";

                        echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</option>';
                        $clients = $wpdb->get_results( "SELECT u.ID as id, u.user_login as login
                                    FROM {$wpdb->users} u
                                    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                                    WHERE
                                        um.meta_key = '{$wpdb->prefix}capabilities'
                                        AND um.meta_value LIKE '%s:10:\"wpc_client\";%'
                                        AND u.ID NOT IN ({$excluded_clients})
                                    ", ARRAY_A );

                        if ( 0 < count( $clients ) ) {
                            foreach( $clients as $client ) {
                                echo '<option value="' . $client['id'] . '">' . $client['login'] . '</option>';
                            }
                        }
                        break;

                    case 'circle':

                        $circles = $wpdb->get_results( "SELECT group_id as id, group_name as name
                                    FROM {$wpdb->prefix}wpc_client_groups
                                    ", ARRAY_A );

                        echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['s'] ) . '</option>';
                        if ( 0 < count( $circles ) ) {
                            foreach( $circles as $circle ) {
                                echo '<option value="' . $circle['id'] . '">' . $circle['name'] . '</option>';
                            }
                        }
                        break;

                    case 'file':

                        break;

                    case 'file_category':

                        break;

                    case 'portal_page':
                        $portal_pages = $wpdb->get_results( "SELECT ID as id, post_title as name FROM {$wpdb->posts} WHERE post_type='clientspage'", ARRAY_A );

                        echo '<option value="all">' . sprintf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) . '</option>';
                        if ( 0 < count( $portal_pages ) ) {
                            foreach( $portal_pages as $portal_page ) {
                                echo '<option value="' . $portal_page['id'] . '">' . $portal_page['name'] . '</option>';
                            }
                        }
                        break;

                    case 'portal_page_category':

                        break;
                 }
             }
             exit;
         }


         /**
         * AJAX get report for permissions report
         **/
         function ajax_get_report_for_permissions() {
             global $wpdb;
             //add key to the end array
             $all_left_key    = array( 'client', 'circle', 'file', 'file_category', 'portal_page', 'portal_page_category' );
             $array_key       = array( 'client' => __( 'Client', WPC_CLIENT_TEXT_DOMAIN ),
                                       'circle' => __( 'Circle', WPC_CLIENT_TEXT_DOMAIN ),
                                       'file' => __( 'File', WPC_CLIENT_TEXT_DOMAIN ),
                                       'file_category' => __( 'File Category', WPC_CLIENT_TEXT_DOMAIN ),
                                       'portal_page' => $this->custom_titles['portal']['s'],
                                       'portal_page_category' => sprintf( __( '%s Category', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) );
             $all_right_key   = array( 'all', 'client', 'circle', 'file', 'file_category', 'portal_page', 'portal_page_category' );

             if ( isset( $_POST['left_key'] ) && in_array( $_POST['left_key'], $all_left_key ) && isset( $_POST['left_value'] ) && is_numeric( $_POST['left_value'] ) && $_POST['right_key'] && in_array( $_POST['right_key'], $all_right_key ) ) {

                 if ( 'there' == $_POST['course'] )
                    $there = true;
                 else $there = false;

                 //create circle name array
                 $circles = $wpdb->get_results( "SELECT group_id as id, group_name as name FROM {$wpdb->prefix}wpc_client_groups", ARRAY_A );
                 foreach ( $circles as $value ) $name_circles[ $value['id'] ] = $value['name'];

                 $name_cats_file = $name_cats_portal_page = array();


                 //which blocks report
                 if ( 'all' == $_POST['right_key'] ) {
                     if ( $there ) {
                         $blocks_report = $all_right_key;
                         array_shift( $blocks_report );array_shift( $blocks_report );array_shift( $blocks_report );
                     } else {
                         $blocks_report = array( 'client', 'circle' );
                     }
                 } else {
                     $blocks_report = array( $_POST['right_key'] );
                 }

                 //bloks
                 foreach ( $blocks_report as $block ) {
                    $items = $ids_circle = array();
                    if ( $there ) {
                        $where_object_type  = " WHERE object_type='$block'";
                        $and_assign_type    = " AND assign_type='{$_POST['left_key']}'";
                        $and_id             = " AND assign_id='{$_POST['left_value']}'";
                        $what_select        = "object_id";
                        $which_class        = "block_left";

                        //items circles
                        if (  'client' == $_POST['left_key'] ) {
                            $ids_circle = $wpdb->get_col( "SELECT group_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id='{$_POST['left_value']}'" );
                            $sql_items_circle = "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type AND assign_type='circle' AND assign_id='%d'";

                            //items circle->category
                            if ( 'file' == $block || 'portal_page' == $block ) {
                                $name_array_cat = 'name_cats_' . $block;
                                $name_cats = $$name_array_cat;
                                $temp = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $ids_circle ) . "')", ARRAY_A );
                                foreach ( $temp as $value ) $names_circles[ $value['group_id'] ] = $value['group_name'];
                                foreach ( $ids_circle as $circle ) {
                                    $ids_circle_cats = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns  WHERE object_type='{$block}_category' AND assign_type='circle' AND assign_id='$circle'" );
                                    foreach ( $ids_circle_cats as $cat ) {
                                        if ( 'file' == $block )
                                            $ids_items_circle_cat = 0;
                                        else if ( 'portal_page' == $block )
                                            $ids_items_circle_cat = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND meta_value='$cat'" );
                                        $items = array_merge( $items, $ids_items_circle_cat );
                                        foreach ( $ids_items_circle_cat as $item )
                                            $all_access[ $item ][] = '
                                                <span class="block_left">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_blue" title="' . $names_circles[ $circle ] .'">' . $names_circles[ $circle ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>
                                                <span class="block_right">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span  class="value_block block_yellow" title="' . $name_cats[ $cat ] . '">' . $name_cats[ $cat ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>' ;
                                    }
                                }
                            }
                        }
                        //items categories
                        if ( 'file' == $block || 'portal_page' == $block ) {
                            $name_array_cat = 'name_cats_' . $block;
                            $name_cats = $$name_array_cat;
                            $ids_cats_assign = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$block}_category' $and_assign_type $and_id" );
                            //items categories circles
                            /*to delete
                            if (  'client' == $_POST['left_key'] ) {
                                foreach ( $ids_cats_assign as $cat ){
                                    $circles_cats = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$block}_category' AND object_id='$cat' AND assign_type='circle'" );
                                    foreach ( $circles_cats as $circle ) {
                                        $ids_items_cat_circle = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type AND assign_type='circle' AND assign_id='$circle'" );
                                        $items = array_merge( $items, $ids_items_cat_circle );
                                        foreach ( $ids_items_cat_circle as $id_item_cat_circle ) $all_access[ $id_item_cat_circle ]['category_circle'][] = $name_cats[ $cat ] . "->" . $name_circles [ $circle ] ;
                                    }
                                }
                            }*/
                            if ( 'file' == $block )
                                $sql_items_cat = false;
                            if ( 'portal_page' == $block )
                                $sql_items_cat = "SELECT post_id as id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND meta_value='%d'";
                            foreach ( $ids_cats_assign as $cat ) {
                                $ids_items_cat = $wpdb->get_col( $wpdb->prepare( $sql_items_cat, $cat ) );
                                $items = array_merge( $items, $ids_items_cat );
                                foreach ( $ids_items_cat as $id_item_cat )
                                    $all_access[ $id_item_cat ][] = '
                                        <span class="block_right">
                                            <span class="block_arrow"></span>
                                            <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                            <span class="value_block block_yellow" title="' . $name_cats[ $cat ] .'">' . $name_cats[ $cat ] . '</span>
                                            <span class="unname_block"></span>
                                        </span>' ;
                            }
                            $ids_items_cat = $wpdb->get_col( "SELECT post_id as id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND meta_value IN ('" . implode( "','", $ids_cats_assign ) . "')" );

                        }
                    } else { //if ( !$there )
                        $where_object_type  = " WHERE object_type='{$_POST['left_key']}'";
                        $and_assign_type    = " AND assign_type='$block'";
                        $and_id             = " AND object_id='{$_POST['left_value']}'";
                        $what_select        = "assign_id";
                        $which_class        = "block_right";
                        //circles
                        if (  'client' == $block ) {
                            $ids_circle = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type AND assign_type='circle' $and_id" );
                            $sql_items_circle = "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id='%d'";
                        }
                        //categories
                        if ( 'file' == $_POST['left_key'] || 'portal_page' == $_POST['left_key'] ) {
                            if ( 'file' == $_POST['left_key'] )
                                $id_cat_object = false;
                            if ( 'portal_page' == $_POST['left_key'] )
                                $id_cat_object = $wpdb->get_var( "SELECT meta_value as id FROM {$wpdb->postmeta} WHERE meta_key='_wpc_category_id' AND post_id='{$_POST['left_value']}'" );
                            if ( isset( $id_cat_object ) ) {
                                $ids_items_cat = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$_POST['left_key']}_category' $and_assign_type  AND object_id='$id_cat_object'" );
                                $items = array_merge( $items, $ids_items_cat );
                                $cat_name = $wpdb->get_var( "SELECT cat_name FROM {$wpdb->prefix}wpc_client_{$_POST['left_key']}_categories WHERE cat_id='$id_cat_object'" );
                                foreach ( $ids_items_cat as $id_item_cat )
                                    $all_access[ $id_item_cat ][] = '
                                        <span class="block_left">
                                            <span class="block_arrow"></span>
                                            <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                            <span class="value_block block_yellow" title="' . $cat_name .'">' . $cat_name . '</span>
                                            <span class="unname_block"></span>
                                        </span>';
                                if (  'client' == $block ) {
                                    $ids_cat_circles = $wpdb->get_col( "SELECT assign_id FROM {$wpdb->prefix}wpc_client_objects_assigns WHERE object_type='{$_POST['left_key']}_category' AND object_id='$id_cat_object' AND assign_type='circle'" );
                                    foreach( $ids_cat_circles as $circle ) {
                                        $ids_items_cat_circle = $wpdb->get_col( "SELECT client_id FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id='$circle'" );
                                        $items = array_merge( $items, $ids_items_cat_circle );
                                        foreach ( $ids_items_cat_circle as $item )
                                            $all_access[ $item ][] = '
                                                <span class="block_left">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'category', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_yellow" title="' . $cat_name .'">' . $cat_name . '</span>
                                                    <span class="unname_block"></span>
                                                </span>
                                                <span class="block_right">
                                                    <span class="block_arrow"></span>
                                                    <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                                    <span class="value_block block_blue" title="' . $name_circles[ $circle ] .'">' . $name_circles[ $circle ] . '</span>
                                                    <span class="unname_block"></span>
                                                </span>';
                                    }
                                }
                            }
                        }
                    }
                    //universal
                    //direct
                    $ids_items_direct = $wpdb->get_col( "SELECT $what_select FROM {$wpdb->prefix}wpc_client_objects_assigns $where_object_type $and_assign_type $and_id" );
                    foreach ( $ids_items_direct as $id_item_direct ) $all_access[ $id_item_direct ][] = '<span>' . __( 'DIRECT', WPC_CLIENT_TEXT_DOMAIN ) . '<br /><br /></span>';
                    $items = array_merge( $items, $ids_items_direct ) ; //may be kick

                    //items of circles
                    if ( 0 < count( $ids_circle ) ) {
                        $temp = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $ids_circle ) . "')", ARRAY_A );
                        foreach ( $temp as $value ) $names_circles[ $value['group_id'] ] = $value['group_name'];
                        foreach ( $ids_circle as $id_circle ) {
                            $ids_items_circle = $wpdb->get_col( $wpdb->prepare( $sql_items_circle, $id_circle ) );
                            $items = array_merge( $items, $ids_items_circle );
                            foreach ( $ids_items_circle as $id_item_circle )
                                $all_access[ $id_item_circle ][] = '
                                    <span class="' . $which_class . '">
                                        <span class="block_arrow"></span>
                                        <span class="name_block">' . __( 'circle', WPC_CLIENT_TEXT_DOMAIN ) . '</span>
                                        <span class="value_block block_blue" title="' . $name_circles[ $id_circle ] .'">' . $name_circles[ $id_circle ] . '</span>
                                        <span class="unname_block"></span>
                                    </span>';
                        }
                    }

                    //items of categories
                    if ( isset( $ids_cat ) ) {

                    }




                    echo '<table class="wc_status_table widefat cellspassing_up" width="750px"><caption>' . $array_key[ $block ] . '</caption><thead><tr><th  width="600px"><b>' . __( 'Access granted via...', WPC_CLIENT_TEXT_DOMAIN ) . '</b></th><th width="150px"><b>' . __( 'Name', WPC_CLIENT_TEXT_DOMAIN ) . '</b></th></tr></thead><tbody>';
                    $items = array_unique( $items );
                    if ( count( $items ) ) {
                        $names_items = $temp = array();
                        switch( $block ){
                            case 'client':
                                $temp = $wpdb->get_results( "SELECT ID as id, user_login as name FROM {$wpdb->users} WHERE ID IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                            break;
                            case 'circle':
                                $temp = $wpdb->get_results( "SELECT group_id as id, group_name as name FROM {$wpdb->prefix}wpc_client_groups WHERE group_id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                            break;
                            case 'file':
                                $temp = false;
                            break;
                            case 'file_category':
                                $temp = $wpdb->get_results( "SELECT cat_id as id, cat_name as name FROM {$wpdb->prefix}wpc_client_file_categories WHERE cat_id IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                            break;
                            case 'portal_page':
                                $temp = $wpdb->get_results( "SELECT ID as id, post_title as name FROM {$wpdb->posts} WHERE ID IN ('" . implode( "','", $items ) . "')", ARRAY_A );
                            break;
                            case 'portal_page_category':
                            break;
                        }
                        foreach ( $temp as $value ) {
                            $names_items[ $value['id'] ] = $value['name'];
                        }
                        foreach ( $items as $key => $item ) {
                            if ( isset( $all_access[ $item ] ) ) {
                                $this_block = false;
                                foreach ( $all_access[ $item ] as $access ) {
                                    echo '<tr class="tr_permissions"><td class="td_permissions">' . $access . '</td>';
                                    if( !$this_block ) {
                                        echo  '<td class="td_name_permissions" rowspan="' . count( $all_access[ $item ] ) . '" ><span>' . $names_items[ $item ] . '</span></td>';
                                        $this_block = true;
                                    }
                                    echo '</tr>';
                                }

                            }
                        }
                    } else echo '<tr><td colspan="2" align="center">' . __( 'Nothing found', WPC_CLIENT_TEXT_DOMAIN ) . '</td></tr>';

                    echo '</tbody></table>';
                 }
             }
             exit;
         }


         /**
         * AJAX get options filter for payments
         **/
         function ajax_get_options_filter_for_payments() {
             global $wpdb;
             if ( isset( $_POST['filter'] ) ) {
                 switch( $_POST['filter'] ) {
                    case 'client':
                        $unique_clients = $wpdb->get_col( "SELECT DISTINCT client_id FROM {$wpdb->prefix}wpc_client_payments" );
                        ?>
                        <option value="-1" selected="selected"><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?></option>
                        <?php
                        if ( is_array( $unique_clients ) && 0 < count( $unique_clients ) ) {
                            foreach( $unique_clients as $client_id ) {
                                if ( '' != $client_id ) {
                                    echo '<option value="' . $client_id . '">' . get_userdata( $client_id )->user_login . '</option>';
                                }
                            }
                        }
                        break;

                    case 'function':
                        $all_functions = $wpdb->get_col( "SELECT DISTINCT function FROM {$wpdb->prefix}wpc_client_payments" );
                        ?>
                        <option value="-1" selected="selected"><?php _e( 'Select Function', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        <?php
                        if ( is_array( $all_functions ) && 0 < count( $all_functions ) ) {
                            foreach( $all_functions as $function ) {
                                if ( '' != $function ) {
                                    echo '<option value="' . $function . '">' . $function . '</option>';
                                }
                            }
                        }
                        break;
                 }
             }
             exit;
         }


        /*
        * AJAX get capabilities for role
        */
        function ajax_get_capabilities() {
            $caps = '';

            if ( isset( $_POST['wpc_role'] ) && '' != $_POST['wpc_role'] ) {
                $wpc_capabilities = $this->cc_get_settings( 'capabilities' );

                $capabilities_maps = $this->acc_get_capabilities_maps();

                if ( isset( $capabilities_maps[$_POST['wpc_role']] ) ) {
                    $s_caps =  isset( $wpc_capabilities[$_POST['wpc_role']] ) ? $wpc_capabilities[$_POST['wpc_role']] : array();

                    foreach ( $capabilities_maps[$_POST['wpc_role']] as $cap_key => $cap_val ) {
                        if ( '' != $cap_key ) {
                            $checked = '';
                            if ( !isset( $s_caps[$cap_key] ) && true == $cap_val['cap'] ) {
                                $checked = 'checked';
                            } elseif ( isset( $s_caps[$cap_key] ) && 1 == $s_caps[$cap_key] ) {
                                $checked = 'checked';
                            }


                            $caps .= '
                                <input type="checkbox" name="capabilities[' . $cap_key . ']" ' . $checked . ' id="' . $cap_key . '" value="1" />
                                <label for="' . $cap_key . '"><span class="description">' . $cap_val['label'] . '</span></label>
                                <br />
                                ';
                        }
                    }
                }

            }

            echo json_encode( array( 'caps' => $caps ) );
            exit;
        }

         /**
         * AJAX update assigned clients\cicles
         **/
        function update_assigned_data() {
             global $wpdb;
             $data = '';
             if( isset($_POST['data_type']) && !empty($_POST['data_type']) && isset($_POST['current_page']) && !empty($_POST['current_page']) ) {
                 $current_page = $_POST['current_page'];
                 $datatype = $_POST['data_type'];
                 do_action( 'wpc_assign_popup_update_additional_data', $_POST, array(
                    'current_page' => $current_page,
                    'data_type' => $datatype
                 ) );
                 switch( $current_page ) {
                    case 'wpclient_clients':
                        switch($datatype) {
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];


                                    $all_id_groups = $this->cc_get_group_ids();


                                    $delete_grous = implode( ',' , $all_id_groups );
                                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE client_id = %d AND group_id IN($delete_grous)", $id ) );

                                    if( 'all' == $_POST['data'] ) {
                                        $check_id_groups = $all_id_groups;
                                    } else {
                                        if( !empty( $_POST['data'] ) ) {
                                            $check_id_groups = explode( ',', $_POST['data'] );
                                        } else {
                                            $check_id_groups = array();
                                        }
                                    }

                                    if ( count( $check_id_groups ) ) {
                                        $values = '';
                                        foreach( $check_id_groups as $id_group ) {
                                            $values .= "( '$id_group', '$id'  ),";
                                        }

                                        $values = substr( $values, 0, -1 );
                                        $wpdb->query( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients ( `group_id`, `client_id` ) VALUES $values" );
                                    }
                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;


                            //case: ''
                        }
                        break;

                    case 'wpclients_portal_pages':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'portal_page', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( 'all' == $_POST['data'] ) {

                                        $circles = $wpdb->get_col(
                                            "SELECT group_id
                                            FROM {$wpdb->prefix}wpc_client_groups"
                                        );

                                        $assign_data = ( is_array( $circles ) && 0 < count( $circles ) ) ? $circles : array();

                                    } else {
                                        if( !empty( $_POST['data'] ) ) {
                                            $assign_data = explode( ',', $_POST['data'] );
                                        }
                                    }


                                    $this->cc_set_assigned_data( 'portal_page', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;

                    case 'wpclients_invoicingrepeat_invoices':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'repeat_invoice', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'repeat_invoice', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;

                    case 'wpclients_files':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'file', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( 'all' == $_POST['data'] ) {
                                        $assign_data = $this->cc_get_group_ids();

                                    } else {
                                        if( !empty( $_POST['data'] ) ) {
                                            $assign_data = explode( ',', $_POST['data'] );
                                        }
                                    }

                                    $this->cc_set_assigned_data( 'file', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;

                    case 'wpclients_managers':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];
                                    //assign process
                                    $assign_data = array();

                                    if( $_POST['data'] == 'all' ) {
                                        $assign_data = $this->acc_get_client_ids();
                                    } elseif( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'manager', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'manager', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }

                            break;
                        }
                        break;

                    case 'wpclients_filescat':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'file_category', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'file_category', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;

                    case 'wpclientspage_categories':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'portal_page_category', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();
                                    if( 'all' == $_POST['data'] ) {

                                        $circles = $wpdb->get_col(
                                            "SELECT group_id
                                            FROM {$wpdb->prefix}wpc_client_groups"
                                        );

                                        $assign_data = ( is_array( $circles ) && 0 < count( $circles ) ) ? $circles : array();

                                    } else {
                                        if( !empty( $_POST['data'] ) ) {
                                            $assign_data = explode( ',', $_POST['data'] );
                                        }
                                    }

                                    $this->cc_set_assigned_data( 'portal_page_category', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => 'Completed' ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;

                    case 'wpclients_groups':
                        if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                            $id = $_POST['id'];
                            $excluded_clients  = $this->cc_get_excluded_clients();
                            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpc_client_group_clients WHERE group_id = %d AND client_id NOT IN('" . implode( ',', $excluded_clients ) . "')", $id ) );
                            if( 'all' == $_POST['data'] ) {
                               //all clients
                                $args = array(
                                    'role'      => 'wpc_client',
                                    'exclude'   => $excluded_clients,
                                    'fields'    => array( 'ID' ),
                                );

                                $clients = get_users( $args );

                                foreach ( $clients as $client ) {
                                    //$data .= '#'.$client->ID.",";
                                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $id,  $client->ID ) );
                                }
                            } else {
                                if(!empty($_POST['data'])) {
                                    $data = explode(',', $_POST['data']);
                                } else {
                                    $data = array();
                                }
                                foreach ( $data as $data_item ) {
                                    $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_group_clients SET group_id = %d, client_id = '%d'", $id,  $data_item ) );
                                }
                            }
                            echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        } else {
                            echo json_encode( array( 'status' => false, 'message' => __( 'Empty ID or data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                        }
                        break;

                    case 'wpclients_templates_ez_hub':
                        switch($datatype) {
                            case 'wpc_clients':
                                if( isset( $_POST['id'] ) && !empty( $_POST['id'] ) && isset( $_POST['data'] ) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }

                                    $this->cc_set_assigned_data( 'ez_hub', $id, 'client', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode (array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                                break;
                            case 'wpc_circles':
                                if( isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['data']) ) {
                                    $id = $_POST['id'];

                                    $assign_data = array();

                                    if( !empty( $_POST['data'] ) ) {
                                        $assign_data = explode( ',', $_POST['data'] );
                                    }


                                    $this->cc_set_assigned_data( 'ez_hub', $id, 'circle', $assign_data );

                                    echo json_encode( array( 'status' => true, 'message' => __( 'Completed', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                } else {
                                    echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
                                }
                            break;
                        }
                        break;
                 }
             } else {
                 echo json_encode( array( 'status' => false, 'message' => __( 'Wrong update data.', WPC_CLIENT_TEXT_DOMAIN ) ) );
             }
             exit;
         }


         /**
         * AJAX save template
         **/
         function ajax_save_enable_custom_redirects() {
             global $wpdb;

             if ( isset( $_POST['wpc_enable_custom_redirects'] ) && in_array( $_POST['wpc_enable_custom_redirects'], array( 'yes', 'no' ) ) ) {
                 update_option( 'wpc_enable_custom_redirects', $_POST['wpc_enable_custom_redirects'] );

                 echo json_encode( array( 'status' => true, 'message' => __( 'Saved!', WPC_CLIENT_TEXT_DOMAIN ) ) );

             }
             exit;
         }


        /**
        * AJAX Function for get ez shortcode settings
        */
         function ajax_ez_get_shortcode_settings() {
            if ( isset( $_POST['key'] ) && isset( $_POST['i'] ) ) {
                $type = ( isset( $_POST['type'] ) && !empty( $_POST['type'] ) ) ? $_POST['type'] : 'ez';
                $data = apply_filters( 'wpc_client_ez_hub_' . $_POST['key'], array(), array(), $_POST['i'], $type ) ;
                echo json_encode( $data );
            }
            die();
         }


        /**
        * AJAX Function for set portal page client
        */
         function ajax_set_portal_page_client() {
            global $wpdb;

            if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
                if ( '' == session_id() )
                    session_start();

                $_SESSION['wpc_preview_client'] = $_POST['id'];
                echo json_encode( array( 'status' => true ) );
            } else {
                echo json_encode( array( 'status' => false ) );
            }
            exit;
         }


        /**
        * AJAX Function for set portal page client
        */
         function ajax_customizer_get_sections() {
            global $wpdb;

            if ( !isset( $_POST['wpc_scheme'] ) || empty( $_POST['wpc_scheme'] ) )
                 die( '' );


            include $this->plugin_dir . '/includes/class.customize.php';

            $wpc_client_customize = new WPC_Client_Customize();

//            ob_start();

                $content = $wpc_client_customize->_get_sections( $_POST['wpc_scheme'] );


//                $content = ob_get_contents();

//            ob_end_clean();


            $sections_header_content = $wpc_client_customize->get_sections_header( $_POST['wpc_scheme'] );


//            $content = preg_replace( '~>\s+<~m', '><', $content );

            echo json_encode( array( 'status' => true, 'content' => $content, 'sections_header_content' => $sections_header_content ) );

            die();
         }


        /**
        * AJAX Function for save allowed gateways
        */
         function ajax_save_allow_gateways() {
            if ( isset( $_POST['name'] ) && isset( $_POST['enable'] ) ) {

                $wpc_gateways = $this->cc_get_settings( 'gateways' );

                //see if there are checkboxes checked
                if ( '1' == $_POST['enable'] ) {
                    if ( isset( $wpc_gateways['allowed'] ) ) {

                    $wpc_gateways['allowed'][] = $_POST['name'];
                    $wpc_gateways['allowed'] = array_unique( $wpc_gateways['allowed'] );

                    }

                } else {
                    if ( isset( $wpc_gateways['allowed'] ) ) {

                    $wpc_gateways['allowed'] = array_diff ( $wpc_gateways['allowed'], array( $_POST['name'] ) );

                    }

                }

                do_action( 'wp_client_settings_update', $wpc_gateways, 'gateways' );


            }

            die();
         }


        /**
        * AJAX Function get settings for gateways
        */
         function ajax_get_gateway_setting() {
            global $wpc_payments_core, $wpc_gateway_active_plugins;

            //load gateways just on settings page
            $wpc_payments_core->load_gateway_plugins();

            if( count( $wpc_gateway_active_plugins ) && isset( $_GET['plugin'] ) ) {
                $wpc_gateways = $this->cc_get_settings( 'gateways' );

                foreach( $wpc_gateway_active_plugins as $plugin ) {
                    if ( isset( $plugin->plugin_name ) && $plugin->plugin_name == $_GET['plugin'] ) {
                        $plugin->create_settings_form( $wpc_gateways );
                    }
                }
            }

            die();
         }


        /**
        * AJAX Function dismiss admin notice
        */
         function ajax_dismiss_admin_notice() {
            if ( isset( $_POST['id'] ) && is_numeric( $_POST['id'] ) ) {
                $wpc_dismiss_admin_notice = $this->cc_get_settings( 'dismiss_admin_notice' );
                $wpc_dismiss_admin_notice[] = $_POST['id'];
                array_unique( $wpc_dismiss_admin_notice );

                do_action( 'wp_client_settings_update', $wpc_dismiss_admin_notice, 'dismiss_admin_notice' );
            }

            die();
         }




    //end class
    }


    $wpc_client = new WPC_Client_Ajax();
}

?>
