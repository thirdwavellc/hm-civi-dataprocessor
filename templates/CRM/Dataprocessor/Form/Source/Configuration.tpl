{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* block for rule data *}
    <h3>{ts}Data Processor Sources configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source_configuration-block">
        <div class="crm-section">

            <h3>{ts}Filter criteria{/ts}</h3>
            <table class="report-layout">
                {foreach from=$filter_fields item=fieldLabel key=fieldName}
                    {assign var=fieldOp     value=$fieldName|cat:"_op"}
                    {assign var=fieldVal   value=$fieldName|cat:"_value"}
                    <tr class="report-contents crm-report crm-report-criteria-filter">
                        <td class="report-contents">{$fieldLabel}</td>
                        <td class="report-contents">{$form.$fieldOp.html}</td>
                        <td>{$form.$fieldVal.html}</td>
                    </tr>
                {/foreach}
            </table>

        </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}