<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

{literal}
<script>

function showSingleSearchWord(id, sw) {
	newurl = {/literal}'{$singleSWUrl}'{literal} + "&swid=" + id + "&sw=" + sw; 
	document.location = newurl;
}
</script>
{/literal}

<div style="float: left;" class="listmenu">

<select id="lang" onChange="document.location=mixUrl(window.location, 'language', this.options[this.selectedIndex].value);" name="language">>
{foreach from=$availableLanguages item=language}	
	<option {if $languageValue == $language.value}selected{/if} value="{$language.value}">{$language.label}</option>
 {/foreach}
</select>

<ul>
<li><a href="javascript:showDiv('main');">Data</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	{$searchwords}
</div>

<div id="metrics" class="paneldiv">
	{foreach from=$searchwordsMetric item=metric}
		{$metric}
	{/foreach}
</div>

<div id="evolution" class="paneldiv">
	{$searchwordsEvolution}
</div>

{foreach from=$detailcharts item=chart name=headdiv}
<div id='colunmInfo{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}
<a href="javascript:hideColumnInfo({$chart.metric});">
	<img src="plugins/SmartLoggent/images/close.png" align="right" border="0" style="padding-right: 10px" />
</a>
</h2>
<hr>
{$chart.chartevolution}
<center>
<a href="javascript:hideColumnInfo({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/foreach}



