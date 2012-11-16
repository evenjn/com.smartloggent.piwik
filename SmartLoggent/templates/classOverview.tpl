<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<div style="float: left;" class="listmenu">
<ul>
<li><a href="javascript:showDiv('main');">Data</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics charts</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution chart</a></li>
</ul>
</div>

{literal}
<script>

function showSingleClasses(class_str) {
	newurl = {/literal}'{$singleClassUrl}'{literal} + "&class=" + class_str; 
	document.location = newurl;
}

function showSubClasses(class_str) {
	newurl = {/literal}'{$subClassUrl}'{literal} + "&class=" + class_str; 
	document.location = newurl;
}

</script>
{/literal}

<div id="main" class="paneldivMain">
	<div>
		<h2 class="sl_hasdescription">{'LOC_SL_ClassOverviewPageTitle'|translate}</h2>
		<div class="sl_description_wrapper">
			<div class="sl_description">
				<p class="sl_main_description">
					{'LOC_SL_ClassOverviewDescription'|translate}
				</p>
			</div>
		</div>
		{$class}		
	</div>	
</div>

<div id="metrics" class="paneldiv">
{foreach from=$classMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	{$evolution}
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
