<?php
class Piwik_SmartLoggent_SQL
{
	

	
	public static function archiveAll(Piwik_ArchiveProcessing $archive)
	{
		$computeEverything = true;
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$segment = $archive->getSegment()->getString();
		$start = $archive->getStartDatetimeUTC();
		$end = $archive->getEndDatetimeUTC();
// 		Piwik::smartlog("for $start : $end segment $segment");
		// in order to avoid useless computation, we need to look inside the segment
		// for example:
		// 
		// the by-class stats table is useless unless the user specified
		// that only the top-classes are to be displayed,
		// or that only the subclasses of a certain class are to be displayed.
		
		
		// SEG_TOPCLASS
		$seg_topclass = false;
		if (Piwik_SmartLoggent_SegmentEditor::featureIsSet(Piwik_SmartLoggent_API::SEG_TOPCLASS, $segment))
			$seg_topclass = true;
		// SEG_SUPERCLASS
		$seg_superclass = false;
		if (Piwik_SmartLoggent_SegmentEditor::featureIsSet(Piwik_SmartLoggent_API::SEG_SUPERCLASS, $segment))
			$seg_superclass = true;
		// SEG_CLASS
		$seg_class = false;
		if (Piwik_SmartLoggent_SegmentEditor::featureIsSet(Piwik_SmartLoggent_API::SEG_CLASS, $segment))
			$seg_class = true;
		// SEG_CLUSTER_ANALYSIS
		$seg_cluster_analysis = false;
		if (Piwik_SmartLoggent_SegmentEditor::featureIsSet(Piwik_SmartLoggent_API::SEG_CLUSTERANALYSIS, $segment))
			$seg_cluster_analysis = true;
		
		// should we compute the normalized search phrase table?
		// in general, yes
		$compute_bynormalized_table = true;
		// not when the user is asking for the distrubution along all top classes.
		if ($seg_topclass)
			$compute_bynormalized_table = false;
		
		// should we compute the natural table?
		// in general no
		$compute_bynatural_table = false;
		// only if the user specifies a search phrase.
// 		if ($seg_cluster_analysis)
			$compute_bynatural_table = true;
		
		// should we compute the NETypes table?
		// in general yes
		$compute_bynetype_table = true;
		// not when the user is asking for the distrubution along all top classes.
		if ($seg_topclass)
			$compute_bynetype_table = false;
		
		// should we compute the Clusters table?
		// in general 
		$compute_bycluster_table = false;
		// only if the user specifies a cluster analysis.
		if ($seg_cluster_analysis)
			$compute_bycluster_table = true;
		
		// should we compute the Search Words table?
		// in general
		$compute_byword_table = true;
		
		// should we compute the Language table?
		// in general
		$compute_bylanguage_table = true;
		
		// should we compute the NE table?
		// as of now, we alwasys compute it
		$compute_byne_table = true;
		// not when the user is asking for the distrubution along all top classes.
		if ($seg_topclass)
			$compute_byne_table = false;
		
		// should we compute the class table?
		$compute_byclass_table = false;
		if ($seg_superclass)
			$compute_byclass_table = true;
		if ($seg_topclass)
			$compute_byclass_table = true;
		if ($seg_class)
			$compute_byclass_table = true;
		
		// should we compute the top normalized search phrases tables?
		// in general, yes
		$compute_top_normalized_tables = true;
		// not when the user is asking for the distrubution along all top classes.
		if ($seg_topclass)
			$compute_top_normalized_tables = false;
		
		// should we compute the top natural search phrases tables?
		// in general, no
		$compute_top_natural_tables = false;
		// not when the user is asking for a specific search phrase
// 		if ($seg_topclass)
// 			$compute_top_normalized_tables = false;
		
		// should we compute the top named entity type tables?
		// in general, yes
		$compute_top_net_tables = true;
		
		// should we compute the top words tables?
		// in general, yes
		$compute_top_words_tables = true;
		
		// should we compute the top languages tables?
		// in general, yes
		$compute_top_languages_tables = true;
		
		// should we compute the top named entity tables?
		// in general, yes
		$compute_top_ne_tables = true;
		
		// should we compute the top classes tables?
		// in general, no
		$compute_top_classes_tables = false;
		
		// should we compute the top cluster tables?
		// in general, no
		$compute_top_cluster_tables = false;
		// only if the user specifies a cluster analysis.
		if ($seg_cluster_analysis)
			$compute_top_cluster_tables = true;
		
		// but when we are showing the class overview, yes.
		// presently disabled!
		if ($seg_topclass)
			$compute_top_classes_tables = true;
		// also, when we show the subclasses of a class.
		if ($seg_superclass)
			$compute_top_classes_tables = true;
		
// 		Piwik::smartlog('Piwik_SmartLoggent_SQL::'.__FUNCTION__." FYI the encoding of Canidae is ".Piwik_SmartLoggent_API::encodeString("Canidae"));
// 		Piwik::smartlog('Piwik_SmartLoggent_SQL::'.__FUNCTION__." FYI the encoding of Felidae is ".Piwik_SmartLoggent_API::encodeString("Felidae"));
		
		
		
		
		// actually we need to store nevertheless the unpartitioned grand totals of
		// all the stats like the total number of unique visitors, total number of
		// queries etc.

		$totalVisitors = 100;
		$totalVisits = 100;
		$totalQueries = 100;
		$totalClicks = 100;
		$resultset = self::queryQueryActionsTotalStats($archive);
// 		// archive it
		$table = new Piwik_DataTable();
		$result = array();
		while($row = $resultset->fetch())
		{
			$rowConstructor = array();
			$rowColumn = array();
			$rowColumn['label'] = 'All';
			$totalVisitors = $row[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS];
			$totalVisits = $row[Piwik_SmartLoggent_API::INDEX_NB_VISITS];
			$totalQueries = $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES];
			$totalClicks = $row[Piwik_SmartLoggent_API::INDEX_NB_CLICKS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS] = $row[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_VISITS] = $row[Piwik_SmartLoggent_API::INDEX_NB_VISITS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_QUERIES] = $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_NB_CLICKS];
			$rowConstructor[Piwik_DataTable_Row::COLUMNS] = $rowColumn;
			$dataTableRow = new Piwik_DataTable_Row($rowConstructor);
			$table->addRow($dataTableRow);
		}
		$archive->insertBlobRecord('SmartLoggent_TotalStats', $table->getSerialized());
		destroy($table);
		
		// BY NORMALIZED SEARCH PHRASE
		if ($computeEverything || $compute_bynormalized_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
		}
		
		// BY LANGUAGE
		if ($computeEverything || $compute_bylanguage_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_LANGUAGE);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_LANGUAGE, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_LANGUAGE);
		}
		
		// BY CLASS
		if ($computeEverything || $compute_byclass_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_CLASS);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_CLASS, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_CLASS);
		}
		
		// BY NE TYPE
		if ($computeEverything || $compute_bynetype_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE);
		}
		
		// BY NE
		if ($computeEverything || $compute_byne_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITY);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITY, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_NAMEDENTITY);
		}
		// BY CLUSTER
		if ($computeEverything || $compute_bycluster_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_CLUSTER);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_CLUSTER, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_CLUSTER);
		}
		// BY NATURAL SEARCH PHRASE
		if ($computeEverything || $compute_bynatural_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE);
		}
		// BY SEARCH WORD
		if ($computeEverything || $compute_byword_table)
		{
			$qa_by_nsp = self::queryQueryActions($archive, Piwik_SmartLoggent_API::DIM_SEARCHWORD);
			self::archive($archive, Piwik_SmartLoggent_API::DIM_SEARCHWORD, $qa_by_nsp, $totalVisitors, $totalVisits, $totalQueries, $totalClicks);
		}
		else
		{
			self::archiveEmpty($archive, Piwik_SmartLoggent_API::DIM_SEARCHWORD);
		}
			
		$DIMENSIONS = array
		(
				Piwik_SmartLoggent_API::DIM_CLASS
				, Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE
				, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE
				, Piwik_SmartLoggent_API::DIM_SEARCHWORD
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE
				, Piwik_SmartLoggent_API::DIM_NAMEDENTITY
				, Piwik_SmartLoggent_API::DIM_CLUSTER
				, Piwik_SmartLoggent_API::DIM_LANGUAGE
		);
		$METRICS = array
		(
				Piwik_SmartLoggent_API::INDEX_NB_QUERIES
				,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
				,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
				,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
				,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
				,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
		);
		foreach($DIMENSIONS as $dimension)
		{
// 			$profilerDimension = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__." for dimension $dimension"); // 		Piwik::profileend($profiler);
			foreach($METRICS as $metric)
			{
				$skip = false;
				if ($dimension == Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE && !$compute_top_natural_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHPHRASE && !$compute_top_normalized_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_CLASS && !$compute_top_classes_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE && !$compute_top_net_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_NAMEDENTITY && !$compute_top_ne_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_CLUSTER && !$compute_top_cluster_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_SEARCHWORD && !$compute_top_words_tables)
					$skip = true;
				if ($dimension == Piwik_SmartLoggent_API::DIM_LANGUAGE && !$compute_top_languages_tables)
					$skip = true;
				if ($computeEverything || !$skip)
				{
// 					$profilerDimensionMetric = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__." for dimension $dimension and metric $metric"); // 		Piwik::profileend($profiler);
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
// 					Piwik::profileend($profilerDimensionMetric);
				}
				else
				{
					self::archiveEmpty($archive, 'SmartLoggent_'.$dimension.'By'.$metric);
				}
			}
			
// 			Piwik::profileend($profilerDimension);
		}
		
