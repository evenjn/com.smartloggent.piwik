<?php
class Piwik_SmartLoggent_Controller extends Piwik_Controller
{

	private $array_metrics = array(
		Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
	,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
	,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
	,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
	,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
	,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
	,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
	,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
	,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
	,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
	);
	
	private $array_metrics_titles = array(
		Piwik_SmartLoggent_API::INDEX_AVG_CLICKS => 'LOC_SL_Column_AVG_CLICKS'
	,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS => 'LOC_SL_Column_AVG_RESULTS'
	,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY => 'LOC_SL_Column_CLICK_PROBABILITY'
	,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS => 'LOC_SL_Column_FR_CLICKS'
	,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES => 'LOC_SL_Column_FR_QUERIES'
	,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS => 'LOC_SL_Column_NB_CLICKS'
	,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES => 'LOC_SL_Column_NB_QUERIES'
	,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY => 'LOC_SL_Column_WEIGHTED_CLICK_PROBABILITY'
	,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS => 'General_ColumnNbUniqVisitors'
	,	Piwik_SmartLoggent_API::INDEX_NB_VISITS => 'General_ColumnNbVisits'
	);
	
	public function overview()
	{
		$view = new Piwik_View('SmartLoggent/templates/overview.tpl');
		
		$view1 = Piwik_ViewDataTable::factory('graphEvolution');
		$view1->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view1->setColumnsToDisplay( Piwik_SmartLoggent_API::INDEX_NB_VISITS);

		echo $view->render();
		
		$this->renderView($view1);
	}
	
