<?php
$translations = array(
		'LOC_SL_PluginDescription' => 'This plugin analyzes the internal search of the websites.'
		
	// menus
	,	'LOC_SL_Menu' => 'SmartLoggent'
	,	'LOC_SL_SubmenuOverview' => 'Overview'
	,	'LOC_SL_SubmenuSearchPhrases' => 'Search Phrases'
	,	'LOC_SL_SubmenuSingleSearchPhrases' => 'Single Search Phrases'
	,	'LOC_SL_SubmenuClasses' => 'Classes'
	,	'LOC_SL_SubmenuSingleClasses' => 'Single Classes'
	,	'LOC_SL_SubmenuClustering' => 'Clustering'
	,	'LOC_SL_SubmenuNamedEntities' => 'Named Entities'
	,	'LOC_SL_SubmenuNamedEntityTypes' => 'Named Entites Types'
	,	'LOC_SL_SubmenuSearchWords' => 'Search Words'													
	,	'LOC_SL_SubmenuLanguages' => 'Languages'
		
	// segments
	,	'LOC_SL_Segment_Language' => 'Language'
	,	'LOC_SL_Segment_SearchPhrase' => 'Search Phrase'
	,	'LOC_SL_Segment_SearchWord' => 'Search Word'
	,	'LOC_SL_Segment_Classification' => 'Classification'
	,	'LOC_SL_Segment_Class' => 'Class'
	,	'LOC_SL_Segment_SuperClass' => 'Superclass'
	,	'LOC_SL_Segment_ClusterAnalysis' => 'Cluster Analysis'
	,	'LOC_SL_Segment_Cluster' => 'Cluster'
	,	'LOC_SL_Segment_NamedEntityType' => 'Named Entity Type'
	,	'LOC_SL_Segment_NamedEntity' => 'Named Entity'
	, 'LOC_SL_Segment_NaturalSearchPhrase' => 'Natural Search Phrase'

	// reports metadata
	,	'LOC_SL_ReportMetadata_SearchPhrase_Category' => 'VALUE_LOC_SL_ReportMetadata_SearchPhrase_Category'
	,	'LOC_SL_ReportMetadata_SearchPhrase_Name' => 'VALUE_LOC_SL_ReportMetadata_SearchPhrase_Name'
	,	'LOC_SL_ReportMetadata_SearchPhrase_Dimension' => 'VALUE_LOC_SL_ReportMetadata_SearchPhrase_Dimension'
	,	'LOC_SL_ReportMetadata_SearchPhrase_Documentation' => 'VALUE_LOC_SL_ReportMetadata_SearchPhrase_Documentation'
	,	'LOC_SL_ReportMetadata_Class_Category' => 'VALUE_LOC_SL_ReportMetadata_Class_Category'
	,	'LOC_SL_ReportMetadata_Class_Name' => 'VALUE_LOC_SL_ReportMetadata_Class_Name'
	,	'LOC_SL_ReportMetadata_Class_Dimension' => 'VALUE_LOC_SL_ReportMetadata_Class_Dimension'
	,	'LOC_SL_ReportMetadata_Class_Documentation' => 'VALUE_LOC_SL_ReportMetadata_Class_Documentation'
	
	// columns
	,	'LOC_SL_Column_Label_Class' => 'Class'
	,	'LOC_SL_Column_Label_SearchPhrase' => 'Search Phrase'
	,	'LOC_SL_Column_Label_Language' => 'Language'
	,	'LOC_SL_Column_Label_SearchWord' => 'Search Word'
	,	'LOC_SL_Column_Label_NaturalSearchPhrase' => 'Natural Search Phrase'
	,	'LOC_SL_Column_Label_NamedEntityType' => 'Named Entity Type'
	,	'LOC_SL_Column_Label_NamedEntity' => 'Named Entity'
	,	'LOC_SL_Column_Label_Cluster' => 'Cluster'
	,	'LOC_SL_Column_AVG_CLICKS' => 'Avg. clicks'
	,	'LOC_SL_Column_AVG_RESULTS' => 'Avg. results'
	,	'LOC_SL_Column_CLICK_PROBABILITY' => 'Click prob.'
	,	'LOC_SL_Column_FR_CLICKS' => 'Clicks %'
	,	'LOC_SL_Column_FR_QUERIES' => 'Queries %'
	,	'LOC_SL_Column_NB_CLICKS' => 'Clicks'
	,	'LOC_SL_Column_NB_QUERIES' => 'Queries'
	,	'LOC_SL_Column_WEIGHTED_CLICK_PROBABILITY' => 'QC-index'
		
	// top N charts: languages
	, 'LOC_SL_Chart_Language_by_1' => 'Languages with the most visitors'
	, 'LOC_SL_Chart_Language_by_2' => 'Languages with the most visits'
	, 'LOC_SL_Chart_Language_by_57' => 'Languages with the most queries'
	, 'LOC_SL_Chart_Language_by_58' => 'Languages with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_Language_by_59' => 'Languages with the highest number of results per query'
	, 'LOC_SL_Chart_Language_by_60' => 'Languages with the most clicks'
	, 'LOC_SL_Chart_Language_by_61' => 'Languages with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_Language_by_62' => 'Languages with the highest number of clicks per query'
	, 'LOC_SL_Chart_Language_by_63' => 'Languages with the highest click probability'
	, 'LOC_SL_Chart_Language_by_64' => 'Languages with the highest QC-index'
	
	// top N charts: search phrases
	, 'LOC_SL_Chart_SearchPhrase_by_1' => 'Search phrases with the most visitors'
	, 'LOC_SL_Chart_SearchPhrase_by_2' => 'Search phrases with the most visits'
	, 'LOC_SL_Chart_SearchPhrase_by_57' => 'Search phrases with the most queries'
	, 'LOC_SL_Chart_SearchPhrase_by_58' => 'Search phrases with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_SearchPhrase_by_59' => 'Search phrases with the highest number of results per query'
	, 'LOC_SL_Chart_SearchPhrase_by_60' => 'Search phrases with the most clicks'
	, 'LOC_SL_Chart_SearchPhrase_by_61' => 'Search phrases with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_SearchPhrase_by_62' => 'Search phrases with the highest number of clicks per query'
	, 'LOC_SL_Chart_SearchPhrase_by_63' => 'Search phrases with highest click probability'
	, 'LOC_SL_Chart_SearchPhrase_by_64' => 'Search phrases with highest QC-index'
		
	// top N charts: search words
	, 'LOC_SL_Chart_SearchWord_by_1' => 'Search words with the most visitors'
	, 'LOC_SL_Chart_SearchWord_by_2' => 'Search words with the most visits'
	, 'LOC_SL_Chart_SearchWord_by_57' => 'Search words with the most queries'
	, 'LOC_SL_Chart_SearchWord_by_58' => 'Search words with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_SearchWord_by_59' => 'Search words with the highest number of results per query'
	, 'LOC_SL_Chart_SearchWord_by_60' => 'Search words with the most clicks'
	, 'LOC_SL_Chart_SearchWord_by_61' => 'Search words with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_SearchWord_by_62' => 'Search words with the highest number of clicks per query'
	, 'LOC_SL_Chart_SearchWord_by_63' => 'Search words with the highest click probability'
	, 'LOC_SL_Chart_SearchWord_by_64' => 'Search words with the highest QC-index'
	
	// top N charts: classes
	, 'LOC_SL_Chart_Class_by_1' => 'Classes with the most visitors'
	, 'LOC_SL_Chart_Class_by_2' => 'Classes with the most visits'
	, 'LOC_SL_Chart_Class_by_57' => 'Classes with the most queries'
	, 'LOC_SL_Chart_Class_by_58' => 'Classes with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_Class_by_59' => 'Classes with the highest number of results per query'
	, 'LOC_SL_Chart_Class_by_60' => 'Classes with the most clicks'
	, 'LOC_SL_Chart_Class_by_61' => 'Classes with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_Class_by_62' => 'Classes with the highest number of clicks per query'
	, 'LOC_SL_Chart_Class_by_63' => 'Classes with the highest click probability'
	, 'LOC_SL_Chart_Class_by_64' => 'Classes with the highest QC-index'
	
	// top N charts: named entities
	, 'LOC_SL_Chart_NamedEntity_by_1' => 'Named entities with the most visitors'
	, 'LOC_SL_Chart_NamedEntity_by_2' => 'Named entities with the most visits'
	, 'LOC_SL_Chart_NamedEntity_by_57' => 'Named entities with the most queries'
	, 'LOC_SL_Chart_NamedEntity_by_58' => 'Named entities with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_NamedEntity_by_59' => 'Named entities with the highest number of results per query'
	, 'LOC_SL_Chart_NamedEntity_by_60' => 'Named entities with the most clicks'
	, 'LOC_SL_Chart_NamedEntity_by_61' => 'Named entities with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_NamedEntity_by_62' => 'Named entities with the highest number of clicks per query'
	, 'LOC_SL_Chart_NamedEntity_by_63' => 'Named entities with the highest click probability'
	, 'LOC_SL_Chart_NamedEntity_by_64' => 'Named entities with the highest QC-index'
	
	// top N charts: named entity types
	, 'LOC_SL_Chart_NamedEntityType_by_1' => 'Types of named entity with the most visitors'
	, 'LOC_SL_Chart_NamedEntityType_by_2' => 'Types of named entity with the most visits'
	, 'LOC_SL_Chart_NamedEntityType_by_57' => 'Types of named entity with the most queries'
	, 'LOC_SL_Chart_NamedEntityType_by_58' => 'Types of named entity with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_NamedEntityType_by_59' => 'Types of named entity with the highest number of results per query'
	, 'LOC_SL_Chart_NamedEntityType_by_60' => 'Types of named entity with the most clicks'
	, 'LOC_SL_Chart_NamedEntityType_by_61' => 'Types of named entity with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_NamedEntityType_by_62' => 'Types of named entity with the highest number of clicks per query'
	, 'LOC_SL_Chart_NamedEntityType_by_63' => 'Types of named entity with the highest click probability'
	, 'LOC_SL_Chart_NamedEntityType_by_64' => 'Types of named entity with the highest QC-index'
	
	// top N charts: clusters
	, 'LOC_SL_Chart_Cluster_by_1' => 'Clusters with the most visitors'
	, 'LOC_SL_Chart_Cluster_by_2' => 'Clusters with the most visits'
	, 'LOC_SL_Chart_Cluster_by_57' => 'Clusters with the most queries'
	, 'LOC_SL_Chart_Cluster_by_58' => 'Clusters with the most queries %' // this of chart is redundant
	, 'LOC_SL_Chart_Cluster_by_59' => 'Clusters with the highest number of results per query'
	, 'LOC_SL_Chart_Cluster_by_60' => 'Clusters with the most clicks'
	, 'LOC_SL_Chart_Cluster_by_61' => 'Clusters with the most clicks %' // this of chart is redundant
	, 'LOC_SL_Chart_Cluster_by_62' => 'Clusters with the highest number of clicks per query'
	, 'LOC_SL_Chart_Cluster_by_63' => 'Clusters with the highest click probability'
	, 'LOC_SL_Chart_Cluster_by_64' => 'Clusters with the highest QC-index'
	
	// page Search Phrase Overview
	,	'LOC_SL_SearchPhraseOverviewPageTitle' => 'Overview of Search Phrases'
	,	'LOC_SL_SearchPhraseOverviewTableDescription' => 'This table shows an overview of Search Phrases.' 

	// page Class Overview
	,	'LOC_SL_OverviewPageTitle' => 'Overview'
	,	'LOC_SL_OverviewDescription' => 'SmartLoggent Overview'		
	,	'LOC_SL_ClassOverviewPageTitle' => 'Overview of Classes'
	,	'LOC_SL_ClassOverviewTableDescription' => 'This table shows an overview of Classes.'
	, 'LOC_SL_classEvolution_ReportDocumentation' => 'VALUE_LOC_SL_classEvolution_ReportDocumentation' 
	
	, 'LOC_SL_TableNoData' => 'There is no data for this report.'
		
	, 'LOC_SL_xxx' => 'VALUE_LOC_SL_xxx'
);

?>
