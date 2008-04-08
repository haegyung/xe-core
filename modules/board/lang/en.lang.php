<?php
    /**
     * @file   en.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  board modules's basic language pack
     **/

    $lang->board = "Board"; 

    $lang->except_notice = "Exclude Notices";

    $lang->cmd_manage_menu = 'Manage Menus';
    $lang->cmd_make_child = 'Add Child Category';
    $lang->cmd_enable_move_category = "Change Category Position (Drag the top menu after selection)";

    // Item
    $lang->parent_category_title = 'Parent Category';
    $lang->category_title = 'Category';
    $lang->expand = 'Expand';
    $lang->category_group_srls = 'Accessable Group';
    $lang->search_result = 'Search Result';
    $lang->consultation = 'Consultation';
    $lang->admin_mail = "Administrator's Mail";

    // words used in button
    $lang->cmd_board_list = 'Boards List';
    $lang->cmd_module_config = 'Common Board Setting';
    $lang->cmd_view_info = 'Board Info';

    // blah blah..
    $lang->about_category_title = 'Please input category name';
    $lang->about_expand = 'By selecting this option, it will be always expanded';
    $lang->about_category_group_srls = 'Only the selected group will be able to see current categories. (Manually open xml file to expose)';
    $lang->about_layout_setup = 'You can manually modify board layout code. Insert or manage the widget code anywhere you want';
    $lang->about_board_category = 'You can make board categories.<br />When board category is broken, try rebuilding the cache file manually.';
    $lang->about_except_notice = "Notice articles will not be displayed on normal list.";
    $lang->about_board = "This module is for creating and managing boards.\nYou may select the module name from the list after creating one to configure specifically.\nPlease be careful with board's module name, since it will be the url. (ex : http://domain/zb/?mid=modulename)"; 
	$lang->about_consultation = "Non-administrator members would see their own articles.\nNon-members would not be able to write articles when using consultation.";
    $lang->about_admin_mail = 'A mail will be sent when an article or comment is submitted.<br />Multiple mails can be sent with commas(,).';
?>
