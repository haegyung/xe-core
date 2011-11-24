<?php

	class Condition {
		var $column_name;
		var $argument;
		var $operation;
		var $pipe;

		var $_value;

                var $_show;
                var $_value_to_string;

		function Condition($column_name, $argument, $operation, $pipe){
			$this->column_name = $column_name;
			$this->argument = $argument;
			$this->operation = $operation;
			$this->pipe = $pipe;

		}

		function getArgument(){
			return null;
		}

		function toString($withValue = true){
                    if(!isset($this->_value_to_string)){
                        if(!$this->show()) { $this->_value_to_string = ''; }
			else if($withValue)
				$this->_value_to_string = $this->toStringWithValue();
			else $this->_value_to_string = $this->toStringWithoutValue();
                    }
                    return $this->_value_to_string;
		}

		function toStringWithoutValue(){
                    return $this->pipe . ' ' . $this->getConditionPart($this->_value);
		}

		function toStringWithValue(){
                    return $this->pipe . ' ' . $this->getConditionPart($this->_value);
		}

		function setPipe($pipe){
			$this->pipe = $pipe;
		}

		function show(){
                    if(!isset($this->_show)){
                        if(is_array($this->_value) && count($this->_value) === 1 && $this->_value[0] === '') {
                            $this->_show = false;
                        }
                        else {
                            $this->_show = true;
                            switch($this->operation) {
                                case 'equal' :
                                case 'more' :
                                case 'excess' :
                                case 'less' :
                                case 'below' :
                                case 'like_tail' :
                                case 'like_prefix' :
                                case 'like' :
                                case 'notlike_tail' :
                                case 'notlike_prefix' :
                                case 'notlike' :
                                case 'in' :
                                case 'notin' :
                                case 'and':
                                case 'or':
                                case 'xor':
                                case 'not':
                                case 'notequal' :
                                        // if variable is not set or is not string or number, return
                                        if(!isset($this->_value)) { $this->_show = false; break;}
                                        if($this->_value === '') { $this->_show = false; break; }
                                        if(!in_array(gettype($this->_value), array('string', 'integer'))) {$this->_show = false; break; }
                                        break;
                                case 'between' :
                                        if(!is_array($this->_value)) { $this->_show = false; break;}
                                        if(count($this->_value)!=2) {$this->_show = false; break;}
                            }
                        }
                    }
                    return $this->_show;
		}

		function getConditionPart($value) {
                    $name = $this->column_name;
                    $operation = $this->operation;

                        switch($operation) {
                            case 'equal' :
                                    return $name.' = '.$value;
                                break;
                            case 'more' :
                                    return $name.' >= '.$value;
                                break;
                            case 'excess' :
                                    return $name.' > '.$value;
                                break;
                            case 'less' :
                                    return $name.' <= '.$value;
                                break;
                            case 'below' :
                                    return $name.' < '.$value;
                                break;
                            case 'like_tail' :
                            case 'like_prefix' :
                            case 'like' :
                                    return $name.' like '.$value;
                                break;
                            case 'notlike_tail' :
                            case 'notlike_prefix' :
                            case 'notlike' :
                                    return $name.' not like '.$value;
                                break;
                            case 'in' :
                                    return $name.' in '.$value;
                                break;
                            case 'notin' :
                                    return $name.' not in '.$value;
                                break;
                            case 'notequal' :
                                    return $name.' <> '.$value;
                                break;
                            case 'notnull' :
                                    return $name.' is not null';
                                break;
                            case 'null' :
                                    return $name.' is null';
                                break;
                            case 'and' :
                                    return $name.' & '.$value;
                                break;
                            case 'or' :
                                    return $name.' | '.$value;
                                break;
                            case 'xor' :
                                    return $name.' ^ '.$value;
                                break;
                            case 'not' :
                                    return $name.' ~ '.$value;
                                break;
                            case 'between' :
                            return $name.' between ' . $value[0] . ' and ' . $value[1];
                                            break;
                }
            }
	}

?>
