{crmScope extensionKey='dataprocessor'}
    {* block for rule data *}
    <h3>{ts}Import data processor{/ts}</h3>
	<div class="crm-block crm-form-block crm-data-processor_source-block">	{$form.uploadFile.html}</div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}