<?php
class Piwik_SmartLoggent_SegmentEditor
{
	
	/**
	 * Produces an array of segments. Hard to explain. Example:
	 *
	 * input:
	 * segment: 'language==it_de;color==blue,black,red;country==us'
	 * dimension: 'language'
	 *
	 * output:
	 *
	 * array (
	 * 'language==it;color==blue,black,red;country==us'
	 * 'language==de;color==blue,black,red;country==us'
	 * )
	 *
	 * @param string $segment
	 * @param string $dimension
	 */
	public static function split($segment, $dimension)
	{
		// 		Piwik::log("attempting to split segment $segment along $dimension");
		// case 1: at the end of the segment
		// array
		$matches = array();
		// string
		$pattern = '/(.*)'.$dimension.'==([^;]+)$/';
		// int
		$tosplit = preg_match($pattern, $segment, $matches);
		$hastail = false;
		if ($tosplit === 1)
		{
			// we're good
			// 			Piwik::log("attempting to split segment $segment along $dimension .. good with no tail");
		}
		else if ($tosplit === 0)
		{
			// try with another method!
			// string
			$pattern = '/(.*)'.$dimension.'==([^;]+)(.+)$/';
			// int
			$tosplit = preg_match($pattern, $segment, $matches);
			if ($tosplit === 1)
			{
				// we're good
				// 				Piwik::log("attempting to split segment $segment along $dimension .. good with tail");
				$hastail = true;
			}
			else if ($tosplit === 0)
			{
				// 				Piwik::log("attempting to split segment $segment along $dimension .. no need to split");
				return array($segment);
			}
			else
			{
				// error!
				// 				Piwik::log("attempting to split segment $segment along $dimension .. error!!!");
				return array($segment);
			}
		}
		else
		{
			// error!
			// 			Piwik::log("attempting to split segment $segment along $dimension .. error!!!");
			return array($segment);
		}
		// string
		$before = $matches[1];
		// 		Piwik::log("attempting to split segment $segment along $dimension .. before is $before");
		$thing = $matches[2];
		// 		Piwik::log("attempting to split segment $segment along $dimension .. thing is $thing");
		$tail = '';
		if ($hastail)
			$tail = $matches[3];
		// 		Piwik::log("attempting to split segment $segment along $dimension .. tail is $tail");
		$values = preg_split('/_/', $thing);
		$result = array();
		foreach ($values as $value)
		{
			$shard =$before.$dimension.'=='.$value.$tail;
			// 			Piwik::log("attempting to split segment $segment along $dimension .. shard is $shard");
			$result[] = $shard;
		}
	
		return $result;
	}
	
	public static function featureIsSet($feature, $segment)
	{
		if ($segment === false || $segment === '')
		{
			return false;
		}
		$booleanclause = self::parseTree($segment);
		$expandedbooleanclause = self::parseSubExpressions($booleanclause);
		foreach($expandedbooleanclause as $leaf)
		{
			$original = $leaf[Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND];
			$originalFeature = $original[0];
			if ($originalFeature == $feature)
			{
				return true;
			}
		}
		return false;
	}
	
	public static function set($feature, $condition, $value, $segment = false)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SegmentEditor::'.__FUNCTION__); //Piwik::profileend($profiler);
		
// 		Piwik::log("segment = $segment");
		if ($segment === false || $segment === '')
		{
			$segment = $feature.$condition.self::escapeForSegment($value);
// 			Piwik::log("returning hardcast $segment");
// 			Piwik::profileend($profiler);
			return $segment;
		}
		$booleanclause = self::parseTree($segment);
		$expandedbooleanclause = self::parseSubExpressions($booleanclause);
		$newsegment = '';
		$notset = true;
// 		Piwik::log("start for each");
		foreach($expandedbooleanclause as $leaf)
		{
// 			Piwik::log("Loop");
			$operator = $leaf[Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR];
			$original = $leaf[Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND];
			$originalFeature = $original[0];
			$originalCondition = $original[1];
			$originalValue = $original[2];
			$newsegment .= self::escapeForSegment($originalFeature);
			if ($originalFeature == $feature)
			{
				$newsegment .= $condition;
				$newsegment .= self::escapeForSegment($value);
				$notset = false;
			}
			 else
			 {
			 	$newsegment .= $originalCondition;
				$newsegment .= self::escapeForSegment($originalValue);
			 }
			if ($operator == 'AND')
				$newsegment .= Piwik_SmartLoggent_Core_SegmentExpression::AND_DELIMITER;
			else if ($operator == 'OR')
				$newsegment .= Piwik_SmartLoggent_Core_SegmentExpression::OR_DELIMITER;
		}
// 		Piwik::log("end for each");
		if ($notset)
		{
			$newsegment .= Piwik_SmartLoggent_Core_SegmentExpression::AND_DELIMITER;
			$newsegment .= $feature.$condition.self::escapeForSegment($value);
		}
// 		Piwik::profileend($profiler);
		return $newsegment;
	}

	private static function escapeForSegment($string)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SegmentEditor::'.__FUNCTION__); //Piwik::profileend($profiler);
		$i = 0;
		$length = strlen($string);
		$result = '';
		while($i < $length)
		{
			$char = $string[$i];
			$isAND = ($char == Piwik_SmartLoggent_Core_SegmentExpression::AND_DELIMITER);
			$isOR = ($char == Piwik_SmartLoggent_Core_SegmentExpression::OR_DELIMITER);
			if ($isAND || $isOR)
				$result .= '\\';
			$result .= $char;
			$i++;
		}
