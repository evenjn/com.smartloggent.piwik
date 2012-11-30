<?php
class Piwik_SmartLoggent_SQL
{
	private static $languages = array (Piwik_SmartLoggent_API::DIM_LANGUAGE, "ja", "it", "de");
	private static $searchPhrases = array (Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, "gatto", "bulldog", "ウサギ");
	private static $searchWords = array (Piwik_SmartLoggent_API::DIM_SEARCHWORD, "gatto", "bulldog", "ウサギ");
	private static $classes = array (Piwik_SmartLoggent_API::DIM_CLASS, "Felidae", "Canidae", "ウサギ科");
	
	private static function archiveTop(Piwik_ArchiveProcessing $archive)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$DIMENSIONS = array
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
		);
		$METRICS = array
		(
				Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
		);
		foreach($DIMENSIONS as $dimension)
		{
			foreach($METRICS as $metric)
			{
				$table = new Piwik_DataTable();
				$resultset = self::queryTop($archive, $dimension, $metric, 20);
				$result = array();
				while($row = $resultset->fetch())
				{
					$rowConstructor = array();
					$rowColumn = array();
					$rowColumn['label'] = $row['label'];
					$rowColumn['id'] = $row['id'];
					$rowConstructor[Piwik_DataTable_Row::COLUMNS] = $rowColumn;
					$dataTableRow = new Piwik_DataTable_Row($rowConstructor);
					$table->addRow($dataTableRow);
				}
				$archive->insertBlobRecord('SmartLoggent_'.$dimension.'By'.$metric, $table->getSerialized());
				destroy($table);
			}
		}
// 		Piwik::profileend($profiler);
	}
	public static function archiveDay(Piwik_ArchiveProcessing_Day $archive)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
// 		Piwik::log('Piwik_SmartLoggent_SQL::'.__FUNCTION__." FYI the encoding of Canidae is ".Piwik_SmartLoggent_API::encodeString("Canidae"));
// 		Piwik::log('Piwik_SmartLoggent_SQL::'.__FUNCTION__." FYI the encoding of Felidae is ".Piwik_SmartLoggent_API::encodeString("Felidae"));
		
		$qa_by_nsp = self::queryQueryActionsByNormalizedSearchPhrase($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
		self::archive($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $qa_by_nsp);
		$qa_by_nsp = self::queryQueryActionsByNormalizedSearchPhrase($archive, Piwik_SmartLoggent_API::DIM_CLASS);
		self::archive($archive, Piwik_SmartLoggent_API::DIM_CLASS, $qa_by_nsp);
		self::archiveTop($archive);
		
// 		Piwik::profileend($profiler);
	}
	
	public static function archivePeriod(Piwik_ArchiveProcessing_Period $archive)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$archive->archiveDataTable(array('SmartLoggent_'.Piwik_SmartLoggent_API::DIM_SEARCHPHRASE));
		$archive->archiveDataTable(array('SmartLoggent_'.Piwik_SmartLoggent_API::DIM_CLASS));
		self::archiveTop($archive);
