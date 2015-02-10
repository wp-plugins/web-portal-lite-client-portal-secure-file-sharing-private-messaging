<?php
    wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
    wp_enqueue_style( 'wpc-fancybox-style' );
    wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
    wp_enqueue_script( 'wpc-fancybox-js' );


$pro_features = array(
    'feedback_wizard' => array(
        'title' => 'Feedback Wizard',
        'desc' => 'The Feedback Wizard is essentially a unique, professional, secure & efficient method whereby the administrator of the site can bundle together a specific set of images, documents, files or links - and effectively present to a client a simple and easy to follow process that allows them to provide formalized and focused feedback.',
        'premium' => '0',
    ),
    'invoicing' => array(
        'title' => 'Estimates/Invoices',
        'desc' => 'Easily create estimates and invoices that your clients can pay online using the provided payment gateways. You can display invoices on your website, send in PDF format via email, or print out and send in traditional snail mail. Functions for recurring and accumulating invoicing also exist, with the option to set automatic recurring PayPal charges for your clients.',
        'premium' => '0',
    ),
    'login_logs' => array(
        'title' => 'Login Logs',
        'desc' => 'When users log into your site, the details are retained in the the database, and a report is generated so you can see a complete record of who has logged into the site. Use this functionality to keep track of unauthorized login attempts to your site, as well as quickly troubleshoot when a client is attempting to login and failing.',
        'premium' => '0',
    ),
    'paid_registration' => array(
        'title' => 'Paid Registration',
        'desc' => 'Configure the self registration system to only give clients access after they have paid using one of the provided payment gateways. Registration is a one-time non-recurring fee that is set in advance, and can be paid via any of WP-Client’s built-in payment gateways.',
        'premium' => '0',
    ),
    'private_post_types' => array(
        'title' => 'Private Post Types',
        'desc' => 'This Extension allows you to make any page, post or custom post type part of your Portal. You can easily assign permissions, restrict public viewing, and include links to these resources in your Client’s HUBs and Portal Pages.',
        'premium' => '0',
    ),
    'time_limited_clients' => array(
        'title' => 'Time Limited Clients',
        'desc' => 'Easily set an expiration date for each individual client after which that clients login will no longer allow access. Their credentials are still in place, but they receive a customizable error notification explaining that their login has expired.',
        'premium' => '0',
    ),


    'payment_gateways' => array(
        'title' => 'Payment Gateways (Premium)',
        'desc' => 'Give your clients more ways to pay! This Premium Extension adds these gateways to the existing WP-Client choices: 2CheckOut, Amazon Payments, Braintree, PayMil, and Skrill. These additional payment gateways can be used in all of the same places as the standard WP-Client gateways, including Invoicing and Paid Registration.',
        'premium' => '1',
    ),
    'project_management' => array(
        'title' => 'Project Management (Premium)',
        'desc' => 'This Premium Extension is designed to let you organize your work, coordinate easily with teams, and integrate your clients into the process. Create tasks and assign them to individual Teammates or Freelancers, send private project-specific messages and files to Clients, and keep track of it all through the main WP-Client Project Dashboard.',
        'premium' => '1',
    ),
    'shutter' => array(
        'title' => 'Shutter (Premium)',
        'desc' => 'This Premium Extension allows you to upload, manage, protect, and sell images from right inside your existing WP-Client installation! Want to share images with your existing clients, or optionally sell them high-res downloads of photos? It’s as simple as installing Shutter right along-side your already running WP-Client. Shutter integrates seamlessly with WP-Client, allowing you to install Shutter and beginning assigning Galleries to your already created clients, so you don’t miss a step along the way!',
        'premium' => '1',
    ),
    'sms_notifications' => array(
        'title' => 'SMS Notifications (Premium)',
        'desc' => 'This Premium Extension allows you to easily use Email-to-SMS technology to send your clients text messages. Choose to send on certain events (such as when a file is uploaded) or send broadcast SMS messages to some or all of your clients.',
        'premium' => '1',
    ),
    'white_label' => array(
        'title' => 'White Label (Premium)',
        'desc' => 'White Label gives you all the power of WP-Client. The standard version of WP-Client gives you the ability to completely customize the front-end interface that users will see. The White Label extension takes this one step further, and gives you the capability to customize the backend interface of the plugin also. Change the name of the plugin, add your company’s graphics, website, etc... White Label lets you brand the plugin like it was yours so that you can resell it as part of website development work.',
        'premium' => '1',
    ),


);


