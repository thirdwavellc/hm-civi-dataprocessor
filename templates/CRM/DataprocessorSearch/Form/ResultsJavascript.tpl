<script type="text/javascript">
    {literal}
    CRM.$(function($) {
      // Clear any old selection that may be lingering in quickform
      $("input.select-row, input.select-rows", 'form.crm-search-form').prop('checked', false).closest('tr').removeClass('crm-row-selected');
      // Retrieve stored checkboxes
      var selectedIds = {/literal}{$selectedIds|@json_encode}{literal};
      if (selectedIds.length > 0) {
        $('#mark_x_' + selectedIds.join(',#mark_x_') + ',input[name=radio_ts][value=ts_sel]').trigger('click');
      }
    });
    {/literal}
</script>