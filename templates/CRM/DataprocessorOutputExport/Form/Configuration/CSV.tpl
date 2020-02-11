{crmScope extensionKey='dataprocessor'}

    <div class="crm-section">
        <div class="label">{$form.delimiter.label}</div>
        <div class="content">{$form.delimiter.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.enclosure.label}</div>
        <div class="content">{$form.enclosure.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.escape_char.label}</div>
        <div class="content">{$form.escape_char.html}</div>
        <div class="clear"></div>
    </div>
  <div class="crm-section">
    <div class="label">{$form.anonymous.label}</div>
    <div class="content">{$form.anonymous.html}
      <p class="description">
        {ts}Tick this box when you want to make the CSV available for non-logged in users. <br>
        This could be necessary when another system is importing this csv file on a regular basis. E.g. a website with
        a public agenda of the upcoming events.
        <br><strong>Caution:</strong> when you check this box the data becomes available without logging so this might lead to a data breach.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>

{/crmScope}
