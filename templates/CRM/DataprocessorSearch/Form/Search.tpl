<div class="crm-form-block crm-search-form-block">
    <div class="crm-accordion-wrapper crm-advanced_search_form-accordion {if (!empty($rows))}collapsed{/if}">
        <div class="crm-accordion-header crm-master-accordion-header">
            {ts}Edit Search Criteria{/ts}
        </div>
        <!-- /.crm-accordion-header -->
        <div class="crm-accordion-body">
            <div id="searchForm" class="form-item">
                <table>
                    <tr>
                        <th>{ts}Name{/ts}</th>
                        <th>{ts}Operator{/ts}</th>
                        <th>{ts}Value{/ts}</th>
                    </tr>
                {foreach from=$filters key=filterName item=filter}
                    {assign var=fieldOp     value=$filterName|cat:"_op"}
                    {assign var=filterVal   value=$filterName|cat:"_value"}
                    {assign var=filterMin   value=$filterName|cat:"_min"}
                    {assign var=filterMax   value=$filterName|cat:"_max"}
                    {if $filter.type == 'Date'}
                        <tr>
                            <td>{$filter.title}</td>
                            {include file="CRM/DataprocessorSearch/Form/DateRange.tpl" fieldName=$filterName from='_from' to='_to'}
                        </tr>
                    {elseif $form.$fieldOp.html}
                        <tr>
                            <td class="label">{$filter.title}</td>
                            <td>{$form.$fieldOp.html}</td>
                            <td>
                                <span id="{$filterVal}_cell">{$form.$filterVal.label}&nbsp;{$form.$filterVal.html}</span>
                                <span id="{$filterMin}_max_cell">{$form.$filterMin.label}&nbsp;{$form.$filterMin.html}&nbsp;&nbsp;{$form.$filterMax.label}&nbsp;{$form.$filterMax.html}</span>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </table>
                <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botton"}</div>
            </div>
        </div>
    </div>
</div>

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
                        <th scope="col"></th>
                        {foreach from=$columnHeaders key=headerName item=headerTitle}
                            <th scope="col">
                                {$sort->_response.$headerName.link}
                            </th>
                        {/foreach}
                        <th scope="col"></th>
                    </tr></thead>


                    {foreach from=$rows item=row}
                        <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                            {assign var=cbName value=$row.checkbox}
                            {assign var=contact_id value=$row.contact_id}
                            {assign var=record value=$row.record}
                            <td>{$form.$cbName.html}</td>
                            <td>{$row.contact_type}</td>
                            {foreach from=$columnHeaders key=headerName item=headerTitle}
                                {assign var=columnValue value=$record.$headerName}
                                <td>{$columnValue->formattedValue}</td>
                            {/foreach}

                            <td>
                                <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$contact_id`"}">
                                    {ts}View contact{/ts}
                                </a>
                            </td>
                        </tr>
                    {/foreach}

                </table>
            </div>

            {include file="CRM/common/pager.tpl" location="bottom"}
        </div>
    </div>

    <script type="text/javascript">
        {literal}
        CRM.$(function($) {
          // Clear any old selection that may be lingering in quickform
          $("input.select-row, input.select-rows", 'form.crm-search-form').prop('checked', false).closest('tr').removeClass('crm-row-selected');
          // Retrieve stored checkboxes
          var cids = {/literal}{$selectedContactIds|@json_encode}{literal};
          if (cids.length > 0) {
            //$('#mark_x_' + cids.join(',#mark_x_') + ',input[name=radio_ts][value=ts_sel]').prop('checked', true);
            $('#mark_x_' + cids.join(',#mark_x_') + ',input[name=radio_ts][value=ts_sel]').trigger('click');
          }
        });
        {/literal}
    </script>
{/if}


{literal}
<script type="text/javascript">
    {/literal}
    {foreach from=$filters item=filter key=filterName}
        {literal}var val = "dnc";{/literal}
        {assign var=fieldOp     value=$filterName|cat:"_op"}
        {if !($field.operatorType & 4) && !$field.no_display && $form.$fieldOp.html}
            {literal}var val = document.getElementById("{/literal}{$fieldOp}{literal}").value;{/literal}
        {/if}
        {literal}showHideMaxMinVal( "{/literal}{$filterName}{literal}", val );{/literal}
    {/foreach}

    {literal}
    function showHideMaxMinVal( field, val ) {
      var fldVal    = field + "_value_cell";
      var fldMinMax = field + "_min_max_cell";
      if ( val == "bw" || val == "nbw" ) {
        cj('#' + fldVal ).hide();
        cj('#' + fldMinMax ).show();
      } else if (val =="nll" || val == "nnll") {
        cj('#' + fldVal).hide() ;
        cj('#' + field + '_value').val('');
        cj('#' + fldMinMax ).hide();
      } else {
        cj('#' + fldVal ).show();
        cj('#' + fldMinMax ).hide();
      }
    }

</script>
{/literal}
