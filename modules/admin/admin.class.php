<?php
    /**
     * @class  admin
     * @author zero (zero@nzeo.com)
     * @brief  admin 모듈의 high class
     **/

    class admin extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // 게시판, 회원관리, 레이아웃관리등 자주 사용될 module을 admin_shortcut에 등록
            $oAdminController = &getAdminController('admin');

            $oAdminController->insertShortCut('blog');
            $oAdminController->insertShortCut('board');
            $oAdminController->insertShortCut('page');
            $oAdminController->insertShortCut('opage');
            $oAdminController->insertShortCut('menu');
            $oAdminController->insertShortCut('layout');
            $oAdminController->insertShortCut('addon');
            $oAdminController->insertShortCut('widget');
            $oAdminController->insertShortCut('member');
            $oAdminController->insertShortCut('module');
           
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

            // 템플릿 컴파일 파일 삭제
            FileHandler::removeFilesInDir("./files/cache/template_compiled");

            // optimized 파일 삭제
            FileHandler::removeFilesInDir("./files/cache/optimized");

            // js_filter_compiled 파일 삭제
            FileHandler::removeFilesInDir("./files/cache/js_filter_compiled");

            // queries 파일 삭제
            FileHandler::removeFilesInDir("./files/cache/queries");

            // ./files/cache/news* 파일 삭제
            $directory = dir("./files/cache/");
            while($entry = $directory->read()) {
                if(substr($entry,0,11)=='newest_news') @unlink("./files/cache/".$entry);
            }
            $directory->close();
        }
    }
?>
