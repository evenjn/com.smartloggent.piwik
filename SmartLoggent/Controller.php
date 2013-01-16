<?php
class Piwik_SmartLoggent_Controller extends Piwik_Controller
{
	private $metrics_with_topN = array(
			Piwik_SmartLoggent_API::INDEX_NB_VISITS
			,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
			,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
	);

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
		
		// In order to retrieve information of visits&actions partitioned by query
		// class where the class is a top-level class, we use the getClass API
		// method with a segment constraint that filters out all the classes that
		// are not top-level
		//  
		// we store the original segment
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
		$originalSegment = $segment;
		// we modify the current segment to set the desired constratint
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_TOPCLASS, "==", '1', $originalSegment);
		// we get the data
		$view->class = $this->getClass(true);

		// we compute the classes to be shown as rows in the evolution graph
		// the top 4 (sorted by number of queries) top-level classes 
		$autoRows = self::getTop(Piwik_SmartLoggent_API::DIM_CLASS, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 4);
		$_GET['autorows'] = implode(',', $autoRows['labels']);
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlSingleClass = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleClasses'));
		$urlSubClass = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'subClasses'));
		$view->singleClassUrl = $urlIndex . "#" . substr($urlSingleClass, 1);
		$view->subClassUrl = $urlIndex . "#" . substr($urlSubClass, 1);
		
		$classMetrics = array();
		$detailcharts = array();

		foreach ($this->array_metrics as $metric) {
			$result_classMetrics = $this->getClassMetricGraph($metric);
			$result_detail_evolution_chart = $this->getClassDetailEvolution("getClassDetailEvolutionData", array('metric' => $metric));
				
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);

			$classMetrics[] = $result_classMetrics;
		}

		$view->classMetrics = $classMetrics;
		$view->detailcharts = $detailcharts;
		
		$_GET['segment'] = $originalSegment;
		
// 		// in addition to that, we allow the user to display additional rows
// 		// with this global variable we set the data that will be available to the
// 		// user in the rowpicker
// 		$generalRows = self::getTop(Piwik_SmartLoggent_API::DIM_CLASS, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 2);
// 		// we merge the arrays
// 		$generalRowsIds = $generalRows['ids'];
		$autoRowsIds = $autoRows['ids'];
// 		foreach ($autoRowsIds as $ar)
// 		{
// 			if (!in_array($ar, $generalRowsIds))
// 				$generalRowsIds[] = $ar;
// 		}
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_CLASS, '==', implode('_', $autoRowsIds), $originalSegment);
		$view->evolution = $this->getClassByQueriesEvolution(false, true);

		echo $view->render();
		$_GET['autorows'] = '';
		$_GET['segment'] = $originalSegment;
	}

	public function singleClasses() {
		$view = new Piwik_View('SmartLoggent/templates/SingleClasses.tpl');
		// we get the parameters from the URL.
		$className = Piwik_Common::getRequestVar("className");
		$classId = Piwik_Common::getRequestVar("classId");
		$decodedClassName = Piwik_SmartLoggent_API::decodeString($className);
		
		// we set the segment constraint to filter out information that is not
		// the class the user requested
		$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_CLASS, "==", $classId, $originalSegment);
		
		$searchPhraseClass = Piwik_ViewDataTable::factory();
		$searchPhraseClass->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->searchPhraseClass = $this->configureUsualTable($searchPhraseClass, 'LOC_SL_Column_Label_SearchPhrase', true, 20, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleClassDatatable.tpl');

		$view->class = $decodedClassName;

		$singleClassesMetrics = array();
		$detailcharts= array();


		foreach ($this->metrics_with_topN as $metric) {
			// the segment constraint is set, so API methods will
			// return information that is filtered by class
			$result_singleClassesMetrics = $this->getSearchPhraseByAnonymous($metric);
// 			$result_singleClassesMetrics = $this->getSearchPhraseClassMetricsGraph($metric);
			$result_detail_evolution_chart = $this->getSingleClassDetailEvolution("getSingleClassDetailEvolutionData", array('metric' => $metric));

			$singleClassesMetrics[] = $result_singleClassesMetrics;
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);

		}
		$_GET['segment'] = $originalSegment;
		$view->singleClassesMetrics = $singleClassesMetrics;
		// evolution: we setup the segment constraints to include only the focused class
		

		$autoRows = array('labels' => array($decodedClassName), 'ids' => array($classId));
		$_GET['autorows'] = implode(',', $autoRows['labels']);
		
