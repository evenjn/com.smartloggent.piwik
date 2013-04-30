<link rel="stylesheet" type="text/css" href="plugins/SmartLoggent/templates/sl.css" />
<script src="plugins/SmartLoggent/templates/sl.js" />

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

<div style="float: left;" class="listmenu">
<ul>
<li><a href="javascript:showDiv('main');">Evolution</a></li>
<li><a href="javascript:showDiv('namedentities');">Named Entities</a></li>
<li><a href="javascript:showDiv('class');">Class</a></li>
<li><a href="javascript:showDiv('cluster');">Cluster</a></li>
<li><a href="javascript:showDiv('natural');">Natural Search Phrases</a></li>
<li><a href="javascript:showDiv('geo');">Geographical</a></li>
</ul>
</div>

<div id="cover" class="paneldivMain">
{include file="SmartLoggent/templates/panelDivMain.tpl"}
</div>

<div id="main" class="paneldiv">
	<h1>{$phrase}</h1>
	{$searchPhraseEvolution}
</div>

<div id="namedentities" class="paneldiv">
	<h1>Named entities for: {$phrase}</h1>
	{$searchPhraseNamedEntities}
</div>

<div id="class" class="paneldiv">
	<h1>Class for: {$phrase}</h1>
	{$searchPhraseClass}
</div>

<div id="cluster" class="paneldiv">
	<h1>Cluster for: {$phrase}</h1>
	{$searchPhraseCluster}
</div>

<div id="natural" class="paneldiv">
	<h1>Natural search for: {$phrase}</h1>
	{$searchPhraseNaturalSearch}
</div>

<div id="geo" class="paneldiv">
	<h1>{$phrase}</h1>
	{$searchPhrasePie}
</div>