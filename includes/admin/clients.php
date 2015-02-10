<?php
global $wpdb, $wpc_client;


$where_circle          = '';

//filter group
if ( isset( $_GET['circle'] ) ) {
     $circle = $_GET['circle'];
     if ( is_numeric( $circle ) && 0 < $circle ) {
        $where_circle = " AND u.ID IN (SELECT d.client_id FROM {$wpdb->prefix}wpc_client_group_clients d WHERE d.group_id = $circle )";
    }
}

$where_clause = '';

if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
    $search_text = $_GET['s'];
    $where_clause .= "AND (
        u.user_login LIKE '%" . $search_text . "%' OR
        u.display_name LIKE '%" . $search_text . "%' OR
        um.meta_value LIKE '%" . $search_text . "%' OR
        u.user_email LIKE '%" . $search_text . "%'
    )";
}

$order_by = 'u.user_registered';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'user_login' :
            $order_by = 'user_login';
            break;
        case 'display_name' :
            $order_by = 'display_name';
            break;
        case 'business_name' :
            $order_by = 'um.meta_value';
            break;
        case 'user_email' :
            $order_by = 'user_email';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


//for manager
$mananger_clients = '';



if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Clients_List_Table extends WP_List_Table {

    var $no_items_message = '';
    var $sortable_columns = array();
    var $default_sorting_field = '';
    var $actions = array();
    var $bulk_actions = array();
    var $columns = array();

    function __construct( $args = array() ){
        $args = wp_parse_args( $args, array(
            'singular'  => __( 'item', WPC_CLIENT_TEXT_DOMAIN ),
            'plural'    => __( 'items', WPC_CLIENT_TEXT_DOMAIN ),
            'ajax'      => false
        ) );

        $this->no_items_message = $args['plural'] . ' ' . __(  'not found.', WPC_CLIENT_TEXT_DOMAIN );

        parent::__construct( $args );

    }

    function __call( $name, $arguments ) {
        return call_user_func_array( array( $this, $name ), $arguments );
    }

    function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
    }

    function column_default( $item, $column_name ) {
        if( isset( $item[ $column_name ] ) ) {
            return $item[ $column_name ];
        } else {
            return '';
        }
    }

    function no_items() {
        _e( $this->no_items_message, WPC_CLIENT_TEXT_DOMAIN );
    }

    function set_sortable_columns( $args = array() ) {
        $return_args = array();
        foreach( $args as $k=>$val ) {
            if( is_numeric( $k ) ) {
                $return_args[ $val ] = array( $val, $val == $this->default_sorting_field );
            } else if( is_string( $k ) ) {
                $return_args[ $k ] = array( $val, $k == $this->default_sorting_field );
            } else {
                continue;
            }
        }
        $this->sortable_columns = $return_args;
        return $this;
    }

    function get_sortable_columns() {
        return $this->sortable_columns;
    }

    function set_columns( $args = array() ) {
        if( count( $this->bulk_actions ) ) {
            $args = array_merge( array( 'cb' => '<input type="checkbox" />' ), $args );
        }
        $this->columns = $args;
        return $this;
    }

    function get_columns() {
        return $this->columns;
    }

    function set_actions( $args = array() ) {
        $this->actions = $args;
        return $this;
    }

    function get_actions() {
        return $this->actions;
    }

    function set_bulk_actions( $args = array() ) {
        $this->bulk_actions = $args;
        return $this;
    }

    function get_bulk_actions() {
        return $this->bulk_actions;
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['id']
        );
    }

    function column_managers( $item ) {
        global $wpc_client;

        $current_manager_ids = $wpc_client->cc_get_assign_data_by_assign( 'manager', 'client', $item['id'] );
        $count = ( $current_manager_ids ) ? count( $current_manager_ids ) : 0;

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['manager']['s'] )
        );
        $input_array = array(
            'name'  => 'wpc_managers_ajax[]',
            'id'    => 'wpc_managers_' . $item['id'],
            'value' => implode( ',', $current_manager_ids )
        );
        $additional_array = array(
            'counter_value' => $count
        );
        $html = $wpc_client->acc_assign_popup('manager', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $html;
    }

    function column_circles( $item ) {
        global $wpc_client;

        $client_groups = $wpc_client->cc_get_client_groups_id( $item['id'] );

        $count = ( $client_groups ) ? count( $client_groups ) : 0;

        $link_array = array(
            'data-id' => $item['id'],
            'data-ajax' => 1,
            'title'   => sprintf( __( 'Assign %s to', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] . ' ' . $wpc_client->custom_titles['circle']['p'] ) . ' ' . $item['username']
        );
        $input_array = array(
            'name'  => 'wpc_circles_ajax[]',
            'id'    => 'wpc_circles_' . $item['id'],
            'value' => implode( ',', $client_groups )
        );
        $additional_array = array(
            'counter_value' => $count
        );
        $circle_popup_html = $wpc_client->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $circle_popup_html;
    }

    function column_quick_actions( $item ) {

        return '<select name="" class="quick_action" id="qa_' . $item['id'] . '">
                    <option value="-1">' . __( 'Quick Action', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                    <option value="send_message">' . __( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                </select>';
    }

    function column_creation_date( $item ) {
        global $wpc_client;

        return $wpc_client->cc_date_timezone( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['creation_date'] ) );
        /*return '<select name="" class="quick_action" id="qa_' . $item['id'] . '">
                    <option value="-1">' . __( 'Quick Action', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                    <option value="send_message">' . __( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ) . '</option>
                </select>'; */
    }

    function column_username( $item ) {
        global $wpc_client;


        $actions = array();

        if ( current_user_can( 'wpc_edit_clients' ) || current_user_can( 'administrator' ) ) {
            $actions['edit'] = '<a href="admin.php?page=wpclient_clients&tab=edit_client&id=' . $item['id'] . '">' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( current_user_can( 'wpc_view_client_details' ) || current_user_can( 'administrator' ) ) {
            $actions['view'] = '<a href="#view_client" rel="' . $item['id'] . '_' . md5( 'wpcclientview_' . $item['id'] ) . '" class="various" >' . __( 'View', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'administrator' ) ) {
            $actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure you want to move this %s to the Archive?', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] ) . '");\' href="admin.php?page=wpclient_clients&action=archive&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_client_archive' . $item['id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Archive', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        }

        if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'administrator' ) ) {
             $actions['internal_notes'] = '<a href="#client_internal_note" rel="' . $item['id'] . '_' . md5( 'wpcclientinternalnote_' . $item['id'] ) . '" class="various_notes" >' . __( 'Internal Notes', WPC_CLIENT_TEXT_DOMAIN ). '</a>';
        }

        if ( current_user_can( 'wpc_admin_user_login' ) ) {
             $actions['wpc_admin_user_login'] = '<a href="admin.php?wpc_action=relogin&id=' . $item['id'] . '">' . sprintf( __( 'Login to %s account', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] ) . '</a>';
        }


        if ( !isset( $item['time_resend'] ) || ( $item['time_resend'] + 3600*23 ) < time() ) {
            $actions['wpc_send_welcome'] = '<a onclick=\'return confirm("' . __( 'Are you sure you want to Re-Send Welcome Email?', WPC_CLIENT_TEXT_DOMAIN ) . '");\' href="admin.php?page=wpclient_clients&action=send_welcome&client_id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_re_send_welcom' . $item['id'] ) .'">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';
        } else {
            $actions['wpc_send_welcome'] = '<span title="' . sprintf( __( 'Wait around %s hours for re-send it.', WPC_CLIENT_TEXT_DOMAIN ), round( ( ( $item['time_resend'] + 3600*24 ) -  time() ) / 3600 ) ) . '">' . __( 'Re-Send Welcome Email', WPC_CLIENT_TEXT_DOMAIN ) . '</span>';
        }

       /* $actions['wpc_send_welcome'] = '<a href="admin.php?page=wpclient_clients&action=send_welcome&client_id=' . $item['id'] . '">' .  __( 'Re-send welcome email', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';*/


        return sprintf('%1$s %2$s', '<span id="client_username_' . $item['id'] . '">' . $item['username'] . '</span>', $this->row_actions( $actions ) );
    }

    function column_email( $item ) {
        global $wpc_client, $wpdb;
        $wpc_clients_staff = $wpc_client->cc_get_settings( 'clients_staff' );
        if ( isset( $wpc_clients_staff['verify_email'] ) && 'yes' == $wpc_clients_staff['verify_email'] ) {
            $not_verify = get_user_meta( $item['id'], 'verify_email_key', true );
            $class = ( $not_verify ) ? 'not_verify_email' : 'verify_email';
        } else {
            $class = '';
        }
        return '<span class="' . $class . '">' . $item['email'] . '</span>';
    }

    function extra_tablenav( $which ){
        if ( 'top' == $which ) {
            global $wpdb, $wpc_client;

            $all_groups            = array();
            $all_circles_groups    = $wpdb->get_results( "SELECT group_id, group_name FROM {$wpdb->prefix}wpc_client_groups", "ARRAY_A" );

            //change structure of array for display circle name in row in table and selectbox
            foreach ( $all_circles_groups as $value ) {
                $all_groups[ $value['group_id'] ] = $value['group_name'];
            }

        ?>
           <div class="alignleft actions">
                <select name="circle" id="circle">
                    <option value="-1" <?php if( !isset( $_GET['circle'] ) || !in_array( $_GET['circle'], $all_groups ) ) echo 'selected'; ?>><?php printf( __( 'Select %s', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['circle']['s'] ) ?></option>
                    <?php
                        foreach ( $all_groups as $circle_id => $circle_name ) {
                         $selected = ( isset( $_GET['circle'] ) && $circle_id == $_GET['circle'] ) ? ' selected' : '' ;
                         echo '<option value="' . $circle_id . '"' . $selected . ' >';
                         _e( $circle_name, WPC_CLIENT_TEXT_DOMAIN );
                         echo '</option>';
                        }
                     ?>
                </select>
                <input type="button" value="<?php _e( 'Filter', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-secondary" id="button_filter_circle" name="" />
                <a class="add-new-h2" id="cancel_filter" <?php if ( !isset( $_GET['circle'] ) || -1 == $_GET['circle'] ) echo 'style="display: none;"'; ?> ><?php _e( 'Remove Filter', WPC_CLIENT_TEXT_DOMAIN ) ?><span style="color: #BC0B0B;"> x </span></a>
            </div>
        <?php
        }
    }


}

