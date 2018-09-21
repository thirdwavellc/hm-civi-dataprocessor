{crmScope extensionKey='dataprocessor'}
    <h3>{ts}Data Sources{/ts}</h3>
<div class="crm-block crm-form-block crm-data-processor_source-block">
    <table>
        <tr>
            <th>{ts}Source{/ts}</th>
            <th>{ts}Title{/ts}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        {foreach from=$sources item=source}
            <tr>
                <td>{$source.type_name}</td>
                <td>{$source.title}</td>
                <td>
                    {if $source.join_link}
                        <a href="{$source.join_link}">{ts}Join Configuration{/ts}</a>
                    {/if}
                </td>
                <td>
                    {if $source.configuration_link}
                        <a href="{$source.configuration_link}">{ts}Configure source{/ts}</a>
                    {/if}
                </td>
                <td>
                    <a href="{crmURL p="civicrm/dataprocessor/form/source" q="reset=1&action=update&data_processor_id=`$source.data_processor_id`&id=`$source.id`"}">{ts}Edit{/ts}</a>
                </td>
                <td>
                    <a href="{crmURL p="civicrm/dataprocessor/form/source" q="reset=1&action=delete&data_processor_id=`$source.data_processor_id`&id=`$source.id`"}">{ts}Remove{/ts}</a>
                </td>
            </tr>
        {/foreach}
    </table>

    <div class="crm-submit-buttons">
        <a class="add button" title="{ts}Add Data Source{/ts}" href="{$addDataSourceUrl}">
            <span><div class="icon add-icon ui-icon-circle-plus"></div>{ts}Add Data Source{/ts}</span></a>
    </div>
</div>
{/crmScope}