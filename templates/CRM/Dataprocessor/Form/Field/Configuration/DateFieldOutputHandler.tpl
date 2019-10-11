{crmScope extensionKey='dataprocessor'}
  {include file="CRM/Dataprocessor/Form/Field/Configuration/SimpleFieldOutputHandler.tpl"}
  <div class="crm-section">
    <div class="label">{$form.format.label}</div>
    <div class="content">
        {$form.format.html}
      <p class="description">
        {ts 1='https://www.php.net/manual/en/function.date.php#format'}See <a href="%1">DateTime format on php.net</a> for help.{/ts}
      </p>
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.function.label}</div>
    <div class="content">{$form.function.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.is_aggregate.label}</div>
    <div class="content">{$form.is_aggregate.html}</div>
    <div class="clear"></div>
  </div>
{/crmScope}
