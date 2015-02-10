<?php

$error = "";

//reset simple template to default
if( isset( $_REQUEST['id'] ) && isset( $_REQUEST['reset'] ) && 'true' == $_REQUEST['reset'] && $_REQUEST['id'] == $this->get_id_simple_temlate() ) {

    $wpc_default_templates = $this->cc_get_settings( 'ez_hub_templates' );

    $wpc_default_templates[ $_REQUEST['id'] ]['name'] = 'Simple Template';
    $content = '
<h2 style="text-align: left;">Hi {contact_name}! Welcome to your private portal!</h2>
<p style="text-align: left;">{logout_link_6} &lt; Click here to logout</p>
<p style="text-align: left;">From this HUB Page, you can access all the pages, documents, photos &amp; files that you have access to.</p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your ' . $this->custom_titles['portal']['p'] . '</h2>
<p dir="ltr" style="text-align: left;">{pages_access_1}</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Files</h2>
<p dir="ltr" style="text-align: left;">{files_uploaded_2}</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Your Uploaded Files</h2>
<p dir="ltr" style="text-align: left;">{files_access_3}</p>
<p style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Upload Files Here</h2>
<p dir="ltr" style="text-align: left;">{upload_files_4}</p>
<p dir="ltr" style="text-align: left;"> </p>
<hr />
<h2 dir="ltr" style="text-align: left;">Private Messages</h2>
<p dir="ltr" style="text-align: left;">{private_messages_5}</p>
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
<p dir="ltr" style="text-align: left;">[wpc_client_filesla show_sort="yes|no" show_date="yes|no" show_size="yes|no" show_tags="yes|no" category="" no_text="" exclude_author="no|yes" /]</p>
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

    update_option( 'wpc_ez_hub_templates', $wpc_default_templates );

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

    update_option( 'wpc_ez_hub_' . $_REQUEST['id'], $wpc_ez_hub_default );


    $target_path = $this->get_upload_dir( 'wpclient/_hub_templates/' );

    if ( is_dir( $target_path ) ) {
        $content_file = fopen( $target_path . $_REQUEST['id'] . '_hub_content.txt', 'w+' );
        fwrite( $content_file, $content );
        fclose( $content_file );

        $tabs_content_file = fopen( $target_path . $_REQUEST['id']  . '_hub_tabs_content.txt', 'w+' );
        fwrite( $tabs_content_file, $tabs_content );
        fclose( $tabs_content_file );
    }

    do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $_REQUEST['id'] );
    exit;

}


//save user data
if ( isset( $_REQUEST['update_hub_template'] ) ) {
    // validate at php side

    if ( !isset( $_REQUEST['hub_template']['name'] ) || empty( $_REQUEST['hub_template']['name'] ) ) {
        $error .= __('A Template Name is required.<br/>', WPC_CLIENT_TEXT_DOMAIN);
    }

    if ( empty( $error ) ) {
        $wpc_ez_hub_templates = $this->cc_get_settings( 'ez_hub_templates' );

        if ( isset( $_REQUEST['id'] ) && '' != $_REQUEST['id'] ) {
            $tmp_id = $_REQUEST['id'];
        } else {
            $tmp_id = time();
        }

        $wpc_ez_hub_settings = ( isset( $_REQUEST['hub_settings'] ) ) ? $_REQUEST['hub_settings'] : array();

        do_action( 'wp_client_settings_update', $wpc_ez_hub_settings, 'ez_hub_' . $tmp_id );

        $wpc_ez_hub_templates[$tmp_id]['name']              = $_REQUEST['hub_template']['name'];
        //$wpc_ez_hub_templates[$tmp_id]['content']           = $tmp_id . '_hub_content' ;
        $wpc_ez_hub_templates[$tmp_id]['general']           = $_REQUEST['hub_template']['general'];
        $wpc_ez_hub_templates[$tmp_id]['type']              = 'advanced';
        //$wpc_ez_hub_templates[$tmp_id]['tabs_content']      = $tmp_id . '_hub_tabs_content' ;

        $content = $_REQUEST['hub_template']['content'] ;

        $target_path = $this->get_upload_dir( 'wpclient/_hub_templates/' );

        if ( is_dir( $target_path ) ) {

            $content_file = fopen( $target_path . $tmp_id . '_hub_content.txt', 'w+' );
            fwrite( $content_file, $content );
            fclose( $content_file );

            $tabs_content_file = fopen( $target_path . $tmp_id . '_hub_tabs_content.txt', 'w+' );
            fwrite( $tabs_content_file, wpc_get_advanced_hub_content( $tmp_id, $content ) );
            fclose( $tabs_content_file );
        }



        do_action( 'wp_client_settings_update', $wpc_ez_hub_templates, 'ez_hub_templates' );

        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_advanced_template&id=' . $tmp_id . '&msg=u' );
        exit;
    }

}

