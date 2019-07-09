{crmScope extensionKey='dataprocessor'}

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts 1=$filter.title}Are you sure to delete filter '%1'?{/ts}</div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
{elseif (!$snippet)}

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    {* block for rule data *}
    <h3>{ts}Filter{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_filter-block">
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
        <div class="crm-section">
            <div class="label">{$form.title.label}</div>
            <div class="content">
                {$form.title.html}
                <span class="">
                {ts}System name:{/ts}&nbsp;
                <span id="systemName" style="font-style: italic;">{if ($filter)}{$filter.name}{/if}</span>
                <a href="javascript:void(0);" onclick="jQuery('#nameSection').removeClass('hiddenElement'); jQuery(this).parent().addClass('hiddenElement'); return false;">
                  {ts}Change{/ts}
                </a>
                </span>
            </div>
            <div class="clear"></div>
        </div>
        <div id="nameSection" class="crm-section hiddenElement">
            <div class="label">{$form.name.label}</div>
            <div class="content">{$form.name.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.is_required.label}</div>
            <div class="content">{$form.is_required.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.is_exposed.label}</div>
            <div class="content">{$form.is_exposed.html}</div>
            <div class="clear"></div>
        </div>
    </div>

    <script type="text/javascript">
        {literal}
        CRM.$(function($) {
          var id = {/literal}{if ($filter)}{$filter.id}{else}false{/if}{literal};
          var data_processor_id = {/literal}{$data_processor_id}{literal};

          $(document).on('change','#type_configuration select.data-processor-field-for-name.crm-form-select',function () {
            {/literal}{if $action eq 1}{literal}
              var titlepreset = $.trim($('#type_configuration select.crm-form-select option:selected').first().text().split("::").pop());
              $('#title').val(titlepreset).trigger('blur');
            {/literal}{/if}{literal};
          });

          $('#type').on('change', function() {
            var type = $('#type').val();
            if (type) {
              var dataUrl = CRM.url('civicrm/dataprocessor/form/filter', {type: type, 'data_processor_id': data_processor_id, 'id': id});
              CRM.loadPage(dataUrl, {'target': '#type_configuration'});
            }
          });

          $('#title').on('blur', function() {
            var title = $('#title').val();
            if ($('#nameSection').hasClass('hiddenElement') && !id) {
              CRM.api3('DataProcessorFilter', 'check_name', {
                'title': title,
                'data_processor_id': data_processor_id
              }).done(function (result) {
                $('#systemName').html(result.name);
                $('#name').val(result.name);
              });
            }
          });

          //$('#type').change();
        });
        {/literal}
    </script>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

{else}
    <div id="type_configuration">
        {if ($configuration_template)}
            {include file=$configuration_template}
        {/if}
    </div>
{/if}
{/crmScope}