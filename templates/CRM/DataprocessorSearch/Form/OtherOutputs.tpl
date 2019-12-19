{if (isset($other_outputs) && !empty($other_outputs))}
    <div class="crm-block action-links">
        <span class="action-link">
        {foreach from=$other_outputs item=other_output}
            <a class="other-output-button" href="#" data-output-id="{$other_output.id}">
                {if ($other_output.icon)}
                    {$other_output.icon}
                {/if}
                {$other_output.title}
            </a>
        {/foreach}
        </span>
    </div>
    <div class="crm-clear"></div>
{/if}
