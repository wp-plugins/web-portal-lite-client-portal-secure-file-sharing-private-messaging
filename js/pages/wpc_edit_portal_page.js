jQuery( document ).ready( function() {

    //update clientpage
    jQuery( '#update' ).click( function() {
        jQuery( '#wpc_action' ).val( 'update' );
        jQuery( '#edit_clientpage' ).submit();
        return false;
    });

    //delete clientpage
    jQuery( '#delete' ).click( function() {
        jQuery( '#wpc_action' ).val( 'delete' );
        jQuery( '#edit_clientpage' ).submit();
        return false;
    });

    //cancel edit clientpage
    jQuery( '#cancel' ).click( function() {
        jQuery( '#wpc_action' ).val( 'cancel' );
        jQuery( '#edit_clientpage' ).submit();
        return false;
    });

});