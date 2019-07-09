{crmScope extensionKey='dataprocessor'}
    <h3>{ts}Default Filter Value{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_filter_value-block">
        <table>
            <tr>
                <th>{ts}Name{/ts}</th>
                <th>{ts}Operator{/ts}</th>
                <th>{ts}Value{/ts}</th>
            </tr>
            {include file=$filter_template filterName=$filter.name filter=$filter}
        </table>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{/crmScope}