function wpc_get_advanced_hub_content( $template_id, $content ) {
    global $wpc_client;

    $hub_settings = $wpc_client->cc_get_settings( 'ez_hub_' . $template_id );

    if ( !is_array( $hub_settings ) || !count( $hub_settings ) ) {
        $ez_hub_default = $this->get_id_simple_temlate();
        $hub_settings = $wpc_client->cc_get_settings( 'ez_hub_' . $ez_hub_default );
    }

    if( is_array( $hub_settings ) && 0 < count( $hub_settings ) ) {

        foreach( $hub_settings as $key=>$value ) {

            $placeholder_name = array_keys($value);
            $placeholder_values = array_values($value);

            $placeholder_content = apply_filters( 'wpc_client_get_ez_shortcode_' . $placeholder_name[0], array(), $placeholder_values[0] );

            if( isset( $placeholder_content ) && 0 < count( $placeholder_content ) ) {
                $placeholder_content = $placeholder_content[0]['page_body'];

                $content = str_replace( '{' . $placeholder_name[0] . '_' . $key . '}', $placeholder_content, $content );
            }
        }
    }
    return $content;
}

//change text
if ( 'add_advanced_template' == $_GET['action'] )
    $button_text = __( 'Add Advanced HUB Template', WPC_CLIENT_TEXT_DOMAIN );
else
    $button_text =  __( 'Update Advanced HUB Template', WPC_CLIENT_TEXT_DOMAIN );



if ( isset( $_REQUEST['hub_template'] ) ) {
    $hub_template      = $_REQUEST['hub_template'];
    $hub_settings      = $_REQUEST['hub_settings'];
} elseif ( isset( $_REQUEST['id'] ) && '' != $_REQUEST['id'] ) {

    $wpc_ez_hub_templates   = $this->cc_get_settings( 'ez_hub_templates' );
    $hub_template           = isset( $wpc_ez_hub_templates[$_REQUEST['id']] ) ? $wpc_ez_hub_templates[$_REQUEST['id']]: array();

    $hub_settings           = $this->cc_get_settings( 'ez_hub_' . $_REQUEST['id'] );

    if( isset( $hub_template['type'] ) && 'ez' == $hub_template['type'] && 'edit_advanced_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_ez_template&id=' . $_REQUEST['id'] );
        exit;
    } elseif( isset( $hub_template['type'] ) && 'simple' == $hub_template['type'] && 'edit_advanced_template' == $_GET['action'] ) {
        do_action( 'wp_client_redirect', admin_url() . 'admin.php?page=wpclients_templates&tab=hubpage&action=edit_simple_template&id=' . $_REQUEST['id'] );
        exit;
    }

} else {

    $hub_settings = array(
        1 => array(
            'pages_access' => array(),
        ),
        2 => array(
            'files_uploaded' => array(),
        ),
        3 => array(
            'files_access' => array(),
        ),
        4 => array(
            'upload_files' => array(),
        ),
        5=> array(
            'private_messages' => array(),
        ),
        6 => array(
            'logout_link' => array(),
        ),


    );
}


$elements = array(
    'pages_access' => __( 'Pages you have access to', WPC_CLIENT_TEXT_DOMAIN ),
    'files_uploaded' => __( 'Files you have uploaded', WPC_CLIENT_TEXT_DOMAIN ),
    'files_access' => __( 'Files you have access to', WPC_CLIENT_TEXT_DOMAIN ),
    'upload_files' => __( 'Upload Files', WPC_CLIENT_TEXT_DOMAIN ),
    'private_messages' => __( 'Private Messages', WPC_CLIENT_TEXT_DOMAIN ),
    'logout_link' => __( 'Logout Link', WPC_CLIENT_TEXT_DOMAIN ),
);

$elements = apply_filters( 'wpc_client_get_shortcode_elements', $elements );

