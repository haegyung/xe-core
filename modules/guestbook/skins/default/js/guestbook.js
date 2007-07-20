/**
 * @file   modules/guestbook/js/guestbook.js
 * @author zero (zero@nzeo.com)
 * @brief  guestbook 모듈의 javascript
 **/

/* 글쓰기 작성후 */
function completeDocumentInserted(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];

    alert(message);

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    location.href = url;
}

/* 글 삭제 */
function completeDeleteDocument(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var page = ret_obj['page'];

    var url = "./?mid="+mid;
    if(page) url += "&page="+page;

    alert(message);

    location.href = url;
}

// 현재 페이지 reload
function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    location.href = location.href;
}

/* 댓글 글쓰기 작성후 */
function completeInsertComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var comment_srl = ret_obj['comment_srl'];

    var url = "./?mid="+mid+"&document_srl="+document_srl;
    //if(comment_srl) url += "#comment_"+comment_srl;

    alert(message);

    location.href = url;
}

/* 댓글 삭제 */
function completeDeleteComment(ret_obj) {
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
