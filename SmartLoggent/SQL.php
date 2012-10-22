<?php

/**
 * Piwik - Open source web analytics
 * SL Plugin
 * Archive
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @author Timo Besenreuther, EZdesign.de
 *
 * @category Piwik_Plugins
 * @package Piwik_SL
 */

class Piwik_SmartLoggent_SQL
{
	private static $languages = array (Piwik_SmartLoggent_API::DIM_LANGUAGE, "ja", "it", "de");
	private static $searchPhrases = array (Piwik_SmartLoggent_API::DIM_SEARCHPHRASE, "gatto", "bulldog", "ウサギ");
	private static $searchWords = array (Piwik_SmartLoggent_API::DIM_SEARCHWORD, "gatto", "bulldog", "ウサギ");
	private static $classes = array (Piwik_SmartLoggent_API::DIM_CLASS, "Felidae", "Canidae", "ウサギ科");
	
	public static function archiveDay(Piwik_ArchiveProcessing_Day $archive)
	{
		self::archiveDayFakeData($archive, self::$languages);
		self::archiveDayFakeData($archive, self::$searchPhrases);
		self::archiveDayFakeData($archive, self::$classes);
		self::archiveDayFakeData($archive, self::$searchWords);
	}
	
	public static function archivePeriod(Piwik_ArchiveProcessing_Period $archive)
	{
		$archive->archiveDataTable(array('SmartLoggent_'.self::$languages[0]));
		$archive->archiveDataTable(array('SmartLoggent_'.self::$searchPhrases[0]));
		$archive->archiveDataTable(array('SmartLoggent_'.self::$classes[0]));
		$archive->archiveDataTable(array('SmartLoggent_'.self::$searchWords[0]));
	}
	
	public static function archiveDayFakeData(Piwik_ArchiveProcessing_Day $archive, $fake)
	{
		$segmentString = $archive->getSegment()->getString();
		// decode segment
		$matches = array();
		// string
		$pattern = '/(.*)SL(.*)==([^;]+)(.*)/';
		// int
		$itmatches = preg_match($pattern, $segmentString, $matches);
// 			Piwik::log("archiveDayFakeData matches = $itmatches");
		if ($itmatches == 1)
		{
			
			$before = $matches[1];
			
			$dimension = $matches[2];
			$thing = $matches[2];
			
			$values = $matches[3];
			$tail = $matches[4];
			$values_split = explode(',', $values);
			$segmentString = $before.'SL'.$dimension.'==';
					$first = true;
			foreach ($values_split as $top )
			{
				$replaced = str_replace('=', '_', $top);
				$decoded = base64_decode($replaced);
				if($first)
					$segmentString .= $decoded;
				else
					$segmentString .= ','.$decoded;					
			}
			$segmentString .= $tail;
		}
		$a = $segmentString;
// 			Piwik::log("archiveDayFakeData decoded segment as $segmentString");
		$table = new Piwik_DataTable();
		for ($i = 1; $i < 4; $i++)
		{
			$legal = false;
			if (strpos($a, 'SLLanguage') !== false)
			{
				// language is selected
				if (strpos($a, 'SLLanguage==it') !== false)
				{
					$legal = true;
					if ($i == 3)
						continue;
				}
				if (strpos($a, 'SLLanguage==ja') !== false)
				{
					$legal = true;
					if ($i == 1)
						continue;
				}
				if (strpos($a, 'SLLanguage==de') !== false)
				{
					continue;
				}
				if (! $legal)
					continue;
			}
			if (strpos($a, 'SLClass') !== false)
			{
				// language is selected
				if (strpos($a, 'SLClass==Felidae') !== false)
				{
					$legal = true;
					if ($i != 1)
						continue;
				}
				if (strpos($a, 'SLClass==Canidae') !== false)
				{
					$legal = true;
					if ($i != 2)
						continue;
				}
				if (strpos($a, 'SLClass==ウサギ科') !== false)
				{
					$legal = true;
					if ($i != 3)
						continue;
				}
				if (! $legal)
					continue;
			}
			if (strpos($a, 'SLSearchWord') !== false)
			{
				// language is selected
				if (strpos($a, 'SLSearchWord==gatto') !== false)
				{
					$legal = true;
					if ($i != 1)
						continue;
				}
				if (strpos($a, 'SLSearchWord==bulldog') !== false)
				{
					$legal = true;
					if ($i != 2)
						continue;
				}
				if (strpos($a, 'SLSearchWord==ウサギ') !== false)
				{
					$legal = true;
					if ($i != 3)
						continue;
				}
				if (! $legal)
					continue;
			}
			if (strpos($a, 'SLSearchPhrase') !== false)
			{
				// language is selected
				if (strpos($a, 'SLSearchPhrase==gatto') !== false)
				{
					$legal = true;
					if ($i != 1)
						continue;
				}
				if (strpos($a, 'SLSearchPhrase==bulldog') !== false)
				{
					$legal = true;
					if ($i != 2)
						continue;
				}
				if (strpos($a, 'SLSearchPhrase==ウサギ') !== false)
				{
					$legal = true;
					if ($i != 3)
						continue;
				}
				if (! $legal)
					continue;
			}
			$rowConstructor = array();
			$rowColumn = array();
			$rowColumn['label'] = $fake[$i];
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS] = 1+$i;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_VISITS] = 2*$i;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_QUERIES] = 3*$i;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_QUERIES] = ($i)/6;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_RESULTS] = 2+$i;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_NB_CLICKS] = 4*$i;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_FR_CLICKS] = ($i)/6;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_AVG_CLICKS] = 4/3;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY] = 1;
			$rowColumn[Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY] = (4*$i)/18;
			$rowMetadata = array();
			$rowMetadata['annotation'] = Piwik_SmartLoggent_API::encodeString($fake[$i]);
			$rowMetadata['type'] = $fake[0];
			$rowConstructor[Piwik_DataTable_Row::METADATA] = $rowMetadata;
			$rowConstructor[Piwik_DataTable_Row::COLUMNS] = $rowColumn;
			$dataTableRow = new Piwik_DataTable_Row($rowConstructor);
			$table->addRow($dataTableRow);
		}
		$archive->insertBlobRecord('SmartLoggent_'.$fake[0], $table->getSerialized());
		destroy($table);
	}
}
