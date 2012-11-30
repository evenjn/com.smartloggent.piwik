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
		,	'segment' => 'SLLanguage'
		,	'sqlSegment' => 'todo_segment_SLLanguage'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_SearchPhrase')
		,	'segment' => 'SLSearchPhrase'
		,	'sqlSegment' => 'normalizedsearchphrase.NP_IdNormalizedSearchPhrase'
		,	'acceptedValues' => "Any string."
		);
		$segments[] = array
		(
		'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Classification')
		,	'segment' => 'SLClassification'
		,	'sqlSegment' => 'todo_segment_SLClassification'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => 'SLClass'
		,	'sqlSegment' => 'ancestor.CA_Ancestor_IdClass'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => 'SLBottomClass'
		,	'sqlSegment' => 'ancestor.CA_Self'
		,	'acceptedValues' => "true"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => 'SLDirectSubClass'
		,	'sqlSegment' => 'supercategory.CA_Direct'
		,	'acceptedValues' => "true"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Class')
		,	'segment' => 'SLTopClass'
		,	'sqlSegment' => 'supercategory.CA_AncestorIsTop'
		,	'acceptedValues' => "true"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_SuperClass')
		,	'segment' => 'SLSuperClass'
		,	'sqlSegment' => 'supercategory.CA_Ancestor_IdClass'
		,	'acceptedValues' => "the id of a specific class"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_ClusterAnalysis')
		,	'segment' => 'SLClusterAnalysis'
		,	'sqlSegment' => 'todo_segment_SLClusterAnalysis'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_Cluster')
		,	'segment' => 'SLCluster'
		,	'sqlSegment' => 'todo_segment_SLCluster'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_NamedEntityType')
		,	'segment' => 'SLNamedEntityType'
		,	'sqlSegment' => 'todo_segment_SLNamedEntityType'
		,	'acceptedValues' => "to do"
		);
		$segments[] = array
		(
			'type' => 'dimension'
		,	'category' => 'Visit'
		,	'name' => Piwik_Translate('LOC_SL_Segment_NamedEntity')
		,	'segment' => 'SLNamedEntity'
		,	'sqlSegment' => 'todo_segment_SLNamedEntity'
		,	'acceptedValues' => "to do"
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
		Piwik_AddMenu('LOC_SL_Menu', '', array('module' => 'SmartLoggent', 'action' => 'searchPhraseOverview'), true , 30);
		$subMenus = array
		(
			'LOC_SL_SubmenuOverview' => 'Overview',	
			'LOC_SL_SubmenuSearchPhrases' => 'searchPhrase',
			'LOC_SL_SubmenuClasses' => 'classes',
			'LOC_SL_SubmenuClustering' => 'clustering',
			'LOC_SL_SubmenuNamedEntities' => 'namedEntities',
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
		Piwik_SmartLoggent_SQL::archiveDay($archiveProcessing);
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
		Piwik_SmartLoggent_SQL::archivePeriod($archiveProcessing);
// 		Piwik::profileend($profiler);
	}
	
	public function logResults($notification)
	{
		// TODO
	}
	
}
