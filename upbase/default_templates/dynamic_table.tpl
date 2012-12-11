{* Smarty *}

{if !$no_excel}
<div style="text-align: right; padding: 0; margin: 0;">
{if $method=='post'}
	 
	<a href="#" onclick="get_excel();">
	<img alt="Excel Icon" title="Download these search results as an Excel spreadsheet file" src="images/gnome-gnumeric.png"></a>
{else}
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
			&excel">
		<img alt="Excel Icon" title="Download these search results as an Excel spreadsheet file" src="images/gnome-gnumeric.png">
	</a>
{/if}
	</div>
{/if}
	{if $pagination}
	Pages:
	{foreach key=key item=item from=$pages name=pageforeach}
		{if $item == $page}
			{$key}
		{else}
		{if $method=='post'}
		<a href="#" onclick="change_page({$key});">{$key}</a>
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
		
		{/if}
		{if !$smarty.foreach.pageforeach.last}, {/if}
	{/foreach}
	{/if}
	<table id="data_table" >
	<tbody>
	{if $table_data ne ""}
		<tr>
		{if $show_iteration}
			<th>#</th>
		{/if}
		{foreach from=$headers item="header"}
			{if $header.type!='hidden'}
			<th {if $data_table_nowrap}style="white-space: nowrap;"{/if} >
				{if $header.sort !== false}
					{if $method=='post'}
						<a href="#" onclick="change_order_by('{$header.dbname}','{if $post.direction == "ASC"}DESC{else}ASC{/if}');">{$header.name}</a>
					{else}
						<a href="{$smarty.server.SCRIPT_NAME}?screen={$screen_name}&page={$page}

						{foreach from=$get key=getkey item=getitem }
							{if $getkey != "page" && $getkey != "screen" && $getkey != "order_by" && $getkey != "direction"}
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
						&order_by={$header.dbname}
						{if $get.direction == "ASC"}
						&direction=DESC
						{else}
						&direction=ASC
						{/if}
						">
						{$header.name}
						</a>
					{/if}
				{else}
					{$header.name}
				{/if}
					{if $post.order_by == $header.dbname AND $header.dbname != FALSE}
						{if $get.direction == "ASC" || $post.direction == "ASC"}
						<img src="images/arrow_down.png">
						{else}
						<img src="images/arrow_up.png">
						{/if}
					{/if}
			</th>
			{/if}
		{/foreach}
		</tr>
		{assign var="ix" value="0"}
		{foreach from=$table_data item="row" name=tablerow}
			<tr class="{cycle values="odd,even" advance=true}">
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
							{elseif $item.type=='date'}
								{$row[$item.dbname]|date_format:"%D"}
							{elseif $item.type=='time'}
								{$row[$item.dbname]|date_format:"%I:%M:%S%p"}
							{elseif $item.type=='link'}
								{if $item.link_text}
									<a href="{$item.link}{$row[$item.link_subject]}">{$item.link_text}</a>
								{elseif $item.link}
									<a href="{$item.link}{$row[$item.link_subject]}">{$row[$item.dbname]}</a>
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
<a name="a_bottom"> </a>
