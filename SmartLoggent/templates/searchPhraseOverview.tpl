<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
{literal}
<script>
function showDiv(id) {
	$('.paneldivMain').fadeOut();
	$('.paneldiv').fadeOut();
	$('#' + id).fadeIn();
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
<div style='display: none; background: #FFFFFF; position: absolute; top: 220px; z-index: 99; left: 120px; width: 900px; height: 600px; -moz-border-radius: 15px; border-radius: 15px; border: 3px solid #aaaaaa;' id='colunmInfo{$chart.metric}'>
<h2>Metric: {$chart.title}</h2>
<hr>
{$chart.chartmetric}
<br/>
{$chart.chartevolution}
<a href="javascript:hideColumnInfo({$chart.metric});">OK</a>
</div>
{/if}
{/foreach}
