/**
 * @file   modules/blog/js/blog.js
 * @author zero (zero@nzeo.com)
 * @brief  blog 모듈의 javascript
 **/

/* 글쓰기 작성후 */
function completeDocumentInserted(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var category_srl = ret_obj['category_srl'];

    alert(message);

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(category_srl) url = url.setQuery('category',category_srl);
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

/* 검색 실행 */
function completeSearch(fo_obj, params) {
    fo_obj.submit();
}

// 현재 페이지 reload
function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    location.href = location.href;
}

/* 댓글쓰기 submit */
function doCommentSubmit() {
    var fo_obj = xGetElementById('fo_comment_write');
    procFilter(fo_obj, insert);
}

/* 댓글 글쓰기 작성후 */
function completeInsertComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    alert(message);
    location.href = current_url.setQuery('comment_srl','').setQuery('act','');
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

/* 트랙백 삭제 */
function completeDeleteTrackback(ret_obj) {
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
function doChangeCategory(sel_obj, url) {
    var category_srl = sel_obj.options[sel_obj.selectedIndex].value;
    if(!category_srl) location.href=url;
    else location.href=url+'&category='+category_srl;
}
