<?php
/*
Core for Payments
*/

define( 'WPC_GATEWAYS_TD', 'wp-client-gateways' );

if ( !class_exists( 'WPC_Payments_Core' ) ) {

    class WPC_Payments_Core {

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            global $wpc_client;

            //load gateways just on settings page
            if ( is_admin() && isset( $_GET['page'] ) && 'wpclients_settings' == $_GET['page'] ) {
                add_action( 'plugins_loaded', array(&$this, 'load_gateway_plugins') );
            }


            //add admin submenu
            add_filter( 'wpc_client_admin_submenus', array( &$this, 'add_admin_submenu' ) );

            add_filter( 'wpc_client_settings_tabs_array', array( &$this, 'add_settings_tab' ) );
            add_action( 'wpc_client_settings_tabs_gateways', array( &$this, 'show_settings_page' ) );

        }


        /*
        * Function for adding admin submenu
        */
        function add_admin_submenu( $plugin_submenus ) {
            //add separater before addons submenu block

            $cap = "manage_options";

            $plugin_submenus['separator_2'] = array(
                'page_title'        => '',
                'menu_title'        => '- - - - - - - - - -',
                'slug'              => '#',
                'capability'        => $cap,
                'function'          => '',
                'hidden'            => false,
                'real'              => false,
                'order'             => 100,
            );

            $plugin_submenus['wpclients_payments'] = array(
                'page_title'        => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
                'menu_title'        => __( 'Payments', WPC_CLIENT_TEXT_DOMAIN ),
                'slug'              => 'wpclients_payments',
                'capability'        => $cap,
                'function'          => array( &$this, 'payments_history_page' ),
                'hidden'            => false,
                'real'              => true,
                'order'             => 130,
            );

            return $plugin_submenus;
        }


        /*
        * Add settings tab
        */
        function add_settings_tab( $tabs ) {
            $tabs['gateways'] = __( 'Payment Gateways', WPC_CLIENT_TEXT_DOMAIN );

            return $tabs;
        }



        /*
        * Show settings page
        */
        function show_settings_page() {
            global $wpc_client;
            include_once( $wpc_client->plugin_dir . 'includes/admin/settings_payment_gateways.php' );
        }


        /*
        *  returns a new unique order id
        */
        function generate_order_id() {
            global $wpdb;

            $count = true;
            while ( $count ) { //make sure it's unique
              $order_id = substr( sha1( uniqid( '' ) ), rand( 1, 24 ), 12 );
              $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpc_client_payments WHERE order_id = '" . $order_id . "'" );
            }

            return $order_id;
        }


        /*
        * save order    -  is deprecated remove after version 3.6.0
        */
        function create_order( $function, $amount, $currency, $client_id = null , $data = array() ) {
            global $wpdb;

            //remove old blank orders
            $old_orders = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_payments WHERE ( order_status IS NULL OR order_status = 'selected_gateway' ) AND time_created < '" . ( time() - 3600*24*5 ) . "'" );
            if ( $old_orders ) {
                $wpdb->query( "DELETE  FROM {$wpdb->prefix}wpc_client_payments WHERE id IN( ". rtrim( implode( ',', $old_orders ), ',') . ") " );
            }

            //create new order
            $client_id = $client_id ? $client_id : get_current_user_id();

            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_payments SET
                order_id = %s,
                function = %s,
                client_id = '%d',
                amount = '%s',
                currency = '%s',
                data = '%s',
                payment_type = 'one_time',
                time_created = '%s'
                ",
                $this->generate_order_id(),
                $function,
                $client_id,
                $amount,
                $currency,
                json_encode( $data ),
                time()
            ) );

            return $wpdb->insert_id;
        }


        /*
        * create new order
        */
        function create_new_order( $args = array() ) {
            global $wpdb;

            $default = array(
                'function' => '',
                'client_id' => null,
                'amount' => 0,
                'currency' => 'USD',
                'payment_type' => 'one_time',
                'payment_method' => null,
                'data' => array(),
            );

            $args = array_merge( $default, $args );

            //remove old blank orders
            $old_orders = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}wpc_client_payments WHERE ( order_status IS NULL OR order_status = 'selected_gateway' ) AND time_created < '" . ( time() - 3600*24*5 ) . "'" );
            if ( $old_orders ) {
                $wpdb->query( "DELETE  FROM {$wpdb->prefix}wpc_client_payments WHERE id IN( ". rtrim( implode( ',', $old_orders ), ',') . ") " );
            }

            $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}wpc_client_payments SET
                order_id = %s,
                function = %s,
                client_id = '%d',
                amount = '%s',
                currency = '%s',
                data = '%s',
                payment_type = '%s',
                payment_method = '%s',
                time_created = '%s'
                ",
                $this->generate_order_id(),
                $args['function'],
                $args['client_id'],
                $args['amount'],
                $args['currency'],
                json_encode( $args['data'] ),
                $args['payment_type'],
                $args['payment_method'],
                time()
            ) );

            return $wpdb->insert_id;
        }


        function update_order_gateway( $order_id, $selected_gateway ) {
            global $wpdb;

            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET payment_method = %s, order_status = 'selected_gateway' WHERE id = %d ", $selected_gateway, $order_id ) );
        }


        /*
        * get order
        */
        function get_order_by( $order_id, $by = 'id' ) {
            global $wpdb;

            if ( empty( $order_id ) )
              return false;

            if ( !in_array( $by, array( 'id', 'order_id' ) ) ) {
                $by = 'id';
            }


            $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpc_client_payments WHERE {$by} = %s ", $order_id ), "ARRAY_A" );

            if ( $order )
                return $order;
            else
                return false;
        }


        /*
        * get orders with the same order_id for partial payments
        */
        function get_orders( $order_ids ) {
            global $wpdb;

            if ( !is_array( $order_ids ) || !count( $order_ids ) )
              return false;

            $orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpc_client_payments WHERE id IN ( " . rtrim( implode( ',', $order_ids ), ',' ) . " ) ", "ARRAY_A" );

            if ( $orders )
                return $orders;
            else
                return false;
        }


        /*
        *
        */
        function order_update( $order_id, $payment_data ) {
            global $wpdb;

            //get the order
            $order = $this->get_order_by( $order_id );
            if ( $order ) {

                $valid_transaction_types = array(
                    'paid',
                    'pending',
                    'failed',
                    'refunded',
                    'subscription_canceled',
                    'subscription_start',
                    'subscription_payment',
                    'subscription_suspended'
                );

                if ( !isset( $payment_data["transaction_type"] ) || !in_array( $payment_data["transaction_type"], $valid_transaction_types ) ) {
                    return false;
                }



                switch( $payment_data["transaction_type"] ) {
                    //payment paid
                    case 'paid': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'paid', transaction_id = '%s', transaction_status = '%s', time_paid = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], time(), $order['id'] ), ARRAY_A );
                    }
                    break;

                    //payment pending
                    case 'pending': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'pending', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ), ARRAY_A );
                    }
                    break;

                    //payment failed
                    case 'failed': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'failed', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ), ARRAY_A );
                    }
                    break;

                    //refund and cancel subscription payments
                    case 'refunded': {
                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET order_status = 'refunded', transaction_id = '%s', transaction_status = '%s' WHERE id = '%s'", $payment_data["transaction_id"], $payment_data["transaction_status"], $order['id'] ), ARRAY_A );
                        do_action( 'wpc_invoice_refund', $order['id'] );
                    }
                    break;

                    //start subscription payments
                    case 'subscription_start': {

                        $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                            subscription_id = '%s'
                            WHERE id = '%s'",
                            $payment_data['subscription_id'],
                            $order_id
                        ) );

                    }
                    break;


                    //cancel subscription in payments
                    case 'subscription_canceled': {

                        //furute - for subscription
                        $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT id, subscription_status FROM {$wpdb->prefix}wpc_client_payments WHERE subscription_id = '%s'", $payment_data['subscription_id'] ), ARRAY_A );

                        if ( $subscriptions ) {
                            foreach( $subscriptions as $subscription ) {
                                if ( isset( $subscription['subscription_status'] ) && 'canceled' != $subscription['subscription_status'] ) {
                                    $wpdb->update( $wpdb->prefix . 'wpc_client_payments',
                                        array(
                                            'subscription_status' => 'canceled'
                                        ),
                                        array(
                                            'id' => $subscription['id']
                                        )
                                    );
                                }
                            }

                            $data = json_decode( $order['data'], true );
                            $profile_id = isset( $data['profile_id'] ) ? $data['profile_id'] : '';
                            do_action( 'wpc_change_status_expired', $profile_id );
                        }

                    }
                    break;

                    //suspend subscription in payments
                    case 'subscription_suspended': {

                        //furute - for subscription
                        $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT id, subscription_status FROM {$wpdb->prefix}wpc_client_payments WHERE subscription_id = '%s'", $payment_data['subscription_id'] ), ARRAY_A );

                        if ( $subscriptions ) {
                            foreach( $subscriptions as $subscription ) {
                                if ( isset( $subscription['subscription_status'] ) && 'canceled' != $subscription['subscription_status'] ) {
                                    $wpdb->update( $wpdb->prefix . 'wpc_client_payments',
                                        array(
                                            'subscription_status' => 'suspended'
                                        ),
                                        array(
                                            'id' => $subscription['id']
                                        )
                                    );
                                }
                            }
                        }

                    }
                    break;


                    case 'subscription_payment': {
                        $next_payment_date = ( isset( $payment_data['next_payment_date'] ) ) ? strtotime( $payment_data['next_payment_date'] ) : '';
                        $next_payment_date = ( '' != $next_payment_date ) ? date( 'Y-m-d H:i:s', $next_payment_date ) : '';

                        if ( isset( $order['order_status'] ) && 'paid' != $order['order_status'] ) {
                            if ( 'Completed' == $payment_data['transaction_status'] ) {
                                $status = 'paid';
                            } else {
                                $status = 'pending';
                            }


                            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                                order_status = '%s',
                                transaction_id = '%s',
                                transaction_status = '%s',
                                subscription_id = '%s',
                                subscription_status = '%s',
                                next_payment_date = '%s',
                                time_paid = '%s'
                                WHERE id = '%s'",
                                $status,
                                $payment_data['transaction_id'],
                                $payment_data['transaction_status'],
                                $payment_data['subscription_id'],
                                $payment_data['subscription_status'],
                                $next_payment_date,
                                time(),
                                $order_id
                            ) );


                        } else {
                            $args = array(
                                'function' => $order['function'],
                                'client_id' => $order['client_id'],
                                'amount' => $order['amount'],
                                'currency' => $order['currency'],
                                'payment_method' => $order['payment_method'],
                                'payment_type' => 'recurring',
                                'data' => isset( $order['data'] ) ? json_decode( $order['data'], true ) : array(),
                            );


                            $order_id = $this->create_new_order( $args );


                            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wpc_client_payments SET
                                order_status = 'paid',
                                transaction_id = '%s',
                                transaction_status = '%s',
                                subscription_id = '%s',
                                subscription_status = '%s',
                                next_payment_date = '%s',
                                time_paid = '%s'
                                WHERE id = '%s'",
                                $payment_data['transaction_id'],
                                $payment_data['transaction_status'],
                                $payment_data['subscription_id'],
                                $payment_data['subscription_status'],
                                $next_payment_date,
                                time(),
                                $order_id
                            ) );

                         }

                    }
                    break;
                }

                //get new order
                $order = $this->get_order_by( $order_id );

                do_action( 'wpc_client_payment_' . $payment_data["transaction_type"] . '_' . $order['function'], $order );

            }
        }


        function payment_step_content( $order, $step = 2 ) {
            global $wpc_gateway_active_plugins, $wpdb;

            //add action for 1st step if some extensions need
            if ( 1 == $step ) {
                $action = get_query_var( 'wpc_order_id' );

                if ( !empty( $action ) ) {
                    return do_action( 'wpc_client_payment_process_' . $action );
                }

            }


            if ( 2 > (int) $step ) {
                $step = 2;
            }

            $content = '';

            if ( 2 == $step ) {

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';

                    $content .= __( 'You need to finish your payment process', WPC_CLIENT_TEXT_DOMAIN ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'];

                    $content .= '<form id="wpc_payment_form" method="post" action="">';

                    if ( 0 < count( $wpc_gateway_active_plugins ) ) {
                        $content .= '<table class="wpc_cart_payment_methods">';
                        $content .= '<thead><tr>';
                        $content .= '<th>'.__('Choose a Payment Method:', WPC_CLIENT_TEXT_DOMAIN).'</th>';
                        $content .= '</tr></thead>';
                        $content .= '<tbody><tr><td><ul class="wpc_payment_gateways_list">';
                        foreach ((array)$wpc_gateway_active_plugins as $plugin) {
                            $content .= '<li><label>';
                            $content .= '<input type="radio" class="wpc_choose_gateway" name="wpc_choose_gateway" value="'.$plugin->plugin_name.'" /> ';
                            if ($plugin->method_img_url) {
                                $content .= '<img src="' . $plugin->method_img_url . '" alt="' . $plugin->public_name . '" />';
                                $content .= ' ' . $plugin->public_name;
                            } else {
                                $content .= $plugin->public_name;
                            }
                            $content .= '</label></li>';
                        }
                        $content .= '</ul></td>';
                        $content .= '</tr>';
                        $content .= '</tbody>';
                        $content .= '</table>';
                    } else {
                        $content .= '<br/>'.__('No payment method is configured:: Please contact to the site administrator.', WPC_CLIENT_TEXT_DOMAIN).'<br/><br/>';
                    }

                    $content .= '<p class="wpc_cart_direct_checkout"><input type="submit" name="wpc_payment_submit" id="wpc_payment_confirm" value="Continue"></p>';
                    $content .= '</form>';

            } else {
                foreach ((array)$wpc_gateway_active_plugins as $plugin) {
                    if( $plugin->plugin_name == $order['payment_method'] ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }

                $content = $active_gateway->payment_process( $order, $step );
            }

            return $content;
        }


        /*
        * start IPN
        */
        function handle_ipn( $order, $page_value ) {
            global $wpdb, $wpc_payments_core, $wpc_gateway_active_plugins, $wpc_client;

            //load gateways just for IPN
            $wpc_payments_core->load_gateway_plugins();

            $active_gateway = '';

            if ( $order && '' != $order['payment_method'] ) {
                foreach ( (array )$wpc_gateway_active_plugins as $plugin ) {
                    if( $plugin->plugin_name == $order['payment_method'] ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }

            } else {
                foreach ( (array )$wpc_gateway_active_plugins as $plugin ) {
                    if( $plugin->plugin_name == $page_value ) {
                        $active_gateway = $plugin;
                        break;
                    }
                }
            }

            if ( !empty( $active_gateway ) && method_exists( $active_gateway, '_ipn' )) {
                $active_gateway->_ipn( $order );
            }

            exit();

        }


        /*
        * display Paymants pages
        */
        function payments_history_page() {
            global $wpc_client;

            include $wpc_client->plugin_dir . 'includes/admin/payments_history.php';

        }


        /*
        * display Paymants pages
        */
        function get_continue_link( $order, $with_text = true ) {

            $link = '';

            if ( isset( $order['function'] ) && '' != $order['function'] ) {
                $link = apply_filters( 'wpc_payment_get_continue_link_' . $order['function'], $link, $order, $with_text );
            }

            return $link;
        }


        /*
        *
        */
        function load_gateway_plugins() {
            global $wpc_client, $wpc_gateway_plugins, $wpc_gateway_active_plugins;


            //get gateway plugins dir
            $dir = $wpc_client->plugin_dir . 'includes/payment_gateways/';

            //search the dir for files
            $gateway_plugins = array();
            if ( !is_dir( $dir ) )
                return;
            if ( ! $dh = opendir( $dir ) )
                return;
            while ( ( $plugin = readdir( $dh ) ) !== false ) {
                if ( substr( $plugin, -4 ) == '.php' )
                    $gateway_plugins[] = $dir . $plugin;
            }
            closedir( $dh );


            //get extra custom gateway plugins
            $dir = $wpc_client->get_upload_dir( 'wpclient/_payment_gateways/' );

            if ( $dh = opendir( $dir ) ) {
                while ( ( $plugin = readdir( $dh ) ) !== false ) {
                    if ( substr( $plugin, -4 ) == '.php' )
                        $gateway_plugins[] = $dir . $plugin;
                }
                closedir( $dh );
            }



            //get extra custom gateway plugins from url
            $dir = apply_filters( 'wpc_client_external_gateways_dir', '' );

            if ( !empty( $dir ) && is_dir( $dir ) ) {
                if ( $dh = opendir( $dir ) ) {
                    while ( ( $plugin = readdir( $dh ) ) !== false ) {
                        if ( substr( $plugin, -4 ) == '.php' )
                            $gateway_plugins[] = $dir . $plugin;
                    }
                    closedir( $dh );
                }
            }

            sort( $gateway_plugins );

            //include them suppressing errors
            foreach ( $gateway_plugins as $file )
                include_once( $file );

            //load chosen plugin classes
            $wpc_gateways = $wpc_client->cc_get_settings( 'gateways' );


            foreach ( (array)$wpc_gateway_plugins as $code => $plugin ) {
                $class = $plugin[0];
                if ( isset( $wpc_gateways['allowed'] ) && in_array( $code, (array)$wpc_gateways['allowed'] ) && class_exists( $class ) )
                    $wpc_gateway_active_plugins[] = new $class;
            }

        }



    }



    $GLOBALS['wpc_gateway_plugins'] = array();
    $GLOBALS['wpc_gateway_active_plugins'] = array();
    $GLOBALS['wpc_payments_core'] = new WPC_Payments_Core();
    /**
     * Use this function to register your gateway plugin class
     *
     * @param string $class_name - the case sensitive name of your plugin class
     * @param string $plugin_name - the sanitized private name for your plugin
     * @param string $admin_name - pretty name of your gateway, for the admin side.
     */
    function wpc_register_gateway_plugin($class_name, $plugin_name, $admin_name) {
      global $wpc_gateway_plugins;

      if ( !is_array( $wpc_gateway_plugins ) ) {
            $wpc_gateway_plugins = array();
        }

        if ( class_exists( $class_name ) ) {
            $wpc_gateway_plugins[ $plugin_name ] = array( $class_name, $admin_name );
        } else {
            return false;
        }
    }


}

?>