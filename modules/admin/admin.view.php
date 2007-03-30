<?php
    /**
     * @class  adminView
     * @author zero (zero@nzeo.com)
     * @brief  admin 모듈의 view class
     **/

    class adminView extends admin {

        /**
         * @brief 초기화
         **/
        function init() {
            // template path 지정
            $this->setTemplatePath($this->module_path.'tpl');

            // 접속 사용자에 대한 체크
            $oMemberModel = &getModel('member');
            $logged_info = $oMemberModel->getLoggedInfo();

            // 로그인 하지 않았다면 로그인 폼 출력
            if(!$oMemberModel->isLogged()) return $this->act = 'dispLogin';

            // 로그인되었는데 관리자(member->is_admin!=1)가 아니면 오류 표시
            if($logged_info->is_admin != 'Y') return $this->stop('msg_is_not_administrator');

            // 관리자용 레이아웃으로 변경
            $this->setLayoutPath($this->getTemplatePath());
            $this->setLayoutFile('layout.html');

            // shortcut 가져오기
            $oAdminModel = &getModel('admin');
            $shortcut_list = $oAdminModel->getShortCuts();
            Context::set('shortcut_list', $shortcut_list);
        }

        /**
         * @brief 관리자 메인 페이지 출력
         **/
        function dispAdminIndex() {
            $this->setTemplateFile('index');
        }

        /**
         * @brief 관리자 메뉴 숏컷 출력
         **/
        function dispAdminShortCut() {
            $this->setTemplateFile('shortcut_list');
        }
    }
?>
