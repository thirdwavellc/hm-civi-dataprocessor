{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Data Processor{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts}Are you sure to delete data processor output?{/ts}</div>
    </div>
{else}

    {* block for rule data *}
    <h3>{ts}Data Processor Source{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_output-block">
        <div class="crm-section">
            <div class="label">{$form.type.label}</div>
            <div class="content">{$form.type.html}</div>
            <div class="clear"></div>
        </div>
    </div>
{/if}

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}