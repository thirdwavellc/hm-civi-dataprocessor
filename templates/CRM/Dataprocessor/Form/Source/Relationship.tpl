{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* block for rule data *}
    <h3>{ts}Data Processor Sources simple join configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_simple_join-block">
        <div class="crm-section">
            <div class="label">{$form.relationship_type_id.label}</div>
            <div class="content">{$form.relationship_type_id.html}</div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}