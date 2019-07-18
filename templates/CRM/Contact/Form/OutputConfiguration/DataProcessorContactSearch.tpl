{crmScope extensionKey='dataprocessor'}
    {include file='CRM/Contact/Form/OutputConfiguration/DashletConfiguration.tpl'}
    <div class="crm-section">
        <div class="label">{$form.title.label}</div>
        <div class="content">{$form.title.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.navigation_parent_path.label}</div>
        <div class="content">{$form.navigation_parent_path.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.permission.label}</div>
        <div class="content">{$form.permission.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.contact_id_field.label}</div>
        <div class="content">{$form.contact_id_field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.hide_id_field.label}</div>
        <div class="content">{$form.hide_id_field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.help_text.label}</div>
        <div class="content">{$form.help_text.html}</div>
        <div class="clear"></div>
    </div>
    
{/crmScope}