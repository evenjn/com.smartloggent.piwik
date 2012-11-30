<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ChartVerticalBar.php 5457 2011-11-22 10:15:49Z EZdesign $
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_ViewDataTable_GenerateGraphData for the vertical bar graph, using Piwik_Visualization_Chart_VerticalBar
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphData_ChartVerticalBar extends Piwik_ViewDataTable_GenerateGraphData
{
  protected $graphLimit = 6;

  protected function getViewDataTableId()
  {
    return 'generateDataChartVerticalBar';
  }

  function __construct()
  {
     
    /**
     *
     * @var Piwik_Visualization_Chart_VerticalBar
     */
    $charty = new Piwik_Visualization_Chart_VerticalBar();
    $charty->showAllTicks();
    //$charty->setXSteps(1);
    $this->view = $charty ;
  }

  protected function getUnitsForColumnsToDisplay()
  {
    // the bar charts contain the labels a first series
    // this series has to be removed from the units
    $units = parent::getUnitsForColumnsToDisplay();
    array_shift($units);
    return $units;
  }
}
