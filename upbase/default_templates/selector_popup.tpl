{* Smarty *}
<html lang="en">
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
	<title>McKesson Corporation - Selector Popup</title>
	<link rel="stylesheet" href="shared_files/mck.css" type="text/css" />
	<link rel="stylesheet" href="shared_files/style.css" type="text/css" /> 

</head>
<body>
<html>
	<script language="JavaScript" src="shared_files/util.js"></script>
	<form method="get" action="{$smarty.server.SCRIPT_NAME}" name="search"> 
		Search using one of the following methods:
		<ul>
		<li>Enter the beginning of the desired item</li>
		<li>Enter % followed by the middle to end of the desired item</li>
		<li>Page/scroll through the list</li>
		</ul>
		<div class="nobr">
			Find: &nbsp; <input type="text" name="search" tabindex="0" value="{$search}"> &nbsp; <input type="submit" value="Search">
			<input type="hidden" name="item" value="{$target_name}">
			<input type="hidden" name="context" value="{$context}">
			<input type="hidden" name="target_fields" value="{$target_fields}">
			<input type="hidden" name="search_fields" value="{$search_fields}">
			<input type="hidden" name="extra" value="{$extra}">
			<input type="hidden" name="screen" value="selector_popup">
			&nbsp;<input type="button" value="Select Blank Value" 
					onClick="
						{foreach item=item from=$target_map}
						self.opener.document.forms[0].{$item.target_field}.value='';
						{/foreach}
						window.close();">
		</div>

				Pages:

					{foreach key=key item=item from=$pages name=pageforeach}
						{if $item == $page}
							{$key}
						{else}
						<a href="screen.php?screen=selector_popup&item={$target_name}&context={$context}&page={$item}&search={$search}&search_fields={$search_fields}&target_fields={$target_fields}&extra={$extra}">{$key}</a> 
						{/if}
						{if !$smarty.foreach.pageforeach.last}, {/if}
					{/foreach}

		<table width="auto" cellpadding="2">
		<tbody>
		{if !$context}
		<tr><td><div class="error">You must use the selector within a specific context</div></td></tr>
		{elseif $error}
		<tr><td><div class="error">{$error}</div></td></tr>
		{else}
			{if $rslt ne ""}
			<tr bgcolor="#aaaaaa">
			{foreach from=$result_headers item="header"}
				<td>{$header.name}</td>
			{/foreach}
			</tr>
			{foreach from=$rslt item="row"}
				<tr bgcolor="{cycle values="#fff1e9,#ddf4ff" advance=true}" 
					onMouseOver="hover_on(this, '#99cdff');" 
					onMouseOut="hover_off(this);" 
					onClick="
						{foreach item=item from=$target_map}
						self.opener.document.forms[0].{$item.target_field}.value='{$row[$item.search_field]|escape:"quotes"}';
						{/foreach}
						window.close();">

				{foreach from=$result_headers item="item"}
					<td class="flowed">
						{$row[$item.dbname]}
					</td>
				{/foreach}
				</tr>
			{/foreach}
			<tr><td>
					{if $page != 1}
					<a href="screen.php?screen=selector_popup&item={$target_name}&context={$context}&page={$page-1}&search={$search}&search_fields={$search_fields}&target_fields={$target_fields}&extra={$extra}">&lt;prev </a> 
					{/if}
					|
					{if $page != $key}
					<a href="screen.php?screen=selector_popup&item={$target_name}&context={$context}&page={$page+1}&search={$search}&search_fields={$search_fields}&target_fields={$target_fields}&extra={$extra}">next&gt;</a> 
					{/if}
					</td></tr>
			{else}
				<tr><td colspan=1>No search results match current specifications.</td></tr>
			{/if}
		{/if}
		</tbody>
		</table>
	</form>

	<script type="text/javascript">
	document.forms.search.search.focus();
	</script>
