{crmScope extensionKey='dataprocessor'}
    <h3>{ts}Output{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_outputs-block">
    <table>
        <tr>
            <th>{ts}Output{/ts}</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        {foreach from=$outputs item=output}
            <tr>
                <td>{$output.type_name}</td>
                <td>
                    {if ($output.configuration_link)}
                        <a href="{$output.configuration_link}">{ts}Configure{/ts}</a>
                    {/if}
                </td><td>
                    <a href="{crmURL p="civicrm/dataprocessor/form/output" q="reset=1&action=update&data_processor_id=`$output.data_processor_id`&id=`$output.id`"}">{ts}Edit{/ts}</a>
                </td><td>
                    <a href="{crmURL p="civicrm/dataprocessor/form/output" q="reset=1&action=delete&data_processor_id=`$output.data_processor_id`&id=`$output.id`"}">{ts}Remove{/ts}</a>
                </td>
            </tr>
        {/foreach}
    </table>

    <div class="crm-submit-buttons">
        <a class="add button" title="{ts}Add Output{/ts}" href="{$addOutputUrl}">
            <span><div class="icon add-icon ui-icon-circle-plus"></div>{ts}Add Output{/ts}</span></a>
    </div>
</div>
{/crmScope}