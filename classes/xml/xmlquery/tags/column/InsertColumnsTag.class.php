<?php 
    /**
     * @class InsertColumnsTag
     * @author Arnia Software
     * @brief Models the <column> tag inside an XML Query file whose action is 'insert'
     *
     **/

	require_once(_XE_PATH_.'classes/xml/xmlquery/tags/column/ColumnTag.class.php');
	require_once(_XE_PATH_.'classes/xml/xmlquery/tags/column/InsertColumnTag.class.php');

	class InsertColumnsTag{
		var $dbParser;
		var $columns;
		
		function InsertColumnsTag($xml_columns, $dbParser) {
			$this->dbParser = $dbParser;
			
			$this->columns = array();			
			
			if(!$xml_columns)
				return;
			
			if(!is_array($xml_columns)) $xml_columns = array($xml_columns); 	
			
			foreach($xml_columns as $column){
				$this->columns[] = new InsertColumnTag($column, $this->dbParser);
			}			
		}
		
		function toString(){
			$output_columns = 'array(' . PHP_EOL;
			foreach($this->columns as $column){
				$output_columns .= $column->getExpressionString() . PHP_EOL . ',';
			}
			$output_columns = substr($output_columns, 0, -1);
			$output_columns .= ')';	
			return $output_columns;			
		}

		function getArguments(){
			$arguments = array();
			foreach($this->columns as $column){
				$arguments[] = $column->getArgument();
			}
			return $arguments;
		}	
		
	}

?>