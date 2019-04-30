{crmScope extensionKey='dataprocessor'}
    {* block for rule data *}
<div class="crm-accordion-wrapper">
    <div class="crm-accordion-header">{ts}Filter criteria{/ts}</div>
    <div class="crm-accordion-body">

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
{/crmScope}