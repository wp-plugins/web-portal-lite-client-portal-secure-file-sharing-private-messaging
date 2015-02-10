<div class="registration_form" id="registration_form">

    <div id="wpc_registration_message" class="wpc_registration_updated" {if empty($error) } style="display: none;" {/if}>
        {if !empty($error)}
            {$error}
        {/if}
    </div>

    <form action="" method="post" id="form_content" >

        <input name="wpc_self_registered" type="hidden" value="1" />

        <p class="business_name">
            <label class="title" for="business_name">{$labels.business_name}{$required_text}</label>
            <input type="text" id="business_name" name="business_name" value="{if $error }{$vals.business_name}{/if}" />
        </p>

        <p class="contact_name">
            <label class="title" for="contact_name">{$labels.contact_name}{$required_text}</label>
            <input type="text" id="contact_name" name="contact_name" value="{if $error }{$vals.contact_name}{/if}" />
        </p>
        <p class="contact_email">
            <label class="title" for="contact_email">{$labels.contact_email}{$required_text}</label>
            <input type="text" id="contact_email" name="contact_email" value="{if $error }{$vals.contact_email}{/if}" />
        </p>

        <p class="contact_phone">
            <label class="title" for="contact_phone">{$labels.contact_phone}</label>
            <input type="text" id="contact_phone" name="contact_phone" value="{if $error }{$vals.contact_phone}{/if}" />
        </p>


        {if isset($custom_fields) && 0 < $custom_fields|@count }
            {foreach $custom_fields as $key => $value }

                {if 'hidden' == $value.type}
                    {$value.field}
                {elseif 'checkbox' == $value.type || 'radio' == $value.type }
                    <p>
                        {if !empty($value.label) }
                            {$value.label}
                        {/if}
                        {if !empty($value.field) }
                            {foreach $value.field as $field }
                                {$field}<label class="title">&nbsp;</label>
                            {/foreach}
                        {/if}
                        {if !empty($value.description) }
                            {$value.description}
                        {/if}
                    </p>
                {else}
                    <p>
                        {if !empty($value.label) }
                            {$value.label}
                        {/if}
                        {if !empty($value.field) }
                            {$value.field}
                        {/if}
                        {if !empty($value.description) }
                            {$value.description}
                        {/if}
                    </p>
                {/if}

            {/foreach}
        {/if}

        {if !empty($custom_html)}
            {$custom_html}
        {/if}

        <hr class="wpc_delimiter" />

        <p class="contact_username">
            <label class="title" for="contact_username">{$labels.contact_username}{$required_text}</label>
            <input type="text" id="contact_username" name="contact_username" value="{if $error }{$vals.contact_username}{/if}" />
        </p>

        <p class="contact_password">
            <label class="title" for="contact_password">{$labels.contact_password}{$required_text}</label>
            <input type="password" id="contact_password" name="contact_password" value="" />
        </p>

        <p class="contact_password2">
            <label class="title" for="contact_password2">{$labels.contact_password2}</label>
            <input type="password" id="contact_password2" name="contact_password2" value="" />
        </p>

        <div id="pass-strength-result">{$labels.password_indicator}</div>
        <div class="indicator-hint">{$labels.password_hint}</div>

        <p class="send_password">
            <label for="send_password">>> {$labels.send_password} >> <input type="checkbox" {if $vals.send_password == 1 } checked {/if} name="user_data[send_password]" id="send_password" value="1" /> {$labels.send_password_desc}</label>
        </p>

        <div id="wpc_block_captcha">{if isset($labels.captcha)}{$labels.captcha}{/if}</div>

        {if isset( $terms_used ) && $terms_used == 'yes'}
            <div id="wpc_block_terms">
                <label class="terms_label" for="terms_agree"><input type="checkbox" id="terms_agree" name="terms_agree" value="1" {$vals.terms_default_checked}/>&nbsp;&nbsp;&nbsp;&nbsp;{$labels.terms_agree}</label>
                <a href="{$vals.terms_hyperlink}" target="_blank" title="Terms/Conditions">Terms/Conditions</a>
            </div>
        {/if}

        <p class="btnAdd">
            <input type='submit' name='btnAdd' id="btnAdd" class='button-primary' value='{$labels.send_button}' />
        </p>
    </form>
</div>
