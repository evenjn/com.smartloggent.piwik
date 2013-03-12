<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

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
<li><a href="javascript:showDiv('searchPhrases');">Search Phrases</a></li>
<li><a href="javascript:showDiv('searchPhrasesMetrics');">Search Phrases Metrics</a></li>
<li><a href="javascript:showDiv('searchPhrasesEvolution');">Search Phrases Evo</a></li>
<li><a href="javascript:showDiv('classes');">Classes</a></li>
<li><a href="javascript:showDiv('clusters');">Clusters</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$singleNamedEntities}
</div>

<div id="metrics" class="paneldiv">
	<h1>{$namedEntityType}</h1>
{foreach from=$singleNEMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$singleNEEvolution}
</div>

<div id="searchPhrases" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$searchPhrases}
</div>

<div id="searchPhrasesEvolution" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$searchPhrasesEvolution}
</div>

<div id="classes" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$classes}
</div>

<div id="clusters" class="paneldiv">
	<h1>{$namedEntityType}</h1>
	{$clusters}
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



