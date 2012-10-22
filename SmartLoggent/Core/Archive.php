<?php

abstract class Piwik_SmartLoggent_Core_Archive
{
	/**
	 * Builds an Archive object or returns the same archive if previously built.
	 *
	 * @param int|string         $idSite                 integer, or comma separated list of integer
	 * @param string             $period                 'week' 'day' etc.
	 * @param Piwik_Date|string  $strDate                'YYYY-MM-DD' or magic keywords 'today' @see Piwik_Date::factory()
	 * @param bool|string        $segment                Segment definition - defaults to false for Backward Compatibility
	 * @param bool|string        $_restrictSitesToLogin  Used only when running as a scheduled task
	 * @return Piwik_Archive
	 */
	static public function build($idSite, $period, $strDate, $segment = false, $_restrictSitesToLogin = false )
	{
		if($idSite === 'all')
		{
			$sites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess($_restrictSitesToLogin);
		}
		else
		{
			$sites = Piwik_Site::getIdSitesFromIdSitesString($idSite);
		}
		
		if (!($segment instanceof Piwik_Segment))
		{
			$segment = new Piwik_SmartLoggent_Core_Segment($segment, $idSite);
		}
		
		// idSite=1,3 or idSite=all
		if( count($sites) > 1 
			|| $idSite === 'all' )
		{
			$archive = new Piwik_SmartLoggent_Core_Archive_Array_IndexedBySite($sites, $period, $strDate, $segment, $_restrictSitesToLogin);
		}
		// if a period date string is detected: either 'last30', 'previous10' or 'YYYY-MM-DD,YYYY-MM-DD'
		elseif(is_string($strDate) && Piwik_Archive::isMultiplePeriod($strDate, $period))
		{
			$oSite = new Piwik_Site($idSite);
			$archive = new Piwik_SmartLoggent_Core_Archive_Array_IndexedByDate($oSite, $period, $strDate, $segment);
		}
		// case we request a single archive
		else
		{
			$oSite = new Piwik_Site($idSite);
			$oPeriod = Piwik_Archive::makePeriodFromQueryParams($oSite, $period, $strDate);
			$archive = new Piwik_SmartLoggent_Core_Archive_Single();
			$archive->setPeriod($oPeriod);
			$archive->setSite($oSite);
			$archive->setSegment($segment);
		}
		return $archive;
	}

	/**
	 * Helper - Loads a DataTable from the Archive.
	 * Optionally loads the table recursively,
	 * or optionally fetches a given subtable with $idSubtable
	 *
	 * @param string      $name
	 * @param int         $idSite
	 * @param string      $period
	 * @param Piwik_Date  $date
	 * @param string      $segment
	 * @param bool        $expanded
	 * @param null        $idSubtable
	 * @return Piwik_DataTable
	 */
	static public function getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable = null )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_SmartLoggent_Core_Archive::build($idSite, $period, $date, $segment );
		if($idSubtable === false)
		{
			$idSubtable = null;
		}
		
		if($expanded)
		{
			$dataTable = $archive->getDataTableExpanded($name, $idSubtable);
		}
		else
		{
			$dataTable = $archive->getDataTable($name, $idSubtable);
		}
		
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		
		return $dataTable;
	}

}
