<?php

class Piwik_SmartLoggent_Core_ArchiveProcessing_Day extends Piwik_ArchiveProcessing_Day
{
	/**
	 * Returns true if there are logs for the current archive.
	 *
	 * If the current archive is for a specific plugin (for example, Referers),
	 *   (for example when a Segment is defined and the Keywords report is requested)
	 * Then the function will create the Archive for the Core metrics 'VisitsSummary' which will in turn process the number of visits
	 *
	 * If there is no specified segment, the SQL query will always run.
	 *
	 * @return bool|null
	 */
	public function isThereSomeVisits()
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_Core_ArchiveProcessing_Day::'.__FUNCTION__); // 		Piwik::profileend($profiler);
		if (!is_null($this->isThereSomeVisits))
		{
			if ($this->isThereSomeVisits && is_null($this->nb_visits))
			{
				debug_print_backtrace();
				exit;
			}
// 			Piwik::profileend($profiler);
			return $this->isThereSomeVisits;
		}
		
		// prepare segmentation
		$segment = $this->getSegment();
		
		// We check if there is visits for the requested date / site / segment
		//  If no specified Segment
		//  Or if a segment is passed and we specifically process VisitsSummary
		//   Then we check the logs. This is to ensure that this query is ran only once for this day/site/segment (rather than running it for every plugin)
		$reportType = self::getPluginBeingProcessed($this->getRequestedReport());
		$specialcondition = $this->shouldProcessReportsAllPlugins($this->getSegment(), $this->period);
		if ($specialcondition
			|| ($reportType == 'VisitsSummary'))
		{
			// build query parts
			$select = "count(distinct log_visit.idvisitor) as nb_uniq_visitors,
				count(*) as nb_visits,
				sum(log_visit.visit_total_actions) as nb_actions,
				max(log_visit.visit_total_actions) as max_actions,
				sum(log_visit.visit_total_time) as sum_visit_length,
				sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as bounce_count,
				sum(case log_visit.visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted
			";
			$from = "log_visit";
			$where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite = ?
			";
			$bind = array($this->getStartDatetimeUTC(), $this->getEndDatetimeUTC(), $this->idsite);
			$query = $segment->getSelectQuery($select, $from, $where, $bind);
			
			$bind = $query['bind'];
			$sql = $query['sql'];
			$data = $this->db->fetchRow($sql, $bind);
			// no visits found
			if (!is_array($data) || $data['nb_visits'] == 0)
			{
// 				Piwik::profileend($profiler);
				return $this->isThereSomeVisits = false;
			}
			
			// visits found: set attribtues
			foreach ($data as $name => $value)
			{
				$this->insertNumericRecord($name, $value);
			}
			$this->setNumberOfVisits($data['nb_visits']);
			$this->setNumberOfVisitsConverted($data['nb_visits_converted']);
// 			Piwik::profileend($profiler);
			return $this->isThereSomeVisits = true;
		}
		
		$zzz = $this->redirectRequestToVisitsSummary();
// 		Piwik::profileend($profiler);
		return $zzz;
	}
	
	/**
	 * If a segment is specified but a plugin other than 'VisitsSummary' is being requested,
	 * we create an archive for processing VisitsSummary Core Metrics, which will in turn
	 * execute the query above (in isThereSomeVisits)
	 *
	 * @return bool|null
	 */
	private function redirectRequestToVisitsSummary()
	{
		
		$archive = new Piwik_SmartLoggent_Core_Archive_Single();
		$archive->setSite($this->site);
		$archive->setPeriod($this->period);
		$archive->setSegment($this->getSegment());
		$archive->setRequestedReport('VisitsSummary');
		
		$nbVisits = $archive->getNumeric('nb_visits');
		$this->isThereSomeVisits = $nbVisits > 0;
		
		if ($this->isThereSomeVisits)
		{
			$nbVisitsConverted = $archive->getNumeric('nb_visits_converted');
			$this->setNumberOfVisits($nbVisits);
			$this->setNumberOfVisitsConverted($nbVisitsConverted);
		}
		
		return $this->isThereSomeVisits;
	}
}
