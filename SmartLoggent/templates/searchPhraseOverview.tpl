<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

{literal}
<script>

function showSingleSearchPhrase(phrase) {
	newurl = {/literal}'{$singleSearchPhraseUrl}'{literal} + "&phrase=" + phrase; 
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

<div id="main" class="paneldivMain">
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
	{$metric}
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
{if !$smarty.foreach.headdiv.first}
<div id='colunmInfo{$chart.metric}' class='columndiv'>
<h2>Metric: {$chart.title}</h2>
<hr>
{$chart.chartmetric}
<br/>
{$chart.chartevolution}
<center>
<a href="javascript:hideColumnInfo({$chart.metric});">CLOSE THIS WINDOW</a>
</center>
</div>
{/if}
{/foreach}


