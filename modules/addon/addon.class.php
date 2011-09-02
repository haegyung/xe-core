<?php
    /**
     * @class  addon
     * @author NHN (developers@xpressengine.com)
     * @brief high class of addon modules
     **/

    class addon extends ModuleObject {

        /**
         * @brief Implement if additional tasks are necessary when installing
         **/
        function moduleInstall() {
            // Register to add a few
            $oAddonController = &getAdminController('addon');
            $oAddonController->doInsert('autolink');
            $oAddonController->doInsert('blogapi');
            $oAddonController->doInsert('counter');
            $oAddonController->doInsert('member_communication');
            $oAddonController->doInsert('member_extra_info');
            $oAddonController->doInsert('mobile');
            $oAddonController->doInsert('referer');
            $oAddonController->doInsert('resize_image');
            $oAddonController->doInsert('openid_delegation_id');
            $oAddonController->doInsert('point_level_icon');
            // To add a few changes to the default activation state
            $oAddonController->doActivate('autolink');
            $oAddonController->doActivate('counter');
            $oAddonController->doActivate('member_communication');
            $oAddonController->doActivate('member_extra_info');
            $oAddonController->doActivate('mobile');
            $oAddonController->doActivate('referer');
            $oAddonController->doActivate('resize_image');
            $oAddonController->makeCacheFile(0);
            return new Object();
        }

        /**
         * @brief a method to check if successfully installed
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
			if(!$oDB->isColumnExists("addons", "is_used_m")) return true;
			if(!$oDB->isColumnExists("addons_site", "is_used_m")) return true;
            return false;
        }

        /**
         * @brief Execute update
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
			if(!$oDB->isColumnExists("addons", "is_used_m")) {
				$oDB->addColumn("addons", "is_used_m", "char", 1, "N", true);
			}
			if(!$oDB->isColumnExists("addons_site", "is_used_m")) { 
				$oDB->addColumn("addons_site", "is_used_m", "char", 1, "N", true);
			}
            return new Object();
        }

        /**
         * @brief Re-generate the cache file
         **/
        function recompileCache() {
        }

    }
?>
