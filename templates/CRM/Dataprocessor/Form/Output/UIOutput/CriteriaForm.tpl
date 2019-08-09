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
                        {include file=$filter.template filter=$filter.filter}
                    {/foreach}
                </table>
                <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botton"}</div>
            </div>
        </div>
    </div>
</div>

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
