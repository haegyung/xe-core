<?php

    /**
     * @class UpdateColumnTag
     * @author Arnia Software
     * @brief Models the <column> tag inside an XML Query file whose action is 'update'
     *
     **/



	class UpdateColumnTag extends ColumnTag {
		var $argument;
                var $default_value;

		function UpdateColumnTag($column) {
			parent::ColumnTag($column->attrs->name);
			$dbParser = DB::getParser();
			$this->name = $dbParser->parseColumnName($this->name);
                        if($column->attrs->var)
                            $this->argument = new QueryArgument($column);
                        else
                            $this->default_value = $dbParser->parseColumnName($column->attrs->default);
		}

		function getExpressionString(){
                    if($this->argument)
			return sprintf('new UpdateExpression(\'%s\', $%s_argument)'
						, $this->name
						, $this->argument->argument_name);
                    else {
			return sprintf('new UpdateExpressionWithoutArgument(\'%s\', \'%s\')'
						, $this->name
						, $this->default_value);
                    }
		}

		function getArgument(){
			return $this->argument;
		}
	}

?>