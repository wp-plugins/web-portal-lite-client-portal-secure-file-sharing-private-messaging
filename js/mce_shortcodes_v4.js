//for tinymce v4

(function() {

    tinymce.PluginManager.add( 'WPC_Client_Shortcodes', function( editor, url ) {


               /**
               * Adds HTML tag to selected content
               */
               editor.addButton( 'wpc_client_button_shortcodes', {
                    title : 'Insert Placeholders & Shortcode',
                    type : 'menubutton',
                    menu: [
                        {
                            text: 'Placeholders: General',
                            value: '',
                            onclick: function() {
                                editor.insertContent(this.value());
                            },
                            menu: [
                                {
                                    text: '{site_title}',
                                    value: '{site_title}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{contact_name}',
                                    value: '{contact_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{client_business_name}',
                                    value: '{client_business_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{client_name}',
                                    value: '{client_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{client_phone}',
                                    value: '{client_phone}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{client_email}',
                                    value: '{client_email}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{client_registration_date}',
                                    value: '{client_registration_date}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{user_name}',
                                    value: '{user_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{login_url}',
                                    value: '{login_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{logout_url}',
                                    value: '{logout_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{manager_name}',
                                    value: '{manager_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{staff_display_name}',
                                    value: '{staff_display_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{staff_first_name}',
                                    value: '{staff_first_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{staff_last_name}',
                                    value: '{staff_last_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{staff_email}',
                                    value: '{staff_email}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{staff_login}',
                                    value: '{staff_login}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                }
                            ]

                        },
                        {
                            text: 'Placeholders: Business',
                            value: '',
                            onclick: function() {
                                editor.insertContent(this.value());
                            },
                            menu: [
                                {
                                    text: '{business_logo_url}',
                                    value: '{business_logo_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_name}',
                                    value: '{business_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_address}',
                                    value: '{business_address}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_mailing_address}',
                                    value: '{business_mailing_address}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_website}',
                                    value: '{business_website}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_email}',
                                    value: '{business_email}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_phone}',
                                    value: '{business_phone}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{business_fax}',
                                    value: '{business_fax}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                }
                            ]
                        },
                        {
                            text: 'Placeholders: Specific',
                            value: '',
                            onclick: function() {
                                editor.insertContent(this.value());
                            },
                            menu: [
                                {
                                    text: '{admin_url}',
                                    value: '{admin_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{approve_url}',
                                    value: '{approve_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{user_password}',
                                    value: '{user_password}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{page_title}',
                                    value: '{page_title}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{admin_file_url}',
                                    value: '{admin_file_url}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{message}',
                                    value: '{message}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{file_name}',
                                    value: '{file_name}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{file_category}',
                                    value: '{file_category}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{estimate_number}',
                                    value: '{estimate_number}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: '{invoice_number}',
                                    value: '{invoice_number}',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                }
                            ]
                        },
                        {
                            text: 'Shortcodes: Pages',
                            value: '',
                            onclick: function() {
                                editor.insertContent(this.value());
                            },
                            menu: [
                                {
                                    text: 'Redirect on Login or HUB',
                                    value: '[wpc_redirect_on_login_hub]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Login Form',
                                    value: '[wpc_client_loginf no_redirect="true|false" no_redirect_text=""]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'HUB Page',
                                    value: '[wpc_client_hub_page]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Portal Page',
                                    value: '[wpc_client_portal_page]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Edit Portal Page',
                                    value: '[wpc_client_edit_portal_page]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Staff Directory',
                                    value: '[wpc_client_staff_directory]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Add Staff',
                                    value: '[wpc_client_add_staff_form]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Edit Staff',
                                    value: '[wpc_client_edit_staff_form]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Client Registration',
                                    value: '[wpc_client_registration_form no_redirect="true|false" no_redirect_text=""]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Successful Client Registration',
                                    value: '[wpc_client_registration_successful]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Client Profile',
                                    value: '[wpc_client_profile]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Feedback Wizard (Addon)',
                                    value: '[wpc_client_feedback_wizard]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Feedback Wizard List (Addon)',
                                    value: '[wpc_client_feedback_wizards_list]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Invoicing (Addon)',
                                    value: '[wpc_client_invoicing]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Invoicing List (Addon)',
                                    value: '[wpc_client_invoicing_list type="invoice|estimate" status="new|inprocess|partial|paid" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Shutter Galleries List',
                                    value: '[wpc_client_shutter_galleries_list show_categories_titles="yes|no" show_creation_date="yes|no" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Shutter Gallery',
                                    value: '[wpc_client_shutter_gallery gallery_id="" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Shutter Shopping Cart',
                                    value: '[wpc_client_shutter_shopping_cart type="popup|link|page" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Shutter Items',
                                    value: '[wpc_client_shutter_my_items type="link|page" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                }
                            ]
                        },
                        {
                            text: 'Shortcodes: Others',
                            value: '',
                            onclick: function() {
                                editor.insertContent(this.value());
                            },
                            menu: [
                                {
                                    text: 'Page Url',
                                    value: '[wpc_client_get_page_link page="hub|login|client_registration|feedback_wizard_list|invoicing_list|staff_directory|add_staff" text="Some Link" id="" class="" style="" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'wpc_client',
                                    value: '[wpc_client]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Logout Link',
                                    value: '[wpc_client_logoutb]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Private Content',
                                    value: '[wpc_client_private for="" for_circle="" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Images URL',
                                    value: '[wpc_client_theme]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Files Client Have Access To',
                                    value: '[wpc_client_filesla show_sort="yes|no" show_tags="yes|no" show_date="yes|no" show_size="yes|no" no_text="" category="" with_subcategories="yes|no" view_type="list|table" show_file_cats="yes|no" show_last_download_date="yes|no" show_actions="yes|no" show_thumbnails="yes|no" show_search="yes|no" show_filters="yes|no" show_pagination="yes|no" show_pagination_by="5" exclude_author="yes|no" /]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Files Client Have Uploaded',
                                    value: '[wpc_client_fileslu show_sort="yes|no" show_tags="yes|no" show_date="yes|no" show_size="yes|no" no_text="" category="" with_subcategories="yes|no" view_type="list|table" show_file_cats="yes|no" show_last_download_date="yes|no" show_actions="yes|no" show_thumbnails="yes|no" show_search="yes|no" show_filters="yes|no" show_pagination="yes|no" show_pagination_by="5" /]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'File Upload Form',
                                    value: '[wpc_client_uploadf category="ID|name" auto_upload="yes|no" exclude="FILE EXTENSIONS HERE (for example: jpg,png,gif)"  include="FILE EXTENSIONS HERE (for example: jpg,png,gif)" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'List of Portal Portals',
                                    value: '[wpc_client_pagel categories="IDs|names" show_categories_titles="yes|no" show_current_page="no|yes" sort_type="date|title" sort="asc|desc" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Private Messages',
                                    value: '[wpc_client_com redirect_after="" show_filters="no|yes" show_number="10" show_more_number="10"]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Graphic',
                                    value: '[wpc_client_graphic]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'List of Private Post Type Pages (Addon)',
                                    value: '[wpc_client_private_post_types  post_type_filter="" sort_type="date|title" sort="asc|desc" ]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Client Managers',
                                    value: '[wpc_client_client_managers]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Custom Field',
                                    value: '[wpc_client_custom_field name="Field Slug(ID)"]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                },
                                {
                                    text: 'Custom Field Value',
                                    value: '[wpc_client_custom_field_value name="Field Slug(ID)" delimiter=", " no_value="None"]',
                                    onclick: function(e) {
                                        e.stopPropagation();
                                        editor.insertContent(this.value());
                                    }
                                }
                            ]
                        }
                   ]

               });



		}
	);

}
)();