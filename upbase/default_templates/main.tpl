{include file="header.tpl"}
{include file="navigation.tpl"}
{include file="errors.tpl"}
<div id="content">
{include file=$mid}
</div>
{if $upbase_debugging}
	{include file="debugging.tpl" debugging=$upbase_debugging_data timing=$upbase_timing}
{/if}
{include file="footer.tpl"}

