<?php
    /**
     * @class  pageAdminController
     * @author zero (zero@nzeo.com)
     * @brief  page 모듈의 admin controller class
     **/

    class pageAdminController extends page {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 페이지 추가
         **/
        function procPageAdminInsert() {
            // 일단 입력된 값들을 모두 받아서 db 입력항목과 그외 것으로 분리
            $args = Context::gets('module_srl','module_category_srl','page_name','browser_title','is_default','layout_srl','content');
            $args->module = 'page';
            $args->mid = $args->page_name;
            if(!$args->content) $args->content = $content;
            else unset($args->conetnt);
            unset($args->page_name);
            if($args->is_default!='Y') $args->is_default = 'N';

            // module_srl이 넘어오면 원 모듈이 있는지 확인
            if($args->module_srl) {
                $oModuleModel = &getModel('module');
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
            }

            // module 모듈의 controller 객체 생성
            $oModuleController = &getController('module');

            // is_default=='Y' 이면
            if($args->is_default=='Y') $oModuleController->clearDefaultModule();

            // module_srl의 값에 따라 insert/update
            if($module_info->module_srl != $args->module_srl) {
                $output = $oModuleController->insertModule($args);
                $msg_code = 'success_registed';
                $module_info->module_srl = $output->get('module_srl');
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            /**
             * 권한 저장
             **/
            // 현 모듈의 권한 목록을 저장
            $grant_list = $this->xml_info->grant;

            if(count($grant_list)) {
                foreach($grant_list as $key => $val) {
                    $group_srls = Context::get($key);
                    if($group_srls) $arr_grant[$key] = explode('|@|',$group_srls);
                }
                $grants = serialize($arr_grant);
            }

            $oModuleController = &getController('module');
            $oModuleController->updateModuleGrant($module_info->module_srl, $grants);


            $this->add("module_srl", $args->module_srl);
            $this->add("page", Context::get('page'));
            $this->setMessage($msg_code);
        }

        /**
         * @brief 페이지 수정 내용 저장
         **/
        function procPageAdminInsertContent() {
            $module_srl = Context::get('module_srl');
            $content = Context::get('content');
            if(!$module_srl) return new Object(-1,'msg_invalid_request');

            // 페이지의 원 정보를 구해옴
            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
            $module_info->content = $content;

            // module 모듈의 controller 객체 생성
            $oModuleController = &getController('module');

            // 저장
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;

            // 캐시파일 재생성
            $this->procPageAdminRemoveWidgetCache();

            $this->add("module_srl", $module_info->module_srl);
            $this->add("page", Context::get('page'));
            $this->setMessage($msg_code);
        }

        /**
         * @brief 페이지 삭제
         **/
        function procPageAdminDelete() {
            $module_srl = Context::get('module_srl');

            // 원본을 구해온다
            $oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            $this->add('module','page');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 페이지 기본 정보의 추가
         **/
        function procPageAdminInsertConfig() {
            // 기본 정보를 받음
            $args = Context::gets('test');

            // module Controller 객체 생성하여 입력
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('page',$args);
            return $output;
        }

        /**
         * @brief 첨부파일 업로드
         **/
        function procUploadFile() {
            // 기본적으로 필요한 변수 설정
            $upload_target_srl = Context::get('upload_target_srl');
            $module_srl = Context::get('module_srl');

            // file class의 controller 객체 생성
            $oFileController = &getController('file');
            $output = $oFileController->insertFile($module_srl, $upload_target_srl);

            // 첨부파일의 목록을 java script로 출력
            $oFileController->printUploadedFileList($upload_target_srl);
        }

        /**
         * @brief 첨부파일 삭제
         * 에디터에서 개별 파일 삭제시 사용
         **/
        function procDeleteFile() {
            // 기본적으로 필요한 변수인 upload_target_srl, module_srl을 설정
            $upload_target_srl = Context::get('upload_target_srl');
            $module_srl = Context::get('module_srl');
            $file_srl = Context::get('file_srl');

            // file class의 controller 객체 생성
            $oFileController = &getController('file');
            if($file_srl) $output = $oFileController->deleteFile($file_srl, $this->grant->manager);

            // 첨부파일의 목록을 java script로 출력
            $oFileController->printUploadedFileList($upload_target_srl);
        }

        /**
         * @brief 지정된 페이지의 위젯 캐시 파일 지우기
         **/
        function procPageAdminRemoveWidgetCache() {
            $module_srl = Context::get('module_srl');

            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);

            $content = $module_info->content;

            // 언어 종류 가져옴
            $lang_list = Context::get('lang_supported');

            // 위젯 캐시 sequence 를 가져옴
            preg_match_all('/widget_sequence="([0-9]+)"/i',$content, $matches);

            $cache_path = './files/cache/widget_cache/';

            for($i=0;$i<count($matches[1]);$i++) {
                $sequence = $matches[1][$i];

                foreach($lang_list as $lang_type => $val) {
                    $cache_file = sprintf('%s%d.%s.cache', $cache_path, $sequence, $lang_type);
                    @unlink($cache_file);
                }
            }

            $this->setMessage('success_updated');
        }

        /**
         * @brief 페이지에 에디터 컨테츠 추가하기 위한 tpl return
         **/
        function procPageAdminAddContent() {
            $content = Context::get('content');
            $args = Context::getRequestVars('style','widget_margin_left','widget_margin_right','widget_margin_bottom','widget_margin_top');

            $tpl = $this->transEditorContent($content, $args);

            $this->add('tpl', $tpl);
        }

        /**
         * @brief 에디터에서 생성한 컨텐츠를 페이지 수정시 사용할 수 있도록 코드 생성
         **/
        function transEditorContent($content, $args = null) {
            // 에디터의 내용을 변환하여 visual한 영역과 원본 소스를 가지고 있는 code로 분리
            $code = $content;

            $oContext = &Context::getInstance();
            $content = preg_replace_callback('!<div([^\>]*)editor_component=([^\>]*)>(.*?)\<\/div\>!is', array($oContext,'transEditorComponent'), $content);
            $content = preg_replace_callback('!<img([^\>]*)editor_component=([^\>]*?)\>!is', array($oContext,'transEditorComponent'), $content);

            // 결과물에 있는 css Meta 목록을 구해와서 해당 css를 아예 읽어버림
            require_once("./classes/optimizer/Optimizer.class.php");
            $oOptimizer = new Optimizer();
            preg_match_all('!<\!\-\-Meta:([^\-]*?)\-\->!is', $content, $matches);
            $css_header = null;
            for($i=0;$i<count($matches[1]);$i++) {
                $css_file = $matches[1][$i];
                $buff = FileHandler::readFile($css_file);
                $css_header .= $oOptimizer->replaceCssPath($css_file, $buff)."\n";
            }

            $tpl = sprintf(
                    '<style type="text/css">%s</style>'.
                    '<div class="widgetOutput" style="%s" widget_margin_left="%s" widget_margin_right="%s" widget_margin_top="%s" widget_margin_bottom="%s" widget="widgetContent">'.
                        '<div class="widgetSetup"></div>'.
                        '<div class="widgetSize"></div>'.
                        '<div class="widgetRemove"></div>'.
                        '<div class="widgetResize"></div>'.
                        '<div class="widgetResizeLeft"></div>'.
                        '<div class="widgetBorder">'.
                            '<div style="margin:%s %s %s %s;">'.
                                '%s'.
                            '</div><div class="clear"></div>'.
                        '</div>'.
                        '<div class="widgetContent" style="display:none;width:1px;height:1px;overflow:hidden;">%s</div>'.
                    '</div>',
                    $css_header,
                    $args->style,
                    $args->widget_margin_left, $args->widget_margin_right, $args->widget_margin_top, $args->widget_margin_bottom,
                    $args->widget_margin_top, $args->widget_margin_right, $args->widget_margin_bottom, $args->widget_margin_left,
                    $content,
                    base64_encode($code)
            );

            return $tpl;
        }

    }
?>
