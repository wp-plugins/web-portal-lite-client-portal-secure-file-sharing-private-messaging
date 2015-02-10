<?php

if( !class_exists( 'WPC_Gateway_AuthorizeNet_AIM' ) ) {
    class WPC_Gateway_AuthorizeNet_AIM {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'authorizenet-aim';

    //name of your gateway, for the admin side.
    var $admin_name = '';

    //public name of your gateway, for lists and such.
    var $public_name = '';

    //url for an image for your checkout method. Displayed on checkout form if set
    var $method_img_url = '';

    //url for an submit button image for your checkout method. Displayed on checkout form if set
    var $method_button_img_url = '';

    //whether or not ssl is needed for checkout page
    var $force_ssl = true;

    //has recurring
    var $recurring = true;

    //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
    var $ipn_url;

    //whether if this is the only enabled gateway it can skip the payment_form step
    var $skip_form = false;

    var $set_ipn = false;

    //credit card vars
    var $API_Username, $API_Password, $SandboxFlag, $API_Endpoint, $API_recurring, $version, $currencyCode, $locale;

    /****** Below are the public methods you may overwrite via a plugin ******/

    /**
    * Runs when your class is instantiated.
    */
    function __construct() {
        global $wpc_payments_core, $wpc_client;
        $wpc_gateways = $wpc_client->cc_get_settings( 'gateways' );

        if ( !isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) || 1 != $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) {
            $this->recurring = false;
        }

        //set names here to be able to translate
        $this->admin_name = __('Authorize.net Checkout', WPC_GATEWAYS_TD);
        $this->public_name = __('Authorize.net Checkout', WPC_GATEWAYS_TD);

        if ( isset( $wpc_gateways[ $this->plugin_name ]['public_name'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['public_name'] ) ) {
            $this->public_name = $wpc_gateways[ $this->plugin_name ]['public_name'];
        }

        $this->method_img_url = $wpc_client->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = $wpc_client->plugin_url . 'images/cc-button.png';

        if ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ) {
            $this->method_img_url = $wpc_gateways[ $this->plugin_name ]['icon_url'];
        }

        $this->version = "63.0"; //api version

        //set credit card vars
        if ( isset( $wpc_gateways[ $this->plugin_name ] ) ) {

          $this->API_Username     = ( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ) ? $wpc_gateways[ $this->plugin_name ]['api_user'] : '';
          $this->API_Password     = ( isset( $wpc_gateways[ $this->plugin_name ]['api_key'] ) ) ? $wpc_gateways[ $this->plugin_name ]['api_key'] : '';
          $this->currencyCode     = ( isset( $wpc_gateways[ $this->plugin_name ]['currency'] ) ) ? $wpc_gateways[ $this->plugin_name ]['currency'] : '';
          $this->md5_hash         = ( isset( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) ) ? $wpc_gateways[ $this->plugin_name ]['md5_hash'] : '';
          $this->set_ipn          = ( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) &&  1 == $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) ? true : false;

          //set api urls
          if ( isset( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) )    {
            $this->API_Endpoint = esc_url_raw( $wpc_gateways[ $this->plugin_name ]['custom_api'] );
          } else if ( !isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) || $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox' )    {
            $this->API_Endpoint = "https://test.authorize.net/gateway/transact.dll";
            $this->API_recurring = "https://apitest.authorize.net/xml/v1/request.api";
          } else {
            $this->API_Endpoint = "https://secure.authorize.net/gateway/transact.dll";
            $this->API_recurring = "https://api.authorize.net/xml/v1/request.api";
          }
        }
    }


    function payment_process( &$order, $step = 3 ) {
        global $wpc_payments_core, $wpc_client;

        if( 'recurring' == $order['payment_type'] ) {

            //recurring order
            switch( $step ) {
                case 3: {


                    //not confirmed IPN setting
                    if ( !$this->set_ipn ) {

                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-2/';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 2 ), get_home_url()  );
                        }

                        echo '<br /><br />';
                        echo __( 'Seems IPN settings is not confirmed. Please edit payment gateway settings.', WPC_GATEWAYS_TD ) . ' ';
                        echo '<a href="' . $redirect . '">' . __( 'Return', WPC_GATEWAYS_TD ) . '</a>';

                        break;
                    }



                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';

                    //make link
                    if ( $wpc_client->permalinks ) {
                        $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-4/';
                    } else {
                        $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 4 ), get_home_url()  );
                    }


                    $content = '<style type="text/css">
                        .cardimage {
                          height: 23px;
                          width: 157px;
                          display: inline-table;
                        }

                        .nocard {
                          background-position: 0px 0px !important;
                        }

                        .visa_card {
                          background-position: 0px -23px !important;
                        }

                        .mastercard {
                          background-position: 0px -46px !important;
                        }

                        .discover_card {
                          background-position: 0px -69px !important;
                        }

                        .amex {
                          background-position: 0px -92px !important;
                        }
                      </style>
                      <script type="text/javascript">
                        function cc_card_pick(card_image, card_num){
                          if (card_image == null) {
                                  card_image = "#cardimage";
                          }
                          if (card_num == null) {
                                  card_num = "#card_num";
                          }

                          numLength = jQuery(card_num).val().length;
                          number = jQuery(card_num).val();
                          if (numLength > 10)
                          {
                                  if((number.charAt(0) == "4") && ((numLength == 13)||(numLength==16))) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage visa_card"); }
                                  else if((number.charAt(0) == "5" && ((number.charAt(1) >= "1") && (number.charAt(1) <= "5"))) && (numLength==16)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage mastercard"); }
                                  else if(number.substring(0,4) == "6011" && (numLength==16))     { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage amex"); }
                                  else if((number.charAt(0) == "3" && ((number.charAt(1) == "4") || (number.charAt(1) == "7"))) && (numLength==15)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage discover_card"); }
                                  else { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage nocard"); }

                          }
                        }
                        jQuery(document).ready( function() {
                          jQuery(".noautocomplete").attr("autocomplete", "off");
                        });
                      </script>';

                    $content .= __( 'You need to finish your payment process', WPC_GATEWAYS_TD ) . ': ';
                    $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'];

                    //empty API
                    if ( empty( $this->API_Username ) || empty( $this->API_Password ) ) {
                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-2/';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 2 ), get_home_url()  );
                        }

                        echo '<br /><br />';
                        echo __( 'API are empty.', WPC_GATEWAYS_TD ) . ' ';
                        echo '<a href="' . $redirect . '">' . __( 'Return', WPC_GATEWAYS_TD ) . '</a>';

                        break;
                    }



                    $content .= '<form id="wpc_payment_form" method="post" action="' . $redirect . '">';


                    $content .= '<table class="wpc_cart_billing">
                        <tbody>
                          <tr>
                            <td align="right">'.__('Credit Card Number:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                              '.apply_filters( 'wpc_checkout_error_card_num', '' ).'
                              <input name="card_num" onkeyup="cc_card_pick(\'#cardimage\', \'#card_num\');"
                               id="card_num" class="credit_card_number input_field noautocomplete"
                               type="text" size="22" maxlength="22" />
                                <div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url('.$wpc_client->plugin_url.'images/card_array.png) no-repeat;"></div></td>
                          </tr>

                          <tr>
                            <td align="right">'.__('Expiration Date:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                            '.apply_filters( 'wpc_checkout_error_exp', '' ).'
                            <label class="inputLabel" for="exp_month">'.__('Month', WPC_GATEWAYS_TD).'</label>
                                <select name="exp_month" id="exp_month">
                                  '.$this->_print_month_dropdown().'
                                </select>
                                <label class="inputLabel" for="exp_year">'.__('Year', WPC_GATEWAYS_TD).'</label>
                                <select name="exp_year" id="exp_year">
                                  '.$this->_print_year_dropdown('', true).'
                                </select>
                                </td>
                          </tr>

                          <tr>
                            <td align="right">'.__('Security Code:', WPC_GATEWAYS_TD).'*</td>
                            <td>'.apply_filters( 'wpc_checkout_error_card_code', '' ).'
                            <input id="card_code" name="card_code" class="input_field noautocomplete"
                               style="width: 70px;" type="text" size="4" maxlength="4" /></td>
                          </tr>
                          <tr>
                            <td align="right">'.__('First Name:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                              '.apply_filters( 'wpc_checkout_error_fname', '' ).'
                              <input name="fname" id="wpc_cart__fname" class="credit_fname input_field noautocomplete"
                               type="text" size="22" maxlength="50" />
                            </td>
                          </tr>

                          <tr>
                            <td align="right">'.__('Last Name:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                              '.apply_filters( 'wpc_checkout_error_lname', '' ).'
                              <input name="lname" id="wpc_cart__lname" class="credit_lname input_field noautocomplete"
                               type="text" size="22" maxlength="50" />
                            </td>
                          </tr>

                        </tbody>
                      </table>';

                    $content .= '<p class="wpc_cart_direct_checkout"><input type="submit" name="wpc_payment_submit" id="wpc_payment_confirm" value="' . __( 'Confirm Payment', WPC_GATEWAYS_TD ) . '"></p>';
                    $content .= '</form>';

                    echo $content;

                    break;
                }

                case 4: {


                    //helper function for parsing response
                    function wpc_auth_substring_between($haystack,$start,$end)
                    {
                        if (strpos($haystack,$start) === false || strpos($haystack,$end) === false)
                        {
                            return false;
                        }
                        else
                        {
                            $start_position = strpos($haystack,$start)+strlen($start);
                            $end_position = strpos($haystack,$end);
                            return substr($haystack,$start_position,$end_position-$start_position);
                        }
                    }



                    $timestamp = time();
                    $wpc_gateways = $wpc_client->cc_get_settings( 'gateways' );

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';


                    $delim_data = ( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) && 'yes' == $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? true : false;



                    //check card before subscribe
                    $auth_only = new WPC_Gateway_Worker_AuthorizeNet_AIM($this->API_Endpoint,
                        $delim_data,
                        $wpc_gateways[ $this->plugin_name ]['delim_char'],
                        $wpc_gateways[ $this->plugin_name ]['encap_char'],
                        $wpc_gateways[ $this->plugin_name ]['api_user'],
                        $wpc_gateways[ $this->plugin_name ]['api_key'],
                        ( $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox')
                    );

                    $auth_only->setTransactionType("AUTH_ONLY");
                    $auth_only->setParameter('x_invoice_num', $order['order_id']);
                    $auth_only->setParameter('x_first_name', $_POST['fname']);
                    $auth_only->setParameter('x_last_name', $_POST['lname']);
                    $auth_only->setParameter('x_card_num', $_POST['card_num']);
                    $auth_only->setParameter('x_card_code', $_POST['card_code']);
                    $auth_only->setParameter('x_exp_date', $_POST['exp_month'] . '/' . $_POST['exp_year']);
                    $auth_only->setParameter("x_amount", 1);
                    $auth_only->process();

                    if ( $auth_only->isApproved() ) {

                        //void first transaction
                        $transaction_id = $auth_only->getTransactionID();

                        $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';

                        if ( 'week' == $data['t3'] ) {
                            $data['t3'] = 'day' ;
                            $data['p3'] = 7 * $data['p3'];
                        }

                        $totalOccurrences = ( !isset( $data['c'] ) || '' == $data['c'] ) ? '9999' : $data['c'] ;

                        //build xml to post
                        $content =
                            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                            "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
                                "<merchantAuthentication>".
                                    "<name>" . $this->API_Username . "</name>".
                                    "<transactionKey>" . $this->API_Password . "</transactionKey>".
                                "</merchantAuthentication>".
                                "<subscription>".
                                    "<name>" . substr( $item_name, 0, 49 ) . "</name>".
                                    "<paymentSchedule>".
                                        "<interval>".
                                            "<length>". $data['p3'] ."</length>".
                                            "<unit>". $data['t3'] . 's' ."</unit>".
                                        "</interval>".
                                        "<startDate>" . date( 'Y-m-d') . "</startDate>".
                                        "<totalOccurrences>". $totalOccurrences . "</totalOccurrences>".
//                                                "<trialOccurrences>". $trialOccurrences . "</trialOccurrences>".
                                    "</paymentSchedule>".
                                    "<amount>". $order['amount'] ."</amount>".
//                                            "<trialAmount>" . $trialAmount . "</trialAmount>".
                                    "<payment>" .
                                        "<creditCard>" .
                                            "<cardNumber>" . $_POST['card_num'] . "</cardNumber>" .
                                            "<expirationDate>" . $_POST['exp_month'] . '/' . $_POST['exp_year'] . "</expirationDate>" .
                                            "<cardCode>" . $_POST['card_code'] . "</cardCode>" .
                                        "</creditCard>" .
                                    "</payment>" .
                                    "<order>" .
                                        "<invoiceNumber>" . $order['order_id'] .  "</invoiceNumber>" .
                                        "<description>" . substr( $item_name, 0, 254 ) .  "</description>" .
                                    "</order>" .
                                    "<billTo>" .
                                     "<firstName>" . $_POST['fname'] . "</firstName>" .
                                     "<lastName>" . $_POST['lname'] . "</lastName>" .
                                    "</billTo>" .
                                "</subscription>" .
                            "</ARBCreateSubscriptionRequest>";


                        $args = array(
                            'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
                            'headers'       => array(
                                'Content-Type'      => 'text/xml',
                                'Content-Length'    => strlen($content),
                                'Connection'        => 'Connection',
                            ),
                            'body'          => $content,
                            'sslverify'     => '',
                            'timeout'   => 30,
                        );

                        //use built in WP http class to work with most server setups
                        $response = wp_remote_post( $this->API_recurring, $args );

                        if ( is_array( $response ) && isset( $response['body'] ) ) {

                            $result = array();
                            $result['refId'] = wpc_auth_substring_between($response['body'],'<refId>','</refId>');
                            $result['resultCode'] = wpc_auth_substring_between($response['body'],'<resultCode>','</resultCode>');
                            $result['code'] = wpc_auth_substring_between($response['body'],'<code>','</code>');
                            $result['text'] = wpc_auth_substring_between($response['body'],'<text>','</text>');
                            $result['subscriptionId'] = wpc_auth_substring_between($response['body'],'<subscriptionId>','</subscriptionId>');


                            if ( false === $result['subscriptionId'] ) {
                                //make link
                                if ( $wpc_client->permalinks ) {
                                    $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-3/?error=1';
                                } else {
                                    $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 3, 'error' => 1 ), get_home_url()  );
                                }

                                echo (sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_GATEWAYS_TD), $result['text'] ) );
                                echo '<br>';
                                echo (sprintf(__('<a href="%s">Please try again</a>', WPC_GATEWAYS_TD), $redirect ) );
                                return false;
                            } else {

                                $payment_data = array();
                                $payment_data['transaction_status'] = "Completed";
                                $payment_data['subscription_id'] = $result['subscriptionId'];
                                $payment_data['subscription_status'] = 'active';
                                $payment_data['parent_txn_id'] = null;
                                $payment_data['transaction_type'] = 'subscription_payment';
                                $payment_data['transaction_id'] = null;


                                $wpc_payments_core->order_update( $order['id'], $payment_data );

                                //make link
                                if ( $wpc_client->permalinks ) {
                                    $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-5/';
                                } else {
                                    $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 5 ), get_home_url()  );
                                }

                                $wpc_client->cc_js_redirect( $redirect );


                            }

                        } else {
                            //make link
                            if ( $wpc_client->permalinks ) {
                                $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-3/?error=1';
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 3, 'error' => 1 ), get_home_url()  );
                            }

                            _e('There was a problem finalizing your purchase: Transaction Failed ', WPC_GATEWAYS_TD ) ;
                            echo '<br>';
                            echo (sprintf(__('<a href="%s">Please try again</a>', WPC_GATEWAYS_TD), $redirect ) );
                            return false;

                        }

                    } else {
                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-3/?error=1';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 3, 'error' => 1 ), get_home_url()  );
                        }

                        echo (sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_GATEWAYS_TD), $auth_only->getResponseText() ) );
                        echo '<br>';
                        echo (sprintf(__('<a href="%s">Please try again</a>', WPC_GATEWAYS_TD), $redirect ) );
                        return false;
                    }
                    break;
                }

                case 5: {
                    global $wpc_client, $wpc_payments_core;
                    echo __( 'Thank you for the payment.', WPC_GATEWAYS_TD );
                    echo ' ';
                    echo $wpc_payments_core->get_continue_link( $order, true );
                    break;
                }
            }

        } else {

            //one time order
            switch( $step ) {
                case 3: {

                        $data       = json_decode( $order['data'], true );
                        $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';

                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-4/';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 4 ), get_home_url()  );
                        }


                       $content = '<style type="text/css">
                        .cardimage {
                          height: 23px;
                          width: 157px;
                          display: inline-table;
                        }

                        .nocard {
                          background-position: 0px 0px !important;
                        }

                        .visa_card {
                          background-position: 0px -23px !important;
                        }

                        .mastercard {
                          background-position: 0px -46px !important;
                        }

                        .discover_card {
                          background-position: 0px -69px !important;
                        }

                        .amex {
                          background-position: 0px -92px !important;
                        }
                      </style>
                      <script type="text/javascript">
                        function cc_card_pick(card_image, card_num){
                          if (card_image == null) {
                                  card_image = "#cardimage";
                          }
                          if (card_num == null) {
                                  card_num = "#card_num";
                          }

                          numLength = jQuery(card_num).val().length;
                          number = jQuery(card_num).val();
                          if (numLength > 10)
                          {
                                  if((number.charAt(0) == "4") && ((numLength == 13)||(numLength==16))) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage visa_card"); }
                                  else if((number.charAt(0) == "5" && ((number.charAt(1) >= "1") && (number.charAt(1) <= "5"))) && (numLength==16)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage mastercard"); }
                                  else if(number.substring(0,4) == "6011" && (numLength==16))     { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage amex"); }
                                  else if((number.charAt(0) == "3" && ((number.charAt(1) == "4") || (number.charAt(1) == "7"))) && (numLength==15)) { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage discover_card"); }
                                  else { jQuery(card_image).removeClass(); jQuery(card_image).addClass("cardimage nocard"); }

                          }
                        }
                        jQuery(document).ready( function() {
                          jQuery(".noautocomplete").attr("autocomplete", "off");
                        });
                      </script>';

                      $content .= __( 'You need to finish your payment process', WPC_GATEWAYS_TD ) . ': ';
                      $content .= $item_name . ' - ' . $order['amount'] . ' ' . $order['currency'];

                        //empty API
                        if ( empty( $this->API_Username ) || empty( $this->API_Password ) ) {
                            //make link
                            if ( $wpc_client->permalinks ) {
                                $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-2/';
                            } else {
                                $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 2 ), get_home_url()  );
                            }

                            echo '<br /><br />';
                            echo __( 'API are empty.', WPC_GATEWAYS_TD ) . ' ';
                            echo '<a href="' . $redirect . '">' . __( 'Return', WPC_GATEWAYS_TD ) . '</a>';

                            break;
                        }



                      $content .= '<form id="wpc_payment_form" method="post" action="' . $redirect . '">';


                      $content .= '<table class="wpc_cart_billing">
                        <tbody>
                          <tr>
                            <td align="right">'.__('Credit Card Number:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                              '.apply_filters( 'wpc_checkout_error_card_num', '' ).'
                              <input name="card_num" onkeyup="cc_card_pick(\'#cardimage\', \'#card_num\');"
                               id="card_num" class="credit_card_number input_field noautocomplete"
                               type="text" size="22" maxlength="22" />
                                <div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url('.$wpc_client->plugin_url.'images/card_array.png) no-repeat;"></div></td>
                          </tr>

                          <tr>
                            <td align="right">'.__('Expiration Date:', WPC_GATEWAYS_TD).'*</td>
                            <td>
                            '.apply_filters( 'wpc_checkout_error_exp', '' ).'
                            <label class="inputLabel" for="exp_month">'.__('Month', WPC_GATEWAYS_TD).'</label>
                                <select name="exp_month" id="exp_month">
                                  '.$this->_print_month_dropdown().'
                                </select>
                                <label class="inputLabel" for="exp_year">'.__('Year', WPC_GATEWAYS_TD).'</label>
                                <select name="exp_year" id="exp_year">
                                  '.$this->_print_year_dropdown('', true).'
                                </select>
                                </td>
                          </tr>

                          <tr>
                            <td align="right">'.__('Security Code:', WPC_GATEWAYS_TD).'</td>
                            <td>'.apply_filters( 'wpc_checkout_error_card_code', '' ).'
                            <input id="card_code" name="card_code" class="input_field noautocomplete"
                               style="width: 70px;" type="text" size="4" maxlength="4" /></td>
                          </tr>

                        </tbody>
                      </table>';

                    $content .= '<p class="wpc_cart_direct_checkout"><input type="submit" name="wpc_payment_submit" id="wpc_payment_confirm" value="' . __( 'Confirm Payment', WPC_GATEWAYS_TD ) . '"></p>';
                    $content .= '</form>';

                    echo $content;

                    break;
                }

                case 4: {
                    $timestamp = time();
                    $wpc_gateways = $wpc_client->cc_get_settings( 'gateways' );

                    $data       = json_decode( $order['data'], true );
                    $item_name  = isset( $data['item_name'] ) ? $data['item_name'] : 'Order';


                    $delim_data = ( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) && 'yes' == $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? true : false;

                    $payment = new WPC_Gateway_Worker_AuthorizeNet_AIM($this->API_Endpoint,
                      $delim_data,
                      $wpc_gateways[ $this->plugin_name ]['delim_char'],
                      $wpc_gateways[ $this->plugin_name ]['encap_char'],
                      $wpc_gateways[ $this->plugin_name ]['api_user'],
                      $wpc_gateways[ $this->plugin_name ]['api_key'],
                      ( $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox') );

                    $payment->transaction($_POST['card_num']);

                    $payment->addLineItem( $order['client_id'], urlencode( substr( $item_name, 0, 31 ) ), '', 1, $order['amount'] );
                    // Billing Info
                    $payment->setParameter("x_card_code", $_POST['card_code']);
                    $payment->setParameter("x_exp_date ", $_POST['exp_month'] . '/' . $_POST['exp_year']);
                    $payment->setParameter("x_amount", $order['amount']);
                    $payment->setParameter("x_currency_code", $order['currency']);

                    // Order Info
                    $payment->setParameter("x_description", urlencode( substr( $item_name, 0, 31 ) ) );
                    $payment->setParameter("x_invoice_num",  $order['order_id'] );
                    if ( $wpc_gateways[ $this->plugin_name ]['mode'] == 'sandbox' )    {
                      $payment->setParameter("x_test_request", true);
                    } else {
                      $payment->setParameter("x_test_request", false);
                    }
                    $payment->setParameter("x_duplicate_window", 30);

                    // E-mail
                    $payment->setParameter("x_header_email_receipt", $wpc_gateways[ $this->plugin_name ]['header_email_receipt']);
                    $payment->setParameter("x_footer_email_receipt", $wpc_gateways[ $this->plugin_name ]['footer_email_receipt']);
                    $payment->setParameter( "x_email_customer", strtoupper( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) );

                    $payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);

                    $payment->process();

                    if ( $payment->isApproved() ) {

                        $payment_data = array();
                        $payment_data['transaction_status'] = "paid";
                        $payment_data['subscription_id'] = null;
                        $payment_data['subscription_status'] = null;
                        $payment_data['parent_txn_id'] = null;
                        $payment_data['transaction_type'] = 'paid';
                        $payment_data['transaction_id'] = $payment->getTransactionID();


                        $wpc_payments_core->order_update( $order['id'], $payment_data );

                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-5/';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 5 ), get_home_url()  );
                        }

                        $wpc_client->cc_js_redirect( $redirect );




                    } else {
                        //make link
                        if ( $wpc_client->permalinks ) {
                            $redirect = $wpc_client->cc_get_slug( 'payment_process_page_id' ) . $order['order_id'] . '/step-3/?error=1';
                        } else {
                            $redirect = add_query_arg( array( 'wpc_page' => 'payment_process', 'wpc_order_id' => $order['order_id'], 'wpc_page_value' => 3, 'error' => 1 ), get_home_url()  );
                        }

                        echo (sprintf(__('There was a problem finalizing your purchase: "%s" ', WPC_GATEWAYS_TD), $payment->getResponseText() ) );
                        echo '<br>';
                        echo (sprintf(__('<a href="%s">Please try again</a>', WPC_GATEWAYS_TD), $redirect ) );
                        return false;
                    }
                    break;
                }

                case 5: {
                    global $wpc_client, $wpc_payments_core;
                    echo __( 'Thank you for the payment.', WPC_GATEWAYS_TD );
                    echo ' ';
                    echo $wpc_payments_core->get_continue_link( $order, true );
                    break;
                }
            }

        }

    }




    /*
    *
    */
    function _ipn( $order ) {

        if ( !empty( $_POST['x_subscription_id'] ) ) {

            global $wpdb, $wpc_payments_core;

            $order = $wpc_payments_core->get_order_by( $_POST['x_invoice_num'], 'order_id' );
            if ( !$order ) {
                die( 'Order Incorrect' );
            }

            $our_hash_api = md5( $this->md5_hash . $_POST['x_trans_id'] . $_POST['x_amount'] );
            $our_hash_sp  = md5( $this->md5_hash . $this->API_Username . $_POST['x_trans_id'] . $_POST['x_amount'] );



            $error = 1;

            if ( strcmp( strtoupper( $our_hash_api ), $_POST['x_MD5_Hash'] ) === 0 ) {
                // Match
                $error = 0;
            } else if ( strcmp( strtoupper( $our_hash_sp ), $_POST['x_MD5_Hash'] ) === 0 ) {
                // Match
                $error = 0;
            }

            if ( !$error ) {
                die('IPN verification failed!');
            }


            if ( 1 == $_POST['x_response_code'] ) {

                $payment_data = array();
                $payment_data['transaction_status'] = 'Completed';
                $payment_data['transaction_id'] = $_POST['x_trans_id'];
                $payment_data['subscription_id'] = $_POST['x_subscription_id'];
                $payment_data['subscription_status'] = 'active';
                $payment_data['parent_txn_id'] = null;
                $payment_data['transaction_type'] = 'subscription_payment';

                $wpc_payments_core->order_update( $order['id'], $payment_data );

            }
        }
    }






    function _print_year_dropdown($sel='', $pfp = false) {
        $localDate=getdate();
        $minYear = $localDate["year"];
        $maxYear = $minYear + 15;

        $output = "<option value=''>--</option>";
        for($i=$minYear; $i<$maxYear; $i++) {
                if ($pfp) {
                        $output .= "<option value='". substr($i, 0, 4) ."'".($sel==(substr($i, 0, 4))?' selected':'').
                        ">". $i ."</option>";
                } else {
                        $output .= "<option value='". substr($i, 2, 2) ."'".($sel==(substr($i, 2, 2))?' selected':'').
                ">". $i ."</option>";
                }
        }
        return($output);
      }

      function _print_month_dropdown($sel='') {
        $output =  "<option value=''>--</option>";
        $output .=  "<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
        $output .=  "<option " . ($sel==2?' selected':'') . "  value='02'>02 - Feb</option>";
        $output .=  "<option " . ($sel==3?' selected':'') . "  value='03'>03 - Mar</option>";
        $output .=  "<option " . ($sel==4?' selected':'') . "  value='04'>04 - Apr</option>";
        $output .=  "<option " . ($sel==5?' selected':'') . "  value='05'>05 - May</option>";
        $output .=  "<option " . ($sel==6?' selected':'') . "  value='06'>06 - Jun</option>";
        $output .=  "<option " . ($sel==7?' selected':'') . "  value='07'>07 - Jul</option>";
        $output .=  "<option " . ($sel==8?' selected':'') . "  value='08'>08 - Aug</option>";
        $output .=  "<option " . ($sel==9?' selected':'') . "  value='09'>09 - Sep</option>";
        $output .=  "<option " . ($sel==10?' selected':'') . "  value='10'>10 - Oct</option>";
        $output .=  "<option " . ($sel==11?' selected':'') . "  value='11'>11 - Nov</option>";
        $output .=  "<option " . ($sel==12?' selected':'') . "  value='12'>12 - Dec</option>";

        return($output);
      }


      /**
       * Echo a settings meta box with whatever settings you need for you gateway.
       *  Form field names should be prefixed with wpc_gateway[plugin_name], like "wpc_gateway[plugin_name][mysetting]".
       *  You can access saved settings via $wpc_gateways array.
       */
      function create_settings_form( $wpc_gateways ) {
        global $wpc_payments_core, $wpc_client;

        //make link
        if ( $wpc_client->permalinks ) {
            $ipn_url = get_home_url() . '/wpc-ipn-handler-url/' . $this->plugin_name . '/';
        } else {
            $ipn_url = add_query_arg( array( 'wpc_page' => 'payment_ipn', 'wpc_page_value' => $this->plugin_name ), get_home_url()  );
        }

        ?>
        <div id="wpc_<?php echo $this->plugin_name ?>" class="postbox">
          <h3 class='hndle'><span><?php _e('Authorize.net AIM Settings', WPC_GATEWAYS_TD); ?></span></h3>
          <div class="inside">
            <span class="description"><?php _e('Authorize.net AIM is a customizable payment processing solution that gives the merchant control over all the steps in processing a transaction. An SSL certificate is required to use this gateway. USD is the only currency supported by this gateway.', WPC_GATEWAYS_TD) ?></span>
            <span class="description"><?php _e('Interval Length for recurring billing must be a value from 7 through 365 for day based subscriptions.', WPC_GATEWAYS_TD) ?></span>
            <table class="form-table">
                      <tr>
                        <th scope="row"><?php _e('Mode', WPC_GATEWAYS_TD) ?></th>
                        <td>
                        <p>
                          <select name="wpc_gateway[<?php echo $this->plugin_name ?>][mode]">
                            <option value="sandbox" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) ? $wpc_gateways[ $this->plugin_name ]['mode'] : '', 'sandbox' ) ?>><?php _e('Sandbox', WPC_GATEWAYS_TD) ?></option>
                            <option value="live" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['mode'] ) ? $wpc_gateways[ $this->plugin_name ]['mode'] : '', 'live' ) ?>><?php _e('Live', WPC_GATEWAYS_TD) ?></option>
                          </select>
                        </p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row"><?php _e( 'Allow Recurring', WPC_GATEWAYS_TD ) ?></th>
                        <td>
                            <p>
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][allow_recurring]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['allow_recurring'] ) && $wpc_gateways[ $this->plugin_name ]['allow_recurring'], '1' ) ?> value="1" />
                                    <?php _e( 'Allow to use this gateway for recurring payments', WPC_GATEWAYS_TD ) ?>
                                </label>
                            </p>
                        </td>
                      </tr>
                      <tr>
                        <th scope="row"><?php _e('Gateway Credentials', WPC_GATEWAYS_TD) ?></th>
                        <td>
                              <span class="description"><?php print sprintf(__('You must login to Authorize.net merchant dashboard to obtain the API login ID and API transaction key. <a target="_blank" href="%s">Instructions &raquo;</a>', WPC_GATEWAYS_TD), "http://www.authorize.net/support/merchant/Integration_Settings/Access_Settings.htm"); ?></span>
                              <p>
                                <label><?php _e('Login ID', WPC_GATEWAYS_TD) ?><br />
                                  <input value="<?php echo esc_attr( isset( $wpc_gateways[ $this->plugin_name ]['api_user'] ) ? $wpc_gateways[ $this->plugin_name ]['api_user'] : '' ); ?>" size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_user]" type="text" />
                                </label>
                              </p>
                              <p>
                                <label><?php _e('Transaction Key', WPC_GATEWAYS_TD) ?><br />
                                  <input value="<?php echo esc_attr( isset( $wpc_gateways[ $this->plugin_name ]['api_key'] ) ? $wpc_gateways[ $this->plugin_name ]['api_key'] : '' ); ?>" size="30" name="wpc_gateway[<?php echo $this->plugin_name ?>][api_key]" type="text" />
                                </label>
                              </p>
                                <p>
                                    <label><a title="<?php _e('The payment gateway generated MD5 hash value that can be used to authenticate the transaction response. You should set the same value like in your Authorize.net Settings', WPC_GATEWAYS_TD); ?>"><?php _e('Security: MD5 Hash', WPC_GATEWAYS_TD); ?></a><br/>
                                      <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['md5_hash'] ) : ''; ?>" size="32" name="wpc_gateway[<?php echo $this->plugin_name ?>][md5_hash]" type="text" />
                                    </label>
                                </p>
                        </td>
                      </tr>

                      <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "IPN URL (Silent Post)", WPC_GATEWAYS_TD ) ?>
                            </th>
                            <td width="75%">
                                <label>
                                    <input type="checkbox" name="wpc_gateway[<?php echo $this->plugin_name ?>][set_ipn]" <?php checked( isset( $wpc_gateways[ $this->plugin_name ]['set_ipn'] ) && $wpc_gateways[ $this->plugin_name ]['set_ipn'], '1' ) ?> value="1" />
                                    <?php _e( 'I certify that I have properly set my IPN alert URL', WPC_GATEWAYS_TD ) ?>
                                </label>
                                <br />
                                <br />
                                <b><?php echo $ipn_url ?></b>
                                <span style="float: left; font-size: 11px;" class="description"><?php _e( 'Use this URL in your Authorize.net "Silent Post URL" Settings.', WPC_GATEWAYS_TD ) ?></span>
                            </td>
                      </tr>
                      <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Public Name", WPC_GATEWAYS_TD ) ?>
                            </th>
                            <td width="75%">
                                 <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][public_name]" type="text" class="form_data" value="<?php echo $this->public_name ?>" />
                                <span style="float: left; font-size: 11px;" class="description"><?php printf( __( '%s will see this during "Choose Gateway" checkout step', WPC_GATEWAYS_TD ), $wpc_client->custom_titles['client']['p'] ) ?></span>
                            </td>
                        </tr>
                        <tr valign="top" style="height: 50px;">
                            <th scope="row" width="25%">
                                <?php _e( "Icon URL", WPC_GATEWAYS_TD ) ?>
                            </th>
                            <td width="75%">
                                 <input size="70" name="wpc_gateway[<?php echo $this->plugin_name ?>][icon_url]" type="text" class="form_data" value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['icon_url'] ) ? $wpc_gateways[ $this->plugin_name ]['icon_url'] : '' ) ?>" />
                                <span style="float: left; font-size: 11px;" class="description"><?php printf( __( '%s will see this during "Choose Gateway" checkout step', WPC_GATEWAYS_TD ), $wpc_client->custom_titles['client']['p'] ) ?></span>
                            </td>
                        </tr>
                      <tr>
                        <th scope="row"><?php _e('Advanced Settings', WPC_GATEWAYS_TD) ?></th>
                        <td>
                          <span class="description"><?php _e('Optional settings to control advanced options', WPC_GATEWAYS_TD) ?></span>
                              <p>
                                <label><a title="<?php _e('Authorize.net default is \',\'. Otherwise, get this from your credit card processor. If the transactions are not going through, this character is most likely wrong.', WPC_GATEWAYS_TD); ?>"><?php _e('Delimiter Character', WPC_GATEWAYS_TD); ?></a><br />
                                  <input value="<?php echo ( ( isset( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['delim_char'] ) : "," ); ?>" size="2" name="wpc_gateway[<?php echo $this->plugin_name ?>][delim_char]" type="text" />
                                </label>
                              </p>

                              <p>
                                <label><a title="<?php _e('Authorize.net default is blank. Otherwise, get this from your credit card processor. If the transactions are going through, but getting strange responses, this character is most likely wrong.', WPC_GATEWAYS_TD); ?>"><?php _e('Encapsulation Character', WPC_GATEWAYS_TD); ?></a><br />
                                  <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['encap_char'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['encap_char'] ) : ''; ?>" size="2" name="wpc_gateway[<?php echo $this->plugin_name ?>][encap_char]" type="text" />
                                </label>
                              </p>

                              <p>
                                <label><?php _e('Email Customer (on success):', WPC_GATEWAYS_TD); ?><br />
                                  <select name="wpc_gateway[<?php echo $this->plugin_name ?>][email_customer]">
                                    <option value="yes" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) ? $wpc_gateways[ $this->plugin_name ]['email_customer'] : '', 'yes' ) ?>><?php _e('Yes', WPC_GATEWAYS_TD) ?></option>
                                    <option value="no" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['email_customer'] ) ? $wpc_gateways[ $this->plugin_name ]['email_customer'] : '', 'no' ) ?>><?php _e('No', WPC_GATEWAYS_TD) ?></option>
                                  </select>
                                </label>
                              </p>

                              <p>
                                <label><a title="<?php _e('This text will appear as the header of the email receipt sent to the customer.', WPC_GATEWAYS_TD); ?>"><?php _e('Customer Receipt Email Header', WPC_GATEWAYS_TD); ?></a><br/>
                                  <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['header_email_receipt'] ) : __('Thanks for your payment!', WPC_GATEWAYS_TD); ?>" size="40" name="wpc_gateway[<?php echo $this->plugin_name ?>][header_email_receipt]" type="text" />
                                </label>
                          </p>

                              <p>
                                <label><a title="<?php _e('This text will appear as the footer on the email receipt sent to the customer.', WPC_GATEWAYS_TD); ?>"><?php _e('Customer Receipt Email Footer', WPC_GATEWAYS_TD); ?></a><br/>
                                  <input value="<?php echo ( isset( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) && !empty( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) ) ?  esc_attr( $wpc_gateways[ $this->plugin_name ]['footer_email_receipt'] ) : ''; ?>" size="40" name="wpc_gateway[<?php echo $this->plugin_name ?>][footer_email_receipt]" type="text" />
                                </label>
                          </p>
                            <p>
                                <label><a title="<?php _e('Request a delimited response from the payment gateway.', WPC_GATEWAYS_TD); ?>"><?php _e('Delim Data:', WPC_GATEWAYS_TD); ?></a><br/>
                                    <select name="wpc_gateway[<?php echo $this->plugin_name ?>][delim_data]">
                                        <option value="yes" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? $wpc_gateways[ $this->plugin_name ]['delim_data'] : '', 'yes' ) ?>><?php _e('Yes', WPC_GATEWAYS_TD) ?></option>
                                        <option value="no" <?php selected( isset( $wpc_gateways[ $this->plugin_name ]['delim_data'] ) ? $wpc_gateways[ $this->plugin_name ]['delim_data'] : '', 'no' ) ?>><?php _e('No', WPC_GATEWAYS_TD) ?></option>
                                    </select>
                                </label>
                            </p>
                            <p>
                                <label><a title="<?php _e('Many other gateways have Authorize.net API emulators. To use one of these gateways input their API post url here.', WPC_GATEWAYS_TD); ?>"><?php _e('Custom API URL', WPC_GATEWAYS_TD) ?></a><br />
                                    <input value="<?php echo isset( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) ? esc_attr( $wpc_gateways[ $this->plugin_name ]['custom_api'] ) : ''; ?>" size="50" name="wpc_gateway[<?php echo $this->plugin_name ?>][custom_api]" type="text" />
                                </label>
                            </p>

                        </td>
                      </tr>
                      <tr>
                        <th scope="row">

                        </th>
                        <td>
                            <p class="submit">
                                <input type="hidden" name="key" value="<?php echo $this->plugin_name; ?>" />
                                <input type="submit" name="submit_settings" class="button-primary" value="<?php _e('Update Settings', WPC_GATEWAYS_TD) ?>" />
                            </p>
                        </td>
                </tr>
            </table>
          </div>
        </div>
      <?php
      }


    }
}




