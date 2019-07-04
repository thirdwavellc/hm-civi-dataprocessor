{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
  {* Are you sure to delete form *}
  <h3>{ts}Delete Data Processor{/ts}</h3>
  <div class="crm-block crm-form-block crm-data-processor_label-block">
    <div class="crm-section">{ts 1=$rule.label}Are you sure to delete data processor '%1'?{/ts}</div>
  </div>

{else}

<h3>Data Processor</h3>
<div class="crm-block crm-form-block crm-data-processor_title-block">
  <div class="crm-section">
    <div class="label">{$form.title.label}</div>
    <div class="content">
      {$form.title.html}
      <span class="">
        {ts}System name:{/ts}&nbsp;
        <span id="systemName" style="font-style: italic;">{if ($dataProcessor)}{$dataProcessor.name}{/if}</span>
        <a href="javascript:void(0);" onclick="jQuery('#nameSection').removeClass('hiddenElement'); jQuery(this).parent().addClass('hiddenElement'); return false;">
          {ts}Change{/ts}
        </a>
      </span>
    </div>
    <div class="clear"></div>
  </div>
  <div id="nameSection" class="crm-section hiddenElement">
    <div class="label">{$form.name.label}</div>
    <div class="content">
      {$form.name.html}
      <p class="description">{ts}Leave empty to let the system generate a name. The name should consist of lowercase letters, numbers and underscore. E.g team_captains.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.description.label}</div>
    <div class="content">{$form.description.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.is_active.label}</div>
    <div class="content">{$form.is_active.html}</div>
    <div class="clear"></div>
  </div>
</div>

  {if $data_processor_id}
    <table>
      <tr>
        <td style="width: 50%;">
          {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Sources.tpl"}
          {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/AggregateFields.tpl"}
          {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Outputs.tpl"}
        </td>
        <td style="width: 50%;">
          {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Fields.tpl"}
          {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Filters.tpl"}
        </td>
      </tr>
    </table>
  {/if}

  <script type="text/javascript">
    {literal}
    CRM.$(function($) {
      var id = {/literal}{if ($dataProcessor)}{$dataProcessor.id}{else}false{/if}{literal};

      $('#title').on('blur', function() {
        var title = $('#title').val();
        if ($('#nameSection').hasClass('hiddenElement') && !id) {
          CRM.api3('DataProcessor', 'check_name', {
            'title': title
          }).done(function (result) {
            $('#systemName').html(result.name);
            $('#name').val(result.name);
          });
        }
      });
    });
    {/literal}
  </script>

{/if}

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}