<?php
    /**
     * @class  fileAdminView
     * @author zero (zero@nzeo.com)
     * @brief  file 모듈의 admin view 클래스
     **/

    class fileAdminView extends file {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 목록 출력 (관리자용)
         **/
        function dispFileAdminList() {
            // 목록을 구하기 위한 옵션
            $args->page = Context::get('page'); ///< 페이지
            $args->list_count = 50; ///< 한페이지에 보여줄 글 수
            $args->page_count = 10; ///< 페이지 네비게이션에 나타날 페이지의 수

            $args->sort_index = 'file_srl'; ///< 소팅 값
            $args->isvalid = Context::get('isvalid');
            $args->module_srl = Context::get('module_srl');

            // 목록 구함
            $oFileModel = &getAdminModel('file');
            $output = $oFileModel->getFileList($args);

            // mid목록을 구함
            $oModuleModel = &getModel('module');
            $mid_list = $oModuleModel->getMidList();
            Context::set('mid_list', $mid_list);

            // 목록의 loop를 돌면서 mid를 구하기 위한 module_srl값을 구함
            $file_count = count($output->data);
            if($file_count) {
                $module_srl_list = array();
                foreach($output->data as $key => $val) {
                    $module_srl = $val->module_srl;
                    if(!in_array($module_srl, $module_srl_list)) $module_srl_list[] = $module_srl;
                }
                if(count($module_srl_list)) {
                    $args->module_srls = implode(',',$module_srl_list);
                    $mid_output = executeQuery('module.getModuleInfoByModuleSrl', $args);
                    if($mid_output->data && !is_array($mid_output->data)) $mid_output->data = array($mid_output->data);
                    for($i=0;$i<count($mid_output->data);$i++) {
                        $mid_info = $mid_output->data[$i];
                        $module_list[$mid_info->module_srl] = $mid_info;
                    }
                }
            }

            // 목록의 loop를 돌면서 document를 구하기
            $file_count = count($output->data);
            if($file_count) {
                $document_srl_list = array();

                foreach($output->data as $val) {
                    $comment_srl = $val->upload_target_srl;
                    if(!in_array($comment_srl, $document_srl_list)) $document_srl_list[] = $comment_srl;
                }

                // comment를 먼저 구해서 document_srl을 구함
                if(count($document_srl_list)) {
                    $args->comment_srls = implode(',', $document_srl_list);
                    $comment_output = executeQuery('comment.getComments', $args);
                    if($comment_output->data && !is_array($comment_output->data)) {
                        $comment_output->data = array($comment_output->data);
                    }

                    if($comment_output->data) {
                        for($i = 0; $i < count($comment_output->data); $i++) {
                            $comment_info = $comment_output->data[$i];
                            $comment_list[$comment_info->comment_srl] = $comment_info;
                        }

                        foreach($output->data as $val) {
                            $val->target_document_srl = $comment_list[$val->upload_target_srl]->document_srl;
                        }

                        foreach($comment_output->data as $val) {
                            $document_srl = $val->document_srl;
                            if(!in_array($document_srl, $document_srl_list)) $document_srl_list[] = $document_srl;
                        }
                    }
                }

                // document를 구함
                if(count($document_srl_list)) {
                    $args->document_srls = implode(',', $document_srl_list);
                    $document_output = executeQuery('document.getDocuments', $args);
                    if($document_output->data && !is_array($document_output->data)) {
                        $document_output->data = array($document_output->data);
                    }

                    for($i = 0; $i < count($document_output->data); $i++) {
                        $document_info = $document_output->data[$i];
                        $document_list[$document_info->document_srl] = $document_info;
                    }
                }
            }

            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('file_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);
            Context::set('module_list', $module_list);
            Context::set('document_list', $document_list);

            // 템플릿 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('file_list');
        }

        /**
         * @brief 첨부파일 정보 설정 (관리자용)
         **/
        function dispFileAdminConfig() {
            $oFileModel = &getModel('file');
            $config = $oFileModel->getFileConfig();
            Context::set('config',$config);

            // 템플릿 파일 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('file_config');
        }

    }
?>
