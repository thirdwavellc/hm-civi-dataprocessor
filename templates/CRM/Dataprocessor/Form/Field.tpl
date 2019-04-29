{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
    {* Are you sure to delete form *}
    <h3>{ts}Delete Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_label-block">
        <div class="crm-section">{ts 1=$field->title}Are you sure to delete field '%1'?{/ts}</div>
    </div>
{else}

    {* block for rule data *}
    <h3>{ts}Field{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source-block">
        <div class="crm-section">
            <div class="label">{$form.type.label}</div>
            <div class="content">{$form.type.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section">
            <div class="label">{$form.title.label}</div>
            <div class="content">
                {$form.title.html}
                <span class="">
                {ts}System name:{/ts}&nbsp;
                <span id="systemName" style="font-style: italic;">{if ($field)}{$field.name}{/if}</span>
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
    </div>

    <script type="text/javascript">
        {literal}
        CRM.$(function($) {
          var id = {/literal}{if ($field)}{$field.id}{else}false{/if}{literal};
          var data_processor_id = {/literal}{$data_processor_id}{literal};

          $('#title').on('blur', function() {
            var title = $('#title').val();
            if ($('#nameSection').hasClass('hiddenElement') && !id) {
              CRM.api3('DataProcessorField', 'check_name', {
                'title': title,
                'data_processor_id': data_processor_id
              }).done(function (result) {
                $('#systemName').html(result.name);
                $('#name').val(result.name);
              });
            }
          });
        });
        {/literal}
    </script>
{/if}

<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}