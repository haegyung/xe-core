<?php
    /**
     * @class  page
     * @author zero (zero@nzeo.com)
     * @brief  page 모듈의 high class
     **/

    class page extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageIndex');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageAdminContent');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageAdminModuleConfig');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageAdminInfo');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageAdminInsert');
            $oModuleController->insertActionFoward('page', 'view', 'dispPageAdminDelete');
            $oModuleController->insertActionFoward('page', 'controller', 'procPageAdminInsert');
            $oModuleController->insertActionFoward('page', 'controller', 'procPageAdminDelete');
            $oModuleController->insertActionFoward('page', 'controller', 'procPageAdminInsertConfig');

            // page 에서 사용할 cache디렉토리 생성
            FileHandler::makeDir('./files/cache/page');

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
