<?php


if ( !class_exists( "WPC_Client_Customize" ) ) {

    class WPC_Client_Customize extends WPC_Client_Admin_Common {


        var $all_properties = array(
            'background-color'              => 'color',
            'background-color-transparent'  => 'option',
            'color'                         => 'color',
            'font-family'                   => 'select',
            'font-size'                     => 'size',
            'font-size-unit'                => 'unit',
            'font-line-height'              => 'size',
            'font-line-height-unit'         => 'unit',
            'display'                       => 'select',
            'float'                         => 'select',
            'width'                         => 'size',
            'width-unit'                    => 'unit',
            'height'                        => 'size',
            'height-unit'                   => 'unit',
            'margin-top'                    => 'spinner',
            'margin-left'                   => 'spinner',
            'margin-right'                  => 'spinner',
            'margin-bottom'                 => 'spinner',
            'padding-top'                   => 'spinner',
            'padding-left'                  => 'spinner',
            'padding-right'                 => 'spinner',
            'padding-bottom'                => 'spinner',
            'border-color'                  => 'color',
            'border-style'                  => 'select',
            'border-top-width'              => 'spinner',
            'border-left-width'             => 'spinner',
            'border-right-width'            => 'spinner',
            'border-bottom-width'           => 'spinner'
        );


        /**
        * PHP 5 constructor
        **/
        function __construct() {

        }


        /*
        * default
        * default
        */
        function get_default_sections() {

            $sections = array(

                'general' => array(
                    'wpc_toolbar' => array(
                        'title' => 'HUB Toolbar',
                        'css_id' => '.wpc-toolbar',
                        'properties' => array(
                        ),
                    ),
                    'dropdown_menu' => array(
                        'title' => 'HUB Toolbar Dropdown',
                        'css_id' => '.wpc-toolbar .dropdown-menu',
                        'properties' => array(
                        ),
                    ),
                    'dropdown_menu_a' => array(
                        'title' => 'HUB Toolbar Dropdown Link',
                        'css_id' => '.wpc-toolbar .dropdown-menu a',
                        'properties' => array(
                        ),
                    ),
                    'wpc_toolbar_pull_right_a' => array(
                        'title' => 'HUB Toolbar Logout Link',
                        'css_id' => '.wpc-toolbar pull-right a',
                        'properties' => array(
                        ),
                    ),
                ),

                'page_access' => array(
                    'wpc_client_client_pages' => array(
                        'title' => 'Page Access Block',
                        'css_id' => '.wpc_client_client_pages',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_client_pages_a' => array(
                        'title' => sprintf( __( '%s Links', WPC_CLIENT_TEXT_DOMAIN ), $this->custom_titles['portal']['s'] ),
                        'css_id' => '.wpc_client_client_pages a',
                        'properties' => array(
                        ),
                    ),
                ),

                'files_access' => array(
                    'wpc_client_files' => array(
                        'title' => 'Files Block',
                        'css_id' => '.wpc_client_files',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_files_a' => array(
                        'title' => 'Sort Links',
                        'css_id' => '.wpc_client_files a',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_files_file_item' => array(
                        'title' => 'File Item Block',
                        'css_id' => '.wpc_client_files .file_item',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_files_file_item_a' => array(
                        'title' => 'File Link',
                        'css_id' => '.wpc_client_files .file_item a',
                        'properties' => array(
                        ),
                    ),
                ),

                'files_upload_form' => array(
                    'wpc_client_upload_form' => array(
                        'title' => 'Upload Block',
                        'css_id' => '.wpc_client_upload_form',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_upload_form_form' => array(
                        'title' => 'Upload Form Block',
                        'css_id' => '.wpc_client_upload_form form',
                        'properties' => array(
                        ),
                    ),
                ),

                'messages_form' => array(
                    'wpc_client_message_form' => array(
                        'title' => 'Message Form',
                        'css_id' => '.wpc_client_message_form',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_message_form_submit' => array(
                        'title' => 'Submit Button',
                        'css_id' => '.wpc_client_message_form #submit',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_messages' => array(
                        'title' => 'Messages Table',
                        'css_id' => '.wpc_client_messages',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_message_author' => array(
                        'title' => 'Author Text',
                        'css_id' => '.wpc_client_message_author',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_message_time' => array(
                        'title' => 'Time Text',
                        'css_id' => '.wpc_client_message_time',
                        'properties' => array(
                        ),
                    ),
                    'wpc_client_message' => array(
                        'title' => 'Message Text',
                        'css_id' => '.wpc_client_message',
                        'properties' => array(
                        ),
                    ),
                ),

            );

            return $sections;
        }

        /*
        * Get style schemes
        */
        function get_style_schemes() {

            $default_style_schemes = array(
                '_default_scheme' => array(
                    'title' => 'Default Scheme',
                    'reset' => true,
                    'modify' => false,
                )
            );

            $style_schemes = $this->cc_get_settings( 'style_schemes_settings' );

            if ( function_exists( 'array_replace_recursive' ) ) {
                $style_schemes = array_replace_recursive( $default_style_schemes, $style_schemes );
            } else {
                $style_schemes = $this->__array_replace_recursive( $default_style_schemes, $style_schemes );
            }

            return $style_schemes;

        }


        /*
        * Get sections header
        */
        function get_sections_header( $scheme ) {

            $sections = $this->get_default_sections();

            $wpc_style_settings = $this->cc_get_settings( 'style_' . $scheme . '_sections' );

            if ( function_exists( 'array_replace_recursive' ) ) {
                $sections = array_replace_recursive( $sections, $wpc_style_settings );
            } else {
                $sections = $this->__array_replace_recursive( $sections, $wpc_style_settings );
            }

            $content = '<div style="margin: 0px 0px 10px 20px; width: 260px;"><b>' . __( 'Section', WPC_CLIENT_TEXT_DOMAIN ) . ':</b> <select id="select_sections">';

            foreach( $sections as $section_name => $section_values ) {
                $content .= '<option value="' . $section_name . '">' . trim( ucwords( str_replace( array( '_', '-' ), ' ', $section_name ) ) ) . '</option>';
            }

            $content .= '</select><span id="wpc_edit_section"></span>
            <span class="wpc_customize_button_add" id="wpc_section_add" title="' . __( 'Add Section', WPC_CLIENT_TEXT_DOMAIN ) . '"></span>
            <span class="wpc_customize_button_save" id="wpc_section_save" style="display: none;" title="' . __( 'Save Section', WPC_CLIENT_TEXT_DOMAIN ) . '"></span>
            <span class="wpc_customize_button_cancel" id="wpc_section_cancel" style="display: none;" title="' . __( 'Cancel Edit', WPC_CLIENT_TEXT_DOMAIN ) . '"></span>
            <span class="wpc_customize_button_edit" id="wpc_section_edit" title="' . __( 'Edit Section', WPC_CLIENT_TEXT_DOMAIN ) . '"></span>
            <span class="wpc_customize_button_delete" id="wpc_section_delete" title="' . __( 'Delete Section', WPC_CLIENT_TEXT_DOMAIN ) . '"></span>
            </div>';

            return $content;
        }


        /*
        * Get section
        */
        function _get_sections( $scheme ) {

            $sections = $this->get_default_sections();

            $wpc_style_settings = $this->cc_get_settings( 'style_' . $scheme . '_sections' );

            if ( function_exists( 'array_replace_recursive' ) ) {
                $sections = array_replace_recursive( $sections, $wpc_style_settings );
            } else {
                $sections = $this->__array_replace_recursive( $sections, $wpc_style_settings );
            }

            return $sections;

        }


        /*
        * Get section
        */
        function get_sections( $scheme ) {

            $sections = $this->get_default_sections();

            $wpc_style_settings = $this->cc_get_settings( 'style_' . $scheme . '_sections' );

            if ( function_exists( 'array_replace_recursive' ) ) {
                $sections = array_replace_recursive( $sections, $wpc_style_settings );
            } else {
                $sections = $this->__array_replace_recursive( $sections, $wpc_style_settings );
            }

            $sections = $sections;



            $i = 0;
            foreach( $sections as $section_name => $section_values ) {
                $style = ( $i ) ? 'style="display: none;"' : '';
                $i = 1;

                echo '<ul class="section_block" id="section-' . $section_name . '" ' . $style . ' >';

                foreach( $section_values as $key => $section ) {

                  echo $this->render_element_section( $key, $section, $section_name );

                }
                echo '</ul>';
            }

            return '';
                ?>


                <?php

        }


        /*
        *
        */
        function render_element_section( $element_name, $element, $section_name ) {
            if ( !is_array( $element ) || !count( $element ) )
                return '';

            $element_id = $section_name . '_' . $element_name . '_' . rand( 100, 1000);
            $form_name = "wpc_style_settings[{$section_name}][{$element_name}][properties]";

            $prop = $element['properties'];

            ob_start();

            ?>
            <li class="control-section accordion-section" data-eID="<?php echo $element_id ?>" id="accordion-section-colors">
                <h3 tabindex="0" class="accordion-section-title">
                    <span id="h3_element_title_<?php echo $element_id ?>"><?php echo $element['title'] ?></span>
                    <span class="wpc_element_button_delete" title="<?php _e( 'Delete Element', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                    <span class="wpc_customize_eye"></span>
                </h3>
                <input type="hidden" name="wpc_style_settings[<?php echo $section_name ?>][<?php echo $element_name ?>][title]" id="<?php echo $element_id ?>_title" value="<?php echo $element['title'] ?>" />
                <input type="hidden" name="wpc_style_settings[<?php echo $section_name ?>][<?php echo $element_name ?>][css_id]" id="<?php echo $element_id ?>_css_id" value="<?php echo $element['css_id'] ?>" />

                <?php
                foreach( $this->all_properties as $key => $type ) {
                    $value = ( isset( $prop[$key] ) && '!no' != $prop[$key] && '' != $prop[$key] ) ? $prop[$key] : '';
                ?>
                    <input type="hidden" name="wpc_style_settings[<?php echo $section_name ?>][<?php echo $element_name ?>][properties][<?php echo $key ?>]" id="<?php echo $element_id ?>_<?php echo $key ?>" value="<?php echo $value ?>" />
                <?php

                }
                ?>

                <ul class="accordion-section-content">
                    <li><div style="width: 1px; height: 830px;"></div></li>
                </ul>

            </li>
            <?php

            $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            return $content;

        }




        function _element_html() {

            ob_start();

            ?>
            <li class="control-section accordion-section" data-eID="{element_id}" id="accordion-section-colors">
                <h3 tabindex="0" class="accordion-section-title">
                    <span id="h3_element_title_{element_id}">{title}</span>
                    <span class="wpc_element_button_delete" title="<?php _e( 'Delete Element', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                    <span class="wpc_customize_eye"></span>
                </h3>
                <input type="hidden" name="wpc_style_settings[{section_key}][{element_key}][title]" id="{element_id}_title" value="{title}" />
                <input type="hidden" name="wpc_style_settings[{section_key}][{element_key}][css_id]" id="{element_id}_css_id" value="{css_id}" />

                <?php
                foreach( $this->all_properties as $key => $type ) {
                ?>
                    <input type="hidden" name="wpc_style_settings[{section_key}][{element_key}][properties][<?php echo $key ?>]" id="{element_id}_<?php echo $key ?>" value="{value_<?php echo $key ?>}" />
                <?php
                    if ( 'size' == $type ) {
                ?>
                    <input type="hidden" name="wpc_style_settings[{section_key}][{element_key}][properties][<?php echo $key ?>-unit]" id="{element_id}_<?php echo $key ?>-unit" value="{value_<?php echo $key ?>-unit}" />
                <?php
                    }
                }
                ?>

                <ul class="accordion-section-content">
                    <li><div style="width: 1px; height: 830px;"></div></li>
                </ul>

            </li>
            <?php

            $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            return $content;

        }



        function _editor_html() {

            ob_start();

            ?>
                <ul class="accordion-section-content">
                    <li>
                        <span class="customize-control-title"><?php _e( 'Element', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                            <span id="wpc_editor_element_title"></span>
                            <span id="wpc_edit_element_title"></span>
                            <span class="wpc_element_title_button_save" id="wpc_element_title_save" style="display: none;" title="<?php _e( 'Save Title', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_element_title_button_cancel" id="wpc_element_title_cancel" style="display: none;" title="<?php _e( 'Cancel Edit', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                            <span class="wpc_element_title_button_edit" id="wpc_element_title_edit" title="<?php _e( 'Edit Element Title', WPC_CLIENT_TEXT_DOMAIN ) ?>"></span>
                        </span>
                    </li>
                    <li>
                        <span class="customize-control-title"><?php _e( 'Element CSS ID', WPC_CLIENT_TEXT_DOMAIN ) ?>:
                            <br>
                            <span id="wpc_editor_element_css_id"></span>
                        </span>
                        <hr />
                    </li>
                    <li>
                        <label for="editor_color"><span class="customize-control-title"><?php _e( 'Background:', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>

                        <input class="wpc_colors" name="editor_background-color" type="text" id="editor_background-color" value="" data-color-type="background" data-default-color="">
                        <label><input type="checkbox" name="editor_background-color-transparent" id="editor_background-color-transparent" value="1" /> <?php _e( 'Transparent', WPC_CLIENT_TEXT_DOMAIN ) ?></label>

                        <hr />
                    </li>
                    <li>
                        <label><span class="customize-control-title"><?php _e( 'Font:', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>

                        <input  class="wpc_colors" name="editor_color" type="text" id="editor_color" value="" data-color-type="font" data-default-color="">

                        <br clear="all" />

                        <label for="editor_font-family"><?php _e( 'Font Family:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <select class="wpc_font_family" style="width: 115px;" name="editor_font-family" id="editor_font-family">
                                <option id="!no"></option>
                                <?php
                                $fonts = array( 'Arial', 'Times New Roman', 'Verdana', 'Helvetica', 'Times', 'serif', 'sans-serif' );
                                foreach( $fonts as $font ) {
                                    echo '<option id="' . $font . '">' . $font . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />

                        <label for="editor_font-size"><?php _e( 'Font Size:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <input class="wpc_spinner_size" data-sType="font-size" name="editor_font-size" id="editor_font-size" value="" />

                            <select class="wpc_size_unit" data-sType="font-size" name="editor_font-size-unit" id="editor_font-size-unit">
                                <option id="!no"></option>
                                <?php
                                $units = array( 'px', 'em', 'pt', '%' );
                                foreach( $units as $unit ) {
                                    echo '<option id="' . $unit . '">' . $unit . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />

                        <label for="editor_line-height"><?php _e( 'Line Height:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <input class="wpc_spinner_size" data-sType="line-height" name="editor_line-height" id="editor_line-height" value="" />

                            <select class="wpc_size_unit" data-sType="line-height" name="editor_line-height-unit" id="editor_line-height-unit">
                                <option id="!no"></option>
                                <?php
                                $units = array( 'px', 'em', 'pt', '%' );
                                foreach( $units as $unit ) {
                                    echo '<option id="' . $unit . '">' . $unit . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />
                        <hr />
                    </li>
                    <li>
                        <span class="customize-control-title"><?php _e( 'Block:', WPC_CLIENT_TEXT_DOMAIN ) ?></span>

                        <label for="editor_display"><?php _e( 'Display:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <select style="width: 115px;" name="editor_display" id="editor_display">
                                <option id="!no"></option>
                                <?php
                                $displays = array( 'Block', 'Inherit', 'Inline', 'Inline-Block', 'List-Item', 'None', 'Table' , 'Table-Row' , 'Table-Cell' , 'Table-Column' );
                                foreach( $displays as $display ) {
                                    echo '<option id="' . strtolower( $display ) . '">' . $display . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />

                        <label for="editor_float"><?php _e( 'Float:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <select style="width: 115px;" name="editor_float" id="editor_float">
                                <option id="!no"></option>
                                <?php
                                $floats = array( 'Inherit', 'Left', 'None', 'Right' );
                                foreach( $floats as $float ) {
                                    echo '<option id="' . strtolower( $float ) . '">' . $float . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />

                        <label for="editor_width"><?php _e( 'Width:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <input class="wpc_spinner_size" data-sType="width" name="editor_width" id="editor_width" value="" />

                            <select class="wpc_size_unit" data-sType="width" name="editor_width-unit" id="editor_width-unit">
                                <option id="!no"></option>
                                <?php
                                $units = array( 'px', 'em', 'pt', '%' );
                                foreach( $units as $unit ) {
                                    echo '<option id="' . $unit . '">' . $unit . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />

                        <label for="editor_height"><?php _e( 'Height:', WPC_CLIENT_TEXT_DOMAIN ) ?></label>
                        <span style="float: right;">
                            <input class="wpc_spinner_size" data-sType="height" name="editor_height" id="editor_height" value="" />

                            <select class="wpc_size_unit" data-sType="height" name="editor_height-unit" id="editor_height-unit">
                                <option id="!no"></option>
                                <?php
                                $units = array( 'px', 'em', 'pt', '%' );
                                foreach( $units as $unit ) {
                                    echo '<option id="' . $unit . '">' . $unit . '</option>';
                                }
                                ?>
                            </select>
                        </span>

                        <br clear="all" />
                        <hr />
                    </li>
                    <li>
                        <label for="editor_margin"><span class="customize-control-title"><?php _e( 'Margin:', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>

                        <div class="wpc_cstmz_s_t">
                            <input class="wpc_spinner" data-sType="m" name="editor_margin-top" id="editor_margin-top" value="" />
                        </div>
                        <div class="wpc_cstmz_s_l">
                            <input class="wpc_spinner" data-sType="m" name="editor_margin-left" id="editor_margin-left" value="" />
                        </div>
                        <div class="wpc_cstmz_s_r">
                            <input class="wpc_spinner" data-sType="m" name="editor_margin-right" id="editor_margin-right" value="" />
                        </div>
                        <div class="wpc_cstmz_s_b">
                            <input class="wpc_spinner" data-sType="m" name="editor_margin-bottom" id="editor_margin-bottom" value="" />
                        </div>
                        <hr />
                    </li>
                    <li>
                        <label for="editor_margin"><span class="customize-control-title"><?php _e( 'Padding:', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>

                        <div class="wpc_cstmz_s_t">
                            <input class="wpc_spinner" data-sType="p" name="editor_padding-top" id="editor_padding-top" value="" />
                        </div>
                        <div class="wpc_cstmz_s_l">
                            <input class="wpc_spinner" data-sType="p" name="editor_padding-left" id="editor_padding-left" value="" />
                        </div>
                        <div class="wpc_cstmz_s_r">
                            <input class="wpc_spinner" data-sType="p" name="editor_padding-right" id="editor_padding-right" value="" />
                        </div>
                        <div class="wpc_cstmz_s_b">
                            <input class="wpc_spinner" data-sType="p" name="editor_padding-bottom" id="editor_padding-bottom" value="" />
                        </div>
                        <hr />
                    </li>
                    <li>
                        <label for="editor_margin"><span class="customize-control-title"><?php _e( 'Border:', WPC_CLIENT_TEXT_DOMAIN ) ?></span></label>

                        <input  class="wpc_colors" name="editor_border-color" type="text" id="editor_border-color" value=""  data-color-type="border" data-default-color="">

                        <select class="wpc_border_style" name="editor_border-style" id="editor_border-style">
                            <option id="!no"></option>
                            <?php
                            $border_styles = array( 'dashed', 'dotted', 'double', 'groove', 'hidden', 'inherit', 'inset', 'none', 'outset', 'ridge', 'solid' );
                            foreach( $border_styles as $border_style ) {
                                echo '<option id="' . $border_style . '">' . ucwords( $border_style ) . '</option>';
                            }
                            ?>
                        </select>

                        <div class="wpc_cstmz_s_t">
                            <input class="wpc_spinner_border" name="editor_border-top-width" id="editor_border-top-width" value="" />
                        </div>
                        <div class="wpc_cstmz_s_l">
                            <input class="wpc_spinner_border" name="editor_border-left-width" id="editor_border-left-width" value="" />
                        </div>
                        <div class="wpc_cstmz_s_r">
                            <input class="wpc_spinner_border" name="editor_border-right-width" id="editor_border-right-width" value="" />
                        </div>
                        <div class="wpc_cstmz_s_b">
                            <input class="wpc_spinner_border" name="editor_border-bottom-width" id="editor_border-bottom-width" value="" />
                        </div>
                        <hr />
                    </li>

                </ul>
            <?php

            $content = ob_get_contents();
            if( ob_get_length() ) {
                ob_end_clean();
            }

            return $content;

        }


        /*
        *  reset style
        */
        function reset_style_settings() {

            do_action( 'wp_client_settings_update', array(), 'style_settings' );

            $target_path = $this->get_upload_dir();

            if ( file_exists( $target_path . 'wpc_custom_style.css' ) ) {
                unlink( $target_path . 'wpc_custom_style.css' );
            }

        }


        /*
        *  save Styles settings
        */
        function save_style_settings( $scheme_settings, $settings ) {

            $scheme_key = $scheme_settings['key'];
            unset( $scheme_settings['key'] );

            $wpc_style_schemes = $this->get_style_schemes();

            if ( isset( $wpc_style_schemes[$scheme_key] ) ) {
                $wpc_style_schemes[$scheme_key] = array_merge( $wpc_style_schemes[$scheme_key], $scheme_settings );
            } else {
                $def = array(
                    'title' => 'Custom Scheme',
                    'reset' => false,
                    'modify' => false,
                );

                $wpc_style_schemes[$scheme_key] = array_merge( $def, $scheme_settings );
            }

            // clear from empty styles
            if ( is_array( $settings ) && count( $settings ) ) {
                $keys = array_keys( $settings );
                foreach( $keys as $key ) {
                    foreach( $settings[$key] as $element => $element_values ) {
                        foreach( $element_values['properties'] as $prop => $prop_value ) {
                            if ( '' == $prop_value || '!no' == $prop_value ) {
                                unset( $settings[$key][$element]['properties'][$prop] );
                            }
                        }
                    }
                }
            } else {
                $settings = array();
            }


            do_action( 'wp_client_settings_update', $wpc_style_schemes, 'style_schemes_settings' );
            do_action( 'wp_client_settings_update', $settings, 'style_' . $scheme_key . '_sections' );

            //render CSS file
            $this->gen_css_file( $scheme_key, $settings );

        }


        /*
        *  Gen CSS
        */
        function gen_css_file( $scheme_key, $settings ) {

            $css_content = "/* WPC Custom Styles */ \n";

            if ( is_array( $settings ) && count( $settings ) ) {
                $keys = array_keys( $settings );
                foreach( $keys as $key ) {
                    foreach( $settings[$key] as $element => $element_values ) {

                        $properties = $this->preparing_properties( $element_values['properties'] );

                        if ( is_array( $properties ) && count( $properties )  ) {
                            $css_content .= $element_values['css_id'] . " {\n";

                            foreach( $properties as $prop => $prop_value ) {
                                $css_content .= "    " . $prop . ": " . $prop_value . ";\n";

                            }

                            $css_content .= "}\n\n";
                        }
                    }
                }
            }

            $target_path    = $this->get_upload_dir();

            if ( is_dir( $target_path ) ) {
                $css_file = fopen( $target_path . 'wpc_custom_style_' . $scheme_key . '.css', 'w+' );
                fputs( $css_file, $css_content );
            }

        }

        /*
        *  preparing properties
        */
        function preparing_properties( $properties ) {
            $rules = array (
                array(
                    'source' => 'width',
                    'target' => 'width-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'height',
                    'target' => 'height-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'font-size',
                    'target' => 'font-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'margin-top',
                    'target' => 'margin-top-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'margin-left',
                    'target' => 'margin-left-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'margin-right',
                    'target' => 'margin-right-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'margin-bottom',
                    'target' => 'margin-bottom-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'padding-top',
                    'target' => 'padding-top-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'padding-left',
                    'target' => 'padding-left-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'padding-right',
                    'target' => 'padding-right-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'padding-bottom',
                    'target' => 'padding-bottom-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'border-top-width',
                    'target' => 'border-top-width-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'border-left-width',
                    'target' => 'border-left-width-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'border-right-width',
                    'target' => 'border-right-width-unit',
                    'default' => 'px',
                ),
                array(
                    'source' => 'border-bottom-width',
                    'target' => 'border-bottom-width-unit',
                    'default' => 'px',
                ),
            );

            foreach( $rules as $rule ) {
                if ( isset( $properties[$rule['source']] ) ) {

                    if ( isset( $properties[$rule['target']] ) ) {
                        $properties[$rule['source']] .= $properties[$rule['target']];
                        unset( $properties[$rule['target']] );

                    } else {
                        $properties[$rule['source']] .= $rule['default'];
                    }
                }
            }

            return $properties;
        }


       /*
       * array replace recursive for PHP less then 5.3
       */
        function __array_replace_recursive() {
            // Get array arguments
            $arrays = func_get_args();

            // Define the original array
            $original = array_shift($arrays);

            // Loop through arrays
            foreach ($arrays as $array) {
                // Loop through array key/value pairs
                foreach ($array as $key => $value) {
                    // Value is an array
                    if (is_array($value)) {
                        // Traverse the array; replace or add result to original array
                        $original[$key] = $this->__array_replace_recursive($original[$key], $array[$key]);
                    }
                    // Value is not an array
                    else {
                        // Replace or add current value to original array
                        $original[$key] = $value;
                    }
                }
            }

            // Return the joined array
            return $original;
        }



    //end class
    }

}

?>