<?php

	class Query extends Object {
		var $queryID;
		var $action;

		var $columns;
		var $tables;
		var $conditions;
		var $groups;
		var $orderby;
		var $limit;

		var $arguments = null;

                var $columnList = null;

		function Query($queryID = null
			, $action = null
			, $columns = null
			, $tables = null
			, $conditions = null
			, $groups = null
			, $orderby = null
			, $limit = null){
			$this->queryID = $queryID;
			$this->action = $action;

                        if(!isset($tables)) return;
			$this->columns = $this->setColumns($columns);
			$this->tables = $this->setTables($tables);
			$this->conditions = $this->setConditions($conditions);
			$this->groups = $this->setGroups($groups);
			$this->orderby = $this->setOrder($orderby);
			$this->limit = $this->setLimit($limit);
		}

		function show(){
			return true;
		}

		function setQueryId($queryID){
			$this->queryID = $queryID;
		}

		function setAction($action){
			$this->action = $action;
		}

                function setColumnList($columnList){
                    if(count($columnList) > 0)
                        $this->columnList = $columnList;
                }

		function setColumns($columns){
			if(!isset($columns) || count($columns) === 0){
				$this->columns = array(new StarExpression());
				return;
			}

			if(!is_array($columns)) $columns = array($columns);

			$this->columns = $columns;
		}

		function setTables($tables){
			if(!isset($tables) || count($tables) === 0){
				$this->setError(true);
				$this->setMessage("You must provide at least one table for the query.");
				return;
			}

			if(!is_array($tables)) $tables = array($tables);

			$this->tables = $tables;
		}

		function setConditions($conditions){
			if(!isset($conditions) || count($conditions) === 0) return;
			if(!is_array($conditions)) $conditions = array($conditions);

			$this->conditions = $conditions;
		}

		function setGroups($groups){
			if(!isset($groups) || count($groups) === 0) return;
			if(!is_array($groups)) $groups = array($groups);

			$this->groups = $groups;
		}

		function setOrder($order){
			if(!isset($order) || count($order) === 0) return;
			if(!is_array($order)) $order = array($order);

			$this->orderby = $order;
		}

		function setLimit($limit = NULL){
			if(!isset($limit)) return;
			$this->limit = $limit;
		}

		// START Fluent interface
		function select($columns= null){
			$this->action = 'select';
			$this->setColumns($columns);
			return $this;
		}

		function from($tables){
			$this->setTables($tables);
			return $this;
		}

		function where($conditions){
			$this->setConditions($conditions);
			return $this;
		}

		function groupBy($groups){
			$this->setGroups($groups);
			return $this;
		}

		function orderBy($order){
			$this->setOrder($order);
			return $this;
		}

		function limit($limit){
			$this->setLimit($limit);
			return $this;
		}
		// END Fluent interface

		function getAction(){
			return $this->action;
		}

		function getSelectString($with_values = true){
                        if(isset($this->columnList)){
                            $selectColumns = array();
                            $dbParser = XmlQueryParser::getDBParser();

                            foreach($this->columnList as $columnName){
                                    $columnName = $dbParser->escapeColumn($columnName);
                                    $selectColumns[] = new SelectExpression($columnName);
                            }
                        }
                        else
                            $selectColumns = $this->columns;

			$select = '';
			foreach($selectColumns as $column){
				if($column->show())
					if(is_a($column, 'Subquery')){
						$select .= $column->toString($with_values) . ' as '. $column->getAlias() .', ';
					}
					else
						$select .= $column->getExpression($with_values) . ', ';
			}
			if(trim($select) == '') return '';
			$select = substr($select, 0, -2);
			return $select;
		}

		function getUpdateString($with_values = true){
			return $this->getSelectString($with_values);
		}

		function getInsertString($with_values = true){
			$columnsList = '';
			$valuesList = '';
			foreach($this->columns as $column){
				if($column->show()){
					$columnsList .= $column->getColumnName() . ', ';
					$valuesList .= $column->getValue($with_values) . ', ';
				}
			}
			$columnsList = substr($columnsList, 0, -2);
			$valuesList = substr($valuesList, 0, -2);

			return "($columnsList) \n VALUES ($valuesList)";
		}

		function getTables(){
			return $this->tables;
		}

                // from table_a
                // from table_a inner join table_b on x=y
                // from (select * from table a) as x
                // from (select * from table t) as x inner join table y on y.x
		function getFromString($with_values = true){
			$from = '';
			$simple_table_count = 0;
			foreach($this->tables as $table){
				if($table->isJoinTable() || !$simple_table_count) $from .= $table->toString($with_values) . ' ';
				else $from .= ', '.$table->toString($with_values) . ' ';

                                if(is_a($table, 'Subquery')) $from .= $table->getAlias() ? ' as ' . $table->getAlias() . ' ' : ' ';

				$simple_table_count++;
			}
			if(trim($from) == '') return '';
			return $from;
		}

		function getWhereString($with_values = true){
			$where = '';
			if(count($this->conditions) > 0){
                                $condition_count = 0;
				foreach($this->conditions as $conditionGroup){
                                        $condition_string = $conditionGroup->toString($with_values);
                                        if($condition_string !== '') $condition_count++;
                                        if($condition_count === 1){
                                            $conditionGroup->setPipe("");
                                            $condition_string = $conditionGroup->toString($with_values);
                                        }
					$where .= $condition_string;
				}
				if(trim($where) == '') return '';

			}
			return $where;
		}

		function getGroupByString(){
			$groupBy = '';
			if($this->groups) if($this->groups[0] !== "")
				$groupBy = implode(', ', $this->groups);
			return $groupBy;
		}

		function getOrderByString(){
			if(count($this->orderby) === 0) return '';
			$orderBy = '';
			foreach($this->orderby as $order){
				$orderBy .= $order->toString() .', ';
			}
			$orderBy = substr($orderBy, 0, -2);
			return $orderBy;
		}

		function getLimit(){
			return $this->limit;
		}

		function getLimitString(){
			$limit = '';
			if(count($this->limit) > 0){
				$limit = '';
				$limit .= $this->limit->toString();
			}
			return $limit;
		}

		function getFirstTableName(){
			return $this->tables[0]->getName();
		}

		function getArguments(){
			if(!isset($this->arguments)){
				$this->arguments = array();

				// Column arguments
				if(count($this->columns) > 0){ // The if is for delete statements, all others must have columns
					foreach($this->columns as $column){
						if($column->show()){
							$arg = $column->getArgument();
							if($arg) $this->arguments[] = $arg;
						}
					}
				}

				// Condition arguments
				if(count($this->conditions) > 0)
					foreach($this->conditions as $conditionGroup){
						$args = $conditionGroup->getArguments();
						if(count($args) > 0) $this->arguments = array_merge($this->arguments, $args);
					}

				// Navigation arguments
				if(count($this->orderby) > 0)
					foreach($this->orderby as $order){
						$args = $order->getArguments();
						if(count($args) > 0) $this->arguments = array_merge($this->arguments, $args);
					}
			}
			return $this->arguments;
		}
	}



?>