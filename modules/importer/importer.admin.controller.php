<?php
    /**
     * @class  importerAdminController
     * @author zero (zero@nzeo.com)
     * @brief  importer 모듈의 admin controller class
     **/

    class importerAdminController extends importer {

        var $oMemberController = null;
        var $oMemberModel = null;
        var $default_group_srl = 0;

        var $oDocumentController = null;
        var $oDocumentModel = null;
        var $oCommentController = null;
        var $oTrackbackController = null;

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 회원정보와 게시물 정보를 싱크
         **/
        function procImporterAdminSync() {
            set_time_limit(0);

            // 게시물정보 싱크
            $output = executeQuery('importer.updateDocumentSync');

            // 댓글정보 싱크
            $output = executeQuery('importer.updateCommentSync');

            $this->setMessage('msg_sync_completed');
        }

        /**
         * @brief 회원xml 파일을 분석해서 import
         **/
        function procImporterAdminMemberImport() {
            set_time_limit(0);

            $xml_file = Context::get('xml_file');
            $total_count = (int)Context::get('total_count');
            $success_count = (int)Context::get('success_count');
            $readed_line = (int)Context::get('readed_line');

            // xml_file 경로가 없으면 에러~
            if(!$xml_file) return new Object(-1, 'msg_no_xml_file');

            // local 파일 지정인데 파일이 없으면 역시 에러~
            if(!eregi('^http:',$xml_file) && (!eregi("\.xml$", $xml_file) || !file_exists($xml_file)) ) return new Object(-1,'msg_no_xml_file');

            // 이제부터 데이터를 가져오면서 처리
            $fp = $this->getFilePoint($xml_file);
            if(!$fp) return new Object(-1,'msg_no_xml_file');

            // 회원 입력을 위한 기본 객체들 생성
            $this->oMemberController = &getController('member');
            $this->oMemberModel = &getModel('member');

            // 기본 회원 그룹을 구함
            $default_group = $this->oMemberModel->getDefaultGroup();
            $this->default_group_srl = $default_group->group_srl;

            $obj = null;
            $extra_var = null;
            $is_extra_var = false;

            $inserted_count = 0;

            // 본문 데이터부터 처리 시작
            $read_line = 0;
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));

                if($str == "</member>") $read_line ++;
                if($read_line < $readed_line) continue;

                // 한 아이템 준비 시작
                if($str == '<member>') {
                    $obj = $extra_var = null;
                    $is_extra_var = false;
                    continue;

                // 추가 변수 시자r 일 경우 
                } elseif($str == '<extra_vars>') {
                    $is_extra_var = true;

                // 추가 변수 종료 일 경우 
                } elseif($str == '</extra_vars>') {
                    $is_extra_var = false;
                    $obj->extra_vars = $extra_var;

                // 아이템 종료시 DB 입력
                } else if( $str == '</member>') {
                    if($this->importMember($obj)) $inserted_count ++;
                    if($inserted_count >= 300) {
                        $manual_break = true;
                        break;
                    }

                // members 태그 체크 (전체 개수를 구함)
                } else if(substr($str,0,8)=='<members') {
                    preg_match('/count="([0-9]+)"/i', $str, $matches);
                    $total_count = $matches[1];
                    continue;

                // 선언구 패스~
                } else if(substr($str,0,5)=='<?xml') {
                    continue;
                    
                // 종료 부분
                } else if($str == '</members>') {
                    break;

                // 이미지 마크, 이미지이름, 프로필 사진일 경우
                } else if(in_array($str, array('<image_mark>','<image_nickname>','<profile_image>'))) {

                    // binary buffer일 경우 임의의 위치에 파일을 저장시켜 놓고 그 파일의 경로를 return
                    $key = substr($str,1,strlen($str)-2);
                    $filename = $this->readFileBuff($fp, $key);
                    $obj->{$key} = $filename;

                // 변수 체크
                } else {
                    $buff .= $str;
                    $pos = strpos($buff, '>');
                    $key = substr($buff, 1, $pos-1);
                    if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                        $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                        if($is_extra_var) $extra_var->{$key} = $val;
                        else $obj->{$key} = $val;
                        $buff = null;
                    }
                }
            }

            fclose($fp);

            $success_count += $inserted_count;

            if($manual_break) {
                $this->add('total_count',$total_count);
                $this->add('success_count',$success_count);
                $this->add('readed_line',$read_line);
                $this->add('is_finished','0');
                $this->setMessage(sprintf(Context::getLang('msg_importing'), $total_count, $success_count));
            } else {
                $this->add('is_finished','1');
                $this->setMessage(sprintf(Context::getLang('msg_import_finished'), $success_count, $total_count));
            }
        }

        /**
         * @brief 주어진 xml 파일을 파싱해서 회원 정보 입력
         **/
        function importMember($obj) {
            // homepage, blog의 url을 정확히 만듬
            if($obj->homepage && !eregi("^http:\/\/",$obj->homepage)) $obj->homepage = 'http://'.$obj->homepage;
            if($obj->blog && !eregi("^http:\/\/",$obj->blog)) $obj->blog = 'http://'.$obj->blog;

            $obj->email_address = $obj->email;
            list($obj->email_id, $obj->email_host) = explode('@', $obj->email);

            if($obj->allow_mailing!='Y') $obj->allow_mailing = 'N';

            $obj->allow_message = 'Y';
            if(!in_array($obj->allow_message, array('Y','N','F'))) $obj->allow_message= 'Y';

            $obj->member_srl = getNextSequence();

            $extra_vars = $obj->extra_vars;
            unset($obj->extra_vars);
            $obj->extra_vars = serialize($extra_vars);

            $output = executeQuery('member.insertMember', $obj);

            // 입력실패시 닉네임 중복인지 확인
            if(!$output->toBool()) {

                // 닉네임 중복이라면 닉네임을 바꾸어서 입력
                $nick_args->nick_name = $obj->nick_name;
                $nick_output = executeQuery('member.getMemberSrl', $nick_args);
                if($nick_ouptut->data->member_srl) {
                    $obj->nick_name .= rand(111,999);
                    $output = executeQuery('member.insertMember', $obj);
                }
            }

            // 입력 성공시
            if($output->toBool()) {

                // 기본 그룹 가입 시킴 
                $obj->group_srl = $this->default_group_srl;
                executeQuery('member.addMemberToGroup',$obj);

                // 이미지네임
                if($obj->image_nickname && file_exists($obj->image_nickname)) {
                    $target_path = sprintf('files/member_extra_info/image_name/%s/', getNumberingPath($obj->member_srl));
                    $target_filename = sprintf('%s%d.gif', $target_path, $obj->member_srl);
                    @rename($obj->image_nickname, $target_filename);
                }

                // 이미지마크
                if($obj->image_mark && file_exists($obj->image_mark)) {
                    $target_path = sprintf('files/member_extra_info/image_mark/%s/', getNumberingPath($obj->member_srl));
                    if(!is_dir($target_path)) FileHandler::makeDir($target_path);
                    $target_filename = sprintf('%s%d.gif', $target_path, $obj->member_srl);
                    @rename($obj->image_mark, $target_filename);
                }

                // 프로필 이미지
                if($obj->profile_image && file_exists($obj->profile_image)) {
                    $target_path = sprintf('files/member_extra_info/profile_image/%s/', getNumberingPath($obj->member_srl));
                    if(!is_dir($target_path)) FileHandler::makeDir($target_path);
                    $target_filename = sprintf('%s%d.gif', $target_path, $obj->member_srl);
                    @rename($obj->profile_image, $target_filename);
                }

                // 서명
                if($obj->signature) {
                    $signature = removeHackTag($obj->signature);
                    $signature_buff = sprintf('<?php if(!defined("__ZBXE__")) exit();?>%s', $signature);

                    $target_path = sprintf('files/member_extra_info/signature/%s/', getNumberingPath($obj->member_srl));
                    if(!is_dir($target_path)) FileHandler::makeDir($target_path);
                    $target_filename = sprintf('%s%d.signature.php', $target_path, $obj->member_srl);

                    FileHandler::writeFile($target_filename, $signature_buff);
                }
                return true;
            }

            return false;
        }

        function procImporterAdminModuleImport() {
            set_time_limit(0);

            $xml_file = Context::get('xml_file');
            $target_module = Context::get('target_module');
            $total_count = (int)Context::get('total_count');
            $success_count = (int)Context::get('success_count');
            $readed_line = (int)Context::get('readed_line');

            // xml_file 경로가 없으면 에러~
            if(!$xml_file) return new Object(-1, 'msg_no_xml_file');

            // local 파일 지정인데 파일이 없으면 역시 에러~
            if(!eregi('^http:',$xml_file) && (!eregi("\.xml$", $xml_file) || !file_exists($xml_file)) ) return new Object(-1,'msg_no_xml_file');

            // 필요한 객체 미리 생성
            $this->oDocumentController = &getController('document');
            $this->oDocumentModel = &getModel('document');
            $this->oCommentController = &getController('comment');
            $this->oTrackbackController = &getController('trackback');

            // 타켓 모듈의 유무 체크
            if(!$target_module) return new Object(-1,'msg_invalid_request');
            $module_srl = $target_module;

            // 타겟 모듈의 카테고리 정보 구함
            $category_list = $this->oDocumentModel->getCategoryList($module_srl);
            if(count($category_list)) {
                foreach($category_list as $key => $val) $category_titles[$val->title] = $val->category_srl;
            } else {
                $category_list = $category_titles = array();
            }

            // 이제부터 데이터를 가져오면서 처리
            $fp = $this->getFilePoint($xml_file);
            if(!$fp) return new Object(-1,'msg_no_xml_file');

            $manual_break = false;
            $obj = null;
            $document_srl = null;

            $inserted_count = 0;

            // 본문 데이터부터 처리 시작
            $read_line = 0;
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));

                if($str == "</post>") $read_line ++;
                if($read_line < $readed_line) continue;

                // 한 아이템 준비 시작
                if(substr($str,0,6) == '<post>') {
                    $obj = null;
                    $obj->module_srl = $module_srl;
                    $obj->document_srl = getNextSequence();
                    continue;

                // 아이템 종료시 DB 입력
                } else if( $str == '</post>') {
                    if($this->importDocument($obj)) $inserted_count ++;
                    if($inserted_count >= 50) {
                        $manual_break = true;
                        break;
                    }

                // posts 태그 체크 (전체 개수를 구함)
                } else if(substr($str,0,6)=='<posts') {
                    preg_match('/count="([0-9]+)"/i', $str, $matches);
                    $total_count = $matches[1];
                    continue;

                // 선언구 패스~
                } else if(substr($str,0,5)=='<?xml') {
                    continue;
                    
                // 종료 부분
                } else if($str == '</posts>') {
                    $manual_break = false;
                    break;

                // 카테고리 입력
                } else if($str == '<categories>') {
                    $this->importCategoris($fp, $module_srl, $category_list, $category_titles);
                    continue;

                // 엮인글 입력
                } else if(substr($str,0,11) == '<trackbacks') {
                    $obj->trackbacks = $this->importTrackbacks($fp);

                // 댓글 입력
                } else if(substr($str,0,9) == '<comments') {
                    $obj->comments = $this->importComments($fp);

                // 첨부파일 입력
                } else if(substr($str,0,9) == '<attaches') {
                    $obj->attaches = $this->importAttaches($fp);

                // 추가 변수 시작 일 경우 
                } elseif($str == '<extra_vars>') {
                    $this->importExtraVars($fp, $obj);

                // 변수 체크
                } else {
                    $buff .= $str;
                    $pos = strpos($buff, '>');
                    $key = substr($buff, 1, $pos-1);
                    if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                        $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                        if($key == 'category' && $category_titles[$val]) $obj->category_srl = $category_titles[$val];
                        else $obj->{$key} = $val;
                        $buff = null;
                    }
                }
            }

            fclose($fp);

            $success_count += $inserted_count;

            if($manual_break) {
                $this->add('total_count',$total_count);
                $this->add('success_count',$success_count);
                $this->add('readed_line',$read_line);
                $this->add('is_finished','0');
                $this->setMessage(sprintf(Context::getLang('msg_importing'), $total_count, $success_count));
            } else {
                $this->add('is_finished','1');
                $this->setMessage(sprintf(Context::getLang('msg_import_finished'), $success_count, $total_count));
            }
        }

        /**
         * @brief 엮인글 정리
         **/
        function importTrackbacks($fp) {
            $trackbacks = array();
            $obj = null;
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                if($str == '</trackbacks>') break;
                elseif($str == '<trackback>') {
                    $obj = null;
                    continue;

                } elseif($str == '</trackback>') {
                    $trackbacks[] = $obj;
                    continue;
                }

                $buff .= $str;
                $pos = strpos($buff, '>');
                $key = substr($buff, 1, $pos-1);
                if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                    $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                    $obj->{$key} = $val;
                    $buff = null;
                }
            }

            return $trackbacks;
        }

        /**
         * @brief 댓글 정리
         **/
        function importComments($fp) {
            $comments = array();
            $obj = null;
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                // 댓글 전체 끝
                if($str == '</comments>') break;

                // 개별 댓글 시작
                elseif($str == '<comment>') {
                    $obj = null;
                    continue;

                // 댓글 끝
                } elseif($str == '</comment>') {
                    $comments[] = $obj;
                    continue;
                // 첨부파일이 있을때
                } elseif($str == '<attaches>') {
                    $obj->attaches = $this->importAttaches($fp);
                }

                $buff .= $str;
                $pos = strpos($buff, '>');
                $key = substr($buff, 1, $pos-1);
                if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                    $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                    $obj->{$key} = $val;
                    $buff = null;
                }
            }

            return $comments;
        }

        /**
         * @brief 댓글 정리
         **/
        function importAttaches($fp) {
            $attaches = array();
            $obj = null;
            $index = 0;

            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                if($str == '</attaches>') break;
                elseif($str == '<attach>') {
                    $obj = null;
                    continue;

                } elseif($str == '</attach>') {
                    $attaches[] = $obj;
                    continue;

                // 첨부파일
                } else if($str == '<file>') {
                    // binary buffer일 경우 임의의 위치에 파일을 저장시켜 놓고 그 파일의 경로를 return
                    $filename = $this->readFileBuff($fp, 'file');
                    $obj->file = $filename;
                }

                $buff .= $str;
                $pos = strpos($buff, '>');
                $key = substr($buff, 1, $pos-1);
                if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                    $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                    $obj->{$key} = $val;
                    $buff = null;
                }
            }

            return $attaches;
        }

        /**
         * @brief 카테고리 정보 가져오기
         **/
        function importCategoris($fp, $module_srl,  &$category_list, &$category_titles) {
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                if($str == '</categories>') break;

                $category = base64_decode(substr($str,10,-11));
                if($category_titles[$category]) continue;

                $obj = null;
                $obj->title = $category;
                $obj->module_srl = $module_srl; 
                $output = $this->oDocumentController->insertCategory($obj);
            }

            $category_list = $this->oDocumentModel->getCategoryList($module_srl);
            if(count($category_list)) {
                foreach($category_list as $key => $val) $category_titles[$val->title] = $val->category_srl;
            } else {
                $category_list = $category_titles = array();
            }
        }

        /**
         * @brief 게시글 추가 변수 설정
         **/
        function importExtraVars($fp, &$obj) {
            $index = 1;
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                if($str == '</extra_vars>') break;

                $buff .= $str;
                $pos = strpos($buff, '>');
                $key = substr($buff, 1, $pos-1);
                if(substr($buff, -1 * ( strlen($key)+3)) == '</'.$key.'>') {
                    $val = base64_decode(substr($buff, $pos, strlen($buff)-$pos*2-2));
                    $obj->{"extra_vars".$index} = $val;
                    $buff = null;
                }
            }
        }

        /**
         * @brief 게시글 입력
         **/
        function importDocument($obj) {
            // 본문, 댓글, 엮인글, 첨부파일별로 변수 정리
            $comments = $obj->comments;
            $trackbacks = $obj->trackbacks;
            $attaches = $obj->attaches;
            unset($obj->comments);
            unset($obj->trackbacks);
            unset($obj->attaches);

            // 첨부파일 미리 등록
            if(count($attaches)) {
                foreach($attaches as $key => $val) {
                    $filename = $val->filename;
                    $download_count = (int)$val->download_count;
                    $file = $val->file;

                    if(!file_exists($file)) continue;

                    $file_info['tmp_name'] = $file;
                    $file_info['name'] = $filename;

                    $oFileController = &getController('file');
                    $oFileController->insertFile($file_info, $obj->module_srl, $obj->document_srl, $download_count, true);

                    // 컨텐츠의 내용 수정 (이미지 첨부파일 관련)
                    if(eregi("\.(jpg|gif|jpeg|png)$", $filename)) $obj->content = str_replace($filename, sprintf('./files/attach/images/%s/%s/%s', $obj->module_srl, $obj->document_srl, $filename), $obj->content);
                    @unlink($file);
                }
            }

            // 게시글 등록
            $obj->member_srl = 0;
            $obj->password_is_hashed = true;
            $output = $this->oDocumentController->insertDocument($obj, true);

            // 등록 실패시 첨부파일을 일단 모두 지움
            if(!$output->toBool()) {
                return false;
            }

            // 댓글 부모/자식 관계 정리하기 위한 변수
            $comment_parent_srls = array();

            // 댓글 등록
            if(count($comments)) {
                foreach($comments as $key => $val) {
                    // 댓글 내용 정리
                    $comment_args->comment_srl = getNextSequence();
                    $comment_parent_srls[$val->sequence] = $comment_args->comment_srl;
                    $comment_args->parent_srl = $comment_parent_srls[$val->parent];
                    $comment_args->module_srl = $obj->module_srl;
                    $comment_args->document_srl = $obj->document_srl;
                    $comment_args->content = $val->content;
                    $comment_args->password = $val->password;
                    $comment_args->nick_name = $val->nick_name;
                    $comment_args->user_id = $val->user_id;
                    $comment_args->user_name = $val->user_name;
                    $comment_args->member_srl = 0;
                    $comment_args->email_address = $val->email;
                    $comment_args->homepage = $val->homepage;
                    $comment_args->regdate = $val->regdate;
                    $comment_args->ipaddress = $val->ipaddress;

                    // 첨부파일 미리 등록
                    if(count($val->attaches)) {
                        foreach($val->attaches as $key => $val) {
                            $filename = $val->filename;
                            $download_count = (int)$val->download_count;
                            $file = $val->file;

                            if(!file_exists($file)) continue;

                            $file_info['tmp_name'] = $file;
                            $file_info['name'] = $filename;

                            $oFileController = &getController('file');
                            $oFileController->insertFile($file_info, $obj->module_srl, $comment_args->comment_srl, $download_count, true);

                            // 컨텐츠의 내용 수정 (이미지 첨부파일 관련)
                            if(eregi("\.(jpg|gif|jpeg|png)$", $filename)) $obj->content = str_replace($filename, sprintf('./files/attach/images/%s/%s/%s', $obj->module_srl, $obj->document_srl, $filename), $obj->content);
                            @unlink($file);
                        }
                    }


                    $this->oCommentController->insertComment($comment_args, true);
                }
            }

            // 엮인글 등록
            if(count($trackbacks)) {
                foreach($trackbacks as $key => $val) {
                    $val->module_srl = $obj->module_srl;
                    $val->document_srl = $obj->document_srl;
                    $this->oTrackbackController->insertTrackback($val, true);
                }
            }

            return true;
        }


        /**
         * @brief 파일포인터 return (local or http wrapper)
         **/
        function getFilePoint($filename) {
            $fp = null;
            if(eregi('^http:', $filename)) {
                $url_info = parse_url($filename);
                if(!$url_info['port']) $url_info['port'] = 80;
                if(!$url_info['path']) $url_info['path'] = '/';

                $fp = @fsockopen($url_info['host'], $url_info['port']);
                if($fp) {

                    // 한글 파일이 있으면 한글파일 부분만 urlencode하여 처리 (iconv 필수)
                    $path = $url_info['path'];
                    if(preg_match('/[\xEA-\xED][\x80-\xFF]{2}/', $path)&&function_exists('iconv')) {
                        $path_list = explode('/',$path);
                        $cnt = count($path_list);
                        $filename = $path_list[$cnt-1];
                        $filename = urlencode(iconv("UTF-8","EUC-KR",$filename));
                        $path_list[$cnt-1] = $filename;
                        $path = implode('/',$path_list);
                        $url_info['path'] = $path;
                    }

                    $header = sprintf("GET %s?%s HTTP/1.0\r\nHost: %s\r\nReferer: %s://%s\r\nConnection: Close\r\n\r\n", $url_info['path'], $url_info['query'], $url_info['host'], $url_info['scheme'], $url_info['host']);
                    @fwrite($fp, $header);
                    while(!feof($fp)) {
                        $str = fgets($fp, 1024);
                        if(!trim($str)) break;
                    }
                }
            } else {
                $fp = fopen($filename,"r");
            }

            return $fp;
        }

        /**
         * @biref 임의로 사용할 파일이름을 return
         **/
        function getTmpFilename($key) {
            $path = "./files/cache/tmp";
            if(!is_dir($path)) FileHandler::makeDir($path);
            $filename = sprintf("%s/%s.%d", $path, $key, rand(11111111,99999999));
            if(file_exists($filename)) $filename .= rand(111,999);
            return $filename;
        }

        /**
         * @brief 특정 파일포인트로부터 key에 해당하는 값이 나타날때까지 buff를 읽음
         **/
        function readFileBuff($fp,$key) {
            $temp_filename = $this->getTmpFilename($key);
            $f = fopen($temp_filename, "w");

            $buff = '';
            while(!feof($fp)) {
                $str = trim(fgets($fp, 1024));
                if($str == sprintf('</%s>', $key)) break;

                $buff .= $str;

                if(substr($buff, -7) == '</buff>') {
                    fwrite($f, base64_decode(substr($buff, 6, -7)));
                    $buff = null;
                }
            }
            fclose($f);
            return $temp_filename;
        }
    }
?>
