{crmScope extensionKey='dataprocessor'}

{if (count($filter_required_fields))}
    <div class="crm-accordion-wrapper">
        <div class="crm-accordion-header">{ts}Filter criteria{/ts}</div>
        <div class="crm-accordion-body">

            <table class="report-layout">
                {foreach from=$filter_required_fields item=fieldLabel key=fieldName}
                    {assign var=fieldOp     value=$fieldName|cat:"_op"}
                    {assign var=fieldVal   value=$fieldName|cat:"_value"}
                    <tr class="report-contents crm-report crm-report-criteria-filter">
                        <td class="report-contents">{$fieldLabel} <span class="marker">*</span></td>
                        <td class="report-contents">{$form.$fieldOp.html}</td>
                        <td>{$form.$fieldVal.html}</td>
                    </tr>
                {/foreach}
            </table>

        </div>
    </div>
{/if}
{if (count($filter_fields))}
    <div class="crm-accordion-wrapper collapsed">
        <div class="crm-accordion-header">{ts}Additional filter criteria{/ts}</div>
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
{/if}
{/crmScope}