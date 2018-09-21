{crmScope extensionKey='dataprocessor'}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="top"}
</div>

{if $action eq 8}
  {* Are you sure to delete form *}
  <h3>{ts}Delete Data Processor{/ts}</h3>
  <div class="crm-block crm-form-block crm-data-processor_label-block">
    <div class="crm-section">{ts 1=$rule->label}Are you sure to delete data processor '%1'?{/ts}</div>
  </div>
{elseif $action eq 128}
  {* Export form *}
  <h3>{ts}Export Data Processor{/ts}</h3>
  <div class="crm-block crm-form-block crm-data-processor_label-block">
    <div class="crm-section">
      <pre>{$export}</pre>
    </div>
  </div>
{else}

{* block for rule data *}
<h3>Data Processor</h3>
<div class="crm-block crm-form-block crm-data-processor_title-block">
  <div class="crm-section">
    <div class="label">{$form.title.label}</div>
    <div class="content">{$form.title.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.name.label}</div>
    <div class="content">
      {$form.name.html}
      <p class="description">{ts}Leave empty to let the system generate a name. The name should consist of lowercase letters, numbers and underscore. E.g team_captains.{/ts}</p>
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.description.label}</div>
    <div class="content">{$form.description.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.is_active.label}</div>
    <div class="content">{$form.is_active.html}</div>
    <div class="clear"></div>
  </div>
</div>

  {if $data_processor_id}
    {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Sources.tpl"}
    {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Fields.tpl"}
    {include file="CRM/Dataprocessor/Form/DataProcessorBlocks/Outputs.tpl"}
  {/if}

{/if}

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}