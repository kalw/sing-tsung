	<table id="data_table" border="1" >
	<tbody>
	{if $table_data ne ""}
		<tr>
		{if $show_iteration}
			<th>#</th>
		{/if}
		{foreach from=$headers item="header"}
			{if $header.type!='hidden'}
			<th {if $data_table_nowrap}style="white-space: nowrap;"{/if} >
					{$header.name}
			</th>
			{/if}
		{/foreach}
		</tr>
		{assign var="ix" value="0"}
		{foreach from=$table_data item="row" name=tablerow}
<tr style="{cycle values="odd,even" advance=true}">
			{if $show_iteration && $pagination}
<td>{$smarty.foreach.tablerow.iteration+$page_size*$page-$page_size}.</td>
			{elseif $show_iteration}
<td>{$smarty.foreach.tablerow.iteration}.</td>
			{/if}
			{foreach from=$headers item="item" }
				{if $simple_dynamic_table or !$item.type}
<td {if $data_table_nowrap}style="white-space: nowrap;"{/if}>
						{$row[$item.dbname]}
</td>
				{else}
					{if $item.type!='hidden'}
<td {if $data_table_nowrap}style="white-space: nowrap;"{/if}>
						{if $item.type}
							{if $item.type=='checkbox'}
<input type="checkbox" name="{$item.dbname}_box[]" value="{$row.PROJECT_SLOT_ID}" 
								{if $row[$item.dbname]==1}
checked
									{if $item.extra == "no_uncheck"}disabled{/if}
								{/if}
>
							{elseif $item.dbalt}
								{$row[$item.dbalt]}
							{elseif $item.type=='wordwrap'}
								{$row[$item.dbname]|truncate:15:"..."}
							{elseif $item.type=='date'}
								{$row[$item.dbname]|date_format:"%D"}
							{elseif $item.type=='time'}
								{$row[$item.dbname]|date_format:"%I:%M:%S%p"}
							{elseif $item.type=='link'}
								{if $item.link_text}
{if 0}<a href="{$item.link}{$row[$item.link_subject]}">{$item.link_text}</a>{/if}
								{elseif $item.link}
									{$row[$item.dbname]}
								{/if}
							{/if}
						{/if}
					</td>
					{/if}
				{/if}
			{/foreach}
</tr>
		{assign var="ix" value="`$ix+1`"}
		{/foreach}
	{else}
<tr><th>Sorry, your search did not return any results</th></tr>
	{/if}
</tbody>
</table>

