<?php

/**
 * Piwik - Open source web analytics
 * SmartLoggent Plugin
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @author {Dmitriy Ofman, Marco Trevisan} @ Celi SRL
 * 
 */

class Piwik_SmartLoggent extends Piwik_Plugin
{
	
	public function getInformation()
	{
		return array
		(
			'description' => Piwik_Translate('LOC_SL_PluginDescription')
		,	'author' => 'Celi SRL'
		,	'author_homepage' => 'http://www.smartloggent.com/'
		,	'version' => '0.0.1'
		,	'translationAvailable' => true
		,	'TrackerPlugin' => true
		);
	}
	
	public function install()
	{
		// TODO
	}

	public function uninstall()
	{
		// TODO
	}

	/** Register Hooks */
	public function getListHooksRegistered()
	{
		return array
		(
			'API.getSegmentsMetadata' => 'getSegmentsMetadata'
		,	'API.getReportMetadata' => 'getReportMetadata'
		,	'ArchiveProcessing_Day.compute' => 'archiveDay'
		,	'ArchiveProcessing_Period.compute' => 'archivePeriod'
		,	'AssetManager.getJsFiles' => 'getJsFiles'
		,	'AssetManager.getCssFiles' => 'getCssFiles'
		,	'Menu.add' => 'addMenu'
		,	'Tracker.Action.record' => 'logResults'
		,	'WidgetsList.add' => 'addWidgets'
		);
	}
	
	public function getReportMetadata($notification)
	{
		$reports = &$notification->getNotificationObject();
		$reports[] = array
		(
			'category'  => Piwik_Translate('VALUE_LOC_SL_ReportMetadata_SearchPhrase_Category')
		,	'name'   => Piwik_Translate('VALUE_LOC_SL_ReportMetadata_SearchPhrase_Name')
		,	'module' => 'SmartLoggent'
		,	'action' => 'getSearchPhrase'
		,	'dimension' => Piwik_Translate('VALUE_LOC_SL_ReportMetadata_SearchPhrase_Dimension')
		,	'documentation' => Piwik_Translate('VALUE_LOC_SL_ReportMetadata_SearchPhrase_Documentation')
			/* I have no idea what is the purpose of 'order' */
		,	'order' => 42
		);
		$reports[] = array
		(
			'category'  => Piwik_Translate('LOC_SL_ReportMetadata_Class_Category')
		,	'name'   => Piwik_Translate('LOC_SL_ReportMetadata_Class_Name')
		,	'module' => 'SmartLoggent'
		,	'action' => 'getClass'
		,	'dimension' => Piwik_Translate('LOC_SL_ReportMetadata_Class_Dimension')
		,	'documentation' => Piwik_Translate('LOC_SL_ReportMetadata_Class_Documentation')
			/* I have no idea what is the purpose of 'order' */
		,	'order' => 43
		);
	}
	