?>

<h2><?php echo $button_text ?></h2>

<div id="message" class="error wpc_notice fade" <?php echo ( empty( $error ) )? 'style="display: none;" ' : '' ?> ><?php echo $error; ?></div>


    <form name="edit_hub_template" id="edit_hub_template" method="post" >

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2 not_bold">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label for="hub_template_name"><strong><?php _e( 'Template Name', WPC_CLIENT_TEXT_DOMAIN ) ?> <span class="description"><?php _e( '(required)', WPC_CLIENT_TEXT_DOMAIN ) ?></span>:</strong></label>
                            <br />
                            <input type="text" name="hub_template[name]" id="hub_template_name" value="<?php echo ( isset( $hub_template['name'] ) ? stripslashes( html_entity_decode( $hub_template['name'] ) ) : '' )?>" class="max_width" />
                        </div>
                    </div>
                    <br />
                    <div id="postdivrich" class="postarea edit-form-section">
                        <label for="hub_template_content"><strong><?php _e( 'HUB Content', WPC_CLIENT_TEXT_DOMAIN ) ?>:</strong></label>
                        <div>
                            <div style="float: left; margin: 0px 20px 0px 0px;" class="validate_page_icon_attention"></div>
                            <span class="description">
                            <?php _e( '<b>NOTE:</b> Just click the "Copy" button next to each placeholder, then paste it in the Visual Editor to insert the corresponding element.', WPC_CLIENT_TEXT_DOMAIN ) ?>
                            </span>
                        </div>
                        <br>
                        <div class="postarea">

                        <span class="description"><?php printf ( __( 'Use the space below to design a HUB Template in the same manner you would design a standard WordPress page, and then add/place %s components by placing the appropriate placeholders below in your desired location. You can use the Visual Editor, or write HTML in the Text Editor. Use the appropriate placeholder for each element below to insert that functionality into the page.', WPC_CLIENT_TEXT_DOMAIN ), $this->plugin['title'] ) ?></span>


                        <?php
                            $settings = array( 'textarea_name' => 'hub_template[content]',  'media_buttons' => false, 'textarea_rows' => 15  );

                            $hub_template_content = '';
                            if ( isset( $_GET['id'] ) ) {
                                $id_hub = $_GET['id'] ;
                                $handle = fopen( $this->get_upload_dir( 'wpclient/_hub_templates/' ) . $id_hub . '_hub_content.txt', 'rb' );
                                if ( $handle !== false ) {
                                    rewind( $handle ) ;
                                    while ( !feof( $handle ) ) {
                                        $hub_template_content .= fread( $handle, 8192 );
                                    }
                                }
                                fclose( $handle );
                                $hub_template_content = stripslashes( html_entity_decode( $hub_template_content ) ) ;
                            } elseif ( isset( $_REQUEST['hub_template']['content'] ) ) {
                                $hub_template_content = $_REQUEST['hub_template']['content'];
                            }

                            wp_editor( $hub_template_content, 'hub_template_content', $settings );
                        ?>

                        </div>
                    </div>
                </div><!-- #post-body-content -->
                <div id="postbox-container-1" class="postbox-container">

                    <div id="side-info-column" class="inner-sidebar">
                        <?php
                            do_meta_boxes( 'wp_client_edit_advanced_hub', 'side', ( isset( $hub_template ) ) ? $hub_template : array() ) ;
                        ?>
                    </div>
                 </div>
                 <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes( 'wp_client_edit_advanced_hub', 'normal', array( 'hub_settings' => $hub_settings, 'elements' => $elements) ); ?>
                </div>
            </div><!-- #post-body -->
        </div> <!-- #poststuff -->

    </form>
<script type="text/javascript" language="javascript">
    var site_url = '<?php echo site_url();?>';

    jQuery( document ).ready( function( $ ) {

        var client = new ZeroClipboard( jQuery( ".wpc_shortcode_clip_button" ) );

        client.on( "ready", function( readyEvent ) {

            client.on( "aftercopy", function( event ) {
                jQuery( event.target ).siblings('.wpc_complete_copy').fadeIn('slow');
                var obj = jQuery( event.target ).siblings( '.wpc_complete_copy' );
                setTimeout( function() {
                    obj.fadeOut('slow');
                }, 2500 );
            });
        });

    });

</script>
