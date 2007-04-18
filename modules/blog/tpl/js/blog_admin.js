/**
 * @file   modules/blog/js/blog_admin.js
 * @author zero (zero@nzeo.com)
 * @brief  blog 모듈의 관리자용 javascript
 **/

/* 모듈 생성 후 */
function completeInsertBlog(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = location.href.setQuery('module_srl',module_srl).setQuery('act','dispBlogAdminBlogInfo');
    if(page) url.setQuery('page',page);
    location.href = url;
}

/* 모듈 삭제 후 */
function completeDeleteBlog(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = location.href.setQuery('act','dispBlogAdminContent').setQuery('module_srl','');
    if(page) url = url.setQuery('page',page);
    location.href = url;
}

/* 카테고리 관련 작업들 */
function doUpdateCategory(category_srl, mode, message) {
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    var fo_obj = xGetElementById('fo_category_info');
    fo_obj.category_srl.value = category_srl;
    fo_obj.mode.value = mode;

    procFilter(fo_obj, update_category);
}

/* 카테고리 정보 수정 후 */
function completeUpdateCategory(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var module_srl = ret_obj['module_srl'];
    var page = ret_obj['page'];
    alert(message);

    var url = location.href.setQuery('module_srl',module_srl).setQuery('act','dispBlogAdminCategoryInfo');
    if(page) url.setQuery('page',page);
    location.href = url;
}

/* 권한 관련 */
function doSelectAll(obj, key) {
    var fo_obj = obj.parentNode;
    while(fo_obj.nodeName != 'FORM') { 
        fo_obj = fo_obj.parentNode; 
    }

    for(var i=0;i<fo_obj.length;i++) {
        var tobj = fo_obj[i];
        if(tobj.name == key) tobj.checked=true;
    }
}

function doUnSelectAll(obj, key) {
    var fo_obj = obj.parentNode;
    while(fo_obj.nodeName != 'FORM') { 
        fo_obj = fo_obj.parentNode; 
    }

    for(var i=0;i<fo_obj.length;i++) {
        var tobj = fo_obj[i];
        if(tobj.name == key) tobj.checked = false;
    }
}

function completeInsertGrant(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = location.href.setQuery('module_srl',module_srl).setQuery('act','dispBlogAdminGrantInfo');
    if(page) url.setQuery('page',page);
    location.href = url;
}

/* 카테고리 이동 */
function doChangeCategory(sel_obj, url) {
    var module_category_srl = sel_obj.options[sel_obj.selectedIndex].value;
    if(!module_category_srl) location.href=url;
    else location.href=url+'&module_category_srl='+module_category_srl;
}

/* 선택된 글의 삭제 또는 이동 */
function doManageDocument(type, mid) {
    var fo_obj = xGetElementById("fo_management");
    fo_obj.type.value = type;

    procFilter(fo_obj, manage_checked_document);
}

/* 선택된 글의 삭제 또는 이동 후 */
function completeManageDocument(ret_obj) {
    if(opener) opener.location.href = opener.location.href;
    alert(ret_obj['message']);
    window.close();
}
