var wpc_marks_type = 'checkbox';
var checkbox_session = new Array();
var wpc_input_ref = '';
var data_type = '';
var wpc_current_page = '';
var wpc_object_id = 0;
var data_send_ajax = 0;
var wpc_page_num = 1;
var full_ids_list = new Array();
var checkbox_name = '';
var included_ids = new Array();
var additional_vars = new Array();

jQuery( document ).ready( function() {
    wpc_current_page = ( typeof wpc_popup_var != 'undefined' && wpc_popup_var.hasOwnProperty('current_page') ) ? wpc_popup_var.current_page : '';

    var ajax_ref_array = new Array();
    jQuery(".wpc_fancybox_link").each(function() {
        data_send_ajax = jQuery(this).data('ajax');
        if( data_send_ajax ) {
            wpc_input_ref = jQuery(this).data('input');
            if( typeof jQuery(this).data('id') != 'undefined' ) {
                wpc_input_ref += '_' + jQuery(this).data('id');
            }
            ajax_ref_array.push( wpc_input_ref );
        }
    });

    jQuery('form').submit(function(){
        for( key in ajax_ref_array ) {
            if( jQuery(this).find( '#' + ajax_ref_array[ key ] ).length ) {
                jQuery(this).find( '#' + ajax_ref_array[ key ] ).remove();
            }
        }
        return true;
    });

    jQuery('body').on( 'click', ".wpc_fancybox_link", function() {

        var href = jQuery(this).attr('href');
        wpc_input_ref = jQuery(this).data('input');
        data_type = jQuery(this).data('type');
        data_send_ajax = jQuery(this).data('ajax');

        if( jQuery(this).data('id') != 'undefined' ) {
            wpc_object_id = jQuery(this).data('id');
        } else {
            wpc_object_id = 0;
        }
        if( typeof jQuery(this).data('id') != 'undefined' ) {
            wpc_input_ref += '_' + jQuery(this).data('id');
        }
        if( jQuery(this).data('marks') == 'radio' || jQuery(this).data('marks') == 'checkbox' ) {
            wpc_marks_type = jQuery(this).data('marks');
        } else {
            wpc_marks_type = 'checkbox';
        }
        var include = jQuery("#" + wpc_input_ref).data('include');
        if( typeof include != 'undefined' && include.toString().length ) {
            included_ids = jQuery("#" + wpc_input_ref).data('include').toString().split(',');
        } else {
            included_ids = new Array();
        }

        jQuery.wpcShowLoading();

        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
            data: {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' ) + 'get_popup_pagination_data',
                marks_type : wpc_marks_type,
                page : 1,
                goto : 'first',
                search : "",
                data_type : data_type,
                open_popup : true,
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page
            },
            dataType: "json",
            success: function(data) {
                if(data.html) {
                    jQuery(href).find(".wpc_inside table tr td").html( data.html );
                    jQuery(href).wpcResetPopup();

                    full_ids_list = data.ids_list;
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                jQuery(href).find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                jQuery(href).find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        jQuery(href).find(".wpc_page_num").html( data.page );
                    } else {
                        jQuery(href).find(".wpc_page_num").html('');
                    }

                    if( jQuery("#" + wpc_input_ref).val().length ) {
                        checkbox_session = jQuery("#" + wpc_input_ref).val().split(',');
                    }

                    for( key in checkbox_session ) {
                        if( checkbox_session.hasOwnProperty( key ) ) {
                            jQuery(href).find(".wpc_inside table tr td input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }

                    if( checkbox_session.length != 0 && checkbox_session.length == data.count ) {
                        jQuery(href).find('.wpc_select_all').prop('checked', true);
                    } else if( jQuery(href).find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]:checked").length == jQuery(href).find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length && jQuery(href).find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length != 0 ) {
                        jQuery(href).find('.wpc_select_all_at_page').prop('checked', true);
                    }

                    jQuery(href).find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    jQuery(href).find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );

                } else {
                    alert( data );
                    jQuery.wpcHideLoading();
                    return;
                }

                jQuery.wpcHideLoading();

                var popup_width = jQuery( href ).actual('width') + 30;
                if( jQuery(window).width() < popup_width ) {
                    popup_width = jQuery(window).width() - 30;
                }
                if( popup_width < 850 ) popup_width = 850;

                var popup_height = jQuery( href ).actual('height');
                if( jQuery(window).height() < popup_height ) {
                    popup_height = jQuery(window).width();
                }

                tb_href = '#TB_inline?width=' + ( popup_width ) + '&height=' + popup_height + '&inlineId=' + href.substring(1);

                if( typeof wpc_popup_title[ href.substring(1) ] != 'undefined' ) {
                    var p_title = wpc_popup_title[ href.substring(1) ];
                } else {
                    var p_title = '';
                }
                tb_show(p_title, tb_href,'');

                additional_vars = new Array();
                ref_array = new Array();
                if( typeof data.blocks != 'undefined' ) {
                    for( key in data.blocks ) {
                        if( typeof data.blocks[ key ].ref != 'undefined' && typeof data.blocks[ key ].code != 'undefined' ) {
                            if( array_search( data.blocks[ key ].ref, ref_array ) === false ) {
                                ref_array.push( data.blocks[ key ].ref );
                                jQuery( '#TB_window ' + data.blocks[ key ].ref ).html('');
                            }
                            jQuery( '#TB_window ' + data.blocks[ key ].ref ).append( data.blocks[ key ].code );

                            jQuery( '#TB_window ' + data.blocks[ key ].ref ).find("select, textarea, input").each(function() {
                                if( jQuery( this ).prop('name') != 'undefined' ) {
                                    additional_vars.push( jQuery( this ).prop('name') );
                                }
                            });
                        }
                    }
                }

                wpc_assign_popup_position();
            },
            error: function(data) {
                jQuery.fancybox.hideLoading();
                jQuery(href).find(".wpc_inside table tr td").html(data.html);
            }
        });

    });

    jQuery("body").on( 'change', '.wpc_show', function() {
        display = jQuery(this).val();
        order = jQuery(this).parents('.postbox').find('.wpc_order').val();
        var goto = 'first';
        var page = 1;
        var search_text = jQuery(this).parents('.postbox').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }

        var link = jQuery(this);
        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
            data: {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type : wpc_marks_type,
                data_type : data_type,
                page : page,
                goto : goto,
                display : display,
                order : order,
                search : search_text,
                current_page : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parent().parent().find(".wpc_inside table tr td").html(data.html);
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parent().find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parent().find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parent().find(".wpc_page_num").html(data.page);
                    } else {
                        link.parent().find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }

                    link.find(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]:checked").length == link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length && link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length != 0 ) {
                        link.find(".wpc_select_all_at_page").prop('checked', true);
                    }

                    link.parents('.postbox').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    link.parents('.postbox').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                }
            },
            error: function(data) {
                link.parent().parent().find(".wpc_inside table tr td").html(data.html);
            }
        });

    });

    jQuery("body").on( 'change', '.wpc_order', function() {
        display = jQuery(this).parents('.postbox').find('.show').val();
        order = jQuery(this).val();
        var goto = 'first';
        var page = 1;
        var search_text = jQuery(this).parents('.postbox').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }

        var link = jQuery(this);
        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
            data: {
                action       : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type   : wpc_marks_type,
                data_type    : data_type,
                page         : page,
                goto         : goto,
                display      : display,
                order        : order,
                search       : search_text,
                current_page : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parents('.postbox').find(".wpc_inside table tr td").html(data.html);
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parents('.postbox').find(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parents('.postbox').find(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parents('.postbox').find(".wpc_page_num").html( data.page );
                    } else {
                        link.parents('.postbox').find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }

                    link.find(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]:checked").length == link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length && link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length != 0 ) {
                        link.find(".wpc_select_all_at_page").prop('checked', true);
                    }

                    link.parents('.postbox').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                    link.parents('.postbox').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                }
            },
            error: function(data) {
                link.parent().parent().find(".wpc_inside table tr td").html(data.html);
            }
        });
    });

        jQuery("body").on( 'keypress', '.wpc_search_field', function( e ) {
        if(e.which == 13) {

            jQuery('.wpc_select_all').prop('checked', false);
            jQuery('.wpc_select_all_at_page').prop('checked', false);

            search_text = jQuery(this).val();
            var goto = 'first';
            var page = 1;
            display = jQuery(this).parents('.postbox').find('.wpc_show').val();
            order = jQuery(this).parents('.postbox').find('.wpc_order').val();

            var link = jQuery(this);
            jQuery.ajax({
                type: "POST",
                url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
                data: {
                    action           : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                    marks_type       : wpc_marks_type,
                    data_type        : data_type,
                    page             : page,
                    goto             : goto,
                    display          : display,
                    order            : order,
                    search           : search_text,
                    current_page     : wpc_current_page,
                    already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                    open_popup : true,
                    included_ids : included_ids.join(','),
                    send_ajax : data_send_ajax,
                    input_ref : wpc_input_ref,
                    current_page : wpc_current_page
                },
                dataType: "json",
                success: function(data){
                    if(data.html) {
                        full_ids_list = data.ids_list;
                        link.parents('.postbox').find(".wpc_inside table tr td").html(data.html);
                        for(key in data.buttons) {
                            if( data.buttons.hasOwnProperty( key ) ) {
                                if(data.buttons[key]) {
                                    link.parents('.postbox').find(".wpc_pagination_links[rel="+key+"]").show();
                                } else {
                                    link.parents('.postbox').find(".wpc_pagination_links[rel="+key+"]").hide();
                                }
                            }
                        }
                        if(data.count > data.per_page) {
                            link.parents('.postbox').find(".wpc_page_num").html(data.page);
                        } else {
                            link.parents('.postbox').find(".wpc_page_num").html('');
                        }

                        wpc_page_num = data.page;
                        if( checkbox_session.length ) {
                            for( key in checkbox_session ) {
                                link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                            }
                        }

                        link.find(".wpc_select_all_at_page").prop('checked', false);
                        if( link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]:checked").length == link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length && link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length != 0 ) {
                            link.find(".wpc_select_all_at_page").prop('checked', true);
                        }

                        link.parents('.postbox').find(".wpc_popup_statistic .wpc_total_count").html( data.count );
                        link.parents('.postbox').find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
                    }
                },
                error: function(data) {
                    link.parents('.postbox').find(".wpc_inside table tr td").html(data.html);
                }
            });
        }
    });

    jQuery("body").on( 'click', '.wpc_pagination_links', function( e ) {
        var goto = jQuery(this).attr('rel');
        var display = jQuery(this).parents('.postbox').find('.wpc_show').val();
        var order = jQuery(this).parents('.postbox').find('.wpc_order').val();
        var search_text = jQuery(this).parents('.postbox').find('.wpc_search_field').val();
        if( search_text == wpc_popup_var.search_text ) {
            search_text = '';
        }
        if( !(typeof(wpc_page_num) == 'number' || !isNaN(wpc_page_num)) )
            wpc_page_num = 1;

        var link = jQuery(this);

        jQuery.ajax({
            type: "POST",
            url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
            data: {
                action           : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'get_popup_pagination_data',
                marks_type       : wpc_marks_type,
                data_type        : data_type,
                page             : wpc_page_num,
                goto             : goto,
                display          : display,
                order            : order,
                search           : search_text,
                current_page     : wpc_current_page,
                already_assinged : ( order == 'first_asc' ? checkbox_session.join(',') : '' ),
                included_ids : included_ids.join(','),
                send_ajax : data_send_ajax,
                input_ref : wpc_input_ref,
                current_page : wpc_current_page
            },
            dataType: "json",
            success: function(data){
                if(data.html) {
                    link.parents('.postbox').find(".wpc_inside table tr td").html(data.html);
                    for(key in data.buttons) {
                        if( data.buttons.hasOwnProperty( key ) ) {
                            if(data.buttons[key]) {
                                link.parent().children(".wpc_pagination_links[rel="+key+"]").show();
                            } else {
                                link.parent().children(".wpc_pagination_links[rel="+key+"]").hide();
                            }
                        }
                    }
                    if(data.count > data.per_page) {
                        link.parents('.postbox').find(".wpc_page_num").html(data.page);
                    } else {
                        link.parents('.postbox').find(".wpc_page_num").html('');
                    }

                    wpc_page_num = data.page;
                    if( checkbox_session.length ) {
                        for( key in checkbox_session ) {
                            link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "][value=" + checkbox_session[ key ] + "]").prop('checked', true);
                        }
                    }
                    jQuery(".wpc_select_all_at_page").prop('checked', false);
                    if( link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]:checked").length == link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length && link.parents('.postbox').find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").length != 0 ) {
                        jQuery(".wpc_select_all_at_page").prop('checked', true);
                    }
                }
            },
            error: function(data) {
                link.parent('.postbox').find(".wpc_inside table tr td").html(data.html);
            }
        });

    });

    //Cancel Assign block
    jQuery( "body" ).on( 'click', '.wpc_select_all', function( e ) {
        jQuery(this).parents('.postbox').find( '.wpc_select_all_at_page' ).prop( 'checked', false );

        if(jQuery(this).is(":checked")) {
            checkbox_session = full_ids_list;
            jQuery(this).parents('.postbox').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', true );
        } else {
            jQuery(this).parents('.postbox').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', false );
            checkbox_session = new Array();
        }
        jQuery( this ).parents(".postbox").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
    });

    //Cancel Assign block
    jQuery( "body" ).on( 'click', ".wpc_cancel_popup", function( e ) {
        tb_remove();
    });

    //Ok Assign block
    jQuery( "body" ).on( 'click', ".wpc_ok_popup", function( e ) {
        tb_remove();
        if( data_send_ajax ) {
            jQuery.wpcShowLoading();
            var input_vars = new Array();
            for( key in additional_vars ) {
                if( jQuery('*[name=' + additional_vars[ key ] + ']').length ) {
                    if( jQuery('*[name=' + additional_vars[ key ] + ']').is(':radio') || jQuery('*[name=' + additional_vars[ key ] + ']').is(':checkbox') ) {
                        input_vars[ additional_vars[ key ] ] = jQuery('*[name=' + additional_vars[ key ] + ']').is(":checked") ? 1 : 0;
                    } else {
                        input_vars[ additional_vars[ key ] ] = jQuery('*[name=' + additional_vars[ key ] + ']').val();
                    }
                }
            }
            var data = {
                action : ( ( typeof wpc_popup_var != 'undefined' && typeof wpc_popup_var.wpc_ajax_prefix != 'undefined' ) ? ( wpc_popup_var.wpc_ajax_prefix + '_' ) : '' )+ 'update_assigned_data',
                data_type : data_type,
                id : wpc_object_id,
                data : checkbox_session.join(','),
                current_page : wpc_current_page
            };
            data = jQuery.extend({}, data, input_vars);
            jQuery.ajax({
                type: "POST",
                url: wpc_popup_var.site_url + "/wp-admin/admin-ajax.php",
                data: data,
                dataType: "json",
                success: function(data) {
                    if( data.status ) {
                        jQuery( '#' + wpc_input_ref ).val( checkbox_session.join(',') );
                        jQuery( '#' + wpc_input_ref ).trigger('change');
                        if( 'radio' == wpc_marks_type ) {
                            if( checkbox_name.length != 0 ) {
                                jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_name + ")");
                            }
                        } else {
                            jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_session.length + ")");
                        }
                    } else {
                        alert( data.message );
                    }
                    jQuery.wpcHideLoading();
                },
                error: function(data) {
                    jQuery.wpcHideLoading();
                    alert('Can not update assign data.');
                }
            });
        } else {
            jQuery( '#' + wpc_input_ref ).val( checkbox_session.join(',') );
            jQuery( '#' + wpc_input_ref ).trigger('change');
            if( 'radio' == wpc_marks_type ) {
                if( checkbox_name.length != 0 ) {
                    jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_name + ")");
                }
            } else {
                jQuery(".counter_" + wpc_input_ref).html("(" + checkbox_session.length + ")");
            }
        }

    });

    //Select/Un-select all clients at page
    jQuery( "body" ).on( 'click', ".wpc_select_all_at_page", function(e) {
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery(this).parents('.postbox').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', true );
        } else {
            jQuery(this).parents('.postbox').find( '.wpc_inside input[type="checkbox"]' ).prop( 'checked', false );
        }
        jQuery(this).parents('.postbox').find( '.wpc_inside input[type="checkbox"]' ).each(function() {
            if( jQuery(this).is(':checked') ) {
                checkbox_session.push( jQuery(this).val() );
            } else {
                key = array_search( jQuery(this).val(),checkbox_session );
                if( key !== false ) {
                    checkbox_session.splice( key, 1);
                }
            }
        });
        checkbox_session = array_unique( checkbox_session );
        jQuery(this).parents(".postbox").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
    });

    //Select/Un-select checkbox
    jQuery( "body" ).on( 'change', ' .wpc_inside input', function() {
        if( 'radio' == wpc_marks_type ) {
            checkbox_session = new Array();
            checkbox_session.push( jQuery(this).val() );
            checkbox_name = jQuery(this).data('name');
        } else {
            if( jQuery(this).is(':checked') ) {
                checkbox_session.push( jQuery(this).val() );
            } else {
                key = array_search( jQuery(this).val(),checkbox_session );
                if( key !== false ) {
                    checkbox_session.splice( key, 1);
                }
            }
            checkbox_session = array_unique( checkbox_session );
            jQuery(this).parents(".postbox").find(".wpc_popup_statistic .wpc_selected_count").html( checkbox_session.length );
            jQuery(this).parents(".postbox").find( ".wpc_select_all" ).removeAttr('checked');
            jQuery(this).parents(".postbox").find( ".wpc_select_all_at_page" ).removeAttr('checked');
        }
    });

});

