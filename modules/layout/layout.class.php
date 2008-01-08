<?php
    /**
     * @class  layout
     * @author zero (zero@nzeo.com)
     * @brief  layout 모듈의 high class
     **/

    class layout extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminContent');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminInsert');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminModify');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminEdit');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminDownloadedList');
            $oModuleController->insertActionForward('layout', 'view', 'dispLayoutAdminInfo');
            
            // 레이아웃에서 사용할 디렉토리 생성
            FileHandler::makeDir('./files/cache/layout');

            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            return new Object();
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }

        /**
         * @brief 권한 체크를 실행하는 method
         * 모듈 객체가 생성된 경우는 직접 권한을 체크하지만 기능성 모듈등 스스로 객체를 생성하지 않는 모듈들의 경우에는
         * ModuleObject에서 직접 method를 호출하여 권한을 확인함
         *
         * isAdminGrant는 관리권한 이양시에만 사용되도록 하고 기본은 false로 return 되도록 하여 잘못된 권한 취약점이 생기지 않도록 주의하여야 함
         **/
        function isAdmin() {
            // 로그인이 되어 있지 않으면 무조건 return false
            $is_logged = Context::get('is_logged');
            if(!$is_logged) return false;

            // 사용자 아이디를 구함
            $logged_info = Context::get('logged_info');

            // 모듈 요청에 사용된 변수들을 가져옴
            $args = Context::getRequestVars();

            // act의 값에 따라서 관리 권한 체크
            switch($args->act) {
                case 'dispLayoutAdminPreview' :
                case 'procLayoutAdminCodeReset' :
                case 'procLayoutAdminCodeUpdate' :
                        if(!$args->layout_srl) return false;

                        $oModuleModel = &getModel('module');
                        $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->layout_srl);
                        if(!$module_info) return false;

                        if($oModuleModel->isModuleAdmin($module_info, $logged_info)) return true; 
                    break;
            }

            return false;
        }
    }
?>
