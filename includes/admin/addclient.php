<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';
</script>
<?php
global $wpdb;

//check auth
if ( !current_user_can( 'wpc_add_clients' ) && !( current_user_can( 'administrator' ) ) ) {
    do_action( 'wp_client_redirect', get_admin_url() . 'admin.php?page=wpclient_clients' );
}

extract( $_REQUEST );

$error = "";

if ( isset( $btnAdd ) ) {

    // validate at php side
    if ( empty( $contact_name ) ) // empty username
        $error .= __('A Contact Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( empty( $contact_username ) ) // empty username
        $error .= __('A username is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( empty( $contact_email ) ) // empty email
        $error .= __('A email is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( username_exists( $contact_username ) ) //  already exsits user name
        $error .= __('Sorry, that username already exists!<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( email_exists( $contact_email ) ) // email already exists
        $error .= __('Email address is already in use another user. Please use a unique email address.<br/>', WPC_CLIENT_TEXT_DOMAIN);

    if ( empty( $contact_password ) || empty( $contact_password2 ) ) {
            if ( empty( $contact_password ) ) // password
                $error .= __("Sorry, password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
            elseif ( empty( $contact_password2 ) ) // confirm password
                $error .= __("Sorry, confirm password is required.<br/>", WPC_CLIENT_TEXT_DOMAIN);
            elseif ( $contact_password != $contact_password2 )
                $error .= __("Sorry, Passwords are not matched! .<br/>", WPC_CLIENT_TEXT_DOMAIN);
    }


    /*our_hook_
        hook_name: wpc_client_validate_add_client_fields
        hook_title: Validate Add Client Form
        hook_description: Hook runs before saving client data on Add Client form.
        hook_type: filter
        hook_in: wp-client
        hook_location addclient.php
        hook_param: string $error
        hook_since: 3.4.9
    */
    $error = apply_filters( 'wpc_client_validate_add_client_fields', $error );

    if ( empty( $error ) ) {
        $userdata = array(
            'user_pass'     => $contact_password2,
            'user_login'    => esc_attr( trim( $contact_username ) ),
            'display_name'  => esc_attr( trim( $contact_name ) ),
            'user_email'    => esc_attr( $contact_email ),
            'role'          => 'wpc_client',
            'business_name' => ( isset( $business_name ) ) ? esc_attr( trim( $business_name ) ) : esc_attr( trim( $contact_name ) ),
            'contact_phone' => esc_attr( $contact_phone ),
            'send_password' => ( isset( $send_password ) ) ? esc_attr( $send_password ) : '',
        );


        do_action( 'wp_clients_update', $userdata );
        do_action( 'wp_client_redirect', 'admin.php?page=wpclient_clients&msg=a' );

        exit;
    }
}

$groups = $this->cc_get_groups();


//get managers
$args = array(
    'role'      => 'wpc_manager',
    'orderby'   => 'user_login',
    'order'     => 'ASC',
    'fields'    => array( 'ID','user_login' ),

);

$managers           = get_users( $args );

?>

<style type="text/css">
    .wrap input[type=text] {
        width:400px;
    }

    .wrap input[type=password] {
        width:400px;
    }

    .wrap textarea {
        width:400px;
    }
</style>

<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <div id="container23">

        <h2></h2>

        <ul class="menu">
            <?php echo $this->gen_tabs_menu( 'clients' ) ?>
        </ul>
        <span class="wpc_clear"></span>
        <div class="content23 add_client">

            <div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>


                <form action="" method="post">
                <table class="form-table">
                    <tr>
                        <td>
                            <label for="business_name"><?php printf( __( 'Business or %s Name', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?> | <span style="font-size: x-small;"><?php printf( __( 'This value is used in the {client_business_name} placeholder that is used in HUB Templates, %s Templates &amp; Emails', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?></span></label> <br/>
                            <input type="text" id="business_name" name="business_name" value="<?php if ( $error ) echo esc_html( $_REQUEST['business_name'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_name"><?php _e( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?> | <span style="font-size: x-small;"><?php printf( __( 'This value is used in the {contact_name} placeholder that is used in HUB Templates, %s Templates &amp; Emails', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?></span></label> <br/>
                            <input type="text" id="contact_name" name="contact_name" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_name'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_email"><?php _e( 'Email', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_email" name="contact_email" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_email'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="contact_phone"><?php _e( 'Phone', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="text" id="contact_phone" name="contact_phone" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_phone'] ); ?>" />
                        </td>
                    </tr>

                    <?php
                    /*our_hook_
                        hook_name: wpc_client_add_client_form_html
                        hook_title: Client Add Form
                        hook_description: Can be used for adding custom fields on Client Add Form.
                        hook_type: action
                        hook_in: wp-client
                        hook_location addclient.php
                        hook_param:
                        hook_since: 3.4.8
                    */

                    do_action( 'wpc_client_add_client_form_html' );
                    ?>

                    <tr>
                        <td>
                            <hr />
                            <label for="contact_username"><?php _e( 'Username', WPC_CLIENT_TEXT_DOMAIN ) ?> | <span style="font-size: x-small;"><?php sprintf( __( 'This value is used in the {user_name} placeholder that is used in HUB Templates, %s Templates &amp; Emails', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?></span></label> <br/>
                            <input type="text" id="contact_username" name="contact_username" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_username'] ); ?>" />
                        </td>
                    </tr>

                    <?php

                    /*our_hook_
                        hook_name: wpc_client_add_client_after_username
                        hook_title: Client Add Form
                        hook_description: Can be used for adding custom fields on Client Add Form after username field.
                        hook_type: action
                        hook_in: wp-client
                        hook_location addclient.php
                        hook_param:
                        hook_since: 3.4.8
                    */

                    do_action( 'wpc_client_add_client_after_username' );


                    if( current_user_can( 'administrator' ) ) {
                        $managers = (array)$managers;
                        if ( is_array( $managers ) && 0 < count( $managers ) ) {
                            if( isset( $_REQUEST['wpc_managers'] ) && count( $_REQUEST['wpc_managers'] ) ) {
                                $selected_managers = is_array( $_REQUEST['wpc_managers'] ) ? $_REQUEST['wpc_managers'] : array();
                            } else {
                                $selected_managers = array();
                            }
                        ?>
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
                                        'value' => implode( ',', $selected_managers )
                                    );
                                    $additional_array = array(
                                        'counter_value' => count( $selected_managers )
                                    );
                                    $this->acc_assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                        </tr>

                        <?php }
                    }

                    if ( is_array( $groups ) && 0 < count( $groups ) ) {
                        if( isset( $_REQUEST['wpc_circles'] ) && count( $_REQUEST['wpc_circles'] ) ) {
                            $selected_groups = is_array( $_REQUEST['wpc_circles'] ) ? $_REQUEST['wpc_circles'] : array();
                        } else {
                            $selected_groups = array();
                            foreach ( $groups as $group ) {
                                if( '1' == $group['auto_select'] && !$error ) {
                                    $selected_groups[] = $group['group_id'];
                                }
                            }
                        }
                    ?>

                    <tr>
                        <td>
                            <label><?php echo $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ?>:</label> <br/>
                            <?php
                                $link_array = array(
                                    'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] ),
                                    'text'    => __( 'Select', WPC_CLIENT_TEXT_DOMAIN ) . ' ' . $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p']
                                );
                                $input_array = array(
                                    'name'  => 'wpc_circles',
                                    'id'    => 'wpc_circles',
                                    'value' => implode( ',', $selected_groups )
                                );
                                $additional_array = array(
                                    'counter_value' => count( $selected_groups )
                                );
                                $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                            ?>
                        </td>
                    </tr>
                    <?php } ?>

                    <tr>
                        <td>
                            <label for="pass1"><?php _e( 'Password', WPC_CLIENT_TEXT_DOMAIN ) ?> | <span style="font-size: x-small;"><?php sprintf( __( 'This value is used in the {user_password} placeholder that is used in HUB Templates, %s Templates &amp; Emails', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['p'] ) ?></span></label> <br/>
                            <input type="password" id="pass1" name="contact_password" autocomplete="off" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_password'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="pass2"><?php _e( 'Confirm Password', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label> <br/>
                            <input type="password" id="pass2" name="contact_password2" value="<?php if ( $error ) echo esc_html( $_REQUEST['contact_password2'] ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="pass-strength-result" style="display: block;"><?php _e( 'Strength indicator', WPC_CLIENT_TEXT_DOMAIN ) ?></div>
                            <div class="description indicator-hint" style="clear:both"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ & ).', WPC_CLIENT_TEXT_DOMAIN ) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="send_password"><input type="checkbox" checked="checked" id="send_password" name="send_password"> <?php _e( 'Send this password to the new user by email.', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type='submit' name='btnAdd' id="btnAdd" class='button-primary' value='<?php printf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) ?>' />
                            &nbsp; &nbsp; &nbsp;
                            <input type='reset' name='btnreset' class='button-secondary' value='<?php _e( 'Reset Form', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
</div>
<?php
    $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
    $this->acc_get_assign_circles_popup( $current_page );
    $this->acc_get_assign_managers_popup( $current_page );
?>

<script type="text/javascript" language="javascript">

    jQuery( document ).ready( function( $ ) {
        <?php echo ( empty( $error ) )? '$( "#message" ).hide();' : '' ?>

        $( "#btnAdd" ).live( 'click', function() {

            var msg = '';

            var emailReg = /^([\w-+\.]+@([\w-]+\.)+[\w-]{2,})?$/;

            if ( $( "#business_name" ).val() == '' ) {
                msg += "<?php _e( 'Business Name required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( $( "#contact_name" ).val() == '' ) {
                msg += "<?php _e( 'Contact Name required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( $( "#contact_email" ).val() == '' ) {
                msg += "<?php _e( 'Contact Email required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            } else if ( !emailReg.test( $( "#contact_email" ).val() ) ) {
                msg += "<?php _e( 'Invalid Contact Email.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( $( "#pass1" ).val() == '' ) {
                msg += "<?php _e( 'Password required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            } else if ( $( "#pass2" ).val() == '' ) {
                msg += "<?php _e( 'Confirm Password required.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            } else if ( $( "#pass1" ).val() != $( "#pass2" ).val() ) {
                msg += "<?php _e( 'Passwords are not matched.', WPC_CLIENT_TEXT_DOMAIN ) ?><br/>";
            }

            if ( msg != '' ) {
                $( "#message" ).html( msg );
                $( "#message" ).show();
                return false;
            }
        });

        $( '.indicator-hint' ).html( wpc_password_protect.hint_message );

        $( 'body' ).on( 'keyup', '#pass1, #pass2',
            function( event ) {
                checkPasswordStrength(
                    $('#pass1'),
                    $('#pass2'),
                    $('#pass-strength-result'),
                    $('#btnAdd'),
                    wpc_password_protect.blackList
                );
            }
        );
    });

</script>