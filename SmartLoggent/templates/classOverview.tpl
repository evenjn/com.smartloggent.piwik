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
<li><a href="javascript:showDiv('metrics');">Metrics charts</a></li>
<li><a href="javascript:showDiv('evolution');">Evolution chart</a></li>
</ul>
</div>

{literal}
<script>

function showSingleClasses(classid_str, class_str) {
	newurl = {/literal}'{$singleClassUrl}'{literal} + "&class=" + class_str + "&classid=" + classid_str; 
	document.location = newurl;
}

function showSubClasses(classid_str, class_str) {
	newurl = {/literal}'{$subClassUrl}'{literal} + "&class=" + class_str + "&classid=" + classid_str; 
	document.location = newurl;
}

</script>
{/literal}

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="cover" class="paneldiv">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
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
