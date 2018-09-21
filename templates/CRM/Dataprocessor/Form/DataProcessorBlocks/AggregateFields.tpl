{crmScope extensionKey='dataprocessor'}
<h3>{ts}Aggregation{/ts}</h3>
<div class="crm-block crm-form-block crm-data-processor_source-block">
    <table>
        <tr>
            <th>{ts}Field{/ts}</th>
            <th></th>
        </tr>
        {foreach from=$aggregateFields item=field key=alias}
            <tr>
                <td>{$field}</td>
                <td>
                    <a href="{crmURL p="civicrm/dataprocessor/form/aggregate_field" q="reset=1&action=delete&id=`$data_processor_id`&alias=`$alias`"}">{ts}Remove{/ts}</a>
                </td>
            </tr>
        {/foreach}
    </table>

    <div class="crm-submit-buttons">
        <a class="add button" title="{ts}Add Aggregate Field{/ts}" href="{$addAggregateFieldUrl}">
            <span><div class="icon add-icon ui-icon-circle-plus"></div>{ts}Add Aggregate Field{/ts}</span></a>
    </div>
</div>
{/crmScope}