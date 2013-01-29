<?php

class Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphHTML_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution
{
	static public function factory($defaultType=null, $force=false)
	{
		return Piwik_SmartLoggent_Core_ViewDataTable::factory($defaultType, $force);
	}
}
