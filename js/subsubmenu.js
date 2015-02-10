jQuery( document ).ready( function() {
    var color_hover;
    var color = jQuery( 'ul.wp-submenu li a:not( .current )' ).css( 'color' );

    for( var key in MySubsubmenu ) {
        var obj = jQuery( 'ul.wp-submenu li a[href="' + key + '"]' );
        obj.hover(
            function() {
                var top = jQuery( this ).position();
                top = top['top'] - 7 ;
                jQuery( '.subsubmenu' ).css( 'top', top );
            },
            function() {
                if ( !color_hover ) {
                    color_hover = jQuery( this ).css( 'color' ) ;
                }
            }
        );

        //jQuery( this ).parent().addClass( 'wp-has-submenu wp-not-current-submenu menu-top toplevel_page_wpclients menu-top-first menu-top-last menu-top-last' );
        //jQuery( this ).addClass( 'wp-has-submenu wp-not-current-submenu menu-top toplevel_page_wpclients menu-top-first menu-top-last menu-top-last'  );
        var array_val = MySubsubmenu[key];
        //obj.html( '<div class="wp-menu-name">' + obj.html() + '</div>' );
        var add_text = '<ul class="wp-submenu subsubmenu" style="display: none;">';
        for( var i in array_val ) {
            add_text += '<li><a href="' + array_val[i]['slug']  + '" style="font-weight: normal; color: ' + color + ';">' + array_val[i]['menu_title'] + '</a></li>' ;
        }
        add_text += '</ul>';

        obj.parent().append( add_text );
    }


    jQuery( 'ul.subsubmenu li a' ).hover(
        function() {
            jQuery( this ).css( 'color', color_hover );
        },
        function() {
            jQuery( this ).css( 'color', color );
        }
    );
});