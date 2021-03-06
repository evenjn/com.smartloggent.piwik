<?php

class Piwik_SmartLoggent_Core_SegmentExpression
{
    const AND_DELIMITER = ';';
    const OR_DELIMITER = ',';
    
    const MATCH_EQUAL = '==';
    const MATCH_NOT_EQUAL = '!=';
    const MATCH_GREATER_OR_EQUAL = '>=';
    const MATCH_LESS_OR_EQUAL = '<=';
    const MATCH_GREATER = '>';
    const MATCH_LESS = '<';
    const MATCH_CONTAINS = '=@';
    const MATCH_DOES_NOT_CONTAIN = '!@';
    
    // Special case, since we look up Page URLs/Page titles in a sub SQL query
    const MATCH_ACTIONS_CONTAINS = 'IN';
    
    const INDEX_BOOL_OPERATOR = 0;
    const INDEX_OPERAND = 1;
    
    function __construct($string)
    {
      $this->string = $string;
      $this->tree = $this->parseTree();
    }
    protected $joins = array();
    protected $valuesBind = array();
    protected $parsedTree = array();
    protected $tree = array();
	protected $parsedSubExpressions = array();

	/**
	 * Given the array of parsed filters containing, for each filter,
	 * the boolean operator (AND/OR) and the operand,
	 * Will return the array where the filters are in SQL representation
	 *
	 * @throws Exception
	 * @return array
	 */
    public function parseSubExpressions()
    {
    	
    	
        $parsedSubExpressions = array();
        foreach($this->tree as $id => $leaf)
        {
            $operand = $leaf[self::INDEX_OPERAND];
            $operator = $leaf[self::INDEX_BOOL_OPERATOR];
            $pattern = '/^(.+?)('	.self::MATCH_EQUAL.'|'
            						.self::MATCH_NOT_EQUAL.'|'
            						.self::MATCH_GREATER_OR_EQUAL.'|'
            						.self::MATCH_GREATER.'|'
            						.self::MATCH_LESS_OR_EQUAL.'|'
            						.self::MATCH_LESS.'|'
            						.self::MATCH_CONTAINS.'|'
            						.self::MATCH_DOES_NOT_CONTAIN
            						.'){1}(.+)/';
            $match = preg_match( $pattern, $operand, $matches );
            if($match == 0)
            {
                throw new Exception('Segment parameter \''.$operand.'\' does not appear to have a valid format.');
            }
//            var_dump($matches);
            
            $leftMember = $matches[1];
            $operation = $matches[2];
            $valueRightMember = $matches[3];
            
            if ($leftMember)
            {
            	
    					if (in_array($leftMember, Piwik_SmartLoggent_API::$SEGMENTSTOESCAPE))
            	{
            		$encoded = $valueRightMember;
//             		Piwik::log("DECODING $encoded");
            		// here we should decode
            		$decoded = Piwik_SmartLoggent_API::decodeString($encoded);
//             		Piwik::log("INTO $decoded");
            		$valueRightMember = $decoded;
            	}

            $parsedSubExpressions[] = array( 
                self::INDEX_BOOL_OPERATOR => $operator,
                self::INDEX_OPERAND => array(
                    $leftMember,
                    $operation, 
                    $valueRightMember, 
            ));
            }
        }
        $this->parsedSubExpressions = $parsedSubExpressions;
        
        return $parsedSubExpressions;
    }

	/**
	 * Set the given expression
	 * @param $parsedSubExpressions
	 */
    public function setSubExpressionsAfterCleanup($parsedSubExpressions)
    {
        $this->parsedSubExpressions = $parsedSubExpressions;
    }

	/**
	 * Returns the current sub expression
	 * @return array
	 */
    public function getSubExpressions()
    {
        return $this->parsedSubExpressions;
    }

	/**
	 * @param array $availableTables
	 */
    public function parseSubExpressionsIntoSqlExpressions(&$availableTables=array())
    {
		//$profiler = Piwik::profilestart('Piwik_SmartLoggent_Core_SegmentExpression::'.__FUNCTION__); // 		Piwik::profileend($profiler);
    	
    	
        $sqlSubExpressions = array();
        $this->valuesBind = array();
        $this->joins = array();
        
        foreach($this->parsedSubExpressions as $leaf)
        {
            $operator = $leaf[self::INDEX_BOOL_OPERATOR];
            $operandDefinition = $leaf[self::INDEX_OPERAND];
            
            $operand = $this->getSqlMatchFromDefinition($operandDefinition, $availableTables);
            
            if ($operand[1] !== null) {
                $this->valuesBind[] = $operand[1];
            }
            $operand = $operand[0];
            $sqlSubExpressions[] = array(
                self::INDEX_BOOL_OPERATOR => $operator,
                self::INDEX_OPERAND => $operand,
                );
        }
        
        $this->tree = $sqlSubExpressions;
        // Piwik::profileend($profiler);
    }

