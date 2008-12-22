<?php
    if(!defined("__ZBXE__")) exit();

    /**
     * @file rainbow.addon.php
     * @author zero (zero@nzeo.com)
     * @brief Rainbow link addon
     *
     * 링크가 걸린 텍스트에 마우스 오버를 하면 무지개색으로 변하게 하는 애드온입니다.
     * rainbow.js 파일만 추가하는 것으로 끝납니다.
     * rainbow.js는 http://www.dynamicdrive.com에서 제작하였으며 저작권을 가지고 있습니다.
     * before_display_content 에서만 요청이 됩니다.
     **/

    if(Context::get('module')=='admin' || $called_position != 'before_module_init') return;

    // Context::addJsFile()을 이용하면 끝
    Context::addJsFile($addon_path.'js/rainbow.js');
?>