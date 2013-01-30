<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

<script>
 {literal}
function showSingleCluster(cluster_str, cluster_id) {
	newurl = {/literal}'{$singleClusterUrl}'{literal} + "&cluster=" + cluster_str + "&clusterid=" + cluster_id; 
	document.location = newurl;
	
}
{/literal}
</script>

<div style="float: left;" class="listmenu" >
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

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
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