$excluded_clients = "'" . implode( "','", $this->cc_get_excluded_clients() ) . "'";


$ListTable = new WPC_Clients_List_Table( array(
        'singular'  => $this->custom_titles['client']['s'],
        'plural'    => $this->custom_titles['client']['p'],
        'ajax'      => false

));

$per_page   = $ListTable->get_items_per_page( 'users_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'username'          => 'user_login',
    'contact_name'      => 'display_name',
    'business_name'     => 'business_name',
    'email'             => 'user_email',
    'creation_date'     => 'user_registered',
) );

if ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'administrator' ) ) {
    $ListTable->set_bulk_actions(array(
        'archive'    => __( 'Move to Archive', WPC_CLIENT_TEXT_DOMAIN ),
        'delete_permanently'    => __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN )
    ));
} elseif( current_user_can( 'administrator' ) ) {
    $ListTable->set_bulk_actions(array(
        'delete_permanently'    => __( 'Delete Permanently', WPC_CLIENT_TEXT_DOMAIN )
    ));
} else {
    $ListTable->set_bulk_actions(array(
    ));
}

$set_columns = array(
    'cb'                => '<input type="checkbox" />',
    'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'contact_name'      => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
    'business_name'     => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'             => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
    'creation_date'     => __( 'Creation Date', WPC_CLIENT_TEXT_DOMAIN ),
    'circles'           => $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'],
    'managers'           => $this->custom_titles['manager']['p'],
    'quick_actions'     => __( 'Quick Actions', WPC_CLIENT_TEXT_DOMAIN ),
);
$ListTable->set_columns( $set_columns );



