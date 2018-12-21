{crmScope extensionKey='dataprocessor'}
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* block for rule data *}
    <h3>{ts}Data Processor Sources configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source_configuration-block">
        <div class="crm-section">
            <div class="label">{$form.uri.label}</div>
            <div class="content">{$form.uri.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">&nbsp;</div>
            <div class="content">{$form.first_row_as_header.html} &nbsp; {$form.first_row_as_header.label}</div>
            <div class="clear"></div>
        </div>
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
            <div class="label">{$form.escape.label}</div>
            <div class="content">{$form.escape.html}</div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}