	/**
	 * Given an array representing one filter operand ( left member , operation , right member)
	 * Will return an array containing
	 * - the SQL substring,
	 * - the values to bind to this substring
	 *
	 * @param array $def
	 * @param array $availableTables
	 * @throws Exception
	 * @return array
	 */
    // @todo case insensitive?
    protected function getSqlMatchFromDefinition($def, &$availableTables)
    {
    	$field = $def[0];
    	$matchType = $def[1];
        $value = $def[2];
        
        switch($matchType)
        {
        	case self::MATCH_EQUAL:
        		$sqlMatch = '=';
        		break;
        	case self::MATCH_NOT_EQUAL:
        		$sqlMatch = '<>';
        		break;
        	case self::MATCH_GREATER:
        		$sqlMatch = '>';
        		break;
        	case self::MATCH_LESS:
        		$sqlMatch = '<';
        		break;
        	case self::MATCH_GREATER_OR_EQUAL:
        		$sqlMatch = '>=';
        		break;
        	case self::MATCH_LESS_OR_EQUAL:
        		$sqlMatch = '<=';
        		break;
        	case self::MATCH_CONTAINS:
        		$sqlMatch = 'LIKE';
        		$value = '%'.$this->escapeLikeString($value).'%';
        		break;
        	case self::MATCH_DOES_NOT_CONTAIN:
        		$sqlMatch = 'NOT LIKE';
        		$value = '%'.$this->escapeLikeString($value).'%';
        		break;
        	
            case self::MATCH_ACTIONS_CONTAINS:
                // this match type is not accessible from the outside
                // (it won't be matched in self::parseSubExpressions())
                // it can be used internally to inject sub-expressions into the query.
                // see Piwik_Segment::getCleanedExpression()
                $sqlMatch = 'IN ('.$value['SQL'].')';
                $value = $this->escapeLikeString($value['bind']);
                break;
        	default:
        		throw new Exception("Filter contains the match type '".$matchType."' which is not supported");
        		break;
        }
        
        if ($matchType === self::MATCH_ACTIONS_CONTAINS) {
            $sqlExpression = "$field $sqlMatch";
        } else {
            $sqlExpression = "$field $sqlMatch ?";
        }
        
        $this->checkFieldIsAvailable($field, $availableTables);
        
        return array($sqlExpression, $value);
    }

	/**
	 * Check whether the field is available
	 * If not, add it to the available tables
	 *
	 * @param string $field
	 * @param array  $availableTables
	 */
    private function checkFieldIsAvailable($field, &$availableTables)
    {
        $fieldParts = explode('.', $field);
        
        $table = count($fieldParts) == 2 ? $fieldParts[0] : false;
        
        // remove sql functions from field name
        // example: `HOUR(log_visit.visit_last_action_time)` gets `HOUR(log_visit` => remove `HOUR(` 
        $table = preg_replace('/^[A-Z_]+\(/', '', $table);
        $tableExists = !$table || in_array($table, $availableTables);
        
        if (!$tableExists)
        {
        	$availableTables[] = $table;
        }
    }

	/**
	 * Escape the characters % and _ in the given string
	 * @param string $str
	 * @return string
	 */
    private function escapeLikeString($str)
    {
    	$str = str_replace("%", "\%", $str);
    	$str = str_replace("_", "\_", $str);
    	return $str;
    }
    
    /**
     * Given a filter string, 
     * will parse it into an array where each row contains the boolean operator applied to it, 
     * and the operand
     *
     * @return array
     */
    protected function parseTree()
    {
        $string = $this->string;
        if(empty($string)) {
            return array();
        }
        $tree = array();
        $i = 0;
        $length = strlen($string);
        $isBackslash = false;
        $operand = '';
        while($i <= $length)
        {
            $char = $string[$i];

            $isAND = ($char == self::AND_DELIMITER);
            $isOR = ($char == self::OR_DELIMITER);
            $isEnd = ($length == $i+1);
            
            if($isEnd)
            {
        	    if($isBackslash && ($isAND || $isOR))
        	    {
        	        $operand = substr($operand, 0, -1);
        	    }
                $operand .= $char;
                $tree[] = array(self::INDEX_BOOL_OPERATOR => '', self::INDEX_OPERAND => $operand);
                break;
            }
            
            if($isAND && !$isBackslash)
            {
            	$tree[] = array(self::INDEX_BOOL_OPERATOR => 'AND', self::INDEX_OPERAND => $operand);
            	$operand = '';
        	}
        	elseif($isOR && !$isBackslash)
        	{
        	    $tree[] = array(self::INDEX_BOOL_OPERATOR => 'OR', self::INDEX_OPERAND => $operand);
            	$operand = '';
        	}
        	else
        	{
        	    if($isBackslash && ($isAND || $isOR))
        	    {
        	        $operand = substr($operand, 0, -1);
        	    }
            	$operand .= $char;
        	}
            $isBackslash = ($char == "\\");
            $i++;
        }
        return $tree;
    }

	/**
	 * Given the array of parsed boolean logic, will return
	 * an array containing the full SQL string representing the filter,
	 * the needed joins and the values to bind to the query
	 *
	 * @throws Exception
	 * @return array SQL Query, Joins and Bind parameters
	 */
    public function getSql()
    {
    	if(count($this->tree) == 0) 
        {
            throw new Exception("Invalid segment, please specify a valid segment.");
        }
        $bind = array();
        $sql = '';
        $subExpression = false;
        foreach($this->tree as $expression)
        {
        	
        	
            $operator = $expression[self::INDEX_BOOL_OPERATOR];
        	
            $operand = $expression[self::INDEX_OPERAND];
            
            if($operator == 'OR'
                && !$subExpression)
            {
                $sql .= ' (';
                $subExpression = true;
            }
            else
            {
                $sql .= ' ';
            }
            
            $sql .= $operand;
            
            if($operator == 'AND'
                && $subExpression)
            {
                $sql .= ')';
                $subExpression = false;
            }
            
            $sql .= " $operator";
        }
        if($subExpression)
        {
            $sql .= ')';
        }
        return array(
        	'where' => $sql, 
        	'bind' => $this->valuesBind,
        	'join' => implode(' ', $this->joins)
        );
    }
}
