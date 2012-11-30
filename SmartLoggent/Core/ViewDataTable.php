<?php


abstract class Piwik_SmartLoggent_Core_ViewDataTable
extends Piwik_ViewDataTable
{
  static public function factory($defaultType=null, $force=false)
  {
    if (is_null($defaultType))
    {
      $defaultType = 'table';
    }
    if ($force === true){
      $type = $defaultType;
    }
    else
    {
      $type = Piwik_Common::getRequestVar('viewDataTable', $defaultType, 'string');
    }
    switch ($type)
    {
      case 'graphVerticalBar':
        return new Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphHTML_ChartVerticalBar();
      case 'generateDataChartVerticalBar':
        return new Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphData_ChartVerticalBar();
      case 'tableAllColumns':
        return new Piwik_SmartLoggent_Core_ViewDataTable_HtmlTable_AllColumns();
      default:
        return Piwik_ViewDataTable::factory($type);
    }
  }
}

?>
