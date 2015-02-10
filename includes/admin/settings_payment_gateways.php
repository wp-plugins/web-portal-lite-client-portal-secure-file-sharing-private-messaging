<?php
global $wpdb, $wpc_gateway_plugins, $wpc_gateway_active_plugins,  $wpc_client;

$wpc_gateways = $wpc_client->cc_get_settings( 'gateways' );

//save settings
if ( isset( $_POST['gateway_settings'] ) ) {

    //see if there are checkboxes checked
    if ( isset( $_POST['wpc_gateway'] ) ) {
        //allow plugins to verify settings before saving
        $wpc_gateways = array_merge( $wpc_gateways, apply_filters('wpc_gateway_settings_filter', $_POST['wpc_gateway'] ) );
    }

    do_action( 'wp_client_settings_update', $wpc_gateways, 'gateways' );


    echo '<div class="updated wpc_notice fade"><p>' . __('Settings saved.', WPC_CLIENT_TEXT_DOMAIN) . '</p></div>';
}


?>

<style>
    .ui-tabs-vertical {
        width: 100%;
    }
    .ui-tabs-vertical .ui-tabs-nav {
        float: left;
        width: 16em;
    }
    .ui-tabs-vertical .ui-tabs-nav li {
        clear: left;
        width: 100%;
        border-bottom-width: 1px !important;
        border-right-width: 0 !important;
        margin: 0 -1px .2em 0;
    }
    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active {
        padding-bottom: 0;
        padding-right: .1em;
        border-right-width: 1px;
        border-right-width: 1px;
        background: none;
        background-color: #fff;
        border: none;
    }
    .ui-tabs-vertical .ui-tabs-panel {
        padding: 1em;
        float: right;
        width: 75%;
        padding-top: 0;
    }


</style>



<div class="wpc_clear"></div>

<h3><?php _e( 'Payment Settings', WPC_CLIENT_TEXT_DOMAIN ) ?></h3>
<p><?php _e( 'From here, you can manage payment gateways.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>


<script type="text/javascript">

    var site_url = '<?php echo site_url();?>';

    jQuery(document).ready(function () {




        //remove settings for not active gateways
        jQuery( '#wpc-gateways-form' ).submit( function() {
            jQuery( '.ui-tabs-panel:hidden' ).each( function() {
                jQuery( this ).remove();
            });

            return true;
        });


        jQuery(".wpc_allowed_gateways").click( function( event ) {

            if ( 'checked' == jQuery( this ).attr( 'checked' ) ) {
                var value    = 1;
            } else {
                var value    = 0;
            }

            var name        = jQuery(this).val();
            var checkbox    = jQuery( this );
            var tab_id    = jQuery( this ).attr( 'data-tab_id' );

            checkbox.hide();
            jQuery( '#wpc_ajax_loading_' + name ).addClass( 'wpc_ajax_loading' );

            jQuery.ajax({
                type: "POST",
                url: site_url+"/wp-admin/admin-ajax.php",
                data: "action=wpc_save_allow_gateways&name=" + name + "&enable=" + value,
                dataType: "json",
                success: function(data){
                    jQuery( '#wpc_ajax_loading_' + name ).removeClass( 'wpc_ajax_loading' );
                    checkbox.show();

                    if ( 1 == value ) {
                        checkbox.parent('li').find('a').show();
                        checkbox.parent('li').find('.wpc_gateway_title').hide();
                        jQuery( "#ui-tabs-" + ( tab_id*1 + 1 ) ).show()
                        jQuery( "#tabs" ).tabs( "load", tab_id );
                    } else {
                        checkbox.parent('li').find('a').hide();
                        checkbox.parent('li').find('.wpc_gateway_title').show();

                        jQuery( "#ui-tabs-" + ( tab_id*1 + 1 ) ).hide()
                    }

                },
            });

        });


        jQuery( "#tabs" ).tabs({
              beforeLoad: function( event, ui ) {

                if ( ! ui.tab.find( '.wpc_allowed_gateways' ).is(':checked') ) {
                    return;
                }

                if ( ui.tab.data( "loaded" ) ) {
                  event.preventDefault();
                  return;
                }

                ui.jqXHR.success(function() {
                  ui.tab.data( "loaded", true );
                });

              },

              load: function( event, ui ) {
                jQuery('.wpc_ibutton').iButton();
              }
            }).addClass( "ui-tabs-vertical ui-helper-clearfix" );

        jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );







    });
</script>




<div id="tabs">
    <form id="wpc-gateways-form" method="post" action="" style="width: 100%" >
        <input type="hidden" name="gateway_settings" value="1" />

        <ul style="width: 20%; float: left; margin: 0;">
            <?php
            $i = 0;
            foreach( (array)$wpc_gateway_plugins as $code => $plugin ) {
            ?>
            <li>
            <?php
            if ( isset( $wpc_gateways['allowed'] ) && in_array( $code, (array) $wpc_gateways['allowed'] ) ) {
                echo '<span id="wpc_ajax_loading_' . $code . '" style="float: left; margin-right: 6px;"></span><input type="checkbox" class="wpc_allowed_gateways" name="wpc_gateway[allowed][]" value="' . $code .'" checked="checked" data-tab_id="' . $i . '" />';
                echo ' <a href="' . site_url() . '/wp-admin/admin-ajax.php?action=wpc_get_gateway_setting&plugin=' . $code . '">' . esc_attr($plugin[1]) . '</a><span class="wpc_gateway_title" style="display: none;" style="float:left;white-space:normal;width:80%;">' . esc_attr( $plugin[1] ) . '</span>';
            } else {
                echo '<span id="wpc_ajax_loading_' . $code . '" style="float: left; margin-right: 6px;"></span><input type="checkbox" class="wpc_allowed_gateways" name="wpc_gateway[allowed][]" value="' . $code .'" data-tab_id="' . $i . '" />';
                echo ' <a href="' . site_url() . '/wp-admin/admin-ajax.php?action=wpc_get_gateway_setting&plugin=' . $code . '" style="display: none;" >' . esc_attr($plugin[1]) . '</a><span class="wpc_gateway_title"style="float:left;white-space:normal;width:80%;">' . esc_attr( $plugin[1] ) . '</span>';
            }
            $i++;
            ?>
            </li>
            <?php
            }
            ?>

        </ul>

        <?php if ( !isset( $GLOBALS['wpc_external_gateways'] ) ) { ?>
            <span style="margin: 15px 0px 0px 15px; float: left; clear: left; "><a href="https://webportalhq.com/link/payment-gateways-extension/" target="_blank" ><?php _e( 'Get More Gateways >>', WPC_CLIENT_TEXT_DOMAIN ) ?></a></span>
        <?php } ?>

    </form>
</div>

<div class="wpc_clear"></div>