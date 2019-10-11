{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="top"}
</div>

<h3>{ts 1=$dataProcessor.title}Clone Data Processor '%1'{/ts}</h3>
<div class="crm-block crm-form-block crm-data-processor_title-block">
  <div class="crm-section">
    <div class="label">{$form.title.label}</div>
    <div class="content">
      {$form.title.html}
      <span class="">
        {ts}System name:{/ts}&nbsp;
        <span id="systemName" style="font-style: italic;"></span>
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

<script type="text/javascript">
  {literal}
  CRM.$(function($) {
    $('#title').on('blur', function() {
      var title = $('#title').val();
      if ($('#nameSection').hasClass('hiddenElement')) {
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


<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}