// 		$archive->archiveDataTable(array('SmartLoggent_'.self::$languages[0]));
// 		$archive->archiveDataTable(array('SmartLoggent_'.self::$searchPhrases[0]));
// 		$archive->archiveDataTable(array('SmartLoggent_'.self::$classes[0]));
// 		$archive->archiveDataTable(array('SmartLoggent_'.self::$searchWords[0]));
// 		Piwik::profileend($profiler);
	}
	
	/**
	 * Originally ArchiveProcessing/Day.php had this function
	 * queryActionsByDimension where you could get a table with statistics about
	 * actions grouped by the value of a certain metric, such as browser name,
	 * referrer name, originating country, etc.
	 *
	 * This method returns a table with statistics about those actions that
	 * we have identified as user queries, also known as Query Events, grouped by
	 *
	 * Typically you want to group by "normalized query", but you may also want to
	 * group by "normalized query, classification value", etc.
	 *
	 * Although the second is better achieved using segmentation.
	 *
	 * Segmentation features are still available.
	 *
	 *
	 * Returns the queries by the given dimension
	 *
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 * @param string $annotationtype1
	 * @param string $annotationtype2
	 *
	 * @return Zend_Db_Statement_Interface
	 */
	private static function queryQueryActionsByNormalizedSearchPhrase(Piwik_ArchiveProcessing_Day $archiveProcessing, $dimension)
	{
		//$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$select = "";
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$select .= "normalizedsearchphrase.NP_NormalizedText AS label";
			$select .= ", normalizedsearchphrase.NP_IdNormalizedSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$select .= "category.CL_Descr AS label";
			$select .= ", category.CL_IdClass AS id";
			$select .= ", ancestor.CA_hasSubclasses AS hasSubclasses";
		}
		$select .= ", count(distinct log_visit.idvisitor) as `".Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS."`";
		$select .= ", count(distinct log_visit.idvisit) as `".Piwik_SmartLoggent_API::INDEX_NB_VISITS."`";
		$select .= ", count(distinct log_link_visit_action.idlink_va) as `".Piwik_SmartLoggent_API::INDEX_NB_QUERIES."`";
		$select .= ", count(distinct log_link_visit_action.idlink_va) as `".Piwik_SmartLoggent_API::INDEX_FR_QUERIES."`";
		$select .= ", avg(queryevent.QE_ResultCount) as `".Piwik_SmartLoggent_API::INDEX_AVG_RESULTS."`";
		$select .= ", sum(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_NB_CLICKS."`";
		$select .= ", sum(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_FR_CLICKS."`";
		$select .= ", avg(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_AVG_CLICKS."`";
		$select .= ", avg(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as `". Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY ."`";
		$select .= ", avg(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as `". Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY ."`";
		$from = array();
		$from[] = "log_link_visit_action";
		$from[] = "log_visit";
		$from[] = self::getSqlSegmentFor('DEP_queryevent');
		$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
		$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
		
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$from[] = self::getSqlSegmentFor('DEP_linkphraseclass');
			$from[] = self::getSqlSegmentFor('DEP_ancestor');
			$from[] = self::getSqlSegmentFor('DEP_category');
		}
		$where = "";
		$where .= "log_link_visit_action.server_time >= ?";
		$where .= " AND log_link_visit_action.server_time <= ?";
		$where .= " AND log_link_visit_action.idsite = ?";
		$groupBy = 'label';
		$bind = array();
		$bind[] = $archiveProcessing->getStartDatetimeUTC();
		$bind[] = $archiveProcessing->getEndDatetimeUTC();
		$bind[] = $archiveProcessing->idsite;

		$custom_segment = $archiveProcessing->getSegment();
		
		//
		// When the ArchiveProcessing_Day.compute event is broadcasted by
		// an ArchiveProcessing_Day object as part of a computation of
		// a getDataTable invoked by a plugin that is not SmartLoggent, the Segment
		// of each ArchiveProcessing_Xx is a regular segment, and thus it cannot
		// be used to produce a query that fetches data from SmartLoggent tables.
		// The system therefore creates a custom segment on the spot.. here!
		//
		if (!($custom_segment instanceof Piwik_SmartLoggent_Core_Segment))
		{
			$idSite = Piwik_Common::getRequestVar('idSite', false, 'int');
			$segmentString = $archiveProcessing->getSegment()->getString();
			$custom_segment = new Piwik_SmartLoggent_Core_Segment($segmentString, $idSite);
		}
		$query = $custom_segment->getSelectQuery($select, $from, $where, $bind, $orderBy=false, $groupBy);
		//
		// to see the final query: echo $query['sql'];
		//
		//echo $query['sql'];
// 		foreach ($query['bind'] as $key => $value)
// 		{
// 			echo "key is $key";
// 			echo "value is $value";
// 		}
		$result = $archiveProcessing->db->query($query['sql'], $query['bind']);
		//Piwik::profileend($profiler);
		return $result;
	}
	
	private static function archive(Piwik_ArchiveProcessing_Day $archiveProcessing, $tableName, Zend_Db_Statement_Interface $resultset)
	{
		//$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$table = new Piwik_DataTable();
		while($row = $resultset->fetch())
		{
			$rowConstructor = array();
			$rowColumn = array();
			$rowColumn['label'] = $row['label'];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS] = $row[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_VISITS] = $row[Piwik_SmartLoggent_API::INDEX_NB_VISITS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_QUERIES] = $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_QUERIES] = $row[Piwik_SmartLoggent_API::INDEX_FR_QUERIES];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_RESULTS] = $row[Piwik_SmartLoggent_API::INDEX_AVG_RESULTS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_NB_CLICKS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_FR_CLICKS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_AVG_CLICKS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY] = $row[Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY] = $row[Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY];
			$rowMetadata = array();
			$rowMetadata['annotation'] = Piwik_SmartLoggent_API::encodeString($row['label']);
			$rowMetadata['sl_id'] = $row['id'];
			if (isset($row['hasSubclasses']))
			{
				$rowMetadata['sl_hasSubclasses'] = $row['hasSubclasses'];
			}
			
			$rowMetadata['type'] = $tableName;
			$rowConstructor[Piwik_DataTable_Row::METADATA] = $rowMetadata;
			$rowConstructor[Piwik_DataTable_Row::COLUMNS] = $rowColumn;
			$dataTableRow = new Piwik_DataTable_Row($rowConstructor);
			$table->addRow($dataTableRow);
		}
		$archiveProcessing->insertBlobRecord('SmartLoggent_'.$tableName, $table->getSerialized());
		destroy($table);
		//Piwik::profileend($profiler);
	}
	
	
	
	static private $instance = null;
	
	private $sqlSegments = null;
	
	// these are the names of the table aliases used in the query that can be
	// used to segment.
	public static $SEGMENT_TABLES = array('category', 'normalizedsearchphrase', 'supercategory', 'ancestor');
	
	static public function getDependenciesFor($tables)
	{
		// $tables is an array of table aliases coming from the segment sql fragment definition, such as SLClassification, SLLanguage
		// presently they are simply "classification"
		$result = array();
		// the result actually depends on the required tables..
		// but for the moment we are happy with all of them.
		//
		// the keys in this map don't need to be (read: it would be less confusing if they were not) names of tables
		$dependencies = array();
		$dependencies['normalizedsearchphrase'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase');
		$dependencies['category'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase', 'DEP_linkphraseclass', 'DEP_ancestor');
		$dependencies['ancestor'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase', 'DEP_linkphraseclass', 'DEP_ancestor', 'DEP_category');
		$dependencies['supercategory'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase', 'DEP_linkphraseclass', 'DEP_ancestor', 'DEP_category','DEP_supercategory');
		foreach (self::$SEGMENT_TABLES as $possible_segment_alias)
		{
			if (in_array($possible_segment_alias, $tables))
			{
				$actual_dependencies = $dependencies[$possible_segment_alias];
				foreach ($actual_dependencies as $actual_dependency)
				{
					if (!in_array($actual_dependency, $result))
						$result[] = $actual_dependency;
				}
			}
		}
		return $result;
	}
	
	static public function getSqlSegmentFor($key)
	{
		return self::getInstance()->sqlSegments[$key];
	}
	
	/** Get singleton instance
	 * @return Piwik_SmartLoggent_API */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
			self::$instance->sqlSegments = array();
			self::$instance->sqlSegments['DEP_queryevent'] = array
			(
					'table' => 'TD_QueryEvents'
					,	'tableAlias' => 'queryevent'
					,	'joinOn' => 'queryevent.QE_Query_piwik_idlink_va = log_link_visit_action.idlink_va'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_queryevent'
			);
			self::$instance->sqlSegments['DEP_naturalsearchphrase'] = array
			(
					'table' => 'TD_NaturalSearchPhrases'
					,	'tableAlias' => 'naturalsearchphrase'
					,	'joinOn' => 'naturalsearchphrase.SP_IdNaturalSearchPhrase = queryevent.QE_SP_IdNaturalSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_naturalsearchphrase'
			);
			self::$instance->sqlSegments['DEP_normalizedsearchphrase'] = array
			(
					'table' => 'TD_NormalizedSearchPhrases'
					,	'tableAlias' => 'normalizedsearchphrase'
					,	'joinOn' => 'normalizedsearchphrase.NP_IdNormalizedSearchPhrase = naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_normalizedsearchphrase'
			);
			self::$instance->sqlSegments['DEP_linkphraseclass'] = array
			(
					'table' => 'TL_PhraseClasses'
					,	'tableAlias' => 'linkphraseclass'
					,	'joinOn' => 'linkphraseclass.PC_NP_IdNormalizedSearchPhrase = normalizedsearchphrase.NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_linkphraseclass'
			);
			self::$instance->sqlSegments['DEP_ancestor'] = array
			(
					'table' => 'TB_ClassAncestors'
					,	'tableAlias' => 'ancestor'
					,	'joinOn' => 'ancestor.CA_Class_IdClass = linkphraseclass.PC_CL_IdClass and ancestor.CA_Common_TA_IdTaxonomy = linkphraseclass.PC_CL_TA_IdTaxonomy'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_ancestor'
			);
			self::$instance->sqlSegments['DEP_category'] = array
			(
					'table' => 'TB_Classes'
					,	'tableAlias' => 'category'
					,	'joinOn' => 'category.CL_IdClass = ancestor.CA_Ancestor_IdClass and category.CL_TA_IdTaxonomy = ancestor.CA_Common_TA_IdTaxonomy'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_category'
			);
			self::$instance->sqlSegments['DEP_supercategory'] = array
			(
					'table' => 'TB_ClassAncestors'
					,	'tableAlias' => 'supercategory'
					,	'joinOn' => 'supercategory.CA_Class_IdClass = ancestor.CA_Ancestor_IdClass and supercategory.CA_Common_TA_IdTaxonomy = ancestor.CA_Common_TA_IdTaxonomy'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_supercategory'
			);
				
		}
		return self::$instance;
	}
	
	
	
	
	/**
	 * Given a start date and an end date, a dimension and a metric, this method
	 * will partition the data along that dimension,
	 * compute the chosen metric of each part,
	 * rank the parts, and return the top N in form of pairs (id, label). 
	 * 
	 * Segmentation features should still available.
	 *
	 * Returns the queries by the given dimension
	 *
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 *
	 * @return Zend_Db_Statement_Interface
	 */
	private static function queryTop(Piwik_ArchiveProcessing $archiveProcessing, $dimension, $metric, $limit)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		
		// the plan is to support all dimensions, but presently only two are supported:
		// Piwik_SmartLoggent_API::DIM_CLASS
		// Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
		
		
		$select = "";
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$select .= "normalizedsearchphrase.NP_NormalizedText AS label";
			$select .= ", normalizedsearchphrase.NP_IdNormalizedSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$select .= "category.CL_Descr AS label";
			$select .= ", category.CL_IdClass AS id";
		}
		// the plan is to support all metrics, but presently only four are supported
		// 
		// Piwik_SmartLoggent_API::INDEX_NB_QUERIES
		// Piwik_SmartLoggent_API::INDEX_NB_VISITS
		// Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
		// Piwik_SmartLoggent_API::INDEX_NB_CLICKS
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
			$select .= ", count(distinct log_link_visit_action.idlink_va) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_VISITS)
			$select .= ", count(distinct log_visit.idvisit) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
			$select .= ", count(distinct log_visit.idvisitor) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
			$select .= ", count(queryevent.QE_ClickCount) as metric";
		
		$from = array();
		$from[] = "log_link_visit_action";
		$from[] = "log_visit";
		$from[] = self::getSqlSegmentFor('DEP_queryevent');
		$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
		$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
	
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$from[] = self::getSqlSegmentFor('DEP_linkphraseclass');
			$from[] = self::getSqlSegmentFor('DEP_ancestor');
			$from[] = self::getSqlSegmentFor('DEP_category');
		}
		$where = "";
		$where .= "log_link_visit_action.server_time >= ?";
		$where .= " AND log_link_visit_action.server_time <= ?";
		$where .= " AND log_link_visit_action.idsite = ?";
		$groupBy = 'id, label';
		$orderBy = 'metric desc';
		$bind = array();
		$bind[] = $archiveProcessing->getStartDatetimeUTC();
		$bind[] = $archiveProcessing->getEndDatetimeUTC();
		$bind[] = $archiveProcessing->idsite;
	
		$custom_segment = $archiveProcessing->getSegment();
	
		//
		// When the ArchiveProcessing_Day.compute event is broadcasted by
		// an ArchiveProcessing_Day object as part of a computation of
		// a getDataTable invoked by a plugin that is not SmartLoggent, the Segment
		// of each ArchiveProcessing_Xx is a regular segment, and thus it cannot
		// be used to produce a query that fetches data from SmartLoggent tables.
		// The system therefore creates a custom segment on the spot.. here!
		//
		if (!($custom_segment instanceof Piwik_SmartLoggent_Core_Segment))
		{
			$idSite = Piwik_Common::getRequestVar('idSite', false, 'int');
			$segmentString = $archiveProcessing->getSegment()->getString();
			$custom_segment = new Piwik_SmartLoggent_Core_Segment($segmentString, $idSite);
		}
		$query = $custom_segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy);
		//
		// to see the final query: echo $query['sql'];
		//
// 		echo $query['sql'];
// 				foreach ($query['bind'] as $key => $value)
// 					{
// 						echo "key is $key";
// 						echo "value is $value";
// 					}
		$result = Zend_Registry::get('db')->query($query['sql'].' limit 0, '.$limit, $query['bind']);
// 		Piwik::profileend($profiler);
		return $result;
	}
}
