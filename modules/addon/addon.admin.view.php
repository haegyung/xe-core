<?php
    /**
     * @class  addonAdminView
     * @author NHN (developers@xpressengine.com)
     * @brief admin view class of addon modules
     **/

    class addonAdminView extends addon {

        /**
         * @brief Initialization
         **/
        function init() {
            $this->setTemplatePath($this->module_path.'tpl');
        }

        /**
         * @brief Add Management main page (showing the list)
         **/
        function dispAddonAdminIndex() {
            $site_module_info = Context::get('site_module_info');
            // Add to the list settings
            $oAddonModel = &getAdminModel('addon');
            $addon_list = $oAddonModel->getAddonList($site_module_info->site_srl);
            Context::set('addon_list', $addon_list);
            // Template specifies the path and file
            $this->setTemplateFile('addon_list');
        }

        /**
         * @biref Setting out the details pop-up add-on
         **/
        function dispAddonAdminSetup() {
            $site_module_info = Context::get('site_module_info');
            // Wanted to add the requested
            $selected_addon = Context::get('selected_addon');
            // Wanted to add the requested information
            $oAddonModel = &getAdminModel('addon');
            $addon_info = $oAddonModel->getAddonInfoXml($selected_addon, $site_module_info->site_srl);
            Context::set('addon_info', $addon_info);
            // Get a mid list
            $oModuleModel = &getModel('module');
            $oModuleAdminModel = &getAdminModel('module');

            if($site_module_info->site_srl) $args->site_srl = $site_module_info->site_srl;
			$columnList = array('module_srl', 'mid', 'browser_title');
            $mid_list = $oModuleModel->getMidList($args, $columnList);
            // module_category and module combination
            if(!$site_module_info->site_srl) {
                // Get a list of module categories
                $module_categories = $oModuleModel->getModuleCategories();

                if($mid_list) {
                    foreach($mid_list as $module_srl => $module) {
                        $module_categories[$module->module_category_srl]->list[$module_srl] = $module; 
                    }
                }
            } else {
                $module_categories[0]->list = $mid_list;
            }

            Context::set('mid_list',$module_categories);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Template specifies the path and file
            $this->setTemplateFile('setup_addon');
        }

        /**
         * @brief Add details (conf/info.xml) a pop-out
         **/
        function dispAddonAdminInfo() {
            $site_module_info = Context::get('site_module_info');
            // Wanted to add the requested
            $selected_addon = Context::get('selected_addon');
            // Wanted to add the requested information
            $oAddonModel = &getAdminModel('addon');
            $addon_info = $oAddonModel->getAddonInfoXml($selected_addon, $site_module_info->site_srl);
            Context::set('addon_info', $addon_info);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Template specifies the path and file
            $this->setTemplateFile('addon_info');
        }

    }
?>
