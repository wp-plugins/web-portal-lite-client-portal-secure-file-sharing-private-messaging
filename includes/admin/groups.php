<?php

if ( isset( $_REQUEST['wpc_action2'] ) ) {
    switch ( $_REQUEST['wpc_action2'] ) {
        //action for edit Client Circle
        case 'edit_group':
            //check_admin_referer( 'wpc_edit_group' .  get_current_user_id() );
            $args = array(
                'group_id'          => ( isset( $_REQUEST['group_id'] ) && 0 < $_REQUEST['group_id'] ) ? $_REQUEST['group_id'] : '0',
                'group_name'        => ( isset( $_REQUEST['group_name'] ) ) ? $_REQUEST['group_name'] : '',
                'auto_select'       => ( isset( $_REQUEST['auto_select'] ) ) ? '1' : '0',
                'auto_add_files'    => ( isset( $_REQUEST['auto_add_files'] ) ) ? '1' : '0',
                'auto_add_pps'      => ( isset( $_REQUEST['auto_add_pps'] ) ) ? '1' : '0',
                'auto_add_manual'   => ( isset( $_REQUEST['auto_add_manual'] ) ) ? '1' : '0',
                'auto_add_self'     => ( isset( $_REQUEST['auto_add_self'] ) ) ? '1' : '0',
                'assign'            => '',
            );
            $this->create_group( $args );
            do_action( 'wp_client_redirect', add_query_arg( 'msg', 's', get_admin_url(). 'admin.php?page=wpclients_groups' ) );

        break;
    }
}

if ( isset($_REQUEST['_wp_http_referer']) ) {
    $redirect = remove_query_arg(array('_wp_http_referer' ), wp_unslash( $_REQUEST['_wp_http_referer'] ) );
} else {
    $redirect = get_admin_url(). 'admin.php?page=wpclients_groups';
}

if ( isset( $_REQUEST['action'] ) ) {
        switch ( $_REQUEST['action'] ) {
            /* delete action */
            case 'delete':

                $groups_id = array();
                if ( isset( $_REQUEST['id'] ) ) {
                    check_admin_referer( 'wpc_group_delete' .  $_REQUEST['id'] . get_current_user_id() );
                    $groups_id = (array) $_REQUEST['id'];
                } elseif( isset( $_REQUEST['item'] ) )  {
                    check_admin_referer( 'bulk-' . sanitize_key( $this->custom_titles['circle']['p'] ) );
                    $groups_id = $_REQUEST['item'];
                }

                if ( count( $groups_id ) ) {
                    foreach ( $groups_id as $group_id ) {
                        $this->delete_group( $group_id );
                    }
                    do_action( 'wp_client_redirect', add_query_arg( 'msg', 'd', $redirect ) );
                    exit;
                }
                do_action( 'wp_client_redirect', $redirect );
                exit;

            break;

            //action for create new Client Circle
            case 'create_group':
                //check_admin_referer( 'wpc_create_group' .  get_current_user_id() );
                $args = array(
                    'group_id'          => '0',
                    'group_name'        => ( isset( $_REQUEST['group_name'] ) ) ? $_REQUEST['group_name'] : '',
                    'auto_select'       => ( isset( $_REQUEST['auto_select'] ) ) ? '1' : '0',
                    'auto_add_files'    => ( isset( $_REQUEST['auto_add_files'] ) ) ? '1' : '0',
                    'auto_add_pps'      => ( isset( $_REQUEST['auto_add_pps'] ) ) ? '1' : '0',
                    'auto_add_manual'   => ( isset( $_REQUEST['auto_add_manual'] ) ) ? '1' : '0',
                    'auto_add_self'     => ( isset( $_REQUEST['auto_add_self'] ) ) ? '1' : '0',
                    'assign'            => ( isset( $_REQUEST['wpc_clients'] ) ) ? $_REQUEST['wpc_clients'] : ''
                );
                $this->create_group( $args );
            break;

    }
}

