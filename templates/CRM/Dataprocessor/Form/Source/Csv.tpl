{crmScope extensionKey='dataprocessor'}
<div class="crm-accordion-wrapper">
    <div class="crm-accordion-header">{ts}Configuration{/ts}</div>
    <div class="crm-accordion-body">

        <div class="help-block" id="help">
            {ts}<p>On this form you can configure the CSV data source.</p>
                <p>The <strong>URI</strong> is the file location. Which is either a path on the server  or a URL from where the file could be downloaded.</p>
                <p>The <strong>Field delimiter</strong> the character which separates each field.</p>
                <p>The <strong>Field enclosure character</strong> is the a character which is wrapped around each field.</p>
                <p>The <strong>Escape character</strong> is the character which marks special characters</p>
            {/ts}
        </div>

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
</div>
{/crmScope}