// 		// in addition to that, we allow the user to display additional rows
// 		// with this global variable we set the data that will be available to the
// 		// user in the rowpicker
// 		$generalRows = self::getTop(Piwik_SmartLoggent_API::DIM_CLASS, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 2);
// 		// we merge the arrays
// 		$generalRowsIds = $generalRows['ids'];
		$autoRowsIds = $autoRows['ids'];
// 		foreach ($autoRowsIds as $ar)
// 		{
// 			if (!in_array($ar, $generalRowsIds))
// 				$generalRowsIds[] = $ar;
// 		}
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_CLASS, '==', implode('_', $autoRowsIds), $originalSegment);
		
		
		
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_CLASS, '==', $classId, $originalSegment);
		$view->singleClassesEvolution = $this->getClassByQueriesEvolution(false, true);
// 		$view->singleClassesEvolution = $this->getSingleClassPhraseEvolution();
		$view->detailcharts = $detailcharts;
		$view->namedEntitiesDistribution = $this->getSingleClassNamedEntitiesDistributionPie();
		$view->namedEntitiesPopularity = $this->getSingleClassNamedEntitiesPopularityGraph();
		$view->singleClassVisitsEvolution = $this->getSingleClassVisitsEvolution();
		$view->singleClassDistribution = $this->getSingleClassDistributionPie();
		
		echo $view->render();
	}

	public function subClasses() {
		$view = new Piwik_View('SmartLoggent/templates/SubClasses.tpl');
		// we get the parameters from the URL.
		$className = Piwik_Common::getRequestVar("className");
		$classId = Piwik_Common::getRequestVar("classId");
		$decodedClassName = Piwik_SmartLoggent_API::decodeString($className);
		
		
		$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
		$segment = Piwik_SmartLoggent_SegmentEditor::set("SLSuperClass", "==", $classId, $originalSegment);
		$segment = Piwik_SmartLoggent_SegmentEditor::set("SLDirectSubClass", "==", '1', $segment);
		$_GET['segment'] = $segment;
		$view->subClasses = $this->getSubClasses('getSearchPhraseClassData', $class);
		
		$_GET['segment'] = $originalSegment;
		$view->class = $decodedClassName;
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlSingleClass = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleClasses'));
		$view->singleClassUrl = $urlIndex . "#" . substr($urlSingleClass, 1);
		
		$subClassesMetrics = array();
		$detailcharts = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_subClassMetrics = $this->getSubClassesMetricGraph($decodedClassName, $metric);
			$result_detail_evolution_chart = $this->getSubClassDetailEvolution("getSubClassDetailEvolutionData", array('metric' => $metric, 'class' => $decodedClassName));
		
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		
			$subClassesMetrics[] = $result_subClassMetrics;
		}
		
		$view->subClassesMetrics = $subClassesMetrics;
		$view->detailcharts = $detailcharts;
		$view->subClassesEvolution = $this->getSubClassesEvolution($decodedClassName);
		
		echo $view->render();
	}
	
	public function clustering()
	{
		$view = new Piwik_View('SmartLoggent/templates/clusteringOverview.tpl');
		
		$clusterAnalysis = Piwik_SmartLoggent_API::getClusterAnalysis();
		$view->clusterAnalysis = $clusterAnalysis;
		
		$can = Piwik_Common::getRequestVar("can", $clusterAnalysis[0]['value']);
		$view->canValue = $can;
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlCan = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'clustering'));
		$singleClusterUrl = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleCluster'));
		$view->canUrl = $urlIndex . "#" . substr($urlCan, 1);
		$view->singleClusterUrl = $urlIndex . "#" . substr($singleClusterUrl, 1);
		
		$clusterMetrics = array();
		$detailcharts = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_clusterMetrics = $this->getClusterMetricGraph($metric);
			$result_detail_evolution_chart = $this->getClusterDetailEvolution($metric);
		
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		
			$clusterMetrics[] = $result_clusterMetrics;
		}
		
		$view->clusterMetrics = $clusterMetrics;
		$view->detailcharts = $detailcharts;
		
		$view->clusters = $this->getClusters(true);
		$view->evolution = $this->getClusterEvolution();
		
		echo $view->render();

	}

	public function singleCluster()
	{
		$view = new Piwik_View('SmartLoggent/templates/SingleCluster.tpl');
		
		$cluster = Piwik_Common::getRequestVar("cluster");
		$view->cluster = $cluster;
		
		$singleClusterMetrics = array();
		$detailcharts = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_singleClusterMetrics = $this->getSingleClusterMetricGraph($cluster, $metric);
			$result_detail_evolution_chart = $this->getSingleClusterPhraseDetailEvolution(array("cluster" => $cluster, "metric" => $metric));
		
			$singleClusterMetrics[] = $result_singleClusterMetrics;
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		
		}
		
		$view->evolution = $this->getSingleClusterEvolution($cluster);
		$view->distribution = $this->getSingleClusterDistributionPie($cluster);
		$view->searchPhrases = $this->getSingleClusterSearchPhrases($cluster);
		$view->singleClusterMetrics = $singleClusterMetrics;
		$view->searchPhraseEvolution = $this->getSingleClusterPhraseEvolution($cluster);
		$view->detailcharts = $detailcharts;
		$view->namedentitiesdistribution = $this->getSingleClusterNamedEntitiesDistributionPie($cluster);
		$view->namedentitiespopularity = $this->getSingleClusterNamedEntitiesPopularity($cluster);
		$view->classification = $this->getSingleClusterClassification($cluster);
		
		echo $view->render();
	}
	
	
	public function singleNamedEntityType() {
		$view = new Piwik_View('SmartLoggent/templates/singleNamedEntityTypeOverview.tpl');
		$namedEntityType = Piwik_Common::getRequestVar("ne");
		
		$view->namedEntityType = $namedEntityType;
	
		$view->singleNamedEntities = $this->getSingleNamedEntityData(true, 20, $namedEntityType, 'getSingleNamedEntityData');
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlSl = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleNamedEntity'));
		$view->singleNEUrl = $urlIndex . "#" . substr($urlSl, 1);
		
		$singleNEMetrics = array();
		$detailcharts= array();
		$searchPhrasesDetailMetrics = array();
				
		foreach ($this->array_metrics as $metric) {
			$result_NEMetrics = $this->getSingleNEMetricsGraph($metric);
			$result_detail_evolution_chart = $this->getNamedEntityTypeDetailEvolution("getNamedEntityTypeDetailEvolutionData", array('namedEntityType' => $namedEntityType, 'metric' => $metric));
			
			$result_NESearchPhrasesDetailMetrics = $this->getSingleNESearchPhrasesMetricsGraph($metric);
			
			$singleNEMetrics[] = $result_NEMetrics;
			
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
	
			$searchPhrasesDetailMetrics[$metric]['chartmetric'] = $result_NESearchPhrasesDetailMetrics;
			$searchPhrasesDetailMetrics[$metric]['metric'] = $metric;
			$searchPhrasesDetailMetrics[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		}
	
		$view->singleNEMetrics = $singleNEMetrics;
		$view->singleNEEvolution = $this->getNEEvolution($namedEntityType);
		$view->detailcharts = $detailcharts;
		
		$view->searchPhrases = $this->getSearchPhrasesNamedEntityType(true, 20, $namedEntityType, "getSearchPhrasesNamedEntityType");
		$view->searchPhrasesDetailMetrics = $searchPhrasesDetailMetrics;
		$view->searchPhrasesEvolution = $this->getSingleNESearchPhrasesEvolutionGraph();
		
		$view->classes = $this->getSingleNamedEntityClassesData(true, 20, $namedEntityType, 'getSingleNamedEntityClassesData');
		
		$view->clusters = $this->getSingleNamedEntityClustersData(true, 20, $namedEntityType, 'getSingleNamedEntityClustersData');
				
		echo $view->render();
	}
	
	public function getSearchPhrase($fetch=false, $limit=20, $metric=Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, $metric, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/searchPhraseDatatable.tpl');
			
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
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/searchPhraseDatatable.tpl');
			
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
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/classDatatable.tpl');

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

	public function getClassDetailEvolution($source=-1, $params = -1)
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
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSearchPhraseByAnonymous($metric=-1)
	{
		static $m;
		if ($metric != -1) $m = $metric;
		$limit = 3;
		$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
		$tops = self::getTop(Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $metric, $limit);
		$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set(Piwik_SmartLoggent_API::SEG_SEARCHPHRASE, '==', implode('_', $tops['ids']), $originalSegment);
		$result= $this->getSearchPhraseByAnonymousRecursive($m);
		
		$_GET['segment'] = $originalSegment;
		return $result;
	}
	
	public function getSearchPhraseByAnonymousRecursive($metric=-1)
	{
		static $m;
		if ($metric != -1) $m = $metric;
		return $this->getSearchPhraseBy(__FUNCTION__, $m, true);
	}

	private function getSearchPhraseBy($function, $metric, $fetch = false)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_Controller::'.__FUNCTION__); // 		Piwik::profileend($profiler);

		$view = Piwik_SmartLoggent_Core_ViewDataTable::factory('graphVerticalBar');
		$view->init($this->pluginName, $function, 'SmartLoggent.getSearchPhrase');
		
		
		$view->setColumnsToDisplay(array('label',	$metric));
		$view->setColumnTranslation($metric,  Piwik_Translate($this->array_metrics_titles[$metric]));
		$view->setSortedColumn($metric, 'desc');
// 		$view->setAxisYUnit($metric);
		$view->disableSearchBox();
		$view->setUniqueIdViewDataTable ("graph_gn_detail_" . $metric);
		// The following line uses a custom template that breaks the footer
		$view->setTemplate("SmartLoggent/templates/SearchPhraseGraphDetail.tpl");
		// so we use this
		$view->disableShowAllColumns();
		$view->disableFooter();
		$result = $this->renderView($view, $fetch);
// 		Piwik::profileend($profiler);
		return $result;
	}
	

	public function getSearchPhraseClassMetricsGraph($metric=-1, $class=-1)
	{
		static $mt;
// 		static $cl;
		if ($metric != -1) $mt = $metric;
// 		if ($class != -1) $cl = $class;

// 		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
// 		$period = Piwik_Common::getRequestVar('period', '', 'string');
// 		$date = Piwik_Common::getRequestVar('date', '', 'string');
// 		$segment = Piwik_Common::getRequestVar('date', false, 'string');

// 		$dataTable = Piwik_SmartLoggent_API::getSearchPhraseClassMetricData($idSite, $period, $date, $segment, array('class' => $cl, 'metric' => $metric));

// 		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
// 		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase' );
// 		$view->disableShowAllColumns();
// 		$view->setColumnsToDisplay(array('label', $mt));
// 		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
// 		$view->setSortedColumn($mt, 'desc');
// 		$view->disableFooter();
// 		$view->setAxisYUnit($mt);
// 		$view->setUniqueIdViewDataTable ("graph_gn_detail_" . $mt);
// 		$view->setTemplate("SmartLoggent/templates/SearchPhraseGraphDetail.tpl");
// 		$result = $this->renderView($view, true);
// 		return $result;
return $this->getSearchPhraseBy(__FUNCTION__, $mt, true);
	}

	public function getSingleClassVisitsEvolution($class = -1)
	{
		static $cl;
	
		if ($class != -1) $cl = $class;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		if ($class) {
			$dataTable = Piwik_SmartLoggent_API::getSingleClassVisitsEvolutionData($idSite, $period, $date, $segment, $cl);
			$view->setDatatable($dataTable);
		}
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleClassDistributionPie($class = -1)
	{
		static $cl;
	
		if ($class != -1) $cl = $class;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphPie');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		if ($class) {
			$dataTable = Piwik_SmartLoggent_API::getSingleClassDistributionData($idSite, $period, $date, $segment, $cl);
			$view->setDatatable($dataTable);
		}
	
		$view->disableShowAllColumns();
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_pie" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	
	/**
	 * To delete
	 * @param unknown_type $class
	 */
	public function getSingleClassPhraseEvolution($class = -1)
	{
		static $cl;

		if ($class != -1) $cl = $class;

		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');

		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');

		if ($class) {
			$dataTable = Piwik_SmartLoggent_API::getSingleClassPhraseEvolutionData($idSite, $period, $date, $segment, $cl);
			$view->setDatatable($dataTable);
		}

		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSingleClassDetailEvolution($source=-1, $params = -1)
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
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSingleClassNamedEntitiesDistributionPie()
	{
		$view = Piwik_ViewDataTable::factory('graphPie');

		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSingleClassNamedEntitiesDistributionData' );
		$view->setUniqueIdViewDataTable ("graph_gn_pie");
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSingleClassNamedEntitiesPopularityGraph()
	{
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');

		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSingleClassNamedEntitiesPopularityData' );
		$view->setUniqueIdViewDataTable ("graph_gn_popularity");
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSubClasses($fetch=false) {
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getClass');
		$limit=20;
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/classDatatable.tpl');
		return $result;
	}
	
	public function getSubClassesMetricGraph($class=-1, $metric=-1) {
	
		static $cl;
		static $mt;
		if ($class != -1) $cl = $class;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSubClassesData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
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
	
	public function getSubClassesEvolution($class = -1)
	{
		static $cl;
	
		if ($class != -1) $cl = $class;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		if ($class) {
			$dataTable = Piwik_SmartLoggent_API::getSubClassesVisitsEvolutionData($idSite, $period, $date, $segment, $cl);
			$view->setDatatable($dataTable);
		}
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSubClassDetailEvolution($source=-1, $params = -1)
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
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getClusters($fetch=false, $limit=20, $metric=Piwik_SmartLoggent_API::INDEX_NB_VISITS)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
		
		$dataTable = Piwik_SmartLoggent_API::getClusters($idSite, $period, $date, $segment);
		
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', true, 20, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/clusterDatatable.tpl');
		
		return $result;
	}
	
	public function getClusterMetricGraph($metric=-1) {
	
		static $mt;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getClusters($idSite, $period, $date, $segment);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
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
	
	public function getClusterEvolution()
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getClusterEvolution($idSite, $period, $date, $segment);
		$view->setDatatable($dataTable);

		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getClusterDetailEvolution($metric=-1)
	{
		static $mt;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getClusterDetailEvolutionData($idSite, $period, $date, $segment, $metric);
		$view->setDatatable($dataTable);
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleClusterSearchPhrases($fetch=false, $limit=20, $cluster=-1) {
	
		static $cl;
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterSearchPhraseData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/subClassDatatable.tpl');
			
		return $result;
	}
			
	public function getSingleClusterEvolution($cluster = -1)
	{
		static $cl;
	
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterEvolutionData($idSite, $period, $date, $segment, $cl);
		$view->setDatatable($dataTable);
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(2);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution");
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getSingleClusterDistributionPie($cluster = -1)
	{
		static $cl;
	
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphPie');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterDistributionData($idSite, $period, $date, $segment, $cl);
		$view->setDatatable($dataTable);
	
		$view->setUniqueIdViewDataTable ("graph_gn_distribution");
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleClusterMetricGraph($cluster=-1, $metric=-1) {
	
		static $cl;
		static $mt;
		if ($cluster != -1) $cl = $cluster;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterSearchPhraseData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
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
	
	public function getSingleClusterPhraseEvolution($cluster = -1)
	{
		static $cl;
	
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterSearchPhraseEvolutionData($idSite, $period, $date, $segment, $cl);
		$view->setDatatable($dataTable);

		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleClusterPhraseDetailEvolution($source=-1, $params = -1)
	{
		static $pars;
	
		if ($params != -1) $pars = $params;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterPhraseDetailEvolutionData($idSite, $period, $date, $segment, $pars);
		$view->setDatatable($dataTable);

		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleClusterNamedEntitiesDistributionPie($cluster)
	{
		static $cl;
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterNamedEntitiesDistributionPieData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory("graphPie");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$view->disableShowAllColumns();
		$view->setUniqueIdViewDataTable ("graph_gn_ne_distribution_");
		$result = $this->renderView($view, true);
	
		return $result;
	}

	public function getSingleClusterNamedEntitiesPopularity($cluster)
	{
		static $cl;
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterNamedEntitiesPopularityData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$view->disableShowAllColumns();
		$view->setUniqueIdViewDataTable ("graph_gn_ne_popularity_");
		$result = $this->renderView($view, true);
	
		return $result;
	}
	
	public function getSingleClusterClassification($cluster)
	{
		static $cl;
		if ($cluster != -1) $cl = $cluster;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleClusterClassificationData($idSite, $period, $date, $segment, $cl);
	
		$view = Piwik_ViewDataTable::factory("graphPie");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$view->disableShowAllColumns();
		$view->setUniqueIdViewDataTable ("graph_gn_ne_classification_");
		$result = $this->renderView($view, true);
	
		return $result;
	}
	
	public function getNamedEntitiesTypes($fetch=false, $limit=20)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getNamedEntitiesTypes');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/namedEntitiesTypesDatatable.tpl');
			
		return $result;
	}
	
	public function getNamedEntitiesMetricGraph($metric=-1) {
	
		static $mt;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getNamedEntitiesTypes($idSite, $period, $date, $segment);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
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
	
	public function getNamedEntitiesEvolution()
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getClusterEvolution($idSite, $period, $date, $segment);
		$view->setDatatable($dataTable);
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}

	public function getNEDetailEvolution($metric=-1)
	{
		static $mt;
		if ($metric != -1) $mt = $metric;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getNEDetailEvolutionData($idSite, $period, $date, $segment, $metric);
		$view->setDatatable($dataTable);
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleNamedEntityData($fetch=false, $limit=20, $namedEntityType=-1, $source=-1) {
	
		static $net;
		static $src;
		if ($namedEntityType != -1) $net = $namedEntityType;
		if ($source != -1) $src = $source;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('namedEntityType' => '$nef'));
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleNamedEntityTypeDatatable.tpl');
			
		return $result;
	}
	
	public function getSingleNEMetricsGraph($metric=-1, $namedEntity=-1) {
	
		static $mt;
		static $net;
		if ($metric != -1) $mt = $metric;
		if ($namedEntity != -1) $net = $namedEntity;
		
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleNamedEntityData($idSite, $period, $date, $segment, $net);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
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
	
	public function getNEEvolution($namedEntityType = -1)
	{
		static $net;
		if ($namedEntityType != -1) $net = $namedEntityType;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		if ($net) {
			$dataTable = Piwik_SmartLoggent_API::getNEEvolutionData($idSite, $period, $date, $segment, $net);
			$view->setDatatable($dataTable);
		}
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getNamedEntityTypeDetailEvolution($source=-1, $params = -1)
	{
		static $pars;
	
		if ($params != -1) $pars = $params;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		$dataTable = Piwik_SmartLoggent_API::getSingleNamedEntityEvolutionData($idSite, $period, $date, $segment, $pars);
		$view->setDatatable($dataTable);
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSearchPhrasesNamedEntityType($fetch=false, $limit=20, $namedEntityType=-1, $source=-1) {
	
		static $net;
		static $src;
		if ($namedEntityType != -1) $net = $namedEntityType;
		if ($source != -1) $src = $source;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('namedEntityType' => '$nef'));
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleNamedEntityTypeSearchPhrasesDatatable.tpl');
			
		return $result;
	}
	
	public function getSingleNESearchPhrasesMetricsGraph($metric=-1, $namedEntity=-1) {
	
		static $mt;
		static $net;
		if ($metric != -1) $mt = $metric;
		if ($namedEntity != -1) $net = $namedEntity;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSearchPhrasesNamedEntityType($idSite, $period, $date, $segment, $net);
	
		$view = Piwik_ViewDataTable::factory("graphVerticalBar");
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array('label', $mt));
		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
		$view->setSortedColumn($mt, 'desc');
		$view->disableFooter();
		$view->setAxisYUnit($mt);
		$view->setUniqueIdViewDataTable ("gn_graphSF" . $mt);
		$view->setTemplate("SmartLoggent/templates/GraphMetric.tpl");
		$result = $this->renderView($view, true);
		
		return $result;
	}
	
	public function getSingleNESearchPhrasesEvolutionGraph($searchPhrase = -1)
	{
		static $sf;
		if ($searchPhrase != -1) $sf = $searchPhrase;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName,  __FUNCTION__,  'SmartLoggent.getSearchPhrase');
	
		if ($sf) {
			$dataTable = Piwik_SmartLoggent_API::getNESearchPhraseEvolutionData($idSite, $period, $date, $segment, $sf);
			$view->setDatatable($dataTable);
		}
	
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay(array(Piwik_SmartLoggent_API::INDEX_NB_VISITS));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS,  Piwik_Translate($this->array_metrics_titles[Piwik_SmartLoggent_API::INDEX_NB_VISITS]));
		$view->disableFooter();
		$view->setLimit(5);
		$view->setUniqueIdViewDataTable ("graph_gn_sf_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, true);
		return $result;
	}
	
	public function getSingleNamedEntityClassesData($fetch=false, $limit=20, $namedEntityType=-1, $source=-1) {
	
		static $net;
		static $src;
		if ($namedEntityType != -1) $net = $namedEntityType;
		if ($source != -1) $src = $source;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('namedEntityType' => '$nef'));
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleNamedEntityTypeClassesDatatable.tpl');
			
		return $result;
	}
	
	public function getSingleNamedEntityClustersData($fetch=false, $limit=20, $namedEntityType=-1, $source=-1) {
	
		static $net;
		static $src;
		if ($namedEntityType != -1) $net = $namedEntityType;
		if ($source != -1) $src = $source;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('namedEntityType' => '$nef'));
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleNamedEntityTypeClustersDatatable.tpl');
			
		return $result;
	}
		
	public function configureUsualTable($view, $labelLocalization, $fetch=false, $limit=20, $sortedColumn=Piwik_SmartLoggent_API::INDEX_NB_QUERIES, $template)
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
		$view->setTemplate($template);
		$result = $this->renderView($view, $fetch);
		return $result;
	}
	
 	/**
	/*
	 * You can't pass parameters. This method is designed to
	 * invoke itself without any arguments.
	 *
	 * This is how Piwik_ViewDataTable::factory('graphEvolution') works
	 */
	public function getClassByQueriesEvolution( $columns = false, $fetch = false)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_Controller::'.__FUNCTION__); // 		Piwik::profileend($profiler);
// 		Piwik::log('Piwik_SmartLoggent_Controller::'.__FUNCTION__.' segment is '.$_GET['segment']);
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'SmartLoggent.get'.Piwik_SmartLoggent_API::DIM_CLASS);
		$rowpicker = false;
		$footer = false;
		Piwik_SmartLoggent_Controller_Evolution::configureEvolution
		(
			$view
		,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
		,	$rowpicker
		,	$footer
		,	$columns);
		// The following line breaks the rowpicker
		$view->setUniqueIdViewDataTable ("graph_gn_evolution" . floor(rand(0,1000)));
		$result = $this->renderView($view, $fetch);
// 		Piwik::profileend($profiler);
		return $result;
	}
	

	public static function getTop($dimension, $metric, $limit, $segment = false)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', $segment, 'string');
		$result = Piwik_SmartLoggent_API::getInstance()->getTop($idSite, $period, $date, $segment, $dimension, $metric, $limit);
		return $result;
	}
}