// 		Piwik::profileend($profiler);
		return $result;
	}
	
	/**
	 * breaks down the operands into triples (feature, comparator, value)
	 */
	private static function parseSubExpressions($tree)
	{
// 		$profiler = Piwik::profilestart('Piwik_SmartLoggent_SegmentEditor::'.__FUNCTION__); //Piwik::profileend($profiler);
			
		$parsedSubExpressions = array();
		foreach($tree as $id => $leaf)
		{
			$operand = $leaf[Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND];
			$operator = $leaf[Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR];
			$pattern = '/^(.+?)('	.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_EQUAL.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_NOT_EQUAL.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_GREATER_OR_EQUAL.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_GREATER.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_LESS_OR_EQUAL.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_LESS.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_CONTAINS.'|'
			.Piwik_SmartLoggent_Core_SegmentExpression::MATCH_DOES_NOT_CONTAIN
			.'){1}(.+)/';
			$match = preg_match( $pattern, $operand, $matches );
			if($match == 0)
			{
				
				Piwik::log(" $operand not matching!");
// 				Piwik::profileend($profiler);
				throw new Exception('Segment parameter \''.$operand.'\' does not appear to have a valid format.');
			}

			$leftMember = $matches[1];
			$operation = $matches[2];
			$valueRightMember = $matches[3];

			if ($leftMember)
			{
				$parsedSubExpressions[] = array(
						Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR => $operator,
						Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND => array(
								$leftMember,
								$operation,
								$valueRightMember,
						));
			}
		}
// 		Piwik::profileend($profiler);
		return $parsedSubExpressions;
	}

	/**
	 * returns a list of pairs (next operator, constraint) that is meant to be read
	 * (AND, x=1), (OR, y=2), (emptystring, z=3)
	 *
	 * AND, OR, emptystring are boolean operators
	 * x=1 are operands.
	 *
	 * x=1 AND (y=2 OR z=3)
	 *
	 */
	private static function parseTree($string)
	{
		if(empty($string)) {
			return array();
		}
		$tree = array();
		$i = 0;
		$length = strlen($string);
		$previouwWasBackslash = false;
		$operand = '';
		while($i <= $length)
		{
			$char = $string[$i];

			$isAND = ($char == Piwik_SmartLoggent_Core_SegmentExpression::AND_DELIMITER);
			$isOR = ($char == Piwik_SmartLoggent_Core_SegmentExpression::OR_DELIMITER);
			$isEnd = ($length == $i+1);

			if($isEnd)
			{
				if($previouwWasBackslash && ($isAND || $isOR))
				{
					$operand = substr($operand, 0, -1);
				}
				$operand .= $char;
				$tree[] = array
				(
						Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR => ''
						, Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND => $operand
				);
				break;
			}

			if($isAND && !$previouwWasBackslash)
			{
				$tree[] = array
				(
						Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR => 'AND'
						, Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND => $operand
				);
				$operand = '';
			}
			elseif($isOR && !$previouwWasBackslash)
			{
				$tree[] = array
				(
						Piwik_SmartLoggent_Core_SegmentExpression::INDEX_BOOL_OPERATOR => 'OR'
						, Piwik_SmartLoggent_Core_SegmentExpression::INDEX_OPERAND => $operand
				);
				$operand = '';
			}
			else
			{
				if($previouwWasBackslash && ($isAND || $isOR))
				{
					$operand = substr($operand, 0, -1);
				}
				$operand .= $char;
			}
			$previouwWasBackslash = ($char == "\\");
			$i++;
		}
		return $tree;
	}
}