$sql = "SELECT count( u.ID )
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id
    WHERE
        um2.meta_key = '{$wpdb->prefix}capabilities'
        AND um2.meta_value LIKE '%s:10:\"wpc_client\";%'
        {$mananger_clients}
        AND u.ID NOT IN ({$excluded_clients})
        AND um.meta_key = 'wpc_cl_business_name'
        {$where_clause}
        {$where_circle}
    ";
$items_count = $wpdb->get_var( $sql );



$sql = "SELECT u.ID as id, u.user_login as username, u.user_registered as creation_date, u.display_name as contact_name, u.user_email as email, um.meta_value as business_name, um3.meta_value as time_resend
    FROM {$wpdb->users} u
    LEFT JOIN {$wpdb->usermeta} um ON ( u.ID = um.user_id AND um.meta_key = 'wpc_cl_business_name' )
    LEFT JOIN {$wpdb->usermeta} um2 ON ( u.ID = um2.user_id AND um2.meta_key = '{$wpdb->prefix}capabilities' )
    LEFT JOIN {$wpdb->usermeta} um3 ON ( u.ID = um3.user_id AND um3.meta_key = 'wpc_send_welcome_email' )
    WHERE
       um2.meta_value LIKE '%s:10:\"wpc_client\";%'
        {$mananger_clients}
        AND u.ID NOT IN ({$excluded_clients})

        {$where_clause}
        {$where_circle}
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$users = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->prepare_items();
$ListTable->items = $users;
$ListTable->set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );


