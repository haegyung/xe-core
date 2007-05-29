<?php
    /**
     * @class  boardController
     * @author zero (zero@nzeo.com)
     * @brief  board 모듈의 Controller class
     **/

    class boardController extends board {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 문서 입력
         **/
        function procBoardInsertDocument() {
            // 글작성시 필요한 변수를 세팅
            $obj = Context::getRequestVars();
            $obj->module_srl = $this->module_srl;
            if($obj->is_notice!='Y'||!$this->grant->manager) $obj->is_notice = 'N';

            // document module의 model 객체 생성
            $oDocumentModel = &getModel('document');

            // document module의 controller 객체 생성
            $oDocumentController = &getController('document');

            // 이미 존재하는 글인지 체크
            $document = $oDocumentModel->getDocument($obj->document_srl, $this->grant->manager);

            // 이미 존재하는 경우 수정
            if($document->document_srl == $obj->document_srl) {
                $output = $oDocumentController->updateDocument($document, $obj);
                $msg_code = 'success_updated';

            // 그렇지 않으면 신규 등록
            } else {
                $output = $oDocumentController->insertDocument($obj);
                $msg_code = 'success_registed';
                $obj->document_srl = $output->get('document_srl');
            }

            // 오류 발생시 멈춤
            if(!$output->toBool()) return $output;

            // 트랙백이 있으면 트랙백 발송
            $trackback_url = Context::get('trackback_url');
            $trackback_charset = Context::get('trackback_charset');
            if($trackback_url) {
                $oTrackbackController = &getController('trackback');
                $oTrackbackController->sendTrackback($obj, $trackback_url, $trackback_charset);
            }

            // 결과를 리턴
            $this->add('mid', Context::get('mid'));
            $this->add('document_srl', $output->get('document_srl'));

            // 성공 메세지 등록
            $this->setMessage($msg_code);
        }

        /**
         * @brief 문서 삭제
         **/
        function procBoardDeleteDocument() {
            // 문서 번호 확인
            $document_srl = Context::get('document_srl');

            // 문서 번호가 없다면 오류 발생
            if(!$document_srl) return $this->doError('msg_invalid_document');

            // document module model 객체 생성
            $oDocumentController = &getController('document');

            // 삭제 시도
            $output = $oDocumentController->deleteDocument($document_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            // 성공 메세지 등록
            $this->add('mid', Context::get('mid'));
            $this->add('page', $output->get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 추천
         **/
        function procBoardVoteDocument() {
            // document module controller 객체 생성
            $oDocumentController = &getController('document');

            $document_srl = Context::get('document_srl');
            return $oDocumentController->updateVotedCount($document_srl);
        }

        /**
         * @brief 코멘트 추가
         **/
        function procBoardInsertComment() {
            // 댓글 입력에 필요한 데이터 추출
            $obj = Context::gets('document_srl','comment_srl','parent_srl','content','password','nick_name','nick_name','member_srl','email_address','homepage');
            $obj->module_srl = $this->module_srl;

            // comment 모듈의 model 객체 생성
            $oCommentModel = &getModel('comment');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

            // comment_srl이 존재하는지 체크
            $comment = $oCommentModel->getComment($obj->comment_srl, $this->grant->manager);

            // comment_srl이 없을 경우 신규 입력
            if($comment->comment_srl != $obj->comment_srl) {

                // parent_srl이 있으면 답변으로
                if($obj->parent_srl) {
                    $parent_comment = $oCommentModel->getComment($obj->parent_srl);
                    if(!$parent_comment->comment_srl) return new Object(-1, 'msg_invalid_request');

                    $output = $oCommentController->insertComment($obj);

                // 없으면 신규
                } else {
                    $output = $oCommentController->insertComment($obj);
                }

            // comment_srl이 있으면 수정으로
            } else {
                $obj->parent_srl = $comment->parent_srl;
                $output = $oCommentController->updateComment($obj);
                $comment_srl = $obj->comment_srl;
            }

            if(!$output->toBool()) return $output;

            $this->setMessage('success_registed');
            $this->add('mid', Context::get('mid'));
            $this->add('document_srl', $obj->document_srl);
            $this->add('comment_srl', $comment_srl);
        }

        /**
         * @brief 코멘트 삭제
         **/
        function procBoardDeleteComment() {
            // 댓글 번호 확인
            $comment_srl = Context::get('comment_srl');
            if(!$comment_srl) return $this->doError('msg_invalid_request');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

            $output = $oCommentController->deleteComment($comment_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 엮인글 삭제
         **/
        function procBoardDeleteTrackback() {
            $trackback_srl = Context::get('trackback_srl');

            // trackback module의 controller 객체 생성
            $oTrackbackController = &getController('trackback');
            $output = $oTrackbackController->deleteTrackback($trackback_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 문서와 댓글의 비밀번호를 확인
         **/
        function procBoardVerificationPassword() {
            // 비밀번호와 문서 번호를 받음
            $password = md5(Context::get('password'));
            $document_srl = Context::get('document_srl');
            $comment_srl = Context::get('comment_srl');

            // comment_srl이 있을 경우 댓글이 대상
            if($comment_srl) {
                // 문서번호에 해당하는 글이 있는지 확인
                $oCommentModel = &getModel('comment');
                $data = $oCommentModel->getComment($comment_srl);
                // comment_srl이 없으면 문서가 대상
            } else {
                // 문서번호에 해당하는 글이 있는지 확인
                $oDocumentModel = &getModel('document');
                $data = $oDocumentModel->getDocument($document_srl);
            }

            // 글이 없을 경우 에러
            if(!$data) return new Object(-1, 'msg_invalid_request');

            // 문서의 비밀번호와 입력한 비밀번호의 비교
            if($data->password != $password) return new Object(-1, 'msg_invalid_password');

            // 해당 글에 대한 권한 부여
            if($comment_srl) {
                $oCommentController = &getController('comment');
                $oCommentController->addGrant($comment_srl);
            } else {
                $oDocumentController = &getController('document');
                $oDocumentController->addGrant($document_srl);
            }
        }

        /**
         * @brief 관리자가 글 선택시 세션에 담음
         **/
        function procBoardAdminAddCart() {
            $document_srl = Context::get('srl');
            $check_flag = Context::get('check_flag');
            if(!$document_srl || !in_array($check_flag, array('add','remove'))) return;

            $flag_list = $_SESSION['document_management'][$this->module_srl];

            if($check_flag == 'remove') unset($flag_list[$document_srl]);
            else $flag_list[$document_srl] = true;

            $_SESSION['document_management'][$this->module_srl] = $flag_list;
        }

        /**
         * @brief 세션에 담긴 선택글의 이동/ 삭제
         **/
        function procBoardAdminManageCheckedDocument() {
            $type = Context::get('type');
            $module_srl = Context::get('target_board');
            $flag_list = $_SESSION['document_management'][$this->module_srl];

            $document_srl_list = array_keys($flag_list);

            $oDocumentController = &getController('document');
            $document_srl_count = count($document_srl_list);

            if($type == 'move') {
                if(!$module_srl) return new Object(-1, 'fail_to_move');
                else {
                    $output = $oDocumentController->moveDocumentModule($document_srl_list, $module_srl, $this->module_srl);
                    if(!$output->toBool()) return new Object(-1, 'fail_to_move');
                    $msg_code = 'success_moved';
                    $_SESSION['document_management'][$this->module_srl] = null;
                }

            } elseif($type =='delete') {
                $oDB = &DB::getInstance();
                $oDB->begin();
                for($i=0;$i<$document_srl_count;$i++) {
                    $document_srl = $document_srl_list[$i];
                    $output = $oDocumentController->deleteDocument($document_srl, true);
                    if(!$output->toBool()) return new Object(-1, 'fail_to_delete');
                }
                $oDB->commit();
                $msg_code = 'success_deleted';
                $_SESSION['document_management'][$this->module_srl] = null;
            }

            $this->setMessage($msg_code);
        }
 
        /**
         * @brief 권한 추가
         **/
        function procBoardAdminInsertGrant() {
            $module_srl = Context::get('module_srl');

            // 현 모듈의 권한 목록을 가져옴
            $grant_list = $this->xml_info->grant;

            if(count($grant_list)) {
                foreach($grant_list as $key => $val) {
                    $group_srls = Context::get($key);
                    if($group_srls) $arr_grant[$key] = explode('|@|',$group_srls);
                }
                $grants = serialize($arr_grant);
            }

            $oModuleController = &getController('module');
            $oModuleController->updateModuleGrant($module_srl, $grants);

            $this->add('module_srl',Context::get('module_srl'));
            $this->setMessage('success_registed');
        }

        /**
         * @brief 스킨 정보 업데이트
         **/
        function procBoardAdminUpdateSkinInfo() {
            // module_srl에 해당하는 정보들을 가져오기
            $module_srl = Context::get('module_srl');
            $oModuleModel = &getModel('module');
            $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
            $skin = $module_info->skin;

            // 스킨의 정보르 구해옴 (extra_vars를 체크하기 위해서)
            $skin_info = $oModuleModel->loadSkinInfo($this->module_path, $skin);

            // 입력받은 변수들을 체크 (mo, act, module_srl, page등 기본적인 변수들 없앰)
            $obj = Context::getRequestVars();
            unset($obj->act);
            unset($obj->module_srl);
            unset($obj->page);

            // 원 skin_info에서 extra_vars의 type이 image일 경우 별도 처리를 해줌
            if($skin_info->extra_vars) {
                foreach($skin_info->extra_vars as $vars) {
                    if($vars->type!='image') continue;

                    $image_obj = $obj->{$vars->name};

                    // 삭제 요청에 대한 변수를 구함
                    $del_var = $obj->{"del_".$vars->name};
                    unset($obj->{"del_".$vars->name});
                    if($del_var == 'Y') {
                        @unlink($module_info->{$vars->name});
                        continue;
                    }

                    // 업로드 되지 않았다면 이전 데이터를 그대로 사용
                    if(!$image_obj['tmp_name']) {
                        $obj->{$vars->name} = $module_info->{$vars->name};
                        continue;
                    }

                    // 정상적으로 업로드된 파일이 아니면 무시
                    if(!is_uploaded_file($image_obj['tmp_name'])) {
                        unset($obj->{$vars->name});
                        continue;
                    }

                    // 이미지 파일이 아니어도 무시
                    if(!eregi("\.(jpg|jpeg|gif|png)$", $image_obj['name'])) {
                        unset($obj->{$vars->name});
                        continue;
                    }

                    // 경로를 정해서 업로드
                    $path = sprintf("./files/attach/images/%s/", $module_srl);

                    // 디렉토리 생성
                    if(!FileHandler::makeDir($path)) return false;

                    $filename = $path.$image_obj['name'];

                    // 파일 이동
                    if(!move_uploaded_file($image_obj['tmp_name'], $filename)) {
                        unset($obj->{$vars->name});
                        continue;
                    }

                    // 변수를 바꿈
                    unset($obj->{$vars->name});
                    $obj->{$vars->name} = $filename;
                }
            }

            // serialize하여 저장
            $skin_vars = serialize($obj);

            $oModuleController = &getController('module');
            $oModuleController->updateModuleSkinVars($module_srl, $skin_vars);

            $this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('default_layout.html');
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile("top_refresh.html");
        }

        /**
         * @brief 게시판 추가
         **/
        function procBoardAdminInsertBoard($args = null) {
            // 일단 입력된 값들을 모두 받아서 db 입력항목과 그외 것으로 분리
            if(!$args) {
                $args = Context::gets('module_srl','module_category_srl','board_name','layout_srl','skin','browser_title','description','is_default','header_text','footer_text','admin_id','open_rss');
            }

            $args->module = 'board';
            $args->mid = $args->board_name;
            unset($args->board_name);
            if($args->is_default!='Y') $args->is_default = 'N';

            // 기본 값외의 것들을 정리
            $extra_var = delObjectVars(Context::getRequestVars(), $args);
            if($extra_var->use_category!='Y') $extra_var->use_category = 'N';
            unset($extra_var->mo);
            unset($extra_var->act);
            unset($extra_var->page);
            unset($extra_var->board_name);

            // module_srl이 넘어오면 원 모듈이 있는지 확인
            if($args->module_srl) {
                $oModuleModel = &getModel('module');
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);

                // 만약 원래 모듈이 없으면 새로 입력하기 위한 처리
                if($module_info->module_srl != $args->module_srl) unset($args->module_srl);
            }

            // $extra_var를 serialize
            $args->extra_vars = serialize($extra_var);

            // module 모듈의 controller 객체 생성
            $oModuleController = &getController('module');

            // is_default=='Y' 이면
            if($args->is_default=='Y') $oModuleController->clearDefaultModule();

            // module_srl의 값에 따라 insert/update
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
                $msg_code = 'success_registed';

                // 파일업로드, 댓글 파일업로드, 관리에 대한 권한 지정
                if($output->toBool()) {
                    $oMemberModel = &getModel('member');
                    $admin_group = $oMemberModel->getAdminGroup();
                    $admin_group_srl = $admin_group->group_srl;

                    $module_srl = $output->get('module_srl');
                    $grants = serialize(array('fileupload'=>array($admin_group_srl), 'comment_fileupload'=>array($admin_group_srl), 'manager'=>array($admin_group_srl)));

                    $oModuleController->updateModuleGrant($module_srl, $grants);
                }
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            $this->add('page',Context::get('page'));
            $this->add('module_srl',$output->get('module_srl'));
            $this->setMessage($msg_code);
        }

        /**
         * @brief 게시판 삭제
         **/
        function procBoardAdminDeleteBoard() {
            $module_srl = Context::get('module_srl');

            // 원본을 구해온다
            $oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            $this->add('module','board');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 카테고리 추가
         **/
        function procBoardAdminInsertCategory() {
            // 일단 입력된 값들을 모두 받아서 db 입력항목과 그외 것으로 분리
            $module_srl = Context::get('module_srl');
            $category_title = Context::get('category_title');

            // module_srl이 있으면 원본을 구해온다
            $oDocumentController = &getController('document');
            $output = $oDocumentController->insertCategory($module_srl, $category_title);
            if(!$output->toBool()) return $output;

            $this->add('page',Context::get('page'));
            $this->add('module_srl',$module_srl);
            $this->setMessage('success_registed');
        }

        /**
         * @brief 카테고리의 내용 수정
         **/
        function procBoardAdminUpdateCategory() {
            $module_srl = Context::get('module_srl');
            $category_srl = Context::get('category_srl');
            $mode = Context::get('mode');

            $oDocumentModel = &getModel('document');
            $oDocumentController = &getController('document');

            switch($mode) {
                case 'up' :
                        $output = $oDocumentController->moveCategoryUp($category_srl);
                        $msg_code = 'success_moved';
                    break;
                case 'down' :
                        $output = $oDocumentController->moveCategoryDown($category_srl);
                        $msg_code = 'success_moved';
                    break;
                case 'delete' :
                        $output = $oDocumentController->deleteCategory($category_srl);
                        $msg_code = 'success_deleted';
                    break;
                case 'update' :
                        $selected_category = $oDocumentModel->getCategory($category_srl);
                        $args->category_srl = $selected_category->category_srl;
                        $args->title = Context::get('category_title');
                        $args->list_order = $selected_category->list_order;
                        $output = $oDocumentController->updateCategory($args);
                        $msg_code = 'success_updated';
                    break;
            }
            if(!$output->toBool()) return $output;

            $this->add('module_srl', $module_srl);
            $this->setMessage($msg_code);
        }
    }
?>
