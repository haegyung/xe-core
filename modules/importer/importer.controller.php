<?php
    /**
     * @class  importerController
     * @author zero (zero@nzeo.com)
     * @brief  importer 모듈의 Controller class
     **/

    class importerController extends importer {

        var $oXml = null;
        var $oMemberController = null;
        var $oDocumentController = null;

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief import step1
         * import하려는 대상에 따라 결과값을 구해서 return
         * 회원정보 : next_step=2, module_list = null
         * 모듈정보 : next_step=12, module_list = modules..
         * 회원정보 동기화 : next_step=3
         **/
        function procImporterAdminStep1() {
            $source_type = Context::get('source_type');
            switch($source_type) {
                case 'module' :
                        // 모듈 목록을 구함
                        $oModuleModel = &getModel('module');
                        $module_list = $oModuleModel->getMidList();
                        foreach($module_list as $key => $val) {
                            $module_list_arr[] = sprintf('%d,%s (%s)', $val->module_srl, $val->browser_title, $val->mid);
                        }
                        if(count($module_list_arr)) $module_list = implode("\n",$module_list_arr);
                        $next_step = 12;
                    break;
                case 'member' :
                        $next_step = 2;
                    break;
                case 'syncmember' :
                        $next_step = 3;
                    break;
            }

            $this->add('next_step', $next_step);
            $this->add('module_list', $module_list);
        }

        /**
         * @brief import step12
         * module_srl을 이용하여 대상 모듈에 카테고리값이 있는지 확인하여
         * 있으면 카테고리 정보를 return, 아니면 파일 업로드 단계로 이동
         **/
        function procImporterAdminStep12() {
            $target_module= Context::get('target_module');

            // 대상 모듈의 카테고리 목록을 구해옴
            $oDocumentModel = &getModel('document');
            $category_list = $oDocumentModel->getCategoryList($target_module);

            if(count($category_list)) {
                foreach($category_list as $key => $val) {
                    $category_list_arr[] = sprintf('%d,%s', $val->category_srl, $val->title);
                }
                if(count($category_list_arr)) {
                    $category_list = implode("\n",$category_list_arr);
                    $next_step = 13;
                }
            } else {
                $category_list = null;
                $next_step = 2;
            }

            $this->add('next_step', $next_step);
            $this->add('category_list', $category_list);
        }

        /**
         * @brief import 실행
         **/
        function procImporterAdminImport() {
            set_time_limit(0);

            // 변수 체크
            $module_srl = Context::get('module_srl');
            $category_srl = Context::get('category_list');
            $xml_file = Context::get('xml_file');

            // 파일을 찾을 수 없으면 에러 표시
            if(!file_exists($xml_file)) return new Object(-1,'msg_no_xml_file');

            $this->oXml = new XmlParser();

            // module_srl이 있으면 module데이터로 판단하여 처리, 아니면 회원정보로..
            if($module_srl) return $this->importModule($xml_file, $module_srl, $category_srl);
            return $this->importMember($xml_file);
        }

        /**
         * @brief 회원정보 import
         **/
        function importMember($xml_file) {
            $filesize = filesize($xml_file);
            if($filesize<1) return;

            $this->oMemberController = &getController('member');

            $fp = fopen($xml_file, "r");
            $readed_size = 0;
            if($fp) {
                while(!feof($fp) && $readed_size<=$filesize) {
                    $str = fgets($fp, 256);
                    $readed_size += strlen($str);

                    $buff .= $str;
                    $buff = preg_replace_callback('!<member user_id="([^\"]*)">([^<]*)</member>!is', array($this, '_importMember'), $buff);

                    if(eregi('</root>', $str)) break;
                }
                fclose($fp);
            }

            return new Object(0,'msg_import_finished');
        }

        function _importMember($matches) {
            $user_id = $matches[1];
            $buff = sprintf('<member>%s</member>',base64_decode($matches[2]));
            $xml_doc = $this->oXml->parse($buff);

            $args->user_id = $xml_doc->member->user_id->body;
            $args->user_name = $xml_doc->member->user_name->body;
            $args->nick_name = $xml_doc->member->nick_name->body;
            $args->homepage = $xml_doc->member->homepage->body;
            $args->birthday = $xml_doc->member->birthday->body;
            $args->email_address = $xml_doc->member->email_address->body;
            $args->password = $xml_doc->member->password->body;
            $args->regdate = $xml_doc->member->regdate->body;
            $args->allow_mailing = $xml_doc->member->allow_mailing->body;
            $args->allow_message = 'Y';
            $output = $this->oMemberController->insertMember($args);

            if($output->toBool()) {
                $member_srl = $output->get('member_srl');
                if($xml_doc->member->image_nickname->body) {
                    $image_nickname = base64_decode($xml_doc->member->image_nickname->body);
                    $fp = fopen('./files/cache/tmp_imagefile','w');
                    fwrite($fp, $image_nickname);
                    fclose($fp);

                    $this->oMemberController->insertImageName($member_srl, './files/cache/tmp_imagefile');
                }
                if($xml_doc->member->image_mark->body) {
                    $image_mark = base64_decode($xml_doc->member->image_mark->body);
                    $fp = fopen('./files/cache/tmp_imagefile','w');
                    fwrite($fp, $image_mark);
                    fclose($fp);

                    $this->oMemberController->insertImageMark($member_srl, './files/cache/tmp_imagefile');
                }
                @unlink('./files/cache/tmp_imagefile');
                if($xml_doc->member->signature->body) {
                    $oMemberController->putSignature($member_srl, $xml_doc->member->signature->body);
                }
            }

            usleep(100);
            return '';
        }

        /**
         * @brief 게시물 import
         **/
        function importModule($xml_file) {
            $filesize = filesize($xml_file);
            if($filesize<1) return;

            $this->oDocumentController = &getController('document');

            $fp = fopen($xml_file, "r");
            $readed_size = 0;
            if($fp) {
                while(!feof($fp) && $readed_size<=$filesize) {
                    $str = fgets($fp, 1024);
                    $readed_size += strlen($str);

                    $buff .= $str;
                    $buff = preg_replace_callback('!<document sequence="([^\"]*)">([^<]*)</document>!is', array($this, '_importModule'), $buff);
                }
                fclose($fp);
            }
            
            return new Object(-1,'haha');
        }

        function _importModule($matches) {
            $user_id = $matches[1];
            $xml_doc = sprintf('<document>%s</document>',base64_decode($matches[2]));
            return '';
        }
    }
?>
