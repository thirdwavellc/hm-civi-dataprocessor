{include file="CRM/DataprocessorSearch/Form/CriteriaForm.tpl"}

{if $debug && isset($debug_info.query)}
    <div class="crm-block crm-form-block">
        <h3>Executes queries</h3>
        {foreach from=$debug_info.query item=query}
            <pre id="debug_info" class="linenums prettyprint prettyprinted" style="font-size: 11px; padding: 1em; border: 1px solid lightgrey; margin-top: 1em; overflow: auto;">{strip}
                {$query|replace:"SELECT":"SELECT\r\n "|replace:"FROM":"\r\nFROM"|replace:"INNER JOIN":"\r\nINNER JOIN"|replace:"LEFT JOIN":"\r\nLEFT JOIN"|replace:"WHERE":"\r\nWHERE"|replace:"ORDER BY":"\r\nORDER BY"|replace:"LIMIT":"\r\nLIMIT"|replace:"AND":"\r\n  AND"|replace:"`, `":"`,\r\n  `"}
            {/strip}</pre>
        {/foreach}
    </div>
{/if}

{if (isset($rows) && !empty($rows))}
    <div class="crm-content-block">
        <div class="crm-results-block">
            {* This section handles form elements for action task select and submit *}
            <div class="crm-search-tasks">
                {include file="CRM/common/searchResultTasks.tpl"}
            </div>

            {include file="CRM/common/pager.tpl" location="top"}

            <div class="crm-search-results">
                <a href="#" class="crm-selection-reset crm-hover-button"><i class="crm-i fa-times-circle-o"></i> {ts}Reset all selections{/ts}</a>
                <table class="selector row-highlight">
                    <thead class="sticky">
                    <tr>
                        <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th>
                        {foreach from=$columnHeaders key=headerName item=headerTitle}
                            <th scope="col">
                                {$sort->_response.$headerName.link}
                            </th>
                        {/foreach}
                        <th scope="col"></th>
                    </tr></thead>


                    {foreach from=$rows item=row}
                        <tr id='rowid{$row.id}' class="{cycle values="odd-row,even-row"}">
                            {assign var=cbName value=$row.checkbox}
                            {assign var=id value=$row.id}
                            {assign var=record value=$row.record}
                            <td>{$form.$cbName.html}</td>
                            {foreach from=$columnHeaders key=headerName item=headerTitle}
                                {assign var=columnValue value=$record.$headerName}
                                <td>{$columnValue->formattedValue}</td>
                            {/foreach}

                            <td>
                                {if ($row.url)}
                                <a href="{$row.url}">
                                    {$row.link_text}
                                </a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}

                </table>
            </div>

            {include file="CRM/common/pager.tpl" location="bottom"}
        </div>
    </div>

    {include file="CRM/DataprocessorSearch/Form/ResultsJavascript.tpl"}
{/if}