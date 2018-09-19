{crmScope extensionKey='dataprocessor'}

{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show','searchForm_hide'"}

{include file="CRM/Dataprocessor/Form/Search/DataProcessorCriteria.tpl"}

{if $rowsEmpty}
    {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
{/if}

{if $summary}
    {$summary.summary}: {$summary.total}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {assign var="showBlock" value="'searchForm_show'"}
    {assign var="hideBlock" value="'searchForm'"}

    <fieldset>

        {* This section displays the rows along and includes the paging controls *}
        <p>

            {include file="CRM/common/pager.tpl" location="top"}

            {include file="CRM/common/pagerAToZ.tpl"}

            {strip}
        <table class="selector" summary="{ts}Search results listings.{/ts}">
            <thead class="sticky">
            {foreach from=$columnHeaders item=header}
                {if $header.name ne "data_processor_id" and $header.name ne 'is_active_value' and $header.name ne 'status_value'}
                    <th scope="col">
                        {if $header.sort}
                            {assign var='key' value=$header.sort}
                            {$sort->_response.$key.link}
                        {else}
                            {$header.name}
                        {/if}
                    </th>
                {/if}
            {/foreach}
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            </thead>

            {counter start=0 skip=1 print=false}
            {foreach from=$rows item=row}
                <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                    {foreach from=$columnHeaders item=header}
                        {assign var=fName value=$header.sort}
                        {if $fName ne 'data_processor_id' and $fName ne 'is_active_value' and $fName ne 'status_value'}
                            <td>
                                {$row.$fName}
                            </td>
                        {/if}
                    {/foreach}
                    <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=update&id=`$row.data_processor_id`"}"
                                 class="" title="{ts}Edit Data Processor{/ts}">{ts}Edit{/ts}</a></span></td>
                    <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=export&id=`$row.data_processor_id`"}"
                                 class="" title="{ts}Export Data Processor{/ts}">{ts}Export{/ts}</a></span></td>
                    {if $row.is_active_value eq 1}
                        <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=disable&id=`$row.data_processor_id`"}"
                                     class="" title="{ts}Disable Data Processor{/ts}">{ts}Disable{/ts}</a></span></td>
                    {else}
                        <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=enable&id=`$row.data_processor_id`"}"
                                     class="" title="{ts}Enable Data Processor{/ts}">{ts}Enable{/ts}</a></span></td>
                    {/if}
                    {if $row.status_value eq 3}
                        <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=revert&id=`$row.data_processor_id`"}"
                                     class="" title="{ts}Revert Data Processor{/ts}">{ts}Revert{/ts}</a></span></td>
                    {else}
                        <td></td>
                    {/if}
                    <td><span><a href="{crmURL p='civicrm/dataprocessor/form/edit' q="reset=1&action=delete&id=`$row.data_processor_id`"}"
                                 class="" title="{ts}Delete Data Processor{/ts}">{ts}Delete{/ts}</a></span></td>
                </tr>
            {/foreach}
        </table>
        {/strip}

        {include file="CRM/common/pager.tpl" location="bottom"}

        </p>
    </fieldset>
    {* END Actions/Results section *}
{/if}
{/crmScope}