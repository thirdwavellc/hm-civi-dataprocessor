<div>
	<table class="dataprocessor_{$dataProcessorName}">
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
$('table.dataprocessor_{/literal}{$dataProcessorName}{literal}').DataTable({
		"pageLength":5,
		"order":[],
		"lengthMenu": [[5, 10, 20], [5, 10, 20]],
		"searching": false,
        "ajax": {
          "url": {/literal}'{crmURL p="civicrm/ajax/getDashlet" q="dataProcessorName=$dataProcessorName"}'{literal},
        }
      });
})(CRM.$);
</script>
{/literal}
