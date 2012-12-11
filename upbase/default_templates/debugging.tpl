<a id="debugging_toggle" href="javascript:void(0);" onclick="toggle_display('debugging');"><img src="upbase/images/debugging.png"></a>
<div id="debugging">
	<div class="menu_box">	
		<h1>Debugging</h1>
		<h2>General</h2>
		<table class="data_table">
			<tr>
				<th>Property</th><th>Value</th>
			</tr>
			{foreach from=$debugging key=name item=data}
			<tr class="{cycle values="odd,even" advance=true}">
				<td>{$name}</td><td>{$data}</td>
				<td>
				</tr>
				{/foreach}
		</table>
		<h2>Timing</h2>
		<table class="data_table">
			<tr>
				<th>Section</th><th>Duration</th><th>Count</th>
			</tr>
			{foreach from=$timing key=name item=data}
			<tr class="{cycle values="odd,even" advance=true}">
				<td>
					{foreach from=$data.depth item=parent}
					&nbsp;
					{/foreach}
					{$name}
				</td>
				<td>
					{foreach from=$data.depth item=parent}
					&nbsp;
					{/foreach}
					{$data.duration|string_format:"%.8f"}
				</td>
				<td>{$data.count}</td>
				<td>
			</tr>
			{/foreach}
		</table>
		<h2>Smarty</h2>
		<table class="data_table">
    <th>Template</th><th>Duration</th>
    {assign_debug_info}
    {section name=templates loop=$_debug_tpls}
      <tr class="{cycle values="odd,even" advance=true}">
      <td>
        {section name=indent loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}
        {$_debug_tpls[templates].filename|escape:html}
      </td>
      <td>
        {section name=indent loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}
        {if isset($_debug_tpls[templates].exec_time)}
          {$_debug_tpls[templates].exec_time|string_format:"%.5f"}
        {else}
        (running at time of timing collection)
        {/if}
        
      </td>
      </tr>
    {/section}
   </table>
  <h2>AJAX</h2>
  <div id="DEBUG[ajax_output]"></div>

	</div>
</div>
