{if $sys_errors}
<div class="error">
Sorry, one or more errors has occurred:
<ul>
{foreach from=$sys_errors item="error" name="errorforeach"}
<li>{$error}</li>
{/foreach}
</ul>
</div>
{/if}
