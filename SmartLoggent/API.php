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
		}
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
	
	public function getSearchWord($idSite, $period, $date, $segment = false)
	{
		return $this->get($idSite, $period, $date, $segment, self::DIM_SEARCHWORD);
	}
	
	public function getClass($idSite, $period, $date, $segment = false)
	{
		$result = $this->get($idSite, $period, $date, $segment, Piwik_SmartLoggent_API::DIM_CLASS);
		return $result;
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
