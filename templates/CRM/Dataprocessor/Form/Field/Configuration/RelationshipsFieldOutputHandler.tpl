{crmScope extensionKey='dataprocessor'}
    <div class="crm-section">
        <div class="label">{$form.contact_id_field.label}</div>
        <div class="content">{$form.contact_id_field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.relationship_type_checkboxes.label}</div>
      <div class="content">
        <ul id="relationship_types" class="crm-checkbox-list crm-sortable-list ui-sortable" style="width: 600px; list-style: none; margin: 10px 0; max-height: 300px; overflow-y: auto;">
          {foreach from=$relationship_types item="relationshipType"}
            <li id="relationship-type-{$relationshipType}" class="ui-state-default ui-corner-all" style="padding: 5px; border-radius: 0;">
              <i class='crm-i fa-arrows crm-grip' style="float:left; margin-right: 10px;"></i>
              <span>{$form.relationship_type_checkboxes.$relationshipType.html}</span>
            </li>
          {/foreach}
        </ul>
      </div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.separator.label}</div>
      <div class="content">{$form.separator.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label"></div>
        <div class="content">{$form.show_label.html}&nbsp;{$form.show_label.label}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label"></div>
      <div class="content">{$form.include_deceased.html} {$form.include_deceased.label}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.sort.label}</div>
      <div class="content">{$form.sort.html}</div>
      <div class="clear"></div>
    </div>
{/crmScope}

<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    function getRelstionshipTypeSorting(e, ui) {
      var params = [];
      var y = 0;
      var items = $("#relationship_types li");
      if (items.length > 0) {
        for (var y = 0; y < items.length; y++) {
          var idState = items[y].id.substring(18);
          params[y + 1] = idState;
        }
      }
      $('#sorted_relationship_types').val(params.toString());
    }

    $("#relationship_types").sortable({
      placeholder: 'ui-state-highlight',
      update: getRelstionshipTypeSorting
    });
  });
  {/literal}
</script>
