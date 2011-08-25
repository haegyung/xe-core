<?php
    /**
     * @class  adminAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief  admin controller class of admin module
     **/

    class adminAdminController extends admin {
        /**
         * @brief initialization
         * @return none
         **/
        function init() {
            // forbit access if the user is not an administrator
            $oMemberModel = &getModel('member');
            $logged_info = $oMemberModel->getLoggedInfo();
            if($logged_info->is_admin!='Y') return $this->stop("msg_is_not_administrator");
        }

        /**
         * @brief Regenerate all cache files
         * @return none
         **/
        function procAdminRecompileCacheFile() {
			// rename cache dir
			$temp_cache_dir = './files/cache_'. time();
			FileHandler::rename('./files/cache', $temp_cache_dir);
			FileHandler::makeDir('./files/cache');

            // remove debug files
            FileHandler::removeFile(_XE_PATH_.'files/_debug_message.php');
            FileHandler::removeFile(_XE_PATH_.'files/_debug_db_query.php');
            FileHandler::removeFile(_XE_PATH_.'files/_db_slow_query.php');

            $oModuleModel = &getModel('module');
            $module_list = $oModuleModel->getModuleList();

            // call recompileCache for each module
            foreach($module_list as $module) {
                $oModule = null;
                $oModule = &getClass($module->module);
                if(method_exists($oModule, 'recompileCache')) $oModule->recompileCache();
            }

			// remove cache dir
			$tmp_cache_list = FileHandler::readDir('./files','/(^cache_[0-9]+)/');
			if($tmp_cache_list){
				foreach($tmp_cache_list as $tmp_dir){
					if($tmp_dir) FileHandler::removeDir('./files/'.$tmp_dir);
				}
			}

			$truncated = array();
			$oObjectCacheHandler = &CacheHandler::getInstance('object');
			$oTemplateCacheHandler = &CacheHandler::getInstance('template');

			if($oObjectCacheHandler->isSupport()){
				$truncated[] = $oObjectCacheHandler->truncate();
			}

			if($oTemplateCacheHandler->isSupport()){
				$truncated[] = $oTemplateCacheHandler->truncate();
			}

			if(count($truncated) && in_array(false,$truncated)){
				return new Object(-1,'msg_self_restart_cache_engine');
			}

            $this->setMessage('success_updated');
        }

        /**
         * @brief Logout
         * @return none
         **/
        function procAdminLogout() {
            $oMemberController = &getController('member');
            $oMemberController->procMemberLogout();

			header('Location: '.getNotEncodedUrl('', 'module','admin'));
        }

		function procAdminInsertThemeInfo(){
			$vars = Context::getRequestVars();
			$theme_file = _XE_PATH_.'files/theme/theme_info.php';

			$theme_output = sprintf('$theme_info->theme=\'%s\';', $vars->themeItem);
			$theme_output = $theme_output.sprintf('$theme_info->layout=%s;', $vars->layout);

			$site_info = Context::get('site_module_info');

			$args->site_srl = $site_info->site_srl;
			$args->layout_srl = $vars->layout;
			// layout submit
			$output = executeQuery('layout.updateAllLayoutInSiteWithTheme', $args);
			if (!$output->toBool()) return $output;

			$skin_args->site_srl = $site_info->site_srl;

			foreach($vars as $key=>$val){
				$pos = strpos($key, '-skin');
				if ($pos === false) continue;
				if ($val != '__skin_none__'){
					$module = substr($key, 0, $pos);
					$theme_output = $theme_output.sprintf('$theme_info->skin_info[%s]=\'%s\';', $module, $val);
					$skin_args->skin = $val;
					$skin_args->module = $module;
					if ($module == 'page')
					{
						$article_output = executeQueryArray('page.getArticlePageSrls');
						if (count($article_output->data)>0){
							$article_module_srls = array();
							foreach($article_output->data as $val){
								$article_module_srls[] = $val->module_srl;
							}
							$skin_args->module_srls = implode(',', $article_module_srls);
						}
					}
					$skin_output = executeQuery('module.updateAllModuleSkinInSiteWithTheme', $skin_args);
					if (!$skin_output->toBool()) return $skin_output;

					$oModuleModel = &getModel('module');
					$module_config = $oModuleModel->getModuleConfig($module, $site_info->site_srl);
					$module_config->skin = $val;
					$oModuleController = &getController('module');
					$oModuleController->insertModuleConfig($module, $module_config, $site_info->site_srl);
				}
			}

            $theme_buff = sprintf(
                '<?php '.
                'if(!defined("__ZBXE__")) exit(); '.
				'%s'.
                '?>',
				$theme_output
            );
            // Save File
            FileHandler::writeFile($theme_file, $theme_buff);

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
                $returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminTheme');
                header('location:'.$returnUrl);
                return;
            }
            else return $output;
		}

		/**
		 * @brief Toggle favorite
		 **/
		function procAdminToggleFavorite()
		{
			$siteSrl = Context::get('site_srl');
			$moduleName = Context::get('module_name');
			$key = Context::get('key');

			// check favorite exists
			$oModel = &getAdminModel('admin');
			$output = $oModel->isExistsFavorite($siteSrl, $moduleName, $key);
			if (!$output->toBool()) return $output;

			// if exists, delete favorite
			if ($output->get('result'))
			{
				$favoriteSrl = $output->get('favoriteSrl');
				$output = $this->deleteFavorite($favoriteSrl);
			}

			// if not exists, insert favorite
			else
			{
				$output = $this->insertFavorite($siteSrl, $moduleName, $key);
			}

			if (!$output->toBool()) return $output;

			$this->setRedirectUrl(Context::get('error_return_url'));
		}

		/**
		 * @brief enviroment gathering agreement
		 **/
		function procAdminEnviromentGatheringAgreement()
		{
			$isAgree = Context::get('is_agree');
			if($isAgree == 'true')
				$_SESSION['enviroment_gather'] = 'Y';

			$redirectUrl = getUrl('', 'module', 'admin');
			$this->setRedirectUrl($redirectUrl);
		}

		/**
		 * @brief admin config update
		 **/
		function procAdminUpdateConfig()
		{
			$adminTitle = Context::get('adminTitle');
            $file = $_FILES['adminLogo'];

            $oModuleModel = &getModel('module');
            $oAdminConfig = $oModuleModel->getModuleConfig('admin');

			if($file['tmp_name'])
			{
				$target_path = 'files/attach/images/admin/';
				FileHandler::makeDir($target_path);

				// Get file information
				list($width, $height, $type, $attrs) = @getimagesize($file['tmp_name']);
				if($type == 3) $ext = 'png';
				elseif($type == 2) $ext = 'jpg';
				else $ext = 'gif';

				$target_filename = sprintf('%s%s.%s.%s', $target_path, 'adminLogo', date('YmdHis'), $ext);
				@move_uploaded_file($file['tmp_name'], $target_filename);

				$oAdminConfig->adminLogo = $target_filename;
			}
			if($adminTitle) $oAdminConfig->adminTitle = strip_tags($adminTitle);

			if($oAdminConfig)
			{
				$oModuleController = &getController('module');
				$oModuleController->insertModuleConfig('admin', $oAdminConfig);
			}

			$this->setMessage('success_updated', 'info');
			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
                $returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminMenuSetup');
				$this->setRedirectUrl($returnUrl);
                return;
            }
		}

		/**
		 * @brief admin logo delete
		 **/
		function procAdminDeleteLogo()
		{
            $oModuleModel = &getModel('module');
            $oAdminConfig = $oModuleModel->getModuleConfig('admin');

            FileHandler::removeFile(_XE_PATH_.$oAdminConfig->adminLogo);
			unset($oAdminConfig->adminLogo);

			$oModuleController = &getController('module');
			$oModuleController->insertModuleConfig('admin', $oAdminConfig);

			$this->setMessage('success_deleted', 'info');
			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
                $returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAdminMenuSetup');
				$this->setRedirectUrl($returnUrl);
                return;
            }
		}

		/**
		 * @brief Insert favorite
		 **/
		function insertFavorite($siteSrl, $module, $key)
		{
			$args->site_srl = $siteSrl;
			$args->module = $module;
			$args->key = $key;
			$output = executeQuery('admin.insertFavorite', $args);
			return $output;
		}

		/**
		 * @brief Delete favorite
		 **/
		function deleteFavorite($favoriteSrl)
		{
			$args->admin_favorite_srl = $favoriteSrl;
			$output = executeQuery('admin.deleteFavorite', $args);
			return $output;
		}

		/**
		 * @brief set favorites at one time
		 **/
		function setFavoritesByModule($siteSrl, $module, $keyList)
		{
			$oModel = &getAdminModel('admin');
			$output = $oModel->getFavoriteListByModule($siteSrl, $module);
			if (!$output->toBool()) return $output;
			$originList = $output->get('list');

			// find insert key
			$insertKey = array_diff($keyList, $originList);

			// find delete key
			$deleteKey = array_diff($originList, $keyList);

			// start transaction
			$oDB = &DB::getInstance();
			$oDB->begin();

			// insert key
			foreach($insertKey as $key)
			{
				$output = $this->insertFavorite($siteSrl, $module, $key);
				if (!$output->toBool())
				{
					$oDB->rollback();
					return $output;
				}
			}

			// delete key
			foreach($deleteKey as $key)
			{
				$output = $oModel->isExistsFavorite($siteSrl, $module, $key);
				if (!$output->toBool())
				{
					$oDB->rollback();
					return $output;
				}
				$favoriteSrl = $output->get('favoriteSrl');

				if ($favoriteSrl)
				{
					$output = $this->deleteFavorite($favoriteSrl);
					if (!$output->toBool())
					{
						$oDB->rollback();
						return $output;
					}
				}
			}

			// commit
			$oDB->commit();

			return new Object();
		}
    }
?>
