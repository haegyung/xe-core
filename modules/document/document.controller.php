<?php
    /**
     * @class  documentController
     * @author zero (zero@nzeo.com)
     * @brief  document 모듈의 controller 클래스
     **/

    class documentController extends Module {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 문서의 권한 부여 
         * 세션값으로 현 접속상태에서만 사용 가능
         **/
        function addGrant($document_srl) {
            $_SESSION['own_document'][$document_srl] = true;
        }

        /**
         * @brief 문서 입력
         **/
        function insertDocument($obj) {
            $oDB = &DB::getInstance();

            // 카테고리가 있나 검사하여 없는 카테고리면 0으로 세팅
            if($obj->category_srl) {
                $category_list = $this->getCategoryList($obj->module_srl);
                if(!$category_list[$obj->category_srl]) $obj->category_srl = 0;
            }

            // 태그 처리
            $oTagController = getController('tag');
            $obj->tags = $oTagController->insertTag($obj->module_srl, $obj->document_srl, $obj->tags);

            // 글 입력
            $obj->readed_count = 0;
            $obj->update_order = $obj->list_order = $obj->document_srl * -1;
            if($obj->password) $obj->password = md5($obj->password);

            // 공지사항일 경우 list_order에 무지막지한 값;;을 입력
            if($obj->is_notice=='Y') $obj->list_order = $this->notice_list_order;

            // DB에 입력
            $output = $oDB->executeQuery('document.insertDocument', $obj);

            if(!$output->toBool()) return $output;

            // 성공하였을 경우 category_srl이 있으면 카테고리 update
            if($obj->category_srl) $this->updateCategoryCount($obj->category_srl);

            // return
            $this->addGrant($obj->document_srl);
            $output->add('document_srl',$obj->document_srl);
            $output->add('category_srl',$obj->category_srl);
            return $output;
        }

        /**
         * @brief 문서 수정
         **/
        function updateDocument($source_obj, $obj) {
            // 카테고리가 변경되었으면 검사후 없는 카테고리면 0으로 세팅
            if($source_obj->category_srl!=$obj->category_srl) {
                $category_list = $this->getCategoryList($obj->module_srl);
                if(!$category_list[$obj->category_srl]) $obj->category_srl = 0;
            }

            // 태그 처리
            $oTagController = getController('tag');
            $obj->tags = $oTagController->insertTag($obj->module_srl, $obj->document_srl, $obj->tags);

            // 수정
            $oDB = &DB::getInstance();
            $obj->update_order = $oDB->getNextSequence() * -1;

            // 공지사항일 경우 list_order에 무지막지한 값을, 그렇지 않으면 document_srl*-1값을
            if($obj->is_notice=='Y') $obj->list_order = $this->notice_list_order;
            else $obj->list_order = $obj->document_srl*-1;

            if($obj->password) $obj->password = md5($obj->password);

            // DB에 입력
            $output = $oDB->executeQuery('document.updateDocument', $obj);

            if(!$output->toBool()) return $output;

            // 성공하였을 경우 category_srl이 있으면 카테고리 update
            if($source_obj->category_srl!=$obj->category_srl) {
                if($source_obj->category_srl) $this->updateCategoryCount($source_obj->category_srl);
                if($obj->category_srl) $this->updateCategoryCount($obj->category_srl);
            }

            $output->add('document_srl',$obj->document_srl);
            return $output;
        }

        /**
         * @brief 문서 삭제
         **/
        function deleteDocument($obj) {
            // 변수 세팅
            $document_srl = $obj->document_srl;
            $category_srl = $obj->category_srl;

            // document의 model 객체 생성
            $oDocumentModel = getModel('document');

            // 기존 문서가 있는지 확인
            $document = $oDocumentModel->getDocument($document_srl);
            if($document->document_srl != $document_srl) return false;

            // 권한이 있는지 확인
            if(!$document->is_granted) return new Object(-1, 'msg_not_permitted');

            $oDB = &DB::getInstance();

            // 글 삭제
            $args->document_srl = $document_srl;
            $output = $oDB->executeQuery('document.deleteDocument', $args);
            if(!$output->toBool()) return $output;

            // 댓글 삭제
            $oCommentController = getController('comment');
            $output = $oCommentController->deleteComments($document_srl);

            // 엮인글 삭제
            $oTrackbackController = getController('trackback');
            $output = $oTrackbackController->deleteTrackbacks($document_srl);

            // 태그 삭제
            $oTagController = getController('tag');
            $oTagController->deleteTag($document_srl);

            // 첨부 파일 삭제
            $oFileController = getController('file');
            if($document->uploaded_count) $oFileController->deleteFiles($document->module_srl, $document_srl);

            // 카테고리가 있으면 카테고리 정보 변경
            if($document->category_srl) $this->updateCategoryCount($document->category_srl);

            return $output;
        }

        /**
         * @brief 특정 모듈의 전체 문서 삭제
         **/
        function deleteModuleDocument($module_srl) {
            $oDB = &DB::getInstance();

            $args->module_srl = $module_srl;
            $output = $oDB->executeQuery('document.deleteModuleDocument', $args);
            return $output;
        }

        /**
         * @brief 해당 document의 조회수 증가
         **/
        function updateReadedCount($document_srl) {
            if($_SESSION['readed_document'][$document_srl]) return false;

            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $output = $oDB->executeQuery('document.updateReadedCount', $args);
            return $_SESSION['readed_document'][$document_srl] = true;
        }

        /**
         * @brief 해당 document의 추천수 증가
         **/
        function updateVotedCount($document_srl) {
            if($_SESSION['voted_document'][$document_srl]) return new Object(-1, 'failed_voted');

            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $output = $oDB->executeQuery('document.updateVotedCount', $args);

            $_SESSION['voted_document'][$document_srl] = true;

            return $output;
        }

        /**
         * @brief 해당 document의 댓글 수 증가
         **/
        function updateCommentCount($document_srl, $comment_count) {
            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $args->comment_count = $comment_count;

            return $oDB->executeQuery('document.updateCommentCount', $args);
        }

        /**
         * @brief 해당 document의 엮인글 수증가
         **/
        function updateTrackbackCount($document_srl, $trackback_count) {
            $oDB = &DB::getInstance();

            $args->document_srl = $document_srl;
            $args->trackback_count = $trackback_count;

            return $oDB->executeQuery('document.updateTrackbackCount', $args);
        }

        /**
         * @brief 카테고리 추가
         **/
        function insertCategory($module_srl, $title) {
            $oDB = &DB::getInstance();

            $args->list_order = $args->category_srl = $oDB->getNextSequence();
            $args->module_srl = $module_srl;
            $args->title = $title;
            $args->document_count = 0;

            return $oDB->executeQuery('document.insertCategory', $args);
        }

        /**
         * @brief 카테고리 정보 수정
         **/
        function updateCategory($args) {
            $oDB = &DB::getInstance();
            return $oDB->executeQuery('document.updateCategory', $args);
        }

        /** 
         * @brief 카테고리에 문서의 숫자를 변경
         **/
        function updateCategoryCount($category_srl, $document_count = 0) {
            // document model 객체 생성
            $oDocumentModel = getModel('document');
            if(!$document_count) $document_count = $oDocumentModel->getCategoryDocumentCount($category_srl);

            $oDB = &DB::getInstance();

            $args->category_srl = $category_srl;
            $args->document_count = $document_count;
            return $oDB->executeQuery('document.updateCategoryCount', $args);
        }

        /**
         * @brief 카테고리 삭제
         **/
        function deleteCategory($category_srl) {
            $oDB = &DB::getInstance();

            $args->category_srl = $category_srl;

            // 카테고리 정보를 삭제
            $output = $oDB->executeQuery('document.deleteCategory', $args);
            if(!$output->toBool()) return $output;

            // 현 카테고리 값을 가지는 문서들의 category_srl을 0 으로 세팅
            unset($args);

            $args->target_category_srl = 0;
            $args->source_category_srl = $category_srl;
            $output = $oDB->executeQuery('document.updateDocumentCategory', $args);
            return $output;
        }

        /**
         * @brief 특정 모듈의 카테고리를 모두 삭제
         **/
        function deleteModuleCategory($module_srl) {
            $oDB = &DB::getInstance();

            $args->module_srl = $module_srl;
            $output = $oDB->executeQuery('document.deleteModuleCategory', $args);
            return $output;
        }

        /**
         * @brief 카테고리를 상단으로 이동
         **/
        function moveCategoryUp($category_srl) {
            $oDB = &DB::getInstance();
            $oDocumentModel = getModel('document');

            // 선택된 카테고리의 정보를 구한다
            $args->category_srl = $category_srl;
            $output = $oDB->executeQuery('document.getCategory', $args);

            $category = $output->data;
            $list_order = $category->list_order;
            $module_srl = $category->module_srl;

            // 전체 카테고리 목록을 구한다
            $category_list = $oDocumentModel->getCategoryList($module_srl);
            $category_srl_list = array_keys($category_list);
            if(count($category_srl_list)<2) return new Object();

            $prev_category = NULL;
            foreach($category_list as $key => $val) {
                if($key==$category_srl) break;
                $prev_category = $val;
            }

            // 이전 카테고리가 없으면 그냥 return
            if(!$prev_category) return new Object(-1,Context::getLang('msg_category_not_moved'));

            // 선택한 카테고리가 가장 위의 카테고리이면 그냥 return
            if($category_srl_list[0]==$category_srl) return new Object(-1,Context::getLang('msg_category_not_moved'));

            // 선택한 카테고리의 정보
            $cur_args->category_srl = $category_srl;
            $cur_args->list_order = $prev_category->list_order;
            $cur_args->title = $category->title;
            $this->updateCategory($cur_args);

            // 대상 카테고리의 정보
            $prev_args->category_srl = $prev_category->category_srl;
            $prev_args->list_order = $list_order;
            $prev_args->title = $prev_category->title;
            $this->updateCategory($prev_args);

            return new Object();
        }

        /** 
         * @brief 카테고리를 아래로 이동
         **/
        function moveCategoryDown($category_srl) {
            $oDB = &DB::getInstance();
            $oDocumentModel = getModel('document');

            // 선택된 카테고리의 정보를 구한다
            $args->category_srl = $category_srl;
            $output = $oDB->executeQuery('document.getCategory', $args);

            $category = $output->data;
            $list_order = $category->list_order;
            $module_srl = $category->module_srl;

            // 전체 카테고리 목록을 구한다
            $category_list = $oDocumentModel->getCategoryList($module_srl);
            $category_srl_list = array_keys($category_list);
            if(count($category_srl_list)<2) return new Object();

            for($i=0;$i<count($category_srl_list);$i++) {
                if($category_srl_list[$i]==$category_srl) break;
            }

            $next_category_srl = $category_srl_list[$i+1];
            if(!$category_list[$next_category_srl]) return new Object(-1,Context::getLang('msg_category_not_moved'));
            $next_category = $category_list[$next_category_srl];

            // 선택한 카테고리의 정보
            $cur_args->category_srl = $category_srl;
            $cur_args->list_order = $next_category->list_order;
            $cur_args->title = $category->title;
            $this->updateCategory($cur_args);

            // 대상 카테고리의 정보
            $next_args->category_srl = $next_category->category_srl;
            $next_args->list_order = $list_order;
            $next_args->title = $next_category->title;
            $this->updateCategory($next_args);

            return new Object();
        }

    }
?>