//remove extra query arg
if ( !empty( $_GET['_wp_http_referer'] ) ) {
    do_action( 'wp_client_redirect', remove_query_arg( array( '_wp_http_referer', '_wpnonce'), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}


global $wpdb;

$order_by = 'group_id';
if ( isset( $_GET['orderby'] ) ) {
    switch( $_GET['orderby'] ) {
        case 'group_name' :
            $order_by = 'group_name';
            break;
    }
}

$order = ( isset( $_GET['order'] ) && 'asc' ==  strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPC_Group_List_Table extends WP_List_Table {

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

        $this->no_items_message = $args['plural'] . ' ' . __( 'not found.', WPC_CLIENT_TEXT_DOMAIN );

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
            '<input type="checkbox" name="item[]" value="%s" />', $item['group_id']
        );
    }

    function column_group_id( $item ) {
        return $item['group_id'];
    }

    function column_group_name( $item ) {
        global $wpc_client;
        $actions = array();
        $actions['edit'] = '<span class="action_links"><a href="javascript:void(0);" id="edit_button_' . $item['group_id'] . '" onclick="jQuery(this).editGroup(' . $item['group_id'] . ', \'edit\' );" >' . __( 'Edit', WPC_CLIENT_TEXT_DOMAIN ) . '</a></span>';

        $actions['delete'] = '<a onclick=\'return confirm("' . sprintf( __( 'Are you sure to delete this %s?', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['circle']['s'] ) . '");\' href="admin.php?page=wpclients_groups&action=delete&id=' . $item['group_id'] . '&_wpnonce=' . wp_create_nonce( 'wpc_group_delete' . $item['group_id'] . get_current_user_id() ) . '&_wp_http_referer=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" >' . __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ) . '</a>';

        return sprintf( '%1$s %2$s', '<span id="group_name_block_' . $item['group_id'] . '">' . $item['group_name'] . '</span>
                    <div id="save_or_close_block_' . $item['group_id'] . '" style="display:none"><a href="javascript:void(0);" id="close_button_' . $item['group_id'] . '" onclick="jQuery(this).editGroup(' . $item['group_id'] . ', \'close\' );" >' . __( 'Close', WPC_CLIENT_TEXT_DOMAIN ) . '</a>&nbsp;|&nbsp;
                    <a onClick="jQuery(this).saveGroup();">' . __( 'Save', WPC_CLIENT_TEXT_DOMAIN ) . '</a></div>', $this->row_actions( $actions ) );
    }

    function column_auto_select( $item ) {
         return '<span id="auto_select_block_' . $item['group_id'] . '">' . ( 1 == $item['auto_select'] ? 'Yes' : 'No' ) . '</span>' ;
    }

    function column_auto_add_files( $item ) {
        return '<span id="auto_add_files_block_' . $item['group_id'] . '">' . ( isset( $item['auto_add_files'] ) && 1 == $item['auto_add_files']  ? 'Yes' : 'No') . '</span>' ;
    }

    function column_auto_add_pps( $item ) {
        return '<span id="auto_add_pps_block_' . $item['group_id'] . '">' . ( isset( $item['auto_add_pps'] ) && 1 == $item['auto_add_pps']  ? 'Yes' : 'No') . '</span>' ;
    }

    function column_auto_add_manual( $item ) {
        return '<span id="auto_add_manual_block_' . $item['group_id'] . '">' . ( isset( $item['auto_add_manual'] ) && 1 == $item['auto_add_manual']  ? 'Yes' : 'No') . '</span>' ;
    }

    function column_auto_add_self( $item ) {
        return '<span id="auto_add_self_block_' . $item['group_id'] . '">' . ( isset( $item['auto_add_self'] ) && 1 == $item['auto_add_self']  ? 'Yes' : 'No') . '</span>' ;
    }

    function column_assign( $item ) {
        global $wpc_client;
        $clients_id = $wpc_client->cc_get_group_clients_id( $item['group_id'] );

        $link_array = array(
            'title'   => sprintf( __( 'Assign %s to ', WPC_CLIENT_TEXT_DOMAIN ), $wpc_client->custom_titles['client']['p'] ) . $item['group_name'],
            'data-ajax' => true,
            'data-id' => $item['group_id'],
        );
        $input_array = array(
            'name'  => 'wpc_clients_ajax[]',
            'id'    => 'wpc_clients_' . $item['group_id'],
            'value' => implode( ',', $clients_id )
        );
        $additional_array = array(
            'counter_value' => count( $clients_id )
        );
        $html = $wpc_client->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array, false );

        return $html;
    }

}


$ListTable = new WPC_Group_List_Table( array(
        'singular'  => $this->custom_titles['circle']['s'],
        'plural'    => $this->custom_titles['circle']['p'],
        'ajax'      => false

));

$per_page   = $ListTable->get_items_per_page( 'users_per_page' );
$paged      = $ListTable->get_pagenum();

$ListTable->set_sortable_columns( array(
    'group_name'        => 'group_name',
    'group_id'          => 'group_id',
) );

$ListTable->set_bulk_actions(array(
    'delete'    => __( 'Delete', WPC_CLIENT_TEXT_DOMAIN ),
));

$ListTable->set_columns(array(
    'cb'                => '<input type="checkbox" />',
    'group_id'          => __( 'ID', WPC_CLIENT_TEXT_DOMAIN ),
    'group_name'        => sprintf( __( '%s %s Name', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['circle']['s'] ),
    'auto_select'       => __( 'Auto-Select', WPC_CLIENT_TEXT_DOMAIN ),
    'auto_add_files'    => __( 'Auto-Add Files', WPC_CLIENT_TEXT_DOMAIN ),
    'auto_add_pps'      => sprintf( __( 'Auto-Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
    'auto_add_manual'   => sprintf( __( 'Auto-Add Manual %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ),
    'auto_add_self'     => sprintf( __( 'Auto-Add Self-Registered %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ),
    'assign'            => sprintf( __( 'Assign %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'] ),
));

$sql = "SELECT count( group_id )
    FROM {$wpdb->prefix}wpc_client_groups
    ";
$items_count = $wpdb->get_var( $sql );

$sql = "SELECT *
    FROM {$wpdb->prefix}wpc_client_groups
    ORDER BY $order_by $order
    LIMIT " . ( $per_page * ( $paged - 1 ) ) . ", $per_page";
$groups = $wpdb->get_results( $sql, ARRAY_A );


$ListTable->prepare_items();
$ListTable->items = $groups;
$ListTable->set_pagination_args( array( 'total_items' => $items_count, 'per_page' => $per_page ) );

$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
?>

<style>
    .column-group_id {
        width: 5%;
    }

</style>

<div class="wrap">

    <?php echo $this->get_plugin_logo_block() ?>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        $msg = $_GET['msg'];
        switch($msg) {
            case 'ae':
                echo '<div id="message" class="error wpc_notice fade"><p>' . __( 'The Circle already exists!!!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 's':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . __( 'Changes to Circle have been saved!', WPC_CLIENT_TEXT_DOMAIN ) . '</p></div>';
                break;
            case 'c':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s %s is created!', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['circle']['s'] ) . '</p></div>';
                break;
            case 'd':
                echo '<div id="message" class="updated wpc_notice fade"><p>' . sprintf( __( '%s %s is deleted!', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'], $this->custom_titles['circle']['s'] ) . '</p></div>';
                break;
        }
    }
    ?>

    <div class="wpc_clear"></div>

    <div id="container23">

        <h2>
            <?php echo $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['p'] ?>:
            <a class="add-new-h2" id="slide_new_form_panel" href="javascript:;"><?php printf( __( 'Create New %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['s'] ) ?> <span class="arrow"></span></a>
        </h2>

        <div id="new_form_panel">
        <form method="post" action="" name="create_group" id="create_group" >
            <input type="hidden" name="action" value="create_group" />
            <input type="hidden" name="_wpnonce" value="<?php wp_create_nonce( 'wpc_create_group' . get_current_user_id() ) ?>" />

            <table class="form-table">
                <tr>
                    <td>
                        <?php printf( __( '%s Name', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['s'] ) ?>:<span class="required">*</span>
                        <input type="text" class="input" name="group_name" id="group_name" value="" size="30" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_select" id="auto_select" value="1" /> <?php printf( __( 'Auto-Select this %s on the Assign Popups', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['s'] ) ?>
                        </label>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_add_files" id="auto_add_files" value="1" /> <?php printf( __( 'Automatically assign new Files to this %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['circle']['s'] ) ?>
                        </label>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_add_pps" id="auto_add_pps" value="1" /> <?php printf( __( 'Automatically assign new %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['p'], $this->custom_titles['circle']['s'] ) ?>
                        </label>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_add_manual" id="auto_add_manual" value="1" /> <?php printf( __( 'Automatically assign new manual %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['s'] ) ?>
                        </label>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_add_self" id="auto_add_self" value="1" /> <?php printf( __( 'Automatically assign new self-registered %s to this %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['s'] ) ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php
                            $link_array = array(
                                'title'   => sprintf( __( 'Assign %s to %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['s'] ),
                                'text'    => sprintf( __( 'Assign %s To %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['p'], $this->custom_titles['circle']['s'] )
                            );
                            $input_array = array(
                                'name'  => 'wpc_clients',
                                'id'    => 'wpc_clients',
                                'value' => ''
                            );
                            $additional_array = array(
                                'counter_value' => 0
                            );
                            $this->acc_assign_popup('client', isset( $current_page ) ? $current_page : '', $link_array, $input_array, $additional_array );
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="button" name="add_group" class="button-primary" id="add_group" value="<?php printf( __( 'Add %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['s'] ) ?>" />
                    </td>
                </tr>
            </table>

        </form>
    </div>

        <span class="wpc_clear"></span>
        <h3><?php printf( __( 'List of %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['client']['s'] . ' ' . $this->custom_titles['circle']['s'] ) ?>:</h3>
        <span class="wpc_clear"></span>

        <div class="content23 circles">

           <form action="" method="get" name="edit_group" id="edit_group">
                <input type="hidden" name="wpc_action2" id="wpc_action2" value="" />
                <input type="hidden" name="page" value="wpclients_groups" />
                <?php $ListTable->display(); ?>
            </form>

        </div>

        <script type="text/javascript">
            var site_url = '<?php echo site_url();?>';

            jQuery( document ).ready( function() {

                //reassign file from Bulk Actions
                jQuery( '#doaction2' ).click( function() {
                    var action = jQuery( 'select[name="action2"]' ).val() ;
                    jQuery( 'select[name="action"]' ).attr( 'value', action );

                    return true;
                });

                //Show/hide new Client Circle form
                jQuery( '#slide_new_form_panel' ).click( function() {
                    jQuery( '#new_form_panel' ).slideToggle( 'slow' );
                    jQuery( this ).toggleClass( 'active' );
                    return false;
                });


                //Add Client Circle action
                jQuery( "#add_group" ).click( function() {

                    jQuery( '#group_name' ).parent().parent().attr( 'class', '' );

                    if ( "" == jQuery( "#group_name" ).val() ) {
                        jQuery( '#group_name' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }

                    jQuery( '#create_group' ).submit();
                });


                var group_name          = "";
                var array_group_auto = [ 'select', 'add_files', 'add_pps', 'add_manual', 'add_self'] ;
                var old_value = [];


                jQuery.fn.editGroup = function ( id, action ) {
                    if ( action == 'edit' ) {
                        if( jQuery('#edit_group input[name=group_name]').length ) {
                            return;
                        }
                        group_name = jQuery( '#group_name_block_' + id ).html();
                        group_name = group_name.replace(/(^\s+)|(\s+$)/g, "");

                        jQuery( '#group_name_block_' + id ).html( '<input type="text" name="group_name" size="30" id="edit_group_name"  value="' + group_name + '" /><input type="hidden" name="group_id" value="' + id + '" />' );


                        var val = "";
                        var check = "";
                        for(  var i=0; i<array_group_auto.length; i++ ) {
                            val = jQuery( '#auto_' + array_group_auto[i] + '_block_' + id ).html();
                            val = val.replace(/(^\s+)|(\s+$)/g, "");
                            old_value[ array_group_auto[i] ] = val;
                            if ( 'Yes' == val )
                                check = ' checked="checked"' ;
                            else
                                check = '' ;
                            jQuery( '#auto_' + array_group_auto[i] + '_block_' + id ).html( '<input type="checkbox" name="auto_' + array_group_auto[i] + '" id="edit_auto_' + array_group_auto[i] + '" value="1"' + check + '/>' );
                        }

                        //jQuery( '#edit_group input[type="button"]' ).attr( 'disabled', true );

                        jQuery( this ).parent().parent().parent().attr('style', "display:none" );
                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:block;" );

                        return;
                    }

                    if ( action == 'close' ) {
                        jQuery( '#group_name_block_' + id ).html( group_name );
                        for(  var i=0; i<array_group_auto.length; i++ ) {
                            jQuery( '#auto_' + array_group_auto[i] + '_block_' + id ).html( old_value[ array_group_auto[i] ] );
                        }

                        jQuery( this ).parent().next().attr('style', "display:block" );
                        jQuery( '#save_or_close_block_' + id ).attr('style', "display:none;" );
                        return;
                    }


                };


                jQuery.fn.saveGroup = function ( ) {

                    jQuery( '#edit_group_name' ).parent().parent().attr( 'class', '' );

                    if ( '' == jQuery( '#edit_group_name' ).val() ) {
                        jQuery( '#edit_group_name' ).parent().parent().attr( 'class', 'wpc_error' );
                        return false;
                    }

                    jQuery( '#wpc_action2' ).val( 'edit_group' );
                    jQuery( '#edit_group' ).submit();
                };

            });
        </script>

    </div>

</div>
