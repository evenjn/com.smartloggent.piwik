<?php

class Piwik_SmartLoggent_API
{
	const DIM_LANGUAGE = 'Language';
	const DIM_CLASS = 'Class';
	const DIM_SEARCHPHRASE= 'SearchPhrase';
	const DIM_CLUSTER = 'Cluster';
	const DIM_CLUSTERANALYSIS = 'ClusterAnalysis';
	const DIM_SEARCHWORD = 'SearchWord';
	const DIM_NAMEDENTITY = 'NamedEntity';
	const DIM_NAMEDENTITYTYPE = 'NamedEntityType';
	
	/**
	 * From Piwik_Archive. The number of unique visitors in this subset.
	 */
	const INDEX_NB_UNIQ_VISITORS = 1;
	
	/**
	 * From Piwik_Archive. The number of sessions in this subset.
	 */
	const INDEX_NB_VISITS = 2;
	
	/**
	 * The number of query events in this subset.
	 */
	const INDEX_NB_QUERIES = 57;
	
	/**
	 * The ratio between the query events in this subset and the total number of
	 * click events in the reference set.
	 */
	const INDEX_FR_QUERIES = 58;
	
	/**
	 * The average number of results returned by the search engine for queries in
	 * this subset.
	 */
	const INDEX_AVG_RESULTS = 59;
	
	/**
	 * The number of click events originated by query events in this subset.
	 */
	const INDEX_NB_CLICKS = 60;
	
	/**
	 * The ratio between the click events originated by query events in this
	 * subset and the total number of click events in the reference set.
	 */
	const INDEX_FR_CLICKS = 61;
	
	/**
	 * The average number of click events originated by query events in this
	 * subset.
	 */
	const INDEX_AVG_CLICKS = 62;
	
	/**
	 * The fraction of query events in this subset that originated at least one
	 * click.
	 */
	const INDEX_CLICK_PROBABILITY = 63;
	
	/**
	 * The ratio between the click events originated by query events in this
	 * subset and the total number of query events in the reference set.
	 */
	const INDEX_WEIGHTED_CLICK_PROBABILITY = 64;
	
	/**
	 * To be defined. Helps to drill down.
	 */
	const ANNOTATION_TYPE = 80;
	
	/**
	 * To be defined. Helps to drill down.
	 */
	const ANNOTATION_VALUE = 81;
	
	/**
	 * The label of this subset.
	 */
	const LABEL = 'label';
	
	static private $instance = null;
	
