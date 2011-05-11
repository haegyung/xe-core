<?php
    /**
     * @class  moduleAdminView
     * @author NHN (developers@xpressengine.com)
     * @brief admin view class of the module module
     **/

    class moduleAdminView extends module {

        /**
         * @brief Initialization
         **/
        function init() {
            // Set the template path
            $this->setTemplatePath($this->module_path.'tpl');
        }

        /**
         * @brief Module admin page
         **/
        function dispModuleAdminContent() {
            $this->dispModuleAdminList();
        }

        /**
         * @brief Display a lost of modules
         **/
        function dispModuleAdminList() {
            // Obtain a list of modules
            $oModuleModel = &getModel('module');
            $module_list = $oModuleModel->getModuleList();
            Context::set('module_list', $module_list);
            // Set a template file
            $this->setTemplateFile('module_list');
        }

        /**
         * @brief Pop-up details of the module (conf/info.xml)
         **/
        function dispModuleAdminInfo() {
            // Obtain a list of modules
            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoXml(Context::get('selected_module'));
            Context::set('module_info', $module_info);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_info');
        }

        /**
         * @brief Module Categories
         **/
        function dispModuleAdminCategory() {
            $module_category_srl = Context::get('module_category_srl');
            
            // Obtain a list of modules
            $oModuleModel = &getModel('module');
            // Display the category page if a category is selected
            if($module_category_srl) {
                $selected_category  = $oModuleModel->getModuleCategory($module_category_srl);
                Context::set('selected_category', $selected_category);
                // Set a template file
                $this->setTemplateFile('category_update_form');
            // If not selected, display a list of categories
            } else {
                $category_list = $oModuleModel->getModuleCategories();
                Context::set('category_list', $category_list);
                // Set a template file
                $this->setTemplateFile('category_list');
            }
        }

        /**
         * @brief Feature to copy module
         **/
        function dispModuleAdminCopyModule() {
            // Get a target module to copy
            $module_srl = Context::get('module_srl');
            // Get information of the module
            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module', 'mid', 'browser_title');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl, $columnList);
            Context::set('module_info', $module_info);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('copy_module');
        }

        /**
         * @brief Applying the default settings to all modules
         **/
        function dispModuleAdminModuleSetup() {
            $module_srls = Context::get('module_srls');

            $modules = explode(',',$module_srls);
            if(!count($modules)) if(!$module_srls) return new Object(-1,'msg_invalid_request');

            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($modules[0], $columnList);
            // Get a skin list of the module
            $skin_list = $oModuleModel->getSkins('./modules/'.$module_info->module);
            Context::set('skin_list',$skin_list);
            // Get a layout list
            $oLayoutMode = &getModel('layout');
            $layout_list = $oLayoutMode->getLayoutList();
            Context::set('layout_list', $layout_list);
            // Get a list of module categories
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_setup');
        }

        /**
         * @brief Apply module addition settings to all modules
         **/
        function dispModuleAdminModuleAdditionSetup() {
            $module_srls = Context::get('module_srls');

            $modules = explode(',',$module_srls);
            if(!count($modules)) if(!$module_srls) return new Object(-1,'msg_invalid_request');
            // pre-define variables because you can get contents from other module (call by reference)
            $content = '';
            // Call a trigger for additional settings
            // Considering uses in the other modules, trigger name cen be publicly used
            $output = ModuleHandler::triggerCall('module.dispAdditionSetup', 'before', $content);
            $output = ModuleHandler::triggerCall('module.dispAdditionSetup', 'after', $content);
            Context::set('setup_content', $content);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_addition_setup');
        }

        /**
         * @brief Applying module permission settings to all modules
         **/
        function dispModuleAdminModuleGrantSetup() {
            $module_srls = Context::get('module_srls');

            $modules = explode(',',$module_srls);
            if(!count($modules)) if(!$module_srls) return new Object(-1,'msg_invalid_request');

            $oModuleModel = &getModel('module');
			$columnList = array('module_srl', 'module', 'site_srl');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($modules[0], $columnList);
            $xml_info = $oModuleModel->getModuleActionXml($module_info->module);
            $source_grant_list = $xml_info->grant;
            // Grant virtual permissions for access and manager
            $grant_list->access->title = Context::getLang('grant_access');
            $grant_list->access->default = 'guest';
            if(count($source_grant_list)) {
                foreach($source_grant_list as $key => $val) {
                    if(!$val->default) $val->default = 'guest';
                    if($val->default == 'root') $val->default = 'manager';
                    $grant_list->{$key} = $val;
                }
            }
            $grant_list->manager->title = Context::getLang('grant_manager');
            $grant_list->manager->default = 'manager';
            Context::set('grant_list', $grant_list);
            // Get a list of groups
            $oMemberModel = &getModel('member');
            $group_list = $oMemberModel->getGroups($module_info->site_srl);
            Context::set('group_list', $group_list);
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_grant_setup');
        }

        /**
         * @brief Language codes
         **/
        function dispModuleAdminLangcode() {
            // Get the language file of the current site
            $site_module_info = Context::get('site_module_info');
            $args->site_srl = (int)$site_module_info->site_srl;
            $args->sort_index = 'name';
            $args->order_type = 'asc';
            $output = executeQueryArray('module.getLangList', $args);
            Context::set('lang_list', $output->data);
            // Get the currently selected language
            $name = Context::get('name');
            if($name) {
                $oModuleAdminModel = &getAdminModel('module');
                Context::set('selected_lang', $oModuleAdminModel->getLangCode($args->site_srl,'$user_lang->'.$name));
            }
            // Set the layout to be pop-up
            $this->setLayoutFile('popup_layout');
            // Set a template file
            $this->setTemplateFile('module_langcode');
        }

    }
?>
