<?php


if ( !class_exists( "WPC_Client_Install" ) ) {

    class WPC_Client_Install extends WPC_Client_Admin_Common {

        /**
        * PHP 5 constructor
        **/
        function __construct() {
            $this->common_construct();
            $this->admin_common_construct();
        }


        /*
        * Pre-set all plugin's pages
        */
        function pre_set_pages() {
            $wpc_pre_pages = array(
                array(
                    'title'     => __( 'Login Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Login Page',
                    'desc'      => __( 'Page content: [wpc_client_loginf]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'login_page_id',
                    'old_id'    => 'login',
                    'shortcode' => true,
                    'content'   => '[wpc_client_loginf]',
                ),
                array(
                    'title'     => __( 'HUB Page', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'HUB Page',
                    'desc'      => __( 'Page content: [wpc_client_hub_page]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'hub_page_id',
                    'old_id'    => 'hub',
                    'shortcode' => true,
                    'content'   => '[wpc_client_hub_page]',
                ),
                array(
                    'title'     => $this->custom_titles['portal']['s'],
                    'name'      => $this->custom_titles['portal']['s'],
                    'desc'      => __( 'Page content: [wpc_client_portal_page]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'portal_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => '[wpc_client_portal_page]',
                ),
                array(
                    'title'     => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'name'      => sprintf( __( 'Edit %s', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                    'desc'      => __( 'Page content: [wpc_client_edit_portal_page]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'edit_portal_page_id',
                    'old_id'    => 'edit_clientpage',
                    'shortcode' => true,
                    'content'   => '[wpc_client_edit_portal_page]',
                ),
                array(
                    'title'     => __( 'Staff Directory', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Staff Directory',
                    'desc'      => __( 'Page content: [wpc_client_staff_directory]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'staff_directory_page_id',
                    'old_id'    => 'staff_directory',
                    'shortcode' => true,
                    'content'   => '[wpc_client_staff_directory]',
                ),
                array(
                    'title'     => __( 'Add Staff', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Add Staff',
                    'desc'      => __( 'Page content: [wpc_client_add_staff_form]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'add_staff_page_id',
                    'old_id'    => 'add_staff',
                    'shortcode' => true,
                    'content'   => '[wpc_client_add_staff_form]',
                ),
                array(
                    'title'     => __( 'Edit Staff', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Edit Staff',
                    'desc'      => __( 'Page content: [wpc_client_edit_staff_form]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'edit_staff_page_id',
                    'old_id'    => 'edit_staff',
                    'shortcode' => true,
                    'content'   => '[wpc_client_edit_staff_form]',
                ),
                array(
                    'title'     => __( 'Client Registration', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Client Registration',
                    'desc'      => __( 'Page content: [wpc_client_registration_form]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'client_registration_page_id',
                    'old_id'    => 'registration',
                    'shortcode' => true,
                    'content'   => '[wpc_client_registration_form]',
                ),
                array(
                    'title'     => __( 'Successful Client Registration', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Successful Client Registration',
                    'desc'      => __( 'Page content: [wpc_client_registration_successful]', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'successful_client_registration_page_id',
                    'old_id'    => 'registration_successful',
                    'shortcode' => true,
                    'content'   => '[wpc_client_registration_successful]',
                ),
                array(
                    'title'     => __( 'Error', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Error',
                    'desc'      => __( 'Page content: [wpc_client_error_image] or any text', WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'error_page_id',
                    'old_id'    => '',
                    'shortcode' => false,
                    'content'   => '[wpc_client_error_image]',
                ),
                array(
                    'title'     => __( 'Client Profile', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Client Profile',
                    'desc'      => __( "Page content: [wpc_client_profile]", WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'profile_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => "[wpc_client_profile]",
                ),
                array(
                    'title'     => __( 'Payment Process', WPC_CLIENT_TEXT_DOMAIN ),
                    'name'      => 'Payment Process',
                    'desc'      => __( "Page content: [wpc_client_payment_process]", WPC_CLIENT_TEXT_DOMAIN ),
                    'id'        => 'payment_process_page_id',
                    'old_id'    => '',
                    'shortcode' => true,
                    'content'   => "[wpc_client_payment_process]",
                )
            );

            $wpc_pre_pages = apply_filters( 'wpc_client_pre_set_pages_array', $wpc_pre_pages );

            return $wpc_pre_pages;
        }


        /*
        * Create all plugin's pages
        */
        function create_pages() {

            $wpc_pre_pages = $this->pre_set_pages();

            $wpc_client_page = get_page_by_title( 'Portal' );

            if ( !isset( $wpc_client_page ) || 0 >= $wpc_client_page->ID ) {

                $current_user = wp_get_current_user();
                //Construct args for the new page
                $args = array(
                    'post_title'     => 'Portal',
                    'post_status'    => 'publish',
                    'post_author'    => $current_user->ID,
                    'post_content'   => '[wpc_redirect_on_login_hub]',
                    'post_type'      => 'page',
                    'ping_status'    => 'closed',
                    'comment_status' => 'closed'
                );
                $parent_page_id = wp_insert_post( $args );
            } else {
                $parent_page_id = $wpc_client_page->ID;
            }

            //pages from settings
            $wpc_pages = $this->cc_get_settings( 'pages' );

            //create page if needs
            foreach( $wpc_pre_pages as $pre_page ) {

                if( isset( $wpc_pages[$pre_page['id']] ) && is_numeric( $wpc_pages[$pre_page['id']] ) ) {
                    $current_page = get_post( $wpc_pages[$pre_page['id']] );
                }

                if ( !isset( $wpc_pages[$pre_page['id']] ) || 0 >= $wpc_pages[$pre_page['id']] || '' == $wpc_pages[$pre_page['id']] || !isset( $current_page->ID ) ) {

                    $wpc_client_page = get_page_by_title( $pre_page['name'] );
                    if ( !isset( $wpc_client_page ) || 0 >= $wpc_client_page->ID ) {

                        $current_user = wp_get_current_user();
                        //Construct args for the new page
                        $args = array(
                            'post_title'        => $pre_page['name'],
                            'post_status'       => 'publish',
                            'post_author'       => $current_user->ID,
                            'post_content'      => $pre_page['content'],
                            'post_type'         => 'page',
                            'ping_status'       => 'closed',
                            'comment_status'    => 'closed',
                            'post_parent'       => $parent_page_id,
                        );
                        $page_id = wp_insert_post( $args );

                        $wpc_pages[$pre_page['id']] = $page_id;
                    } else {
                        $wpc_pages[$pre_page['id']] = $wpc_client_page->ID;
                    }
                }
            }

            do_action( 'wp_client_settings_update', $wpc_pages, 'pages' );
        }


        /*
        * Create DB tables
        */
        function creating_db() {
            global $wpdb;

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $charset_collate = '';

            if ( ! empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";

            // specific tables.
$tables = "CREATE TABLE {$wpdb->prefix}wpc_client_groups (
 group_id int(11) NOT NULL auto_increment,
 group_name varchar(255) NOT NULL,
 auto_select varchar(1) NULL,
 auto_add_files varchar(1) NULL,
 auto_add_pps varchar(1) NULL,
 auto_add_manual varchar(1) NULL,
 auto_add_self varchar(1) NULL,
 PRIMARY KEY  (group_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_group_clients (
group_id int(11) NOT NULL,
client_id int(11) NOT NULL
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_login_redirects (
 rul_type enum('user','circle','role','level','all') NOT NULL,
 rul_value varchar(255) NOT NULL default '',
 rul_url LONGTEXT NULL,
 rul_url_logout LONGTEXT NULL,
 rul_order int(2) NOT NULL default '0',
 UNIQUE KEY rul_type (rul_type,rul_value)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_clients_page (
 id mediumint(9) NOT NULL AUTO_INCREMENT,
 pagename tinytext NOT NULL,
 template tinytext NOT NULL,
 users tinytext NOT NULL,
 PRIMARY KEY  (id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_objects_assigns (
 id bigint(20) NOT NULL AUTO_INCREMENT,
 object_type enum('file','file_category','portal_page','portal_page_category','post_category','ez_hub','manager','feedback_wizard','invoice','accum_invoice','repeat_invoice','estimate','shutter','shutter_category','form') NOT NULL,
 object_id bigint(20) NULL,
 assign_type enum('circle','client') NOT NULL,
 assign_id bigint(20) NULL,
 PRIMARY KEY  (id),
 KEY objectid_assignid (object_id,assign_id),
 KEY objectid (object_id),
 KEY assignid (assign_id)
) $charset_collate;
CREATE TABLE {$wpdb->prefix}wpc_client_payments (
 id int(11) NOT NULL AUTO_INCREMENT,
 order_id varchar(50) NULL,
 order_status varchar(30) NULL,
 function varchar(50) NULL,
 payment_method varchar(50) NULL,
 payment_type varchar(64) DEFAULT NULL,
 client_id int(11) NULL,
 amount varchar(30) NULL,
 currency varchar(10) NULL,
 data text NULL,
 transaction_id text NULL,
 transaction_status text NULL,
 time_created text NULL,
 time_paid text NULL,
 subscription_id varchar(50) NULL,
 subscription_status varchar(50) NULL,
 next_payment_date varchar(25) NULL,
 PRIMARY KEY  (id)
) $charset_collate\n;
CREATE TABLE {$wpdb->prefix}wpc_client_categories (
 id int(11) NOT NULL AUTO_INCREMENT,
 parent_id int(11) NOT NULL DEFAULT 0,
 name text NULL,
 type enum('file','portal_page','shutter') NOT NULL,
 cat_order int NULL,
 PRIMARY KEY  (id)
)$charset_collate\n;";

            dbDelta( $tables );
        }


        /**
        * Set Default Settings
        **/
        function default_settings() {
            $wpc_default_settings['general'] = array(
                'show_hub_link'                 => 'no',
                'hub_link_text'                 => 'My HUB',
                'show_hub_title'                => 'yes',
                'show_custom_menu'              => 'no',
                'custom_menu_logged_in'         => '',
                'custom_menu_logged_out'        => '',
                'graphic'                       => '',
            );

            $key1 = substr( md5('USD'), 0, 13);
            $key2 = substr( md5('EUR'), 0, 13);
            $wpc_default_settings['currency'] = array(
                $key1 => array(
                    'default' => 1,
                    'title' => 'US Dollar',
                    'code' => 'USD',
                    'symbol' => '$',
                    'align' => 'left'
                ),
                $key2 => array(
                    'default' => 0,
                    'title' => 'European euro',
                    'code' => 'EUR',
                    'symbol' => '&euro;',
                    'align' => 'left'
                )
            );

            $wpc_default_settings['clients_staff'] = array(
                'hide_dashboard'                => 'no',
                'create_portal_page'            => 'yes',
                'use_portal_page_settings'      => '0',
                'hide_admin_bar'                => 'yes',
                'lost_password'                 => 'no',
                'client_registration'           => 'no',
                'auto_client_approve'           => 'no',
                'new_client_admin_notify'       => 'yes',
                'send_approval_email'           => 'no',
                'staff_registration'            => 'no',
                'registration_using_captcha'    => 'no',
                'captcha_publickey'             => '',
                'captcha_privatekey'            => '',
                'captcha_theme'                 => 'red',
            );

            $wpc_default_settings['file_sharing'] = array(
                'show_sort'                     => 'yes',
                'js_sort'                       => 'yes',
                'show_file_cats'                => 'yes',
                'deny_file_cats'                => 'yes',
                'flash_uplader_admin'           => 'plupload',
                'flash_uplader_client'          => 'plupload',
                'file_size_limit'               => '',
                'attach_file_admin'             => 'no',
            );

            $wpc_default_settings['custom_login'] = array(
                'cl_enable'             => 'yes',
                'cl_background'         => $this->plugin_url .'images/logo.png',
                'cl_backgroundColor'    => 'ffffff',
                'cl_color'              => '000033',
                'cl_linkColor'          => '00A5E2',
                'cl_login_url'          => '',
                'cl_hide_admin'         => 'no'
            );

            $wpc_default_settings['business_info'] = array();

            $wpc_default_settings['enable_custom_redirects'] = 'no';

            $wpc_default_settings['default_redirects'] = array(
                'login' => '',
                'logout' => '',
            );

            $wpc_default_settings['capabilities'] = array();

            $wpc_default_settings['pages'] = array();

            $wpc_default_settings['gateways'] = array(
                'allowed' => array(),
            );

            $wpc_default_settings['custom_titles'] = array();

            $wpc_default_settings['login_alerts'] = array(
                'email'         => '',
                'successful'    => '0',
                'failed'        => '0',
            );

            $wpc_default_settings['skins'] = 'light';

            $wpc_default_settings['smtp'] = array(
                'enable_smtp'   => false,
                'smtp_host'     => '',
                'smtp_port'     => '',
                'secure_prefix' => '',
                'smtp_username' => '',
                'smtp_password' => ''
            );

            $wpc_default_settings['limit_ips'] = array(
                'enable_limit'  => 'no',
                'ips'           => array()
            );

            //Set settings
            foreach( $wpc_default_settings as $key => $values ) {
                add_option( 'wpc_' . $key, $values );

                if ( is_array( $values ) && count( $values ) ) {
                    $current_setting = get_option( 'wpc_' . $key );
                    if ( is_array( $current_setting ) ) {
                        $new_setting = array_merge( $values, $current_setting );
                    } else {
                        $new_setting = $values;
                    }
                    update_option( 'wpc_' . $key, $new_setting );
                }
            }

        }


        /**
        * Set Default Templates
        **/
        function default_templates() {

            $wpc_default_templates['templates_hubpage'] =  htmlentities( stripslashes( '[wpc_client_hub_page_template /]' ) );

            $wpc_default_templates['templates_clientpage'] =  htmlentities( stripslashes( '
<p>[wpc_client]<span style="font-size: medium;">Welcome {client_business_name} to your first Portal Page<span style="font-size: small;"> | [wpc_client_get_page_link page="hub" text="HUB Page"] | [wpc_client_logoutb]</span></span></p>
<p>We\'ll be using this page to relay information and graphics to you.</p>
<p>[/wpc_client]</p>
' ) );


            //email when Client created by admin
            $wpc_default_templates['templates_emails']['new_client_password'] = array(
                'subject'               => 'Your Private and Unique Client Portal has been created',
                'body'                  => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>Your private and secure Client Portal has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            $wpc_default_templates['templates_emails']['self_client_registration'] = array(
                'subject'               => 'Your Private and Unique Client Portal has been created',
                'body'                  => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>Your private and secure Client Portal has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Client created for verify email
            $wpc_default_templates['templates_emails']['new_client_verify_email'] = array(
                'subject'               => 'Verify email',
                'body'                  => '<p>Hello {contact_name}<br /> <br /> </p>
                <p>Your email verified by clicking <strong><a href="{verify_url}">HERE</a></strong></p>',
            );

            //email when Client updated
            $wpc_default_templates['templates_emails']['client_updated'] = array(
                'subject'   => 'Your Client Password has been updated',
                'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>Your password has been updated. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Portal Page is updated
            $wpc_default_templates['templates_emails']['client_page_updated'] = array(
                'subject'   => sprintf( __( 'Your %s has been updated', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                'body'      => sprintf( __('<p>Hello {contact_name},</p>
                            <p>Your %s, {page_title} has been updated | <a href="{page_id}">Click HERE to visit</a></p>
                            <p>Thanks, and please contact us if you experience any difficulties,</p>
                            <p>{business_name}</p>', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] )
            );

            //email when Admin/Manager uploaded file
            $wpc_default_templates['templates_emails']['new_file_for_client_staff'] = array(
                'subject'   => 'New file at {site_title}',
                'body'      => '<p>You have been give access to a file at {site_title}</p>
                            <p>Click <a href="{login_url}">HERE</a> to access the file.</p>',
            );

            //email when Client registered
            $wpc_default_templates['templates_emails']['new_client_registered'] = array(
                'subject'   => 'A new client has registered on your site | {site_title}',
                'body'      => '<p>To approve this new client, you will need to login and navigate to > Clients > <strong><a href="{approve_url}">Approve Clients</a></strong></p>',
            );

            //email to Admin and Managers when Client uploaded the file
            $wpc_default_templates['templates_emails']['client_uploaded_file'] = array(
                'subject'   => 'The user {user_name} uploaded a file at {site_title}',
                'body'      => '<p>The user {user_name} uploaded a file. To view/download the file, click <a href="{admin_file_url}">HERE</a>"</p>',
            );

            //email to Admin and Managers when Client downloaded the file
            $wpc_default_templates['templates_emails']['client_downloaded_file'] = array(
                'enable'    => '0',
                'subject'   => 'The user {user_name} downloaded a file {file_name} at {site_title}',
                'body'      => '<p>The user {user_name} downloaded a file "{file_name}"."</p>',
            );

            //email when Staff created
            $wpc_default_templates['templates_emails']['staff_created'] = array(
                'subject'   => 'Your Staff account has been created',
                'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>You have been granted access to a private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Client registered Staff
            $wpc_default_templates['templates_emails']['staff_registered'] = array(
                'subject'   => 'Your Staff account has been registered',
                'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>You have been granted access to our private and secure Client Portal. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Manager created
            $wpc_default_templates['templates_emails']['manager_created'] = array(
                'subject'   => 'Your Manager account has been created',
                'body'      => '<p>Hello {contact_name},<br /> <br /> Your Username is : <strong>{user_name}</strong> and Password is : <strong>{user_password}</strong></p>
                <p>Your manager account has been created. You can login by clicking <strong><a href="{admin_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Admin send message to Client
            $wpc_default_templates['templates_emails']['notify_client_about_message'] = array(
                'subject'   => 'A user: {user_name} from {site_title} has sent you a private message',
                'body'      => '<p>A user: {user_name} has sent you a private message. To see the message login <a href="{login_url}">here</a>.</p>',
            );

            //email when Client send message to CC
            $wpc_default_templates['templates_emails']['notify_cc_about_message'] = array(
                'subject'   => "A new private message from {user_name}, sent from '{site_title}'",
                'body'      => '<p>{user_name} says,
                            <br/>
                            {message}
                            </p>',
            );

            //email when Client send message to Admin/Manager
            $wpc_default_templates['templates_emails']['notify_admin_about_message'] = array(
                'subject'   => "You've received a new private message from {user_name}, sent from '{site_title}'",
                'body'      => '<p>{user_name} says,
                            <br/>
                            {message}
                            <br/>
                            <br/>
                            To view the entire thread of messages and send a reply, click <a href="{admin_url}">HERE</a></p>',
            );

            //email when Client approved
            $wpc_default_templates['templates_emails']['account_is_approved'] = array(
                'subject'   => 'Your account is approved',
                'body'      => '<p>Hello {contact_name},<br /> <br /> Your account is approved.</p>
                <p>You can login by clicking <strong><a href="{login_url}">HERE</a></strong></p>
                <p>Thanks, and please contact us if you experience any difficulties,</p>
                <p>{business_name}</p>',
            );

            //email when Client reset it`s password
            $wpc_default_templates['templates_emails']['reset_password'] = array(
                'subject'   => '[{blog_name}]Password Reset',
                'body'      => '<p>Hi {user_name},</p>
                            <p>You have requested to reset your password.</p>
                            <p>Please follow the link below.</p>
                            <p><a href="{reset_address}">Reset Your Password</a></p>
                            <p>Thanks,</p>
                            <p>{business_name}</p>',
            );

            //email when updated Private Post type page
            $wpc_default_templates['templates_emails']['private_post_type'] = array(
                'subject'   => 'You have been given access to {page_title}',
                'body'      => '<p>Hello {contact_name},</p>
                            <p>You have been given access to {page_title} | <a href="{page_id}">Click HERE to visit</a></p>
                            <p>Thanks, and please contact us if you experience any difficulties,</p>
                            <p>{business_name}</p>',
            );


            //Set templates
            foreach( $wpc_default_templates as $key => $values ) {

                add_option( 'wpc_' . $key, $values );


                if ( is_array( $values ) && count( $values ) ) {

                    $current_setting = get_option( 'wpc_' . $key );
                        if ( is_array( $current_setting ) ) {
                            /*if( $key == 'ez_hub_templates' ) {
                                $new_setting = $current_setting + $values;
                            } elseif( $key == 'ez_hub_default' ) {
                                continue;
                            } else { */
                                $new_setting = array_merge( $values, $current_setting );
                            //}
                        } else {
                            $new_setting = $values;
                        }

                    update_option( 'wpc_' . $key, $new_setting );
                }
            }


            /*
            *  Simple HUB default
            */

            $content = '<h2 style="text-align: left;">Hi {contact_name}! Welcome to your private portal!</h2>
<p style="text-align: left;">{logout_link_6} &lt; Click here to logout</p>
<p style="text-align: left;">From this HUB Page, you can access all the pages and other resources that have been assigned to you.</p>


<hr />

<h2 dir="ltr" style="text-align: left;">Your Portal Pages</h2>
<p dir="ltr" style="text-align: left;">{pages_access_1}</p>


<hr />

<em><strong>&gt;&gt; Delete the instructional tips below before your Portal is live &lt;&lt;</strong></em>

<hr />

<h2 dir="ltr" style="text-align: left;">Customize this HUB Page template to fit your needs</h2>
<p dir="ltr" style="text-align: left;">The above layout is only a sample. You can use whatever layout you like.</p>
<p dir="ltr" style="text-align: left;">You can rename, remove or reformat any headings or text.</p>
<p dir="ltr" style="text-align: left;">You can remove any parts that you don`t need.</p>
<p style="text-align: left;">See below for tips on how you can modify various components.</p>
<p style="text-align: left;">When you are ready, you can simply delete this instructional section.</p>
<p style="text-align: left;">Shortcodes referenced below use {curly brackets} instead [square brackets] to keep them from inserting the components.</p>
<p style="text-align: left;">In actual use, you should use [square brackets]</p>


<hr />

<h2 dir="ltr">TIP: Advanced HUB VS EZ HUB</h2>
<ul>
    <li style="text-align: justify;">The items addressed below involving shortcodes only apply to the Advanced HUB Template. If you do not wish to use these, you can opt for the EZ HUB approach. The core of these EZ HUB Templates is the EZ HUB Navigation Bar. The EZ Bar allows the Client/Member to find the resources they need using an intuitive drop-down select box. The items that appear in the EZ Bar are completely customizable to fit your specific needs.</li>
    <li style="text-align: justify;">You can create EZ and Advanced HUB Templates from the HUB Templates menu, and assign them as you see fit to your Clients/Members and Circles.</li>
</ul>
<h2 dir="ltr">TIP: Displaying Portal Pages that Clients/Members have access to</h2>
<ul>
    <li style="text-align: justify;">Use “categories=” to display Portal Pages only from a specific Portal Page category. For example, the shortcode {wpc_client_pagel categories="Recreation"} would only display Portal Pages from the “Recreation” category</li>
    <li style="text-align: justify;">Use “show_categories_titles=” to determine if you want the Portal Page category titles displayed next to the name of the Portal Page</li>
    <li style="text-align: justify;">Use “sort=” and “sort_type=” to determine how you would like the page listing to be sorted. For example {wpc_client_pagel sort_type="date" sort="desc"} would display the Portal Page list sorted by date in descending order</li>
    <li style="text-align: justify;">Use “show_current_page=” to determine if you would like to display the current page the client/member is on in the listing of available Portal Pages. This is not necessary if you are displaying the list of Portal Pages on a HUB Page. For example, let’s say a client/member has access to 3 Portal Pages (Alpha, Bravo, and Delta). On Portal Page Alpha, you include the shortcode {wpc_client_pagel}, which displays a list of Portal Pages. Since the client/member is already on Portal Page Alpha, they do not necessarily need to see a link to that page in the list. If you add the modifier “show_current_page="no”” to the shortcode, it will exclude Portal Page Alpha from the list, as Alpha is the page the client/member is on currently.</li>
</ul>
<h2 dir="ltr">TIP: Displaying private info for one Client/Member or Circle</h2>
<ul>
    <li style="text-align: justify;">Use this shortcode: {wpc_client_private for="" for_circle=""}{/wpc_client_private}</li>
    <li style="text-align: justify;">This shortcode can be used to display unique information for a particular Client/Member or Circle. Simply place this shortcode into your HUB Template, and the information between the brackets will only be displayed for the correctly permissioned Client/Member or Circle. You can even do this for multiple Client/Members or Circles in the same Template. See below for an example:</li>
    <li style="text-align: justify;">This feature offers an exciting new way to think about your HUB Page template and/or any other Portal Page that you are creating to be part of your portal. Now, you can place content for many different Circles on one page, and only show the content that a particular Circle is supposed to see to those who are part of that Circle.</li>
    <li style="text-align: justify;">This powerful feature lets you essentially create multiple Hub Page variations, each one unique to its’ unique Client Circle. Simply wrap each variation of Hub Page code in the appropriate “private for” short code and stack them on top of each other in the Hub Page template and the appropriate hub page will be shown to each Client depending on their Client Circle affiliation. This same effect can be achieved by creating  separate Advanced HUB or EZ HUB templates for each Client Circle and assigning those templates to those Circles.</li>
</ul>
<p style="padding-left: 30px;">For example… see the below  as a simple example…. users in Circle Alpha will only see ‘Elephants are Green’ while those in Circle Charlie will see ‘Elephants are Blue’, and so on…</p>
<p style="padding-left: 30px;">———  Works on any HUB, Portal Page or native WordPress page/post ———-</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Alpha"}</p>
<p style="padding-left: 30px;">Elephants are Green</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Bravo"}</p>
<p style="padding-left: 30px;">Elephants are Red</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Charlie"}</p>
<p style="padding-left: 30px;">Elephants are Blue</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Delta"}</p>
<p style="padding-left: 30px;">Elephants are Purple</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">———  Works on any HUB, Portal Page or native WordPress page/post ———-</p>

<h2>Find other Tips in the Help menu</h2>';


                $tabs_content = '
<h2 style="text-align: left;">Hi {contact_name}! Welcome to your private portal!</h2>
<p style="text-align: left;">[wpc_client_logoutb/] &lt; Click here to logout</p>
<p style="text-align: left;">From this HUB Page, you can access all the pages, documents, photos &amp; files that you have access to.</p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your ' . $this->custom_titles['portal']['p'] . '</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_pagel categories="IDs|names" show_categories_titles="yes|no" show_current_page="no|yes" sort_type="date|title" sort="asc|desc" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Files</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_filesla show_sort="yes|no" show_date="yes|no" show_size="yes|no show_tags="yes|no" category="" no_text="" exclude_author="no|yes" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Uploaded Files</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_fileslu show_sort="yes|no" show_date="yes|no" show_size="yes|no" show_tags="yes|no" category="" no_text="" /]</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Upload Files Here</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_uploadf category="ID|name" /]</p>
<p dir="ltr" style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Private Messages</h2>
<p dir="ltr" style="text-align: left;">[wpc_client_com redirect_after="" /]</p>
<hr />
<p><em><strong>&gt;&gt; Delete the instructional tips below before your Portal is live &lt;&lt;</strong></em></p>
<hr />
<h2 dir="ltr" style="text-align: left;">Customize this HUB Page template to fit your needs</h2>
<p dir="ltr" style="text-align: left;">The above layout is only a sample. You can use whatever layout you like.</p>
<p dir="ltr" style="text-align: left;">You can rename, remove or reformat any headings or text. </p>
<p dir="ltr" style="text-align: left;">You can remove any parts that you don`t need.</p>
<p style="text-align: left;">See below for tips on how you can modify various components.</p>
<p style="text-align: left;">When you are ready, you can simply delete this instructional section.</p>
<p style="text-align: left;">Shortcodes referenced below use {curly brackets} instead [square brackets] to keep them from inserting the components.</p>
<p style="text-align: left;">In actual use, you should use [square brackets]</p>
<hr />
<h2 dir="ltr">TIP: Advanced HUB VS EZ HUB</h2>
<ul>
<li style="text-align: justify;">The items addressed below involving shortcodes only apply to the Advanced HUB Template. If you do not wish to use these, you can opt for the EZ HUB approach. The core of these EZ HUB Templates is the EZ HUB Navigation Bar. The EZ Bar allows the Client/Member to find the resources they need using an intuitive drop-down select box. The items that appear in the EZ Bar are completely customizable to fit your specific needs.</li>
<li style="text-align: justify;">You can create EZ and Advanced HUB Templates from the HUB Templates menu, and assign them as you see fit to your Clients/Members and Circles.</li>
</ul>
<h2 dir="ltr"> </h2>
<h2 dir="ltr">TIP: Displaying ' . $this->custom_titles['portal']['p'] . ' that Clients/Members have access to</h2>
<ul>
<li style="text-align: justify;">Use “categories=” to display ' . $this->custom_titles['portal']['p'] . ' only from a specific ' . $this->custom_titles['portal']['s'] . ' category. For example, the shortcode {wpc_client_pagel categories="Recreation"} would only display ' . $this->custom_titles['portal']['p'] . ' from the “Recreation” category</li>
<li style="text-align: justify;">Use “show_categories_titles=” to determine if you want the ' . $this->custom_titles['portal']['s'] . ' category titles displayed next to the name of the ' . $this->custom_titles['portal']['s'] . '</li>
<li style="text-align: justify;">Use “sort=” and “sort_type=” to determine how you would like the page listing to be sorted. For example {wpc_client_pagel sort_type="date" sort="desc"} would display the ' . $this->custom_titles['portal']['s'] . ' list sorted by date in descending order</li>
<li style="text-align: justify;">Use “show_current_page=” to determine if you would like to display the current page the client/member is on in the listing of available ' . $this->custom_titles['portal']['p'] . '. This is not necessary if you are displaying the list of ' . $this->custom_titles['portal']['p'] . ' on a HUB Page. For example, let’s say a client/member has access to 3 ' . $this->custom_titles['portal']['p'] . ' (Alpha, Bravo, and Delta). On ' . $this->custom_titles['portal']['s'] . ' Alpha, you include the shortcode {wpc_client_pagel}, which displays a list of ' . $this->custom_titles['portal']['p'] . '. Since the client/member is already on ' . $this->custom_titles['portal']['s'] . ' Alpha, they do not necessarily need to see a link to that page in the list. If you add the modifier “show_current_page="no”” to the shortcode, it will exclude ' . $this->custom_titles['portal']['s'] . ' Alpha from the list, as Alpha is the page the client/member is on currently.</li>
</ul>
<p style="text-align: left;"> </p>
<h2 dir="ltr">TIP: Displaying Files that Clients/Members have access to</h2>
<ul>
<li style="text-align: justify;">Use “show_sort=” to determine whether to display a sorting option for the clients/members to use</li>
<li style="text-align: justify;">Use “show_date=” to determine whether to display the date that the file was uploaded</li>
<li style="text-align: justify;">Use “show_size=” to determine whether to display the size of the file, in kilobytes (K)</li>
<li style="text-align: justify;">Use “show_tags=” to determine whether to display the file tags</li>
<li style="text-align: justify;">Use “category=” to only display files from a certain File Category. For example, {wpc_client_filesla category="Work"} would only display files from the “Work” File Category</li>
<li style="text-align: justify;">Use “exclude_author=” to choose to display files the client/member has uploaded, in addition to files that have been uploaded/assigned to them by the admin. For example, {wpc_client_filesla exclude_author="yes"} would display files that have been uploaded/assigned to the client/member by the admin, but it would not display files the client/member has uploaded themselves</li>
</ul>
<p style="text-align: left;"> </p>
<h2 dir="ltr">TIP: Displaying Files that Clients/Members have uploaded</h2>
<ul>
<li style="text-align: justify;">Use “show_sort=” to determine whether to display a sorting option for the clients/members to use</li>
<li style="text-align: justify;">Use “show_date=” to determine whether to display the date that the file was uploaded</li>
<li style="text-align: justify;">Use “show_size=” to determine whether to display the size of the file, in kilobytes (K)</li>
<li style="text-align: justify;">Use “show_tags=” to determine whether to display the file tags</li>
<li style="text-align: justify;">Use “category=” to only display files from a certain File Category. For example, {wpc_client_filesla category="Work"} would only display files from the “Work” File Category</li>
</ul>
<p dir="ltr"> </p>
<h2 dir="ltr">TIP: Adjusting the File Upload Form</h2>
<ul>
<li style="text-align: justify;">Use “category=” Use “category=” to only allow files to be uploaded to a certain File Category. For example, {wpc_client_uploadf category="Work"} would automatically assign all uploaded files to the “Work” File Category</li>
</ul>
<p style="text-align: left;"> </p>
<h2 dir="ltr">TIP: Adjusting the Private Messaging Form</h2>
<ul>
<li style="text-align: justify;">Use “redirect_after=” to redirect the client/member to a specific URL after sending a private message. For example, {wpc_client_com redirect_after="http://exampledomain.com/home/"} would redirect the client/member to the installation home page after sending a private message.</li>
</ul>
<h2 dir="ltr"> </h2>
<h2 dir="ltr">TIP: Displaying Feedback Wizard</h2>
<ul>
<li style="text-align: justify;">To display a list of Feedback Wizards available to the client/member, you will first need to install and activate the Feedback Wizard extension in the Extensions menu. After that, simply place this shortcode in the client/member’s HUB Page: {wpc_client_feedback_wizards_list}</li>
</ul>
<h2 dir="ltr"> </h2>
<h2 dir="ltr">TIP: Displaying private info for one Client/Member or Circle</h2>
<ul>
<li style="text-align: justify;">Use this shortcode: {wpc_client_private for="" for_circle=""}{/wpc_client_private}</li>
<li style="text-align: justify;">This shortcode can be used to display unique information for a particular Client/Member or Circle. Simply place this shortcode into your HUB Template, and the information between the brackets will only be displayed for the correctly permissioned Client/Member or Circle. You can even do this for multiple Client/Members or Circles in the same Template. See below for an example:</li>
<li style="text-align: justify;">This feature offers an exciting new way to think about your HUB Page template and/or any other ' . $this->custom_titles['portal']['s'] . ' that you are creating to be part of your portal. Now, you can place content for many different Circles on one page, and only show the content that a particular Circle is supposed to see to those who are part of that Circle.</li>
<li style="text-align: justify;">This powerful feature lets you essentially create multiple Hub Page variations, each one unique to its’ unique Client Circle. Simply wrap each variation of Hub Page code in the appropriate “private for” short code and stack them on top of each other in the Hub Page template and the appropriate hub page will be shown to each Client depending on their Client Circle affiliation. This same effect can be achieved by creating  separate Advanced HUB or EZ HUB templates for each Client Circle and assigning those templates to those Circles.</li>
</ul>
<p style="padding-left: 30px;">For example… see the below  as a simple example…. users in Circle Alpha will only see ‘Elephants are Green’ while those in Circle Charlie will see ‘Elephants are Blue’, and so on…</p>
<p style="padding-left: 30px;">———  Works on any HUB, ' . $this->custom_titles['portal']['s'] . ' or native WordPress page/post ———-</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Alpha"}</p>
<p style="padding-left: 30px;">Elephants are Green</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Bravo"}</p>
<p style="padding-left: 30px;">Elephants are Red</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Charlie"}</p>
<p style="padding-left: 30px;">Elephants are Blue</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">{wpc_client_private for_circle="Circle Delta"}</p>
<p style="padding-left: 30px;">Elephants are Purple</p>
<p style="padding-left: 30px;">{/wpc_client_private}</p>
<p style="padding-left: 30px;">———  Works on any HUB, ' . $this->custom_titles['portal']['s'] . ' or native WordPress page/post ———-</p>
<p style="padding-left: 30px;"> </p>
<h2>Find other Tips in the Help menu</h2>';




            $old_templates = get_option( 'wpc_ez_hub_templates' );

            $wpc_ez_hub_default = array(
                '1' => array(
                    'pages_access' => array(
                        'show_current_page'         => 'yes',
                        'sort_type'                 => 'date',
                        'sort'                      => 'asc',
                        'show_categories_titles'    => 'yes',
                    )
                ),
                '2' => array(
                    'files_uploaded' => array(
                        'show_sort'                 => 'yes',
                        'show_date'                 => 'yes',
                        'show_size'                 => 'yes',
                        'show_tags'                 => 'yes',
                        'category'                  => '',
                    )
                ),
                '3' => array(
                    'files_access' => array(
                        'show_sort'                 => 'yes',
                        'show_date'                 => 'yes',
                        'show_size'                 => 'yes',
                        'show_tags'                 => 'yes',
                        'category'                  => '',
                        'exclude_author'            => 'yes',
                    )
                ),
                '4' => array(
                    'upload_files' => array(
                        'category'                  => '',
                    )
                ),
                '5' => array(
                    'private_messages' => array(
                        'show_number'               => 25,
                        'show_more_number'          => 25,
                        'show_filters'              => 'no',
                    ),
                ),
                '6' => array(
                    'logout_link' => array(
                    ),
                )
            );

            $tmp_id = time();

            $wpc_ez_default_templates = array(
                $tmp_id => array(
                    'name'              => 'Simple Template',
                    'type'              => 'advanced',
                    'not_delete'        => true,
                    'is_default'        => 1,
                    )
                );

            $target_path = $this->get_upload_dir( 'wpclient/_hub_templates/' );

            if( $old_templates ) {
                $id_simple_temlate = $this->get_id_simple_temlate() ;

                if ( $id_simple_temlate ) {
                    //for update version with $id_simple_temlate

                    if ( is_dir( $target_path ) ) {
                        if ( !file_exists( $target_path . $id_simple_temlate . '_hub_content.txt' ) ) {
                            $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
                            fwrite( $content_file, $content );
                            fclose( $content_file );
                        }

                        if( !file_exists( $target_path . $id_simple_temlate . '_hub_tabs_content.txt' ) ) {
                            $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
                            fwrite( $tabs_content_file, $tabs_content );
                            fclose( $tabs_content_file );
                        }
                    }
                } elseif( !isset( $old_templates['default'] ) ) {
                    //for update version without $id_simple_temlate

                    $wpc_ez_templates = array_merge( $old_templates, $wpc_ez_default_templates );

                    update_option( 'wpc_ez_hub_templates', $wpc_ez_templates ) ;
                    update_option( 'wpc_ez_hub_' . $tmp_id, $wpc_ez_hub_default ) ;

                    if ( is_dir( $target_path ) ) {

                        $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
                        fwrite( $content_file, $content );
                        fclose( $content_file );

                        $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
                        fwrite( $tabs_content_file, $tabs_content );
                        fclose( $tabs_content_file );
                    }
                }
            } else {
                //for new install
                update_option( 'wpc_ez_hub_templates', $wpc_ez_default_templates ) ;
                update_option( 'wpc_ez_hub_' . $tmp_id, $wpc_ez_hub_default ) ;

                if ( is_dir( $target_path ) ) {

                    $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
                    fwrite( $content_file, $content );
                    fclose( $content_file );

                    $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
                    fwrite( $tabs_content_file, $tabs_content );
                    fclose( $tabs_content_file );
                }

            }





        }


        /*
        * Updating to new version
        */
        function updating( $ver ) {
            global $wpdb;

            //for very old installs
            if ( version_compare( $ver, '1.0.0', '<' ) ) {

            }

            update_option( 'wp_client_lite_ver', WPC_CLIENT_LITE_VER );
        }


    //end class
    }

}

?>