	Pages:
	{foreach key=key item=item from=$pages name=pageforeach}
		{if $item == $page}
			{$key}
		{else}
		<a href="{$smarty.server.SCRIPT_NAME}?screen={$screen_name}&page={$key}

		{foreach from=$get key=getkey item=getitem }
			{if $getkey != "page" && $getkey != "screen"}
				{foreach from=$getitem item=subitem name=getloop}
					{* bogus check for arrays *}
					{if $smarty.foreach.getloop.last && $smarty.foreach.getloop.first}
						&{$getkey}={$subitem}
					{else}
						&{$getkey}[]={$subitem}
					{/if}
				{/foreach}
			{/if}
		{/foreach}

		">{$key}</a> 
		
		{/if}
		{if !$smarty.foreach.pageforeach.last}, {/if}
	{/foreach}
