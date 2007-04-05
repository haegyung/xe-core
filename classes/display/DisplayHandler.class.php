<?php
    /**
    * @class DisplayHandler
    * @author zero (zero@nzeo.com)
    * @brief 데이터 출력을 위한 class (XML/HTML 데이터를 구분하여 출력)
    *
    * Response Method에 따라서 html or xml 출력방법을 결정한다
    * xml : oModule의 variables를 simple xml 로 출력
    * html : oModule의 template/variables로 html을 만들고 contents_html로 처리
    *        plugin이나 layout의 html과 연동하여 출력
    **/

    class DisplayHandler extends Handler {

        var $content_size = 0; ///< 출력하는 컨텐츠의 사이즈

        /**
         * @brief 모듈객체를 받아서 content 출력
         **/
        function printContent(&$oModule) {
            // header 출력
            $this->_printHeader();

            // request method에 따른 처리
            $content = $this->getContent($oModule);

            // 요청방식에 따라 출력을 별도로
            if(Context::getResponseMethod()!="XMLRPC") {

                Context::set('content', $content);

                // 레이아웃을 컴파일
                require_once("./classes/template/TemplateHandler.class.php");
                $oTemplate = new TemplateHandler();
                $zbxe_final_content = $oTemplate->compile($oModule->getLayoutPath(), $oModule->getLayoutFile());

                // 각 플러그인, 에디터 컴포넌트의 코드 변경
                $oContext = &Context::getInstance();
                $zbxe_final_content= $oContext->transContent($zbxe_final_content);

                // 최종 결과를 common_layout에 넣어버림 
                Context::set('zbxe_final_content', $zbxe_final_content);
                $output = $oTemplate->compile('./common/tpl', 'common_layout');

                // rewrite module을 위한 코드 
                // $output = preg_replace("/([0-9]+)\?(.*)/",'?\\2',$output);

            } else {
                $output = $content;
            }

            // 애드온 실행
            $called_position = 'before_display_content';
            @include("./files/cache/activated_addons.cache.php");

            $this->content_size = strlen($output);

            // 컨텐츠 출력
            if(Context::getResponseMethod()!="XMLRPC") {
                //ini_set('zlib.output_compression_level',3);
                //ob_start('ob_gzhandler');
                print trim($output);
                //ob_end_flush();
            } else {
                print trim($output);
            }

            // 디버깅 데이터 출력
            $this->_debugOutput();
        }

        /**
         * @brief 모듈 객체의 content return
         **/
        function getContent(&$oModule) {
            return $this->_toDoc($oModule);
        }

        /**
         * @brief 모듈 객체의 content return
         **/
        function _toDoc(&$oModule) {
            if(Context::getRequestMethod() == 'XMLRPC') $content = $this->_toXmlDoc($oModule);
            else $content = $this->_toHTMLDoc($oModule);
            return $content;
        }

        /**
         * @brief RequestMethod가 XML이면 XML 데이터로 컨텐츠 생성
         **/
        function _toXmlDoc(&$oModule) {
            $xmlDoc  = "<response>\n";
            $xmlDoc .= sprintf("<error>%s</error>\n",$oModule->getError());
            $xmlDoc .= sprintf("<message>%s</message>\n",$oModule->getMessage());

            $variables = $oModule->getVariables();

            if(count($variables)) {
                foreach($variables as $key => $val) {
                    if(is_string($val)) $val = '<![CDATA['.$val.']]>';
                    $xmlDoc .= "<{$key}>{$val}</{$key}>\n";
                }
            }

            $xmlDoc .= "</response>";

            return $xmlDoc;
        }

        /**
         * @brief RequestMethod가 XML이 아니면 html 컨텐츠 생성
         **/
        function _toHTMLDoc(&$oModule) {
            // template handler 객체 생성
            require_once("./classes/template/TemplateHandler.class.php");
            $oTemplate = new TemplateHandler();

            // module tpl 변환
            $template_path = $oModule->getTemplatePath();
            $tpl_file = $oModule->getTemplateFile();
            return $oTemplate->compile($template_path, $tpl_file);
        }

        /**
         * @brief content size return
         **/
        function getContentSize() {
            return $this->content_size;
        }

        /**
         * @brief 디버그 모드일 경우 디버기 메세지 출력
         *
         * __DEBUG__가 true일 경우 각 부분의 실행시간등을 debugPrint 함수를 이용해서 출력\n
         * 개발시나 테스트시에 config/config.inc.php의 __DEBUG__를 true로 세팅하고\n
         * tail -f ./files/_debug_message.php로 하여 console로 확인하면 편리함\n
         **/
        function _debugOutput() {
            if(!__DEBUG__) return;
            $end = getMicroTime();

            $buff  = "\n\n** Debug at ".date('Y-m-d H:i:s')." ************************************************************\n";
            $buff .= "\n- Request/ Response info\n";
            $buff .= sprintf("\tRequest URI \t\t\t: %s:%s%s%s%s\n", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']?'?':'', $_SERVER['QUERY_STRING']);
            $buff .= sprintf("\tRequest method \t\t\t: %s\n", $_SERVER['REQUEST_METHOD']);
            $buff .= sprintf("\tResponse method \t\t: %s\n", Context::getResponseMethod());
            $buff .= sprintf("\tResponse contents size\t\t: %d byte\n", $this->getContentSize());
            if($GLOBALS['__db_queries__']) {
            $buff .= "\n- DB Queries\n";
            $buff .= $GLOBALS['__db_queries__'];
            }
            $buff .= "\n- Elapsed time\n";

            if($GLOBALS['__db_elapsed_time__']) $buff .= sprintf("\tDB queries elapsed time\t\t: %0.5f sec\n", $GLOBALS['__db_elapsed_time__']);
            $buff .= sprintf("\tclass file load elapsed time \t: %0.5f sec\n", __RequireClassEndTime__-__RequireClassStartTime__);
            $buff .= sprintf("\tTemplate compile elapsed time\t: %0.5f sec\n", $GLOBALS['__template_elapsed__']);
            $buff .= sprintf("\tXmlParse compile elapsed time\t: %0.5f sec\n", $GLOBALS['__xmlparse_elapsed__']);
            $buff .= sprintf("\tPHP elapsed time \t\t: %0.5f sec\n", $end-__StartTime__-$GLOBALS['__template_elapsed__']-$GLOBALS['__xmlparse_elapsed__']-$GLOBALS['__db_elapsed_time__']-(__RequireClassEndTime__-__RequireClassStartTime__));
            $buff .= sprintf("\tTotal elapsed time \t\t: %0.5f sec", $end-__StartTime__);

            debugPrint($buff, false);
        }

        /**
         * @brief RequestMethod에 맞춰 헤더 출력
         ***/
        function _printHeader() {
            if(Context::getResponseMethod() == 'XMLRPC') return $this->_printXMLHeader();
            else return $this->_printHTMLHeader();
        }

        /**
         * @brief xml header 출력 (utf8 고정)
         **/
        function _printXMLHeader() {
            header("Content-Type: text/xml; charset=UTF-8");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }

        /**
         * @brief html header 출력 (utf8 고정)
         **/
        function _printHTMLHeader() {
            header("Content-Type: text/html; charset=UTF-8");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }
?>
