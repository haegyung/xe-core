<?php
	/**
	 * @class XmlLangParser
	 * @author NHN (developers@xpressengine.com)
	 * @brief change to lang php file from xml.
     * @version 0.1
	 **/

	class XmlLangParser extends XmlParser {
		var $compiled_path = './files/cache/lang/'; // / directory path for compiled cache file
		var $xml_file = NULL; // / Target xml file
		var $php_file = NULL; // / Target php file
		var $code;
		var $lang_types;
		var $lang_type;

		/**
		 * @brief constructor
		 **/
		function XmlLangParser($xml_file, $lang_type) {
			$this->lang_type = $lang_type;
			$this->xml_file = $xml_file;
			$this->php_file = $this->_getCompiledFileName($lang_type);
		}

		/**
		 * @brief compile a xml_file only when a corresponding php lang file does not exists or is outdated
         * @return Returns compiled php file.
         **/
		function compile() {
			if(!file_exists($this->xml_file)) return false;
			if(!file_exists($this->php_file)){
				$this->_compile();
			} else { 
				if(filemtime($this->xml_file)>filemtime($this->php_file)) $this->_compile();
				else return $this->php_file;
			}

			return $this->_writefile() ? $this->php_file : false;
		}

		/**
		 * @brief compile a xml_file
		 **/
		function _compile() {
			$lang_selected = Context::loadLangSelected();
			$this->lang_types = array_keys($lang_selected);

			// read xml file
			$buff = FileHandler::readFile($this->xml_file);
			$buff = str_replace('xml:lang','xml_lang',$buff);

			// xml parsing
			$xml_obj = parent::parse($buff);

			$item = $xml_obj->lang->item;
			if(!is_array($item)) $item = array($item);
			foreach($item as $i){
				$this->_parseItem($i, $var='$lang->%s');
			}
		}

		/**
		 * @brief writing cache file
		 **/
		function _writeFile(){
			if(!$this->code) return;
			FileHandler::writeFile($this->php_file, "<?php\n".$this->code);
			return false;
		}

		/**
		 * @brief Parsing item node
		 **/
		function _parseItem($item, $var){
			$name = $item->attrs->name;
			$value = $item->value;
			$var = sprintf($var, $name);

			if($item->item) {
				$type = $item->attrs->type;

				if($type == 'array'){
					$this->code .= $var."=array();\n"; 
					$var .= '[\'%s\']';
				}else{
					$this->code .= $var."=new stdClass;\n"; 
					$var .= '->%s';
				}

				$items = $item->item;
				if(!is_array($items)) $item = array($items);
				foreach($items as $item){
					$this->_parseItem($item, $var);
				}

			} else {
				$code = $this->_parseValues($value, $var);	
				$this->code .= $code;
			}
		}

		/**
		 * @brief Parsing value nodes
		 **/
		function _parseValues($nodes, $var) {
			if(!is_array($nodes)) $nodes = array($nodes);

			$value = array();
			foreach($nodes as $node){
				$return = $this->_parseValue($node, $var);
				if($return && is_array($return)) $value = array_merge($value, $return);
			}

			if($value[$this->lang_type]) return $value[$this->lang_type];
			else if($value['en']) return $value['en'];
			else if($value['ko']) return $value['ko'];

			foreach($this->lang_types as $lang_type) {
				if($lang_type == 'en' || $lang_type == 'ko' || $lang_type == $this->lang_type) continue;
				if($value[$lang_type]) return $value[$lang_type];
			}

			return '';
		}

		/**
		 * @brief Parsing value node
		 **/
		function _parseValue($node, $var) {
			$lang_type = $node->attrs->xml_lang;
			$value = $node->body;
			if(!$value) return false;

			$var .= '=\'' . str_replace("'","\'",$value) . "';\n";
			return array($lang_type=>$var);
		}

		/**
		 * @brief get cache file name
		 **/
		function _getCompiledFileName($lang_type, $type='php') {
			return sprintf('%s%s.%s.php',$this->compiled_path, md5($this->xml_file), $lang_type);
		}
	}