if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients';
}

switch ( $ListTable->current_action() ) {
    /* archive action */
    case 'archive':

        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_archive' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( $this->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_archive_clients' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                //move to archive
                $this->archive_client( $client_id );
            }
            do_action( 'wp_client_redirect', add_query_arg( 'msg', 't', $redirect ) );
            exit;
        }
        do_action( 'wp_client_redirect', $redirect );
        exit;

    break;
    case 'send_welcome':
        if ( isset( $_GET['client_id'] ) && 0 < (int)$_GET['client_id'] ) {
            check_admin_referer( 'wpc_re_send_welcom' .  $_GET['client_id'] );
            $client_id = (int)$_GET['client_id'] ;
            $userdata = get_userdata( $client_id );
            $new_password = wp_generate_password();
            $update_data = array( 'ID' => $client_id, 'user_pass' => $new_password );

            wp_update_user( $update_data );

            $args = array(
                'client_id' => $client_id,
                'user_password' => $new_password
            );

            update_user_meta( $client_id, 'wpc_send_welcome_email', time() );

            //send email
            $this->cc_mail( 'self_client_registration', $userdata->user_email, $args, 'new_client' );


            do_action( 'wp_client_redirect', add_query_arg( 'msg', 'wel', $redirect ) );
            exit;
        } else {
            do_action( 'wp_client_redirect', $redirect );
            exit;
        }
    break;
    case 'delete_permanently':

        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_archive' .  $_REQUEST['id'] . get_current_user_id() );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } elseif( isset( $_REQUEST['item'] ) )  {
            check_admin_referer( 'bulk-' . sanitize_key( $this->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                //delete client
                wp_delete_user( $client_id );
            }
            if( 1 == count( $clients_id ) )
                do_action( 'wp_client_redirect', add_query_arg( 'msg', 'd', $redirect ) );
            else
                do_action( 'wp_client_redirect', add_query_arg( 'msg', 'ds', $redirect ) );
            exit;
        }
        do_action( 'wp_client_redirect', $redirect );
        exit;

    break;

    default:

        //remove extra query arg
        if ( !empty( $_GET['_wp_http_referer'] ) ) {
            do_action( 'wp_client_redirect', remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
            exit;
        }


    break;
}


$code = md5( 'wpc_client_' . get_current_user_id() . '_send_mess' );
?>

<style>
    .column-username {
        width: 25%;
    }

    .column-contact_name {
        width: 12%;
    }

    .column-business_name {
        width: 13%;
    }

    .column-circles {
        width: 10%;
    }

    .column-circles {
        text-align: center !important;
    }

</style>