// 		Piwik::profileend($profiler);
	}
	
// 	public static function archivePeriod(Piwik_ArchiveProcessing_Period $archive)
// 	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
// 		$qa_by_nsp = self::queryQueryActionsByNormalizedSearchPhrase($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE);
// 		self::archive($archive, Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, $qa_by_nsp);
// 		$qa_by_nsp = self::queryQueryActionsByNormalizedSearchPhrase($archive, Piwik_SmartLoggent_API::DIM_CLASS);
// 		self::archive($archive, Piwik_SmartLoggent_API::DIM_CLASS, $qa_by_nsp);
// 		self::archiveTop($archive);
// 		Piwik::profileend($profiler);
// 	}
	
	
	/**
	 *
	 * @return Zend_Db_Statement_Interface
	 */
	private static function queryQueryActionsTotalStats(Piwik_ArchiveProcessing $archiveProcessing)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$select = "1 as label";
		$select .= ", count(distinct log_visit.idvisitor) as `".Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS."`";
		$select .= ", count(distinct log_visit.idvisit) as `".Piwik_SmartLoggent_API::INDEX_NB_VISITS."`";
		$select .= ", count(distinct log_link_visit_action.idlink_va) as `".Piwik_SmartLoggent_API::INDEX_NB_QUERIES."`";
		$select .= ", sum(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_NB_CLICKS."`";
		$from = array();
		$from[] = "log_link_visit_action";
		$from[] = "log_visit";
		$from[] = self::getSqlSegmentFor('DEP_queryevent');
		$where = "";
		$where .= "log_link_visit_action.server_time >= ?";
		$where .= " AND log_link_visit_action.server_time <= ?";
		$where .= " AND log_link_visit_action.idsite = ?";
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
		$query = $custom_segment->getSelectQuery($select, $from, $where, $bind);
		//
		// to see the final query: echo $query['sql'];
		//
// 		Piwik::smartlog("queryQueryActionsTotalStats SQL:");
// 		Piwik::smartlog($query['sql']);
// 				foreach ($query['bind'] as $key => $value)
// 					{
// 						Piwik::smartlog("key is $key, value is $value");
// 					}
		$result = Zend_Registry::get('db')->query($query['sql'], $query['bind']);
// 		Piwik::profileend($profiler);
		return $result;
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
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @param string $annotationtype1
	 * @param string $annotationtype2
	 *
	 * @return Zend_Db_Statement_Interface
	 */
	private static function queryQueryActions(Piwik_ArchiveProcessing $archiveProcessing, $dimension)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		
		$select = "";
		if ($dimension === Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE)
		{
			$select .= "naturalsearchphrase.SP_NaturalText AS label";
			$select .= ", naturalsearchphrase.SP_IdNaturalSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$select .= "normalizedsearchphrase.NP_NormalizedText AS label";
			$select .= ", normalizedsearchphrase.NP_IdNormalizedSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			$select .= "lemma.LM_LemmaString AS label";
			$select .= ", lemma.LM_IdLemma AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$select .= "category.CL_Descr AS label";
			$select .= ", category.CL_IdClass AS id";
			$select .= ", category.CL_hasSubclasses AS hasSubclasses";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			$select .= "namedentitytype.NT_Descr AS label";
			$select .= ", namedentitytype.NT_IdEntityType AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			$select .= "namedentity.NE_EntityDescr AS label";
			$select .= ", namedentity.NE_IdEntity AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			$select .= "cluster.CS_Descr AS label";
			$select .= ", cluster.CS_IdCluster AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			$select .= "language.LG_Descr AS label";
			$select .= ", language.LG_IdLanguage AS id";
		}
		$select .= ", count(distinct log_visit.idvisitor) as `".Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS."`";
		$select .= ", count(distinct log_visit.idvisit) as `".Piwik_SmartLoggent_API::INDEX_NB_VISITS."`";
		$select .= ", count(distinct log_link_visit_action.idlink_va) as `".Piwik_SmartLoggent_API::INDEX_NB_QUERIES."`";
// 		$select .= ", count(distinct log_link_visit_action.idlink_va) as `".Piwik_SmartLoggent_API::INDEX_FR_QUERIES."`";
		$select .= ", avg(queryevent.QE_ResultCount) as `".Piwik_SmartLoggent_API::INDEX_AVG_RESULTS."`";
		$select .= ", sum(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_NB_CLICKS."`";
