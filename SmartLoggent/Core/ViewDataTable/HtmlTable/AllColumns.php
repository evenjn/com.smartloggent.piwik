<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: AllColumns.php 6398 2012-05-30 09:03:12Z matt $
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_SmartLoggentView
 */
class Piwik_SmartLoggent_Core_ViewDataTable_HtmlTable_AllColumns extends Piwik_ViewDataTable_HtmlTable 
{
	protected function getViewDataTableId()
	{
		return 'tableAllColumns';
	}
	
	public function main()
	{
		$this->viewProperties['show_exclude_low_population'] = true;
		parent::main();
	}
	
	static public function factory($defaultType=null, $force=false)
	{
	  return Piwik_SmartLoggent_Core_ViewDataTable::factory($defaultType, $force);
	}
	
	protected function getRequestString()
	{
		$requestString = parent::getRequestString();
		return $requestString . '&filter_add_columns_when_show_all_columns=1';
	}
	
	protected function postDataTableLoadedFromAPI()
	{
		$valid = parent::postDataTableLoadedFromAPI();
		if(!$valid) return false;
		Piwik_Controller::setPeriodVariablesView($this);
// 		$columnUniqueVisitors = false;
// 		if($this->period == 'day')
// 		{
// 			$columnUniqueVisitors = 'nb_uniq_visitors';
// 		}
		
		// only display conversion rate for the plugins that do not provide "per goal" metrics
		// otherwise, conversion rate is meaningless as a whole (since we don't process 'cross goals' conversions)
// 		$columnConversionRate = false;
// 		if(empty($this->viewProperties['show_goals']))
// 		{
// 			$columnConversionRate = 'conversion_rate';
// 		}
		$this->setColumnsToDisplay(array(

				'label'
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
		));
// 		$this->dataTable->filter('ColumnCallbackReplace', array('avg_time_on_site', create_function('$averageTimeOnSite', 'return Piwik::getPrettyTimeFromSeconds($averageTimeOnSite);')));
		
		return true;
	}
}
