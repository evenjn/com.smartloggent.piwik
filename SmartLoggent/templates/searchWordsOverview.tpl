<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

{literal}
<script>

function showSingleSearchWord(sw) {
	newurl = {/literal}'{$singleSWUrl}'{literal} + "&searchword=" + sw; 
	document.location = newurl;
}
</script>
{/literal}

<div style="float: left;" class="listmenu">
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


