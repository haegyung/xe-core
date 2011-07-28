<?php 

	/** 
	 * @class InsertExpression
	 * @author Arnia Software
	 * @brief 
	 *
	 */

	class InsertExpression extends Expression {
		var $argument;
		
		function InsertExpression($column_name, $argument){
			parent::Expression($column_name);
			$this->argument = $argument;
		}
		
		function getValue($with_values = true){
			if($with_values)
				return $this->argument->getValue();
			return '?';
		}
		
		function show(){
			$value = $this->argument->getValue();
			if(!isset($value)) return false;
			return true;
		}
		
		function getArgument(){
			return $this->argument;
		}
	}

?>