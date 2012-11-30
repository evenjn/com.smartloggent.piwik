<?php


class Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphHTML_ChartVerticalBar
extends Piwik_ViewDataTable_GenerateGraphHTML
{
  static public function factory($defaultType=null, $force=false)
  {
    return Piwik_SmartLoggent_Core_ViewDataTable::factory($defaultType, $force);
  }

  protected $graphType = 'bar';

  protected function getViewDataTableId()
  {
    return 'graphVerticalBar';
  }

  protected function getViewDataTableIdToLoad()
  {
    return 'generateDataChartVerticalBar';
  }
}
