  jQuery( document ).ready( function() {
    jQuery( '.wpc-hub-toolbar-dropdown .dropdown-toggle' ).dropdown()

    jQuery( '.dropdown-menu div' ).click( function() {
        jQuery( '.dropdown-menu div' ).removeClass( 'active' );
        jQuery( this ).addClass( 'active' );
        jQuery( '.dropdown-menu' ).hide();
        content_id = jQuery( this ).find( 'a.bar-link' ).attr( 'rel' );
        jQuery( '.hub_content' ).hide();
        jQuery( content_id ).show();

        jQuery( '.dropdown-toggle' ).html( jQuery( this ).find( 'a.bar-link' ).html() + '<span class="caret"></span>' );

    });

    jQuery( '.dropdown' ).mouseover( function() {
        jQuery( '.dropdown-menu' ).show();
    });

    jQuery( '.dropdown, .dropdown-menu' ).mouseleave( function() {
        jQuery( '.dropdown-menu' ).hide();
    });

    jQuery( '.hub_content:first' ).show();
});