<?php
class Piwik_SmartLoggent_Controller extends Piwik_Controller
{
	public function searchPhraseOverview()
	{
		$view = new Piwik_View('SmartLoggent/templates/searchPhraseOverview.tpl');
		$view->searchPhrase = $this->getSearchPhrase(true);
		echo $view->render();
	}
	
	public function classOverview()
	{
		$view = new Piwik_View('SmartLoggent/templates/classOverview.tpl');
		$view->class = $this->getClass(true);
		$view->evolution = $this->getClassEvolution(false, true);
		echo $view->render();
	}
	
	public function getSearchPhrase($fetch=false, $limit=20)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getSearchPhrase');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_SearchPhrase', $fetch, $limit);
		return $result;
	}
	
	public function getClass($fetch=false, $limit=20)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init($this->pluginName,  __FUNCTION__, 'SmartLoggent.getClass');
		$result = $this->configureUsualTable($view, 'LOC_SL_Column_Label_Class', $fetch, $limit);
		return $result;
	}
	
	public function configureUsualTable($view, $labelLocalization, $fetch=false, $limit=20)
	{
		$view->setColumnsToDisplay(array(
			'label'
		,	Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS
		,	Piwik_SmartLoggent_API::INDEX_NB_VISITS
		,	Piwik_SmartLoggent_API::INDEX_NB_QUERIES
		,	Piwik_SmartLoggent_API::INDEX_FR_QUERIES
		,	Piwik_SmartLoggent_API::INDEX_AVG_RESULTS
		,	Piwik_SmartLoggent_API::INDEX_NB_CLICKS
		,	Piwik_SmartLoggent_API::INDEX_FR_CLICKS
		,	Piwik_SmartLoggent_API::INDEX_AVG_CLICKS
		,	Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY
		,	Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY
		));

		$view->setColumnTranslation('label', Piwik_Translate($labelLocalization));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_AVG_CLICKS, Piwik_Translate('LOC_SL_Column_AVG_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_AVG_RESULTS, Piwik_Translate('LOC_SL_Column_AVG_RESULTS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_CLICK_PROBABILITY, Piwik_Translate('LOC_SL_Column_CLICK_PROBABILITY'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_FR_CLICKS, Piwik_Translate('LOC_SL_Column_FR_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_FR_QUERIES, Piwik_Translate('LOC_SL_Column_FR_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_CLICKS, Piwik_Translate('LOC_SL_Column_NB_CLICKS'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_QUERIES, Piwik_Translate('LOC_SL_Column_NB_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_WEIGHTED_CLICK_PROBABILITY, Piwik_Translate('LOC_SL_Column_WEIGHTED_CLICK_PROBABILITY'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS, Piwik_Translate('General_ColumnNbUniqVisitors'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS, Piwik_Translate('General_ColumnNbVisits'));

		$view->setSortedColumn(Piwik_SmartLoggent_API::INDEX_NB_QUERIES, 'desc');
		$view->disableShowAllViewsIcons();
		$view->disableShowAllColumns();
		$view->setLimit($limit);
		$view->setTemplate('SmartLoggent/templates/datatable.tpl');
		$result = $this->renderView($view, $fetch);
		return $result;
	}
	
	public function getClassEvolution( $columns = false, $fetch = false)
	{
		$view = $this->genericEvolution(__FUNCTION__, Piwik_SmartLoggent_API::DIM_CLASS, false, true, $columns);
		$result = $this->renderView($view, $fetch);
		return $result;
	}
	
	/**
	 * 
	 * @param unknown_type $function
	 * @param unknown_type $apimethod
	 * @param unknown_type $rowpicker currently the rowpicker cannot be displayed, so the only legal value is false.
	 * @param unknown_type $footer
	 * @param unknown_type $columns
	 */
	private function genericEvolution($function, $dimension, $rowpicker = false, $footer = true, $columns = false)
	{
		/*
		 * You can't pass parameters. Seriously. This method is designed to
		* invoke itself without any arguments.
		*
		* This is how Piwik_ViewDataTable::factory('graphEvolution') works
		*/
		$metric = Piwik_SmartLoggent_API::INDEX_NB_QUERIES;
		$view = $this->getLastUnitGraph($this->pluginName, $function, 'SmartLoggent.get'.$dimension);
	
		/*
		 * Don't disable the footer unless you also disable the rowpicker. See the following bug.
		* http://dev.piwik.org/trac/ticket/3313
		*/
		if (!$rowpicker)
		{
			/* but you may decide to disable the rowpicker and still keep the footer */
			if (!$footer)
				$view->disableFooter();
		}
	
		/*
		 * CATEGORIES SELECTION (ROWS)
		*
		* The visible rows are either chosen by the user using the rowPicker,
		* or the rows that correspond to the top items according to a metric
		*
		* If the user has picked some rows manually, they appear as a 'rows'
		* parameter.
		*/
		$visibleRows = Piwik_Common::getRequestVar('rows', false);
		if ($visibleRows !== false)
		{
			/* this happens when the row picker has been used */
			$visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
		}
		else
		{
			// we had a mechanism to specify the exact elements to show (the top 5)
			// but now this mechanism must be changed to fit wit the new API.
			// this mechanism is crucial to display only the few relevant items.
			//
			// the idea is to invoke one of the API methods that return the
			// identifiers of the top items ranked according the desired metric, such
			// as
			//
			// getTopClass_NB_UNIQ_VISITORS
			//
			// then either modify the segment so that the elements retrieved are only
			// those, or let the API know it in some other way.
			
			if (Piwik_Common::getRequestVar('smartloggent_reset_filter_evolution', 'yes', 'string') == 'yes')
			{
				$_GET['smartloggent_reset_filter_evolution'] = 'no';
				$_GET['smartloggent_filter_evolution'] = '';
				$visibleRows = self::getTop(Piwik_SmartLoggent_API::DIM_CLASS, $metric);
			
				if ($rowpicker)
				{
					$view->setParametersToModify(array('rows' => implode(',', $visibleRows)));
				}
				else
				{
					$filter_tops_array= array();
					foreach ($visibleRows as $lala)
					{
						$encoded = Piwik_SmartLoggent_API::encodeString($lala);
						$filter_tops_array [] = $encoded;
					}
					$_GET['smartloggent_filter_evolution'] = implode(',', $filter_tops_array);
				}
			}
		}
		if ($rowpicker)
			$view->addRowPicker($visibleRows);
	
		/* METRICS LABELS (COLUMNS) */
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_QUERIES, Piwik_Translate('LOC_SL_Column_NB_QUERIES'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS, Piwik_Translate(
				'General_ColumnNbUniqVisitors'));
		$view->setColumnTranslation(Piwik_SmartLoggent_API::INDEX_NB_VISITS, Piwik_Translate(
				'General_ColumnNbVisits'));
	
		/*
		 *  METRICS SELECTION (COLUMNS)
		 *
		 *  The metrics to display can be set programmatically with the $columns
		 *  argumnent.
		 */
		if(empty($columns))
		{
			
			$columns = Piwik_Common::getRequestVar('columns', Piwik_SmartLoggent_API::INDEX_NB_QUERIES);
			$columns = Piwik::getArrayFromApiParameter($columns);
		}
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
	
		/*
		 * It is not possible to use the row picker when there are no selectable
		 * columns.
		 */
		if ($rowpicker)
			$view->setSelectableColumns(array($metric, Piwik_SmartLoggent_API::INDEX_NB_UNIQ_VISITORS));
		return $view;
	}
	
	public static function getTop($dimension, $metric)
	{
		$idSite = Piwik_Common::getRequestVar('idSite', '', 'string');
		$period = Piwik_Common::getRequestVar('period', '', 'string');
		$date = Piwik_Common::getRequestVar('date', '', 'string');
		$segment = Piwik_Common::getRequestVar('date', false, 'string');
		return Piwik_SmartLoggent_API::getInstance()->getTop(Piwik_SmartLoggent_API::INDEX_NB_VISITS, $idSite, $period, $date, $segment, $dimension, $metric);
	}
}