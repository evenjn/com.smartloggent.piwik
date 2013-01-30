<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

{literal}
<script>
function showSingleClass(class_str) {
	newurl = {/literal}'{$singleClassUrl}'{literal} + "&class=" + class_str; 
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
	<h1>{$class}</h1>
	{$subClasses}
</div>

<div id="metrics" class="paneldiv">
	<h1>{$class}</h1>
{foreach from=$subClassesMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	<h1>{$class}</h1>
	{$subClassesEvolution}
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
