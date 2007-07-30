<?php
    /**
     * @file   modules/point/lang/ko.lang.php
     * @author zero <zero@nzeo.com>
     * @brief  Default language pack of point module
     **/

    $lang->point = "Point"; 
    $lang->level = "Level"; 

    $lang->about_point_module = "You can grant points on writing/adding comments/uploading/downloading.<br />But point module only configure settings, and the point will be accumulated only when point addon is activated";
    $lang->about_act_config = "Each module like board/blog has action such as writing/deleting/adding comments/deleting comments.<br />You can just add act value to link module except board/blog with point system.<br />Comma(,) will distinguish multiple values.";

    $lang->max_level = 'Max Level';
    $lang->about_max_level = 'You can set the max level. Level icons should be considered and 1000 is the maximum value you can set';

    $lang->level_icon = 'Level Icon';
    $lang->about_level_icon = 'Path of level icon is ./module/point/icons/[level].gif and max level could be different with icon set. So be careful';

    $lang->point_name = 'Point Name';
    $lang->about_point_name = 'You can give point name or unit';

    $lang->level_point = 'Level Point';
    $lang->about_level_point = 'Level will be adjusted when point reaches to each level point or decrease under each level point';

    $lang->disable_download = 'Prohibit Downloads';
    $lang->about_disable_download = "It will prohibit downloads when there isn't enough point. (Exclude image files)";

    $lang->about_module_point = "You can set point for each module and modules which don't have any value will use default point.<br />All point will be restored on acting reverse.";

    $lang->point_insert_document = 'On Writing';
    $lang->point_delete_document = 'On Deleting';
    $lang->point_insert_comment = 'On Adding Comments';
    $lang->point_delete_comment = 'On Deleting Comments';
    $lang->point_upload_file = 'On Uploading';
    $lang->point_delete_file = 'On Deleting Files';
    $lang->point_download_file = 'On Downloading Files (Exclude images)';


    $lang->cmd_point_config = 'Default Setting';
    $lang->cmd_point_module_config = 'Module Setting';
    $lang->cmd_point_act_config = 'Act Setting';
    $lang->cmd_point_member_list = 'Member Point List';

    $lang->msg_cannot_download = "You don't have enough point to download";
?>
