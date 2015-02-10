<div class="wpc_client_client_pages">

    {if !empty($message)}
    <span id="message" class="updated fade">{$message}</span><br />
    {/if}

    {if !empty($add_staff_url)}
    <strong><a href="{$add_staff_url}">{$add_staff_text}</a></strong><br />
    {/if}

    {if !empty($staff_directory_url)}
    <strong><a href="{$staff_directory_url}">{$staff_directory_text}</a></strong><br /><br /><br />
    {/if}

    {if isset( $show_sort ) && true == $show_sort}
        {$sort_by_text}
        <br />
        {$time_added_text}
        <br />
        [<a href="javascript:void(0);" class="sort_date_asc">{$asc_text}</a>] [<a href="javascript:void(0);" class="sort_date_desc">{$desc_text}</a>]
        <br />
        {$name_text}
        <br />
        [<a href="javascript:void(0);" class="sort_title_asc">{$asc_text}</a>] [<a href="javascript:void(0);" class="sort_title_desc">{$desc_text}</a>]
        <br />
        <br />
    {/if}

    {if !empty($pages)}
        {if $show_category_name}
            {foreach from=$pages item=category key=category_name}
                <br />
                <strong>{$category_name}</strong><br />
                <div class="wpc_client_portal_page_category" id="category_{$category_name}">
                    {foreach $category as $page}
                        <span class="wpc_page_item"><a data-timestamp="{$page.creation_date}" href="{$page.url}">{$page.title}</a>{if !empty($page.edit_link)}
                            [<a href="{$page.edit_link}" >Edit</a>]
                        {/if}</span><br />
                    {/foreach}
                </div>
            {/foreach}
        {else}
            <div class="wpc_client_portal_page_category" id="category_general">
                {foreach $pages as $page}
                    <span class="wpc_page_item"><a data-timestamp="{$page.creation_date}" href="{$page.url}">{$page.title}</a>{if !empty($page.edit_link)}
                        [<a href="{$page.edit_link}" >Edit</a>]
                    {/if}</span><br />
                {/foreach}
            </div>
        {/if}
    {/if}

    <style type='text/css'>
    {literal}
        .navigation .alignleft, .navigation .alignright {display:none;}
    {/literal}
    </style>

</div>