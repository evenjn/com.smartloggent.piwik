<?php

abstract class Piwik_SmartLoggent_Core_ArchiveProcessing
{
	static function factory($name)
	{
		switch($name)
		{
			case 'day':
				$process = new Piwik_SmartLoggent_Core_ArchiveProcessing_Day();		
				$process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_day'];
			break;
			
			case 'week':
			case 'month':
			case 'year':
				$process = new Piwik_SmartLoggent_Core_ArchiveProcessing_Period();
				$process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_period'];
			break;
			
			case 'range':
				$process = new Piwik_SmartLoggent_Core_ArchiveProcessing_Period();
				$process->debugAlwaysArchive = Piwik_Config::getInstance()->Debug['always_archive_data_range'];
			break;
			
			default:
				throw new Exception("Unknown Archiving period specified '$name'");
			break;
		}
		return $process;
	}
}