ksort($pro_features );


?>






<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>


    <div class="icon32" id="icon-options-general"></div>
    <h2><?php _e( 'WP-Client Pro Extensions', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

    <p><?php _e( 'WP-Client PRO uses Extensions to expand the functionality of the plugin, such as adding invoicing functionality. Some Extensions are included with all WP-Client PRO license levels, whereas others can be added "a la carte" to licenses at an additional cost.', WPC_CLIENT_TEXT_DOMAIN ) ?></p>




    <div class="wpc_pro_features_table">
        <?php foreach( $pro_features as $key => $value ) {
            $dir = $this->plugin_dir . 'images/screenshots/_extensions/'. $key . '/';

            if ( is_dir( $dir ) ) {
                $dh = opendir( $dir );
                if ( $dh ) {

                    $screenshots = array();
                    while ( ( $img = readdir( $dh ) ) !== false ) {
                        if ( '..' != $img && '.' != $img ) {
                            $screenshots[] = $img;
                        }
                    }
                    closedir( $dh );

                }
            }
        ?>

        <div class="postbox">
            <a name="<?php echo $key ?>" style="margin-top: -50px; float: left;"></a>
            <h3 class='hndle'><span><?php echo $value['title'] ?></span></h3>
            <div class="inside">
                <p class="description">
                    <?php if ( count( $screenshots ) ) { ?>
                        <a href="<?php echo $this->plugin_url . 'images/screenshots/_extensions/' . $key . '/' . $screenshots[0] ?>" rel="wpclients_<?php echo $key ?>" class="fancybox_<?php echo $key ?>" title="<?php echo $value['title'] ?>"><img alt="" class="wpc_pro_screenshot" src="<?php echo $this->plugin_url . 'images/screenshots/_extensions/' . $key . '/' . $screenshots[0] ?>" class="image"></a>
                    <?php
                        unset( $screenshots[0] );
                    }
                    ?>
                    <?php echo $value['desc'] ?>



                </p>

                <p>
                    <?php if ( '1' == $value['premium'] ) { ?>
                    *Premium Extension, not included with all license levels.
                    <?php } else { ?>
                    *Standard Extension, included with all PRO license levels.
                    <?php } ?>

                </p>

                <?php if ( count( $screenshots ) ) { ?>
                    <div class="wpc_pro_screenshots">
                        <span class="wpc_pro_screenshots_text"><?php _e( 'Additional Screenshots', WPC_CLIENT_TEXT_DOMAIN ) ?>:</span>
                        <div class="wpc_pro_gallery">
                            <?php
                            foreach( $screenshots as $file_name ) {
                            ?>
                                <a href="<?php echo $this->plugin_url . 'images/screenshots/_extensions/' . $key . '/' . $file_name ?>" rel="wpclients_<?php echo $key ?>" class="fancybox_<?php echo $key ?>" title="<?php echo $value['title'] ?>"><img alt="" class="wpc_pro_screenshot" src="<?php echo $this->plugin_url . 'images/screenshots/_extensions/' . $key . '/' . $file_name ?>" class="image"></a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>

                <?php } ?>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function() {

                jQuery(".fancybox_<?php echo $key ?>").fancybox({
                    openEffect    : 'none',
                    closeEffect    : 'none'
                });
            });
        </script>

        <?php
            }
        ?>

    </div>


</div>