<?php 

	class ConditionGroupTag {	
		var $conditions;
		var $pipe;
		
		function ConditionGroupTag($conditions, $pipe = ""){
			$this->pipe = $pipe;
			
			if(!is_array($conditions)) $conditions = array($conditions);
			if(count($conditions))require_once(_XE_PATH_.'classes/xml/xmlquery/tags/condition/ConditionTag.class.php');
			
			foreach($conditions as $condition){
				if($condition->name === 'query') $this->conditions[] = new QueryTag($condition, true);
				else $this->conditions[] = new ConditionTag($condition);
			}
		}
		
		function getConditions(){
			return $this->conditions;
		}
		
		function getConditionGroupString(){
			$conditions_string = 'array('.PHP_EOL;
			foreach($this->conditions as $condition)
				$conditions_string .= $condition->getConditionString() . PHP_EOL . ',';
			$conditions_string = substr($conditions_string, 0, -2);//remove ','
			$conditions_string .= ')';
			
			return sprintf("new ConditionGroup(%s%s)", $conditions_string, $this->pipe ? ',\''.$this->pipe . '\'': '');
		}
	
		function getArguments(){
			$arguments = array();
			foreach($this->conditions as $condition){
				$arguments[] = $condition->getArgument();
			}
			return $arguments;
		}
				
	}
?>