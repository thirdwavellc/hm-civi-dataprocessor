{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <h3>{ts}Output configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_output-block">
        <div class="crm-section">
            <div class="label">{$form.api_entity.label}</div>
            <div class="content">{$form.api_entity.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.api_action.label}</div>
            <div class="content">{$form.api_action.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.api_count_action.label}</div>
            <div class="content">{$form.api_count_action.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.api_permission.label}</div>
            <div class="content">{$form.api_permission.html}</div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}