jQuery.fn.extend({
    wpcResetPopup: function() {
        this.find(".wpc_search_field").val( wpc_popup_var.search_text );
        this.find(".wpc_select_all").prop('checked', false);
        this.find(".wpc_select_all_at_page").prop('checked', false);
        this.find(".wpc_pagination_links[rel=first]").hide();
        this.find(".wpc_pagination_links[rel=prev]").hide();
        this.find(".wpc_page_num").html('1');
        this.find(".wpc_inside table tr td input[type=" + wpc_marks_type + "]").removeAttr('checked');
        this.find( ".wpc_select_all_at_page" ).removeAttr('checked');
        this.find('.wpc_show option').removeAttr('selected');
        this.find('.wpc_order option').removeAttr('selected');

        if( 'radio' == wpc_marks_type ) {
            this.find('.wpc_select_all_at_page').parent().hide();
            this.find('.wpc_select_all').parent().hide();
        } else {
            this.find('.wpc_select_all_at_page').parent().show();
            this.find('.wpc_select_all').parent().show();
        }
        checkbox_session = new Array();
        checkbox_name = '';
        full_ids_list = new Array();
        wpc_page_num = 1;
        return true;
    }
});

jQuery.wpcShowLoading = function() {
    jQuery("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
    jQuery('#TB_load').show();//show loader
    return true;
};
jQuery.wpcHideLoading = function() {
    jQuery("#TB_load").remove();
    return true;
};
(function(a){a.fn.addBack=a.fn.addBack||a.fn.andSelf;
a.fn.extend({actual:function(b,l){if(!this[b]){throw'$.actual => The jQuery method "'+b+'" you called does not exist';}var f={absolute:false,clone:true,includeMargin:false};
var i=a.extend(f,l);var e=this.eq(0);var h,j;if(i.clone===true){h=function(){var m="position: absolute !important; top: -1000 !important; ";e=e.clone().attr("style",m).appendTo("body");
};j=function(){e.remove();};}else{var g=[];var d="";var c;h=function(){c=e.parents().addBack().filter(":hidden");d+="visibility: hidden !important; display: block !important; ";
if(i.absolute===true){d+="position: absolute !important; ";}c.each(function(){var m=a(this);var n=m.attr("style");g.push(n);m.attr("style",n?n+";"+d:d);
});};j=function(){c.each(function(m){var o=a(this);var n=g[m];if(n===undefined){o.removeAttr("style");}else{o.attr("style",n);}});};}h();var k=/(outer)/.test(b)?e[b](i.includeMargin):e[b]();
j();return k;}});})(jQuery);

function array_unique( inArr ){
    var uniHash={}, outArr=[], i=inArr.length;
    while( i-- ) uniHash[ inArr[ i ] ]=i;
    for( i in uniHash ) outArr.push( i );
    return outArr;
}

function array_search( needle, haystack, strict ) {
    var strict = !!strict;
    for(var key in haystack){
        if( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) && haystack.hasOwnProperty( key ) ){
            return key;
        }
    }
    return false;
}

function wpc_assign_popup_position() {
    var isIE6 = typeof document.body.style.maxHeight === "undefined";
    jQuery("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
    if ( ! isIE6 ) { // take away IE6
        jQuery("#TB_window").css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px'});
        jQuery("#TB_window").css({top: '50%', height: 'auto'});
    }
}