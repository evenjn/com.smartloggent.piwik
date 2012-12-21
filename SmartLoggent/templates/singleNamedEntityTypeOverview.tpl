<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<script>
 {literal}
function showSingleNamedEntityType(ne) {
	newurl = {/literal}'{$singleNEUrl}'{literal} + "&ne=" + ne; 
	document.location = newurl;
}
{/literal}
</script>

<div style="float: left;" class="listmenu">
<ul>
<li><a href="javascript:showDiv('main');">Data</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution</a></li>
<li><a href="javascript:showDiv('searchPhrases');">Search Phrases</a></li>
<li><a href="javascript:showDiv('searchPhrasesMetrics');">Search Phrases Metrics</a></li>
<li><a href="javascript:showDiv('searchPhrasesEvolution');">Search Phrases Evo</a></li>
<li><a href="javascript:showDiv('classes');">Classes</a></li>
<li><a href="javascript:showDiv('clusters');">Clusters</a></li>
</ul>
</div>

<div id="main" class="paneldivMain">
	{$singleNamedEntities}
</div>

<div id="metrics" class="paneldiv">
{foreach from=$singleNEMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	{$singleNEEvolution}
</div>

<div id="searchPhrases" class="paneldiv">
	{$searchPhrases}
</div>

<div id="searchPhrasesEvolution" class="paneldiv">
	{$searchPhrasesEvolution}
</div>

<div id="classes" class="paneldiv">
	{$classes}
</div>

<div id="clusters" class="paneldiv">
	{$clusters}
</div>

{foreach from=$detailcharts item=chart name=headdiv}
{if !$smarty.foreach.headdiv.first}
<div id='colunmInfo{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}</h2>
<hr>
{$chart.chartevolution}
<center>
<a href="javascript:hideColumnInfo({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/if}
{/foreach}

{foreach from=$searchPhrasesDetailMetrics item=chart name=headdiv}
{if !$smarty.foreach.headdiv.first}
<div id='columnInfoSF{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}</h2>
<hr>
{$chart.chartmetric}
<center>
<a href="javascript:hideColumnInfoSF({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/if}
{/foreach}