<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<div style="float: left;" class="listmenu">

{literal}
<script>

function showSingleSearchPhrase(phraseid_str, phrase_str) {
	newurl = {/literal}'{$singleSearchPhraseUrl}'{literal} + "&phrase=" + phrase_str + "&phrase_id=" + phraseid_str; 
	document.location = newurl;
}

</script>
{/literal}

<select id="lang" onChange="document.location=mixUrl(window.location, 'language', this.options[this.selectedIndex].value);" name="language">>
{foreach from=$availableLanguages item=language}	
	<option {if $languageValue == $language.value}selected{/if} value="{$language.value}">{$language.label}</option>
 {/foreach}
</select>

<ul>
<li><a href="javascript:showDiv('main');">Class Search Phrases</a></li>
<li><a href="javascript:showDiv('visits');">Visits</a></li>
<li><a href="javascript:showDiv('country');">Country</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution</a></li>
<li><a href="javascript:showDiv('namedentitiesdistrib');">NE Distribution</a></li>
<li><a href="javascript:showDiv('namedentitiespopularity');">NE Popularity</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	<h1>{$class}</h1>
	{$searchPhraseClass}
</div>

<div id="visits" class="paneldiv">
	<h1>{$class}</h1>
	{$singleClassVisitsEvolution}
</div>

<div id="country" class="paneldiv">
	<h1>{$class}</h1>
	{$singleClassDistribution}
</div>

<div id="metrics" class="paneldiv">
	<h1>{$class}</h1>
{foreach from=$singleClassesMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	<h1>{$class}</h1>
	{$singleClassesEvolution}
</div>

<div id="namedentitiesdistrib" class="paneldiv">
	<h1>{$class}</h1>
	{$namedEntitiesDistribution}
</div>

<div id="namedentitiespopularity" class="paneldiv">
	<h1>{$class}</h1>
	{$namedEntitiesPopularity}
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

{literal}
{/literal}

