{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <h3>{ts}Output configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_output-block">
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
            <div class="label">{$form.activity_id_field.label}</div>
            <div class="content">{$form.activity_id_field.html}</div>
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
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}