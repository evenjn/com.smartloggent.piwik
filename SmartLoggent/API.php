<?php

class Piwik_SmartLoggent_API
{
	const DIM_CLASS = 'Class';
	const DIM_CLUSTER = 'Cluster';
	const DIM_LANGUAGE = 'Language';
	const DIM_NAMEDENTITY = 'NamedEntity';
	const DIM_NAMEDENTITYTYPE = 'NamedEntityType';
	const DIM_NATURALSEARCHPHRASE = 'NaturalSearchPhrase';
	const DIM_SEARCHPHRASE= 'SearchPhrase';
	const DIM_SEARCHWORD = 'SearchWord';	
	
	const SEG_CLASS = 'SLClass';
	const SEG_CLASSIFICATION = 'SLClassification';
	const SEG_CLUSTER = 'SLCluster';
	const SEG_CLUSTERANALYSIS = 'SLClusterAnalysis';
	const SEG_LANGUAGE = 'SLLanguage';
	const SEG_NAMEDENTITY = 'SLNamedEntity';
	const SEG_NAMEDENTITYTYPE = 'SLNamedEntityType';
	const SEG_NATURALSEARCHPHRASE= 'SLNaturalSearchPhrase';
	const SEG_SEARCHPHRASE= 'SLSearchPhrase';
	const SEG_SEARCHWORD = 'SLSearchWord';
	const SEG_SUPERCLASS = 'SLSuperClass';
	const SEG_TOPCLASS = 'SLTopClass';
	
	public static $SEGMENTS = array
	(
		self::SEG_CLASS
	,	self::SEG_CLASSIFICATION
	,	self::SEG_CLUSTER
	,	self::SEG_CLUSTERANALYSIS
	,	self::SEG_LANGUAGE
	,	self::SEG_NAMEDENTITY
	,	self::SEG_NAMEDENTITYTYPE
	,	self::SEG_NATURALSEARCHPHRASE
	,	self::SEG_SEARCHWORD
	,	self::SEG_SEARCHPHRASE
	,	self::SEG_SUPERCLASS
	,	self::SEG_TOPCLASS
	);
	
	public static $SEGMENTSTOESCAPE = array
	(
			// no segments to escape yet
	);
	
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
		}
		return self::$instance;
	}
	
	public function getClass($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, Piwik_SmartLoggent_API::DIM_CLASS);
	}
	
	public function getCluster($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_CLUSTER);
	}
	
	public function getLanguage($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_LANGUAGE);
	}
	
	public function getNamedEntity($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_NAMEDENTITY);
	}
	
	public function getNamedEntityType($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_NAMEDENTITYTYPE);
	}
	
	public function getNaturalSearchPhrase($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_NATURALSEARCHPHRASE);
	}
	
	public function getSearchPhrase($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_SEARCHPHRASE);
	}
	
	public function getSearchWord($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_SEARCHWORD);
	}
	
	public function get($idSite, $period, $date, $segment = false, $dimension = self::DIM_SEARCHPHRASE)
	{
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
		$shards =  Piwik_SmartLoggent_SegmentEditor::split($segment, 'SL'.$dimension);
		$result;
		$firstDatatable = true;
		foreach ($shards as $shard)
		{
// 			Piwik::smartlog("get$partition building from shard " . $shard);
			$archive = Piwik_SmartLoggent_Core_Archive::build($idSite, $period, $date, $shard);
			$dataTable = $archive->getDataTable('SmartLoggent_'.$dimension);
// 			Piwik::smartlog("get$partition got the datatable from archive, type " .get_class($dataTable) );
			if($firstDatatable)
			{
				$firstDatatable = false;
				$result = $dataTable;
// 				if ($result instanceof Piwik_DataTable_Array)
// 				{
// 					$asarray = $result->getArray();
// 					Piwik::smartlog("get$dimension first shard is array of DT.. Piwik_DataTable_Array");
// 					foreach ($asarray as $id => $table)
// 					{
// 						foreach ($table->getRows() as $row)
// 						{
// 							Piwik::smartlog("get$dimension first shard datatable table #$id contains row " .$row->getColumn('label'));
// 						}
// 					}
// 				}
// 				else if ($result instanceof Piwik_DataTable)
// 				{
// 					foreach ($result->getRows() as $row)
// 					{
// 						Piwik::smartlog("get$dimension first shard datatable contains row " .$row->getColumn('label'));
// 					}
// 				}
			}
			else
			{
// 				Piwik::smartlog("get$partition it's not the first");
				if ($result instanceof Piwik_DataTable_Array)
				{
					if (!($dataTable instanceof Piwik_DataTable_Array))
					{
						// This should never occur, but I am not completely sure.
						Piwik::log("get$dimension error on Piwik_DataTable_Array");
					}
// 					Piwik::smartlog("get$partition building from array");
					$asarray = $result->getArray();
					foreach ($asarray as $id => $table)
					{
// 						Piwik::smartlog("get$partition building from array, one more table..");
						$dataTableArray = $dataTable->getArray();
						$goodtogo = array_key_exists($id, $dataTableArray);
						if (! $goodtogo)
						{
							// This should never occur, but I am not completely sure.
							Piwik::log("fatal error, could not find id $id");
							Piwik::log("available ids:");
							foreach ($dataTableArray as $id => $table)
								Piwik::log($id);
						}
						$relatedTable = $dataTableArray[$id];
						foreach ($relatedTable->getRows() as $row)
						{
// 							Piwik::smartlog("get$dimension building adding row " .$row->getColumn('label'));
							$table->addRow($row);
						}
					}
				}
				else if ($result instanceof Piwik_DataTable)
				{
					if (!($dataTable instanceof Piwik_DataTable))
					{
						// This should never occur, but I am not completely sure.
						Piwik::log("get$dimension error Piwik_DataTable");
					}
// 					Piwik::smartlog("get$dimension building from single");
					$rows = $dataTable->getRows();
					foreach ($rows as $row)
					{
// 						Piwik::smartlog("get$dimension building adding row " .$row->getColumn('label'));
						$result->addRow($row);
					}
				}
				else
				{
					// This should never occur, but I am not completely sure.
					Piwik::log("error get$dimension building from unknown " . get_class($result));
				}
			}
		}
		return $result;
	}
	
	public function getTop($idSite, $period, $date, $segment = false, $partition = self::DIM_SEARCHPHRASE, $metrics = self::INDEX_CLICK_PROBABILITY, $limit=10)
	{
		$archive = Piwik_SmartLoggent_Core_Archive::build($idSite, $period, $date, $segment);
		$dataTable = $archive->getDataTable('SmartLoggent_'.$partition."By".$metrics);
		$result = array();
		$ids = array();
		$labels = array();
		$rows = $dataTable->getRows();
		$count = 0;
		foreach ($rows as $row)
		{
			if ($count >= $limit)
				break;
			$label = $row->getColumn('label');
			$id = $row->getColumn('id');
			$ids[] = $id;
			$labels[] = $label;
			$count = $count + 1;
		}
		$result['ids'] = $ids;
		$result['labels'] = $labels;
		return $result;
	}
	
	public static function encodeString($value)
	{
		$encoded = base64_encode($value);
		$replaced = str_replace('=', '_', $encoded);
		// there are other characters to replace..
		return $replaced;
	}
	
	public static function decodeString($value)
	{
		// there are other characters to replace..
		$replaced = str_replace('_', '=', $value);
		$decoded = base64_decode($replaced);
		return $decoded;
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
}
