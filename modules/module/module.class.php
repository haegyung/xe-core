<?php
    /**
     * @class  module
     * @author zero (zero@nzeo.com)
     * @brief  module 모듈의 high class
     **/

    class module extends ModuleObject {

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // module 모듈에서 사용할 디렉토리 생성
            FileHandler::makeDir('./files/cache/module_info');

            // 기본 모듈을 생성
            $oModule = &getController('module');
            $oModule->makeDefaultModule();
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
