/**
 * @file   modules/page/js/admin.js
 * @author zero (zero@nzeo.com)
 * @desc   page모듈의 관리자용 javascript
 **/

/* 모듈 생성 후 */
function completeInsertPage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = "./?module=admin&module_srl="+module_srl+"&act=dispPageAdminInfo";
    if(page) url += "&page="+page;

    location.href = url;
}


/* 모듈 삭제 후 */
function completeDeletePage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = "./?module=admin&act=dispPageAdminContent";
    if(page) url += "&page="+page;

    location.href = url;
}

/* 카테고리 이동 */
function doChangeCategory(sel_obj, url) {
    var module_category_srl = sel_obj.options[sel_obj.selectedIndex].value;
    if(!module_category_srl) location.href=url;
    else location.href=url+'&module_category_srl='+module_category_srl;
}