// 		$select .= ", sum(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_FR_CLICKS."`";
		$select .= ", avg(queryevent.QE_ClickCount) as `".Piwik_SmartLoggent_API::INDEX_AVG_CLICKS."`";
		$select .= ", sum(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as queryeventwithclick";
// 		$select .= ", avg(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as `". Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY ."`";
		$from = array();
		$from[] = "log_link_visit_action";
		$from[] = "log_visit";

		if ($dimension === Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphraselemma');
			$from[] = self::getSqlSegmentFor('DEP_lemma');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphraseclass');
			$from[] = self::getSqlSegmentFor('DEP_category');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasenamedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentitytype');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasenamedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentity');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasecluster');
			$from[] = self::getSqlSegmentFor('DEP_cluster');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_language');
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
		
 			if(Piwik_SmartLoggent_SegmentEditor::featureIsSet(Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE, Piwik_Common::getRequestVar('segment', false, 'string')))
 			{
 		Piwik::smartlog($query['sql']);
 		foreach ($query['bind'] as $key => $value)
 			Piwik::smartlog("$key: $value");
 				foreach ($query['bind'] as $key => $value)
 					{
 						Piwik::smartlog("key is $key, value is $value");
 					}
 			}

		$result = Zend_Registry::get('db')->query($query['sql'], $query['bind']);
