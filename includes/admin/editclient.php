<?php

//check auth
if ( !current_user_can( 'wpc_edit_clients' ) && !current_user_can( 'administrator' ) ) {
    do_action( 'wp_client_redirect', get_admin_url() . 'admin.php?page=wpclient_clients' );
}

extract( $_REQUEST );

$error = '';

if ( isset( $btnAdd ) ) {
    // validate at php side
    if ( empty( $contact_name ) ) // empty username
        $error .= __('A Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( empty( $contact_email ) ) // empty email
        $error .= __('A email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( email_exists( $contact_email ) ) {
        if ( $ID != get_user_by( 'email', $contact_email )->ID ) {
            // email already exist
            $error .= __( 'Email address already uses.<br/>', WPC_CLIENT_TEXT_DOMAIN );
        }
    }

    if ( !empty( $contact_password ) ) {
        if ( empty( $contact_password2 ) ) // confirm password
            $error .= __("Sorry, confirm password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
        elseif ( $contact_password != $contact_password2 )
            $error .= __("Sorry, Passwords are not matched! .<br/>", WPC_CLIENT_TEXT_DOMAIN);

    }


    /*our_hook_
        hook_name: wpc_client_validate_edit_client_fields
        hook_title: Validate Edit Client Form
        hook_description: Hook runs before saving client data on Edit Client form.
        hook_type: filter
        hook_in: wp-client
        hook_location editclient.php
        hook_param: string $error
        hook_since: 3.4.9
    */
    $error = apply_filters( 'wpc_client_validate_edit_client_fields', $error );

    if ( empty( $error ) ) {

        $userdata = array(
            'ID'                => esc_attr($ID),
            'user_pass'         => $contact_password,
            'user_login'        => esc_attr( get_userdata($ID)->get( 'user_login' ) ),
            'business_name'     => ( isset( $business_name ) ) ? esc_attr( trim( $business_name ) ) : esc_attr( trim( $contact_name ) ),
            'display_name'      => esc_attr( trim( $contact_name ) ),
            'user_email'        => esc_attr( $contact_email ),
//            'first_name'        => esc_attr( trim( $business_name ) ),
            'contact_phone'     => esc_attr( $contact_phone ),
            'send_password'     => ( isset( $send_password ) && '1' == $send_password ) ? '1' : '0',
        );

        if ( empty( $contact_password ) ) {
            unset( $userdata['user_pass'] );
            $userdata['send_password'] = 0;
        }

        do_action('wp_clients_update', $userdata );
        do_action('wp_client_redirect', get_admin_url(). 'admin.php?page=wpclient_clients&msg=u');
        exit;
    }
}

global $wpdb;

$client = get_userdata( $id );
$client_contact_phone = get_user_meta( $id, $wpdb->prefix . 'contact_phone', true );

//get managers
$args = array(
    'role'      => 'wpc_manager',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
    'fields'    => array( 'ID','user_login' ),

);

$managers               = get_users( $args );

$current_manager_ids = $this->cc_get_assign_data_by_assign( 'manager', 'client', $id );
$business_name          = get_user_meta( $id, 'wpc_cl_business_name', true );

?>

<style type="text/css">

    .wrap input[type=text] {
        width:400px;
    }

    .wrap input[type=password] {
        width:400px;
    }

</style>

<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="container23">
        <ul class="menu">
                <?php echo $this->gen_tabs_menu( 'clients' ) ?>
        </ul>

        <span class="wpc_clear"></span>
        <div class="content23 news">

            <div class="icon32" id="icon-edit"><br></div>
            <h2><?php printf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?>:</h2>

            <div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>

            <form action="" method="post">
                <input type="hidden" name="wpc_action" value="client_edit" />
                <input type="hidden" name="contact_username" value="<?php echo $client->user_login?>" />
                <input type="hidden" name="ID" value="<?php echo $id;?>" />
                <table class="form-table">
                    <tr>
                        <td>
                            <label for="business_name"><?php _e( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?>: </label> <br/>
                            <input type="text" id="business_name" name="business_name" value="<?php echo $business_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_name" name="contact_name" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_name'] ); else echo $client->display_name; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_email" name="contact_email" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_email'] ); else echo $client->user_email ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_phone" name="contact_phone" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_phone'] ); else echo $client_contact_phone; ?>" />
                        </td>
                    </tr>


                    <?php
                    /*our_hook_
                        hook_name: wpc_client_edit_client_form_html
                        hook_title: Client Edit Form
                        hook_description: Can be used for adding custom fields on Client Edit Form.
                        hook_type: action
                        hook_in: wp-client
                        hook_location editclient.php
                        hook_param: int $id
                        hook_since: 3.4.8
                    */

                    do_action( 'wpc_client_edit_client_form_html', $id );
                    ?>

                    <tr>
                        <td>
                            <hr />
                            <label for="contact_username"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?> <?php _e( "(can't be changed)", WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_username" disabled="disabled" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_username'] ); else echo $client->user_login?>" />
                        </td>
                    </tr>
                    <?php
                    do_action( 'wpc_client_edit_client_after_username', $id );

                    if( current_user_can( 'administrator' ) ) {
                        if ( is_array( $managers ) && 0 < count( $managers ) ) { ?>
                            <tr>
                                <td>
                                    <label><?php echo $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['manager']['p'] ?>:</label> <br/>
                                    <?php
                                        $link_array = array(
                                            'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['manager']['p'] ),
                                            'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['manager']['p']
                                        );
                                        $input_array = array(
                                            'name'  => 'wpc_managers',
                                            'id'    => 'wpc_managers',
                                            'value' => ( is_array( $current_manager_ids ) && 0 < count( $current_manager_ids ) ) ? implode( ',', $current_manager_ids ) : ''
                                        );
                                        $additional_array = array(
                                            'counter_value' => ( is_array( $current_manager_ids ) && 0 < count( $current_manager_ids ) ) ? count( $current_manager_ids ) : 0
                                        );
                                        $this->acc_assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                    ?>
                                </td>
                            </tr>
                    <?php }} ?>
                    <tr>
                        <td>
                            <?php
                                $client_groups = $this->cc_get_client_groups_id( $id );
                            ?>
                            <label><?php echo $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ?>:</label> <br/>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] ),
                                    'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p']
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'wpc_circles',
                                    'value' => implode( ',', $client_groups )
                                );
                                $additional_array = array(
                                    'counter_value' => count( $client_groups )
                                );
                                $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_password"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="password" id="contact_password" name="contact_password" autocomplete="off" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_password'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_password2"><?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="password" id="contact_password2" name="contact_password2" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_password2'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                            <br><br>
                            <p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', WPC_CLIENT_TEXT_DOMAIN ) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td id="send_password_block" style="display: none;">
                            <input type="checkbox" id="send_password" name="send_password" value="1" /><label for="send_password"> <?php _e( 'Send this updated password to the user by email', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                            <hr />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type='submit' name='btnAdd' id="btnAdd" class='button-primary' value='<?php printf( __( 'Update %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?>' />
                            &nbsp; &nbsp; &nbsp;
                            <input type='reset' name='btnreset' class='button-secondary' value='<?php _e( 'Reset Form', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
                        </td>
                    </tr>
                </table>

            </form>

        </div>
    </div>
</div>
<?php
    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
    $this->acc_get_assign_circles_popup( $current_page );
    $this->acc_get_assign_managers_popup( $current_page );
?>

<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery( document ).ready( function( $ ) {

        <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        $( "#btnAdd" ).live( 'click', function() {

            var msg = '';

            var emailReg = /^([\w-+\.]+@([\w-]+\.)+[\w-]{2,})?$/;

//            if ( $( "#business_name" ).val() == '' ) {
//                msg += "<?php _e( 'Business Name required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
//            }

            if ( $( "#contact_name" ).val() == '' ) {
                msg += "<?php _e( 'Contact Name required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( $( "#contact_email" ).val() == '' ) {
                msg += "<?php _e( 'Email required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            } else if ( !emailReg.test( $( "#contact_email" ).val() ) ) {
                msg += "<?php _e( 'Invalid Email.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( $( "#contact_password" ).val() != '' ) {
                if ( $( "#contact_password2" ).val() == '' ) {
                    msg += "<?php _e( 'Confirm Password required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
                } else if ( $( "#contact_password" ).val() != $( "#contact_password2" ).val() ) {
                    msg += "<?php _e( 'Passwords are not matched.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
                }
            }

            if ( msg != '' ) {
                $( "#message" ).html( msg );
                $( "#message" ).show();
                return false;
            }
        });

        jQuery( '#contact_password' ).change( function() {
            if ( jQuery( '#contact_password' ).val() != '' ) {
                jQuery( '#send_password_block' ).show();
            }  else {
                jQuery( '#send_password_block' ).hide();
            }
        });

        $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

        $( 'body' ).on( 'keyup', '#contact_password, #contact_password2',
            function( event ) {
                checkPasswordStrength(
                    $('#contact_password'),
                    $('#contact_password2'),
                    $('#pass-strength-result'),
                    $('#btnAdd'),
                    wpc_password_protect.blackList
                );
            }
        );

    });

</script>