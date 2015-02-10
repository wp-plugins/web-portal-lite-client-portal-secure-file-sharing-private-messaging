jQuery( document ).ready( function() {
    jQuery( '#wpc_select_client_for_preview' ).change( function() {
        jQuery( 'body' ).css( 'cursor', 'wait' );

        var id = jQuery( this ).val();

        jQuery.ajax({
            type        : 'post',
            dataType    : 'json',
            url         : wpc_preview.url,
            data        : 'action=wpc_set_portal_page_client&id=' + id,
            success     : function( response ) {

                jQuery( 'body' ).css( 'cursor', 'default' );

                if ( response.status ) {
                    window.location = "";
                } else {
                    return false;
                }
            }
        });
    });
});