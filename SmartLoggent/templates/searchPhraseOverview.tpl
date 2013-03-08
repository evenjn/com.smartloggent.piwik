<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

{literal}
<script>
function showSingleSearchPhrase(phrase, phrase_id) {
	newurl = {/literal}'{$singleSearchPhraseUrl}'{literal} + "&phrase=" + phrase + "&phrase_id=" + phrase_id; 
	document.location = newurl;
}

</script>
{/literal}

<div style="float: left;" class="listmenu">
<ul>
<li><a href="javascript:showDiv('main');">Data</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics charts</a></li>
<li><a href="javascript:showDiv('tagcloud');">Tag Cloud</a></li>
<li><a href="javascript:showDiv('pie');">Distribution</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution chart</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	<div>
		<h2 class="sl_hasdescription">{'LOC_SL_SearchPhraseOverviewPageTitle'|translate}</h2>
		<div class="sl_description_wrapper">
			<div class="sl_description">
				<p class="sl_main_description">
					{'LOC_SL_SearchPhraseOverviewTableDescription'|translate}
				</p>
			</div>
		</div>
		{$searchPhrase}		
	</div>	
</div>

<div id="metrics" class="paneldiv">
{foreach from=$searchPhraseMetrics item=metric}
<div class="metricgraph">
	{$metric}
</div>
</br>
{/foreach}
</div>

<div id="tagcloud" class="paneldiv">
	{$searchPhraseTagCloud}
</div>

<div id="pie" class="paneldiv">
	{$searchPhrasePie}
</div>

<div id="evolution" class="paneldiv">
	{$searchPhraseEvolution}
</div>

{foreach from=$detailcharts item=chart name=headdiv}
<div id='colunmInfo{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}
<a href="javascript:hideColumnInfo({$chart.metric});">
	<img src="plugins/SmartLoggent/images/close.png" align="right" border="0" style="padding-right: 10px" />
</a>
</h2>
<hr>
{$chart.chartmetric}
<br/>
{$chart.chartevolution}
<center>
<br/><br/>
<a href="javascript:hideColumnInfo({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/foreach}
