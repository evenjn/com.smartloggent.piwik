<?php

class Piwik_SmartLoggent_Core_Archive_Single extends Piwik_Archive_Single
{
	public function prepareArchive()
	{
		$archiveJustProcessed = false;

		
		$periodString = $this->period->getLabel();
		$plugin = Piwik_ArchiveProcessing::getPluginBeingProcessed($this->getRequestedReport());
		
		$cacheKey = 'all';
		if($periodString == 'range')
		{
			$cacheKey = $plugin;
		}
		if(!isset($this->alreadyChecked[$cacheKey]))
		{
			$this->isThereSomeVisits = false;
			$this->alreadyChecked[$cacheKey] = true;
			$dayString = $this->period->getPrettyString();
			$logMessage = "Preparing archive: " . $periodString . "(" . $dayString . "), plugin $plugin ";
			// if the END of the period is BEFORE the website creation date
			// we already know there are no stats for this period
			// we add one day to make sure we don't miss the day of the website creation
			if( $this->period->getDateEnd()->addDay(2)->isEarlier( $this->site->getCreationDate() ) )
			{
				Piwik::log("$logMessage skipped, archive is before the website was created.");
				return;
			}
			
			// if the starting date is in the future we know there is no visit
			if( $this->period->getDateStart()->subDay(2)->isLater( Piwik_Date::today() ) )
			{
				Piwik::log("$logMessage skipped, archive is after today.");
				return;
			}
			
			// we make sure the archive is available for the given date
			$periodLabel = $this->period->getLabel();
			$this->archiveProcessing = Piwik_SmartLoggent_Core_ArchiveProcessing::factory($periodLabel);
			$this->archiveProcessing->setSite($this->site);
			$this->archiveProcessing->setPeriod($this->period);
			$this->archiveProcessing->setSegment($this->segment);
			
			
			$this->archiveProcessing->init();
			
			$this->archiveProcessing->setRequestedReport( $this->getRequestedReport() );
		
			$archivingDisabledArchiveNotProcessed = false;
			
			$idArchive = $this->archiveProcessing->loadArchive();
			
			if(empty($idArchive))
			{
				if($this->archiveProcessing->isArchivingDisabled())
				{
					$archivingDisabledArchiveNotProcessed = true;
					$logMessage = "* ARCHIVING DISABLED, for $logMessage";
				}
				else
				{
					Piwik::log("* PROCESSING $logMessage, not archived yet...");
					$archiveJustProcessed = true;

					// Process the reports
			
					$this->archiveProcessing->launchArchiving();
			

					$idArchive = $this->archiveProcessing->getIdArchive();
					$logMessage = "PROCESSED: idArchive = ".$idArchive.", for $logMessage";
				}
			}
			else
			{
				
			
				$logMessage = "* ALREADY PROCESSED, Fetching idArchive = $idArchive (idSite=".$this->site->getId()."), for $logMessage";
			}
			
			
			Piwik::log("$logMessage, Visits = ". $this->archiveProcessing->getNumberOfVisits());
			$this->isThereSomeVisits = !$archivingDisabledArchiveNotProcessed
										&& $this->archiveProcessing->isThereSomeVisits();
			$this->idArchive = $idArchive;
		}
		return $archiveJustProcessed;
	}
}
