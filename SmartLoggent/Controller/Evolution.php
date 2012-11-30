<?php

class Piwik_SmartLoggent_Controller_Evolution
{

public static function configureEvolution(&$view, $metric, $rowpicker = false, $footer = true, $columns = false)
{

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
	if ($visibleRows)
	{
		/* this happens when the row picker has been used */
		$visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
	}
	else
	{
		// this is implode(',', $getTopResult['labels']) calculated externally
		$visibleRows = Piwik_Common::getRequestVar('autorows', false);
		if ($visibleRows && $rowpicker)
		{
			$view->setParametersToModify(array('rows' => $visibleRows));
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
}
}