// 		Piwik::profileend($profiler);
		Piwik::smartlog($query['sql']);
		Piwik::smartlog("TEST");
		
		return $result;
	}
	
	
	
	private static function archiveEmpty(Piwik_ArchiveProcessing $archiveProcessing, $tableName)
	{
		$table = new Piwik_DataTable();
		$archiveProcessing->insertBlobRecord('SmartLoggent_'.$tableName, $table->getSerialized());
		destroy($table);
	}
	
	private static function archive(Piwik_ArchiveProcessing $archiveProcessing, $tableName, Zend_Db_Statement_Interface $resultset
			, $totalVisitors, $totalVisits, $totalQueries, $totalClicks)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SQL::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$table = new Piwik_DataTable();
		while($row = $resultset->fetch())
		{
			$rowConstructor = array();
			$rowColumn = array();
			$rowColumn['label'] = $row['label'];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS] = $row[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_VISITS] = $row[Piwik_SmartLoggent_API::INDEX_NB_VISITS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_QUERIES] = $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES];
// 			$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_QUERIES] = 0;
// 			if ($totalQueries > 0)
// 				$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_QUERIES] = (1.0 * $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES]) / (1.0 * $totalQueries);
			 
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_RESULTS] = $row[Piwik_SmartLoggent_API::INDEX_AVG_RESULTS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_NB_CLICKS];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_CLICKS] = $row[Piwik_SmartLoggent_API::INDEX_AVG_CLICKS];
			$rowColumn['queryeventwithclick'] = $row['queryeventwithclick'];
			$rowColumn['queryevents'] = $row[Piwik_SmartLoggent_API::INDEX_NB_QUERIES];
