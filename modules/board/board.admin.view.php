<?php
    /**
     * @class  boardAdminView
     * @author zero (zero@nzeo.com)
     * @brief  board 모듈의 admin view class
     **/

    class boardAdminView extends board {

        /**
         * @brief 초기화
         *
         * board 모듈은 일반 사용과 관리자용으로 나누어진다.\n
         **/
        function init() {
            // module_srl이 있으면 미리 체크하여 존재하는 모듈이면 module_info 세팅
            $module_srl = Context::get('module_srl');
            if(!$module_srl && $this->module_srl) {
                $module_srl = $this->module_srl;
                Context::set('module_srl', $module_srl);
            }

            // module model 객체 생성 
            $oModuleModel = &getModel('module');

            // module_srl이 넘어오면 해당 모듈의 정보를 미리 구해 놓음
            if($module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
                if(!$module_info) {
                    Context::set('module_srl','');
                    $this->act = 'list';
                } else {
                    $this->module_info = $module_info;
                    Context::set('module_info',$module_info);
                }
            }

            // 모듈 카테고리 목록을 구함
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);

            // 템플릿 경로 지정 (board의 경우 tpl에 관리자용 템플릿 모아놓음)
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

        /**
         * @brief 게시판 관리 목록 보여줌
         **/
        function dispBoardAdminContent() {
            // 등록된 board 모듈을 불러와 세팅
            $args->sort_index = "module_srl";
            $args->page = Context::get('page');
            $args->list_count = 40;
            $args->page_count = 10;
            $args->s_module_category_srl = Context::get('module_category_srl');
            $output = executeQuery('board.getBoardList', $args);

            // 템플릿에 쓰기 위해서 context::set
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('board_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            // 템플릿 파일 지정
            $this->setTemplateFile('index');
        }

        /**
         * @brief 선택된 게시판의 정보 출력
         **/
        function dispBoardAdminBoardInfo() {

            // module_srl 값이 없다면 그냥 index 페이지를 보여줌
            if(!Context::get('module_srl')) return $this->dispBoardAdminContent();

            // 레이아웃이 정해져 있다면 레이아웃 정보를 추가해줌(layout_title, layout)
            if($this->module_info->layout_srl) {
                $oLayoutModel = &getModel('layout');
                $layout_info = $oLayoutModel->getLayout($this->module_info->layout_srl);
                $this->module_info->layout = $layout_info->layout;
                $this->module_info->layout_title = $layout_info->layout_title;
            }

            // 정해진 스킨이 있으면 해당 스킨의 정보를 구함
            if($this->module_info->skin) {
                $oModuleModel = &getModel('module');
                $skin_info = $oModuleModel->loadSkinInfo($this->module_path, $this->module_info->skin);
                $this->module_info->skin_title = $skin_info->title;
            }

            // 템플릿 파일 지정
            $this->setTemplateFile('board_info');
        }

        /**
         * @brief 게시판 추가 폼 출력
         **/
        function dispBoardAdminInsertBoard() {

            // 스킨 목록을 구해옴
            $oModuleModel = &getModel('module');
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list',$skin_list);

            // 레이아웃 목록을 구해옴
            $oLayoutMode = &getModel('layout');
            $layout_list = $oLayoutMode->getLayoutList();
            Context::set('layout_list', $layout_list);

            // 템플릿 파일 지정
            $this->setTemplateFile('board_insert');
        }

        /**
         * @brief 게시판 삭제 화면 출력
         **/
        function dispBoardAdminDeleteBoard() {

            if(!Context::get('module_srl')) return $this->dispBoardAdminContent();

            $module_info = Context::get('module_info');

            $oDocumentModel = &getModel('document');
            $document_count = $oDocumentModel->getDocumentCount($module_info->module_srl);
            $module_info->document_count = $document_count;

            Context::set('module_info',$module_info);

            // 템플릿 파일 지정
            $this->setTemplateFile('board_delete');
        }

        /**
         * @brief 스킨 정보 보여줌
         **/
        function dispBoardAdminSkinInfo() {

            // 현재 선택된 모듈의 스킨의 정보 xml 파일을 읽음
            $module_info = Context::get('module_info');
            $skin = $module_info->skin;

            $oModuleModel = &getModel('module');
            $skin_info = $oModuleModel->loadSkinInfo($this->module_path, $skin);

            // skin_info에 extra_vars 값을 지정
            if(count($skin_info->extra_vars)) {
                foreach($skin_info->extra_vars as $key => $val) {
                    $name = $val->name;
                    $type = $val->type;
                    $value = $module_info->{$name};
                    if($type=="checkbox"&&!$value) $value = array();
                    $skin_info->extra_vars[$key]->value= $value;
                }
            }

            Context::set('skin_info', $skin_info);
            $this->setTemplateFile('skin_info');
        }

        /**
         * @brief 카테고리의 정보 출력
         **/
        function dispBoardAdminCategoryInfo() {

            // module_srl을 구함
            $module_srl = Context::get('module_srl');

            // 카테고리의 목록을 구해옴
            $oDocumentModel = &getModel('document');
            $category_list = $oDocumentModel->getCategoryList($module_srl);
            Context::set('category_list', $category_list);

            // 수정하려는 카테고리가 있다면해당 카테고리의 정보를 가져옴
            $category_srl = Context::get('category_srl');

            if($category_srl) {

                $selected_category = $oDocumentModel->getCategory($category_srl);

                if(!$selected_category) Context::set('category_srl','');
                else Context::set('selected_category',$selected_category);

                $this->setTemplateFile('category_update_form');

            } else {

                $this->setTemplateFile('category_list');

            }
        }

        /**
         * @brief 권한 목록 출력
         **/
        function dispBoardAdminGrantInfo() {
            // module_srl을 구함
            $module_srl = Context::get('module_srl');

            // module.xml에서 권한 관련 목록을 구해옴
            $grant_list = $this->xml_info->grant;
            Context::set('grant_list', $grant_list);

            // 권한 그룹의 목록을 가져온다
            $oMemberModel = &getModel('member');
            $group_list = $oMemberModel->getGroups();
            Context::set('group_list', $group_list);

            $this->setTemplateFile('grant_list');
        }

        /**
         * @brief 선택한 목록 출력
         **/
        function dispBoardAdminManageDocument() {
            // 선택한 목록을 세션에서 가져옴
            $flag_list = $_SESSION['document_management'][$this->module_srl];

            // 목록이 있으면 게시글을 가져옴
            if(count($flag_list)) $document_srl_list = array_keys($flag_list);
            if(is_array($document_srl_list) && count($document_srl_list)) {
                $oDocumentModeul = &getModel('document');
                $document_list = $oDocumentModeul->getDocuments($document_srl_list, $this->grant->is_admin);
                Context::set('document_list', $document_list);
            }

            // 게시판의 목록을 가져옴
            $output = executeQuery('board.getAllBoard', $args);
            $board_list = $output->data;
            if($board_list && !is_array($board_list)) $board_list = array($board_list);
            Context::set('board_list', $board_list);

            // 팝업 레이아웃 선택
            $this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');

            $this->setTemplateFile('checked_list');
        }
    }
?>