<?php
    /**
     * @class  board
     * @author zero (zero@nzeo.com)
     * @brief  board 모듈의 high class
     **/

    class board extends ModuleObject {

        var $search_option = array('title','content','title_content','user_name','user_id'); ///< 검색 옵션

        var $skin = "default"; ///< 스킨 이름
        var $list_count = 20; ///< 한 페이지에 나타날 글의 수
        var $page_count = 10; ///< 페이지의 수
        var $category_list = NULL; ///< 카테고리 목록

        var $editor = 'default'; ///< 에디터 종류

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminContent');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminModuleConfig');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminBoardInfo');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminInsertBoard');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminDeleteBoard');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminSkinInfo');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminCategoryInfo');
            $oModuleController->insertActionFoward('board', 'view', 'dispBoardAdminGrantInfo');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminInsertGrant');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminUpdateSkinInfo');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminDeleteBoard');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminInsertCategory');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminUpdateCategory');
            $oModuleController->insertActionFoward('board', 'view', 'procBoardAdminInsertConfig');

            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function moduleIsInstalled() {
            return new Object();
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            return new Object();
        }

    }
?>
