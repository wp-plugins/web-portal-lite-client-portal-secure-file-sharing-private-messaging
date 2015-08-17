<?php


// Widget for Client Portal Pages list
class wpc_client_widget_pp extends WP_Widget {
    //constructor
    function __construct() {
        global $wpc_client;

        $widget_ops = array( 'classname' => 'wpc_widget_pp', 'description' => sprintf( __( 'Display %s %s list.', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'], $wpc_client->custom_titles['portal']['p'] ) );
        parent::__construct( 'wpc_client_widget_pp', $wpc_client->plugin['title'] . sprintf( __( ': %s list', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['portal']['p'] ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $wpc_client, $wpdb;

        extract( $args );

        $title                  = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        ?>

    <div class="wpclient_portal_pages_block">

        <?php
        if ( is_user_logged_in() ) {

            if( current_user_can( 'wpc_client' ) ) {
                $user_id = get_current_user_id();
            } else {
                //$user_id = get_current_user_id();
                $user_id = $wpc_client->current_plugin_page['client_id'];
            }

            $client_portal_page_ids = $wpc_client->cc_get_assign_data_by_assign( 'portal_page', 'client', $user_id );
            $client_groups = $wpc_client->cc_get_client_groups_id( $user_id );
            foreach ( $client_groups as $client_group ) {
                $group_portal_page_ids = $wpc_client->cc_get_assign_data_by_assign( 'portal_page', 'circle', $client_group );
                $client_portal_page_ids = array_merge( $client_portal_page_ids, $group_portal_page_ids );
            }
            $client_portal_page_ids = array_unique( $client_portal_page_ids );
            $in = "('" . implode( "','", $client_portal_page_ids ) . "')";
            $client_portal_page = $wpdb->get_results(
                "SELECT ID,
                    post_title,
                    post_name
                FROM {$wpdb->posts}
                WHERE ID IN $in AND post_status = 'publish'",
            ARRAY_A );
            ?>
            <ul style="list-style: none;">
            <?php
                foreach( $client_portal_page as $page ) {

                    //make link
                    if ( $wpc_client->permalinks ) {
                        $page['url'] = $wpc_client->cc_get_slug( 'portal_page_id' ) . $page['post_name'];
                    } else {
                        $page['url'] = add_query_arg( array( 'wpc_page' => 'portal_page', 'wpc_page_value' => $page['post_name'] ), $wpc_client->cc_get_slug( 'portal_page_id', false ) );
                    }

                    echo '<li><a href="' . $page['url'] . '">' . $page['post_title'] . '</a></li>';
                }

             ?>
            </ul>
            <?php
        }
        ?>
    </div><!--//wpc_client-widget_pp  -->


        <?php echo $after_widget; ?>

    <?php

    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance                       = $old_instance;
        $instance['title']              = strip_tags( $new_instance['title'] );
        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        global $wpc_client;
        if ( isset( $instance['title'] ) )
            $title = esc_attr( $instance['title'] );
        else
            $title = sprintf( __( '%s list', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['portal']['p'] );

        ?>
            <p>
                <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:', WPC_CLIENT_TEXT_DOMAIN) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

            </p>
        <?php
    }

} // class wpc_client_widget_pp


add_action( 'widgets_init', create_function( '', 'return register_widget("wpc_client_widget_pp");' ) );

?>