if(!class_exists('WPC_Gateway_Worker_AuthorizeNet_AIM')) {
  class WPC_Gateway_Worker_AuthorizeNet_AIM
  {
    var $login;
    var $transkey;
    var $params   = array();
    var $results  = array();
    var $line_items = array();

    var $approved = false;
    var $declined = false;
    var $error    = true;
    var $method   = "";

    var $fields;
    var $response;

    var $instances = 0;

    function __construct($url, $delim_data, $delim_char, $encap_char, $gw_username, $gw_tran_key, $gw_test_mode)
    {
      if ($this->instances == 0)
      {
    $this->url = $url;

    $this->params['x_delim_data']     = $delim_data;
    $this->params['x_delim_char']     = $delim_char;
    $this->params['x_encap_char']     = $encap_char;
    $this->params['x_relay_response'] = "FALSE";
    $this->params['x_url']            = "FALSE";
    $this->params['x_version']        = "3.1";
    $this->params['x_method']         = "CC";
    $this->params['x_type']           = "AUTH_CAPTURE";
    $this->params['x_login']          = $gw_username;
    $this->params['x_tran_key']       = $gw_tran_key;
    $this->params['x_test_request']   = $gw_test_mode;

    $this->instances++;
      } else {
    return false;
      }
    }

    function transaction($cardnum)
    {
      $this->params['x_card_num']  = trim($cardnum);
    }

    function addLineItem($id, $name, $description, $quantity, $price, $taxable = 0)
    {
      $this->line_items[] = "{$id}<|>{$name}<|>{$description}<|>{$quantity}<|>{$price}<|>{$taxable}";
    }

    function process($retries = 1)
    {
      global $wpc_payments_core;

      $this->_prepareParameters();
      $query_string = rtrim($this->fields, "&");

      $count = 0;
      while ($count < $retries)
      {
        //$args['user-agent'] = "WPC-Client/" . WPC_CLIENT_LITE_VER . ": http://wpclient.com | Authorize.net AIM Plugin/" . WPC_CLIENT_LITE_VER;
        $args['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
        $args['body'] = $query_string;
        $args['sslverify'] = false;
                $args['timeout'] = 30;

        //use built in WP http class to work with most server setups
        $response = wp_remote_post($this->url, $args);

        if (is_array($response) && isset($response['body'])) {
          $this->response = $response['body'];
        } else {
          $this->response = "";
          $this->error = true;
          return;
        }

    $this->parseResults();

    if ($this->getResultResponseFull() == "Approved")
    {
          $this->approved = true;
      $this->declined = false;
      $this->error    = false;
          $this->method   = $this->getMethod();
      break;
    } else if ($this->getResultResponseFull() == "Declined")
    {
          $this->approved = false;
      $this->declined = true;
      $this->error    = false;
      break;
    }
    $count++;
      }
    }

    function parseResults()
    {
      $this->results = explode($this->params['x_delim_char'], $this->response);
    }

    function setParameter($param, $value)
    {
      $param                = trim($param);
      $value                = trim($value);
      $this->params[$param] = $value;
    }

    function setTransactionType($type)
    {
      $this->params['x_type'] = strtoupper(trim($type));
    }

    function _prepareParameters()
    {
      foreach($this->params as $key => $value)
      {
    $this->fields .= "$key=" . urlencode($value) . "&";
      }
      for($i=0; $i<count($this->line_items); $i++) {
        $this->fields .= "x_line_item={$this->line_items[$i]}&";
      }
    }

    function getMethod()
    {
      if (isset($this->results[51]))
      {
        return str_replace($this->params['x_encap_char'],'',$this->results[51]);
      }
      return "";
    }

    function getGatewayResponse()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[0]);
    }

    function getResultResponseFull()
    {
      $response = array("", "Approved", "Declined", "Error");
      return $response[str_replace($this->params['x_encap_char'],'',$this->results[0])];
    }

    function isApproved()
    {
      return $this->approved;
    }

    function isDeclined()
    {
      return $this->declined;
    }

    function isError()
    {
      return $this->error;
    }

    function getResponseText()
    {
      return $this->results[3];
      $strip = array($this->params['x_delim_char'],$this->params['x_encap_char'],'|',',');
      return str_replace($strip,'',$this->results[3]);
    }

    function getAuthCode()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[4]);
    }

    function getAVSResponse()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[5]);
    }

    function getTransactionID()
    {
      return str_replace($this->params['x_encap_char'],'',$this->results[7]);
    }
  }
}

//register payment gateway plugin
wpc_register_gateway_plugin( 'WPC_Gateway_AuthorizeNet_AIM', 'authorizenet-aim', __('Authorize.net AIM Checkout', WPC_GATEWAYS_TD) );
?>