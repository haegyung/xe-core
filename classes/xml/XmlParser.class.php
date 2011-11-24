<?php
    /**
     * @class XmlParser 
     * @author NHN (developers@xpressengine.com)
     * @brief class parsing a given xmlrpc request and creating a data object
     * @version 0.1
     *
     * @remarks { 
     * This class may drops unsupported xml lanuage attributes when multiple language attributes are given.
     * For example, if 'xml:lang='ko, en, ch, jp..' is given in a xml file, only ko will be left ignoring all other language
     * attributes when kor is only supported language. It seems to work fine now but we did not scrutinze any potential side effects,
     * }
     **/

    class XmlParser {

        var $oParser = NULL; ///< xml parser

        var $input = NULL; ///< input xml
        var $output = array(); ///< output object

        var $lang = "en"; // /< The default language type

        /**
         * @brief load a xml file specified by a filename and parse it to Return the resultant data object
         * @param[in] $filename a file path of file
         * @return Returns a data object containing data extracted from a xml file or NULL if a specified file does not exist
         **/
        function loadXmlFile($filename) {
            if(!file_exists($filename)) return;
            $buff = FileHandler::readFile($filename);

            $oXmlParser = new XmlParser();
            return $oXmlParser->parse($buff);
        }

        /**
         * @brief parse xml data to extract values from it and construct data object
         * @param[in] a data buffer containing xml data
         * @return Returns a resultant data object or NULL in case of error
         **/
        function parse($input = '') {
            // Save the compile starting time for debugging
            if(__DEBUG__==3) $start = getMicroTime();

            $this->lang = Context::getLangType();

            $this->input = $input?$input:$GLOBALS['HTTP_RAW_POST_DATA'];
			$this->input = str_replace(array('',''),array('',''),$this->input);

            // extracts a supported language
            preg_match_all("/xml:lang=\"([^\"].+)\"/i", $this->input, $matches);

            // extracts the supported lanuage when xml:lang is used
            if(count($matches[1]) && $supported_lang = array_unique($matches[1])) {
                // if lang of the first log-in user doesn't exist, apply en by default if exists. Otherwise apply the first lang.
                if(!in_array($this->lang, $supported_lang)) {
                    if(in_array('en', $supported_lang)) {
                        $this->lang = 'en';
                    } else {
                        $this->lang = array_shift($supported_lang);
                    }
                }
            // uncheck the language if no specific language is set.
            } else {
				$this->lang = '';
            }

            $this->oParser = xml_parser_create('UTF-8');

            xml_set_object($this->oParser, $this);
            xml_set_element_handler($this->oParser, "_tagOpen", "_tagClosed");
            xml_set_character_data_handler($this->oParser, "_tagBody");

            xml_parse($this->oParser, $this->input);
            xml_parser_free($this->oParser);

            if(!count($this->output)) return;

            $output = array_shift($this->output);
            // Save compile starting time for debugging
            if(__DEBUG__==3) $GLOBALS['__xmlparse_elapsed__'] += getMicroTime() - $start;

            return $output;
        }


       /**
        * @brief start element handler.
        * @param[in] $parse an instance of parser
        * @param[in] $node_name a name of node
        * @param[in] $attrs attributes to be set
        */
        function _tagOpen($parser, $node_name, $attrs) {
            $obj->node_name = strtolower($node_name);
            $obj->attrs = $this->_arrToObj($attrs);

            array_push($this->output, $obj);
        }


       /**
        * @brief character data handler
        *  variable in the last element of this->output
        * @param[in] $parse an instance of parser
        * @param[in] $body a data to be added
        * @remark the first parameter, $parser ought to be remove since it is not used.
        */
        function _tagBody($parser, $body) {
            //if(!trim($body)) return;
            $this->output[count($this->output)-1]->body .= $body;
        }

       /**
        * @brief end element handler
        * @param[in] $parse an instance of parser
        * @param[in] $node_name name of xml node
        */
        function _tagClosed($parser, $node_name) {
            $node_name = strtolower($node_name);
            $cur_obj = array_pop($this->output);
            $parent_obj = &$this->output[count($this->output)-1];
            if($this->lang&&$cur_obj->attrs->{'xml:lang'}&&$cur_obj->attrs->{'xml:lang'}!=$this->lang) return;
            if($this->lang&&$parent_obj->{$node_name}->attrs->{'xml:lang'}&&$parent_obj->{$node_name}->attrs->{'xml:lang'}!=$this->lang) return;

            if(isset($parent_obj->{$node_name})) {
                $tmp_obj = $parent_obj->{$node_name};
                if(is_array($tmp_obj)) {
                    array_push($parent_obj->{$node_name}, $cur_obj);
                } else {
                    $parent_obj->{$node_name} = array();
                    array_push($parent_obj->{$node_name}, $tmp_obj);
                    array_push($parent_obj->{$node_name}, $cur_obj);
                }
            } else {
                $parent_obj->{$node_name} = $cur_obj;
            }
        }

        /**
        * @brief method to transfer values in an array to a data object       
        * @param[in] $arr data array 
        **/
        function _arrToObj($arr) {
            if(!count($arr)) return;
            foreach($arr as $key => $val) {
                $key = strtolower($key);
                $output->{$key} = $val;
            }
            return $output;
        }
    }
?>
