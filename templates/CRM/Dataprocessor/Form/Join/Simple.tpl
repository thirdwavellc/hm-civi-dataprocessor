{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

    {* block for rule data *}
    <h3>{ts}Data Processor Sources simple join configuration{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_simple_join-block">
        <div class="crm-section">
            <table class="form_layout">
                <tr>
                    <td>
                        {$form.left_field.label}
                    </td>
                    <td></td>
                    <td>
                        {$form.right_field.label}
                    </td>
                </tr>
                <tr>
                    <td>
                        {$form.left_field.html}
                    </td>
                    <td>=</td>
                    <td>
                        {$form.right_field.html}
                    </td>
                </tr>
            </table>

        </div>
    </div>

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}