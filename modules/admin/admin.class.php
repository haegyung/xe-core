<?php
    /**
     * @class  admin
     * @author zero (zero@nzeo.com)
     * @brief  admin 모듈의 high class
     **/

    class admin extends ModuleObject {

        /**
         * @brief MVC 에서 공통으로 사용되는 설정등을 모아 놓은것..
         **/
        function init() {
        }

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // 게시판, 회원관리, 레이아웃관리등 자주 사용될 module을 admin_shortcut에 등록
            $oAdminController = &getController('admin');

            $oAdminController->insertShortCut('module');
            $oAdminController->insertShortCut('addon');
            $oAdminController->insertShortCut('layout');
            $oAdminController->insertShortCut('plugin');
            $oAdminController->insertShortCut('board');
            $oAdminController->insertShortCut('member');
           
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
