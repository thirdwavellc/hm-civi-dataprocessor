{crmScope extensionKey='dataprocessor'}

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Data Processor{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts}Are you sure to delete data processor output?{/ts}</div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{elseif (!$snippet)}

    {* block for rule data *}
    <h3>{ts}Data Processor Output{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_output-block">
        <div class="crm-section">
            <div class="label">{$form.type.label}</div>
            <div class="content">{$form.type.html}</div>
            <div class="clear"></div>
        </div>

    <div id="type_configuration">
        {if ($configuration_template)}
            {include file=$configuration_template}
        {/if}
    </div>

    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

    <script type="text/javascript">
        {literal}
        CRM.$(function($) {
          var id = {/literal}{if ($output)}{$output.id}{else}false{/if}{literal};
          var data_processor_id = {/literal}{$data_processor_id}{literal};

          $('#type').on('change', function() {
            var type = $('#type').val();
            if (type) {
              var dataUrl = CRM.url('civicrm/dataprocessor/form/output', {type: type, 'data_processor_id': data_processor_id, 'id': id});
              CRM.loadPage(dataUrl, {'target': '#type_configuration'});
            }
          });

          $('#type').change();
        });
        {/literal}
    </script>
{else}
    <div id="type_configuration">
        {if ($configuration_template)}
            {include file=$configuration_template}
        {/if}
    </div>
{/if}
{/crmScope}