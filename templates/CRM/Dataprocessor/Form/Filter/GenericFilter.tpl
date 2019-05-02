{assign var=fieldOp     value=$filterName|cat:"_op"}
{assign var=filterVal   value=$filterName|cat:"_value"}
{assign var=filterMin   value=$filterName|cat:"_min"}
{assign var=filterMax   value=$filterName|cat:"_max"}

{if $filter.type == 'Date' || $filter.type == 'Timestamp'}
    <tr>
        <td class="label">{$filter.title}</td>
        {include file="CRM/Dataprocessor/Form/Filter/DateRange.tpl" fieldName=$filterName from='_from' to='_to'}
    </tr>
{elseif $form.$fieldOp.html}
    <tr>
        <td class="label">{$filter.title}</td>
        <td>{$form.$fieldOp.html}</td>
        <td>
            <span id="{$filterVal}_cell">{$form.$filterVal.label}&nbsp;{$form.$filterVal.html}</span>
            <span id="{$filterMin}_max_cell">{$form.$filterMin.label}&nbsp;{$form.$filterMin.html}&nbsp;&nbsp;{$form.$filterMax.label}&nbsp;{$form.$filterMax.html}</span>
        </td>
    </tr>
{/if}