<div class="wrap">

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>
    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch( $_GET['msg'] ) {
            case 'a':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Added</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'wel':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( 'Re-Sent Email for %s.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'ds':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'u':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Updated</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 't':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Moved to the Archive</strong>.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
            case 'ci':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . ( ( isset( $_GET['cl_count'] ) ) ? $_GET['cl_count'] . ' ' : '0 ')  . sprintf( __( '%s are <strong>Imported</strong>.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) . '</p></div>';
                break;
            case 'uf':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'There was an error uploading the file, please try again!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'id':
                echo '<div id="message" class="error wpc_notice fade"><p>' . sprintf( __( 'Wrong %s ID.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
                break;
        }
    }
    ?>

    <div id="container23">

        <h2></h2>

        <ul class="menu">
            <?php echo $this->gen_tabs_menu( 'clients' ) ?>
        </ul>
        <span class="wpc_clear"></span>
        <div class="content23 clients">

          <?php if ( current_user_can( 'administrator' ) ) { ?>
            <div class="alignleft actions">
                <form action="" method="post" enctype="multipart/form-data">
                    <table>
                        <tr>
                            <td>
                            <span style="color: #800000;">
                                <em>
                                    <span style="font-size: small;">
                                        <span style="line-height: normal;">
                                            <?php printf( __( 'Import %s from CSV File:', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) ?>
                                        </span>
                                    </span>
                                </em>
                            </span>
                            </td>
                            <td><input type="file" accept=".csv" /></td>
                            <td>
                                <?php
                                    $link_array = array(
                                        'title'   => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] ),
                                        'text'    => sprintf( __( 'Assign To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['p'] )
                                    );
                                    $input_array = array(
                                        'name'  => 'circles_for_import',
                                        'id'    => 'wpc_circles',
                                        'value' => ''
                                    );
                                    $additional_array = array(
                                        'counter_value' => 0
                                    );
                                    $this->acc_assign_popup('circle', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                                ?>
                            </td>
                            <td><input type="button" class='button' name="import" value="Import ! (Pro)" /></td>
                        </tr>
                    </table>

                </form>
            </div>
            <br clear="all" />
            <hr />
            <?php } ?>


           <form action="" method="get" name="wpc_clients_list_form" id="wpc_clients_list_form">

                <input type="hidden" name="page" value="wpclient_clients" />
                <?php $ListTable->search_box( sprintf( __( 'Search %s' , WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ), 'search-submit' ); ?>

                <?php $ListTable->display(); ?>
            </form>


            <?php
                $current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
                $this->acc_get_assign_circles_popup( $current_page );
                $this->acc_get_assign_managers_popup( $current_page );
            ?>


            <div style="display: none;">
                <div class="wpc_qa_send_message" id="qa_send_message" >
                    <h3><?php _e( 'Send Message To:', WPC_CLIENT_TEXT_DOMAIN ) ?> <span id="qa_send_username"></span></h3>
                    <form method="post" name="" id="">
                        <input type="hidden" name="" id="" value="" />
                        <table>
                            <tr>
                                <td>
                                    <textarea name="" id="" style="width:500px; height:100px;" placeholder="<?php _e( 'Type your private message here', WPC_CLIENT_TEXT_DOMAIN ) ?>" disabled></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                </td>
                            </tr>
                        </table>
                        <div style="clear: both; text-align: center;">

                            <input type="button" class='button' id="" name="" value="<?php _e( 'Send Message', WPC_CLIENT_TEXT_DOMAIN ) ?> (only in Pro)" />
                            <input type="button" class='button-primary' id="close_send_message" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </form>
                </div>
            </div>

            <?php if ( current_user_can( 'wpc_view_client_details' ) ||current_user_can( 'administrator' ) ) { ?>
            <div style="display: none;">
                <div id="view_client">
                    <div id="wpc_client_details_content"></div>
                </div>
            </div>
            <?php } ?>


            <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'administrator' ) ) {
                      $readonly_textarea = ( !current_user_can( 'wpc_update_client_internal_notes' ) && !current_user_can( 'administrator' ) ) ? ' readonly' : '';
            ?>
            <div style="display: none;">
                <div id="client_internal_note">
                   <h3><?php _e( 'Internal Notes:', WPC_CLIENT_TEXT_DOMAIN ) ?> <span id="wpc_client_name"></span></h3>
                    <form method="post" name="wpc_add_payment" id="wpc_add_payment">
                        <input type="hidden" id="wpc_client_id" value="" />
                        <table>
                            <tr>
                                <td>
                                    <label>
                                        <?php _e( 'Notes:', WPC_CLIENT_TEXT_DOMAIN ) ?>
                                        <br />
                                        <textarea cols="67" rows="3" id="wpc_internal_notes" autocomplete="off" <?php echo $readonly_textarea ?> ></textarea>
                                    </label>
                                    <br />
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <div id="ajax_result_message2" style="display: inline;"></div>
                                </td>
                            </tr>
                        </table>
                        <br />
                        <div style="clear: both; text-align: center;">
                            <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'administrator' ) ) { ?>
                            <input type="button" class='button-primary' id="update_internal_notes" value="<?php _e( 'Save Notes', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                            <?php } ?>
                            <input type="button" class='button' id="close_internal_notes" value="<?php _e( 'Close', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
                        </div>
                    </form>
                </div>
            </div>


            <?php } ?>
            </div>


            <script type="text/javascript">

                var site_url = '<?php echo site_url();?>';

                jQuery(document).ready(function(){

                    jQuery("#doaction").click( function() {
                        if( jQuery(this).parent().find('select[name=action]').val() == 'delete_permanently' ) {
                            if( confirm('<?php echo sprintf( __( 'Do you really want to delete permanently this %s?', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] ) ?>') ) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    });


                    //remove extra fields before submit form
                    jQuery( '#wpc_clients_list_form' ).submit( function() {
                        jQuery( '.change_circles' ).remove();
                        return true;
                    });


                     //filter group
                    jQuery( '#button_filter_circle' ).click( function() {
                        if ( '-1' != jQuery( '#circle' ).val() ) {
                            var req_uri = "<?php echo preg_replace( '/&circle=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                            window.location = req_uri + '&circle=' + jQuery( '#circle' ).val();
                        }
                        return false;
                    });


                    jQuery( '#cancel_filter' ).click( function() {
                        var req_uri = "<?php echo preg_replace( '/&circle=[0-9]+|&msg=[^&]+/', '', $_SERVER['REQUEST_URI'] ); ?>";
                        window.location = req_uri;
                        return false;
                    });


                    //Quick actions
                    jQuery( '.quick_action' ).change( function() {
                        var qa_id           = jQuery( this ).attr( 'id' );
                        var client_id       = jQuery( this ).attr( 'id' ).replace( 'qa_', '' );
                        var client_username = jQuery( '#client_username_' + client_id ).html();

                        if ( 'send_message' == jQuery( this ).val() ) {

                            jQuery( '#qa_send_message_client_id' ).val( client_id );
                            jQuery( '#qa_send_username' ).html( client_username );

                            jQuery.fancybox({
                                fitToView   : false,
                                minHeight   : 400,
                                autoResize  : true,
                                autoSize    : true,
                                closeClick  : false,
                                openEffect  : 'none',
                                closeEffect : 'none',
                                href : '#qa_send_message',
                                helpers : {
                                    title : null,
                                },
                                onCleanup: function () {
                                    jQuery( '#' + qa_id ).val( '-1' );
                                    jQuery('.fancybox-inline-tmp').replaceWith(jQuery(jQuery(this).attr('href')));
                                }
                            });



                        }

                    });


                    //close QA send message
                    jQuery( '#close_send_message' ).click( function() {
                        jQuery( '#qa_send_message_client_id' ).val( '' );
                        jQuery( '#qa_send_message_comment' ).val( '' );
                        jQuery.fancybox.close();
                    });


                    <?php if ( current_user_can( 'wpc_view_client_details' ) || current_user_can( 'administrator' ) ) { ?>
                    //open view client
                    jQuery( '.various' ).click( function() {
                        var id = jQuery( this ).attr( 'rel' );
                        jQuery( 'body' ).css( 'cursor', 'wait' );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            data: 'action=wpc_view_client&id=' + id,
                            dataType: "json",
                            success: function( data ){
                                jQuery( 'body' ).css( 'cursor', 'default' );

                                if( data.content ) {
                                    jQuery( '#wpc_client_details_content' ).html( data.content );
                                } else {
                                    jQuery( '#wpc_client_details_content' ).html( '' );
                                }

                                jQuery.fancybox({
                                    minWidth    : 500,
                                    minHeight   : 400,
                                    autoResize  : true,
                                    autoSize    : true,
                                    closeClick  : false,
                                    openEffect  : 'none',
                                    closeEffect : 'none',
                                    href : '#view_client',
                                    helpers : {
                                        title : null,
                                    },
                                    onCleanup: function () {
                                        jQuery('.fancybox-inline-tmp').replaceWith(jQuery(jQuery(this).attr('href')));
                                    }
                                });



                            },

                         });


                    });
                    <?php } ?>


                    <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'wpc_view_client_internal_notes' ) || current_user_can( 'administrator' ) ) { ?>
                    //open view Internal Notes
                    jQuery( '.various_notes' ).click( function() {
                        var id = jQuery( this ).attr( 'rel' );
                        jQuery( 'body' ).css( 'cursor', 'wait' );

                         jQuery( '#wpc_client_id' ).val( '' );
                         jQuery( '#wpc_client_name' ).html( '' );
                         jQuery( '#wpc_internal_notes' ).val( '' );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            data: 'action=wpc_get_client_internal_notes&id=' + id,
                            dataType: "json",
                            success: function( data ){
                                jQuery( 'body' ).css( 'cursor', 'default' );

                                if( data.client_name ) {
                                    jQuery( '#wpc_client_id' ).val( id );
                                    jQuery( '#wpc_client_name' ).html( data.client_name );
                                    jQuery( '#wpc_internal_notes' ).val( data.internal_notes );
                                } else {
                                    jQuery( '#wpc_internal_notes' ).val( '' );
                                }

                                jQuery.fancybox({
                                    autoResize  : true,
                                    autoSize    : true,
                                    openEffect  : 'none',
                                    openEffect  : 'none',
                                    closeEffect : 'none',
                                    href : '#client_internal_note',
                                    helpers : {
                                        title : null,
                                    },
                                    onCleanup: function () {
                                        jQuery('.fancybox-inline-tmp').replaceWith(jQuery(jQuery(this).attr('href')));
                                    }
                                });

                            },

                         });



                    });

                    //close Internal Notes
                    jQuery( '#close_internal_notes' ).click( function() {
                        jQuery( '#wpc_client_id' ).val( '' );
                        jQuery( '#wpc_client_name' ).html( '' );
                        jQuery( '#wpc_internal_notes' ).val( '' );
                        jQuery.fancybox.close();
                    });

                    <?php } ?>


                    <?php if ( current_user_can( 'wpc_update_client_internal_notes' ) || current_user_can( 'administrator' ) ) { ?>
                    // AJAX - Udate Internal Notes
                    jQuery( '#update_internal_notes' ).click( function() {
                        var id              = jQuery( '#wpc_client_id' ).val();
                        var content         = jQuery( '#wpc_internal_notes' ).val();
                        var crypt_content   = jQuery.base64Encode( content );
                        crypt_content       = crypt_content.replace( /\+/g, "-" );

                        jQuery( 'body' ).css( 'cursor', 'wait' );
                        jQuery( '#ajax_result_message2' ).html( '' );
                        jQuery( '#ajax_result_message2' ).show();
                        jQuery( '#ajax_result_message2' ).css( 'display', 'inline' );
                        jQuery( '#ajax_result_message2' ).html( '<div class="wpc_ajax_loading"></div>' );

                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo site_url() ?>/wp-admin/admin-ajax.php',
                            data: 'action=wpc_update_client_internal_notes&id=' + id + '&notes=' + crypt_content,
                            dataType: "json",
                            success: function( data ){
                                jQuery( 'body' ).css( 'cursor', 'default' );

                                    if( data.status ) {
                                        jQuery( '#ajax_result_message2' ).css( 'color', 'green' );
                                    } else {
                                        jQuery( '#ajax_result_message2' ).css( 'color', 'red' );
                                    }
                                    jQuery( '#ajax_result_message2' ).html( data.message );
                                    setTimeout( function() {
                                        jQuery( '#ajax_result_message2' ).fadeOut(1500);
                                    }, 2500 );

                                },
                            error: function( data ) {
                                jQuery( '#ajax_result_message2' ).css( 'color', 'red' );
                                jQuery( '#ajax_result_message2' ).html( 'Unknown error.' );
                                setTimeout( function() {
                                    jQuery( '#ajax_result_message2' ).fadeOut( 1500 );
                                }, 2500 );
                            }
                         });

                    });
                    <?php } ?>




                });


            </script>

        </div>

</div>