	/** Get singleton instance
	 * @return Piwik_SmartLoggent_API */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}		// TODO @CELI: return a dataTable for SingleSearchPhrase evolution graph ([13])
		
		return self::$instance;
	}
	
	public function getLanguage($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_LANGUAGE);
	}
	
	
	public function getSearchPhrase($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_SEARCHPHRASE);
	}
	
	public function getSearchPhraseDistributionData($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for pie graph ([8])
		
		$queryPieDistrib = array(
				'thents' => 0.382,
				'hundreds' => 0.949,
				'thousands' => 1.00,
		);

		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
		
	}
	
	public function getSearchPhraseTagCloudData($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for tag cloud graph ([9])
	
		$queryPieDistrib = array(
				'word1' => 0.382,
				'word2' => 0.949,
				'word3' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getSingleSearchEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase evolution graph ([13])
	
		$searchPhrase = $params['searchPhrase'];
		
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleSearchPhraseNamedEntitiesData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase named entities table ([14])
	
		$searchPhrase = $params['searchPhrase'];
	
		$namedEntities[1] = new Piwik_DataTable_Row();
		$namedEntities[1]->addColumn('label', 'Paris');
		$namedEntities[1]->addMetadata('type', "");
		$namedEntities[1]->addMetadata('annotation', "");
		$namedEntities[2] = new Piwik_DataTable_Row();
		$namedEntities[2]->addColumn('label', 'London');
		$namedEntities[2]->addMetadata('type', "");
		$namedEntities[2]->addMetadata('annotation', "");
		$namedEntities[3] = new Piwik_DataTable_Row();
		$namedEntities[3]->addColumn('label', 'Rome');
		$namedEntities[3]->addMetadata('type', "");
		$namedEntities[3]->addMetadata('annotation', "");
		
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($namedEntities);
		return $dataTable;
		
	}
	
	public function getSingleSearchPhraseClassData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase class table ([15])
	
		$searchPhrase = $params['searchPhrase'];
	
		$class[1] = new Piwik_DataTable_Row();
		$class[1]->addColumn('label', 'Class 1');
		$class[1]->addMetadata('type', "");
		$class[1]->addMetadata('annotation', "");
		$class[2] = new Piwik_DataTable_Row();
		$class[2]->addColumn('label', 'Class 2');
		$class[2]->addMetadata('type', "");
		$class[2]->addMetadata('annotation', "");
		$class[3] = new Piwik_DataTable_Row();
		$class[3]->addColumn('label', 'Class 3');
		$class[3]->addMetadata('type', "");
		$class[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($class);
		return $dataTable;
	
	}
	
	public function getSingleSearchPhraseClusterData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase cluster table ([16])
	
		$searchPhrase = $params['searchPhrase'];
	
		$cluster[1] = new Piwik_DataTable_Row();
		$cluster[1]->addColumn('label', 'Cluster A');
		$cluster[1]->addMetadata('type', "");
		$cluster[1]->addMetadata('annotation', "");
		$cluster[2] = new Piwik_DataTable_Row();
		$cluster[2]->addColumn('label', 'Cluster B');
		$cluster[2]->addMetadata('type', "");
		$cluster[2]->addMetadata('annotation', "");
		$cluster[3] = new Piwik_DataTable_Row();
		$cluster[3]->addColumn('label', 'Cluster C');
		$cluster[3]->addMetadata('type', "");
		$cluster[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($cluster);
		return $dataTable;
	
	}
	
	public function getSingleSearchPhraseNaturalSearchData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase Natural Search table ([17])
	
		$searchPhrase = $params['searchPhrase'];
	
		$naturalSearch[1] = new Piwik_DataTable_Row();
		$naturalSearch[1]->addColumn('label', 'Search X');
		$naturalSearch[1]->addMetadata('type', "");
		$naturalSearch[1]->addMetadata('annotation', "");
		$naturalSearch[2] = new Piwik_DataTable_Row();
		$naturalSearch[2]->addColumn('label', 'Search Y');
		$naturalSearch[2]->addMetadata('type', "");
		$naturalSearch[2]->addMetadata('annotation', "");
		$naturalSearch[3] = new Piwik_DataTable_Row();
		$naturalSearch[3]->addColumn('label', 'Search Z');
		$naturalSearch[3]->addMetadata('type', "");
		$naturalSearch[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($naturalSearch);
		return $dataTable;
	
	}
	
	public function getSearchPhraseGeoData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for SingleSearchPhrase Geographical distribution Pie graph ([18])
	
		$searchPhrase = $params['searchPhrase'];	
		$queryPieDistrib = array(
				'Italy' => 0.382,
				'France' => 0.949,
				'Spain' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getSearchPhraseClassData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for Search Phrases of the class table ([26])
	
		$class = $params['class'];
	
		$phrase[1] = new Piwik_DataTable_Row();
		$phrase[1]->addColumn('label', 'Phrase 1');
		$phrase[1]->addMetadata('type', "");
		$phrase[1]->addMetadata('annotation', "");
		$phrase[2] = new Piwik_DataTable_Row();
		$phrase[2]->addColumn('label', 'Phrase 2');
		$phrase[2]->addMetadata('type', "");
		$phrase[2]->addMetadata('annotation', "");
		$phrase[3] = new Piwik_DataTable_Row();
		$phrase[3]->addColumn('label', 'Phrase 3');
		$phrase[3]->addMetadata('type', "");
		$phrase[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($phrase);
		return $dataTable;
	
	}
	
	public function getSearchPhraseClassMetricData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for Search Phrase of the class metric graphs ([27])
	
		$class = $params['class'];
		$metric = $params['metric'];
		
		$metricData = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($metricData);
		return $dataTable;
	
	}
	
	public function getSingleClassNamedEntitiesDistributionData($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for named entities distribution Pie graph ([30])
	
		$queryPieDistrib = array(
				'Cities' => 0.382,
				'People' => 0.949,
				'Countries' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getSingleClassNamedEntitiesPopularityData($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for named entitites popularity graph ([31])
		
		$popularity = array(
				'Paris' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($popularity);
		return $dataTable;
	
	}
	
	public function getClassDetailEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for evolution detail for class graph ([22])
	
		$metric = $params['metric'];
		
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClassVisitsEvolutionData($idSite, $period, $date, $segment = false, $class)
	{
		// TODO @CELI: return a dataTable for class visits evolution graph ([24])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClassDistributionData($idSite, $period, $date, $segment = false, $class)
	{
		// TODO @CELI: return a dataTable for Single Class Geographical distribution Pie graph ([25])
	
		$queryPieDistrib = array(
				'Italy' => 0.382,
				'France' => 0.949,
				'Spain' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getSingleClassPhraseEvolutionData($idSite, $period, $date, $segment = false, $class)
	{
		// TODO @CELI: return a dataTable for Search Phrases of the class evolution graph ([28])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClassDetailEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for evolution detail for single class graph ([29])
	
		$metric = $params['metric'];
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSubClassesVisitsEvolutionData($idSite, $period, $date, $segment = false, $class)
	{
		// TODO @CELI: return a dataTable for subclass evolution graph ([35])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSearchWord($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_SEARCHWORD);
	}
	
	public function getClass($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for class ([19])

		$class[1] = new Piwik_DataTable_Row();
		$class[1]->addColumn('label', 'Class 1');
		$class[1]->addMetadata('type', "");
		$class[1]->addMetadata('annotation', "");
		$class[2] = new Piwik_DataTable_Row();
		$class[2]->addColumn('label', 'Class 2');
		$class[2]->addMetadata('type', "");
		$class[2]->addMetadata('annotation', "");
		$class[3] = new Piwik_DataTable_Row();
		$class[3]->addColumn('label', 'Class 3');
		$class[3]->addMetadata('type', "");
		$class[3]->addMetadata('annotation', "");

		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($class);
		return $dataTable;
	}
	
	public function getSubClassesData($idSite, $period, $date, $segment = false, $class)
	{
		// TODO @CELI: return a dataTable for subclasses table ([33])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Subclass 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Subclass 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Subclass 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getSubClassDetailEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for evolution detail for subclass graph ([36])
	
		$class = $params['class'];
		$metric = $params['metric'];
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getClusterAnalysis() {
		// TODO @CELI: return array of available cluster analysis ([38])
		
		$cans = array(array("title"=> "Analisys 1", "value" => 1), 
			           array("title"=> "Analisys 2", "value" => 2),
				       array("title"=> "Analisys 3", "value" => 3),
				       array("title"=> "Analisys 4", "value" => 4),
		);
		
		return $cans;
	}
	
	public function getClusters($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for clusters ([40])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Cluster 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Cluster 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Cluster 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getClusterEvolution($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for evolution cluster graph ([42])

		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getClusterDetailEvolutionData($idSite, $period, $date, $segment = false, $metric)
	{
		// TODO @CELI: return a dataTable for evolution detail for cluster graph ([43])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClusterEvolutionData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for evolution detail for single cluster graph ([46])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClusterDistributionData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for SingleCluster Geographical distribution Pie graph ([47])
	
		$queryPieDistrib = array(
				'Italy' => 0.382,
				'France' => 0.949,
				'Spain' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}

	public function getSingleClusterSearchPhraseData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable with search phrase for single cluster ([48])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Search Phrase 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Search Phrase 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Search Phrase 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getSingleClusterSearchPhraseEvolutionData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for single cluster evolution graph ([50])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleClusterPhraseDetailEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for single cluster detail evolution graph ([51])
	
		$cluster = $params['cluster'];
		$metric = $params['metric'];
		
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
		
	public function getSingleClusterNamedEntitiesDistributionPieData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for cluster NE distribution Pie graph ([52])
	
		$queryPieDistrib = array(
				'NE Type 1' => 0.382,
				'NE Type 2' => 0.949,
				'NE Type 3' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getSingleClusterNamedEntitiesPopularityData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for named entitites popularity graph ([53])
		
		$popularity = array(
				'Paris' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($popularity);
		return $dataTable;
	
	}
	
	public function getSingleClusterClassificationData($idSite, $period, $date, $segment = false, $cluster)
	{
		// TODO @CELI: return a dataTable for cluster NE classification Pie graph ([54])
	
		$queryPieDistrib = array(
				'Type 1' => 0.382,
				'Type 2' => 0.949,
				'Type 3' => 1.00,
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($queryPieDistrib);
		return $dataTable;
	
	}
	
	public function getNamedEntitiesTypes($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for named entities types ([55])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Cities');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'History');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Region');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getNamedEntitiesEvolution($idSite, $period, $date, $segment = false)
	{
		// TODO @CELI: return a dataTable for evolution NE graph ([57])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getNEDetailEvolutionData($idSite, $period, $date, $segment = false, $metric)
	{
		// TODO @CELI: return a dataTable for evolution detail for named entities graph ([58])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleNamedEntityData($idSite, $period, $date, $segment = false, $netype)
	{
		// TODO @CELI: return a dataTable with named entities for single named entity type ([60])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Named entity 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Named entity 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Named entity 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getNEEvolutionData($idSite, $period, $date, $segment = false, $namedEntityType)
	{
		// TODO @CELI: return a dataTable for evolution for for named entities of $namedEntityType ([62])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleNamedEntityEvolutionData($idSite, $period, $date, $segment = false, $params)
	{
		// TODO @CELI: return a dataTable for single named entity type evolution detail ([63])
	
		$namedEntityType = $params['namedEntityType'];
		$metric = $params['metric'];
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSearchPhrasesNamedEntityType($idSite, $period, $date, $segment = false, $namedEntityType)
	{
		// TODO @CELI: return a dataTable for search phrases for named entities types ([64])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Search phrases 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Search phrases 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Search phrases 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getNESearchPhraseEvolutionData($idSite, $period, $date, $segment = false, $searchPhrase)
	{
		// TODO @CELI: return a dataTable for evolution for for searchPhrase for a named entity type ([64])
	
		$evolution = array(
				'1' => array(2=>3),
				'2' => array(2=>1),
				'3' => array(2=>4),
		);
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($evolution);
		return $dataTable;
	
	}
	
	public function getSingleNamedEntityClassesData($idSite, $period, $date, $segment = false, $netype)
	{
		// TODO @CELI: return a dataTable with classes for single named entity type ([68])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Class 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Class 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Class 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	
	public function getSingleNamedEntityClustersData($idSite, $period, $date, $segment = false, $netype)
	{
		// TODO @CELI: return a dataTable with clusters for single named entity type ([69])
	
		$subclass[1] = new Piwik_DataTable_Row();
		$subclass[1]->addColumn('label', 'Cluster 1');
		$subclass[1]->addMetadata('type', "");
		$subclass[1]->addMetadata('annotation', "");
		$subclass[2] = new Piwik_DataTable_Row();
		$subclass[2]->addColumn('label', 'Cluster 2');
		$subclass[2]->addMetadata('type', "");
		$subclass[2]->addMetadata('annotation', "");
		$subclass[3] = new Piwik_DataTable_Row();
		$subclass[3]->addColumn('label', 'Cluster 3');
		$subclass[3]->addMetadata('type', "");
		$subclass[3]->addMetadata('annotation', "");
	
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArray($subclass);
		return $dataTable;
	
	}
	public function get($idSite, $period, $date, $segment = false, $dimension = self::DIM_SEARCHPHRASE)
	{
		$tops_string = Piwik_Common::getRequestVar('smartloggent_filter_evolution', '');
		if ($tops_string === '')
		{
			$archive = Piwik_SmartLoggent_Core_Archive::build($idSite, $period, $date, $segment);
			$dataTable = $archive->getDataTable('SmartLoggent_'.$dimension);
			return $dataTable;
		}
		$shards =  self::splitSegment('SL'.$dimension.'=='.$tops_string, 'SL'.$dimension);
	
		$result;
		$firstDatatable = true;
		foreach ($shards as $shard)
		{
			// 			Piwik::log("get$partition building from shard " . $shard);
			$archive = Piwik_SmartLoggent_Core_Archive::build($idSite, $period, $date, $shard);
			$dataTable = $archive->getDataTable('SmartLoggent_'.$dimension);
			// 			Piwik::log("get$partition got the datatable from archive, type " .get_class($dataTable) );
			if($firstDatatable)
			{
				// 				Piwik::log("get$partition it's the first");
				$firstDatatable = false;
				$result = $dataTable;
			}
			else
			{
				// 				Piwik::log("get$partition it's not the first");
				if ($result instanceof Piwik_DataTable_Array)
				{
					if (!($dataTable instanceof Piwik_DataTable_Array))
					{
						Piwik::log("get$dimension error on Piwik_DataTable_Array");
					}
					// 					Piwik::log("get$partition building from array");
					$asarray = $result->getArray();
					foreach ($asarray as $id => $table)
					{
						// 						Piwik::log("get$partition building from array, one more table..");
						$dataTableArray = $dataTable->getArray();
						$goodtogo = array_key_exists($id, $dataTableArray);
						if (! $goodtogo)
						{
							Piwik::log("fatal error, could not find id $id");
							Piwik::log("available ids:");
							foreach ($dataTableArray as $id => $table)
								Piwik::log($id);
							// error!
						}
						$relatedTable = $dataTableArray[$id];
						foreach ($relatedTable->getRows() as $row)
						{
							// 							Piwik::log("get$partition building adding row " .$row->getColumn('label'));
							$table->addRow($row);
						}
					}
				}
				else if ($result instanceof Piwik_DataTable)
				{
					if (!($dataTable instanceof Piwik_DataTable))
					{
						Piwik::log("get$dimension error Piwik_DataTable");
					}
					//Piwik::log("get$dimension building from single");
					$rows = $dataTable->getRows();
					foreach ($rows as $row)
					{
						// 						Piwik::log("get$partition building adding row " .$row->getColumn('label'));
						$result->addRow($row);
					}
				}
				else
				{
					Piwik::log("error get$dimension building from unknown " . get_class($result));
				}
			}
		}
		return $result;
	}
	
	public function getTop($idSite, $period, $date, $segment = false, $partition = self::DIM_SEARCHPHRASE, $metrics = self::INDEX_CLICK_PROBABILITY)
	{
		// presently this is hard-coded.
		return array('ウサギ科', 'Canidae');
	}

	/**
	 * Produces an array of segments. Hard to explain. Example:
	 *
	 * input:
	 * segment: 'language==it,de;color==blue,black,red;country==us'
	 * dimension: 'language'
	 *
	 * output:
	 *
	 * array (
	 * 'language==it;color==blue,black,red;country==us'
	 * 'language==de;color==blue,black,red;country==us'
	 * )
	 *
	 * @param string $segment
	 * @param string $dimension
	 */
	private static function splitSegment($segment, $dimension)
	{
// 		Piwik::log("attempting to split segment $segment along $dimension");
		// case 1: at the end of the segment
		// array
		$matches = array();
		// string
		$pattern = '/(.*)'.$dimension.'==([^;]+)$/';
		// int
		$tosplit = preg_match($pattern, $segment, $matches);
		$hastail = false;
		if ($tosplit === 1)
		{
			// we're good
// 			Piwik::log("attempting to split segment $segment along $dimension .. good with no tail");
		}
		else if ($tosplit === 0)
		{
			// try with another method!
			// string
			$pattern = '/(.*)'.$dimension.'==([^;]+)(.+)$/';
			// int
			$tosplit = preg_match($pattern, $segment, $matches);
			if ($tosplit === 1)
			{
				// we're good
// 				Piwik::log("attempting to split segment $segment along $dimension .. good with tail");
				$hastail = true;
			}
			else if ($tosplit === 0)
			{
// 				Piwik::log("attempting to split segment $segment along $dimension .. no need to split");
				return array($segment);
			}
			else
			{
				// error!
// 				Piwik::log("attempting to split segment $segment along $dimension .. error!!!");
				return array($segment);
			}
		}
		else
		{
			// error!
// 			Piwik::log("attempting to split segment $segment along $dimension .. error!!!");
			return array($segment);
		}
		// string
		$before = $matches[1];
// 		Piwik::log("attempting to split segment $segment along $dimension .. before is $before");
		$thing = $matches[2];
// 		Piwik::log("attempting to split segment $segment along $dimension .. thing is $thing");
		$tail = '';
		if ($hastail)
			$tail = $matches[3];
// 		Piwik::log("attempting to split segment $segment along $dimension .. tail is $tail");
		$values = preg_split('/,/', $thing);
		$result = array();
		foreach ($values as $value)
		{
			$shard =$before.$dimension.'=='.$value.$tail;
// 			Piwik::log("attempting to split segment $segment along $dimension .. shard is $shard");
			$result[] = $shard;
		}
	
		return $result;
	}
	
	public static function encodeString($value)
	{
		$encoded = base64_encode($value);
		$replaced = str_replace('=', '_', $encoded);
		return $replaced;
	}
}
