<?php
    /**
     * @class  autoinstallAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief autoinstall module admin controller class
     **/

    require_once(_XE_PATH_.'modules/autoinstall/autoinstall.lib.php');

    class autoinstallAdminController extends autoinstall {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        function checkFileCheckSum($file, $checksum){
            $local_checksum = md5_file(FileHandler::getRealPath($file));
            return ($local_checksum === $checksum);
        }

        function _cleanDownloaded($obj){
            FileHandler::removeDir($obj->download_path);
        }

        function procAutoinstallAdminUpdateinfo()
        {
			$this->_updateinfo();
            $this->setMessage("success_updated", 'update');
			$this->setRedirectUrl(Context::get('error_return_url'));
        }

		function _updateinfo(){
            $oModel = &getModel('autoinstall');
            $item = $oModel->getLatestPackage();
            if($item)
            {
                $params["updatedate"] = $item->updatedate;
            }

            $params["act"] = "getResourceapiUpdate";
            $body = XmlGenerater::generate($params);
            $buff = FileHandler::getRemoteResource(_XE_DOWNLOAD_SERVER_, $body, 3, "POST", "application/xml");
            $xml = new XmlParser();
            $xmlDoc = $xml->parse($buff);
            $this->updateCategory($xmlDoc);
            $this->updatePackages($xmlDoc);
            $this->checkInstalled();
		}

        function checkInstalled()
        {
            executeQuery("autoinstall.deleteInstalledPackage");
            $oModel =& getModel('autoinstall');
            $packages = $oModel->getPackages();
            foreach($packages as $package)
            {
                $real_path = FileHandler::getRealPath($package->path);
                if(!file_exists($real_path)) {
                    continue;
                }

				$type = $oModel->getTypeFromPath($package->path);
				if($type == "core")
				{
                    $version = __ZBXE_VERSION__;
				}
                else
                {
					$config_file = null;
                    switch($type)
                    {
						case "m.layout":
							$type = "layout";
						case "module":
						case "addon":
						case "layout":
						case "widget":
                            $config_file = "/conf/info.xml";
                        break;
                        case "component":
                            $config_file = "/info.xml";
                        break;
						case "style":
						case "m.skin":
							$type = "skin";
						case "skin":
						case "widgetstyle":
                            $config_file = "/skin.xml";
                        break;
                        case "drcomponent":
                            $config_file = "/info.xml";
                            $type = "component";
                        break;
                    }
					if(!$config_file) continue;
                    $xml = new XmlParser();
                    $xmlDoc = $xml->loadXmlFile($real_path.$config_file);
                    if(!$xmlDoc) continue;
                    $version = $xmlDoc->{$type}->version->body;
                }

                $args = null;
                $args->package_srl = $package->package_srl;
                $args->version = $package->version;
                $args->current_version = $version;
                if(version_compare($args->version, $args->current_version, ">"))
                {
                    $args->need_update="Y";
                }
                else
                {
                    $args->need_update="N";
                }

                $output = executeQuery("autoinstall.insertInstalledPackage", $args);
            }
        }

        function procAutoinstallAdminPackageinstall()
        {
            @set_time_limit(0);
            $package_srls = Context::get('package_srl');
            $oModel =& getModel('autoinstall');
            $packages = explode(',', $package_srls);
            $ftp_info =  Context::getFTPInfo();
            if(!$_SESSION['ftp_password'])
            {
                $ftp_password = Context::get('ftp_password');
            }
            else
            {
                $ftp_password = $_SESSION['ftp_password'];
            }

            foreach($packages as $package_srl)
            {
                $package = $oModel->getPackage($package_srl);
                if($ftp_info->sftp && $ftp_info->sftp == 'Y')
                {
                    $oModuleInstaller = new SFTPModuleInstaller($package);
                }
                else if(function_exists(ftp_connect))
                {
                    $oModuleInstaller = new PHPFTPModuleInstaller($package);
                }
                else
                {
                    $oModuleInstaller = new FTPModuleInstaller($package);
                }

				$oModuleInstaller->setServerUrl(_XE_DOWNLOAD_SERVER_);
                $oModuleInstaller->setPassword($ftp_password);
                $output = $oModuleInstaller->install();
                if(!$output->toBool()) return $output;
            }

			$this->_updateinfo();

            $this->setMessage('success_installed', 'update');

			if (Context::get('return_url'))
			{
				$this->setRedirectUrl(Context::get('return_url'));
			}
			else
			{
				$this->setRedirectUrl(preg_replace('/act=[^&]*/', 'act=dispAutoinstallAdminIndex', Context::get('error_return_url')));
			}
        }

        function updatePackages(&$xmlDoc)
        {
            $oModel =& getModel('autoinstall');
            if(!$xmlDoc->response->packages->item) return;
            if(!is_array($xmlDoc->response->packages->item))
            {
                $xmlDoc->response->packages->item = array($xmlDoc->response->packages->item);
            }
            $targets = array('package_srl', 'updatedate', 'latest_item_srl', 'path', 'version', 'category_srl');
            foreach($xmlDoc->response->packages->item as $item)
            {
                $args = null;
                foreach($targets as $target)
                {
                    $args->{$target} = $item->{$target}->body;
                }
                if($oModel->getPackage($args->package_srl))
                {
                    $output = executeQuery("autoinstall.updatePackage", $args);
                }
                else
                {
                    $output = executeQuery("autoinstall.insertPackage", $args);
					if(!$output->toBool())
					{
						$output = executeQuery("autoinstall.deletePackage", $args);
						$output = executeQuery("autoinstall.insertPackage", $args);
					}
                }
            }
        }

        function updateCategory(&$xmlDoc)
        {
            executeQuery("autoinstall.deleteCategory");
            $oModel =& getModel('autoinstall');
            if(!is_array($xmlDoc->response->categorylist->item))
            {
                $xmlDoc->response->categorylist->item = array($xmlDoc->response->categorylist->item);
            }
            foreach($xmlDoc->response->categorylist->item as $item)
            {
                $args = null;
                $args->category_srl = $item->category_srl->body;
                $args->parent_srl = $item->parent_srl->body;
                $args->title = $item->title->body;
                $output = executeQuery("autoinstall.insertCategory", $args);
            }
        }

		function procAutoinstallAdminUninstallPackage()
		{
			$package_srl = Context::get('package_srl');
            $oModel =& getModel('autoinstall');
			$package = $oModel->getPackage($package_srl);
			$path = $package->path;

            if(!$_SESSION['ftp_password'])
            {
                $ftp_password = Context::get('ftp_password');
            }
            else
            {
                $ftp_password = $_SESSION['ftp_password'];
            }
            $ftp_info =  Context::getFTPInfo();

			if($ftp_info->sftp && $ftp_info->sftp == 'Y')
			{
				$oModuleInstaller = new SFTPModuleInstaller($package);
			}
			else if(function_exists('ftp_connect'))
			{
				$oModuleInstaller = new PHPFTPModuleInstaller($package);
			}
			else
			{
				$oModuleInstaller = new FTPModuleInstaller($package);
			}

			$oModuleInstaller->setServerUrl(_XE_DOWNLOAD_SERVER_);

			$oModuleInstaller->setPassword($ftp_password);
			$output = $oModuleInstaller->uninstall();
			if(!$output->toBool()) return $output;

			$this->_updateinfo();

			$this->setMessage('success_deleted', 'update');

			if (Context::get('return_url'))
			{
				$this->setRedirectUrl(Context::get('return_url'));
			}
			else
			{
				$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAutoinstallAdminInstalledPackages'));
			}
		}
    }
?>
