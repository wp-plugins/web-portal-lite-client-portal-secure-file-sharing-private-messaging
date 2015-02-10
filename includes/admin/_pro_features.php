<?php
    wp_register_style( 'wpc-fancybox-style', $this->plugin_url . 'js/fancybox/jquery.fancybox.css' );
    wp_enqueue_style( 'wpc-fancybox-style' );
    wp_register_script( 'wpc-fancybox-js', $this->plugin_url . 'js/fancybox/jquery.fancybox.pack.js' );
    wp_enqueue_script( 'wpc-fancybox-js' );


$pro_features = array(
    'file_sharing' => array(
        'title' => 'File Sharing',
        'desc' => 'The file sharing system of WP-Client PRO is one of the core functionalities of the plugin. This feature allows the Admin to upload and assign files to each Client, either to large groups (through Circles or File Categories), or individually on a granular level. Files can be uploaded in browser using one of the built-in HTML5-based uploaders, or optionally files can be added via FTP and “synchronized” in the portal. Files from external locations (such as Dropbox) are also supported, provided that the file has a “public share URL” that can be utilized. Clients will only have access to files that are assigned to them, and only when they are properly logged in. This gives the Admin total control over exactly what files their Clients can access. Additionally, Clients can be given the ability to upload their own files from within their browser in their HUB Page. Any files that are uploaded by a Client will be viewable by any Admin and any assigned WPC-Manager.',
    ),
    'private_messaging' => array(
        'title' => 'Private Messaging',
        'desc' => 'The Private Messaging feature is essentially a secure email system built right into the plugin. This PRO feature allows you to read and reply to messages sent from your Clients from their secure HUB and Portal Pages. You can send a message to multiple Clients and/or entire Circles. When you send a single message to multiple people, the message is actually “split” into separate messages for each person. This allows you to have a separate message thread for each Client, and prevents them from seeing any messages that do not apply to them.',
    ),
    'managers' => array(
        'title' => 'Managers',
        'desc' => 'The additional roles included in WP-Client PRO also include the WPC-Manager. The WPC-Manager role is akin to a WPC-Admin role, but with fewer permissions.  This role allows you to assign a specific group of Clients to one “manager” within your organization. You can create WPC-Managers and assign existing Clients to them to provide a more personal experience and greater organization. For example, when a Client uploads a file or a private message is sent, the alert email is sent to that Client’s Manager. Additionally, the permissions of the WPC-Manager role can be controlled and modified using the “Capabilities” settings (available in PRO).',
    ),
    'admins' => array(
        'title' => 'Admins',
        'desc' => 'In WP-Client PRO, you have access to additional user roles that allow you to customize the experience of your team. The WPC-Admin role has full access to WP-Client settings, but no access to the rest of your WordPress installation.  This is ideal if you want to delegate the admin duties of the Portal to an employee, but don’t want to give them full WordPress admin level capabilities.',
    ),
    'client_approve' => array(
        'title' => 'Client Approval',
        'desc' => 'With the optional “admin approval” feature of Client registration in WP-Client PRO, Clients can self-register themselves on your site using the plugin’s provided Client Registration Form. After registration, you (as the site admin) will be notified via email that there are new registrations awaiting approval. Clients will be unable to view the contents of their HUB Page until the admin approves them from the WP-Client backend dashboard.',
    ),
    'convert' => array(
        'title' => 'Convert User',
        'desc' => 'When you install WP-Client, you may have existing users already created on your website (Subscribers, Editors, etc.) that you want to now have the capabilities of a WPC-Manager, WPC-Admin, Client, or Client Staff. The “Convert Users” functionality of WP-Client PRO makes that conversion possible, with the option to retain the characteristics of the users current role as well. Additionally, WP-Client has an option to set any standard WordPress role (Editor, Subscriber, etc) to be automatically converted to a Client upon creation, allowing you more ways to add Clients to your installation.',
    ),
    'client_staff' => array(
        'title' => 'Client Staff',
        'desc' => 'If you’re working with a large Client and have multiple employees or persons within the Client organization that you will be working with, each person in that organization will need a separate portal login. With WP-Client PRO, individual Staff users can be created under each existing Client. The Staff user role essentially acts as a “mirror” account of their parent Client, allowing multiple employees in a company to access the portal, without needing to share one username/password. Within the Staff functionality of WP-Client PRO, there are multiple ways that Staff users can be added. One avenue is for the Staff users to be added manually by the admin. This allows you to create a Staff user, assign them to a Client, and have their account automatically be approved and immediately active. A second option for adding Staff users in the portal is allowing Clients to register them. WP-Client PRO allows you to enable the “Client’s Staff Registration Form”, which will display for Clients in their HUB Page, along with their other assigned pages. Clients can then use this frontend form to create their own Staff users, which are automatically assigned to themselves. Any Staff users created by a Client will need to be approved by an admin before their account will become active.',
    ),
    'custom_fields' => array(
        'title' => 'Custom Fields',
        'desc' => 'If you would like to collect more information than what is natively done by WP-Client, you can use Custom Fields. This PRO feature allows you to associate new data fields with your Clients, allowing you to gather additional information. For instance, each of Clients may have a unique business ID you would like to keep track of, or perhaps you would like to collect a mailing address to use for billing purposes. Any quantifiable data that you need collect can most likely be done using Custom Fields.',
    ),
    'templates' => array(
        'title' => 'Templates',
        'desc' => '
            There are many email notifications that are sent out by WP-Client regarding various actions, such as when a new file is uploaded, a private message is received, and so on. Nearly every action in WP-Client has a corresponding email notification. In WP-Client PRO, you have the ability to completely customize these email notifications via templates. These templates all use the same Visual Editor utilized elsewhere in the plugin, allowing you to customize the templates with ease. Additionally, you can utilize WP-Client placeholders (both default ones and from Custom Fields) to dynamically insert information into the templates, giving the emails a personalized feel.
            <br><br>
            Shortcode Templates allow advanced users to modify the actual output of shortcodes in WP-Client PRO, which opens up a whole world of customization possibilities. From simple changes like adjusting the spacing of a Portal Page list, to more advanced custom PHP functionality, the full unencrypted shortcode template code is there for you to make use of however you feel would fit your needs the best.
            ',
    ),
    'settings' => array(
        'title' => 'Settings',
        'desc' => 'WP-Client PRO includes a wealth of settings that allow you to setup and customize your portal to fit your needs. Included in these settings is the ability to change plugin terminology (Client, Staff, Circle, etc) to your own Custom Titles. This allows you to give the backend menus of WP-Client a more personalized feel. You can personalize the frontend of the plugin using the “Custom Login” settings, which allows you to completely rebrand the standard “wp-login.php” WordPress login URL, including adding custom graphics and text colors. Also include in the WP-Client PRO settings suite are a host of permission controls, including the “Capabilities” menu, which allows you to control what access certain WP-Client roles have to specific parts of the plugin. The ability to restrict access to your portal based on IP addresses is also included. There are also several setting options dedicated to notifications and emails, most notably the ability to be notified via email whenever a user logs in (or attempts a login and fails). These emails, and others created by WP-Client, can be routed through a different mail service using the plugin’s SMTP option.',
    ),
    'portal_category' => array(
        'title' => 'Portal Page Categories',
        'desc' => 'WP-Client PRO gives you more control over organizing and showcasing Portal Pages in your installation, through Portal Page Categories. Using Portal Page Categories, you can organize your Portal Pages, allowing you to easily bulk assign multiple pages at once to one or more Clients and/or Circles. Additionally, you can use attributes within the “List of Portal Pages” shortcode to customize the shortcode to only display Portal Pages from a certain category. These functions combine to give you full granular control over who can see which Portal Pages, and how they see them.',
    ),


);


