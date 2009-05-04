<?php
    /**
    * @class Context
    * @author zero (zero@nzeo.com)
    * @brief  Request Argument/환경변수등의 모든 Context를 관리
    * Context 클래스는 Context::methodname() 처럼 쉽게 사용하기 위해 만들어진 객체를 받아서
    * 호출하는 구조를 위해 이중 method 구조를 가지고 있다.
    **/

    define('FOLLOW_REQUEST_SSL',0);
    define('ENFORCE_SSL',1);
    define('RELEASE_SSL',2);

    class Context {

        var $allow_rewrite = false;  ///< @brief rewrite mod 사용에 대한 변수

        var $request_method = 'GET'; ///< @brief GET/POST/XMLRPC 중 어떤 방식으로 요청이 왔는지에 대한 값이 세팅. GET/POST/XML 3가지가 있음
        var $response_method = ''; ///< @brief HTML/XMLRPC 중 어떤 방식으로 결과를 출력할지 결정. (강제 지정전까지는 request_method를 따름)

        var $context = NULL; ///< @brief request parameter 및 각종 환경 변수등을 정리하여 담을 변수

        var $db_info = NULL; ///< @brief DB 정보
        var $ftp_info = NULL; ///< @brief FTP 정보

        var $ssl_actions = array(); ///< @brief ssl로 전송해야 할 action등록 (common/js/xml_handler.js에서 ajax통신시 활용)
        var $js_files = array(); ///< @brief display시에 사용하게 되는 js files의 목록
        var $css_files = array(); ///< @brief display시에 사용하게 되는 css files의 목록

        var $html_header = NULL; ///< @brief display시에 사용하게 되는 <head>..</head>내의 스크립트코드
	var $body_class = array(); ///< @brief display시에 사용하게 되는 <body> 안에 출력될 class
        var $body_header = NULL; ///< @brief display시에 사용하게 되는 <body> 바로 다음에 출력될 스크립트 코드
        var $html_footer = NULL; ///< @brief display시에 사용하게 되는 </body> 바로 앞에 추가될 코드

        var $path = ''; ///< zbxe의 경로

        /**
         * @brief 언어 정보
         * 기본으로 ko. HTTP_USER_AGENT나 사용자의 직접 세팅(쿠키이용)등을 통해 변경됨
         **/
        var $lang_type = ''; ///< 언어 종류
        var $lang = NULL; ///< 언어 데이터를 담고 있는 변수
        var $loaded_lang_files = array(); ///< 로딩된 언어파일의 목록 (재로딩을 피하기 위함)

        var $site_title = ''; ///< @brief 현 사이트의 browser title. Context::setBrowserTitle() 로 변경 가능

        var $get_vars = NULL; ///< @brief form이나 get으로 요청이 들어온 변수만 별도로 관리

        var $is_uploaded = false; ///< @brief 첨부파일이 업로드 된 요청이였는지에 대한 체크 플래그

        /**
         * @brief 유일한 Context 객체를 반환 (Singleton)
         * Context는 어디서든 객체 선언없이 사용하기 위해서 static 하게 사용
         **/
        function &getInstance() {
            static $theInstance;
            if(!isset($theInstance)) $theInstance = new Context();
            return $theInstance;
        }

        /**
         * @brief DB정보, Request Argument등을 세팅
         * Context::init()은 단 한번만 호출되어야 하며 init()시에 Request Argument, DB/언어/세션정보등의 모든 정보를 세팅한다
         **/
        function init() {
            // context 변수를 $GLOBALS의 변수로 지정
            $this->context = &$GLOBALS['__Context__'];
            $this->context->lang = &$GLOBALS['lang'];
            $this->context->_COOKIE = $_COOKIE;

            // Request Method 설정
            $this->_setRequestMethod();

            // Request Argument 설정
            $this->_setXmlRpcArgument();
            $this->_setJSONRequestArgument();
            $this->_setRequestArgument();
            $this->_setUploadedArgument();

            // 기본적인 DB정보 세팅
            $this->_loadDBInfo();

            // 설치가 되어 있다면 가상 사이트 정보를 구함
            if(Context::isInstalled()) {
                // site_module_info를 구함
                $oModuleModel = &getModel('module');
                $site_module_info = $oModuleModel->getDefaultMid();
                Context::set('site_module_info', $site_module_info);

                if($site_module_info->site_srl && isSiteID($site_module_info->vid)) Context::set('vid', $site_module_info->vid);
                $this->db_info->lang_type = $site_module_info->default_language;
                if(!$this->db_info->lang_type) $this->db_info->lang_type = 'en';
            }

            // 언어 파일 불러오기
            $lang_supported = $this->loadLangSelected();

            // 사용자의 쿠키 설정된 언어 타입 추출
            if($_COOKIE['lang_type']) $this->lang_type = $_COOKIE['lang_type'];

            // 사용자 설정 언어 타입이 없으면 기본 언어타입으로 지정
            if(!$this->lang_type) $this->lang_type = $this->db_info->lang_type;

            // 관리자 설정 언어값에 등록된 것이 아니라면 기본 언어로 변경
            if(!$this->lang_type) $this->lang_type = "en";

            Context::set('lang_supported', $lang_supported);
            $this->setLangType($this->lang_type);

            // module의 언어파일 강제 로드 (언어 type에 맞춰서)
            $this->loadLang(_XE_PATH_.'modules/module/lang');

            // 세션 핸들러 지정
            if($this->db_info->use_db_session != 'N') {
                $oSessionModel = &getModel('session');
                $oSessionController = &getController('session');
                session_set_save_handler(
                    array(&$oSessionController,"open"),
                    array(&$oSessionController,"close"),
                    array(&$oSessionModel,"read"),
                    array(&$oSessionController,"write"),
                    array(&$oSessionController,"destroy"),
                    array(&$oSessionController,"gc")
                );
            }
            session_start();


            // 인증 관련 정보를 Context와 세션에 설정
            if(Context::isInstalled()) {
                // 인증관련 데이터를 Context에 설정
                $oMemberModel = &getModel('member');
                $oMemberController = &getController('member');

                // 인증이 되어 있을 경우 유효성 체크
                if($oMemberModel->isLogged()) {
                    $oMemberController->setSessionInfo();

                // 인증이 되어 있지 않을 경우 자동 로그인 확인
                } elseif($_COOKIE['xeak']) {
                    $oMemberController->doAutologin();
                }

                $this->_set('is_logged', $oMemberModel->isLogged() );
                $this->_set('logged_info', $oMemberModel->getLoggedInfo() );
            }

            // 기본 언어파일 로드
            $this->lang = &$GLOBALS['lang'];
            $this->_loadLang(_XE_PATH_."common/lang/");

            // rewrite 모듈사용 상태 체크
            if(file_exists(_XE_PATH_.'.htaccess')&&$this->db_info->use_rewrite == 'Y') $this->allow_rewrite = true;
            else $this->allow_rewrite = false;

            // 기본 JS/CSS 등록
            $this->addJsFile("./common/js/jquery.js");
            $this->addJsFile("./common/js/x.js");
            $this->addJsFile("./common/js/common.js");
            $this->addJsFile("./common/js/xml_handler.js");
            $this->addJsFile("./common/js/xml_js_filter.js");
            $this->addCSSFile("./common/css/default.css");
            $this->addCSSFile("./common/css/button.css");

            // 관리자 페이지일 경우 관리자 공용 CSS 추가
            if(Context::get('module')=='admin' || strpos(Context::get('act'),'Admin')>0) $this->addCssFile("./modules/admin/tpl/css/admin.css", false);

            // rewrite module때문에 javascript에서 location.href 문제 해결을 위해 직접 실제 경로 설정
            if($_SERVER['REQUEST_METHOD'] == 'GET') {
                if($this->get_vars) {
                    foreach($this->get_vars as $key => $val) {
                        if(!$val) continue;
                        if(is_array($val)&&count($val)) {
                            foreach($val as $k => $v) {
                                $url .= ($url?'&':'').$key.'['.$k.']='.urlencode($v);
                            }
                        } else {
                            $url .= ($url?'&':'').$key.'='.urlencode($val);
                        }
                    }
                    Context::set('current_url',sprintf('%s?%s', $this->getRequestUri(), $url));
                } else {
                    Context::set('current_url',$this->getUrl());
                }
            } else {
                Context::set('current_url',$this->getRequestUri());
            }
            Context::set('request_uri',Context::getRequestUri());
        }

        /**
         * @brief DB및 기타 자원들의 close
         **/
        function close() {
            // Session Close
            if(function_exists('session_write_close')) session_write_close();

            // DB close
            $oDB = &DB::getInstance();
            if(is_object($oDB)&&method_exists($oDB, 'close')) $oDB->close();
        }

        /**
         * @brief DB의 및 기타 정보 load
         **/
        function loadDBInfo() {
            $oContext = &Context::getInstance();
            return $oContext->_loadDBInfo();
        }

        /**
         * @brief DB 정보를 설정하고 DB Type과 DB 정보를 return
         **/
        function _loadDBInfo() {
            if(!$this->isInstalled()) return;

            // db 정보 설정
            $db_config_file = $this->getConfigFile();
            if(file_exists($db_config_file)) @include($db_config_file);

            if(!$db_info->time_zone) $db_info->time_zone = date("O");
            if(!$db_info->use_optimizer || $db_info->use_optimizer != 'N') $db_info->use_optimizer = 'Y';
            else $db_info->use_optimizer = 'N';
            if(!$db_info->qmail_compatibility || $db_info->qmail_compatibility != 'Y') $db_info->qmail_compatibility = 'N';
            else $db_info->qmail_compatibility = 'Y';
            if(!$db_info->use_ssl) $db_info->use_ssl = 'none';

            $this->_setDBInfo($db_info);

            $GLOBALS['_time_zone'] = $db_info->time_zone;
            $GLOBALS['_qmail_compatibility'] = $db_info->qmail_compatibility;
            $this->set('_use_ssl', $db_info->use_ssl);
            if($db_info->http_port)
            {
                $this->set('_http_port',  $db_info->http_port);
            }
            if($db_info->https_port)
            {
                $this->set('_https_port',  $db_info->https_port);
            }
        }

        /**
         * @brief DB의 db_type을 return
         **/
        function getDBType() {
            $oContext = &Context::getInstance();
            return $oContext->_getDBType();
        }

        /**
         * @brief DB의 db_type을 return
         **/
        function _getDBType() {
            return $this->db_info->db_type;
        }

        /**
         * @brief DB 정보가 담긴 object를 return
         **/
        function setDBInfo($db_info) {
            $oContext = &Context::getInstance();
            $oContext->_setDBInfo($db_info);
        }

        /**
         * @brief DB 정보가 담긴 object를 return
         **/
        function _setDBInfo($db_info) {
            $this->db_info = $db_info;
        }

        /**
         * @brief DB 정보가 담긴 object를 return
         **/
        function getDBInfo() {
            $oContext = &Context::getInstance();
            return $oContext->_getDBInfo();
        }

        /**
         * @brief DB 정보가 담긴 object를 return
         **/
        function _getDBInfo() {
            return $this->db_info;
        }

        /**
         * @brief 기본 URL을 return
         **/
        function getDefaultUrl() {
            $db_info = Context::getDBInfo();
            return $db_info->default_url;
        }

        /**
         * @brief 지원되는 언어 파일 찾기
         **/
        function loadLangSupported() {
            static $lang_supported = null;
            if(is_null($lang_supported)) {
                $langs = file(_XE_PATH_.'common/lang/lang.info');
                foreach($langs as $val) {
                    list($lang_prefix, $lang_text) = explode(',',$val);
                    $lang_text = trim($lang_text);
                    $lang_supported[$lang_prefix] = $lang_text;
                }
            }
            return $lang_supported;
        }

        /**
         * @brief 설정한 언어 파일 찾기
         **/
        function loadLangSelected() {
            static $lang_selected = null;
            if(is_null($lang_selected)) {
                $orig_lang_file = _XE_PATH_.'common/lang/lang.info';
                $selected_lang_file = _XE_PATH_.'files/cache/lang_selected.info';
                if(!file_exists($selected_lang_file) || !filesize($selected_lang_file)) {
                    $buff = FileHandler::readFile($orig_lang_file);
                    FileHandler::writeFile($selected_lang_file, $buff);
                    $lang_selected = Context::loadLangSupported();
                } else {
                    $langs = file($selected_lang_file);
                    foreach($langs as $val) {
                        list($lang_prefix, $lang_text) = explode(',',$val);
                        $lang_text = trim($lang_text);
                        $lang_selected[$lang_prefix] = $lang_text;
                    }
                }
            }
            return $lang_selected;
        }

        /**
         * @brief SSO URL이 설정되어 있고 아직 SSO URL검사를 하지 않았다면 return true
         **/
        function checkSSO() {
            // GET 접속이 아니거나 설치가 안되어 있으면 패스
            if(Context::getRequestMethod()!='GET' || !Context::isInstalled()) return true;

            // DB info에 설정된 Default URL이 없다면 무조건 무사통과
            $default_url = trim($this->db_info->default_url);
            if(!$default_url) return true;
            if(substr($default_url,-1)!='/') $default_url .= '/';

            // SSO 검증을 요청 받는 사이트
            if($default_url == Context::getRequestUri()) {
                if(Context::get('default_url')) {
                    $url = base64_decode(Context::get('default_url'));
                    $url_info = parse_url($url);
                    $url_info['query'].= ($url_info['query']?'&':'').'SSOID='.session_id();
                    $redirect_url = sprintf('%s://%s%s%s?%s',$url_info['scheme'],$url_info['host'],$url_info['port']?':'.$url_info['port']:'',$url_info['path'], $url_info['query']);
                    header("location:".$redirect_url);
                    return false;
                }
            // SSO 검증을 요청하는 사이트
            } else {
                // SSO 결과를 받는 경우 session_name() 세팅
                if(Context::get('SSOID')) {
                    setcookie(session_name(), Context::get('SSOID'), 0, '/');
                    header("location:".getUrl('SSOID',''));
                    return false;
                // SSO 결과를 요청
                } else if($_COOKIE['sso']!=md5(Context::getRequestUri()) && !Context::get('SSOID')) {
                    setcookie('sso',md5(Context::getRequestUri()),0,'/');
                    $url = sprintf("%s?default_url=%s", $default_url, base64_encode(Context::getRequestUrl()));
                    header("location:".$url);
                    return false;
                }
            }

            return true;
        }

        /**
         * @biref FTP 정보가 등록되었는지 확인
         **/
        function isFTPRegisted() {
            $ftp_config_file = Context::getFTPConfigFile();
            if(file_exists($ftp_config_file)) return true;
            return false;
        }

        /**
         * @brief FTP 정보가 담긴 object를 return
         **/
        function getFTPInfo() {
            $oContext = &Context::getInstance();
            return $oContext->_getFTPInfo();
        }

        /**
         * @brief FTP 정보가 담긴 object를 return
         **/
        function _getFTPInfo() {
            if(!$this->isFTPRegisted()) return null;

            $ftp_config_file = $this->getFTPConfigFile();
            @include($ftp_config_file);
            return $ftp_info;
        }

        /**
         * @brief 사이트 title adding
         **/
        function addBrowserTitle($site_title) {
            if(!$site_title) return;
            $oContext = &Context::getInstance();
            $oContext->_addBrowserTitle($site_title);
        }

        /**
         * @brief 사이트 title adding
         **/
        function _addBrowserTitle($site_title) {
            if($this->site_title) $this->site_title .= ' - '.$site_title;
            else $this->site_title .= $site_title;
        }

        /**
         * @brief 사이트 title setting
         **/
        function setBrowserTitle($site_title) {
            if(!$site_title) return;
            $oContext = &Context::getInstance();
            $oContext->_setBrowserTitle($site_title);
        }

        /**
         * @brief 사이트 title setting
         **/
        function _setBrowserTitle($site_title) {
            $this->site_title = $site_title;
        }

        /**
         * @brief 사이트 title return
         **/
        function getBrowserTitle() {
            $oContext = &Context::getInstance();
            return $oContext->_getBrowserTitle();
        }

        /**
         * @brief 사이트 title return
         **/
        function _getBrowserTitle() {
            return $this->site_title;
        }

        /**
         * @brief 지정된 언어파일 로드
         **/
        function loadLang($path) {
            $oContext = &Context::getInstance();
            $oContext->_loadLang($path);
        }

        /**
         * @brief 지정된 언어파일 로드
         *
         * loaded_lang_files 변수를 이용하여 한번 로드된 파일을 다시 로드하지 않음
         **/
        function _loadLang($path) {
            global $lang;
            if(!$this->lang_type) return;
            if(substr($path,-1)!='/') $path .= '/';
            $filename = sprintf('%s%s.lang.php', $path, $this->lang_type);
            if(!file_exists($filename)) $filename = sprintf('%s%s.lang.php', $path, 'ko');
            if(!file_exists($filename)) return;
            if(!is_array($this->loaded_lang_files)) $this->loaded_lang_files = array();
            if(in_array($filename, $this->loaded_lang_files)) return;
            $this->loaded_lang_files[] = $filename;
            if(file_exists($filename)) @include($filename);
        }

        /**
         * @brief lang_type을 세팅 (기본 ko)
         **/
        function setLangType($lang_type = 'ko') {
            $oContext = &Context::getInstance();
            $oContext->_setLangType($lang_type);
            $_SESSION['lang_type'] = $lang_type;
        }

        /**
         * @brief lang_type을 세팅 (기본 ko)
         **/
        function _setLangType($lang_type = 'ko') {
            $this->lang_type = $lang_type;
            $this->_set('lang_type',$lang_type);
        }

        /**
         * @brief lang_type을 return
         **/
        function getLangType() {
            $oContext = &Context::getInstance();
            return $oContext->_getLangType();
        }

        /**
         * @brief lang_type을 return
         **/
        function _getLangType() {
            return $this->lang_type;
        }

        /**
         * @brief code에 해당하는 문자열을 return
         *
         * 만약 code에 해당하는 문자열이 없다면 code를 그대로 리턴
         **/
        function getLang($code) {
            if(!$code) return;
            if($GLOBALS['lang']->{$code}) return $GLOBALS['lang']->{$code};
            return $code;
        }

        /**
         * @brief 직접 lang 변수에 데이터를 추가
         **/
        function setLang($code, $val) {
            $GLOBALS['lang']->{$code} = $val;
        }

        /**
         * @brief object내의 variables의 문자열을 utf8로 변경
         **/
        function convertEncoding($source_obj) {
            $charset_list = array(
                'UTF-8', 'EUC-KR', 'CP949', 'ISO8859-1', 'EUC-JP', 'SHIFT_JIS', 'CP932',
                'EUC-CN', 'HZ', 'GBK', 'GB18030', 'EUC-TW', 'BIG5', 'CP950', 'BIG5-HKSCS',
                'ISO2022-CN', 'ISO2022-CN-EXT', 'ISO2022-JP', 'ISO2022-JP-2', 'ISO2022-JP-1',
                'ISO8859-6', 'ISO8859-8', 'JOHAB', 'ISO2022-KR', 'CP1255', 'CP1256', 'CP862',
                'ASCII', 'ISO8859-1', 'ISO8850-2', 'ISO8850-3', 'ISO8850-4', 'ISO8850-5',
                'ISO8850-7', 'ISO8850-9', 'ISO8850-10', 'ISO8850-13', 'ISO8850-14',
                'ISO8850-15', 'ISO8850-16', 'CP1250', 'CP1251', 'CP1252', 'CP1253', 'CP1254',
                'CP1257', 'CP850', 'CP866',
            );

            $obj = clone($source_obj);

            for($i=0;$i<count($charset_list);$i++) {
                $charset = $charset_list[$i];
                $flag = true;
                foreach($obj as $key=>$val) {
                    if(!$val) continue;
                    if($val && iconv($charset,$charset,$val)!=$val) $flag = false;
                }
                if($flag == true) {
                    if($charset == 'UTF-8') return $obj;
                    foreach($obj as $key => $val) $obj->{$key} = iconv($charset,'UTF-8',$val);
                    return $obj;
                }
            }
            return $obj;
        }

        /**
         * @brief 특정 문자열만 utf-8로 변경
         **/
        function convertEncodingStr($str) {
            $obj->str = $str;
            $obj = Context::convertEncoding($obj);
            return $obj->str;
        }

        /**
         * @brief response method를 강제로 지정 (기본으로는 request method를 이용함)
         *
         * method의 종류에는 HTML/ TEXT/ XMLRPC/ JSON가 있음
         **/
        function setResponseMethod($method = "HTML") {
            $oContext = &Context::getInstance();
            return $oContext->_setResponseMethod($method);
        }

        function _setResponseMethod($method = "HTML") {
            $this->response_method = $method;
        }

        /**
         * @brief response method 값을 return
         *
         * method의 종류에는 HTML/ TEXT/ XMLRPC가 있음
         * 별도로 response method를 지정하지 않았다면 request method로 판단하여 결과 return
         **/
        function getResponseMethod() {
            $oContext = &Context::getInstance();
            return $oContext->_getResponseMethod();
        }

        function _getResponseMethod() {
            if($this->response_method) return $this->response_method;

            $RequestMethod = $this->_getRequestMethod();
            if($RequestMethod=="XMLRPC") return "XMLRPC";
            else if($RequestMethod=="JSON") return "JSON";
            return "HTML";
        }

        /**
         * @brief request method가 어떤것인지 판단하여 저장 (GET/POST/XMLRPC/JSON)
         **/
        function setRequestMethod($type) {
            $oContext = &Context::getInstance();
            $oContext->_setRequestMethod($type);
        }


        /**
         * @brief request method가 어떤것인지 판단하여 저장 (GET/POST/XMLRPC/JSON)
         **/
        function _setRequestMethod($type = '') {
            if($type) return $this->request_method = $type;

            if(strpos($_SERVER['CONTENT_TYPE'],'json')) return $this->request_method = 'JSON';
            if($GLOBALS['HTTP_RAW_POST_DATA']) return $this->request_method = "XMLRPC";

            $this->request_method = $_SERVER['REQUEST_METHOD'];
        }

        /**
         * @brief GET/POST방식일 경우 처리
         **/
        function _setRequestArgument() {
            if($this->_getRequestMethod() == 'XMLRPC' || $this->_getRequestMethod() == 'JSON') return;
            if(!count($_REQUEST)) return;

            foreach($_REQUEST as $key => $val) {
                if($val === "") continue;
                $val = $this->_filterRequestVar($key, $val);
                if($this->_getRequestMethod()=='GET'&&isset($_GET[$key])) $set_to_vars = true;
                elseif($this->_getRequestMethod()=='POST'&&isset($_POST[$key])) $set_to_vars = true;
                else $set_to_vars = false;
                $this->_set($key, $val, $set_to_vars);
            }
        }

        /**
         * @brief JSON 방식일 경우 처리
         **/
        function _setJSONRequestArgument() {
            if($this->_getRequestMethod() != 'JSON') return;
//            if(!$GLOBALS['HTTP_RAW_POST_DATA']) return;

            $params = array();
            parse_str($GLOBALS['HTTP_RAW_POST_DATA'],$params);

            foreach($params as $key => $val) {
                $val = $this->_filterRequestVar($key, $val,0);
                $this->_set($key, $val, true);
            }
        }

        /**
         * @brief XML RPC일때
         **/
        function _setXmlRpcArgument() {
            if($this->_getRequestMethod() != 'XMLRPC') return;
            $oXml = new XmlParser();
            $xml_obj = $oXml->parse();

            $params = $xml_obj->methodcall->params;
            unset($params->node_name);

            unset($params->attrs);
            if(!count($params)) return;
            foreach($params as $key => $obj) {
                $val = $this->_filterRequestVar($key, $obj->body,0);
                $this->_set($key, $val, true);
            }
        }

        /**
         * @brief 변수명에 따라서 필터링 처리
         * _srl, page, cpage등의 변수는 integer로 형변환
         **/
        function _filterRequestVar($key, $val, $do_stripslashes = 1) {
            if( ($key == "page" || $key == "cpage" || substr($key,-3)=="srl")) return !preg_match('/^[0-9,]+$/',$val)?(int)$val:$val;
            if(is_array($val) && count($val) ) {
                foreach($val as $k => $v) {
                    if($do_stripslashes && version_compare(PHP_VERSION, "5.9.0", "<") && get_magic_quotes_gpc()) $v = stripslashes($v);
                    $v = trim($v);
                    $val[$k] = $v;
                }
            } else {
                if($do_stripslashes && version_compare(PHP_VERSION, "5.9.0", "<") && get_magic_quotes_gpc()) $val = stripslashes($val);
                $val = trim($val);
            }
            return $val;
        }

        /**
         * @brief 업로드 되었을 경우 return true
         **/
        function isUploaded() {
            $oContext = &Context::getInstance();
            return $oContext->_isUploaded();
        }

        /**
         * @brief 업로드 되었을 경우 return true
         **/
        function _isUploaded() {
            return $this->is_uploaded;
        }

        /**
         * @brief 업로드된 파일이 있을 경우도 역시 context에 통합 처리 (단 정상적인 업로드인지 체크)
         **/
        function _setUploadedArgument() {
            if($this->_getRequestMethod() != 'POST') return;
            if(!preg_match("/multipart\/form-data/i",$_SERVER['CONTENT_TYPE'])) return;
            if(!$_FILES) return;

            foreach($_FILES as $key => $val) {
                $tmp_name = $val['tmp_name'];
                if(!$tmp_name || !is_uploaded_file($tmp_name)) continue;
                $this->_set($key, $val, true);
                $this->is_uploaded = true;
            }
        }

        /**
         * @brief Request Method값을 return (GET/POST/XMLRPC/JSON);
         **/
        function getRequestMethod() {
            $oContext = &Context::getInstance();
            return $oContext->_getRequestMethod();
        }

        /**
         * @brief Request Method값을 return (GET/POST/XMLRPC/JSON);
         **/
        function _getRequestMethod() {
            return $this->request_method;
        }

        /**
         * @brief 현재 요청된 full url을 return
         **/
        function getRequestUrl() {
            static $url = null;
            if(is_null($url)) {
                $url = Context::getRequestUri();
                if(count($_GET)) {
                    foreach($_GET as $key => $val) $vars[] = $key.'='.$val;
                    $url .= '?'.implode('&',$vars);
                }
            }
            return $url;
        }

        /**
         * @brief 요청받은 url에 args_list를 적용하여 return
         **/
        function getUrl($num_args=0, $args_list=array(), $domain = null) {
            $oContext = &Context::getInstance();
            return $oContext->_getUrl($num_args, $args_list, $domain);
        }

        /**
         * @brief 요청받은 url에 args_list를 적용하여 return
         **/
        function _getUrl($num_args=0, $args_list=array(), $domain = null) {
            static $site_module_info = null;
            if($domain) $is_site = true;
            else $is_site = false;

            if(is_null($site_module_info)) {
                $site_module_info = Context::get('site_module_info');
            }

            // SiteID 요청시 전처리
            if($domain && isSiteID($domain)) {
                $vid = $domain;
                $domain = '';
            } 

            // SiteID가 요청되지 않았다면 현재 site_module_info에서 SiteID 판별
            if(!$vid && $site_module_info->domain && isSiteID($site_module_info->domain)) {
                $vid = $site_module_info->domain;
            }

            if(!$domain) {
                if($site_module_info->domain && !isSiteID($site_module_info->domain)) $domain = $site_module_info->domain;
                else {
                    if($this->db_info->default_url) $domain = $this->db_info->default_url;
                    else if(!$domain) $domain = Context::getRequestUri();
                }
            }
            $domain = preg_replace('/^(http|https):\/\//i','', trim($domain));
            if(substr($domain,-1) != '/') $domain .= '/';

            if(!$this->get_vars || $args_list[0]=='') {
                $get_vars = null;
                if(is_array($args_list) && $args_list[0]=='') {
                    array_shift($args_list);
                    $num_args = count($args_list);
                }
            } else {
                $get_vars = get_object_vars($this->get_vars);
            }

            for($i=0;$i<$num_args;$i=$i+2) {
                $key = $args_list[$i];
                $val = trim($args_list[$i+1]);
                if(!isset($val)) {
                  unset($get_vars[$key]);
                  continue;
                }
                $get_vars[$key] = $val;
            }
            unset($get_vars['vid']);
            unset($get_vars['rnd']);
            if(isset($get_vars['page'])&&$get_vars['page']<2) unset($get_vars['page']);

            /* member module중의 쪽지함/친구 관리 기능이 communication 모듈로 이전하여 하위 호환성을 위한 act값 변경 */
            if($get_vars['act'] == 'dispMemberFriend') $get_vars['act'] = 'dispCommunicationFriend';
            elseif($get_vars['act'] == 'dispMemberMessages') $get_vars['act'] = 'dispCommunicationMessages';
            /* 기존의 action의 값이 바뀌어서 이를 강제 변경 */
            elseif($get_vars['act'] == 'dispDocumentAdminManageDocument') $get_vars['act'] = 'dispDocumentManageDocument';
            elseif($get_vars['act'] == 'dispModuleAdminSelectList') $get_vars['act'] = 'dispModuleSelectList';

            if($get_vars['act'] && $this->isExistsSSLAction($get_vars['act'])) $path = $this->getRequestUri(ENFORCE_SSL, $domain);
            else $path = $this->getRequestUri(RELEASE_SSL, $domain);

            $var_count = count($get_vars);
            if(!$var_count) {
                if(!$is_site) return $path;
                if($vid) {
                    if($this->allow_rewrite) $path .= $vid;
                    else $path .= '?vid='.$vid;
                } 
                return $path;
            }

            // rewrite모듈을 사용할때 getUrl()을 이용한 url 생성
            // 2009. 4. 8 mid, document_srl, site id, entry 를 제외하고는 rewrite rule 사용하지 않도록 변경
            if($this->allow_rewrite) {
                if(count($get_vars)) foreach($get_vars as $key => $value) if(!isset($value) || $value === '') unset($get_vars[$key]);

                $var_keys = array_keys($get_vars);
                asort($var_keys);
                $target = implode('.',$var_keys);

                if($vid) $rpath = $path.$vid .'/';
                else $rpath = $path;

                switch($target) {
                    case 'mid' :
                        return $rpath.$get_vars['mid'];
                    case 'document_srl' :
                        return $rpath.$get_vars['document_srl'];
                    case 'document_srl.mid' :
                        return sprintf('%s%s/%s',$rpath,$get_vars['mid'],$get_vars['document_srl']);
                    case 'entry.mid' :
                        return sprintf('%s%s/entry/%s',$rpath,$get_vars['mid'],$get_vars['entry']);
                    case 'act.document_srl.key' :
                            if($get_vars['act']=='trackback') return sprintf('%s%s/%s/%s', $rpath,$get_vars['document_srl'],$get_vars['key'],$get_vars['act']);
                        break;
                        
                }
            }

            // rewrite 모듈을 사용하지 않고 인자의 값이 2개 이상이거나 rewrite모듈을 위한 인자로 적당하지 않을 경우
            if($vid) $url = 'vid='.$vid;
            foreach($get_vars as $key => $val) {
                if(!isset($val)) continue;
                if(is_array($val) && count($val)) {
                    foreach($val as $k => $v) {
                        $url .= ($url?'&':'').$key.'['.$k.']='.urlencode($v);
                    }
                } else {
                    $url .= ($url?'&':'').$key.'='.urlencode($val);
                }
            }
            return $path.'?'.htmlspecialchars($url);
        }

        /**
         * @brief 요청이 들어온 URL에서 argument를 제거하여 return
         **/
        function getRequestUri($ssl_mode = FOLLOW_REQUEST_SSL, $domain = null) {
            // HTTP Request가 아니면 패스
            if(!isset($_SERVER['SERVER_PROTOCOL'])) return ;

            static $url = array();
            if(Context::get('_use_ssl') == "always") $ssl_mode = ENFORCE_SSL;

            if($domain) $domain_key = md5($domain);
            else $domain_key = 'default';

            if(isset($url[$ssl_mode][$domain_key])) return $url[$ssl_mode][$domain_key];

            switch($ssl_mode) {
                case FOLLOW_REQUEST_SSL :
                        if($_SERVER['HTTPS']=='on') $use_ssl = true;
                        else $use_ssl = false;
                    break;
                case ENFORCE_SSL :
                        $use_ssl = true;
                    break;
                case RELEASE_SSL :
                        $use_ssl = false;
                    break;
            }

            if($domain) {
                $target_url = trim($domain);
                if(substr($target_url,-1) != '/') $target_url.= '/';
            } else {
                $target_url= $_SERVER['HTTP_HOST'].getScriptPath();
            }

            $url_info = parse_url('http://'.$target_url);
            if($use_ssl) {
                if(Context::get("_https_port") && Context::get("_https_port") != 443) {
                    $url_info['port'] = Context::get("_https_port");
                }
                else
                {
                    unset($url_info['port']);
                }
            } else {
                if(Context::get("_http_port") && Context::get("_http_port") != 80) {
                    $url_info['port'] = Context::get("_http_port");
                }
                else
                {
                    unset($url_info['port']);
                }
            }

            $url[$ssl_mode][$domain_key] = sprintf("%s://%s%s%s",$use_ssl?'https':$url_info['scheme'], $url_info['host'], $url_info['port']&&$url_info['port']!=80?':'.$url_info['port']:'',$url_info['path']);

            return $url[$ssl_mode][$domain_key];
        }

        /**
         * @brief key/val로 context vars 세팅
         **/
        function set($key, $val, $set_to_get_vars = false) {
            $oContext = &Context::getInstance();
            $oContext->_set($key, $val, $set_to_get_vars);
        }

        /**
         * @brief key/val로 context vars 세팅
         **/
        function _set($key, $val, $set_to_get_vars = false) {
            $this->context->{$key} = $val;
            if($set_to_get_vars || $this->get_vars->{$key}) $this->get_vars->{$key} = $val;
        }

        /**
         * @brief key값에 해당하는 값을 return
         **/
        function get($key) {
            $oContext = &Context::getInstance();
            return $oContext->_get($key);
        }

        /**
         * @brief key값에 해당하는 값을 return
         **/
        function _get($key) {
            return $this->context->{$key};
        }

        /**
         * @brief 받고자 하는 변수만 object에 입력하여 받음
         *
         * key1, key2, key3 .. 등의 인자를 주어 여러개의 변수를 object vars로 세팅하여 받을 수 있음
         **/
        function gets() {
            $num_args = func_num_args();
            if($num_args<1) return;
            $args_list = func_get_args();

            $oContext = &Context::getInstance();
            return $oContext->_gets($num_args, $args_list);
        }

        /**
         * @brief 받고자 하는 변수만 object에 입력하여 받음
         *
         * key1, key2, key3 .. 등의 인자를 주어 여러개의 변수를 object vars로 세팅하여 받을 수 있음
         **/
        function _gets($num_args, $args_list) {
            for($i=0;$i<$num_args;$i++) {
                $args = $args_list[$i];
                $output->{$args} = $this->_get($args);
            }
            return $output;
        }

        /**
         * @brief 모든 데이터를 return
         **/
        function getAll() {
            $oContext = &Context::getInstance();
            return $oContext->_getAll();
        }

        /**
         * @brief 모든 데이터를 return
         **/
        function _getAll() {
            return $this->context;
        }

        /**
         * @brief GET/POST/XMLRPC에서 넘어온 변수값을 return
         **/
        function getRequestVars() {
            $oContext = &Context::getInstance();
            return $oContext->_getRequestVars();
        }

        /**
         * @brief GET/POST/XMLRPC에서 넘어온 변수값을 return
         **/
        function _getRequestVars() {
            return clone($this->get_vars);
        }

        /**
         * @brief SSL로 인증되어야 할 action이 있을 경우 등록
         * common/js/xml_handler.js에서 이 action들에 대해서 https로 전송되도록 함
         **/
        function addSSLAction($action) {
            $oContext = &Context::getInstance();
            return $oContext->_addSSLAction($action);
        }

        function _addSSLAction($action) {
            if(in_array($action, $this->ssl_actions)) return;
            $this->ssl_actions[] = $action;
        }

        function getSSLActions() {
            $oContext = &Context::getInstance();
            return $oContext->_getSSLActions();
        }

        function _getSSLActions() {
            return $this->ssl_actions;
        }

        function isExistsSSLAction($action) {
            $oContext = &Context::getInstance();
            return $oContext->_isExistsSSLAction($action);
        }

        function _isExistsSSLAction($action) {
            return in_array($action, $this->ssl_actions);
        }

        /**
         * @brief js file을 추가
         **/
        function addJsFile($file, $optimized = true, $targetie = '',$index=null) {
            $oContext = &Context::getInstance();
            return $oContext->_addJsFile($file, $optimized, $targetie,$index);
        }

        /**
         * @brief js file을 추가
         **/
        function _addJsFile($file, $optimized, $targetie,$index) {
            if(in_array($file, $this->js_files)) return;

            if(is_null($index)) $index=count($this->js_files);
            for($i=$index;array_key_exists($i,$this->js_files);$i++);
            $this->js_files[$i] = array('file' => $file, 'optimized' => $optimized, 'targetie' => $targetie);
        }

        /**
         * @brief js file을 제거
         **/
        function unloadJsFile($file, $optimized = true, $targetie = '') {
            $oContext = &Context::getInstance();
            return $oContext->_unloadJsFile($file, $optimized, $targetie);
        }

        /**
         * @brief js file을 제거
         **/
        function _unloadJsFile($file, $optimized, $targetie) {
            foreach($this->js_files as $key => $val) {
                if(realpath($val['file'])==realpath($file) && $val['optimized'] == $optimized && $val['targetie'] == $targetie) {
                    unset($this->js_files[$key]);
                    return;
                }
            }
        }

        /**
         * @brief javascript filter 추가
         **/
        function addJsFilter($path, $filename) {
            $oXmlFilter = new XmlJSFilter($path, $filename);
            $oXmlFilter->compile();
        }

        /**
         * @brief array_unique와 동작은 동일하나 file 첨자에 대해서만 동작함
         **/
        function _getUniqueFileList($files) {
            ksort($files);
            $files = array_values($files);
            $filenames = array();
            $size = count($files);
            for($i = 0; $i < $size; ++ $i)
            {
                if(in_array($files[$i]['file'], $filenames))
                    unset($files[$i]);
                $filenames[] = $files[$i]['file'];
            }

            return $files;
        }

        /**
         * @brief js file 목록을 return
         **/
        function getJsFile() {
            $oContext = &Context::getInstance();
            return $oContext->_getJsFile();
        }

        /**
         * @brief js file 목록을 return
         **/
        function _getJsFile() {
            require_once(_XE_PATH_."classes/optimizer/Optimizer.class.php");
            $oOptimizer = new Optimizer();
            return $oOptimizer->getOptimizedFiles($this->_getUniqueFileList($this->js_files), "js");
        }

        /**
         * @brief CSS file 추가
         **/
        function addCSSFile($file, $optimized = true, $media = 'all', $targetie = '',$index = null) {
            $oContext = &Context::getInstance();
            return $oContext->_addCSSFile($file, $optimized, $media, $targetie,$index);
        }

        /**
         * @brief CSS file 추가
         **/
        function _addCSSFile($file, $optimized, $media, $targetie, $index) {
            if(in_array($file, $this->css_files)) return;

            if(is_null($index)) $index=count($this->css_files);
            for($i=$index;array_key_exists($i,$this->css_files);$i++);

            //if(preg_match('/^http:\/\//i',$file)) $file = str_replace(realpath("."), ".", realpath($file));
            $this->css_files[$i] = array('file' => $file, 'optimized' => $optimized, 'media' => $media, 'targetie' => $targetie);
        }

        /**
         * @brief css file을 제거
         **/
        function unloadCSSFile($file, $optimized = true, $media = 'all', $targetie = '') {
            $oContext = &Context::getInstance();
            return $oContext->_unloadCSSFile($file, $optimized, $media, $targetie);
        }

        /**
         * @brief css file을 제거
         **/
        function _unloadCSSFile($file, $optimized, $media, $targetie) {
            foreach($this->css_files as $key => $val) {
                if(realpath($val['file'])==realpath($file) && $val['optimized'] == $optimized && $val['media'] == $media && $val['targetie'] == $targetie) {
                    unset($this->css_files[$key]);
                    return;
                }
            }
        }

        /**
         * @brief CSS file 목록 return
         **/
        function getCSSFile() {
            $oContext = &Context::getInstance();
            return $oContext->_getCSSFile();
        }

        /**
         * @brief CSS file 목록 return
         **/
        function _getCSSFile() {
            require_once(_XE_PATH_."classes/optimizer/Optimizer.class.php");
            $oOptimizer = new Optimizer();
            return $oOptimizer->getOptimizedFiles($this->_getUniqueFileList($this->css_files), "css");
        }

        /**
         * @brief javascript plugin load
         **/
        function loadJavascriptPlugin($plugin_name) {
            $oContext = &Context::getInstance();
            return $oContext->_loadJavascriptPlugin($plugin_name);
        }

        function _loadJavascriptPlugin($plugin_name) {
            static $loaded_plugins = array();
            if($loaded_plugins[$plugin_name]) return;
            $loaded_plugins[$plugin_name] = true;

            $plugin_path = './common/js/plugins/'.$plugin_name.'/';
            if(!is_dir($plugin_path)) return;

            $info_file = $plugin_path.'plugin.load';
            if(!file_exists($info_file)) return;

            $list = file($info_file);
            for($i=0,$cnt=count($list);$i<$cnt;$i++) {
                $filename = trim($list[$i]);
                if(!$filename) continue;
                if(substr($filename,0,2)=='./') $filename = substr($filename,2);
                if(preg_match('/\.js$/i',$filename)) $this->_addJsFile($plugin_path.$filename, false, '', null);
                elseif(preg_match('/\.css$/i',$filename)) $this->_addCSSFile($plugin_path.$filename, false, 'all','', null);
            }

            if(is_dir($plugin_path.'lang')) $this->_loadLang($plugin_path.'lang');
        }

        /**
         * @brief HtmlHeader 추가
         **/
        function addHtmlHeader($header) {
            $oContext = &Context::getInstance();
            return $oContext->_addHtmlHeader($header);
        }

        /**
         * @brief HtmlHeader 추가
         **/
        function _addHtmlHeader($header) {
            $this->html_header .= "\n".$header;
        }

        /**
         * @brief HtmlHeader return
         **/
        function getHtmlHeader() {
            $oContext = &Context::getInstance();
            return $oContext->_getHtmlHeader();
        }

        /**
         * @brief HtmlHeader return
         **/
        function _getHtmlHeader() {
            return $this->html_header;
        }

        /**
         * @brief Html Body에 css class 추가
         **/
        function addBodyClass($class_name) {
            $oContext = &Context::getInstance();
            return $oContext->_addBodyClass($class_name);
        }

        /**
         * @brief Html Body에 css class 추가
         **/
        function _addBodyClass($class_name) {
	    $this->body_class[] = $class_name;
        }

        /**
         * @brief Html Body에 css class return
         **/
        function getBodyClass() {
            $oContext = &Context::getInstance();
            return $oContext->_getBodyClass();
        }

        /**
         * @brief Html Body에 css class return
         **/
        function _getBodyClass() {
	    $this->body_class = array_unique($this->body_class);
	    if(count($this->body_class)>0) return sprintf(' class="%s"', join(' ',$this->body_class));
            else return '';
        }


        /**
         * @brief BodyHeader 추가
         **/
        function addBodyHeader($header) {
            $oContext = &Context::getInstance();
            return $oContext->_addBodyHeader($header);
        }

        /**
         * @brief BodyHeader 추가
         **/
        function _addBodyHeader($header) {
            $this->body_header .= "\n".$header;
        }

        /**
         * @brief BodyHeader return
         **/
        function getBodyHeader() {
            $oContext = &Context::getInstance();
            return $oContext->_getBodyHeader();
        }

        /**
         * @brief BodyHeader return
         **/
        function _getBodyHeader() {
            return $this->body_header;
        }

        /**
         * @brief HtmlFooter 추가
         **/
        function addHtmlFooter($footer) {
            $oContext = &Context::getInstance();
            return $oContext->_addHtmlFooter($footer);
        }

        /**
         * @brief HtmlFooter 추가
         **/
        function _addHtmlFooter ($footer) {
            $this->html_footer .= ($this->Htmlfooter?"\n":"").$footer;
        }

        /**
         * @brief HtmlFooter return
         **/
        function getHtmlFooter() {
            $oContext = &Context::getInstance();
            return $oContext->_getHtmlFooter();
        }

        /**
         * @brief HtmlFooter return
         **/
        function _getHtmlFooter() {
            return $this->html_footer;
        }

        /**
         * @brief db설정내용이 저장되어 있는 config file의 path를 return
         **/
        function getConfigFile() {
            return _XE_PATH_."files/config/db.config.php";
        }

        /**
         * @brief ftp설정내용이 저장되어 있는 config file의 path를 return
         **/
        function getFTPConfigFile() {
            return _XE_PATH_."files/config/ftp.config.php";
        }

        /**
         * @brief 설치가 되어 있는지에 대한 체크
         *
         * 단순히 db config 파일의 존재 유무로 설치 여부를 체크한다
         **/
        function isInstalled() {
            return file_exists(Context::getConfigFile()) && filesize(Context::getConfigFile());
        }

        /**
         * @brief 내용의 위젯이나 기타 기능에 대한 code를 실제 code로 변경
         **/
        function transContent($content) {
            return $content;
        }

        /**
         * @brief rewrite mod 사용에 대한 변수 return
         **/
        function isAllowRewrite() {
            $oContext = &Context::getInstance();
            return $oContext->allow_rewrite;
        }
    }
?>
