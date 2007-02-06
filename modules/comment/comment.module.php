<?php
  /**
   * @file   : modules/comment/comment.module.php
   * @author : zero <zero@nzeo.com>
   * @desc   : 기본 모듈중의 하나인 comment module
   *           Module class에서 상속을 받아서 사용
   *           action 의 경우 disp/proc 2가지만 존재하며 이는 action명세서에 
   *           미리 기록을 하여야 함
   **/

  class comment extends Module {

    /**
     * 모듈의 정보
     **/
    var $cur_version = "20070130_0.01";

    /**
     * 기본 action 지정
     * $act값이 없거나 잘못된 값이 들어올 경우 $default_act 값으로 진행
     **/
    var $default_act = '';

    /**
     * 현재 모듈의 초기화를 위한 작업을 지정해 놓은 method
     * css/js파일의 load라든지 lang파일 load등을 미리 선언
     *
     * Init() => 공통 
     * dispInit() => disp시에
     * procInit() => proc시에
     *
     * $this->module_path는 현재 이 모듈파일의 위치를 나타낸다
     * (ex: $this->module_path = "./modules/install/";
     **/

    // 초기화
    function init() {/*{{{*/
      //Context::loadLang($this->module_path.'lang');
    }/*}}}*/

    // disp 초기화
    function dispInit() {/*{{{*/
    }/*}}}*/
    
    // proc 초기화
    function procInit() {/*{{{*/
    }/*}}}*/

    /**
     * 여기서부터는 action의 구현
     * request parameter의 경우 각 method의 첫번째 인자로 넘어온다
     *
     * dispXXXX : 출력을 위한 method, output에 tpl file이 지정되어야 한다
     * procXXXX : 처리를 위한 method, output에는 comment, comment가 지정되어야 한다
     **/

    /**
     * 여기부터는 이 모듈과 관련된 라이브러리 개념의 method들
     **/

    // 코멘트의 권한 부여 
    // 세션값으로 현 접속상태에서만 사용 가능
    // public void addGrant($comment_srl) {/*{{{*/
    function addGrant($comment_srl) {
      $_SESSION['own_comment'][$comment_srl] = true;
    }/*}}}*/

    // public void isGranted($comment_srl) {/*{{{*/
    function isGranted($comment_srl) {
      return $_SESSION['own_comment'][$comment_srl];
    }/*}}}*/

    // 코멘트 
    // public boolean insertComment($obj)/*{{{*/
    // 댓글 입력
    function insertComment($obj) {
      // document_srl에 해당하는 글이 있는지 확인
      $document_srl = $obj->document_srl;
      if(!$document_srl) return new Output(-1,'msg_invalid_document');
      $oDocument = getModule('document');
      $document = $oDocument->getDocument($document_srl);
      if(!$document_srl) return new Output(-1,'msg_invalid_document');
      if($document->lock_comment=='Y') return new Output(-1,'msg_invalid_request');

      // 댓글를 입력
      $oDB = &DB::getInstance();
      $obj->comment_srl = $oDB->getNextSequence();
      $obj->list_order = $obj->comment_srl * -1;
      if($obj->password) $obj->password = md5($obj->password);
      $output = $oDB->executeQuery('comment.insertComment', $obj);

      // 입력에 이상이 없으면 해당 글의 댓글 수를 올림
      if(!$output->toBool()) return $output;

      // 해당 글의 전체 댓글 수를 구해옴
      $comment_count = $this->getCommentCount($document_srl);

      // 해당글의 댓글 수를 업데이트
      $output = $oDocument->updateCommentCount($document_srl, $comment_count);

      // 댓글의 권한을 부여
      $this->addGrant($obj->comment_srl);
      $output->add('comment_srl', $obj->comment_srl);
      return $output;
    }/*}}}*/

    // public boolean updateComment($obj)/*{{{*/
    // 댓글 수정
    function updateComment($obj) {
      // 권한이 있는지 확인
      if(!$this->isGranted($obj->comment_srl)) return new Output(-1, 'msg_not_permitted');

      // 업데이트
      $oDB = &DB::getInstance();
      if($obj->password) $obj->password = md5($obj->password);
      $output = $oDB->executeQuery('comment.updateComment', $obj);
      $output->add('comment_srl', $obj->comment_srl);
      return $output;
    }/*}}}*/

    // public boolean deleteComment($comment_srl)/*{{{*/
    // 댓글 삭제
    function deleteComment($comment_srl) {
      // 기존 댓글이 있는지 확인
      $comment = $this->getComment($comment_srl);
      if($comment->comment_srl != $comment_srl) return new Output(-1, 'msg_invalid_request');
      $document_srl = $comment->document_srl;

      // 해당 댓글에 child가 있는지 확인
      $child_count = $this->getChildCommentCount($comment_srl);
      if($child_count>0) return new Output(-1, 'fail_to_delete_have_children');

      // 권한이 있는지 확인
      if(!$this->isGranted($comment_srl)) return new Output(-1, 'msg_not_permitted');

      // 삭제
      $oDB = &DB::getInstance();
      $args->comment_srl = $comment_srl;
      $output = $oDB->executeQuery('comment.deleteComment', $args);
      if(!$output->toBool()) return new Output(-1, 'msg_error_occured');

      // 댓글 수를 구해서 업데이트
      $comment_count = $this->getCommentCount($document_srl);

      // 해당글의 댓글 수를 업데이트
      $oDocument = getModule('document');
      $output = $oDocument->updateCommentCount($document_srl, $comment_count);
      $output->add('document_srl', $document_srl);
      return $output;
    }/*}}}*/

    // public boolean deleteComments($document_srl)/*{{{*/
    // 특정 글의 모든 댓글 삭제
    function deleteComments($document_srl) {
      // 권한이 있는지 확인
      if(!$this->isGranted($document_srl)) return new Output(-1, 'msg_not_permitted');

      // 삭제
      $oDB = &DB::getInstance();
      $args->document_srl = $document_srl;
      $output = $oDB->executeQuery('comment.deleteComments', $args);
      return $output;
    }/*}}}*/

    // public boolean deleteMoudleComments($module_srl)/*{{{*/
    // 특정 모듈의 모든 댓글 삭제
    function deleteModuleComments($module_srl) {
      // 삭제
      $oDB = &DB::getInstance();
      $args->module_srl = $module_srl;
      $output = $oDB->executeQuery('comment.deleteModuleComments', $args);
      return $output;
    }/*}}}*/

    // public int getChildCommentCount($comment_srl)/*{{{*/
    // 자식 답글의 갯수 리턴
    function getChildCommentCount($comment_srl) {
      $oDB = &DB::getInstance();
      $args->comment_srl = $comment_srl;
      $output = $oDB->executeQuery('comment.getChildCommentCount', $args);
      return (int)$output->data->count;
    }/*}}}*/

    // public boolean getComment($comment_srl)/*{{{*/
    // 댓글 가져오기
    function getComment($comment_srl) {
      $oDB = &DB::getInstance();
      $args->comment_srl = $comment_srl;
      $output = $oDB->executeQuery('comment.getComment', $args);
      return $output->data;
    }/*}}}*/

    // public boolean getComments($comment_srl_list)/*{{{*/
    // 여러개의 댓글들을 가져옴 (페이징 아님)
    function getComments($comment_srl_list) {
      if(is_array($comment_srl_list)) $comment_srls = implode(',',$comment_srl_list);

      $oDB = &DB::getInstance();
      $args->comment_srls = $comment_srls;
      $output = $oDB->executeQuery('comment.getComments', $args);
      return $output->data;
    }/*}}}*/

    // public number getCommentCount($module_srl, $search_obj = NULL)/*{{{*/
    // document_srl 에 해당하는 댓글의 전체 갯수를 가져옴
    function getCommentCount($document_srl) {
      $oDB = &DB::getInstance();
      $args->document_srl = $document_srl;
      $output = $oDB->executeQuery('comment.getCommentCount', $args);
      $total_count = $output->data->count;
      return (int)$total_count;
    }/*}}}*/

    // public boolean getCommentList($document_srl)/*{{{*/
    // module_srl값을 가지는 댓글의 목록을 가져옴
    function getCommentList($document_srl) {
      // 댓글 목록을 가져옴
      $oDB = &DB::getInstance();
      $args->document_srl = $document_srl;
      $args->list_order = 'list_order';
      $output = $oDB->executeQuery('comment.getCommentList', $args);
      if(!$output->toBool()) return $output;
      $source_list= $output->data;
      if(!is_array($source_list)) $source_list = array($source_list);

      // 댓글를 계층형 구조로 정렬
      $comment_count = count($source_list);

      $root = NULL;
      $list = NULL;
      for($i=$comment_count-1;$i>=0;$i--) {
        $comment_srl = $source_list[$i]->comment_srl;
        $parent_srl = $source_list[$i]->parent_srl;
        if(!$comment_srl) continue;

        $list[$comment_srl] = $source_list[$i];

        if($parent_srl) {
          $list[$parent_srl]->child[] = &$list[$comment_srl];
        } else {
          $root->child[] = &$list[$comment_srl];
        }

      }
      $this->_arrangeComment($comment_list, $root->child, 0);
      return $comment_list;
    }/*}}}*/

    // private object _arrangeComment(&$comment_list, $list, $depth)/*{{{*/
    // 댓글를 계층형으로 재배치
    function _arrangeComment(&$comment_list, $list, $depth) {
      if(!count($list)) return;
      foreach($list as $key => $val) {
        if($val->child) {
          $tmp = $val;
          $tmp->depth = $depth;
          $comment_list[$tmp->comment_srl] = $tmp;
          $this->_arrangeComment($comment_list,$val->child,$depth+1);
        }
        else {
          $val->depth = $depth;
          $comment_list[$val->comment_srl] = $val;
        }
      }
    }/*}}}*/
  }
?>
