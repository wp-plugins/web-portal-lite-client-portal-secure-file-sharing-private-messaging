<?php

define( 'IFRAME_REQUEST', true );
//wp_enqueue_script( 'jquery-ui-accordion' );

wp_enqueue_script( 'jquery-ui-spinner' );

wp_register_style( 'wpc-jqueryui', $this->plugin_url . 'css/jqueryui/jquery-ui-1.10.3.css' );
wp_enqueue_style( 'wpc-jqueryui' );



wp_enqueue_script( 'accordion' );
wp_enqueue_style( 'customize-controls' );

wp_enqueue_script( 'wp-color-picker' );
wp_enqueue_style( 'wp-color-picker' );


include $this->plugin_dir . '/includes/class.customize.php';

$wpc_client_customize = new WPC_Client_Customize();

//save settings
if ( isset( $_POST['wpc_scheme_settings'] ) && isset( $_POST['wpc_style_settings'] ) && is_array( $_POST['wpc_style_settings'] ) ) {

    $wpc_client_customize->save_style_settings( $_POST['wpc_scheme_settings'], $_POST['wpc_style_settings'] );

    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_customize&msg=u' );
    exit;
}


//reset settings
if ( isset( $_GET['reset'] ) && 'true' == $_GET['reset']  ) {
    $wpc_client_customize->reset_style_settings();

    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_customize&msg=r' );
    exit;
}


$args = array(
    'role'          => 'wpc_client',
    'meta_key'      => 'wpc_cl_hubpage_id',
    'fields'        => 'ID',
    'number'        => 1,
);
$client = get_users( $args );


if ( isset( $client[0] ) ) {
    $hub_id = get_user_meta( $client[0], 'wpc_cl_hubpage_id', true );

    //make link
    if ( $this->permalinks ) {
        $iframe_url = $this->cc_get_slug( 'hub_page_id' ) . $hub_id;
    } else {
        $iframe_url = add_query_arg( array( 'wpc_page' => 'hub_preview', 'wpc_page_value' => $hub_id ), $this->cc_get_slug( 'hub_page_id', false ) );
    }
} else {
    $iframe_url = get_home_url();
}



$style_schemes = $wpc_client_customize->get_style_schemes();
$style_schemes_keys = array_keys( $style_schemes );

?>

<div class="ajax_big_loading">
    <span class="loading_text"><?php _e( 'Launching Customizer...', WPC_CLIENT_TEXT_DOMAIN ) ?></span>
</div>

<div class='wrap' >

    <div id="wpc_customize_block" >


        <form id="wpc_customize_controls" class="wrap wp-full-overlay-sidebar" style="top: 28px" method="post" action="">

            <div id="customize-header-actions" class="wp-full-overlay-header">
                <input type="button" value="Save" class="button button-primary" id="wpc_save_scheme_settings" name="save">
                <span class="spinner"></span>
                <a class="back button" href="<?php echo admin_url( 'admin.php?page=wpclients_templates' ) ?>"><?php _e( 'Cancel', WPC_CLIENT_TEXT_DOMAIN ) ?></a>
