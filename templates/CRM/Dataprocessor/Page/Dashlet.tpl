<div>
	<table class="case-selector">
	<thead>
	  <tr>
	  	{foreach from=$columnHeaders key=headerName item=headerTitle}
            <th data-data={$headerName} class="crm-dashlet-{$headerName}" data-orderable="true">
                {$headerTitle}
            </th>
        {/foreach}
	  </tr>
	</thead>
	</table>
</div>

{literal}
<script type="text/javascript">
(function($) {
$('table.case-selector').DataTable({
		"pageLength":5,
		"order":[],
		"lengthMenu": [[5, 10, 20], [5, 10, 20]],
		"searching": true,
        "ajax": {
          "url": {/literal}'{crmURL p="civicrm/ajax/getDashlet" q="dataProcessorId=$dataProcessorId&outputId=$outputId"}'{literal},
        }
      });
})(CRM.$);
</script>
{/literal}