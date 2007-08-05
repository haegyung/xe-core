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
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            $this->add("module_srl", $args->module_srl);
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


    }
?>
