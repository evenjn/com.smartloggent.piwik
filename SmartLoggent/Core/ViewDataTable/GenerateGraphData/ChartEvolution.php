<?php

class Piwik_SmartLoggent_Core_ViewDataTable_GenerateGraphData_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphData_ChartEvolution
{
	protected function handleRowForRowPicker(&$rowLabel)
	{
		
		// determine whether row is visible
		$isVisible = true;
		switch ($this->rowPicker)
		{
			case 'label':
				if (empty($this->visibleRows))
					$isVisible = true;
				else
					$isVisible = in_array($rowLabel, $this->visibleRows);
				break;
		}
		
		// build config
		if (!isset($this->rowPickerConfig[$rowLabel]))
		{
			$this->rowPickerConfig[$rowLabel] = array(
				'label' => $rowLabel,
				'matcher' => $rowLabel,
				'displayed' => $isVisible
			);
		}
		
		return $isVisible;
	}
}
