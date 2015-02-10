<?php


// Widget for Client Login/logout
class wpc_client_widget extends WP_Widget {
    //constructor
    function wpc_client_widget() {
        global $wpc_client;

        $widget_ops = array( 'classname' => 'wpc_widget_login', 'description' => sprintf( __( 'Allow %s to login/Logout.', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] ) );
        parent::WP_Widget( 'wpc_client_widget', $wpc_client->plugin['title'] . __( ': Login/Logout', WPC_CLIENT_TEXT_DOMAIN ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $wpc_client;

        extract( $args );

        $instance['text_remember'] = ( isset( $instance['text_remember'] ) && !empty( $instance['text_remember'] ) ) ? $instance['text_remember'] : __( 'Remember Me', WPC_CLIENT_TEXT_DOMAIN );
        $instance['text_forgot'] = ( isset( $instance['text_forgot'] ) && !empty( $instance['text_forgot'] ) ) ? $instance['text_forgot'] : __( 'Lost Your Password?', WPC_CLIENT_TEXT_DOMAIN );
        $instance['enable_forgot'] = ( isset( $instance['enable_forgot'] ) && !empty( $instance['enable_forgot'] ) ) ? $instance['enable_forgot'] : '';
        $instance['disable_hub_redirect'] = ( isset( $instance['disable_hub_redirect'] ) && !empty( $instance['disable_hub_redirect'] ) ) ? $instance['disable_hub_redirect'] : '';

        $title                  = apply_filters( 'widget_title', $instance['title'] );
        $text_login             = apply_filters( 'widget_title', $instance['text_login'] );
        $text_pass              = apply_filters( 'widget_title', $instance['text_pass'] );
        $text_login_button      = apply_filters( 'widget_title', $instance['text_login_button'] );
        $text_welcome           = apply_filters( 'widget_title', $instance['text_welcome'] );
        $text_logout            = apply_filters( 'widget_title', $instance['text_logout'] );
        $logout_redirect        = apply_filters( 'widget_title', $instance['logout_redirect'] );
        $text_remember          = apply_filters( 'widget_title', $instance['text_remember'] );
        $text_forgot            = apply_filters( 'widget_title', $instance['text_forgot'] );
        $enable_forgot          = apply_filters( 'widget_title', $instance['enable_forgot'] );
        $disable_hub_redirect   = apply_filters( 'widget_title', $instance['disable_hub_redirect'] );

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        ?>

    <div class="wpclient_login_block">

        <?php if ( isset( $GLOBALS['wpclient_login_msg'] ) && '' != $GLOBALS['wpclient_login_msg'] )
            echo '<div id="wpclient_message">' . $GLOBALS['wpclient_login_msg'] . '</div>'
        ?>

        <form method="post" name="wpclient_login_form" id="wpclient_login_form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
            <?php if ( !is_user_logged_in() ) { ?>
            <input type="hidden" name="wpclient_disable_redirect" id="wpclient_disable_redirect" value="<?php echo $disable_hub_redirect ?>"/>
            <ul style="list-style: none;">
                <li>
                    <label for="wpclient_login"><?php echo $text_login ?></label>
                </li>
                <li>
                    <input type="text" name="wpclient_login" id="wpclient_login" value="<?php echo ( isset( $_POST['wpclient_login'] ) ) ? esc_attr( $_POST['wpclient_login'] ) : '' ?>" />
                </li>
                <li>
                    <label for="wpclient_pass"><?php echo $text_pass ?></label>
                </li>
                <li>
                    <input type="password" name="wpclient_pass" id="wpclient_pass" value="" />
                </li>
                <li class="forgetmenot">
                    <label for="wpclient_rememberme"><input type="checkbox" name="wpclient_rememberme" id="wpclient_rememberme" value="forever" tabindex="90"><?php echo $text_remember ?></label>
                </li>
                <li>
                    <input type="submit" name="wpclient_login_button" id="wpclient_login_button" value="<?php echo $text_login_button ?>" />
                </li>
                <?php
                $clients_staff_settings = $wpc_client->cc_get_settings( 'clients_staff' );
                if( isset( $clients_staff_settings['lost_password'] ) && 'yes' == $clients_staff_settings['lost_password'] && $enable_forgot ) { ?>
                    <li id="nav">
                    <?php $login_url = explode( '?', $wpc_client->cc_get_login_url() );
                        $login_url = $login_url[0]; ?>
                        <a href="<?php echo $login_url ?>?action=lostpassword" title="Password Lost and Found"><?php echo $text_forgot ?></a>
                    </li>
                <?php } ?>
            </ul>
            <?php
            } else {

                $link = $wpc_client->cc_get_logout_url();

                if ( isset( $logout_redirect ) && '' != $logout_redirect )
                    $link = add_query_arg( array( 'redirect_to' => $logout_redirect ), $link );

            ?>
            <ul style="list-style: none;">
                <li>
                    <span><?php echo do_shortcode( $text_welcome ); ?></span>
                </li>
                <li>
                     <a href="<?php echo $link ?>" ><?php echo $text_logout ?></a>
                </li>
            </ul>
            <?php
            }
            ?>
        </form>
    </div><!--//wpc_client-widget  -->


        <?php echo $after_widget; ?>

    <?php

    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance                           = $old_instance;
        $instance['title']                  = strip_tags( $new_instance['title'] );
        $instance['text_login']             = strip_tags( $new_instance['text_login'] );
        $instance['text_pass']              = strip_tags( $new_instance['text_pass'] );
        $instance['text_login_button']      = strip_tags( $new_instance['text_login_button'] );
        $instance['text_welcome']           = strip_tags( $new_instance['text_welcome'] );
        $instance['text_logout']            = strip_tags( $new_instance['text_logout'] );
        $instance['logout_redirect']        = strip_tags( $new_instance['logout_redirect'] );
        $instance['text_forgot']            = strip_tags( $new_instance['text_forgot'] );
        $instance['text_remember']          = strip_tags( $new_instance['text_remember'] );
        $instance['enable_forgot']          = strip_tags( $new_instance['enable_forgot'] );
        $instance['disable_hub_redirect']   = strip_tags( $new_instance['disable_hub_redirect'] );
        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {

        if ( isset( $instance['title'] ) )
            $title = esc_attr( $instance['title'] );
        else
            $title = '';

        if ( isset( $instance['text_login'] ) )
            $text_login = esc_attr( $instance['text_login'] );
        else
            $text_login = __( 'Login:', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['text_pass'] ) )
            $text_pass = esc_attr( $instance['text_pass'] );
        else
            $text_pass = __( 'Password:', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['text_login_button'] ) )
            $text_login_button = esc_attr( $instance['text_login_button'] );
        else
            $text_login_button = __( 'Login', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['text_welcome'] ) )
            $text_welcome = esc_attr( $instance['text_welcome'] );
        else
            $text_welcome = 'Welcome [wpc_client_business_name]';

        if ( isset( $instance['text_logout'] ) )
            $text_logout = esc_attr( $instance['text_logout'] );
        else
            $text_logout = __( 'Logout', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['logout_redirect'] ) )
            $logout_redirect = esc_attr( $instance['logout_redirect'] );
        else
            $logout_redirect = '';