	/**
	 * @param Piwik_Event_Notification $notification
	 * The array of segments.
	 */
	public function getSegmentsMetadata($notification)
	{
		$segments =& $notification->getNotificationObject();
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Language')
		,	'segment' => Piwik_SmartLoggent_API::SEG_LANGUAGE
		,	'sqlSegment' => 'language.LG_IdLanguage'
		,	'acceptedValues' => "the id of a specific language like eng, fra, ita"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_SearchPhrase')
		,	'segment' => Piwik_SmartLoggent_API::SEG_SEARCHPHRASE
		,	'sqlSegment' => 'naturalsearchphrase.SP_NP_IdNormalizedSearchPhrase'
		,	'acceptedValues' => "the id of a specific normalized search phrase"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_SearchWord')
		,	'segment' => Piwik_SmartLoggent_API::SEG_SEARCHWORD
		,	'sqlSegment' => 'linkphraselemma.PL_LM_IdLemma'
		,	'acceptedValues' => "the id of a specific search word"
		);
		$segments[] = array
		(
				'type' => 'dimension'
				,	'category' => 'Visit'
				,	'name' => Piwik_Translate('LOC_SL_Segment_NaturalSearchPhrase')
				,	'segment' => Piwik_SmartLoggent_API::SEG_NATURALSEARCHPHRASE
				,	'sqlSegment' => 'queryevent.QE_SP_IdNaturalSearchPhrase'
				,	'acceptedValues' => "the id of a specific natural search phrase"
		);
		$segments[] = array
		(
		'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Classification')
		,	'segment' => Piwik_SmartLoggent_API::SEG_CLASSIFICATION
		,	'sqlSegment' => 'linkphraseclass.PC_CL_TA_IdTaxonomy'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => Piwik_SmartLoggent_API::SEG_CLASS
		,	'sqlSegment' => 'linkphraseclass.PC_CL_IdClass'
		,	'acceptedValues' => "the id of a specific class"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => Piwik_SmartLoggent_API::SEG_TOPCLASS
		,	'sqlSegment' => 'category.CL_parentIsTop'
		,	'acceptedValues' => "1"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_SuperClass')
		,	'segment' => Piwik_SmartLoggent_API::SEG_SUPERCLASS
		,	'sqlSegment' => 'category.CL_IdClass_Parent'
		,	'acceptedValues' => "the id of a specific class"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_ClusterAnalysis')
		,	'segment' => Piwik_SmartLoggent_API::SEG_CLUSTERANALYSIS
		,	'sqlSegment' => 'cluster.CS_SE_IdClusteringSession'
		,	'acceptedValues' => "the id of a specific cluster analysis/session"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Cluster')
		,	'segment' => Piwik_SmartLoggent_API::SEG_CLUSTER
		,	'sqlSegment' => 'linkphrasecluster.PL_CS_IdCluster'
		,	'acceptedValues' => "the id of a specific cluster"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_NamedEntityType')
		,	'segment' => Piwik_SmartLoggent_API::SEG_NAMEDENTITYTYPE
		,	'sqlSegment' => 'namedentity.NE_NT_IdEntityType'
		,	'acceptedValues' => "the id of a specific named entity type"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_NamedEntity')
		,	'segment' => Piwik_SmartLoggent_API::SEG_NAMEDENTITY
		,	'sqlSegment' => 'linkphrasenamedentity.PE_NE_IdEntity'
		,	'acceptedValues' => "the id of a specific named entity"
		);
	}
	
	public function getJsFiles($notification)
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = 'plugins/SmartLoggent/templates/sl.js';
	}
	
	public function getCssFiles($notification)
	{
		$cssFiles = &$notification->getNotificationObject();
		$cssFiles[] = 'plugins/SmartLoggent/templates/sl.css';
	}
	
	public function addMenu()
	{
		Piwik_AddMenu('LOC_SL_Menu', '', array('module' => 'SmartLoggent', 'action' => 'overview'), true , 30);
		$subMenus = array
		(
			'LOC_SL_SubmenuOverview' => 'Overview',	
			'LOC_SL_SubmenuSearchPhrases' => 'searchPhrase',
			'LOC_SL_SubmenuClasses' => 'classes',
			'LOC_SL_SubmenuClustering' => 'clustering',
			'LOC_SL_SubmenuNamedEntityTypes' => 'namedEntityType',
			'LOC_SL_SubmenuSearchWords' => 'searchWords',
			'LOC_SL_SubmenuLanguages' => 'languages',
		);
		$order = 1;
		foreach($subMenus as $subMenu => $action) 
		{
			Piwik_AddMenu('LOC_SL_Menu', $subMenu, array('module' => 'SmartLoggent', 'action' => $action), true, $order++);
		}
	}
	
	public function addWidgets()
	{
		// TODO publish widgets for the dashboard
	}
	
	public function archiveDay($notification)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$archiveProcessing = $notification->getNotificationObject();
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName()))
		{
// 			Piwik::profileend($profiler);
			return;
		}
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		Piwik_SmartLoggent_SQL::archiveAll($archiveProcessing);
// 		Piwik::profileend($profiler);
		
	}
	
	public function archivePeriod($notification)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$archiveProcessing = $notification->getNotificationObject();
		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName()))
		{
// 			Piwik::profileend($profiler);
			return;
		}
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		Piwik_SmartLoggent_SQL::archiveAll($archiveProcessing);
// 		Piwik::profileend($profiler);
	}
	
	public function logResults($notification)
	{
		// TODO
	}
	
}
