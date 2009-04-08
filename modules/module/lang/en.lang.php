<?php
    /**
     * @file   modules/module/lang/en.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  English language pack 
     **/

    $lang->virtual_site = "Virtual Site";
    $lang->module_list = "Modules List";
    $lang->module_index = "Modules List";
    $lang->module_category = "Module Category";
    $lang->module_info = "Module Info";
    $lang->add_shortcut = "Add Shortcuts";
    $lang->module_action = "Actions";
    $lang->module_maker = "Module Developer";
    $lang->module_license = 'License';
    $lang->module_history = "Update history";
    $lang->category_title = "Category Title";
    $lang->header_text = 'Header Text';
    $lang->footer_text = 'Footer Text';
    $lang->use_category = 'Enable Category';
    $lang->category_title = 'Category Title';
    $lang->checked_count = 'Number of Checked Articles';
    $lang->skin_default_info = 'Default Skin Info';
    $lang->skin_author = 'Skin Developer';
    $lang->skin_license = 'License';
    $lang->skin_history = 'Update history';
    $lang->module_copy = "Duplicate Module";
    $lang->module_selector = "Module Selector";
    $lang->do_selected = "선택된 것들을...";
    $lang->bundle_setup = "일괄 기본 설정";
    $lang->bundle_addition_setup = "일괄 추가 설정";
    $lang->bundle_grant_setup = "일괄 권한 설정";
    $lang->lang_code = "언어 코드";
    $lang->filebox = "파일박스";

    $lang->access_type = '접속 방법';
    $lang->access_domain = 'Doamin 접속';
    $lang->access_sid = 'Site ID 접속';
    $lang->about_domain = "In order to create more than one virtual site, each of them needs to have own domain name.<br />Sub-domain (e.g., aaa.bbb.com of bbb.com) also can be used. Input the address including the path installed xe. <br /> ex) www.xpressengine.com/xe";
    $lang->about_sid = '별도의 도메인이 아닌 http://XE주소/ID 로 접속할 수 있습니다. 모듈명(mid)와 중복될 수 없습니다.<br/>첫글자는 영문으로 시작해야 하고 영문과 숫자 그리고 _ 만 사용할 수 있습니다';
    $lang->msg_already_registed_sid = '이미 등록된 사이트 ID 입니다. 게시판등의 mid와도 중복이 되지 않습니다. 다른 ID를 입력해주세요.';
    $lang->msg_already_registed_domain = "It is already registered domain name. Please use the different one.";

    $lang->header_script = "Header Script";
    $lang->about_header_script = "You can input the html script between &lt;header&gt; and &lt;/header&gt; by yourself.<br />You can use &lt;script, &lt;style or &lt;meta tag";

    $lang->grant_access = "Access";
    $lang->grant_manager = "Management";

    $lang->grant_to_all = "All users";
    $lang->grant_to_login_user = "Logged users";
    $lang->grant_to_site_user = "Joined users";
    $lang->grant_to_group = "Specification group users";

    $lang->cmd_add_shortcut = "Add Shortcut";
    $lang->cmd_install = "Install";
    $lang->cmd_update = "Update";
    $lang->cmd_manage_category = 'Manage Categories';
    $lang->cmd_manage_grant = 'Manage Permission';
    $lang->cmd_manage_skin = 'Manage Skins';
    $lang->cmd_manage_document = 'Manage Articles';
    $lang->cmd_find_module = '모듈 찾기';
    $lang->cmd_find_langcode = 'Find lang code';

    $lang->msg_new_module = "Create new module";
    $lang->msg_update_module = "Modify module";
    $lang->msg_module_name_exists = "The name already exists. Please try another name.";
    $lang->msg_category_is_null = 'There is no registered category.';
    $lang->msg_grant_is_null = 'There is no permission list.';
    $lang->msg_no_checked_document = 'No checked articles exist.';
    $lang->msg_move_failed = 'Failed to move';
    $lang->msg_cannot_delete_for_child = 'Cannot delete a category having child categories.';
	$lang->msg_limit_mid ="Only alphabets+[alphabets+numbers+_] can be used as module name.";
    $lang->msg_extra_name_exists = '이미 존재하는 확장변수 이름입니다. 다른 이름을 입력해주세요.';

    $lang->about_browser_title = "It will be shown in the browser title. It will be also used in a RSS/Trackback.";
    $lang->about_mid = "The module name will be used like http://address/?mid=ModuleName.\n(only english alphabet + [english alphabet ,numbers, and underscore(_)] are allowed)";
    $lang->about_default = "If checked, the default will be shown when access to the site without no mid value(mid=NoValue).";
    $lang->about_module_category = "It enables you to manage it through module category.\n The URL for the module manager is <a href=\"./?module=admin&amp;act=dispModuleAdminCategory\">Manage module > Module category </a>.";
    $lang->about_description= 'It is the description only for a manager.';
    $lang->about_default = 'If checked, this module will be shown when users access to the site without mid value (mid=NoValue).';
    $lang->about_header_text = 'The contents will be shown on the top of the module.(html tags available)';
    $lang->about_footer_text = 'The contents will be shown on the bottom of the module.(html tags available)';
    $lang->about_skin = 'You may choose a module skin.';
    $lang->about_use_category = 'If checked, category function will be enabled.';
    $lang->about_list_count = 'You can set the number of limit to show article in a page.(default is 20)';
	$lang->about_search_list_count = 'You may set the number of articles to be exposed when you use search or category function. (default is 20)';
    $lang->about_page_count = 'You can set the number of page link to move pages in a bottom of page.(default is 10)';
    $lang->about_admin_id = 'You can grant a manager to have all permissions to the module.';
    $lang->about_grant = 'If you disable all permissions for a specific object, members who has not logged in would get permission.'; 
    $lang->about_grant_deatil = '가입한 사용자는 cafeXE등 분양형 가상 사이트에 가입을 한 로그인 사용자를 의미합니다';
    $lang->about_module = "XE consists of modules except basic library.\n [Module Manage] module will show all installed modules and help you to manage them.";

	$lang->about_extra_vars_default_value = 'If multiple default values are needed,	 you can link them with comma(,).';
    $lang->about_search_virtual_site = "가상 사이트(카페XE등)의 도메인을 입력하신 후 검색하세요.<br/>가상 사이트이외의 모듈은 내용을 비우고 검색하시면 됩니다.  (http:// 는 제외)";
    $lang->about_langcode = "언어별로 다르게 설정하고 싶으시면 언어코드 찾기를 이용해주세요";
    $lang->about_file_extension= "%s 파일만 가능합니다.";
?>
