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
		$dimension = Piwik_SmartLoggent_API::DIM_SEARCHPHRASE;
		$view = new Piwik_View('SmartLoggent/templates/searchPhraseOverview.tpl');

		$view->singleSearchPhraseUrl = $this->getUrlForAction('singleSearchPhrase'); 
		
		$viewData = Piwik_ViewDataTable::factory();
		$viewData->init($this->pluginName, __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$result = $this->configureUsualTable($viewData, 'LOC_SL_Column_Label_SearchPhrase', true, 20, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, "SmartLoggent/templates/searchPhraseDatatable.tpl");
		$view->searchPhrase = $result;
		
		$searchPhraseMetrics = array();
		$detailcharts = array();

		foreach ($this->array_metrics as $metric) {
			$result_searchPhraseMetrics = $this->getTopChart($dimension, $metric, true, true);
			
			//$result_detail_metric_chart = $this->getTopChart(Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $metric, true, true);
			$result_detail_metric_chart = $this->getCloud(Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, 5, -1, $metric);
			
			$result_detail_evolution_chart = $this->getTopChartEvolution($dimension, $metric, false, true, true);
			$searchPhraseMetrics[] = "<h2>".Piwik_Translate('LOC_SL_Chart_'.$dimension.'_by_'.$metric)."</h2>".$result_searchPhraseMetrics;
			$detailcharts[$metric]['chartmetric'] = $result_detail_metric_chart;
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);

		}

		$view->searchPhraseMetrics = $searchPhraseMetrics;
		$view->searchPhraseEvolution = $this->getTopChartEvolution($dimension, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, false, true, true);
		$view->searchPhrasePie = $this->getPie();
		$view->searchPhraseTagCloud = $this->getCloud(Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, 5, -1, Piwik_SmartLoggent_API::INDEX_NB_VISITS);
		$view->detailcharts = $detailcharts;

		echo $view->render();
	}

	public function singleSearchPhrase() {
		$view = new Piwik_View('SmartLoggent/templates/SingleSearchPhrase.tpl');
		$phrase = Piwik_Common::getRequestVar("phrase");
		$view->phrase = $phrase;
		//$view->searchPhraseEvolution = $this->getSearchPhraseEvolution('getSingleSearchEvolutionData', array('searchPhrase'=>$phrase));
		//$view->searchPhraseNamedEntities = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseNamedEntitiesData');
		//$view->searchPhraseClass = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseClassData');
		//$view->searchPhraseCluster = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseClusterData');
		
		$view->searchPhraseNaturalSearch = $this->getSingleSearchPhraseData(true, 10, $phrase, 'getSingleSearchPhraseNaturalSearchData');
		$view->searchPhrasePie = $this->getSingleSearchPhasePie($phrase);

		$view->searchPhraseEvolution = $this->getGraph('getSearchPhraseFiltered', 
																Piwik_SmartLoggent_API::INDEX_NB_VISITS, 
																1, 
																array('SLSearchPhrase'=>Piwik_SmartLoggent_API::encodeString($phrase)),
																'graphEvolution');
		
		$view->searchPhraseNamedEntities = $this->getTable('getDataFiltered',
															Piwik_SmartLoggent_API::INDEX_NB_VISITS, 
															5,
															array('SLSearchPhrase'=>Piwik_SmartLoggent_API::encodeString($phrase)),
															"searchPhraseDatatable.tpl",
															Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
		
		$view->searchPhraseClass = $this->getTable('getDataFiltered',
									Piwik_SmartLoggent_API::INDEX_NB_VISITS,
									5,
									array('SLSearchPhrase'=>Piwik_SmartLoggent_API::encodeString($phrase)),
									"searchPhraseDatatable.tpl",
									Piwik_SmartLoggent_API::DIM_CLASS);
		
		$view->searchPhraseCluster = $this->getTable('getDataFiltered',
									Piwik_SmartLoggent_API::INDEX_NB_VISITS,
									5,
									array('SLSearchPhrase'=>Piwik_SmartLoggent_API::encodeString($phrase)),
									"searchPhraseDatatable.tpl",
									Piwik_SmartLoggent_API::DIM_CLUSTER);
		
		echo $view->render();
	}

public function classes()
	{
		$dimension = Piwik_SmartLoggent_API::DIM_CLASS;
		$view = new Piwik_View('SmartLoggent/templates/classOverview.tpl');
		//$view->class = $this->getClass(true);
		$view->class = $this->getTable('get',
				Piwik_SmartLoggent_API::INDEX_NB_VISITS,
				5, -1, "classDatatable.tpl",
				Piwik_SmartLoggent_API::DIM_CLASS);

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
			
			$result_detail_evolution_chart = $this->getGraph('get',
					$metric,
					1,-1,
					Piwik_SmartLoggent_API::DIM_CLASS,
					'graphEvolution');
			
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
			$classMetrics[] = "<h2>".Piwik_Translate('LOC_SL_Chart_'.$dimension.'_by_'.$metric)."</h2>".$this->getTopChart($dimension, $metric, true, true); 
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		}

		$view->classMetrics = $classMetrics;
		$view->detailcharts = $detailcharts;

		//$view->evolution = $this->getClassEvolution(false, true);
		$view->evolution = $this->getGraph('get',
				Piwik_SmartLoggent_API::INDEX_NB_VISITS,
				1,-1, 
				Piwik_SmartLoggent_API::DIM_CLASS,
				'graphEvolution');
		
		echo $view->render();
	}

	public function singleClasses() {
		$view = new Piwik_View('SmartLoggent/templates/SingleClasses.tpl');
		$class = Piwik_Common::getRequestVar("class");
		$slid = Piwik_Common::getRequestVar("classid");

		$view->class = $class;

		$view->searchPhraseClass  = $this->getTable('getDataFiltered',
				Piwik_SmartLoggent_API::INDEX_NB_VISITS,
				10,
				array('SLClass'=>Piwik_SmartLoggent_API::encodeString($class)),
				"singleClassDatatable.tpl",
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
		
		$singleClassesMetrics = array();
		$detailcharts= array();
		$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
		foreach ($this->array_metrics as $metric) {
			$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set("SLClass", "==", $slid, $originalSegment);
			$singleClassesMetrics[] =
			"<h2>".Piwik_Translate('LOC_SL_Chart_'.Piwik_SmartLoggent_API::DIM_SEARCHPHRASE.'_by_'.$metric)
			."</h2>".$this->getTopChart(Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $metric, true, true);
			$_GET['segment'] = $originalSegment;
			
			//$result_detail_evolution_chart = $this->getSingleClassDetailEvolution("getSingleClassDetailEvolutionData", array('metric' => $metric));

			$result_detail_evolution_chart = $this->getGraph('getDataFiltered',
					$metric,
					10,
					array('SLClass'=>Piwik_SmartLoggent_API::encodeString($class)),
					Piwik_SmartLoggent_API::DIM_SEARCHPHRASE,
					'graphEvolution');
			
			$singleClassesMetrics[] = $result_singleClassesMetrics;
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);

		}

		$view->singleClassesMetrics = $singleClassesMetrics;
		
		//$view->singleClassesEvolution = $this->getSingleClassPhraseEvolution();
		$view->singleClassesEvolution = $this->getGraph('getDataFiltered',
						Piwik_SmartLoggent_API::INDEX_NB_VISITS,
						30,
						array('SLClass'=>Piwik_SmartLoggent_API::encodeString($class)),
						Piwik_SmartLoggent_API::DIM_SEARCHPHRASE,
						'graphEvolution'
				);
		
		$view->detailcharts = $detailcharts;
		$view->namedEntitiesDistribution = $this->getSingleClassNamedEntitiesDistributionPie();
		$view->namedEntitiesPopularity = $this->getSingleClassNamedEntitiesPopularityGraph();
		$view->singleClassVisitsEvolution = $this->getSingleClassVisitsEvolution();
		$view->singleClassDistribution = $this->getSingleClassDistributionPie();
		
		echo $view->render();
	}

	public function subClasses() {
		$view = new Piwik_View('SmartLoggent/templates/SubClasses.tpl');
		$class = Piwik_Common::getRequestVar("class");
		$view->subClasses = $this->getSubClasses('getSearchPhraseClassData', $class);
		$view->class = $class;
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlSingleClass = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleClasses'));
		$view->singleClassUrl = $urlIndex . "#" . substr($urlSingleClass, 1);
		
		$subClassesMetrics = array();
		$detailcharts = array();
		
		foreach ($this->array_metrics as $metric) {
			$result_subClassMetrics = $this->getSubClassesMetricGraph($class, $metric);
			$result_detail_evolution_chart = $this->getSubClassDetailEvolution("getSubClassDetailEvolutionData", array('metric' => $metric, 'class' => $class));
		
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
		
			$subClassesMetrics[] = $result_subClassMetrics;
		}
		
		$view->subClassesMetrics = $subClassesMetrics;
		$view->detailcharts = $detailcharts;
		$view->subClassesEvolution = $this->getSubClassesEvolution($class);
		
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

	public function namedEntityType()
	{
		$view = new Piwik_View('SmartLoggent/templates/namedEntitiesTypesOverview.tpl');
	
		$NEMetrics = array();
		$detailcharts = array();
		
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlSl = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => 'singleNamedEntityType'));
		$view->singleNEUrl = $urlIndex . "#" . substr($urlSl, 1);
		
		$view->namedEntitiesTypes = $this->getNamedEntitiesTypes(true);
		
		foreach ($this->array_metrics as $metric) {
			$result_NEMetrics = $this->getNamedEntitiesMetricGraph($metric);
			$result_detail_evolution_chart = $this->getNEDetailEvolution($metric);
	
			$detailcharts[$metric]['chartevolution'] = $result_detail_evolution_chart;
			$detailcharts[$metric]['metric'] = $metric;
			$detailcharts[$metric]['title'] = Piwik_Translate($this->array_metrics_titles[$metric]);
	
			$NEMetrics[] = $result_NEMetrics;
		}
		
		$view->NEMetrics = $NEMetrics;
		$view->detailcharts = $detailcharts;
		
		$view->evolution = $this->getNamedEntitiesEvolution();
		
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
	
	//DEPRECATED
	public function getSearchPhrase($fetch=false, $limit=20, $metric=Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/searchPhraseDatatable.tpl');
		
		return $result;
	}

	//DEPRECATED
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

	//DEPRECATED
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
		//$view->setAxisYUnit($mt);
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

	//DEPRECATED
	public function getSearchPhraseEvolution($source=-1, $params = -1)
	{
		static $src;
		static $pars;

		if ($source != -1) $src = $source;
		if ($params != -1) $pars = $params;

		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');

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

	//DEPRECATED
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

	//DEPRECATED
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

	//DEPRECATED
	public function getSearchPhraseClassData($source=-1, $class=-1) {

		static $sf;
		static $src;
		if ($class != -1) $cl = $class;
		if ($source != -1) $src = $source;

		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');

		$dataTable = Piwik_SmartLoggent_API::$src($idSite, $period, $date, $segment, array('class' => $cl));

		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', true, 20, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/singleClassDatatable.tpl');
			
		return $result;
	}

	public function getSearchPhraseClassMetricsGraph($metric=-1, $class=-1)
	{
		static $mt;
		static $cl;
		if ($metric != -1) $mt = $metric;
		if ($class != -1) $cl = $class;

		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');

		$dataTable = Piwik_SmartLoggent_API::getSearchPhraseClassMetricData($idSite, $period, $date, $segment, array('class' => $cl, 'metric' => $metric));

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

	//DEPRECATED
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

	public function getSubClasses($fetch=false, $limit=20, $class=-1) {
	
		static $cl;
		if ($class != -1) $cl = $class;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
	
		$dataTable = Piwik_SmartLoggent_API::getSubClassesData($idSite, $period, $date, $segment, array('searchPhrase' => '$sf'));
	
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'SmartLoggent/templates/subClassDatatable.tpl');
			
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
	
 	private function getUrlForAction($action) {
		$urlIndex = Piwik_Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome',
				'action' => 'index',
		));
		$urlAction = Piwik_Url::getCurrentQueryStringWithParametersModified(array('action' => $action));
		return ($urlIndex . "#" . substr($urlAction, 1));
	}
	
	private function getUUID() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	
	public function getTable($function=-1, $metric=-1, $limit=5, $arFilters = -1, $template = -1, $dimension = -1)
	{
		static $mt; if ($metric != -1) $mt = $metric;
		static $fn; if ($function != -1) $fn = $function;
		static $lm; if ($limit != 5) $lm = $limit;
		static $arFlt; if ($arFilters != -1) $arFlt = $arFilters;
		static $tpl; if ($template != -1) $tpl = $template;
		static $dim; if ($dimension != -1) $dim = $dimension;
		
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
	
		if ($arFlt)
			$dataTable = Piwik_SmartLoggent_API::$fn($idSite, $period, $date, $segment, $arFlt, $dim);
		else
			$dataTable = Piwik_SmartLoggent_API::$fn($idSite, $period, $date, $segment, $dim);

		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->setDatatable($dataTable);
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', true, $limit, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, "SmartLoggent/templates/$tpl");
			
		return $result;
		
	}
	
	public function getPie() {
		$viewPie = Piwik_ViewDataTable::factory('graphPie');
		$viewPie->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhraseDistributionData' );
		$viewPie->setUniqueIdViewDataTable ($this->getUUID());
		$pieGraph = $this->renderView($viewPie, true);
		return $pieGraph;
	}
	
	public function getCloud($dimension = -1, $limit = 5, $arFilters = -1, $metric = -1) {
	
		static $dim; if ($dimension != -1) $dim = $dimension;
		static $lm; if ($limit != 5) $lm = $limit;
		static $mt; if ($metric != -1) $mt = $metric;
		static $arFlt; if ($arFilters != -1) $arFlt = $arFilters;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
	
		if ($arFlt)
			$dataTable = Piwik_SmartLoggent_API::getDataFiltered($idSite, $period, $date, $segment, $arFlt, $dim);
		else
			$dataTable = Piwik_SmartLoggent_API::get($idSite, $period, $date, $segment, $dim);
	
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase' );
		$view->setUniqueIdViewDataTable ($this->getUUID());
		$view->setLimit($lm);
		$view->setColumnsToDisplay(array('label', $mt));
		$result = $this->renderView($view, true);
				
		return $result;		
	}
	
	public function getGraph($function=-1, $metric=-1, $limit=5, $arFilters = -1, $dimension = -1, $type = -1)
	{
		static $mt; if ($metric != -1) $mt = $metric;
		static $fn; if ($function != -1) $fn = $function;
		static $lm; if ($limit != 5) $lm = $limit;
		static $arFlt; if ($arFilters != -1) $arFlt = $arFilters;
		static $dim; if ($dimension != -1) $dim = $dimension;
		static $tp; if ($type != -1) $tp = $type;
	
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
	
		if ($arFlt)
			$dataTable = Piwik_SmartLoggent_API::$fn($idSite, $period, $date, $segment, $arFlt, $dim);
		else
			$dataTable = Piwik_SmartLoggent_API::$fn($idSite, $period, $date, $segment, $dim);
	
		$view = Piwik_ViewDataTable::factory($tp);
		$view->init( $this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$view->disableShowAllColumns();
		$view->setDatatable($dataTable);
		$view->setColumnsToDisplay(array('label', $mt));
		$view->setColumnTranslation($mt,  Piwik_Translate($this->array_metrics_titles[$mt]));
		$view->disableFooter();
		$view->setLimit($lm);
		$view->setUniqueIdViewDataTable ($this->getUUID());
		$result = $this->renderView($view, true);
		
		return $result;
	}
	
	// ----------------------
	// 	TOP CHARTS
	
	public static function getTop($dimension, $metric, $limit)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
		$result = Piwik_SmartLoggent_API::getInstance()->getTop($idSite, $period, $date, $segment, $dimension, $metric, $limit);
		return $result;
	}
	
	public function getTopChart($dimension, $metric, $fetch = false, $recompute_top = false)
	{
		if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getSearchPhraseByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getSearchPhraseByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getSearchPhraseByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getSearchPhraseByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getSearchPhraseByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getSearchPhraseByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getSearchPhraseByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchPhraseByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchPhraseByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getSearchPhraseByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getSearchPhraseByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getSearchWordByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getSearchWordByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getSearchWordByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getSearchWordByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getSearchWordByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getSearchWordByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getSearchWordByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchWordByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchWordByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getSearchWordByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getSearchWordByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getLanguageByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getLanguageByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getLanguageByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getLanguageByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getLanguageByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getLanguageByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getLanguageByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getLanguageByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getLanguageByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getLanguageByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getLanguageByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_CLASS)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getClassByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getClassByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getClassByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getClassByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getClassByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getClassByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getClassByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClassByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClassByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getClassByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getClassByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getClusterByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getClusterByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getClusterByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getClusterByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getClusterByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getClusterByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getClusterByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClusterByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClusterByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getClusterByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getClusterByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getNamedEntityTypeByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getNamedEntityTypeByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getNamedEntityTypeByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getNamedEntityTypeByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getNamedEntityTypeByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getNamedEntityTypeByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getNamedEntityTypeByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityTypeByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityTypeByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getNamedEntityTypeByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getNamedEntityTypeByQCIndex($fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getNamedEntityByVisitors($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getNamedEntityByVisits($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getNamedEntityByQueries($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getNamedEntityByQueriesPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getNamedEntityByClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getNamedEntityByClicksPercent($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getNamedEntityByAverageClicks($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityByAverageResults($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getNamedEntityByClickProbability($fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getNamedEntityByQCIndex($fetch, $recompute_top);
		}
	}
	
	public function getDimensionByMetric($dimension, $segment, $metric_to_rank, $metric_to_display, $function, $fetch = false, $recompute_top = false)
	{
		$to_reset = false;
		if ($recompute_top)
		{
			$to_reset = true;
			$tops = self::getTop($dimension, $metric_to_rank, 4);
			$topsids = $tops['ids'];
			if (empty($tops))
				$tops[] = '0';
			$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
			$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set($segment, '==', implode('_', $topsids), $originalSegment);
		}
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init($this->pluginName, $function, "SmartLoggent.get$dimension");
		$view->setColumnsToDisplay(array('label',	$metric_to_display));
		$view->setColumnTranslation('label', Piwik_Translate("LOC_SL_Column_Label_$dimension"));
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
		$view->setSortedColumn($metric_to_rank, 'desc');
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformationAndPaginationControls();
		$view->disableSort();
		$view->disableShowAllColumns();
		$view->disallowPercentageInGraphTooltip();
		$view->showAllTicks();
		$view->disableShowPieChart();
		// 		$view->disableFooter();
		$view->setUniqueIdViewDataTable ($this->getUUID());
		// 		$view->setTemplate("SmartLoggent/templates/GraphMetric.tpl"); // this breaks multiple chart types
		$result = $this->renderView($view, $fetch);
		if ($to_reset)
			$_GET['segment'] = $originalSegment;
		return $result;
	}
	
	// SEARCH PHRASES
	
	public function getSearchPhraseByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// SEARCH WORDS
	
	public function getSearchWordByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// LANGUAGES
	
	public function getLanguageByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// CLASSES
	
	public function getClassByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClassByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// CLUSTERS
	
	public function getClusterByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// NAMED ENTITY TYPES
	
	public function getNamedEntityTypeByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// NAMED ENTITIES
	
	public function getNamedEntityByVisitors($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByVisits($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQueries($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQueriesPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClicksPercent($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByAverageClicks($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByAverageResults($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClickProbability($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQCIndex($fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetric
		(
				Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	__FUNCTION__
				,	$fetch
				, $recompute_top
		);
	}
	
	// TOP CHART EVOLUTION
	
	public function getTopChartEvolution($dimension, $metric, $columns, $fetch, $recompute_top)
	{
		if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getSearchPhraseByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getSearchPhraseByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getSearchPhraseByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getSearchPhraseByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getSearchPhraseByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getSearchPhraseByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getSearchPhraseByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchPhraseByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getSearchPhraseByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getSearchPhraseByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getSearchWordByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getSearchWordByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getSearchWordByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getSearchWordByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getSearchWordByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getSearchWordByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getSearchWordByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getSearchWordByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getSearchWordByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getSearchWordByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getLanguageByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getLanguageByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getLanguageByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getLanguageByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getLanguageByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getLanguageByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getLanguageByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getLanguageByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getLanguageByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getLanguageByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_CLASS)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getClassByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getClassByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getClassByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getClassByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getClassByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getClassByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getClassByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClassByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getClassByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getClassByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getClusterByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getClusterByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getClusterByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getClusterByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getClusterByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getClusterByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getClusterByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getClusterByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getClusterByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getClusterByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getNamedEntityTypeByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getNamedEntityTypeByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getNamedEntityTypeByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getNamedEntityTypeByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getNamedEntityTypeByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getNamedEntityTypeByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getNamedEntityTypeByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityTypeByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getNamedEntityTypeByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getNamedEntityTypeByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
		else if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
				return $this->getNamedEntityByVisitorsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_VISITS)
				return $this->getNamedEntityByVisitsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
				return $this->getNamedEntityByQueriesEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_QUERIES)
				return $this->getNamedEntityByQueriesPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
				return $this->getNamedEntityByClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_FR_CLICKS)
				return $this->getNamedEntityByClicksPercentEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
				return $this->getNamedEntityByAverageClicksEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
				return $this->getNamedEntityByAverageResultsEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
				return $this->getNamedEntityByClickProbabilityEvolution($columns, $fetch, $recompute_top);
			if ($metric == Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
				return $this->getNamedEntityByQCIndexEvolution($columns, $fetch, $recompute_top);
		}
	}
	
	private function getDimensionByMetricEvolution($function, $dimension, $segment, $metric_to_rank, $metric_to_display, $rowpicker = false, $footer = true, $columns = false, $fetch = false, $recompute_top = false)
	{
		$originalSegment = Piwik_Common::getRequestVar('segment', false, 'string');
		if ($recompute_top)
		{
			$tops = self::getTop($dimension, $metric_to_rank, 4);
			$_GET['autorows'] = implode(',', $tops['labels']);
			$topsids = $tops['ids'];
			if (empty($topsids))
				$topsids[] = '0';
			$_GET['segment'] = Piwik_SmartLoggent_SegmentEditor::set($segment, '==', implode('_', $topsids), $originalSegment);
		}
	
		$view = Piwik_SmartLoggent_Core_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName, $function, 'SmartLoggent.get'.$dimension );
		// configure the evolution graph
		/*
		 * Don't disable the footer unless you also disable the rowpicker.
		* See the bug http://dev.piwik.org/trac/ticket/3313
		*/
		if (!$rowpicker)
		{
			/*
			 * .. but you may decide to disable the rowpicker and still keep the
			* footer
			*/
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
		$visibleRows = Piwik_Common::getRequestVar('rows', '');
		if ($visibleRows != '')
		{
			/* this happens when the row picker has been used */
			$visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
		}
		else
		{
			$visibleRows = Piwik_Common::getRequestVar('autorows', '');
			if ($rowpicker && $visibleRows != '')
				$view->setParametersToModify(array('rows' => $visibleRows));
		}
		if ($rowpicker)
		{
			if ($visibleRows === '')
			{
				/*
				 * This works only with SmartLoggent version of evolution graph.
				* The standard chart does not work with an empty selection of
				* rows in the rowpicker.
				*/
				$view->addRowPicker(array());
			}
			else
				$view->addRowPicker($visibleRows);
		}
		/*
		 *  METRICS LABELS (COLUMNS)
		*/
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
		
		/*
		 *  METRICS SELECTION (COLUMNS)
		*
		*  The metrics to display can be set programmatically with the $columns
		*  argumnent.
		*/
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns'
					, $metric_to_display);
			$columns = Piwik::getArrayFromApiParameter($columns);
		}
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
		/*
		 * It is not possible to use the row picker when there are no selectable
		* columns.
		*/
		if ($rowpicker)
		{
			$selectableColumns = array($metric_to_display);
			$view->setSelectableColumns($selectableColumns);
		}
		// evolution graph configured
		$result = $this->renderView($view, $fetch);
		if ($recompute_top)
		{
			$_GET['autorows'] = '';
			$_GET['segment'] = $originalSegment;
		}
		return $result;
	}
	
	// SEARCH PHRASE
	
	public function getSearchPhraseByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchPhraseByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// SEARCH WORD
	
	public function getSearchWordByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getSearchWordByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				,	Piwik_SmartLoggent_API::SEG_SEARCHWORD
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// LANGUAGE
	
	public function getLanguageByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getLanguageByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
				,	Piwik_SmartLoggent_API::SEG_LANGUAGE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// CLASS
	
	public function getClassByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClassByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLASS
				,	Piwik_SmartLoggent_API::SEG_CLASS
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// CLUSTER
	
	public function getClusterByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getClusterByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				,	Piwik_SmartLoggent_API::SEG_CLUSTER
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// NAMED ENTITY TYPE
	
	public function getNamedEntityTypeByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityTypeByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	// NAMED ENTITY
	
	public function getNamedEntityByVisitorsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByVisitsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQueriesEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQueriesPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClicksPercentEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByAverageClicksEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByAverageResultsEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByClickProbabilityEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
	
	public function getNamedEntityByQCIndexEvolution($columns = false, $fetch = false, $recompute_top = false)
	{
		return $this->getDimensionByMetricEvolution
		(
				__FUNCTION__
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				,	Piwik_SmartLoggent_API::SEG_NAMEDENTITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				, true
				, true
				, $columns
				, $fetch
				, $recompute_top
		);
	}
}