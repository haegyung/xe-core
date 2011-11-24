<?php

	/**
	 * @class HintTableTag
	 * @author Arnia Sowftare
	 * @brief Models the <table> tag inside an XML Query file
         *      and the corresponding <index_hint> tag
	 *
	 */

	class HintTableTag extends TableTag {
                var $index;

                /**
                 * @brief Initialises Table Tag properties
                 * @param XML <table> tag $table
                 */
		function HintTableTag($table, $index){
                        parent::TableTag($table);
                        $this->index = $index;
		}

		function getTableString(){
			$dbParser = DB::getParser();
                        $dbType = ucfirst(Context::getDBType());

                        $result = sprintf('new %sTableWithHint(\'%s\'%s, array('
                                            , $dbType
                                            , $dbParser->escape($this->name)
                                            , $this->alias ? ', \'' . $dbParser->escape($this->alias) .'\'' : ', null'
                                            //, ', \'' . $dbParser->escape($this->index->name) .'\', \'' . $this->index->type .'\''
                                        );
                        foreach($this->index as $indx){
                            $result .= "new IndexHint(";
                            $result .= '\'' . $dbParser->escape($indx->name) .'\', \'' . $indx->type .'\'' . ') , ';
                        }
                        $result = substr($result, 0, -2);
                        $result .= '))';
                        return $result;
		}

                function getArguments(){
                    if(!isset($this->conditionsTag)) return array();
                    return $this->conditionsTag->getArguments();
                }
	}

?>