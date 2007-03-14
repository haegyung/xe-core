<?php
    /**
     * @class  pluginView
     * @author zero (zero@nzeo.com)
     * @brief  plugin 모듈의 View class
     **/

    class pluginView extends plugin {

        /**
         * @brief 초기화
         **/
        function init() {
            $this->setTemplatePath($this->module_path.'tpl.admin');
        }
        
        /**
         * @brief 플러그인 목록을 보여줌
         **/
        function dispDownloadedPluginList() {
            // 플러그인 목록을 세팅
            $oPluginModel = &getModel('plugin');
            $plugin_list = $oPluginModel->getDownloadedPluginList();
            Context::set('plugin_list', $plugin_list);

            $this->setTemplateFile('downloaded_plugin_list');
        }

        /**
         * @brief 플러그인의 상세 정보(conf/info.xml)를 팝업 출력
         **/
        function dispPluginInfo() {
            // 선택된 플러그인 정보를 구함
            $oPluginModel = &getModel('plugin');
            $plugin_info = $oPluginModel->getPluginInfo(Context::get('selected_plugin'));
            Context::set('plugin_info', $plugin_info);

            // 플러그인을 팝업으로 지정
            $this->setLayoutFile('popup_layout');

            // 템플릿 파일 지정
            $this->setTemplateFile('plugin_detail_info');
        }

        /**
         * @brief 플러그인의 코드 생성기
         **/
        function dispGenerateCode() {
            // 선택된 플러그인 정보를 구함
            $oPluginModel = &getModel('plugin');
            $plugin_info = $oPluginModel->getPluginInfo(Context::get('selected_plugin'));
            Context::set('plugin_info', $plugin_info);

            // mid 목록을 가져옴
            $oModuleModel = &getModel('module');
            $mid_list = $oModuleModel->getMidList();
            Context::set('mid_list', $mid_list);

            // 플러그인을 팝업으로 지정
            $this->setLayoutFile('popup_layout');

            // 템플릿 파일 지정
            $this->setTemplateFile('plugin_generate_code');
        }

        /**
         * @brief 플러그인의 생성된 코드를 출력
         **/
        function dispGeneratedCode() {
            // 선택된 플러그인 정보를 구함
            $oPluginModel = &getModel('plugin');
            $plugin_info = $oPluginModel->getPluginInfo(Context::get('selected_plugin'));
            Context::set('plugin_info', $plugin_info);

            // mid 목록을 가져옴
            $oModuleModel = &getModel('module');
            $mid_list = $oModuleModel->getMidList();
            Context::set('mid_list', $mid_list);

            // 플러그인을 팝업으로 지정
            $this->setLayoutFile('popup_layout');

            // 템플릿 파일 지정
            $this->setTemplateFile('plugin_generate_code');
        }

    }
?>
