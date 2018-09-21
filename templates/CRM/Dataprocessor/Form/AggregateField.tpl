{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts}Are you sure to remove this field?{/ts}</div>
    </div>
{else}

    {* block for rule data *}
    <h3>{ts}Aggregation Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source-block">
        <div class="crm-section">
            <div class="label">{$form.field.label}</div>
            <div class="content">{$form.field.html}</div>
            <div class="clear"></div>
        </div>
    </div>
{/if}

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}