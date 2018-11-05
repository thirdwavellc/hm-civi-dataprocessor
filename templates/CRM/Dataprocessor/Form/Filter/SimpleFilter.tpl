{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

<h3>{ts}Filter configuration{/ts}</h3>
<div class="crm-block crm-form-block crm-data-processor_source-block">
    <div class="crm-section">
        <div class="label">{$form.field.label}</div>
        <div class="content">{$form.field.html}</div>
        <div class="clear"></div>
    </div>
</div>

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}