{crmScope extensionKey='dataprocessor'}
    <div class="crm-section">
        <div class="label">{$form.field.label}</div>
        <div class="content">{$form.field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label"></div>
        <div class="content">
            <p class="help">{ts}{literal}Be careful with this. Dont link to your own data processor. Also dont link it to an API call which returns a long list of results.{/literal}{/ts}</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.api_entity.label}</div>
        <div class="content">{$form.api_entity.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.api_action.label}</div>
        <div class="content">{$form.api_action.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.api_params.label}</div>
        <div class="content">{$form.api_params.html}
            <br />
            <span class="description">{ts}{literal}Enter in JSON format e.g. {"contact_type": "Individual"}{/literal}{/ts}</span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.value_field.label}</div>
        <div class="content">{$form.value_field.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.label_field.label}</div>
        <div class="content">{$form.label_field.html}</div>
        <div class="clear"></div>
    </div>
{/crmScope}