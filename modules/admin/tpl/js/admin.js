/**
 * @file   : modules/admin/js/admin.js
 * @author : zero <zero@nzeo.com>
 * @desc   : admin 모듈의 javascript
 **/

// 현재 페이지 reload
function procReload(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];

  location.href = location.href;
}

// 로그아웃
function procLogout(ret_obj, response_tags) {
  var error = ret_obj['error'];
  var message = ret_obj['message'];

  location.href = "./admin.php";
}

// 로그인폼에서 아이디 포커스
function procAdminLoginFocus() {
  var fo = xGetElementById('user_id');
  if(fo) fo.focus();
}
