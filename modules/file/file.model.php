<?php
    /**
     * @class  fileModel
     * @author NHN (developers@xpressengine.com)
     * @brief model class of the file module
     **/

    class fileModel extends file {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Return a file list attached in the document
         * It is used when a file list of the upload_target_srl is requested for creating/updating a document.
         * Attempt to replace with sever-side session if upload_target_srl is not yet determined
         **/
        function getFileList() {
            $oModuleModel = &getModel('module');

            $mid = Context::get('mid');
            $editor_sequence = Context::get('editor_sequence');
            $upload_target_srl = Context::get('upload_target_srl');
            if(!$upload_target_srl) $upload_target_srl = $_SESSION['upload_info'][$editor_sequence]->upload_target_srl;

            if($upload_target_srl) {
                $tmp_files = $this->getFiles($upload_target_srl);
                $file_count = count($tmp_files);

                for($i=0;$i<$file_count;$i++) {
                    $file_info = $tmp_files[$i];
                    if(!$file_info->file_srl) continue;

                    $obj = null;
                    $obj->file_srl = $file_info->file_srl;
                    $obj->source_filename = $file_info->source_filename;
                    $obj->file_size = $file_info->file_size;
                    $obj->disp_file_size = FileHandler::filesize($file_info->file_size);
                    if($file_info->direct_download=='N') $obj->download_url = $this->getDownloadUrl($file_info->file_srl, $file_info->sid);
                    else $obj->download_url = str_replace('./', '', $file_info->uploaded_filename);
                    $obj->direct_download = $file_info->direct_download;
                    $files[] = $obj;
                    $attached_size += $file_info->file_size;
                }
            } else {
                $upload_target_srl = 0;
                $attached_size = 0;
                $files = array();
            }
            // Display upload status
            $upload_status = $this->getUploadStatus($attached_size);
            // Check remained file size until upload complete
            $config = $oModuleModel->getModuleInfoByMid($mid);
            $file_config = $this->getUploadConfig();
            $left_size = $file_config->allowed_attach_size*1024*1024 - $attached_size;
            // Settings of required information
            $this->add("files",$files);
            $this->add("editor_sequence",$editor_sequence);
            $this->add("upload_target_srl",$upload_target_srl);
            $this->add("upload_status",$upload_status);
            $this->add("left_size",$left_size);
        }

        /**
         * @brief Return number of attachments which belongs to a specific document
         **/
        function getFilesCount($upload_target_srl) {
            $args->upload_target_srl = $upload_target_srl;
            $output = executeQuery('file.getFilesCount', $args);
            return (int)$output->data->count;
        }

        /**
         * @brief Get a download path
         **/
        function getDownloadUrl($file_srl, $sid) {
            return sprintf('?module=%s&amp;act=%s&amp;file_srl=%s&amp;sid=%s', 'file', 'procFileDownload', $file_srl, $sid);
        }

        /**
         * @brief Get file cinfigurations
         **/
        function getFileConfig($module_srl = null) {
            // Get configurations (using module model object)
            $oModuleModel = &getModel('module');

            $file_module_config = $oModuleModel->getModuleConfig('file');

            if($module_srl) $file_config = $oModuleModel->getModulePartConfig('file',$module_srl);
            if(!$file_config) $file_config = $file_module_config;

            if($file_config) {
                $config->allowed_filesize = $file_config->allowed_filesize;
                $config->allowed_attach_size = $file_config->allowed_attach_size;
                $config->allowed_filetypes = $file_config->allowed_filetypes;
                $config->download_grant = $file_config->download_grant;
                $config->allow_outlink = $file_config->allow_outlink;
                $config->allow_outlink_site = $file_config->allow_outlink_site;
                $config->allow_outlink_format = $file_config->allow_outlink_format;
            }
            // Property for all files comes first than each property
            if(!$config->allowed_filesize) $config->allowed_filesize = $file_module_config->allowed_filesize;
            if(!$config->allowed_attach_size) $config->allowed_attach_size = $file_module_config->allowed_attach_size;
            if(!$config->allowed_filetypes) $config->allowed_filetypes = $file_module_config->allowed_filetypes;
            if(!$config->allow_outlink) $config->allow_outlink = $file_module_config->allow_outlink;
            if(!$config->allow_outlink_site) $config->allow_outlink_site = $file_module_config->allow_outlink_site;
            if(!$config->allow_outlink_format) $config->allow_outlink_format = $file_module_config->allow_outlink_format;
            if(!$config->download_grant) $config->download_grant = $file_module_config->download_grant;
            // Default setting if not exists
            if(!$config->allowed_filesize) $config->allowed_filesize = '2';
            if(!$config->allowed_attach_size) $config->allowed_attach_size = '3';
            if(!$config->allowed_filetypes) $config->allowed_filetypes = '*.*';
            if(!$config->allow_outlink) $config->allow_outlink = 'Y';
            if(!$config->download_grant) $config->download_grant = array();

            return $config;
        }

        /**
         * @brief Get file information
         **/
        function getFile($file_srl, $columnList = array()) {
            $args->file_srl = $file_srl;
            $output = executeQuery('file.getFile', $args, $columnList);
            if(!$output->toBool()) return $output;

            $file = $output->data;
            $file->download_url = $this->getDownloadUrl($file->file_srl, $file->sid);

            return $file;
        }

        /**
         * @brief Return all files which belong to a specific document
         **/
        function getFiles($upload_target_srl, $columnList = array()) {
            $args->upload_target_srl = $upload_target_srl;
            $args->sort_index = 'file_srl';
            $output = executeQuery('file.getFiles', $args, $columnList);
            if(!$output->data) return;

            $file_list = $output->data;

            if($file_list && !is_array($file_list)) $file_list = array($file_list);

            $file_count = count($file_list);
            for($i=0;$i<$file_count;$i++) {
                $file = $file_list[$i];
                $file->source_filename = stripslashes($file->source_filename);
                $file->download_url = $this->getDownloadUrl($file->file_srl, $file->sid);
                $file_list[$i] = $file;
            }

            return $file_list;
        }

        /**
         * @brief Return configurations of the attachement (it automatically checks if an administrator is)
         **/
        function getUploadConfig() {
            $logged_info = Context::get('logged_info');
            if($logged_info->is_admin == 'Y') {
                $file_config->allowed_filesize = preg_replace("/[a-z]/is","",ini_get('upload_max_filesize'));
                $file_config->allowed_attach_size = preg_replace("/[a-z]/is","",ini_get('upload_max_filesize'));
                $file_config->allowed_filetypes = '*.*';
            } else {
                $module_srl = Context::get('module_srl');
                // Get the current module if module_srl doesn't exist
                if(!$module_srl) {
                    $current_module_info = Context::get('current_module_info');
                    $module_srl = $current_module_info->module_srl;
                }
                $file_config = $this->getFileConfig($module_srl);
            }
            return $file_config;
        }

        /**
         * @brief Return messages for file upload and it depends whether an admin is or not
         **/
        function getUploadStatus($attached_size = 0) {
            $file_config = $this->getUploadConfig();
            // Display upload status
            $upload_status = sprintf(
                    '%s : %s/ %s<br /> %s : %s (%s : %s)',
                    Context::getLang('allowed_attach_size'),
                    FileHandler::filesize($attached_size),
                    FileHandler::filesize($file_config->allowed_attach_size*1024*1024),
                    Context::getLang('allowed_filesize'),
                    FileHandler::filesize($file_config->allowed_filesize*1024*1024),
                    Context::getLang('allowed_filetypes'),
                    $file_config->allowed_filetypes
                );
            return $upload_status;
        }

        /**
         * @brief Return file configuration of the module
         **/
        function getFileModuleConfig($module_srl) {
            return $this->getFileConfig($module_srl);
        }
    }
?>
