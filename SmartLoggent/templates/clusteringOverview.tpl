<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<script>
 {literal}
function showSingleCluster(cluster_str) {
	newurl = {/literal}'{$singleClusterUrl}'{literal} + "&cluster=" + cluster_str; 
	document.location = newurl;
	
}
{/literal}
</script>

<div style="float: left;" class="listmenu">
Cluster analysis:
<select id="can">
{foreach from=$clusterAnalysis item=can}
	<option {if $canValue == $can.value}selected{/if} value="{$can.value}">{$can.title}</option>
 {/foreach}
</select>
<input type="button" value="go" onClick="document.location='{$canUrl}&can=' + $('#can option:selected').val();">
<ul>
<li><a href="javascript:showDiv('main');">Data</a></li>
<li><a href="javascript:showDiv('metrics');">Metrics</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution</a></li>
</ul>
</div>

<div id="main" class="paneldivMain">
	<br/><br/>
	{$clusters}
</div>

<div id="metrics" class="paneldiv">
	<br/><br/>
{foreach from=$clusterMetrics item=metric}
	{$metric}
{/foreach}
</div>

<div id="evolution" class="paneldiv">
	<br/><br/>
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