<div id="dataprocessorContactSummaryTab_{$dataProcessorName}"></div>

{literal}
<script type="text/javascript">
  (function($) {
    var target = "#dataprocessorContactSummaryTab_{/literal}{$dataProcessorName}{literal}";
    var form = CRM.loadForm('{/literal}{$url}{literal}', {
      "target": target,
      "dialog": false,
    }).on('crmFormSuccess', function(event, data) {
      $(target).crmSnippet('option', 'url', data.userContext).crmSnippet('refresh');
    });

  })(CRM.$);
</script>
{/literal}
