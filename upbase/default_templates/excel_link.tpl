<a href="index.php?
		{foreach from=$get key=getkey item=getitem }
				{foreach from=$getitem item=subitem name=getloop}
					{* bogus check for arrays *}
					{if $smarty.foreach.getloop.last && $smarty.foreach.getloop.first}
						&{$getkey}={$subitem}
					{else}
						&{$getkey}[]={$subitem}
					{/if}
				{/foreach}
		{/foreach}
		&excel"><img alt="Excel Icon" title="Download this table as an Excel spreadsheet file" src="{$imagedir}images/gnome-gnumeric.png"></a>
