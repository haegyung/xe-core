<?php
    /**
     * @class  rssAdminView
     * @author misol (misol221@paran.com)
     * @brief  rss ����� admin view class
     **/

    class rssAdminView extends rss {
        /**
         * @brief �ʱ�ȭ
         **/
        function init() {
            // ���ø� ��� ���� 
            $this->setTemplatePath($this->module_path.'tpl');
        }


        /**
         * @brief ������ ������ �ʱ�ȭ��
         **/
        function dispRssAdminIndex() {
            $oModuleModel = &getModel('module');
            $rss_config = $oModuleModel->getModulePartConfigs('rss');
            $total_config = $oModuleModel->getModuleConfig('rss');
            $oRssModel = &getModel('rss');

            if($rss_config) {
                foreach($rss_config as $module_srl => $config) {
                    if($config) {
                        $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
                        $site = $oModuleModel->getSiteInfo($module_info->site_srl);
                        if(!strpos($site->domain, '.')) $vid = $site->domain;
                        else $site = null;
                        if($site) $feed_config[$module_srl]['url'] = $oRssModel->getModuleFeedUrl($vid, $module_info->mid, 'rss');
                        $feed_config[$module_srl]['mid'] = $module_info->mid;
                        $feed_config[$module_srl]['open_feed'] = $config->open_rss;
                        $feed_config[$module_srl]['open_total_feed'] = $config->open_total_feed;
                    }
                }
            }

            Context::set('feed_config', $feed_config);
            Context::set('total_config', $total_config);
           	$this->setTemplatePath($this->module_path.'tpl');
		        $this->setTemplateFile('rss_admin_index');
        }
    }
?>