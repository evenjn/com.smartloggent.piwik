<?php

class Piwik_SmartLoggent_Core_ArchiveProcessing_Period extends Piwik_ArchiveProcessing_Period
{
	/**
	 * Returns the ID of the archived subperiods.
	 * 
	 * @return array  Array of the idArchive of the subperiods
	 */
	protected function loadSubperiodsArchive()
	{
		//$profiler = Piwik::profilestart('Piwik_SmartLoggent_Core_ArchiveProcessing_Period::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		$periods = array();
		
		// we first compute every subperiod of the archive
		foreach($this->period->getSubperiods() as $period)
		{
			$archivePeriod = new Piwik_SmartLoggent_Core_Archive_Single();
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			$archivePeriod->setSegment( $this->getSegment() );
			$archivePeriod->setRequestedReport($this->getRequestedReport());
			
			$periods[] = $archivePeriod;
		}
		//Piwik::profileend($profiler);
		return $periods;
	}
	
	/**
	 *
	 * @see Piwik_ArchiveProcessing_Day::isThereSomeVisits()
	 * @return bool|null
	 */
	public function isThereSomeVisits()
	{
		if(!is_null($this->isThereSomeVisits))
		{
			return $this->isThereSomeVisits;
		}
		
		$this->loadSubPeriods();
		if(self::getPluginBeingProcessed($this->getRequestedReport()) == 'VisitsSummary'
			|| $this->shouldProcessReportsAllPlugins($this->getSegment(), $this->period)
		)
		{
			$toSum = self::getCoreMetrics();
			$record = $this->archiveNumericValuesSum($toSum);
			$this->archiveNumericValuesMax( 'max_actions' ); 
	
			$nbVisitsConverted = $record['nb_visits_converted'];
			$nbVisits = $record['nb_visits'];
		}
		else
		{
			$archive = new Piwik_SmartLoggent_Core_Archive_Single();
			$archive->setSite( $this->site );
			$archive->setPeriod( $this->period );
			$archive->setSegment( $this->getSegment() );
			
			$nbVisits = $archive->getNumeric('nb_visits');
			$nbVisitsConverted = 0;
			if($nbVisits > 0)
			{
				$nbVisitsConverted = $archive->getNumeric('nb_visits_converted');
			}
		}
		
		$this->setNumberOfVisits($nbVisits);
		$this->setNumberOfVisitsConverted($nbVisitsConverted);
		$this->isThereSomeVisits = ($nbVisits > 0);
		return $this->isThereSomeVisits;
	}
}