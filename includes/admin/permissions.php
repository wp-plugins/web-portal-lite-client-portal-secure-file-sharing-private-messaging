<div class='wrap'>
    <?php echo $this->get_plugin_logo_block() ?>
    <div class="wpc_clear"></div>
    <div id="container23">
        <h2><?php _e( 'Permissions Report', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>
        <div>
            <p>
                This tool is designed to show comprehensive reports of the assigned permissions given based on Client or Circle affiliation, and by what path those permissions were assigned. This helps you ensure that your permissions are setup the way you intended and helps identify and possible accidental assignment of permissions that you did not intend.
<br /><br />
You can use this tool in two ways.
<br /><br />
1. Show the resources that are permissioned to any Client or Circle by selecting Client or Circle from first select box, and then selecting a specific Client or Circle from the select box below that. Then, you can make your selection of all or specific resources from the select box on the right and click REPORT
<br /><br />
Click the directional arrow button in the middle to change to Method 2
<br /><br />
2. Select a resource type from the top select box, and then use the 2nd select box to choose a specific resource from that category. Then, use the select box on the right to choose to view permissions based on Client or Circle assignment.
            </p>
        </div>
        <br />
        <div id="left_select" style="float: left;">

            <select name="left_select_first" id="left_select_first" style="float: left;" class="select_report left_select" >
                <option value="client" selected="selected"><?php echo $this->custom_titles['client']['s'] ?></option>
                <option value="circle"><?php echo $this->custom_titles['circle']['s'] ?></option>
            </select>

            <select name="left_select_second" id="left_select_second" style="display: none; float: left;" class="select_report left_select" >
                <option value="portal_page"><?php echo $this->custom_titles['portal']['s']; ?></option>
                <option value="portal_page_category"><?php printf( __( '%s Category', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?></option>
            </select>
            <br />

            <span id="load_select_filter" style="float: left;"></span>
            <br />
            <div id="for_selectbox"></div>

        </div>

        <div id="right_select" style="float: left;">
            <div id="reverse" style="float: left; cursor: pointer;" class="jfk-button-img" data-course="there"></div>

            <select name="right_select_first" id="right_select_first" style="float: left; " class="select_report" >
                <option value="all" selected="selected"><?php _e( 'Show All', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                <option value="portal_page"><?php echo $this->custom_titles['portal']['p']; ?></option>
                <option value="portal_page_category"><?php printf( __( '%s Categories', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ) ?></option>
            </select>

            <select name="right_select_second" id="right_select_second" style="float: left; display: none;"  class="select_report" >
                <option value="all" selected="selected"><?php _e( 'Show All', WPC_CLIENT_TEXT_DOMAIN ) ?></option>
                <option value="client"><?php echo $this->custom_titles['client']['p'] ?></option>
                <option value="circle"><?php echo $this->custom_titles['circle']['p'] ?></option>
            </select>

        </div>
        <input type="button" style="float: left; margin-left: 15px;" value="<?php _e( 'Report', WPC_CLIENT_TEXT_DOMAIN ) ?>" class="button-primary" id="report" name="" />
        <span id="load_text_report" style="float: left;"></span>
    </div>
    <br />
    <div class="wpc_clear"></div>
    <div id="text_report"></div>
    <script type="text/javascript">
        var site_url = '<?php echo site_url();?>';

        jQuery(document).ready(function(){
            //change left select first
            jQuery( '.left_select' ).change( function(){
                //jQuery( '#select_filter_chzn' ).remove();
                var left_select = jQuery( this ).val();
                jQuery( '#for_selectbox' ).html( '' );
                jQuery( '#for_selectbox' ).html( '<select name="select_filter" id="select_filter" style="display: none;"  class="select_report chzn-select" >' );
                //jQuery( '#select_filter' ).css( 'display', 'none' );
                jQuery( '#select_filter' ).html( '' );
                jQuery( '#load_select_filter' ).addClass( 'wpc_ajax_loading' );
                jQuery.ajax({
                type: 'POST',
                url: site_url + '/wp-admin/admin-ajax.php',
                data: 'action=wpc_get_options_filter_for_permissions&left_select=' + left_select,
                dataType: 'html',
                success: function( data ){
                    jQuery( '#select_filter' ).html( data );
                    jQuery( '#load_select_filter' ).removeClass( 'wpc_ajax_loading' );
                    jQuery( '#select_filter' ).css( 'display', 'block' );
                    jQuery( '.chzn-select' ).chosen({
                        no_results_text: '<?php _e( 'No results matched', WPC_CLIENT_TEXT_DOMAIN ) ?>',
                        allow_single_deselect: true,
                    });
                }
                });
                return false;
            } );

            jQuery( '#left_select_first' ).trigger('change');

            //click reverse
            jQuery( '#reverse' ).click( function() {
                if ( 'there' == jQuery( '#reverse' ).attr('data-course') ) {
                    jQuery( '#left_select_first' ).css( 'display', 'none' );
                    jQuery( '#left_select_second' ).css( 'display', 'block' );
                    jQuery( '#right_select_first' ).css( 'display', 'none' );
                    jQuery( '#right_select_second' ).css( 'display', 'block' );
                    jQuery( '#select_filter' ).css( 'display', 'none' );
                    jQuery( '#select_filter' ).html( '' );
                    jQuery( '#reverse' ).attr('data-course', 'back');
                    jQuery( '#text_report' ).html( '' );

                    jQuery( '#left_select_second' ).trigger('change');
                } else if ( 'back' == jQuery( '#reverse' ).attr('data-course') ){
                    jQuery( '#left_select_first' ).css( 'display', 'block' );
                    jQuery( '#left_select_second' ).css( 'display', 'none' );
                    jQuery( '#right_select_first' ).css( 'display', 'block' );
                    jQuery( '#right_select_second' ).css( 'display', 'none' );
                    jQuery( '#select_filter' ).css( 'display', 'none' );
                    jQuery( '#select_filter' ).html( '' );
                    jQuery( '#reverse' ).attr('data-course', 'there');
                    jQuery( '#text_report' ).html( '' );

                    jQuery( '#left_select_first' ).trigger('change');

                }
            });

            //click report
            jQuery( '#report' ).click( function() {
                var left_value = jQuery( '#select_filter' ).val();
                if ( 'all' != jQuery( '#select_filter' ).val() && left_value ) {
                    jQuery( '#select_filter' ).parent().removeClass( 'wpc_error' );
                    var course = jQuery( '#reverse' ).attr('data-course');
                    if ( 'back' == jQuery( '#reverse' ).attr('data-course') ) {
                        var right_key = jQuery( '#right_select_second' ).val();
                        var left_key = jQuery( '#left_select_second' ).val();
                    }
                    else {
                        var right_key = jQuery( '#right_select_first' ).val();
                        var left_key = jQuery( '#left_select_first' ).val();
                    }
                    jQuery( '#text_report' ).css( 'display', 'none' );
                    jQuery( '#text_report' ).html( '' );
                    jQuery( '#load_text_report' ).addClass( 'wpc_ajax_loading' );
                    jQuery.ajax({
                    type: 'POST',
                    url: site_url + '/wp-admin/admin-ajax.php',
                    data: 'action=wpc_get_report_for_permissions&left_key=' + left_key + '&left_value=' + left_value + '&right_key=' + right_key + '&course=' + course,
                    dataType: 'html',
                    success: function( data ){
                        jQuery( '#text_report' ).html( data );
                        jQuery( '#load_text_report' ).removeClass( 'wpc_ajax_loading' );
                        jQuery( '#text_report' ).css( 'display', 'block' );
                    }
                    });
                } else {
                    jQuery( '#select_filter' ).parent().addClass( 'wpc_error' );
                }

                return false;
            });

        });

        </script>
</div>
