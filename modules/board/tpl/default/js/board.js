/**
 * @file   : modules/board/js/board.js
 * @author : zero <zero@nzeo.com>
 * @desc   : board 모듈의 javascript
 **/

/* 글쓰기 작성후 */
function procInsert(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  var mid = ret_obj['mid'];
  var document_srl = ret_obj['document_srl'];
  var category_srl = ret_obj['category_srl'];
  alert(message);
  url =  "./?mid="+mid+"&document_srl="+document_srl;
  if(category_srl) url += '&category='+category_srl;
  location.href = url;
}

/* 글 삭제 */
function procDeleteDocument(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  var mid = ret_obj['mid'];
  var page = ret_obj['page'];
  var url = "./?mid="+mid;
  if(page) url += "&page="+page;
  alert(message);
  location.href = url;
}

/* 검색 실행 */
function procSearch(fo_obj, args) {
  fo_obj.submit();
}

/* 추천, 추천은 별도의 폼입력이 필요 없어 직접 필터 사용 */
function doVote() {
  var fo_obj = document.getElementById('fo_document_info');
  procFormFilter(fo_obj, vote, procVote)
}

function procVote(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  alert(message);

  location.href = location.href;
}

// 현재 페이지 reload
function procReload(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];

  location.href = location.href;
}

/* 댓글 글쓰기 작성후 */
function procInsertComment(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  var mid = ret_obj['mid'];
  var document_srl = ret_obj['document_srl'];
  var comment_srl = ret_obj['comment_srl'];
  var url = "./?mid="+mid+"&document_srl="+document_srl;
  if(comment_srl) url += "#comment_"+comment_srl;

  alert(message);

  location.href = url;
}

/* 댓글 삭제 */
function procDeleteComment(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  var mid = ret_obj['mid'];
  var document_srl = ret_obj['document_srl'];
  var page = ret_obj['page'];
  var url = "./?mid="+mid+'&document_srl='+document_srl;
  if(page) url += "&page="+page;
  alert(message);

  location.href = url;
}

/* 트랙백 삭제 */
function procDeleteTrackback(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];
  var mid = ret_obj['mid'];
  var document_srl = ret_obj['document_srl'];
  var page = ret_obj['page'];
  var url = "./?mid="+mid+'&document_srl='+document_srl;
  if(page) url += "&page="+page;
  alert(message);

  location.href = url;
}

/* 카테고리 이동 */
function procChangeCategory(sel_obj, url) {
  var category_srl = sel_obj.options[sel_obj.selectedIndex].value;
  if(!category_srl) location.href=url;
  else location.href=url+'&category='+category_srl;
}