	public function searchPhrase()
	{
		$view = new Piwik_View('SmartLoggent/templates/searchPhraseOverview.tpl');
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
																				  'action' => 'index',																				  
																				 ));
		$urlSl = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleSearchPhrase'));
		$view->singleSearchPhraseUrl = $urlIndex . "#" . substr($urlSl, 1);
		
		$view->searchPhrase = $this->getSearchPhrase(true);
		
		$searchPhraseMetrics = array();
		$detailcharts = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_searchPhraseMetrics = $this->getSearchPhraseMetricGraph($metric);
			$result_detail_metric_chart = $this->getSearchPhraseDetailMetricGraph($metric);
			$result_detail_evolution_chart = $this->getSearchPhraseDetailEvolution($metric);
			
			$searchPhraseMetrics[] = $result_searchPhraseMetrics;
			$detailcharts[$metric]['chartmetric'] = $result_detail_metric_chart;
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
				
		}
		
		$view->searchPhraseMetrics = $searchPhraseMetrics; 
		$view->searchPhraseEvolution = $this->getSearchPhraseEvolution();
		$view->searchPhrasePie = $this->getSearchPhasePie();
		$view->searchPhraseTagCloud = $this->getSearchPhaseTagCloud();
		$view->detailcharts = $detailcharts;
		
		echo $view->render();
	}
	
	public function singleSearchPhrase() {
		$view = new Piwik_View('SmartLoggent/templates/SingleSearchPhrase.tpl');
		$phrase = Piwik_Common::getRequestVar("phrase");
		$view->phrase = $phrase;
		$view->searchPhraseEvolution = $this->getSearchPhraseEvolution('getSingleSearchEvolutionData', array('searchPhrase'=>$phrase));
		$view->searchPhraseNamedEntities = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseNamedEntitiesData');
		$view->searchPhraseClass = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseClassData');
		$view->searchPhraseCluster = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseClusterData');
		$view->searchPhraseNaturalSearch = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseNaturalSearchData');
		$view->searchPhrasePie = $this->getSingleSearchPhasePie($phrase);
		
		echo $view->render();
	}

	public function classes()
	{
		$view = new Piwik_View('SmartLoggent/templates/classOverview.tpl');
		$view->class = $this->getClass(true);
		
		$classMetrics = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_classMetrics = $this->getClassMetricGraph($metric);
			$classMetrics[] = $result_classMetrics;
		}
		
		$view->classMetrics = $classMetrics;
		
		$view->evolution = $this->getClassEvolution(false, true);
		echo $view->render();
	}
	
	public function getSearchPhrase($fetch=false, $limit=20, $metric=Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
	{
		$view = Piwik_ViewDataTable::factory();		
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, $metric);		
			
		return $result;
	}
	
	public function getSingleSearchPhraseData($fetch=false, $limit=20, $searchPhrase=-1, $source=-1) {

		static $sf;
		static $src;
		if ($searchPhrase != -1) $sf = $searchPhrase;
		if ($source != -1) $src = $source;
		
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');

		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('searchPhrase' => '$sf'));
		
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit);
			
		return $result;
	}
	
	public function getSingleSearchPhasePie($searchPhrase=-1)
	{
		static $sf;
		if ($searchPhrase != -1) $sf = $searchPhrase;
		
		$view = Piwik_ViewDataTable::factory('graphPie');
		
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
		
		$dataTable = Piwik_SmartLoggent_API::getSearchPhraseGeoData($idSite, $period, $date, $segment, array('searchPhrase' => '$sf'));
		
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhraseDistributionData' );
		$view->setDatatable($dataTable);
		$view->setUniqueIdViewDataTable ("graph_gn_distrib");
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSearchPhraseMetricGraph($metric=-1, $limit=3)
	{
		static $mt;
		if ($metric != -1)
			$mt = $metric;
		
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase' );
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array('label', $mt));
		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
		$view->setSortedColumn($mt, 'desc');
		$view->disableFooter();
		$view->setAxisYUnit($mt);
		$view->setUniqueIdViewDataTable ("graph_gn_" . $mt);
		$view->setTemplate("SmartLoggent/templates/GraphMetric.tpl");
		$result = $this->renderView($view, true);
		return $result; 
	}

	public function getSearchPhraseDetailMetricGraph($metric=-1)
	{
		static $mt;
		if ($metric != -1)
			$mt = $metric;
	
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase' );
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array('label', $mt));
		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
		$view->setSortedColumn($mt, 'desc');
		$view->disableFooter();
		$view->setAxisYUnit($mt);
		$view->setUniqueIdViewDataTable ("graph_gn_detail_" . $mt);
		$view->setTemplate("SmartLoggent/templates/SearchPhraseGraphDetail.tpl");
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSearchPhraseEvolution($source=-1, $params = -1)
	{
		static $src;
		static $pars;
		
		if ($source != -1) $src = $source;
		if ($params != -1) $pars = $params;
		
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
		
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
		
		if ($src) {
			$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, $pars);
			$view->setDatatable($dataTable);
		}
		
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(2);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution");
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSearchPhraseDetailEvolution()
	{
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase' );
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_detail_evolution" . floor(rand(0,10000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSearchPhasePie()
	{
		$view = Piwik_ViewDataTable::factory('graphPie');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhraseDistributionData' );
		$view->setUniqueIdViewDataTable ("graph_gn_distrib");
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSearchPhaseTagCloud()
	{
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhraseTagCloudData' );
		$view->setUniqueIdViewDataTable ("graph_gn_cloud");
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getClass($fetch=false, $limit=20)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getClass');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', $fetch, $limit);
		
		return $result;
	}
	
	public function getClassMetricGraph($metric=-1, $limit=4)
	{
		static $mt;
		if ($metric != -1)
			$mt = $metric;
	
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getClass' );
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array('label', $mt));
		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
		$view->setSortedColumn($mt, 'desc');
		$view->disableFooter();
		$view->setAxisYUnit($mt);
		$view->setUniqueIdViewDataTable ("graph_gn_" . $mt);
		$view->setTemplate("SmartLoggent/templates/GraphMetric.tpl");
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function configureUsualTable($view, $labelLocalization, $fetch=false, $limit=20, $sortedColumn=Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
	{
		$view->setColumnsToDisplay(array_merge(array('label'), $this->array_metrics));

		$view->setColumnTranslation('label', Piwik_Translate($labelLocalization));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_AVG_CLICKS, Piwik_Translate('LOC_SL_Column_AVG_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_AVG_RESULTS, Piwik_Translate('LOC_SL_Column_AVG_RESULTS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY, Piwik_Translate('LOC_SL_Column_CLICK_PROBABILITY'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_FR_CLICKS, Piwik_Translate('LOC_SL_Column_FR_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_FR_QUERIES, Piwik_Translate('LOC_SL_Column_FR_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_CLICKS, Piwik_Translate('LOC_SL_Column_NB_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_QUERIES, Piwik_Translate('LOC_SL_Column_NB_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY, Piwik_Translate('LOC_SL_Column_WEIGHTED_CLICK_PROBABILITY'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS, Piwik_Translate('General_ColumnNbUniqVisitors'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS, Piwik_Translate('General_ColumnNbVisits'));
		
		$view->setSortedColumn($sortedColumn, 'desc');
		$view->disableShowAllViewsIcons();
		$view->disableShowAllColumns();
		$view->setLimit($limit);
		$view->setTemplate('SmartLoggent/templates/datatable.tpl');
		$result = $this->renderView($view, $fetch);
		return $result;
	}
	
	public function getClassEvolution( $columns = false, $fetch = false)
	{
		$view = $this->genericEvolution(__FUNCTION__, Piwik_SmartLoggent_API::DIM_CLASS, false, true, $columns);
		$result = $this->renderView($view, $fetch);
		return $result;
	}
	
	/**
	 * 
	 * @param unknown_type $function
	 * @param unknown_type $apimethod
	 * @param unknown_type $rowpicker currently the rowpicker cannot be displayed, so the only legal value is false.
	 * @param unknown_type $footer
	 * @param unknown_type $columns
	 */
	private function genericEvolution($function, $dimension, $rowpicker = false, $footer = true, $columns = false)
	{
		/*
		 * You can't pass parameters. Seriously. This method is designed to
		* invoke itself without any arguments.
		*
		* This is how Piwik_ViewDataTable::factory('graphEvolution') works
		*/
		$metric = Piwik_SmartLoggent_API::INDEX_NB_QUERIES;
		$view = $this->getLastUnitGraph($this->pluginName, $function, 'SmartLoggent.get'.$dimension);
	
		/*
		 * Don't disable the footer unless you also disable the rowpicker. See the following bug.
		* http://dev.piwik.org/trac/ticket/3313
		*/
		if (!$rowpicker)
		{
			/* but you may decide to disable the rowpicker and still keep the footer */
			if (!$footer)
				$view->disableFooter();
		}
	
		/*
		 * CATEGORIES SELECTION (ROWS)
		*
		* The visible rows are either chosen by the user using the rowPicker,
		* or the rows that correspond to the top items according to a metric
		*
		* If the user has picked some rows manually, they appear as a 'rows'
		* parameter.
		*/
		$visibleRows = Piwik_Common::getRequestVar('rows', false);
		if ($visibleRows !== false)
		{
			/* this happens when the row picker has been used */
			$visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
		}
		else
		{
			// we had a mechanism to specify the exact elements to show (the top 5)
			// but now this mechanism must be changed to fit wit the new API.
			// this mechanism is crucial to display only the few relevant items.
			//
			// the idea is to invoke one of the API methods that return the
			// identifiers of the top items ranked according the desired metric, such
			// as
			//
			// getTopClass_NB_UNIQ_VISITORS
			//
			// then either modify the segment so that the elements retrieved are only
			// those, or let the API know it in some other way.
			
			if (Piwik_Common::getRequestVar('smartloggent_reset_filter_evolution', 'yes', 'string') == 'yes')
			{
				$_GET['smartloggent_reset_filter_evolution'] = 'no';
				$_GET['smartloggent_filter_evolution'] = '';
				$visibleRows = self::getTop(Piwik_SmartLoggent_API::DIM_CLASS, $metric);
			
				if ($rowpicker)
				{
					$view->setParametersToModify(array('rows' => implode(',', $visibleRows)));
				}
				else
				{
					$filter_tops_array= array();
					foreach ($visibleRows as $lala)
					{
						$encoded = Piwik_SmartLoggent_API::encodeString($lala);
						$filter_tops_array [] = $encoded;
					}
					$_GET['smartloggent_filter_evolution'] = implode(',', $filter_tops_array);
				}
			}
		}
		if ($rowpicker)
			$view->addRowPicker($visibleRows);
	
		/* METRICS LABELS (COLUMNS) */
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_QUERIES, Piwik_Translate('LOC_SL_Column_NB_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS, Piwik_Translate(
				'General_ColumnNbUniqVisitors'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS, Piwik_Translate(
				'General_ColumnNbVisits'));
	
		/*
		 *  METRICS SELECTION (COLUMNS)
		 *
		 *  The metrics to display can be set programmatically with the $columns
		 *  argumnent.
		 */
		if(empty($columns))
		{
			
			$columns = Piwik_Common::getRequestVar('columns', Piwik_SmartLoggent_API::INDEX_NB_QUERIES);
			$columns = Piwik::getArrayFromApiParameter($columns);
		}
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
	
		/*
		 * It is not possible to use the row picker when there are no selectable
		 * columns.
		 */
		if ($rowpicker)
			$view->setSelectableColumns(array($metric, Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS));
		return $view;
	}
	
	public static function getTop($dimension, $metric)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
		return Piwik_SmartLoggent_API::getInstance()->getTop(Piwik_SmartLoggent_API::INDEX_NB_VISITS, $idSite, $period, $date, $segment, $dimension, $metric);
	}
}