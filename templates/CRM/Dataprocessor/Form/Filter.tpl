{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts 1=$filter->title}Are you sure to delete filter '%1'?{/ts}</div>
    </div>
{else}

    {* block for rule data *}
    <h3>{ts}Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source-block">
        <div class="crm-section">
            <div class="label">{$form.type.label}</div>
            <div class="content">{$form.type.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.title.label}</div>
            <div class="content">{$form.title.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.name.label}</div>
            <div class="content">{$form.name.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.is_required.label}</div>
            <div class="content">{$form.is_required.html}</div>
            <div class="clear"></div>
        </div>
    </div>
{/if}

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}