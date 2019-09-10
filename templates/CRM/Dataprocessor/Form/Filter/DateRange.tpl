{assign var=relativeName   value=$fieldName|cat:"_relative"}
<td>
    {if $label}
        {ts}{$label}{/ts}<br />
    {/if}
    {$form.$relativeName.html}<br />
</td><td>
    <span class="crm-absolute-date-range">
    <span class="crm-absolute-date-from">
      {assign var=fromName   value=$fieldName|cat:$from}
        {$form.$fromName.label}{if $filter.size == 'compact'}<br />{/if}
        {include file="CRM/common/jcalendar.tpl" elementName=$fromName}
    </span>
    {if $filter.size == 'compact'}<br />{/if}
    <span class="crm-absolute-date-to">
      {assign var=toName   value=$fieldName|cat:$to}
        {$form.$toName.label}{if $filter.size == 'compact'}<br />{/if}
        {include file="CRM/common/jcalendar.tpl" elementName=$toName}
    </span>
  </span>
    {literal}
    <script type="text/javascript">
      cj("#{/literal}{$relativeName}{literal}").change(function() {
        var n = cj(this).parent().parent();
        if (cj(this).val() == "0") {
          cj(".crm-absolute-date-range", n).show();
        } else {
          cj(".crm-absolute-date-range", n).hide();
          cj(':text', n).val('');
        }
      }).change();
    </script>
    {/literal}
</td>