        if ( isset( $instance['text_remember'] ) )
            $text_remember = esc_attr( $instance['text_remember'] );
        else
            $text_remember = __( 'Remember Me', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['text_forgot'] ) )
            $text_forgot = esc_attr( $instance['text_forgot'] );
        else
            $text_forgot = __( 'Lost Your Password?', WPC_CLIENT_TEXT_DOMAIN );

        if ( isset( $instance['enable_forgot'] ) && $instance['enable_forgot'] == '1' )
            $enable_forgot = 'checked="checked"';
        else
            $enable_forgot = '';
        if ( isset( $instance['disable_hub_redirect'] ) && $instance['disable_hub_redirect'] == '1' )
            $disable_hub_redirect = 'checked="checked"';
        else
            $disable_hub_redirect = '';


        ?>
            <p>
                <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:', WPC_CLIENT_TEXT_DOMAIN) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_login' ); ?>"><?php _e( 'Text for Login field:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_login' ); ?>" name="<?php echo $this->get_field_name( 'text_login' ); ?>" type="text" value="<?php echo $text_login; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_pass' ); ?>"><?php _e( 'Text for Password field:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_pass' ); ?>" name="<?php echo $this->get_field_name( 'text_pass' ); ?>" type="text" value="<?php echo $text_pass; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_login_button' ); ?>"><?php _e( 'Text for Login Button:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_login_button' ); ?>" name="<?php echo $this->get_field_name( 'text_login_button' ); ?>" type="text" value="<?php echo $text_login_button; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_remember' ); ?>"><?php _e( 'Text for Remember Me:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_remember' ); ?>" name="<?php echo $this->get_field_name( 'text_remember' ); ?>" type="text" value="<?php echo $text_remember; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'enable_forgot' ); ?>"><?php _e( 'Enable Forgot Password:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'enable_forgot' ); ?>" name="<?php echo $this->get_field_name( 'enable_forgot' ); ?>" type="checkbox" <?php echo $enable_forgot ?> value="1" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_forgot' ); ?>"><?php _e( 'Text for Forgot Password:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_forgot' ); ?>" name="<?php echo $this->get_field_name( 'text_forgot' ); ?>" type="text" value="<?php echo $text_forgot; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'disable_hub_redirect' ); ?>"><?php _e( 'Disable redirect on HUB Page:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'disable_hub_redirect' ); ?>" name="<?php echo $this->get_field_name( 'disable_hub_redirect' ); ?>" type="checkbox" <?php echo $disable_hub_redirect ?> value="1" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_welcome' ); ?>"><?php _e( 'Welcome text:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_welcome' ); ?>" name="<?php echo $this->get_field_name( 'text_welcome' ); ?>" type="text" value="<?php echo $text_welcome; ?>" />
                <br>
                <span class="description">
                    [wpc_client_business_name] -
                    <br>
                    <?php _e( 'Display Business Name', WPC_CLIENT_TEXT_DOMAIN ) ?>
                    <br>
                    [wpc_client_contact_name] -
                    <br>
                    <?php _e( 'Display Contact Name', WPC_CLIENT_TEXT_DOMAIN ) ?>
                </span>
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'text_logout' ); ?>"><?php _e( 'Text for Logout link:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'text_logout' ); ?>" name="<?php echo $this->get_field_name( 'text_logout' ); ?>" type="text" value="<?php echo $text_logout; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'logout_redirect' ); ?>"><?php _e( 'Logout redirect link:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'logout_redirect' ); ?>" name="<?php echo $this->get_field_name( 'logout_redirect' ); ?>" type="text" value="<?php echo $logout_redirect; ?>" />
            </p>

        <?php
    }

} // class wpc_client_widget


add_action( 'widgets_init', create_function( '', 'return register_widget("wpc_client_widget");' ) );

?>
