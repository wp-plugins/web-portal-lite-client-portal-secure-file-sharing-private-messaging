(function() {
	tinymce.create( "tinymce.plugins.WPC_Client_Shortcodes",
		{
			init: function( d,e ) {},
			createControl: function( d,e )
			{
				if ( 'wpc_client_button_shortcodes' == d ) {
					d = e.createMenuButton( 'wpc_client_button_shortcodes',{
						title: 'Insert Placeholders & Shortcode',
						icons: false
						});

						var a=this;
                        d.onRenderMenu.add( function( c, b ) {
                            c = b.addMenu( {title: 'Placeholders: General'} );
                                a.addImmediate( c, '{site_title}', '{site_title}' );
                                a.addImmediate( c, '{contact_name}', '{contact_name}' );
                                a.addImmediate( c, '{client_business_name}', '{client_business_name}' );
                                a.addImmediate( c, '{client_name}', '{client_name}' );
                                a.addImmediate( c, '{client_phone}', '{client_phone}' );
                                a.addImmediate( c, '{client_email}', '{client_email}' );
                                a.addImmediate( c, '{client_registration_date}', '{client_registration_date}' );
                                a.addImmediate( c, '{user_name}', '{user_name}' );
                                a.addImmediate( c, '{login_url}', '{login_url}' );
                                a.addImmediate( c, '{logout_url}', '{logout_url}' );
                                a.addImmediate( c, '{manager_name}', '{manager_name}' );
                                a.addImmediate( c, '{staff_display_name}', '{staff_display_name}' );
                                a.addImmediate( c, '{staff_first_name}', '{staff_first_name}' );
                                a.addImmediate( c, '{staff_last_name}', '{staff_last_name}' );
                                a.addImmediate( c, '{staff_email}', '{staff_email}' );
                                a.addImmediate( c, '{staff_login}', '{staff_login}' );

                            b.addSeparator();

                            c = b.addMenu( {title: 'Placeholders: Business'} );
                                a.addImmediate( c, '{business_logo_url}', '{business_logo_url}' );
                                a.addImmediate( c, '{business_name}', '{business_name}' );
                                a.addImmediate( c, '{business_address}', '{business_address}' );
                                a.addImmediate( c, '{business_mailing_address}', '{business_mailing_address}' );
                                a.addImmediate( c, '{business_website}', '{business_website}' );
                                a.addImmediate( c, '{business_email}', '{business_email}' );
                                a.addImmediate( c, '{business_phone}', '{business_phone}' );
                                a.addImmediate( c, '{business_fax}', '{business_fax}' );

                            b.addSeparator();

                                c = b.addMenu( {title: 'Placeholders: Specific'} );
                                a.addImmediate( c, '{admin_url}', '{admin_url}' );
                                a.addImmediate( c, '{approve_url}', '{approve_url}' );
                                a.addImmediate( c, '{user_password}', '{user_password}' );
                                a.addImmediate( c, '{page_title}', '{page_title}' );
                                a.addImmediate( c, '{admin_file_url}', '{admin_file_url}' );
                                a.addImmediate( c, '{message}', '{message}' );
                                a.addImmediate( c, '{file_name}', '{file_name}' );
                                a.addImmediate( c, '{file_category}', '{file_category}' );
                                a.addImmediate( c, '{estimate_number}', '{estimate_number}' );
                                a.addImmediate( c, '{invoice_number}', '{invoice_number}' );

                            b.addSeparator();

							c = b.addMenu( {title: 'Shortcodes: Pages'} );
                                a.addImmediate( c, 'Redirect on Login or HUB', '[wpc_redirect_on_login_hub]' );
                                a.addImmediate( c, 'Login Form', '[wpc_client_loginf no_redirect="true|false" no_redirect_text=""]' );
                                a.addImmediate( c, 'HUB Page', '[wpc_client_hub_page]' );
                                a.addImmediate( c, 'Portal Page', '[wpc_client_portal_page]' );
                                a.addImmediate( c, 'Edit Portal Page', '[wpc_client_edit_portal_page]' );
                                a.addImmediate( c, 'Staff Directory', '[wpc_client_staff_directory]' );
                                a.addImmediate( c, 'Add Staff', '[wpc_client_add_staff_form]' );
                                a.addImmediate( c, 'Edit Staff', '[wpc_client_edit_staff_form]' );
                                a.addImmediate( c, 'Client Registration', '[wpc_client_registration_form no_redirect="true|false" no_redirect_text=""]' );
                                a.addImmediate( c, 'Successful Client Registration', '[wpc_client_registration_successful]' );
                                a.addImmediate( c, 'Client Profile', '[wpc_client_profile]' );
                                a.addImmediate( c, 'Feedback Wizard (Addon)', '[wpc_client_feedback_wizard]' );
                                a.addImmediate( c, 'Feedback Wizard List (Addon)', '[wpc_client_feedback_wizards_list]' );
                                a.addImmediate( c, 'Invoicing (Addon)', '[wpc_client_invoicing]' );
                                a.addImmediate( c, 'Invoicing List (Addon)', '[wpc_client_invoicing_list type="invoice|estimate" status="new|inprocess|partial|paid" ]' );
                                a.addImmediate( c, 'Shutter Galleries List', '[wpc_client_shutter_galleries_list show_categories_titles="yes|no" show_creation_date="yes|no" ]' );
                                a.addImmediate( c, 'Shutter Gallery', '[wpc_client_shutter_gallery gallery_id="" ]' );
                                a.addImmediate( c, 'Shutter Shopping Cart', '[wpc_client_shutter_shopping_cart type="popup|link|page" ]' );
                                a.addImmediate( c, 'Shutter Items', '[wpc_client_shutter_my_items type="link|page" ]' );

                            b.addSeparator();

                            c = b.addMenu( {title: 'Shortcodes: Others'} );
                                a.addImmediate( c, 'Page Url', '[wpc_client_get_page_link page="hub|login|client_registration|feedback_wizard_list|invoicing_list|staff_directory|add_staff" text="Some Link" id="" class="" style="" ]' );
                                a.addImmediate( c, 'wpc_client', '[wpc_client]' );
                                a.addImmediate( c, 'Logout Link', '[wpc_client_logoutb]' );
                                a.addImmediate( c, 'Private Content', '[wpc_client_private for="" for_circle="" ]' );
                                a.addImmediate( c, 'Images URL', '[wpc_client_theme]' );
                                a.addImmediate( c, 'Files Client Have Access To', '[wpc_client_filesla show_tags="yes|no" show_sort="yes|no" show_date="yes|no" show_size="yes|no" no_text="" category="" with_subcategories="yes|no" view_type="list|table" show_file_cats="yes|no" show_last_download_date="yes|no" show_actions="yes|no" show_thumbnails="yes|no" show_search="yes|no" show_filters="yes|no" show_pagination="yes|no" show_pagination_by="5" exclude_author="yes|no" /]' );
                                a.addImmediate( c, 'Files Client Have Uploaded', '[wpc_client_fileslu show_tags="yes|no" show_sort="yes|no" show_date="yes|no" show_size="yes|no" no_text="" category="" with_subcategories="yes|no" view_type="list|table" show_file_cats="yes|no" show_last_download_date="yes|no" show_actions="yes|no" show_thumbnails="yes|no" show_search="yes|no" show_filters="yes|no" show_pagination="yes|no" show_pagination_by="5" /]' );
                                a.addImmediate( c, 'File Upload Form', '[wpc_client_uploadf category="ID|name" auto_upload="yes|no" exclude="FILE EXTENSIONS HERE (for example: jpg,png,gif)"  include="FILE EXTENSIONS HERE (for example: jpg,png,gif)" ]' );
                                a.addImmediate( c, 'List of Portal Portals', '[wpc_client_pagel categories="IDs|names" show_categories_titles="yes|no" show_current_page="no|yes" sort_type="date|title" sort="asc|desc" ]' );
                                a.addImmediate( c, 'Private Messages', '[wpc_client_com redirect_after="" show_filters="no|yes" show_number="10" show_more_number="10"]' );
                                a.addImmediate( c, 'Graphic', '[wpc_client_graphic]' );
                                a.addImmediate( c, 'List of Private Post Type Pages (Addon)', '[wpc_client_private_post_types  post_type_filter="" sort_type="date|title" sort="asc|desc" ]' );
                                a.addImmediate( c, 'Client Managers', '[wpc_client_client_managers]' );
                                a.addImmediate( c, 'Custom Field', '[wpc_client_custom_field name="Field Slug(ID)"]' );
                                a.addImmediate( c, 'Custom Field Value', '[wpc_client_custom_field_value name="Field Slug(ID)" delimiter=", " no_value="None"]' );
						});
					return d
				}
				return null
			},
			addImmediate: function( d, e, a ){ d.add({ title: e, onclick: function(){ tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, a )} }) }
		}
	);
	tinymce.PluginManager.add( 'WPC_Client_Shortcodes', tinymce.plugins.WPC_Client_Shortcodes );
}
)();