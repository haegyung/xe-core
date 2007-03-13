<?php
    /**
     * @class  adminModel
     * @author zero (zero@nzeo.com)
     * @brief  admin 모듈의 model class
     **/

    class adminModel extends admin {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief admin shortcut 에 등록된 목록을 return;
         **/
        function getShortCuts() {
            $oDB = &DB::getInstance();
            $output = $oDB->executeQuery('admin.getShortCutList');
            if(!$output->toBool()) return $output;

            if(!is_array($output->data)) return array($output->data);
            return $output->data;
        }

    }
?>
