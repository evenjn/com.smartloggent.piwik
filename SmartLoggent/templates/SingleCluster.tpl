<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<div style="float: left;" class="listmenu">
<ul>
<li><a href="javascript:showDiv('main');">Evolution</a></li>
<li><a href="javascript:showDiv('distribution');">Distribution</a></li>
<li><a href="javascript:showDiv('searchphrases');">Search Phrases</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics</a></li>
<li><a href="javascript:showDiv('searchphrasesevolution');">Phrases Evolution</a></li>
<li><a href="javascript:showDiv('namedentitiesdistribution');">NE Distribution</a></li>
<li><a href="javascript:showDiv('namedentitiespopularity');">NE Popularity</a></li>
<li><a href="javascript:showDiv('classification');">Classification</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	<h1>{$cluster}</h1>
	{$evolution}
</div>

<div id="distribution" class="paneldiv">
	<h1>{$cluster}</h1>	
	{$distribution}	
</div>

<div id="searchphrases" class="paneldiv">
	<h1>{$cluster}</h1>	
	{$searchPhrases}	
</div>

<div id="metrics" class="paneldiv">
	<h1>{$cluster}</h1>
{foreach from=$singleClusterMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="searchphrasesevolution" class="paneldiv">
	<h1>{$cluster}</h1>	
	{$searchPhraseEvolution}	
</div>

<div id="namedentitiesdistribution" class="paneldiv">
	<h1>{$cluster}</h1>
	{$namedentitiesdistribution}
</div>

<div id="namedentitiespopularity" class="paneldiv">
	<h1>{$cluster}</h1>
	{$namedentitiespopularity}
</div>

<div id="classification" class="paneldiv">
	<h1>{$cluster}</h1>
	{$classification}
</div>

{foreach from=$detailcharts item=chart name=headdiv}
{if !$smarty.foreach.headdiv.first}
<div id='colunmInfo{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}<a href="javascript:hideColumnInfo({$chart.metric});">
	<img src="plugins/SmartLoggent/images/close.png" align="right" border="0" style="padding-right: 10px" />
</a>
</h2>
<hr>
{$chart.chartevolution}
<center>
<a href="javascript:hideColumnInfo({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/if}
{/foreach}


