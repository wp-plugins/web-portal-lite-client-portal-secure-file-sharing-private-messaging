<?php
global $wpdb, $wpc_client;

//check auth
if ( !current_user_can( 'wpc_archive_clients' ) && !current_user_can( 'wpc_restore_clients' ) && !current_user_can( 'wpc_delete_clients' ) && !current_user_can( 'administrator' ) ) {
    do_action( 'wp_client_redirect', get_admin_url() . 'admin.php?page=wpclient_clients' );
}

if ( isset( $_GET['_wp_http_referer'] ) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), stripslashes_deep( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclient_clients&tab=archive';
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Archive_User_List_Table extends WP_List_Table {

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

    function column_username( $item ) {
        global $wpc_client;
        $actions = array();
        if ( current_user_can( 'wpc_restore_clients' ) || current_user_can( 'administrator' ) ) {
            $actions['restore'] = '<a class="edit" href="'. get_admin_url() . 'admin.php?page=wpclient_clients&tab=archive&action=restore&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_client_restore' . $item['id'] ) . '">Restore</a>';
        }
        if ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'administrator' ) ) {
            $actions['delete'] = '<a class="delete" onclick="return confirm(' . sprintf( __( '\'Are you sure to delete this %s?\'', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['s'] ) . ')"  href="'. get_admin_url() . 'admin.php?page=wpclient_clients&tab=archive&action=delete&id=' . $item['id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_client_delete' . $item['id'] ) . '">Delete Permanently</a>';
        }
        return sprintf('%1$s %2$s', $item['username'], $this->row_actions( $actions ) );
    }

    function column_contact_name( $item ) {
        return $item['contact_name'];
    }

    function column_business_name( $item ) {
        return $item['business_name'];
    }
}

$ListTable = new WPC_Archive_User_List_Table(array(
        'singular'  => $this->custom_titles['client']['s'],
        'plural'    => $this->custom_titles['client']['p'],
        'ajax'      => false

));


switch ( $ListTable->current_action() ) {
    // delete clients
    case 'delete':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_delete' .  $_REQUEST['id'] );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } else if ( isset( $_REQUEST['item'] ) ) {
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

    //restore clients
    case 'restore':
        $clients_id = array();
        if ( isset( $_REQUEST['id'] ) ) {
            check_admin_referer( 'wpc_client_restore' .  $_REQUEST['id'] );
            $clients_id = ( is_array( $_REQUEST['id'] ) ) ? $_REQUEST['id'] : (array) $_REQUEST['id'];
        } else if ( isset( $_REQUEST['item'] ) ) {
            check_admin_referer( 'bulk-' . sanitize_key( $this->custom_titles['client']['p'] ) );
            $clients_id = $_REQUEST['item'];
        }

        if ( count( $clients_id ) && ( current_user_can( 'wpc_delete_clients' ) || current_user_can( 'administrator' ) ) ) {
            foreach ( $clients_id as $client_id ) {
                //restore client
                $this->restore_client( $client_id );
            }
            if( 1 == count( $clients_id ) )
                do_action( 'wp_client_redirect', add_query_arg( 'msg', 'r', $redirect ) );
            else
                do_action( 'wp_client_redirect', add_query_arg( 'msg', 'rs', $redirect ) );
            exit;
        }
        do_action( 'wp_client_redirect', $redirect );
        exit;

    default:

        //remove extra query arg
        if ( !empty( $_GET['_wp_http_referer'] ) ) {
            do_action( 'wp_client_redirect', remove_query_arg( array( '_wp_http_referer', '_wpnonce'), stripslashes_deep( $_SERVER['REQUEST_URI'] ) ) );
            exit;
        }
    break;
}


$per_page   = $ListTable->get_items_per_page( 'users_per_page' );
$paged      = $ListTable->get_pagenum();

$where_clause = '';

if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
    $search_text = strtolower( trim( mysql_real_escape_string( $_GET['s'] ) ) );
    $where_clause .= "AND (
        u.user_login LIKE '%" . $search_text . "%' OR
        u.display_name LIKE '%" . $search_text . "%' OR
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
            $order_by = 'um2.meta_value';
            break;
        case 'user_email' :
            $order_by = 'user_email';
            break;
    }
}

$sql = "SELECT count( u.ID )
    FROM {$wpdb->prefix}users u
    LEFT JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id

    WHERE um.meta_key = 'archive' AND um.meta_value = 1
    " . $where_clause . "";
$items_count = $wpdb->get_var( $sql );


$sql = "SELECT u.ID as id, u.user_login as username, u.display_name as contact_name, u.user_email as email, um2.meta_value as business_name
    FROM {$wpdb->prefix}users u
    LEFT JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id
    LEFT JOIN {$wpdb->prefix}usermeta um2 ON u.ID = um2.user_id
    LEFT JOIN {$wpdb->prefix}usermeta um3 ON u.ID = um3.user_id
    WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND um.meta_value LIKE '%s:10:\"wpc_client\";%' AND um3.meta_key = 'archive' AND um3.meta_value = 1 AND um2.meta_key = 'wpc_cl_business_name'
    " . $where_clause . "

    ORDER BY $order_by
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$users = $wpdb->get_results( $sql, ARRAY_A );

$ListTable->set_sortable_columns( array(
    'username'          => 'user_login',
    'contact_name'      => 'display_name',
    'business_name'     => 'business_name',
    'email'             => 'user_email',
) );
$ListTable->set_bulk_actions(array(
    'restore'   => __( 'Restore', WPC_CLIENT_TEXT_DOMAIN ),
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));
$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'username'          => __( 'Username', WPC_CLIENT_TEXT_DOMAIN ),
    'contact_name'      => __( 'Contact Name', WPC_CLIENT_TEXT_DOMAIN ),
    'business_name'     => __( 'Business Name', WPC_CLIENT_TEXT_DOMAIN ),
    'email'             => __( 'E-mail', WPC_CLIENT_TEXT_DOMAIN ),
));


$ListTable->prepare_items();
$ListTable->items = $users;
$ListTable->set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );



if ( isset( $_GET['msg'] ) ) {
    switch( $_GET['msg'] ) {
        case 'r':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Restored</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
            break;
        case 'rs':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Restored</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) . '</p></div>';
            break;
        case 'd':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] ) . '</p></div>';
            break;
        case 'ds':
            echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s <strong>Deleted</strong> Successfully.', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ) . '</p></div>';
            break;
    }
}

?>

<div style="" class='wrap'>

    <?php echo $wpc_client->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <h2><?php printf( __( 'Archive %s', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['p'] ) ?></h2>
    <div id="container23">
        <ul class="menu">
            <?php echo $this->gen_tabs_menu( 'clients' ) ?>
        </ul>
        <div class="content23 clients_archive">
            <form action="" method="get">
                <input type="hidden" name="page" value="wpclient_clients" />
                <input type="hidden" name="tab" value="archive" />
                <div class="wpc_clear"></div>
                    <?php $ListTable->search_box( __( 'Search Users' ), 'user' ); ?>
                    <?php $ListTable->display(); ?>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){

        //reassign file from Bulk Actions
        jQuery( '#doaction2' ).click( function() {
            var action = jQuery( 'select[name="action2"]' ).val() ;
            jQuery( 'select[name="action"]' ).attr( 'value', action );
            return true;
        });

    });
</script>