<!--                <a class="back button" onclick='return confirm("<?php _e( 'Are you sure to reset Custom Styles?', WPC_CLIENT_TEXT_DOMAIN ) ?>");' href="<?php echo admin_url( 'admin.php?page=wpclients_customize&reset=true' ) ?>"><?php _e( 'Reset', WPC_CLIENT_TEXT_DOMAIN ) ?></a>-->
            </div>

            <div tabindex="-1" class="wp-full-overlay-sidebar-content accordion-container">

                <div id="properties_editor" style="display: none;" ><?php echo $wpc_client_customize->_editor_html() ?></div>


                <div class="accordion-section" id="customize-info">
                    <div tabindex="0" aria-label="Theme Customizer Options" class="accordion-section-title">
                        <span class="preview-notice"><?php _e( 'You are customizing style scheme:', WPC_CLIENT_TEXT_DOMAIN ) ?> <strong class="theme-name"><?php echo ( isset( $style_schemes[$style_schemes_keys[0]] ) ) ? $style_schemes[$style_schemes_keys[0]]['title'] : '' ?></strong></span>
                    </div>
                    <div class="accordion-section-content" style="display: none; padding: 0px 0px 15px 20px;">
                        <b><?php _e( 'Scheme', WPC_CLIENT_TEXT_DOMAIN ) ?>:</b>
                        <input type="hidden" name="wpc_scheme_settings[title]" id="scheme_title" value="" >
                        <select id="select_scheme" name="wpc_scheme_settings[key]" >
                            <?php

                            foreach( $style_schemes as $key => $value ) {
                                echo '<option value="' . $key . '">' . $value['title'] . '</option>';
                            }
                             ?>
                        </select>
                        <span id="wpc_edit_scheme"></span>
                        <span id="wpc_scheme_buttons">
                            <span class="wpc_customize_button_add" id="wpc_scheme_add" title="<?php _e( 'Add Section', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_customize_button_save" id="wpc_scheme_save" style="display: none;" title="<?php _e( 'Save Section', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_customize_button_cancel" id="wpc_scheme_cancel" style="display: none;" title="<?php _e( 'Cancel Edit', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_customize_button_edit" id="wpc_scheme_edit" title="<?php _e( 'Edit Section', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_customize_button_delete" id="wpc_scheme_delete" title="<?php _e( 'Delete Section', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                        </span>
                        <span id="ajax_result"></span>
                    </div>

                    <div id="theme_header">
                        <?php
                        $first_scheme_key = array_keys( $style_schemes );

                        if ( isset( $first_scheme_key[0] ) ) {
                             echo $wpc_client_customize->get_sections_header( $first_scheme_key[0] );
                        }

                        ?>
                    </div>

                </div>


                <div id="customize-theme-controls">
                    <?php
                        echo $wpc_client_customize->get_sections( '_default_scheme' );
                    ?>
                </div>

                <hr />

                <div style="width: 100%; text-align: center;">
                    <label style="margin-left: 20px;"><?php _e( 'New Element Title', WPC_CLIENT_TEXT_DOMAIN ) ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="text" id="wpc_add_element_title" value="" />
                    </label>
                    <br>
                    <label style="margin-left: 20px;"><?php _e( 'New Element CSS ID', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                        <input type="text" id="wpc_add_element_css_id" value="" />
                    </label>
                    <br>
                </div>

                <input type="button" class="button button-primary" id="wpc_customize_add_element" value="<?php _e( 'Add Element', WPC_CLIENT_TEXT_DOMAIN ) ?>" />
            </div>

        </form>

        <div id="element_html" style="display: none;"><?php echo $wpc_client_customize->_element_html() ?></div>

        <div id="preview_block" class="wp-full-overlay-main">
            <iframe id="my_preview" src="<?php echo $iframe_url ?>" width="100%" height="100%"></iframe>
        </div>

        <script type="text/javascript">

            var site_url = '<?php echo get_admin_url();?>';
            var eID = '';

            //preparing page content
            jQuery( '#adminmenuback' ).remove();
            jQuery( '#adminmenuwrap' ).remove();

            jQuery( '#wpcontent' ).css( 'margin-left', '300px' );

            jQuery( document ).ready( function() {

                /*
                * size of iframe
                */
                var screenheight = jQuery( window ).height();
                var screenwidth = jQuery( window ).width();

                jQuery( '#my_preview' ).css( 'width', screenwidth - 300 );
                jQuery( '#my_preview' ).css( 'height', screenheight - 28 );

                jQuery( document ).resize( function() {
                    screenheight = jQuery( window ).height();
                    screenwidth = jQuery( window ).width();

                    jQuery( '#my_preview' ).css( 'width', screenwidth - 300 );
                    jQuery( '#my_preview' ).css( 'height', screenheight - 28 );

                });


                //remove admin footer
                jQuery( '#wpwrap' ).css( 'width', 0 );
                jQuery( '#wpwrap' ).css( 'height', 0 );
                jQuery( '#wpfooter' ).remove();
                jQuery( '.message' ).remove();


                jQuery( '#select_sections' ).live( 'change', function() {
                    //hide editor block
                    jQuery( '#properties_editor' ).hide();

                    jQuery( '.section_block' ).hide();
//                    jQuery( '.theme-name' ).html( jQuery( '#select_sections option:selected' ).text() );
                    jQuery( '#section-' + jQuery( this ).val() ).show();

                });

                //changes in iframe
                jQuery('iframe#my_preview').load(function(){
                    var myiframe = jQuery( 'iframe#my_preview' ).contents();
                    jQuery( '.ajax_big_loading' ).fadeOut(600, function(){ jQuery(this).remove();});
                    jQuery( 'iframe#my_preview' ).contents().find( 'body' ).append( '<div id="wpc_backlight_block" style="display: none; left: 0px; top: 0px; width: 100px; height: 100px; border: solid #9DD6ED 2px; position: absolute; z-index: 999999999999999999999999999999999; background: transparent url(<?php echo $this->plugin_url . '/images/customizer_backlight_block.png' ?>) repeat" ></div>' );

                    jQuery( this ).wpc_style_to_iframe();


                   // jQuery( 'h3.accordion-section-title' ).mouseover( function() {
//
//                        var id = jQuery( this ).closest( 'li' ).attr( 'data-eID');
//                        var css_id = jQuery( '#' + id + '_css_id' ).val();
//                        var element = jQuery( myiframe ).find( css_id + ':visible' );
//
//
//                        if ( element.length || 0 < element.width() ) {
//
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).show();
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'border-width', '2px' );
//
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'left', element.offset().left + 'px' );
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'top', element.offset().top + 'px' );
//
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'width', element.width() + 'px' );
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'height', element.height() + 'px' );
//
//                        } else {
//                            jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'border-width', '0px' );
//                        }
//
//                    });
//
//                    jQuery( 'h3.accordion-section-title' ).mouseout(function(){
//                        jQuery( myiframe ).find( '#wpc_backlight_block' ).hide();
//                        jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'width', '0px' );
//                        jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'border-width', '0px' );
//                        jQuery( myiframe ).find( '#wpc_backlight_block' ).css( 'height', '0px' );
//                    });
//
//


                });





                jQuery( '#select_scheme' ).change( function() {

                    //hide editor block
                    jQuery( '#properties_editor' ).hide();

                    jQuery( '.theme-name' ).html( jQuery( '#select_scheme option:selected' ).text() );

                    jQuery( '#scheme_title' ).val( jQuery( '#select_scheme option:selected' ).text() );

                    jQuery( 'iframe#my_preview' )[0].contentWindow.location.reload(true);

                    var scheme = jQuery( this ).val();

//                    jQuery( '#customize-theme-controls' ).html( '' );
                    jQuery( '#customize-theme-controls' ).hide();
//                    jQuery( '#theme_header' ).html( '' );
                    jQuery( '#theme_header' ).hide();

//                    jQuery( "#ajax_result" ).show();
//                    jQuery( "#ajax_result" ).css( 'display', 'inline' );


                    jQuery( "#wpc_scheme_buttons" ).hide();
                    jQuery( "#ajax_result" ).html( '<span class="wpc_ajax_loading"></span>' );
//

                    jQuery.ajax({
                        type: "POST",
                        url: site_url+"admin-ajax.php",
                        data: "action=wpc_customizer_get_sections&wpc_scheme=" + scheme,
                        dataType: "json",
                        success: function( data ){

                            jQuery( "#ajax_result" ).html( '' );
                            jQuery( "#wpc_scheme_buttons" ).show();
//                            jQuery( ".accordion-section-content" ).hide();


                            if ( data.status ) {
                                jQuery( '#customize-theme-controls' ).html( jQuery( this ).get_sections( data.content ) );

                                jQuery( '#theme_header' ).html( data.sections_header_content );
                                jQuery( '#customize-theme-controls' ).show();
                                jQuery( '#theme_header' ).show();


//                jQuery( '.h3.accordion-section-title' ).accordion('resize');

//                                jQuery( "#ajax_result" ).css( 'color', 'green' );
                            } else {
//                                jQuery( "#ajax_result" ).css( 'color', 'red' );
                            }
                        },
                        error: function( data ) {
                            alert('error');
                        }
                    });


                });


                //add section
                jQuery( '#wpc_section_add' ).live( 'click', function() {
                    jQuery( '#select_sections' ).hide();
                    jQuery( '#wpc_section_add' ).hide();
                    jQuery( '#wpc_section_edit' ).hide();
                    jQuery( '#wpc_section_delete' ).hide();
                    jQuery( '#wpc_section_save' ).show();
                    jQuery( '#wpc_section_cancel' ).show();

                    var html = '<input type="text" id="wpc_edit_section_name" value="" />';
                    html += '<input type="hidden" id="wpc_edit_section_id" value="" />';

                    jQuery( '#wpc_edit_section' ).html( html );

                });

                //edit section
                jQuery( '#wpc_section_edit' ).live( 'click', function() {
                    var section_id = jQuery( '#select_sections' ).val();
                    var section_name = jQuery( '#select_sections option:selected' ).text();

                    jQuery( '#select_sections' ).hide();
                    jQuery( '#wpc_section_add' ).hide();
                    jQuery( '#wpc_section_edit' ).hide();
                    jQuery( '#wpc_section_delete' ).hide();
                    jQuery( '#wpc_section_save' ).show();
                    jQuery( '#wpc_section_cancel' ).show();

                    var html = '<input type="text" id="wpc_edit_section_name" value="' + section_name + '" />';
                    html += '<input type="hidden" id="wpc_edit_section_id" value="' + section_id + '" />';

                    jQuery( '#wpc_edit_section' ).html( html );

                });


                //save section
                jQuery( '#wpc_section_save' ).live( 'click', function() {
                    var section_id = jQuery( '#wpc_edit_section_id' ).val();
                    var section_name = jQuery( '#wpc_edit_section_name' ).val();

                    if ( '' != section_name && ' ' != section_name ) {

                        if ( '' != section_id ) {
                            jQuery( '#select_sections option:selected' ).text( section_name );
                        } else {
                            section_id = section_name;
                            section_id = section_id.replace(  /\s/g, '_' );
                            section_id = section_id.replace(  /\W/g, '_' );
                            section_id = section_id.replace(  /[_]{2,}/g, '_' );

                            jQuery( '#select_sections ' ).append( jQuery( '<option></option>' ).attr( 'value', section_id ).text( section_name ) );

                            jQuery('#select_sections option[value=' + section_id + ']').attr( 'selected', 'selected' );

                            var html = '<ul id="section-' + section_id + '" class="section_block"></ul>';

                            jQuery( '#customize-theme-controls ul' ).hide();

                            jQuery( '#customize-theme-controls' ).append( html );



                        }

                        jQuery( '#select_sections' ).show();
                        jQuery( '#wpc_section_add' ).show();
                        jQuery( '#wpc_section_edit' ).show();
                        jQuery( '#wpc_section_delete' ).show();
                        jQuery( '#wpc_section_save' ).hide();
                        jQuery( '#wpc_section_cancel' ).hide();
                        jQuery( '#wpc_edit_section' ).html( '' );

                    }


                });

                //cancel edit section
                jQuery( '#wpc_section_cancel' ).live( 'click', function() {
                    jQuery( '#select_sections' ).show();
                    jQuery( '#wpc_section_add' ).show();
                    jQuery( '#wpc_section_edit' ).show();
                    jQuery( '#wpc_section_delete' ).show();
                    jQuery( '#wpc_section_save' ).hide();
                    jQuery( '#wpc_section_cancel' ).hide();

                    jQuery( '#wpc_edit_section' ).html( '' );

                });


                //delete section
                jQuery( '#wpc_section_delete' ).live( 'click', function() {
                    var section_id = jQuery( '#select_sections' ).val();

                    jQuery( '#select_sections option:selected' ).remove();
                    jQuery( '#section-' + section_id ).remove();

                    var new_section_id = jQuery( '#select_sections' ).val();

                    if ( null != new_section_id ) {
                        jQuery( '#section-' + new_section_id ).show();
                    }

                });



                //add scheme
                jQuery( '#wpc_scheme_add' ).live( 'click', function() {
                    jQuery( '#select_scheme' ).hide();
                    jQuery( '#wpc_scheme_add' ).hide();
                    jQuery( '#wpc_scheme_edit' ).hide();
                    jQuery( '#wpc_scheme_delete' ).hide();
                    jQuery( '#wpc_scheme_save' ).show();
                    jQuery( '#wpc_scheme_cancel' ).show();

                    var html = '<input type="text" id="wpc_edit_scheme_name" value="" />';
                    html += '<input type="hidden" id="wpc_edit_scheme_id" value="" />';

                    jQuery( '#wpc_edit_scheme' ).html( html );

                });

                //edit scheme
                jQuery( '#wpc_scheme_edit' ).live( 'click', function() {
                    var scheme_id = jQuery( '#select_scheme' ).val();
                    var scheme_name = jQuery( '#select_scheme option:selected' ).text();

                    jQuery( '#select_scheme' ).hide();
                    jQuery( '#wpc_scheme_add' ).hide();
                    jQuery( '#wpc_scheme_edit' ).hide();
                    jQuery( '#wpc_scheme_delete' ).hide();
                    jQuery( '#wpc_scheme_save' ).show();
                    jQuery( '#wpc_scheme_cancel' ).show();

                    var html = '<input type="text" id="wpc_edit_scheme_name" value="' + scheme_name + '" />';
                    html += '<input type="hidden" id="wpc_edit_scheme_id" value="' + scheme_id + '" />';

                    jQuery( '#wpc_edit_scheme' ).html( html );

                });


                //save scheme
                jQuery( '#wpc_scheme_save' ).live( 'click', function() {
                    var scheme_id = jQuery( '#wpc_edit_scheme_id' ).val();
                    var scheme_name = jQuery( '#wpc_edit_scheme_name' ).val();

                    if ( '' != scheme_name && ' ' != scheme_name ) {

                        if ( '' != scheme_id ) {
                            jQuery( '#scheme_title' ).val( scheme_name );
                            jQuery( '.theme-name' ).html( scheme_name );
                            jQuery( '#select_scheme option:selected' ).text( scheme_name );
                        } else {
                            jQuery( 'iframe#my_preview' )[0].contentWindow.location.reload(true);

                            scheme_id = scheme_name;
                            scheme_id = scheme_id.replace(  /\s/g, '_' );
                            scheme_id = scheme_id.replace(  /\W/g, '_' );
                            scheme_id = scheme_id.replace(  /[_]{2,}/g, '_' );

                            jQuery( '#scheme_title' ).val( scheme_name );
                            jQuery( '#select_scheme' ).append( jQuery( '<option></option>' ).attr( 'value', scheme_id ).text( scheme_name ) );

                            jQuery('#select_scheme option[value=' + scheme_id + ']').attr( 'selected', 'selected' );

                            jQuery( '#select_sections' ).html( '' );

//                            var html = '<ul id="scheme-' + scheme_id + '" class="scheme_block"></ul>';


//                            jQuery( '#customize-theme-controls ul' ).hide();

//                            jQuery( '#customize-theme-controls' ).append( html );
                            jQuery( '#customize-theme-controls' ).html( '' );

                            jQuery( '.theme-name' ).html( jQuery( '#select_scheme option:selected' ).text() );



                        }

                        jQuery( '#select_scheme' ).show();
                        jQuery( '#wpc_scheme_add' ).show();
                        jQuery( '#wpc_scheme_edit' ).show();
                        jQuery( '#wpc_scheme_delete' ).show();
                        jQuery( '#wpc_scheme_save' ).hide();
                        jQuery( '#wpc_scheme_cancel' ).hide();
                        jQuery( '#wpc_edit_scheme' ).html( '' );

                    }


                });

                //cancel edit scheme
                jQuery( '#wpc_scheme_cancel' ).live( 'click', function() {
                    jQuery( '#select_scheme' ).show();
                    jQuery( '#wpc_scheme_add' ).show();
                    jQuery( '#wpc_scheme_edit' ).show();
                    jQuery( '#wpc_scheme_delete' ).show();
                    jQuery( '#wpc_scheme_save' ).hide();
                    jQuery( '#wpc_scheme_cancel' ).hide();

                    jQuery( '#wpc_edit_scheme' ).html( '' );

                });


                //delete scheme
                jQuery( '#wpc_scheme_delete' ).live( 'click', function() {
                    jQuery( '#select_scheme option:selected' ).remove();
                    jQuery( '#customize-theme-controls' ).html( '' );
                });



                //add element
                jQuery( '#wpc_customize_add_element' ).live( 'click', function() {

                    var title = jQuery( '#wpc_add_element_title' ).val();
                    var css_id = jQuery( '#wpc_add_element_css_id' ).val();

                    if ( '' != title && '' != css_id ) {

                        var html = '';

                        var objSettings = new Object();
                        objSettings.title = title;
                        objSettings.css_id = css_id;

                        jQuery( '#wpc_add_element_css_id' ).val();

                        var section_id = jQuery( '#select_sections' ).val();

                        html = jQuery( this ).get_element( section_id, '', objSettings );

                        jQuery( '#customize-theme-controls .section_block:visible' ).append( html );

                        jQuery( '#wpc_add_element_title' ).val( '' );
                        jQuery( '#wpc_add_element_css_id' ).val( '' );
                    }

                });


                //edit element title
                jQuery( '#wpc_element_title_edit' ).live( 'click', function() {
                    var element_title = jQuery( '#wpc_editor_element_title' ).html();

                    jQuery( '#wpc_editor_element_title' ).hide();
                    jQuery( '#wpc_element_title_edit' ).hide();
                    jQuery( '#wpc_element_title_save' ).show();
                    jQuery( '#wpc_element_title_cancel' ).show();

                    var html = '<input type="text" class="wpc_edit_element_title" id="wpc_field_element_title" value="' + element_title + '" />';

                    jQuery( '#wpc_edit_element_title' ).html( html );

                });


                //save element title
                jQuery( '#wpc_element_title_save' ).live( 'click', function() {

                    var element_title = jQuery( '#wpc_field_element_title' ).val();

                    if ( '' != element_title && ' ' != element_title ) {

                        jQuery( '#wpc_editor_element_title' ).html( element_title );
                        jQuery( '#h3_element_title_' + eID ).html( element_title );
                        jQuery( '#' + eID + '_title' ).val( element_title );

                        jQuery( '#wpc_edit_element_title' ).html( '' );
                        jQuery( '#wpc_editor_element_title' ).show();
                        jQuery( '#wpc_element_title_edit' ).show();
                        jQuery( '#wpc_element_title_save' ).hide();
                        jQuery( '#wpc_element_title_cancel' ).hide();

                    }

                });


                //cancel edit element title
                jQuery( '.wpc_element_title_button_cancel' ).live( 'click', function() {

                    jQuery( '#wpc_editor_element_title' ).show();
                    jQuery( '#wpc_element_title_edit' ).show();
                    jQuery( '#wpc_element_title_save' ).hide();
                    jQuery( '#wpc_element_title_cancel' ).hide();

                    jQuery( '#wpc_edit_element_title' ).html( '' );

                });


                //delete element
                jQuery( '.wpc_element_button_delete' ).live( 'click', function() {
                    jQuery( this ).closest( 'li' ).remove();
                });


                //show editor
                jQuery( '.section_block .control-section' ).live( 'click', function() {
                    eID = jQuery( this ).attr( 'data-eID' );

                    if ( jQuery( this ).hasClass( 'open' ) ) {
                        jQuery( '#properties_editor' ).css( 'left',  jQuery( this ).offset().left + 'px' );
                        jQuery( '#properties_editor' ).css( 'top', ( jQuery( this ).offset().top - 42 ) + 'px' );
                        jQuery( this ).editor_set_values( eID );
                        jQuery( '#properties_editor' ).show();
                    } else {
                        jQuery( '#properties_editor' ).hide();
                        jQuery( this ).editor_get_values( eID );
                        eID = '';
                    }
                });