// 			$rowColumn['reserved2'] = $row[Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY];
// 			if ($totalQueries > 0)
// 				$rowColumn[Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY] =  $rowColumn[Piwik_SmartLoggent_API::INDEX_FR_QUERIES];
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
		$table->filter('ColumnCallbackAddColumnPercentage', array(Piwik_SmartLoggent_API::INDEX_FR_QUERIES, Piwik_SmartLoggent_API::INDEX_NB_QUERIES, $totalQueries, 2));
		$table->filter('ColumnCallbackAddColumnPercentage', array(Piwik_SmartLoggent_API::INDEX_FR_CLICKS, Piwik_SmartLoggent_API::INDEX_NB_CLICKS, $totalClicks, 2));
		$table->filter('ColumnCallbackAddColumnPercentage', array(Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY, 'queryeventwithclick', 'queryevents', 2));
		$table->filter('ColumnCallbackAddColumnQuotient', array(Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY, 'queryeventwithclick', $totalQueries, 2));
		$archiveProcessing->insertBlobRecord('SmartLoggent_'.$tableName, $table->getSerialized());
		destroy($table);
// 		Piwik::profileend($profiler);
	}
	
	
	
	static private $instance = null;
	
	private $sqlSegments = null;
	
	// these are the names of the table aliases used in the query that can be
	// used to segment.
	public static $SEGMENT_TABLES = array('language', 'linkphraselemma', 'naturalsearchphrase', 'category', 'normalizedsearchphrase', 'linkphraseclass', 'namedentity', 'linkphrasenamedentity', 'linkphrasecluster', 'cluster', 'queryevent');
	
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
		$dependencies['queryevent'] = array('DEP_queryevent');
		$dependencies['naturalsearchphrase'] = array('DEP_queryevent', 'DEP_naturalsearchphrase');
		$dependencies['normalizedsearchphrase'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase');
		$dependencies['language'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_normalizedsearchphrase', 'DEP_language');
		$dependencies['category'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphraseclass', 'DEP_category');
		$dependencies['linkphraseclass'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphraseclass');
		$dependencies['namedentity'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphrasenamedentity', 'DEP_namedentity');
		$dependencies['linkphrasenamedentity'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphrasenamedentity');
		$dependencies['linkphrasecluster'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphrasecluster');
		$dependencies['linkphraselemma'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphraselemma');
		$dependencies['cluster'] = array('DEP_queryevent', 'DEP_naturalsearchphrase', 'DEP_linkphrasecluster', 'DEP_cluster');
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
					'table' => 'TL_PhraseClassesClosure'
					,	'tableAlias' => 'linkphraseclass'
					,	'joinOn' => 'linkphraseclass.PC_NP_IdNormalizedSearchPhrase = naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_linkphraseclass'
			);
			self::$instance->sqlSegments['DEP_category'] = array
			(
					'table' => 'TB_Classes'
					,	'tableAlias' => 'category'
					,	'joinOn' => 'category.CL_IdClass = linkphraseclass.PC_CL_IdClass and category.CL_TA_IdTaxonomy = linkphraseclass.PC_CL_TA_IdTaxonomy'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_category'
			);
			self::$instance->sqlSegments['DEP_linkphrasenamedentity'] = array
			(
					'table' => 'TL_PhraseNamedEntities'
					,	'tableAlias' => 'linkphrasenamedentity'
					,	'joinOn' => 'linkphrasenamedentity.PE_NP_IdNormalizedSearchPhrase = naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_linkphrasenamedentity'
			);
			self::$instance->sqlSegments['DEP_namedentity'] = array
			(
					'table' => 'TD_NamedEntities'
					,	'tableAlias' => 'namedentity'
					,	'joinOn' => 'namedentity.NE_IdEntity = linkphrasenamedentity.PE_NE_IdEntity'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_namedentity'
			);
			self::$instance->sqlSegments['DEP_namedentitytype'] = array
			(
					'table' => 'TB_NamedEntityTypes'
					,	'tableAlias' => 'namedentitytype'
					,	'joinOn' => 'namedentitytype.NT_IdEntityType = namedentity.NE_NT_IdEntityType'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_namedentitytype'
			);
			self::$instance->sqlSegments['DEP_linkphrasecluster'] = array
			(
					'table' => 'TL_PhraseClusters'
					,	'tableAlias' => 'linkphrasecluster'
					,	'joinOn' => 'linkphrasecluster.PL_NP_IdNormalizedSearchPhrase = naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_linkphrasecluster'
			);
			self::$instance->sqlSegments['DEP_cluster'] = array
			(
					'table' => 'TD_Clusters'
					,	'tableAlias' => 'cluster'
					,	'joinOn' => 'cluster.CS_IdCluster = linkphrasecluster.PL_CS_IdCluster'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_cluster'
			);
			self::$instance->sqlSegments['DEP_linkphraselemma'] = array
			(
					'table' => 'TL_PhraseLemma'
					,	'tableAlias' => 'linkphraselemma'
					,	'joinOn' => 'linkphraselemma.PL_NP_IdNormalizedSearchPhrase = naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_linkphraselemma'
			);
			self::$instance->sqlSegments['DEP_lemma'] = array
			(
					'table' => 'TD_Lemma'
					,	'tableAlias' => 'lemma'
					,	'joinOn' => 'lemma.LM_IdLemma = linkphraselemma.PL_LM_IdLemma'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_lemma'
			);
			self::$instance->sqlSegments['DEP_language'] = array
			(
					'table' => 'TB_Languages'
					,	'tableAlias' => 'language'
					,	'joinOn' => 'language.LG_IdLanguage = normalizedsearchphrase.NP_LG_IdLanguage'
					,	'joinType' => 'inner join'
					, 'noprefix' => true
					, 'id' => 'DEP_language'
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
		if ($dimension === Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE)
		{
			$select .= "naturalsearchphrase.SP_NaturalText AS label";
			$select .= ", naturalsearchphrase.SP_IdNaturalSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$select .= "normalizedsearchphrase.NP_NormalizedText AS label";
			$select .= ", normalizedsearchphrase.NP_IdNormalizedSearchPhrase AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			$select .= "lemma.LM_LemmaString AS label";
			$select .= ", lemma.LM_IdLemma AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$select .= "category.CL_Descr AS label";
			$select .= ", category.CL_IdClass AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			$select .= "namedentitytype.NT_Descr AS label";
			$select .= ", namedentitytype.NT_IdEntityType AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			$select .= "namedentity.NE_EntityDescr AS label";
			$select .= ", namedentity.NE_IdEntity AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			$select .= "cluster.CS_Descr AS label";
			$select .= ", cluster.CS_IdCluster AS id";
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			$select .= "language.LG_Descr AS label";
			$select .= ", language.LG_IdLanguage AS id";
		}
		
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_QUERIES)
			$select .= ", count(distinct log_link_visit_action.idlink_va) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_VISITS)
			$select .= ", count(distinct log_visit.idvisit) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS)
			$select .= ", count(distinct log_visit.idvisitor) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_NB_CLICKS)
			$select .= ", sum(queryevent.QE_ClickCount) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_AVG_CLICKS)
			$select .= ", avg(queryevent.QE_ClickCount) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY)
			$select .= ", avg(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY)
			$select .= ", sum(case queryevent.QE_ClickCount when 0 then 0 else 1 end) as metric";
		if ($metric === Piwik_SmartLoggent_API::INDEX_AVG_RESULTS)
			$select .= ", avg(queryevent.QE_ResultCount) as metric";
		
		$from = array();
		$from[] = "log_link_visit_action";
		$from[] = "log_visit";
		if ($dimension === Piwik_SmartLoggent_API::DIM_NATURALSEARCHPHRASE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHPHRASE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_SEARCHWORD)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphraselemma');
			$from[] = self::getSqlSegmentFor('DEP_lemma');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLASS)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphraseclass');
			$from[] = self::getSqlSegmentFor('DEP_category');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITYTYPE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasenamedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentitytype');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_NAMEDENTITY)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasenamedentity');
			$from[] = self::getSqlSegmentFor('DEP_namedentity');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_CLUSTER)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_linkphrasecluster');
			$from[] = self::getSqlSegmentFor('DEP_cluster');
		}
		if ($dimension === Piwik_SmartLoggent_API::DIM_LANGUAGE)
		{
			$from[] = self::getSqlSegmentFor('DEP_queryevent');
			$from[] = self::getSqlSegmentFor('DEP_naturalsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_normalizedsearchphrase');
			$from[] = self::getSqlSegmentFor('DEP_language');
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
// 		Piwik::smartlog($query['sql']);
// 		foreach ($query['bind'] as $key => $value)
// 			Piwik::smartlog("$key: $value");
		$result = Zend_Registry::get('db')->query($query['sql'].' limit 0, '.$limit, $query['bind']);
// 		Piwik::profileend($profiler);
		return $result;
	}
}
