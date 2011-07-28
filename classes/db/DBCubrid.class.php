<?php
	/**
	 * @class DBCubrid
	 * @author NHN (developers@xpressengine.com)
	 * @brief Cubrid DBMS to use the class
	 * @version 0.1p1
	 *
	 * Modified to work with CUBRID2008 R1.3 verion by Prototype (prototype@cubrid.com)/09.02.23
	 * Test completed for CUBRID 7.3 ~ 2008 R1.3 versions.
	 * Only basic query used so query tunning and optimization needed
	 **/

	class DBCubrid extends DB
	{

		/**
		 * @brief CUBRID DB connection information
		 **/
		var $hostname = '127.0.0.1'; ///< hostname
		var $userid = NULL; ///< user id
		var $password = NULL; ///< password
		var $database = NULL; ///< database
		var $port = 33000; ///< db server port
		var $prefix = 'xe'; // / <prefix of XE tables(One more XE can be installed on a single DB)
		var $cutlen = 12000; // /< max size of constant in CUBRID(if string is larger than this, '...'+'...' should be used)
		var $comment_syntax = '/* %s */';

		/**
		 * @brief column type used in CUBRID
		 *
		 * column_type should be replaced for each DBMS's type
		 * becasue it uses commonly defined type in the schema/query xml
		 **/
		var $column_type = array(
			'bignumber' => 'numeric(20)',
			'number' => 'integer',
			'varchar' => 'character varying',
			'char' => 'character',
			'tinytext' => 'character varying(256)',
			'text' => 'character varying(1073741823)',
			'bigtext' => 'character varying(1073741823)',
			'date' => 'character varying(14)',
			'float' => 'float',
		);

		/**
		 * @brief constructor
		 **/
		function DBCubrid()
		{
			$this->_setDBInfo();
			$this->_connect();
		}

		/**
		 * @brief create an instance of this class
		 */
		function create()
		{
			return new DBCubrid;
		}

		/**
		 * @brief Return if installable
		 **/
		function isSupported()
		{
			if (!function_exists('cubrid_connect')) return false;
			return true;
		}

		/**
		 * @brief DB settings and connect/close
		 **/
		function _setDBInfo()
		{
			$db_info = Context::getDBInfo();
			$this->hostname = $db_info->db_hostname;
			$this->userid   = $db_info->db_userid;
			$this->password   = $db_info->db_password;
			$this->database = $db_info->db_database;
			$this->port = $db_info->db_port;
			$this->prefix = $db_info->db_table_prefix;

			if (!substr($this->prefix, -1) != '_') $this->prefix .= '_';
		}

		/**
		 * @brief DB Connection
		 **/
		function _connect()
		{
			// ignore if db information not exists
			if (!$this->hostname || !$this->userid || !$this->password || !$this->database || !$this->port) return;

			// attempts to connect
			$this->fd = @cubrid_connect ($this->hostname, $this->port, $this->database, $this->userid, $this->password);

			// check connections
			if (!$this->fd) {
				$this->setError (-1, 'database connect fail');
				return $this->is_connected = false;
			}

			$this->is_connected = true;
			$this->password = md5 ($this->password);
		}

		/**
		 * @brief DB disconnect
		 **/
		function close()
		{
			if (!$this->isConnected ()) return;

			@cubrid_commit ($this->fd);
			@cubrid_disconnect ($this->fd);
			$this->transaction_started = false;
		}

		/**
		 * @brief handles quatation of the string variables from the query
		 **/
		function addQuotes($string)
		{
			if (!$this->fd) return $string;

			if (version_compare (PHP_VERSION, "5.9.0", "<") &&
			  get_magic_quotes_gpc ()) {
				$string = stripslashes (str_replace ("\\","\\\\", $string));
			}

			if (!is_numeric ($string)) {
			/*
				if ($this->isConnected()) {
					$string = cubrid_real_escape_string($string);
				}
				else {
					$string = str_replace("'","\'",$string);
				}
				*/

				$string = str_replace("'","''",$string);
			}

			return $string;
		}

		/**
		 * @brief Begin transaction
		 **/
		function begin()
		{
			if (!$this->isConnected () || $this->transaction_started) return;
			$this->transaction_started = true;
		}

		/**
		 * @brief Rollback
		 **/
		function rollback()
		{
			if (!$this->isConnected () || !$this->transaction_started) return;
			@cubrid_rollback ($this->fd);
			$this->transaction_started = false;
		}

		/**
		 * @brief Commit
		 **/
		function commit()
		{
			if (!$force && (!$this->isConnected () ||
			  !$this->transaction_started)) return;

			@cubrid_commit($this->fd);
			$this->transaction_started = false;
		}

		/**
		 * @brief : executing the query and fetching the result
		 *
		 * query: run a query and return the result\n
		 * fetch: NULL if no value returned \n
		 *		array object if rows returned \n
		 *		object if a row returned \n
		 *		return\n
		 **/
		function _query($query)
		{
			if (!$query || !$this->isConnected ()) return;

			// Notify to start a query execution
			$this->actStart ($query);

			// Execute the query
			$result = @cubrid_execute ($this->fd, $query);
			// error check
			if (cubrid_error_code ()) {
				$code = cubrid_error_code ();
				$msg = cubrid_error_msg ();

				$this->setError ($code, $msg);
			}

			// Notify to complete a query execution
			$this->actFinish ();

			// Return the result
 			return $result;
		}

		/**
		 * @brief Fetch the result
		 **/
		function _fetch($result, $arrayIndexEndValue = NULL)
		{
			if (!$this->isConnected() || $this->isError() || !$result) return;

			// TODO Improve this piece of code
			// This code trims values from char type columns
			$col_types = cubrid_column_types ($result);
			$col_names = cubrid_column_names ($result);
			$max = count ($col_types);

			for ($count = 0; $count < $max; $count++) {
				if (preg_match ("/^char/", $col_types[$count]) > 0) {
					$char_type_fields[] = $col_names[$count];
				}
			}

			while ($tmp = cubrid_fetch ($result, CUBRID_OBJECT)) {
				if (is_array ($char_type_fields)) {
					foreach ($char_type_fields as $val) {
						$tmp->{$val} = rtrim ($tmp->{$val});
					}
				}

				if($arrayIndexEndValue) $output[$arrayIndexEndValue--] = $tmp;
				else $output[] = $tmp;
			}

			unset ($char_type_fields);

			if ($result) cubrid_close_request($result);

                        if($arrayIndexEndValue !== null && count($output) == 1) return $output[$arrayIndexEndValue + 1];
			else if (count($output) == 1) return $output[0];
			return $output;
		}

		/**
		 * @brief return the sequence value incremented by 1(auto_increment column only used in the CUBRID sequence table)
		 **/
		function getNextSequence()
		{
			$this->_makeSequence();

			$query = sprintf ("select \"%ssequence\".\"nextval\" as \"seq\" from db_root", $this->prefix);
			$result = $this->_query($query);
			$output = $this->_fetch($result);

			return $output->seq;
		}

		/**
		 * @brief return if the table already exists
		 **/
		function _makeSequence()
		{
			if($_GLOBALS['XE_EXISTS_SEQUENCE']) return;

			// check cubrid serial
			$query = sprintf('select count(*) as "count" from "db_serial" where name=\'%ssequence\'', $this->prefix);
			$result = $this->_query($query);
			$output = $this->_fetch($result);

			// if do not create serial
			if ($output->count == 0) {
				$query = sprintf('select max("a"."srl") as "srl" from '.
								 '( select max("document_srl") as "srl" from '.
								 '"%sdocuments" UNION '.
								 'select max("comment_srl") as "srl" from '.
								 '"%scomments" UNION '.
								 'select max("member_srl") as "srl" from '.
								 '"%smember"'.
								  ') as "a"', $this->prefix, $this->prefix, $this->prefix);

				$result = $this->_query($query);
				$output = $this->_fetch($result);
				$srl = $output->srl;
				if ($srl < 1) {
					$start = 1;
				}
				else {
					$start = $srl + 1000000;
				}

				// create sequence
				$query = sprintf('create serial "%ssequence" start with %s increment by 1 minvalue 1 maxvalue 10000000000000000000000000000000000000 nocycle;', $this->prefix, $start);
				$this->_query($query);
			}

			$_GLOBALS['XE_EXISTS_SEQUENCE'] = true;
		}


		/**
		 * brief return a table if exists
		 **/
		function isTableExists ($target_name)
		{
			if($target_name == 'sequence') {
				$query = sprintf ("select \"name\" from \"db_serial\" where \"name\" = '%s%s'", $this->prefix, $target_name);
			}
			else {
				$query = sprintf ("select \"class_name\" from \"db_class\" where \"class_name\" = '%s%s'", $this->prefix, $target_name);
			}

			$result = $this->_query ($query);
			if (cubrid_num_rows($result) > 0) {
				$output = true;
			}
			else {
				$output = false;
			}

			if ($result) cubrid_close_request ($result);

			return $output;
		}

		/**
		 * @brief add a column to the table
		 **/
		function addColumn($table_name, $column_name, $type = 'number', $size = '', $default = '', $notnull = false)
		{
			$type = strtoupper($this->column_type[$type]);
			if ($type == 'INTEGER') $size = '';

			$query = sprintf ("alter class \"%s%s\" add \"%s\" ", $this->prefix, $table_name, $column_name);

			if ($type == 'char' || $type == 'varchar') {
				if ($size) $size = $size * 3;
			}

			if ($size) {
				$query .= sprintf ("%s(%s) ", $type, $size);
			}
			else {
				$query .= sprintf ("%s ", $type);
			}

			if ($default) {
				if ($type == 'INTEGER' || $type == 'BIGINT' || $type=='INT') {
					$query .= sprintf ("default %d ", $default);
				}
				else {
					$query .= sprintf ("default '%s' ", $default);
				}
			}

			if ($notnull) $query .= "not null ";

			$this->_query ($query);
		}

		/**
		 * @brief drop a column from the table
		 **/
		function dropColumn ($table_name, $column_name)
		{
			$query = sprintf ("alter class \"%s%s\" drop \"%s\" ", $this->prefix, $table_name, $column_name);

			$this->_query ($query);
		}

		/**
		 * @brief return column information of the table
		 **/
		function isColumnExists ($table_name, $column_name)
		{
			$query = sprintf ("select \"attr_name\" from \"db_attribute\" where ".  "\"attr_name\" ='%s' and \"class_name\" = '%s%s'", $column_name, $this->prefix, $table_name);
			$result = $this->_query ($query);

			if (cubrid_num_rows ($result) > 0) $output = true;
			else $output = false;

			if ($result) cubrid_close_request ($result);

			return $output;
		}

		/**
		 * @brief add an index to the table
		 * $target_columns = array(col1, col2)
		 * $is_unique? unique : none
		 **/
		function addIndex ($table_name, $index_name, $target_columns, $is_unique = false)
		{
			if (!is_array ($target_columns)) {
				$target_columns = array ($target_columns);
			}

			$query = sprintf ("create %s index \"%s\" on \"%s%s\" (%s);", $is_unique?'unique':'', $this->prefix .$index_name, $this->prefix, $table_name, '"'.implode('","',$target_columns).'"');

			$this->_query ($query);
		}

		/**
		 * @brief drop an index from the table
		 **/
		function dropIndex ($table_name, $index_name, $is_unique = false)
		{
			$query = sprintf ("drop %s index \"%s\" on \"%s%s\"", $is_unique?'unique':'', $this->prefix .$index_name, $this->prefix, $table_name);

			$this->_query($query);
		}

		/**
		 * @brief return index information of the table
		 **/
		function isIndexExists ($table_name, $index_name)
		{
			$query = sprintf ("select \"index_name\" from \"db_index\" where ".  "\"class_name\" = '%s%s' and \"index_name\" = '%s' ", $this->prefix, $table_name, $this->prefix .$index_name);
			$result = $this->_query ($query);

			if ($this->isError ()) return false;

			$output = $this->_fetch ($result);

			if (!$output) return false;
			return true;
		}

		/**
		 * @brief creates a table by using xml file
		 **/
		function createTableByXml ($xml_doc)
		{
			return $this->_createTable ($xml_doc);
		}

		/**
		 * @brief creates a table by using xml file
		 **/
		function createTableByXmlFile ($file_name)
		{
			if (!file_exists ($file_name)) return;
			// read xml file
			$buff = FileHandler::readFile ($file_name);
			return $this->_createTable ($buff);
		}

		/**
		 * @brief create table by using the schema xml
		 *
		 * type : number, varchar, tinytext, text, bigtext, char, date, \n
		 * opt : notnull, default, size\n
		 * index : primary key, index, unique\n
		 **/
		function _createTable ($xml_doc)
		{
			// xml parsing
			$oXml = new XmlParser();
			$xml_obj = $oXml->parse($xml_doc);
			// Create a table schema
			$table_name = $xml_obj->table->attrs->name;

			// if the table already exists exit function
			if ($this->isTableExists($table_name)) return;

			// If the table name is sequence, it creates a serial
			if ($table_name == 'sequence') {
				$query = sprintf ('create serial "%s" start with 1 increment by 1'.
								  ' minvalue 1 '.
								  'maxvalue 10000000000000000000000000000000000000'.  ' nocycle;', $this->prefix.$table_name);

				return $this->_query($query);
			}


			$table_name = $this->prefix.$table_name;

			$query = sprintf ('create class "%s";', $table_name);
			$this->_query ($query);

			if (!is_array ($xml_obj->table->column)) {
				$columns[] = $xml_obj->table->column;
			}
			else {
				$columns = $xml_obj->table->column;
			}

			$query = sprintf ("alter class \"%s\" add attribute ", $table_name);

			foreach ($columns as $column) {
				$name = $column->attrs->name;
				$type = $column->attrs->type;
				$size = $column->attrs->size;
				$notnull = $column->attrs->notnull;
				$primary_key = $column->attrs->primary_key;
				$index = $column->attrs->index;
				$unique = $column->attrs->unique;
				$default = $column->attrs->default;

				switch ($this->column_type[$type]) {
					case 'integer' :
						$size = null;
						break;
					case 'text' :
						$size = null;
						break;
				}

				if (isset ($default) && ($type == 'varchar' || $type == 'char' ||
				  $type == 'text' || $type == 'tinytext' || $type == 'bigtext')) {
					$default = sprintf ("'%s'", $default);
				}

				if ($type == 'varchar' || $type == 'char') {
					if($size) $size = $size * 3;
				}


				$column_schema[] = sprintf ('"%s" %s%s %s %s',
									$name,
									$this->column_type[$type],
									$size?'('.$size.')':'',
									isset($default)?"default ".$default:'',
									$notnull?'not null':'');

				if ($primary_key) {
					$primary_list[] = $name;
				}
				else if ($unique) {
					$unique_list[$unique][] = $name;
				}
				else if ($index) {
					$index_list[$index][] = $name;
				}
			}

			$query .= implode (',', $column_schema).';';
			$this->_query ($query);

			if (count ($primary_list)) {
				$query = sprintf ("alter class \"%s\" add attribute constraint ".  "\"pkey_%s\" PRIMARY KEY(%s);", $table_name, $table_name, '"'.implode('","',$primary_list).'"');
				$this->_query ($query);
			}

			if (count ($unique_list)) {
				foreach ($unique_list as $key => $val) {
					$query = sprintf ("create unique index \"%s\" on \"%s\" ".  "(%s);", $this->prefix .$key, $table_name, '"'.implode('","', $val).'"');
					$this->_query ($query);
				}
			}

			if (count ($index_list)) {
				foreach ($index_list as $key => $val) {
					$query = sprintf ("create index \"%s\" on \"%s\" (%s);", $this->prefix .$key, $table_name, '"'.implode('","',$val).'"');
					$this->_query ($query);
				}
			}
		}




		/**
		 * @brief handles insertAct
		 **/
		function _executeInsertAct($queryObject)
		{
			$query = $this->getInsertSql($queryObject);
			if(is_a($query, 'Object')) return;

			$query .= (__DEBUG_QUERY__&1 && $output->query_id)?sprintf (' '.$this->comment_syntax, $this->query_id):'';

			$result = $this->_query ($query);
			if ($result && !$this->transaction_started) {
				@cubrid_commit ($this->fd);
			}

			return $result;
		}

		/**
		 * @brief handles updateAct
		 **/
		function _executeUpdateAct($queryObject)
		{
			$query = $this->getUpdateSql($queryObject);
			if(is_a($query, 'Object')) return;

			$result = $this->_query($query);

			if ($result && !$this->transaction_started) @cubrid_commit ($this->fd);

			return $result;
		}


		/**
		 * @brief handles deleteAct
		 **/
		function _executeDeleteAct($queryObject)
		{
			$query =  $this->getDeleteSql($queryObject);
			if(is_a($query, 'Object')) return;

			$result = $this->_query ($query);

			if ($result && !$this->transaction_started) @cubrid_commit ($this->fd);

			return $result;
		}

		/**
		 * @brief Handle selectAct
		 *
		 * to get a specific page list easily in select statement,\n
		 * a method, navigation, is used
		 **/
		// TODO Rewrite with Query object as input
		 function _executeSelectAct($queryObject){
			$query = $this->getSelectSql($queryObject);
			if(is_a($query, 'Object')) return;

			$query .= (__DEBUG_QUERY__&1 && $queryObject->query_id)?sprintf (' '.$this->comment_syntax, $this->query_id):'';
			$result = $this->_query ($query);

			if ($this->isError ())
                                return $this->queryError($queryObject);
			else
                            return $this->queryPageLimit($queryObject, $result);
		}

		function queryError($queryObject){
			if ($queryObject->getLimit() && $queryObject->getLimit()->isPageHandler()){
					$buff = new Object ();
					$buff->total_count = 0;
					$buff->total_page = 0;
					$buff->page = 1;
					$buff->data = array ();
					$buff->page_navigation = new PageHandler (/*$total_count*/0, /*$total_page*/1, /*$page*/1, /*$page_count*/10);//default page handler values
					return $buff;
				}else
					return;
		}

		function queryPageLimit($queryObject, $result){
			 	if ($queryObject->getLimit() && $queryObject->getLimit()->isPageHandler()) {
		 		// Total count
		 		$count_query = sprintf('select count(*) as "count" %s %s', 'FROM ' . $queryObject->getFromString(), ($queryObject->getWhereString() === '' ? '' : ' WHERE '. $queryObject->getWhereString()));
				if ($queryObject->getGroupByString() != '') {
					$count_query = sprintf('select count(*) as "count" from (%s) xet', $count_query);
				}

				$count_query .= (__DEBUG_QUERY__&1 && $output->query_id)?sprintf (' '.$this->comment_syntax, $this->query_id):'';
				$result_count = $this->_query($count_query);
				$count_output = $this->_fetch($result_count);
				$total_count = (int)$count_output->count;

				// Total pages
				if ($total_count) {
					$total_page = (int) (($total_count - 1) / $queryObject->getLimit()->list_count) + 1;
				}	else	$total_page = 1;

		 		$virtual_no = $total_count - ($queryObject->getLimit()->page - 1) * $queryObject->getLimit()->list_count;
		 		$data = $this->_fetch($result, $virtual_no);

		 		$buff = new Object ();
				$buff->total_count = $total_count;
				$buff->total_page = $total_page;
				$buff->page = $queryObject->getLimit()->page->getValue();
				$buff->data = $data;
				$buff->page_navigation = new PageHandler($total_count, $total_page, $queryObject->getLimit()->page->getValue(), $queryObject->getLimit()->page_count);
			}else{
				$data = $this->_fetch($result);
				$buff = new Object ();
				$buff->data = $data;
			}
			return $buff;
		}

		function getParser(){
			return new DBParser('"');
		}
	}

	return new DBCubrid;
?>