ksort($pro_features );


?>






<div class='wrap'>

    <?php echo $this->get_plugin_logo_block() ?>

    <div class="wpc_clear"></div>

    <h2><?php _e( 'WP-Client Pro Features', WPC_CLIENT_TEXT_DOMAIN ) ?></h2>

    <p><?php _e( "Upgrading to WP-Client PRO unlocks the full power of WP-Client, giving you one plugin with the ability to configure multiple areas of your WordPress installation, and allowing you to easily create private client areas, client management portals, and Private Staff Pages on your site by entering  just a few data fields. Additionally, with the features of WP-Client PRO, clients can upload/download secure files, view and pay assigned estimates and invoices, and privately send messages to the site admin or their assigned manager. WP-Client PRO also includes a client import functionality, allowing you to bring in all of your existing clients at one time using a simply formatted CSV file and a few clicks.", WPC_CLIENT_TEXT_DOMAIN ) ?></p>

    <div class="wpc_pro_features_table">
        <?php foreach( $pro_features as $key => $value ) {
            $dir = $this->plugin_dir . 'images/screenshots/'. $key . '/';

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
                        <a href="<?php echo $this->plugin_url . 'images/screenshots/' . $key . '/' . $screenshots[0] ?>" rel="wpclients_<?php echo $key ?>" class="fancybox_<?php echo $key ?>" title="<?php echo $value['title'] ?>"><img alt="" class="wpc_pro_screenshot" src="<?php echo $this->plugin_url . 'images/screenshots/' . $key . '/' . $screenshots[0] ?>" class="image"></a>
                    <?php
                        unset( $screenshots[0] );
                    }
                    ?>
                    <?php echo $value['desc'] ?>
                </p>

                <?php if ( count( $screenshots ) ) { ?>
                    <div class="wpc_pro_screenshots">
                        <span class="wpc_pro_screenshots_text"><?php _e( 'Additional Screenshots', WPC_CLIENT_TEXT_DOMAIN ) ?>:</span>
                        <div class="wpc_pro_gallery">
                            <?php
                            foreach( $screenshots as $file_name ) {
                            ?>
                                <a href="<?php echo $this->plugin_url . 'images/screenshots/' . $key . '/' . $file_name ?>" rel="wpclients_<?php echo $key ?>" class="fancybox_<?php echo $key ?>" title="<?php echo $value['title'] ?>"><img alt="" class="wpc_pro_screenshot" src="<?php echo $this->plugin_url . 'images/screenshots/' . $key . '/' . $file_name ?>" class="image"></a>
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