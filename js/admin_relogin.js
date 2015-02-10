jQuery(document).ready(function() {
    var style_array = {
        '.wpc_admin_login_block' : {
            'width' : '280px',
            'height': '20px',
            'padding' : '5px',
            'line-height': '20px',
            'font-size' : '12px',
            'background': '#fff',
            'border': '1px solid #eee',
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'z-index' : '999999',
            'box-sizing' : 'content-box'
        },
        '.wpc_admin_return_button' : {
            'display' : 'block',
            'float' : 'right',
            'background' : '#2ea2cc',
            'border-color' : '#0074a2',
            'background': '#2ea2cc',
            'border-color' : '#0074a2',
            'box-sizing' : 'border-box',
            'border-radius' : '3px',
            'white-space' : 'nowrap',
            'font-size' : '9px',
            'line-height' : '20px',
            'height' : '20px',
            'margin' : '0',
            'padding' : '0 10px 1px',
            'cursor' : 'pointer',
            'border-width' : '1px',
            'border-style' : 'solid',
            '-webkit-appearance' : 'none'
        }
    }

    jQuery('body').prepend('<div class="wpc_admin_login_block">' + wpc_var.message + ' <input type="button" class="button-primary wpc_admin_return_button" value="' + wpc_var.button_value + '" /></div>');

    for( block in style_array ) {
        for( style_name in style_array[ block ] ) {
            jQuery( block ).css( style_name, style_array[ block ][ style_name ] );
        }
    }

    jQuery('body').on( 'click', '.wpc_admin_return_button', function() {
        jQuery.ajax({
            type     : 'POST',
            dataType : 'json',
            url      : wpc_var.ajax_url,
            data: 'action=wpc_return_to_admin_panel&secure_key=' + wpc_var.secure_key,
            success: function( data ){
                if( data.status ) {
                    window.location = data.message;
                } else {
                    alert( data.message );
                }
            }
        });
    });

});