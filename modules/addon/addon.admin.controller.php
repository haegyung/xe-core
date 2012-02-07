<?php
    /**
     * @class  addonAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief admin controller class of addon modules
     **/

    require_once(_XE_PATH_.'modules/addon/addon.controller.php');

    class addonAdminController extends addonController {

        /**
         * @brief Initialization
         **/
        function init() {
        }

		/**
		 * @brief Set addon activate
		 **/
		function procAddonAdminSaveActivate()
		{
			$pcOnList = Context::get('pc_on');
			$mobileOnList = Context::get('mobile_on');
			$fixed = Context::get('fixed');
			
			$site_module_info = Context::get('site_module_info');
			
			if($site_module_info->site_srl) $site_srl = $site_module_info->site_srl;
			else $site_srl = 0;

			if (!$pcOnList) $pcOnList = array();
			if (!$mobileOnList) $mobileOnList = array();
			if (!$fixed) $fixed = array();

			if (!is_array($pcOnList)) $pcOnList = array($pcOnList);
			if (!is_array($mobileOnList)) $pcOnList = array($mobileOnList);
			if (!is_array($fixed)) $pcOnList = array($fixed);

			// get current addon info
			$oModel = getAdminModel('addon');
			$currentAddonList = $oModel->getAddonList($site_srl, 'site');

			// get need update addon list
			$updateList = array();
			foreach($currentAddonList as $addon)
			{
				if ($addon->activated !== in_array($addon->addon_name, $pcOnList))
				{
					$updateList[] = $addon->addon_name;
					continue;
				}

				if ($addon->mactivated !== in_array($addon->addon_name, $mobileOnList))
				{
					$updateList[] = $addon->addon_name;
					continue;
				}

				if ($addon->fixed !== in_array($addon->addon_name, $fixed))
				{
					$updateList[] = $addon->addon_name;
					continue;
				}
			}

			// update
			foreach($updateList as $targetAddon)
			{
				unset($args);

				if (in_array($targetAddon, $pcOnList))
					$args->is_used = 'Y';
				else
					$args->is_used = 'N';

				if (in_array($targetAddon, $mobileOnList))
					$args->is_used_m = 'Y';
				else
					$args->is_used_m = 'N';

				if (in_array($targetAddon, $fixed))
					$args->fixed = 'Y';
				else
					$args->fixed = 'N';

				$args->addon = $targetAddon;
				$args->site_srl = $site_srl;

				$output = executeQuery('addon.updateSiteAddon', $args);
				if (!$output->toBool()) return $output;
			}

			if (count($updateList))
			{
				$this->makeCacheFile($site_srl, 'pc', 'site');
				$this->makeCacheFile($site_srl, 'mobile', 'site');
			}

            $this->setMessage('success_updated', 'info');
			if (Context::get('success_return_url'))
			{
				$this->setRedirectUrl(Context::get('success_return_url'));
			}
			else
			{
				$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAddonAdminIndex'));
			}
		}

        /**
         * @brief Add active/inactive change
         **/
        function procAddonAdminToggleActivate() {
			$oAddonModel = getAdminModel('addon');

			$site_module_info = Context::get('site_module_info');
			// batahom addon values
			$addon = Context::get('addon');
			$type = Context::get('type');
			if(!$type) $type = "pc";
			if($addon) {
				// If enabled Disables
				if($oAddonModel->isActivatedAddon($addon, $site_module_info->site_srl, $type)) $this->doDeactivate($addon, $site_module_info->site_srl, $type);
				// If it is disabled Activate
				else $this->doActivate($addon, $site_module_info->site_srl, $type);
			}

			$this->makeCacheFile($site_module_info->site_srl, $type);
        }

        /**
         * @brief Add the configuration information input
         **/
        function procAddonAdminSetupAddon() {
            $args = Context::getRequestVars();
			$module = $args->module;
            $addon_name = $args->addon_name;
            unset($args->module);
            unset($args->act);
            unset($args->addon_name);
            unset($args->body);
			unset($args->error_return_url);

            $site_module_info = Context::get('site_module_info');

            $output = $this->doSetup($addon_name, $args, $site_module_info->site_srl, 'site');
			if (!$output->toBool()) return $output;

            $this->makeCacheFile($site_module_info->site_srl, "pc", 'site');
            $this->makeCacheFile($site_module_info->site_srl, "mobile", 'site');

			$this->setRedirectUrl(getNotEncodedUrl('', 'module', $module, 'act', 'dispAddonAdminSetup', 'selected_addon', $addon_name));
        }



        /**
         * @brief Add-on
         * Adds Add to DB
         **/
        function doInsert($addon, $site_srl = 0, $gtype = 'site', $isUsed = 'N') {
            $args->addon = $addon;
            $args->is_used = $isUsed;
            if($gtype == 'global') return executeQuery('addon.insertAddon', $args);
            $args->site_srl = $site_srl;
            return executeQuery('addon.insertSiteAddon', $args);
        }

        /**
         * @brief Add-activated
         * addons add-ons to the table on the activation state sikyeojum
         **/
        function doActivate($addon, $site_srl = 0, $type = "pc", $gtype = 'site') {
            $args->addon = $addon;
			if($type == "pc") $args->is_used = 'Y';
			else $args->is_used_m = "Y";
            if($gtype == 'global') return executeQuery('addon.updateAddon', $args);
            $args->site_srl = $site_srl;
            return executeQuery('addon.updateSiteAddon', $args);
        }

        /**
         * @brief Disable Add-ons
         *
         * addons add a table to remove the name of the deactivation is sikige
         **/
        function doDeactivate($addon, $site_srl = 0, $type = "pc", $gtype = 'site') {
            $args->addon = $addon;
			if($type == "pc") $args->is_used = 'N';
			else $args->is_used_m = 'N';
            if($gtype == 'global') return executeQuery('addon.updateAddon', $args);
            $args->site_srl = $site_srl;
            return executeQuery('addon.updateSiteAddon', $args);
        }
    }
?>
