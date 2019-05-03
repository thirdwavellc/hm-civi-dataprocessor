{crmScope extensionKey='dataprocessor'}
<h3>{ts}Fields{/ts}</h3>
<div class="crm-block crm-form-block crm-data-processor_source-block">
    <table>
        <tr>
            <th>{ts}Title{/ts}</th>
            <th></th>
            <th></th>
        </tr>
        {foreach from=$fields item=field}
            <tr>
                <td>{$field.title} <br /><span class="description">{$field.name}</span></td>
                <td style="width: 20%">{if ($field.weight && !is_numeric($field.weight))}{$field.weight}{/if}</td>
                <td style="width: 20%">
                    <a href="{crmURL p="civicrm/dataprocessor/form/field" q="reset=1&action=update&data_processor_id=`$field.data_processor_id`&id=`$field.id`"}">{ts}Edit{/ts}</a>
                    <a href="{crmURL p="civicrm/dataprocessor/form/field" q="reset=1&action=delete&data_processor_id=`$field.data_processor_id`&id=`$field.id`"}">{ts}Remove{/ts}</a>
                </td>
            </tr>
        {/foreach}
    </table>

    <div class="crm-submit-buttons">
        <a class="add button" title="{ts}Add Field{/ts}" href="{$addFieldUrl}">
            <span><div class="icon add-icon ui-icon-circle-plus"></div>{ts}Add Field{/ts}</span></a>
    </div>
</div>
{/crmScope}