{if $debug && isset($debug_info.query)}
    <div class="crm-block crm-form-block">
        <h3>Executes queries</h3>
        {foreach from=$debug_info.query item=query}
            <pre id="debug_info" class="linenums prettyprint prettyprinted" style="font-size: 11px; padding: 1em; border: 1px solid lightgrey; margin-top: 1em; overflow: auto;">{strip}
                {$query|replace:"SELECT":"SELECT\r\n "|replace:"FROM":"\r\nFROM"|replace:"INNER JOIN":"\r\nINNER JOIN"|replace:"LEFT JOIN":"\r\nLEFT JOIN"|replace:"WHERE":"\r\nWHERE"|replace:"GROUP BY":"\r\nGROUP BY"|replace:"ORDER BY":"\r\nORDER BY"|replace:"LIMIT":"\r\nLIMIT"|replace:"AND":"\r\n  AND"|replace:"`, `":"`,\r\n  `"}
            {/strip}</pre>
        {/foreach}
    </div>
{/if}
