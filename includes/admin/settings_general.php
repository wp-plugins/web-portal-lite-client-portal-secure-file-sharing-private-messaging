<?php

if ( isset( $_POST['update_settings'] ) ) {

        if ( isset( $_POST['wpc_general'] ) ) {
            $settings = $_POST['wpc_general'];
        } else {
            $settings = array();
        }

        do_action( 'wp_client_settings_update', $settings, 'general' );
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_settings&msg=u' );
        exit;

}

$wpc_general = $this->cc_get_settings( 'general' );
$wpc_currency = $this->cc_get_settings( 'currency' );

?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        var wpc_busy = 0;

        jQuery('.add_currency').click(function() {
            jQuery('.wpc_add_row').remove();
            jQuery('.currency_table tbody').prepend('<tr class="wpc_add_row">' +
                '<td>' +
                    '<input type="radio" name="default_check" class="wpc_default_new" value="1" />' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="title" class="wpc_title" value="" />' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="code" class="wpc_code" value="" />' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="symbol" class="wpc_symbol" value="" />' +
                '</td>' +
                '<td>' +
                    '<select name="align" class="wpc_align">' +
                        '<option value="left"><?php _e( 'Left', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                        '<option value="right"><?php _e( 'Right', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                    '</select>' +
                    '<a href="javascript:void(0);" class="wpc_remove_currency_row"><?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                    '<a href="javascript:void(0);" class="wpc_add_currency_row"><?php _e( 'Save', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                '</td>' +
            '</tr>');
        });

        jQuery('.currency_table').on('click', '.wpc_remove_currency_row', function() {
            jQuery('.wpc_add_row').remove();
        });

        jQuery('.currency_table').on('click', '.wpc_add_currency_row', function() {
            if( !wpc_busy ) {
                wpc_busy = 1;
                var wpc_default = jQuery(this).parents('.currency_table tr').find('.wpc_default_new:checked').length;
                var title = jQuery(this).parents('.currency_table tr').find('.wpc_title').val();
                if( title.length == 0 ) {
                    alert('<?php _e( 'Title is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var code = jQuery(this).parents('.currency_table tr').find('.wpc_code').val();
                if( code.length == 0 ) {
                    alert('<?php _e( 'Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var symbol = jQuery(this).parents('.currency_table tr').find('.wpc_symbol').val();
                if( symbol.length == 0 ) {
                    alert('<?php _e( 'Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var align = jQuery(this).parents('.currency_table tr').find('.wpc_align').val();

                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=add&default=' + wpc_default + '&title=' + title + '&code=' + code + '&symbol=' + symbol + '&align=' + align,
                    success  : function( data ){
                        if( data.status ) {
                            jQuery('.wpc_add_row').remove();
                            jQuery('.currency_table tbody').prepend('<tr>' +
                                '<td>' +
                                    '<input type="radio" name="default" class="wpc_default" value="' + data.message + '" ' + ( wpc_default ? 'checked="checked"' : '' ) + ' />' +
                                '</td>' +
                                '<td>' +
                                    '<strong><span class="currency_title">' + title + '</span></strong>' +
                                    '<div class="row-actions">' +
                                        '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + data.message + '">Edit</a> | </span>' +
                                        '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + data.message + '">Delete Permanently</a></span>' +
                                    '</div>' +
                                '</td>' +
                                '<td>' +
                                    '<span class="currency_code">' + code + '</span>' +
                                '</td>' +
                                '<td>' +
                                    '<span id="currency_symbol">' + symbol + '</span>' +
                                '</td>' +
                                '<td>' +
                                    '<span id="currency_align">' + align.charAt(0).toUpperCase() + align.substr(1) + '</span>' +
                                '</td>' +
                            '</tr>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.wpc_edit_currency_row', function() {
            if( !wpc_busy ) {
                wpc_busy = 1;
                var id = jQuery(this).data('id');

                var wpc_default = jQuery(this).parents('.currency_table tr').find('.wpc_default:checked').length;
                var title = jQuery(this).parents('.currency_table tr').find('.wpc_title').val();
                if( title.length == 0 ) {
                    alert('<?php _e( 'Title is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var code = jQuery(this).parents('.currency_table tr').find('.wpc_code').val();
                if( code.length == 0 ) {
                    alert('<?php _e( 'Alphabetic Currency Code is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var symbol = jQuery(this).parents('.currency_table tr').find('.wpc_symbol').val();
                if( symbol.length == 0 ) {
                    alert('<?php _e( 'Currency Symbol is empty', WPC_CLIENT_TEXT_DOMAIN ); ?>');
                    return false;
                }
                var align = jQuery(this).parents('.currency_table tr').find('.wpc_align').val();
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=edit&id=' + id + '&default=' + wpc_default + '&title=' + title + '&code=' + code + '&symbol=' + symbol + '&align=' + align,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message.wpc_default ? 'checked="checked"' : '' ) + ' />' +
                            '</td>' +
                            '<td>' +
                                '<strong><span class="currency_title">' + title + '</span></strong>' +
                                '<div class="row-actions">' +
                                    '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + id + '">Edit</a> | </span>' +
                                    '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + id + '">Delete Permanently</a></span>' +
                                '</div>' +
                            '</td>' +
                            '<td>' +
                                '<span class="currency_code">' + code + '</span>' +
                            '</td>' +
                            '<td>' +
                                '<span id="currency_symbol">' + symbol + '</span>' +
                            '</td>' +
                            '<td>' +
                                '<span id="currency_align">' + align.charAt(0).toUpperCase() + align.substr(1) + '</span>' +
                            '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.wpc_back_currency_row', function() {
            var id = jQuery(this).data('id');
            if( !wpc_busy ) {
                wpc_busy = 1;
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=get_data&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message['default'] ? 'checked="checked"' : '' ) + ' />' +
                            '</td>' +
                            '<td>' +
                                '<strong><span class="currency_title">' + data.message.title + '</span></strong>' +
                                '<div class="row-actions">' +
                                    '<span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="' + id + '">Edit</a> | </span>' +
                                    '<span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="' + id + '">Delete Permanently</a></span>' +
                                '</div>' +
                            '</td>' +
                            '<td>' +
                                '<span class="currency_code">' + data.message.code + '</span>' +
                            '</td>' +
                            '<td>' +
                                '<span id="currency_symbol">' + data.message.symbol + '</span>' +
                            '</td>' +
                            '<td>' +
                                '<span id="currency_align">' + data.message.align.charAt(0).toUpperCase() + data.message.align.substr(1) + '</span>' +
                            '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
            return false;
        });

        jQuery('.currency_table').on('click', '.delete_currency', function() {
            var id = jQuery(this).data('id');
            if( jQuery(this).parents('.currency_table tr').find('.wpc_default:checked').length ) {
                alert("<?php _e( "You can't remove currency with default mark", WPC_CLIENT_TEXT_DOMAIN ); ?>");
                return;
            }
            var obj = jQuery(this);
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=delete&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            obj.parents('.currency_table tr').remove();
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

        jQuery('.currency_table').on('click', '.edit_currency', function() {
            var id = jQuery(this).data('id');
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                var obj = jQuery(this);
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=get_data&id=' + id,
                    success  : function( data ){
                        if( data.status ) {
                            jQuery('.wpc_add_row').remove();
                            obj.parents('.currency_table tr').html('<td>' +
                                '<input type="radio" name="default" class="wpc_default" value="' + id + '" ' + ( data.message['default'] ? 'checked="checked"' : '' ) + ' />' +
                            '</td>' +
                            '<td>' +
                                '<input type="text" name="title" class="wpc_title" value="' + data.message.title + '" />' +
                            '</td>' +
                            '<td>' +
                                '<input type="text" name="code" class="wpc_code" value="' + data.message.code + '" />' +
                            '</td>' +
                            '<td>' +
                                '<input type="text" name="symbol" class="wpc_symbol" value="' + data.message.symbol + '" />' +
                            '</td>' +
                            '<td>' +
                                '<select name="align" class="wpc_align">' +
                                    '<option value="left" ' + ( data.message.align == 'left' ? 'selected="selected"' : '' ) + '><?php _e( 'Left', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                                    '<option value="right" ' + ( data.message.align == 'right' ? 'selected="selected"' : '' ) + '><?php _e( 'Right', WPC_CLIENT_TEXT_DOMAIN ); ?></option>' +
                                '</select>' +
                                '<a href="javascript:void(0);" class="wpc_back_currency_row" data-id="' + id + '"><?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                                '<a href="javascript:void(0);" class="wpc_edit_currency_row" data-id="' + id + '"><?php _e( 'Save', WPC_CLIENT_TEXT_DOMAIN ); ?></a>' +
                            '</td>');
                        } else {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

        jQuery('.currency_table').on('click', '.wpc_default', function() {
            var id = jQuery(this).val();
            if( id.length > 0 && !wpc_busy ) {
                wpc_busy = 1;
                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php',
                    data     : 'action=wpc_settings&tab=currency&act=set_default&id=' + id,
                    success  : function( data ){
                        if( !data.status ) {
                            alert( data.message );
                        }
                        wpc_busy = 0;
                    }
                });
            }
        });

    });
</script>

<form action="" method="post" name="wpc_settings" id="wpc_settings" >

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'Portal Display Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="show_hub_title"><?php _e( 'Show HUB Title on HUB page', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_general[show_hub_title]" id="show_hub_title" style="width: 100px;">
                            <option value="yes" <?php echo ( isset( $wpc_general['show_hub_title'] ) && $wpc_general['show_hub_title'] == 'yes' ) ? "selected" : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( isset( $wpc_general['show_hub_title'] ) && $wpc_general['show_hub_title'] == 'no' ) ? "selected" : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="graphic"><?php _e( 'Graphic (for shortcode [wpc_client_graphic])', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <input type="text" name="wpc_general[graphic]" id="graphic" value="<?php echo ( isset( $wpc_general['graphic'] ) ) ?$wpc_general['graphic'] : '' ?>" />
                    </td>
                </tr>

            </table>
        </div>
    </div>

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'Custom Navigation Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <div class="inside">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="show_custom_menu"><?php _e( 'Show custom menu on login', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                    </th>
                    <td>
                        <select name="wpc_general[show_custom_menu]" id="show_custom_menu" style="width: 100px;">
                            <option value="yes" <?php echo ( isset( $wpc_general['show_custom_menu'] ) && $wpc_general['show_custom_menu'] == 'yes' ) ? "selected" : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            <option value="no" <?php echo ( isset( $wpc_general['show_custom_menu'] ) && $wpc_general['show_custom_menu'] == 'no' ) ? "selected" : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                        </select>
                    </td>
                </tr>

                <?php
                $locations = get_registered_nav_menus();
                if ( is_array( $locations ) && 0 < count( $locations ) ) {
                    foreach( $locations as $key => $value ) {
                ?>
                    <tr valign="top">
                        <th scope="row">
                            <label for="custom_menu_logged_in"><?php echo $value ?> <span class="description"><?php _e( '(logged-in)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>:</label>
                        </th>
                        <td>
                            <?php
                                $nav_menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
                                $num_menus = count( array_keys( $nav_menus ) );
                                if( $num_menus > 0 ) {

                            ?>
                            <select name="wpc_general[custom_menu_logged_in][<?php echo $key ?>]" id="custom_menu_logged_in" style="width: 100px;">
                                <option value=""></option>
                                <?php
                                    foreach ( $nav_menus as $menu ) {
                                ?>
                                        <option value="<?php echo $menu->term_id; ?>" <?php echo ( isset( $wpc_general['custom_menu_logged_in'][$key] ) && $wpc_general['custom_menu_logged_in'][$key] == $menu->term_id ) ? 'selected' : ''; ?> ><?php echo $menu->name; ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            <?php
                                }
                                else {
                            ?>
                                <span class="description"><?php _e( 'Please first create menu in Appearance->Menus', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <?php
                                }
                            ?>
                            <span class="description"><?php _e( '(Custom menu for logged-in users)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="custom_menu_logged_out"><?php echo $value ?> <span class="description"><?php _e( '(not logged-in)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>:</label>
                        </th>
                        <td>
                            <?php
                                $nav_menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
                                $num_menus = count( array_keys( $nav_menus ) );
                                if ( $num_menus > 0 ) {
                            ?>
                            <select name="wpc_general[custom_menu_logged_out][<?php echo $key ?>]" id="custom_menu_logged_out" style="width: 100px;">
                                <option value=""></option>
                                <?php
                                    foreach ( $nav_menus as $menu ) {
                                ?>
                                        <option value="<?php echo $menu ->term_id; ?>" <?php echo ( isset( $wpc_general['custom_menu_logged_out'][$key] ) && $wpc_general['custom_menu_logged_out'][$key] == $menu->term_id ) ? 'selected' : ''; ?> ><?php echo $menu->name; ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            <?php
                                }
                                else {
                            ?>
                                <span class="description"><?php _e( 'Please first create menu in Appearance->Menus', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                            <?php
                                }
                            ?>
                            <span class="description"><?php _e( '(Custom menu for not logged-in users)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
                        </td>
                    </tr>

                <?php
                    }
                }
                ?>

                    <tr valign="top">
                        <th scope="row">
                            <label for="show_hub_link"><?php _e( 'Show HUB page link in menu', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        </th>
                        <td>
                            <select name="wpc_general[show_hub_link]" id="show_hub_link" style="width: 100px;">
                                <option value="no" <?php echo ( isset( $wpc_general['show_hub_link'] ) && $wpc_general['show_hub_link'] == 'no' ) ? "selected" : '' ?> ><?php _e( 'No', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                                <option value="yes" <?php echo ( isset( $wpc_general['show_hub_link'] ) && $wpc_general['show_hub_link'] == 'yes' ) ? "selected" : '' ?> ><?php _e( 'Yes', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="hub_link_text"><?php _e( 'HUB page link text', WPC_CLIENT_TEXT_DOMAIN ) ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="wpc_general[hub_link_text]" id="hub_link_text" value="<?php echo ( isset( $wpc_general['hub_link_text'] ) ) ? $wpc_general['hub_link_text'] : '' ?>" />
                        </td>
                    </tr>



            </table>
        </div>
    </div>

    <div class="postbox">
        <h3 class='hndle'><span><?php _e( 'Currency Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></span></h3>
        <a class="add-button add_currency" href="javascript:void(0);"><?php _e( 'Add Currency', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
        <div class="inside">
            <table class="wp-list-table widefat fixed currency_table" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="default" class="manage-column column-default" style="width: 50px;"><?php _e( 'Default', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                        <th scope="col" id="title" class="manage-column column-title" style=""><?php _e( 'Title', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                        <th scope="col" id="code" class="manage-column column-code" style=""><?php _e( 'Alphabetic Code', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                        <th scope="col" id="symbol" class="manage-column column-symbol" style="width: 50px;"><?php _e( 'Symbol', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                        <th scope="col" id="align" class="manage-column column-align" style="width: 250px;"><?php _e( 'Align', WPC_CLIENT_TEXT_DOMAIN ); ?></th>
                    </tr>
                </thead>

                <tbody id="the-list">
                    <?php foreach( $wpc_currency as $key=>$val ) { ?>
                        <tr>
                            <td>
                                <input type="radio" name="default" class="wpc_default" value="<?php echo $key; ?>" <?php checked( $val['default'], 1 ); ?> />
                            </td>
                            <td>
                                <strong><span class="currency_title"><?php echo $val['title']; ?></span></strong>
                                <div class="row-actions">
                                    <span class="edit"><a class="edit_currency" href="javascript: void(0);" data-id="<?php echo $key; ?>">Edit</a> | </span>
                                    <span class="delete"><a class="delete_currency" href="javascript: void(0);" data-id="<?php echo $key; ?>">Delete Permanently</a></span>
                                </div>
                            </td>
                            <td>
                                <span class="currency_code"><?php echo $val['code']; ?></span>
                            </td>
                            <td>
                                <span id="currency_symbol"><?php echo $val['symbol']; ?></span>
                            </td>
                            <td>
                                <span id="currency_align"><?php echo ucfirst( $val['align'] ); ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <input type='submit' name='update_settings' class='button-primary' value='<?php _e( 'Update Settings', WPC_CLIENT_TEXT_DOMAIN ) ?>' />
</form>