//                jQuery( '.section_block .open' ).live( 'click', function() {
//                    jQuery( '#properties_editor' ).hide();
//                });

//                jQuery( '#properties_editor' ).hide();



                //save scheme
                jQuery( '#wpc_save_scheme_settings' ).live( 'click', function() {
                    jQuery( this ).editor_get_values( eID );
                    eID = '';
                    jQuery( '#wpc_customize_controls' ).submit();
                });






            });
        </script>










<script type="text/javascript">

                    jQuery( document ).ready( function() {

                        var element_html = jQuery( '#element_html' ).html();
                        var element_id = '<?php echo time() ?>';


                        var allProperties2 = new Array(
                            'background-color',
                            'font-size',
                            'color'
                         );

                         var allProperties = {
                             <?php
                                $i = 1;
                                $count = count( $wpc_client_customize->all_properties );
                                foreach( $wpc_client_customize->all_properties as $key => $type ) {
                                    echo ( $i != $count ) ? "'{$key}' : '{$type}'," : "'{$key}' : '{$type}'";
                                    $i++;
                                }
                             ?>
                             <?php ?>
                         };


                        jQuery.fn.wpc_style_to_iframe = function() {

                            jQuery( '#customize-theme-controls .control-section' ).each( function() {

                                var id = jQuery( this ).attr( 'data-eID' );
                                var css_id = jQuery( '#' + id + '_css_id' ).val();

                                jQuery.each( allProperties, function( key, type  ) {
                                    var value = jQuery( '#' + id + '_' + key ).val();

                                    if ( '' != value && '!no' != value ) {
                                        jQuery( 'iframe#my_preview' ).contents().find( css_id ).css( key, value );
                                    }
                                });

                            });
                        }


                        jQuery.fn.editor_get_values = function( element_key ) {
                            jQuery.each( allProperties, function( key, type  ) {

                                if ( 'color' == type ) {
                                    jQuery( '#' + element_key + '_' + key ).val( jQuery( '#editor_' + key ).wpColorPicker( 'color' ) );

                                } else if ( 'size' == type ) {
                                    jQuery( '#' + element_key + '_' + key ).val( jQuery( '#editor_' + key ).val() );
                                    jQuery( '#' + element_key + '_' + key + '-unit' ).val( jQuery( '#editor_' + key + '-unit' ).val() );

                                } else {
                                    jQuery( '#' + element_key + '_' + key ).val( jQuery( '#editor_' + key ).val() );
                                }

                            });
                        }


                        jQuery.fn.editor_set_values = function( element_key ) {

                            jQuery( '#wpc_editor_element_title' ).html( jQuery( '#' + element_key + '_title' ).val() );
                            jQuery( '#wpc_editor_element_css_id' ).html( jQuery( '#' + element_key + '_css_id' ).val() );

                            jQuery.each( allProperties, function( key, type  ) {
                                 var value = ''
//
                                //update color picker value
                                if ( 'color' == type ) {

                                    value = jQuery( '#' + element_key + '_' + key ).val();

                                    if ( '' == value ) {
                                         jQuery( '#editor_' + key ).wpColorPicker().val( '' );
                                         jQuery( '#editor_' + key ).closest( '.wp-picker-container' ).children( '.wp-color-result' ).attr( 'style', '' )
                                    } else {
                                        jQuery( '#editor_' + key ).wpColorPicker( 'color', value );
                                    }
                                } else if ( 'size' == type ) {
                                    jQuery( '#editor_' + key ).val( jQuery( '#' + element_key + '_' + key ).val() );
                                    jQuery( '#editor_' + key + '-unit' ).val( jQuery( '#' + element_key + '_' + key + '-unit' ).val() );

                                } else {
                                    jQuery( '#editor_' + key ).val( jQuery( '#' + element_key + '_' + key ).val() );
                                }

                            });
                        }




                        jQuery.fn.get_element = function( section_key, element_key, obj_element ) {

                            var html = '';

                            if ( typeof obj_element =='object' ) {

                                html = element_html;

                                if ( '' == element_key )
                                    element_key = 'el_' + element_id;

//                                jQuery.each( obj_element, function( key, value ) {

                                    html = html.replace( /{section_key}/g, section_key );
                                    html = html.replace( /{element_key}/g, element_key );
                                    html = html.replace( /{form_name}/g, 'wpc_style_settings[' + section_key + '][' + element_key + '][properties]');
                                    html = html.replace( /{title}/g, obj_element.title );
                                    html = html.replace( /{css_id}/g, obj_element.css_id );
                                    html = html.replace( /{element_id}/g, 'el_' + element_id );


                                    //replace element properties
                                    if ( typeof obj_element.properties == 'object' ) {
                                        jQuery.each( obj_element.properties, function( property_key, property_value ) {
                                            var value = '';
                                            if ( '' != property_value && '!no' != property_value ) {
                                                value = property_value;
                                            }

                                            html = html.replace(  '{value_' + property_key + '}', property_value );

//                                            jQuery( 'iframe#my_preview' ).contents().find( obj_element.css_id ).css( property_key, property_value );

//                                            var pattern = new RegExp( '{title}', 'g' );
//                                    html = html.replace( /{title}/, obj_settings.title );
                                        });

                                    }

                                    //remove blank values
                                    jQuery.each( allProperties, function( key, type  ) {
                                        html = html.replace(  '{value_' + key + '}', '' );
                                    });

                                    element_id = element_id * 1 + 1;


//                                });

                            }
                            //
//
//                            if ( settings.element_title ) {
//                                html = html.replace( '{element_title}', settings.element_title );
//                            } else {
//                                html = html.replace( /{element_title}/g, 'Custom Title 1' );
//                            }
//
//                            if ( settings.element_id ) {
//                                html = html.replace( '{element_id}', settings.element_id );
//                            } else {
//                                html = html.replace( /{element_id}/g, 'custom_title_1' );
//                            }
//
//                            if ( settings.section_name ) {
//                                html = html.replace( '{section_name}', settings.section_name );
//                            } else {
//                                html = html.replace( /{section_name}/g, 'section_name' );
//                            }
//
//                            if ( settings.element_name ) {
//                                html = html.replace( '{element_name}', settings.element_name );
//                            } else {
//                                html = html.replace( /{element_name}/g, 'element_name' );
//                            }
//
//                            if ( settings.element_css_id ) {
//                                html = html.replace( '{element_css_id}', settings.element_css_id );
//                            } else {
//                                html = html.replace( /{element_css_id}/g, 'element_css_id' );
//                            }
//

                            return html;

                        }


                        jQuery.fn.get_sections = function( obj_sections ) {
                            var html = '';
                            var i = 1;

                            if ( typeof obj_sections =='object' ) {
                                jQuery.each( obj_sections, function( section_key, section_values ) {
                                    if ( 1 == i ) {
                                        html = html + '<ul class="section_block" id="section-' + section_key + '">';
                                    } else {
                                        html = html + '<ul class="section_block" id="section-' + section_key + '" style="display: none;" >';
                                    }

                                    i = 0;

                                    jQuery.each( section_values, function( element_key, element_values ) {

                                        html = html + jQuery( this ).get_element( section_key, element_key, element_values );

                                    });
                                    html = html + '</ul>';
                                });

                            }


                            return html;

                        }


                        jQuery('.wpc_colors').wpColorPicker({
                            change: function( event, ui ) {
                                var type = jQuery( this ).attr( 'data-color-type' );
                                var css_id = jQuery( '#' + eID + '_css_id' ).val();

                                if ( 'background' == type ) {
                                    jQuery('iframe#my_preview').contents().find( css_id ).css( 'background-color', jQuery( this ).wpColorPicker( 'color' ) );

                                } else if ( 'border' == type ) {
                                    jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-color', jQuery( this ).wpColorPicker( 'color' ) );

                                } else if ( 'font' == type ) {
                                    jQuery('iframe#my_preview').contents().find( css_id ).css( 'color', jQuery( this ).wpColorPicker( 'color' ) );
                                }

                            }
                        });


                        jQuery( '.wpc_spinner' ).each(function() {
                            jQuery( this ).spinner({
//                                min: 1,
                                numberFormat: "n",
                                stop: function( event, ui ) {
                                    var type    = jQuery( this ).attr( 'data-sType' );
                                    var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                                    //margin
                                    if ( 'm' == type ) {
                                        var m_top       = jQuery( '#editor_margin-top' ).val();
                                        var m_right     = jQuery( '#editor_margin-right' ).val();
                                        var m_bottom    = jQuery( '#editor_margin-bottom' ).val();
                                        var m_left      = jQuery( '#editor_margin-left' ).val();

                                        if ( '' == m_top || '!no' == m_top ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-top', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-top', m_top + 'px' );
                                        }

                                        if ( '' == m_right || '!no' == m_right ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-right', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-right', m_right + 'px' );
                                        }

                                        if ( '' == m_bottom || '!no' == m_bottom ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-bottom', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-bottom', m_bottom + 'px' );
                                        }

                                        if ( '' == m_left || '!no' == m_left ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-left', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'margin-left', m_left + 'px' );
                                        }

                                    //padding
                                    } else if ( 'p' == type ) {
                                        var p_top       = jQuery( '#editor_padding-top' ).val();
                                        var p_right     = jQuery( '#editor_padding-right' ).val();
                                        var p_bottom    = jQuery( '#editor_padding-bottom' ).val();
                                        var p_left      = jQuery( '#editor_padding-left' ).val();

                                        if ( '' == p_top || '!no' == p_top ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-top', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-top', p_top + 'px' );
                                        }

                                        if ( '' == p_right || '!no' == p_right ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-right', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-right', p_right + 'px' );
                                        }

                                        if ( '' == p_bottom || '!no' == p_bottom ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-bottom', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-bottom', p_bottom + 'px' );
                                        }

                                        if ( '' == p_left || '!no' == p_left ) {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-left', '' );
                                        } else {
                                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'padding-left', p_left + 'px' );
                                        }

                                    }
                                }
                            });
                        });

                        jQuery( '.wpc_spinner_border' ).each(function() {
                            jQuery( this ).spinner({
                                min: 0,
                                numberFormat: "n",
                                stop: function( event, ui ) {
                                    var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                                    var b_top       = jQuery( '#editor_border-top-width' ).val();
                                    var b_right     = jQuery( '#editor_border-right-width' ).val();
                                    var b_bottom    = jQuery( '#editor_border-bottom-width' ).val();
                                    var b_left      = jQuery( '#editor_border-left-width' ).val();

                                    if ( '' == b_top || '!no' == b_top ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-top-width', '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-top-width', b_top + 'px' );
                                    }

                                    if ( '' == b_right || '!no' == b_right ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-right-width', '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-right-width', b_right + 'px' );
                                    }

                                    if ( '' == b_bottom || '!no' == b_bottom ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-bottom-width', '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-bottom-width', b_bottom + 'px' );
                                    }

                                    if ( '' == b_left || '!no' == b_left ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-left-width', '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-left-width', b_left + 'px' );
                                    }

                                    jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-style', jQuery( '#editor_border-style' ).val() );

                                }
                            });
                        });

                        jQuery( '.wpc_spinner_font' ).each(function() {
                            jQuery( this ).spinner({
                                min: 0,
                                numberFormat: "n",
                                stop: function( event, ui ) {
                                    var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                                    var size = jQuery( '#editor_font-size' ).val();
                                    var unit = jQuery( '#editor_font-size-unit' ).val();

                                    if ( '' == size || '!no' == size || '' == unit || '!no' == unit ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'font-size', '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( 'font-size', size + unit );
                                    }

                                }
                            });
                        });


                        jQuery( '.wpc_border_style' ).change( function() {
                            var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                            jQuery('iframe#my_preview').contents().find( css_id ).css( 'border-style', jQuery( this ).val() );
                        });


                        jQuery( '.wpc_font_family' ).change( function() {
                            var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                            if ( '' == jQuery( this ).val() || '!no' == jQuery( this ).val() ) {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'font-family', '' );
                            } else {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'font-family', jQuery( this ).val() );
                            }

                        });

                        //display
                        jQuery( '#editor_display' ).change( function() {
                            var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                            if ( '' == jQuery( this ).val() || '!no' == jQuery( this ).val() ) {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'display', '' );
                            } else {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'display', jQuery( this ).val() );
                            }

                        });

                        //float
                        jQuery( '#editor_float' ).change( function() {
                            var css_id  = jQuery( '#' + eID + '_css_id' ).val();

                            if ( '' == jQuery( this ).val() || '!no' == jQuery( this ).val() ) {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'float', '' );
                            } else {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( 'float', jQuery( this ).val() );
                            }

                        });


                        //some sizies
                        jQuery( '.wpc_spinner_size' ).each(function() {
                            jQuery( this ).spinner({
                                min: 0,
                                numberFormat: "n",
                                stop: function( event, ui ) {
                                    var css_id  = jQuery( '#' + eID + '_css_id' ).val();
                                    var type    = jQuery( this ).attr( 'data-sType' );
                                    var size    = jQuery( this ).val();
                                    var unit    = jQuery( '#editor_' + type + '-unit' ).val();

                                    if ( '' == size || '!no' == size ) {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( type, '' );
                                    } else {
                                        jQuery('iframe#my_preview').contents().find( css_id ).css( type, size + unit );
                                    }

                                }
                            });
                        });


                        //size units
                        jQuery( '.wpc_size_unit' ).change( function() {
                            var css_id  = jQuery( '#' + eID + '_css_id' ).val();
                            var type    = jQuery( this ).attr( 'data-sType' );
                            var size    = jQuery( '#editor_' + type ).val();
                            var unit    = jQuery( this ).val();

                            if ( '' == size || '!no' == size || '' == unit || '!no' == unit ) {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( type, '' );
                            } else {
                                jQuery('iframe#my_preview').contents().find( css_id ).css( type, size + unit );
                            }

                        });




                    });

                </script>




    </div>
</div>