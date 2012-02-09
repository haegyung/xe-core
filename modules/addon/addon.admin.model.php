<?php
    /**
     * @class  addonAdminModel
     * @author NHN (developers@xpressengine.com)
     * @brief admin model class of addon modules
     **/

    class addonAdminModel extends addon {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Wanted to add the path to
         **/
        function getAddonPath($addon_name) {
            $class_path = sprintf('./addons/%s/', $addon_name);
            if(is_dir($class_path)) return $class_path;
            return "";
        }

		/**
		 * @brief Get addon list for super admin
		 **/
		function getAddonListForSuperAdmin()
		{
			$addonList = $this->getAddonList(0, 'site');

			$oAutoinstallModel = getModel('autoinstall');
			foreach($addonList as $key => $addon)
			{
				// get easyinstall remove url
				$packageSrl = $oAutoinstallModel->getPackageSrlByPath($addon->path);
				$addonList[$key]->remove_url = $oAutoinstallModel->getRemoveUrlByPackageSrl($packageSrl);

				// get easyinstall need update
				$package = $oAutoinstallModel->getInstalledPackages($packageSrl);
				$addonList[$key]->need_update = $package[$packageSrl]->need_update;

				// get easyinstall update url
				if ($addonList[$key]->need_update == 'Y')
				{
					$addonList[$key]->update_url = $oAutoinstallModel->getUpdateUrlByPackageSrl($packageSrl);
				}
			}

			return $addonList;
		}

        /**
         * @brief Wanted to add the kind of information and
         **/
        function getAddonList($site_srl = 0, $gtype = 'site') {
            // Wanted to add a list of activated
            $inserted_addons = $this->getInsertedAddons($site_srl, $gtype);
            // Downloaded and installed add-on to the list of Wanted
            $searched_list = FileHandler::readDir('./addons','/^([a-zA-Z0-9-_]+)$/');
            $searched_count = count($searched_list);
            if(!$searched_count) return;
            sort($searched_list);

			$oAddonAdminController = getAdminController('addon');

            for($i=0;$i<$searched_count;$i++) {
                // Add the name of
                $addon_name = $searched_list[$i];
				if($addon_name == "smartphone") continue;
                // Add the path (files/addons precedence)
                $path = $this->getAddonPath($addon_name);
                // Wanted information on the add-on
                unset($info);
                $info = $this->getAddonInfoXml($addon_name, $site_srl, $gtype);

                $info->addon = $addon_name;
                $info->path = $path;
                $info->activated = false;
				$info->mactivated = false;
				$info->fixed = false;
                // Check if a permossion is granted entered in DB
                if(!in_array($addon_name, array_keys($inserted_addons))) {
                    // If not, type in the DB type (model, perhaps because of the hate doing this haneungeo .. ㅡ. ㅜ)
                    $oAddonAdminController->doInsert($addon_name, $site_srl, $type);
                // Is activated
                } else {
                    if($inserted_addons[$addon_name]->is_used=='Y') $info->activated = true;
                    if($inserted_addons[$addon_name]->is_used_m=='Y') $info->mactivated = true;
					if ($gtype == 'global' && $inserted_addons[$addon_name]->is_fixed == 'Y') $info->fixed = true;
                }

                $list[] = $info;
            }
            return $list;
        }

        /**
         * @brief Modules conf/info.xml wanted to read the information
         **/
        function getAddonInfoXml($addon, $site_srl = 0, $gtype = 'site') {
            // Get a path of the requested module. Return if not exists.
            $addon_path = $this->getAddonPath($addon);
            if(!$addon_path) return;
            // Read the xml file for module skin information
            $xml_file = sprintf("%sconf/info.xml", $addon_path);
            if(!file_exists($xml_file)) return;

            $oXmlParser = new XmlParser();
            $tmp_xml_obj = $oXmlParser->loadXmlFile($xml_file);
            $xml_obj = $tmp_xml_obj->addon;

            if(!$xml_obj) return;


            // DB is set to bring history
            $db_args->addon = $addon;
            if($gtype == 'global') $output = executeQuery('addon.getAddonInfo',$db_args);
            else {
                $db_args->site_srl = $site_srl;
                $output = executeQuery('addon.getSiteAddonInfo',$db_args);
            }
            $extra_vals = unserialize($output->data->extra_vars);

            if($extra_vals->mid_list) {
                $addon_info->mid_list = $extra_vals->mid_list;
            } else {
                $addon_info->mid_list = array();
            }


            // Add information
            if($xml_obj->version && $xml_obj->attrs->version == '0.2') {
                // addon format v0.2
                sscanf($xml_obj->date->body, '%d-%d-%d', $date_obj->y, $date_obj->m, $date_obj->d);
                $addon_info->date = sprintf('%04d%02d%02d', $date_obj->y, $date_obj->m, $date_obj->d);

                $addon_info->addon_name = $addon;
                $addon_info->title = $xml_obj->title->body;
                $addon_info->description = trim($xml_obj->description->body);
                $addon_info->version = $xml_obj->version->body;
                $addon_info->homepage = $xml_obj->link->body;
                $addon_info->license = $xml_obj->license->body;
                $addon_info->license_link = $xml_obj->license->attrs->link;

                if(!is_array($xml_obj->author)) $author_list[] = $xml_obj->author;
                else $author_list = $xml_obj->author;

                foreach($author_list as $author) {
                    unset($author_obj);
                    $author_obj->name = $author->name->body;
                    $author_obj->email_address = $author->attrs->email_address;
                    $author_obj->homepage = $author->attrs->link;
                    $addon_info->author[] = $author_obj;
                }

                // Expand the variable order
                if($xml_obj->extra_vars) {
                    $extra_var_groups = $xml_obj->extra_vars->group;
                    if(!$extra_var_groups) $extra_var_groups = $xml_obj->extra_vars;
                    if(!is_array($extra_var_groups)) $extra_var_groups = array($extra_var_groups);

                    foreach($extra_var_groups as $group) {
                        $extra_vars = $group->var;
                        if(!is_array($group->var)) $extra_vars = array($group->var);

                        foreach($extra_vars as $key => $val) {
                            unset($obj);
                            if(!$val->attrs->type) { $val->attrs->type = 'text'; }

                            $obj->group = $group->title->body;
                            $obj->name = $val->attrs->name;
                            $obj->title = $val->title->body;
                            $obj->type = $val->attrs->type;
                            $obj->description = $val->description->body;
                            $obj->value = $extra_vals->{$obj->name};
                            if(strpos($obj->value, '|@|') != false) { $obj->value = explode('|@|', $obj->value); }
                            if($obj->type == 'mid_list' && !is_array($obj->value)) { $obj->value = array($obj->value); }

                            // 'Select'type obtained from the option list.
                            if(is_array($val->options)) {
                                $option_count = count($val->options);

                                for($i = 0; $i < $option_count; $i++) {
                                    $obj->options[$i]->title = $val->options[$i]->title->body;
                                    $obj->options[$i]->value = $val->options[$i]->attrs->value;
                                }
                            } else {
                                $obj->options[0]->title = $val->options[0]->title->body;
                                $obj->options[0]->value = $val->options[0]->attrs->value;
                            }

                            $addon_info->extra_vars[] = $obj;
                        }
                    }
                }

                // history
                if($xml_obj->history) {
                    if(!is_array($xml_obj->history)) $history[] = $xml_obj->history;
                    else $history = $xml_obj->history;

                    foreach($history as $item) {
                        unset($obj);

                        if($item->author) {
                            (!is_array($item->author)) ? $obj->author_list[] = $item->author : $obj->author_list = $item->author;

                            foreach($obj->author_list as $author) {
                                unset($author_obj);
                                $author_obj->name = $author->name->body;
                                $author_obj->email_address = $author->attrs->email_address;
                                $author_obj->homepage = $author->attrs->link;
                                $obj->author[] = $author_obj;
                            }
                        }

                        $obj->name = $item->name->body;
                        $obj->email_address = $item->attrs->email_address;
                        $obj->homepage = $item->attrs->link;
                        $obj->version = $item->attrs->version;
                        $obj->date = $item->attrs->date;
                        $obj->description = $item->description->body;

                        if($item->log) {
                            (!is_array($item->log)) ? $obj->log[] = $item->log : $obj->log = $item->log;

                            foreach($obj->log as $log) {
                                unset($log_obj);
                                $log_obj->text = $log->body;
                                $log_obj->link = $log->attrs->link;
                                $obj->logs[] = $log_obj;
                            }
                        }

                        $addon_info->history[] = $obj;
                    }
                }


            } else {
                // addon format 0.1
                $addon_info->addon_name = $addon;
                $addon_info->title = $xml_obj->title->body;
                $addon_info->description = trim($xml_obj->author->description->body);
                $addon_info->version = $xml_obj->attrs->version;
                sscanf($xml_obj->author->attrs->date, '%d. %d. %d', $date_obj->y, $date_obj->m, $date_obj->d);
                $addon_info->date = sprintf('%04d%02d%02d', $date_obj->y, $date_obj->m, $date_obj->d);
                $author_obj->name = $xml_obj->author->name->body;
                $author_obj->email_address = $xml_obj->author->attrs->email_address;
                $author_obj->homepage = $xml_obj->author->attrs->link;
                $addon_info->author[] = $author_obj;

                if($xml_obj->extra_vars) {
                    // Expand the variable order
                    $extra_var_groups = $xml_obj->extra_vars->group;
                    if(!$extra_var_groups) $extra_var_groups = $xml_obj->extra_vars;
                    if(!is_array($extra_var_groups)) $extra_var_groups = array($extra_var_groups);
                    foreach($extra_var_groups as $group) {
                        $extra_vars = $group->var;
                        if(!is_array($group->var)) $extra_vars = array($group->var);

                        foreach($extra_vars as $key => $val) {
                            unset($obj);
                            if(!$val->type->body) { $val->type->body = 'text'; }

                            $obj->group = $group->title->body;
                            $obj->name = $val->attrs->name;
                            $obj->title = $val->title->body;
                            $obj->type = $val->type->body;
                            $obj->description = $val->description->body;
                            $obj->value = $extra_vals->{$obj->name};
                            if(strpos($obj->value, '|@|') != false) { $obj->value = explode('|@|', $obj->value); }
                            if($obj->type == 'mid_list' && !is_array($obj->value)) { $obj->value = array($obj->value); }
                            // 'Select'type obtained from the option list.
                            if(is_array($val->options)) {
                                $option_count = count($val->options);

                                for($i = 0; $i < $option_count; $i++) {
                                    $obj->options[$i]->title = $val->options[$i]->title->body;
                                    $obj->options[$i]->value = $val->options[$i]->value->body;
                                }
                            }

                            $addon_info->extra_vars[] = $obj;
                        }
                    }
                }

            }



            return $addon_info;
        }

        /**
         * @brief Add to the list of active guhaeom
         **/
        function getInsertedAddons($site_srl = 0, $gtype = 'site') {
            $args->list_order = 'addon';
            if($gtype == 'global') $output = executeQuery('addon.getAddons', $args);
            else {
                $args->site_srl = $site_srl;
                $output = executeQuery('addon.getSiteAddons', $args);
            }
            if(!$output->data) return array();
            if(!is_array($output->data)) $output->data = array($output->data);

            $activated_count = count($output->data);
            for($i=0;$i<$activated_count;$i++) {
                $addon = $output->data[$i];
                $addon_list[$addon->addon] = $addon;
            }
            return $addon_list;
        }

        /**
         * @brief Add-on is enabled, check whether
         **/
        function isActivatedAddon($addon, $site_srl = 0, $type = "pc", $gtype = 'site') {
            $args->addon = $addon;
            if($gtype == 'global') {
				if($type == "pc") $output = executeQuery('addon.getAddonIsActivated', $args);
				else $output = executeQuery('addon.getMAddonIsActivated', $args);
			}
            else {
                $args->site_srl = $site_srl;
				if($type == "pc") $output = executeQuery('addon.getSiteAddonIsActivated', $args);
				else $output = executeQuery('addon.getSiteMAddonIsActivated', $args);
            }
            if($output->data->count>0) return true;
            return false;
        }

    }
?>
