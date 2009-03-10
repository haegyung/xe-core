<?php
    /**
    * @class ModuleHandler
    * @author zero (zero@nzeo.com)
    * @brief 모듈 핸들링을 위한 Handler
    *
    * 모듈을 실행시키기 위한 클래스.
    * constructor에 아무 인자 없이 객체를 생성하면 현재 요청받은 
    * 상태를 바탕으로 적절한 모듈을 찾게 되고,
    * 별도의 인자 값을 줄 경우 그에 맞는 모듈을 찾아서 실행한다.
    * 만약 찾아진 모듈의 요청된 act 가 없으면 action_foward를 참조하여 다른 모듈의 act를 실행한다.
    **/

    class ModuleHandler extends Handler {

        var $oModule = NULL; ///< 모듈 객체

        var $module = NULL; ///< 모듈
        var $act = NULL; ///< action
        var $mid = NULL; ///< 모듈의 객체명
        var $document_srl = NULL; ///< 문서 번호
        var $module_srl = NULL; ///< 모듈의 번호

        var $module_info = NULL; ///< 모듈의 정보

        var $error = NULL; ///< 진행 도중 에러 발생시 에러 코드를 정의, message 모듈을 호출시 사용

        /**
         * @brief constructor
         *
         * ModuleHandler에서 사용할 변수를 미리 세팅
         * 인자를 넘겨주지 않으면 현 페이지 요청받은 Request Arguments를 이용하여
         * 변수를 세팅한다.
         **/
        function ModuleHandler($module = '', $act = '', $mid = '', $document_srl = '', $module_srl = '') {
            // 설치가 안되어 있다면 install module을 지정
            if(!Context::isInstalled()) {
                $this->module = 'install';
                $this->act = Context::get('act');
                return;
            }

            // Request Argument중 모듈을 찾을 수 있는 변수를 구함
            if(!$module) $this->module = Context::get('module');
            else $this->module = $module;

            if(!$act) $this->act = Context::get('act');
            else $this->act = $act;

            if(!$mid) $this->mid = Context::get('mid');
            else $this->mid = $mid;

            if(!$document_srl) $this->document_srl = (int)Context::get('document_srl');
            else $this->document_srl = (int)$document_srl;

            if(!$module_srl) $this->module_srl = (int)Context::get('module_srl');
            else $this->module_srl = (int)$module_srl;

            $this->entry = Context::get('entry');

            // 기본 변수들의 검사 (XSS방지를 위한 기초적 검사)
            if($this->module && !preg_match("/^([a-z0-9\_\-]+)$/i",$this->module)) die(Context::getLang("msg_invalid_request"));
            if($this->mid && !preg_match("/^([a-z0-9\_\-]+)$/i",$this->mid)) die(Context::getLang("msg_invalid_request"));
            if($this->act && !preg_match("/^([a-z0-9\_\-]+)$/i",$this->act)) die(Context::getLang("msg_invalid_request"));

            // 애드온 실행 (모듈 실행 전)
            $called_position = 'before_module_init';
            $oAddonController = &getController('addon');
            $addon_file = $oAddonController->getCacheFilePath();
            if(file_exists($addon_file)) @include($addon_file);
        }

        /**
         * @brief module, mid, document_srl을 이용하여 모듈을 찾고 act를 실행하기 위한 준비를 함
         **/
        function init() {
            // ModuleModel 객체 생성
            $oModuleModel = &getModel('module');

            $site_module_info = Context::get('site_module_info');

            if(!$this->document_srl && $this->mid && $this->entry) {
                $oDocumentModel = &getModel('document');
                $this->document_srl = $oDocumentModel->getDocumentSrlByAlias($this->mid, $this->entry);
                if($this->document_srl) Context::set('document_srl', $this->document_srl);
            }

            // 문서번호(document_srl)가 있을 경우 모듈 정보를 구해옴
            if($this->document_srl) {
                $module_info = $oModuleModel->getModuleInfoByDocumentSrl($this->document_srl);

                // 문서가 존재하지 않으면 문서 정보를 제거
                if(!$module_info) {
                    unset($this->document_srl);
                // 문서가 존재할 경우 모듈 정보를 바탕으로 virtual site 및 mid 비교
                } else {
                    // mid 값이 다르면 문서의 mid로 설정
                    if($this->mid != $module_info->mid) {
                        $this->mid = $module_info->mid;
                        Context::set('mid', $module_info->mid, true);
                    }
                }
                // 요청된 모듈과 문서 번호가 다르면 문서 번호에 의한 모듈 정보를 제거
                if($this->module && $module_info->module != $this->module) unset($module_info);
            }

            // 모듈정보를 구하지 못했고 mid 요청이 있으면 mid에 해당하는 모듈 정보를 구함
            if(!$module_info && $this->mid) {
                $module_info = $oModuleModel->getModuleInfoByMid($this->mid, $site_module_info->site_srl);
                //if($this->module && $module_info->module != $this->module) unset($module_info);
            }

            // 모듈을 여전히(;;) 못 찾고 모듈번호(module_srl)가 있으면 해당 모듈을 구함
            // module_srl로 대상 모듈을 찾는 것을 주석 처리함.
            if(!$module_info && $this->module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
                //if($this->module && $module_info->module != $this->module) unset($module_info);
            }

            // 역시 모듈을 못 찾았고 $module이 없다면 기본 모듈을 찾아봄
            if(!$module_info && !$this->module) $module_info = $site_module_info;

            // 모듈정보와 사이트 모듈정보가 다르면(다른 사이트이면) 페이지 리다이렉트
            if($module_info && $module_info->site_srl != $site_module_info->site_srl) {
                // 현재 요청된 모듈이 가상 사이트 모듈일 경우
                if($module_info->site_srl) {
                    $site_info = $oModuleModel->getSiteInfo($module_info->site_srl);
                    $redirect_url = getSiteUrl($site_info->domain, 'mid',Context::get('mid'),'document_srl',Context::get('document_srl'),'module_srl',Context::get('module_srl'),'entry',Context::get('entry'));
                // 가상 사이트 모듈이 아닌데 가상 사이트에서 호출되었을 경우
                } else {
                    $db_info = Context::getDBInfo();
                    if(!$db_info->default_url) return die("기본 URL이 정해지지 않아서 동작을 중지합니다");
                    else $redirect_url = getSiteUrl($db_info->default_url, 'mid',Context::get('mid'),'document_srl',Context::get('document_srl'),'module_srl',Context::get('module_srl'),'entry',Context::get('entry'));
                }
                header("location:".$redirect_url);
                return false;
            }

            // 모듈 정보가 찾아졌을 경우 모듈 정보에서 기본 변수들을 구함, 모듈 정보에서 module 이름을 구해움
            if($module_info) {
                $this->module = $module_info->module;
                $this->mid = $module_info->mid;
                $this->module_info = $module_info;
                Context::setBrowserTitle($module_info->browser_title);
                $part_config= $oModuleModel->getModulePartConfig('layout',$module_info->layout_srl);
                Context::addHtmlHeader($part_config->header_script);
            }

            // 모듈정보에 module과 mid를 강제로 지정
            $this->module_info->module = $this->module;
            $this->module_info->mid = $this->mid;

            // 여기까지도 모듈 정보를 찾지 못했다면 깔끔하게 시스템 오류 표시
            if(!$this->module) $this->error = 'msg_module_is_not_exists';

            // mid값이 있을 경우 mid값을 세팅
            if($this->mid) Context::set('mid', $this->mid, true);

            // 현재 모듈의 정보를 세팅
            Context::set('current_module_info', $module_info);
                
            // 실제 동작을 하기 전에 trigger 호출
            $output = ModuleHandler::triggerCall('display', 'before', $content);
            if(!$output->toBool()) {
                $this->error = $output->getMessage();
                return false;
            }

            return true;
        }

        /**
         * @brief 모듈과 관련된 정보를 이용하여 객체를 구하고 act 실행까지 진행시킴
         **/
        function procModule() {
            // 에러가 있으면 메세지 객체를 만들어서 return
            if($this->error) {
                $oMessageView = &getView('message');
                $oMessageView->setError(-1);
                $oMessageView->setMessage($this->error);
                $oMessageView->dispMessage();
                return $oMessageView;
            }

            // ModuleModel 객체 생성
            $oModuleModel = &getModel('module');

            // 해당 모듈의 conf/action.xml 을 분석하여 action 정보를 얻어옴
            $xml_info = $oModuleModel->getModuleActionXml($this->module);

            // 미설치시에는 act값을 강제로 변경
            if($this->module=="install") {
                if(!$this->act || !$xml_info->action->{$this->act}) $this->act = $xml_info->default_index_act;
            } 

            // 현재 요청된 act가 있으면 $xml_info에서 type을 찾음, 없다면 기본 action을 이용
            if(!$this->act) $this->act = $xml_info->default_index_act;

            // act값이 지정이 안되어 있으면 오류 표시
            if(!$this->act) {
                $this->error = 'msg_module_is_not_exists';
                return;
            }

            // type, kind 값 구함
            $type = $xml_info->action->{$this->act}->type;
            $kind = strpos(strtolower($this->act),'admin')!==false?'admin':'';
            if(!$kind && $this->module == 'admin') $kind = 'admin';

            // 모듈 객체 생성
            $oModule = &$this->getModuleInstance($this->module, $type, $kind);
            if(!is_object($oModule)) {
                $this->error = 'msg_module_is_not_exists';
                return;
            }

            // 모듈에 act값을 세팅
            $oModule->setAct($this->act);

            // 모듈 정보 세팅
            $this->module_info->module_type = $type;
            $oModule->setModuleInfo($this->module_info, $xml_info);

            // 모듈을 수행하고 결과가 false이면 message 모듈 호출 지정
            if(!$oModule->proc()) $this->error = $oModule->getMessage();

            return $oModule;
        }

        /**
         * @ 실행된 모듈의 컨텐츠를 출력
         **/
        function displayContent($oModule = NULL) {
            // 설정된 모듈이 정상이지 않을 경우 message 모듈 객체 생성
            if(!$oModule || !is_object($oModule)) {
                $this->error = 'msg_module_is_not_exists';
            }

            // install 모듈이 아닐 때 DB 접속에 문제가 있으면 오류
            if($this->module != 'install' && $GLOBALS['__DB__'][Context::getDBType()]->is_connected == false) {
                $this->error = 'msg_dbconnect_failed';
            }

            // HTML call 이면 message view 객체 이용하도록
            if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
                // 에러가 발생하였을시 처리
                if($this->error) {
                    // message 모듈 객체를 생성해서 컨텐츠 생성
                    $oMessageView = &getView('message');
                    $oMessageView->setError(-1);
                    $oMessageView->setMessage($this->error);
                    $oMessageView->dispMessage();

                    // 정상적으로 호출된 객체가 있을 경우 해당 객체의 template를 변경
                    if($oModule) {
                        $oModule->setTemplatePath($oMessageView->getTemplatePath());
                        $oModule->setTemplateFile($oMessageView->getTemplateFile());

                    // 그렇지 않으면 message 객체를 호출된 객체로 지정
                    } else {
                        $oModule = $oMessageView;
                    }
                }

                // 해당 모듈에 layout_srl이 있는지 확인
                if($oModule->module_info->layout_srl && !$oModule->getLayoutFile()) {

                    // layout_srl이 있으면 해당 레이아웃 정보를 가져와 layout_path/ layout_file 위치 변경
                    $oLayoutModel = &getModel('layout');
                    $layout_info = $oLayoutModel->getLayout($oModule->module_info->layout_srl);
                    if($layout_info) {

                        // 레이아웃 정보중 extra_vars의 이름과 값을 $layout_info에 입력
                        if($layout_info->extra_var_count) {

                            foreach($layout_info->extra_var as $var_id => $val) {
                                if($val->type == 'image') {
                                    if(preg_match('/^\.\/files\/attach\/images\/(.+)/i',$val->value)) $val->value = Context::getRequestUri().substr($val->value,2);
                                }
                                $layout_info->{$var_id} = $val->value;
                            }
                        }
                        // 레이아웃 정보중 menu를 Context::set
                        if($layout_info->menu_count) {
                            foreach($layout_info->menu as $menu_id => $menu) {
                                if(file_exists($menu->php_file)) @include($menu->php_file);
                                Context::set($menu_id, $menu);
                            }
                        }

                        // 레이아웃 정보를 Context::set
                        Context::set('layout_info', $layout_info);

                        $oModule->setLayoutPath($layout_info->path);
                        $oModule->setLayoutFile('layout');

                        // 레이아웃이 수정되었을 경우 수정본을 지정
                        $edited_layout = $oLayoutModel->getUserLayoutHtml($layout_info->layout_srl);
//                        $edited_layout_css = $oLayoutModel->getUserLayoutCss($layout_info->layout_srl);
//                        Context::addCSSFile($edited_layout_css);
                        if(file_exists($edited_layout)) $oModule->setEditedLayoutFile($edited_layout);
                    }
                }
            }

            // 컨텐츠 출력
            $oDisplayHandler = new DisplayHandler();
            $oDisplayHandler->printContent($oModule);
        }

        /**
         * @brief module의 위치를 찾아서 return
         **/
        function getModulePath($module) {
            return sprintf('./modules/%s/', $module);
        }

        /**
         * @brief 모듈 객체를 생성함
         **/
        function &getModuleInstance($module, $type = 'view', $kind = '') {
            $class_path = ModuleHandler::getModulePath($module);
            if(!is_dir(_XE_PATH_.$class_path)) return NULL;

            if(__DEBUG__==3) $start_time = getMicroTime();

            if($kind != 'admin') $kind = 'svc';

            // global 변수에 미리 생성해 둔 객체가 없으면 새로 생성
            if(!$GLOBALS['_loaded_module'][$module][$type][$kind]) {

                /**
                 * 모듈의 위치를 파악
                 **/

                // 상위 클래스명 구함
                if(!class_exists($module)) {
                    $high_class_file = sprintf('%s%s%s.class.php', _XE_PATH_,$class_path, $module);
                    if(!file_exists($high_class_file)) return NULL;
                    require_once($high_class_file);
                }

                // 객체의 이름을 구함
                switch($type) {
                    case 'controller' :
                            if($kind == 'admin') {
                                $instance_name = sprintf("%sAdmin%s",$module,"Controller");
                                $class_file = sprintf('%s%s%s.admin.%s.php', _XE_PATH_, $class_path, $module, $type);
                            } else {
                                $instance_name = sprintf("%s%s",$module,"Controller");
                                $class_file = sprintf('%s%s%s.%s.php', _XE_PATH_, $class_path, $module, $type);
                            }
                        break;
                    case 'model' :
                            if($kind == 'admin') {
                                $instance_name = sprintf("%sAdmin%s",$module,"Model");
                                $class_file = sprintf('%s%s%s.admin.%s.php', _XE_PATH_, $class_path, $module, $type);
                            } else {
                                $instance_name = sprintf("%s%s",$module,"Model");
                                $class_file = sprintf('%s%s%s.%s.php', _XE_PATH_, $class_path, $module, $type);
                            }
                        break;
                    case 'api' :
                            $instance_name = sprintf("%s%s",$module,"API");
                            $class_file = sprintf('%s%s%s.api.php', _XE_PATH_, $class_path, $module);
                        break;
                    case 'wap' :
                            $instance_name = sprintf("%s%s",$module,"WAP");
                            $class_file = sprintf('%s%s%s.wap.php', _XE_PATH_, $class_path, $module);
                        break;
                    case 'class' :
                            $instance_name = $module;
                            $class_file = sprintf('%s%s%s.class.php', _XE_PATH_, $class_path, $module);
                        break;
                    default :
                            $type = 'view';
                            if($kind == 'admin') {
                                $instance_name = sprintf("%sAdmin%s",$module,"View");
                                $class_file = sprintf('%s%s%s.admin.view.php', _XE_PATH_, $class_path, $module, $type);
                            } else {
                                $instance_name = sprintf("%s%s",$module,"View");
                                $class_file = sprintf('%s%s%s.view.php', _XE_PATH_, $class_path, $module, $type);
                            }
                        break;
                }

                // 클래스 파일의 이름을 구함
                if(!file_exists($class_file)) return NULL;

                // eval로 객체 생성
                require_once($class_file);
                $eval_str = sprintf('$oModule = new %s();', $instance_name);
                @eval($eval_str);
                if(!is_object($oModule)) return NULL;

                // 해당 위치에 속한 lang 파일을 읽음
                Context::loadLang($class_path.'lang');

                // 생성된 객체에 자신이 호출된 위치를 세팅해줌
                $oModule->setModule($module);
                $oModule->setModulePath($class_path);

                // 요청된 module에 constructor가 있으면 실행
                if(!isset($GLOBALS['_called_constructor'][$instance_name])) {
                    $GLOBALS['_called_constructor'][$instance_name] = true;
                    if(@method_exists($oModule, $instance_name)) $oModule->{$instance_name}();
                }

                // GLOBALS 변수에 생성된 객체 저장
                $GLOBALS['_loaded_module'][$module][$type][$kind] = $oModule;
            }

            if(__DEBUG__==3) $GLOBALS['__elapsed_class_load__'] += getMicroTime() - $start_time;

            // 객체 리턴
            return $GLOBALS['_loaded_module'][$module][$type][$kind];
        }

        /**
         * @brief trigger_name, called_position을 주고 trigger 호출
         **/
        function triggerCall($trigger_name, $called_position, &$obj) {
            // 설치가 안되어 있다면 trigger call을 하지 않고 바로 return
            if(!Context::isInstalled()) return new Object();

            $oModuleModel = &getModel('module');
            $triggers = $oModuleModel->getTriggers($trigger_name, $called_position);
            if(!$triggers || !count($triggers)) return new Object();

            foreach($triggers as $item) {
                $module = $item->module;
                $type = $item->type;
                $called_method = $item->called_method;

                $oModule = null;
                $oModule = &getModule($module, $type);
                if(!$oModule || !method_exists($oModule, $called_method)) continue;

                $output = $oModule->{$called_method}($obj);
                if(!$output->toBool()) return $output;
                unset($oModule);
            }

            return new Object();
        }
    }
?>
