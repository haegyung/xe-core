<?php
    /**
     * @file   modules/point/lang/zh-CN.lang.php
     * @author zero <zero@nzeo.com>
     * @brief  积分 (point) 模块简体中文语言包
     **/

    $lang->point = "积分"; 
    $lang->level = "级别"; 

    $lang->about_point_module = "积分系统可以在发表/删除新帖，发表/删除评论，上传/下载/删除/文件等动作时，付与其相应的积分。<br />积分系统模块只能设置各项积分，不能记录积分。只有激活积分插件后才可以正常记录相关积分。";
    $lang->about_act_config = "版面，博客等模块都有发表/删除新帖，发表/删除评论等动作。 <br />要想与版面/博客之外的模块关联积分功能时，添加与其各模块功能相适合的act值即可。";

    $lang->max_level = '最高级别';
    $lang->about_max_level = '可以指定最高级别。级别共设1000级，因此制作级别图标时要好好考虑一下。';

    $lang->level_icon = '级别图标';
    $lang->about_level_icon = '级别图标要以 ./modules/point/icons/级别.gif形式指定，有时出现最高级别的图标跟您指定的最高级别图标不同的现象，敬请注意。';

    $lang->point_name = '积分名';
    $lang->about_point_name = '可以指定积分名或积分单位。';

    $lang->level_point = '级别积分';
    $lang->about_level_point = '积分达到或减少到下列各级别所设置的积分值时，将会自动调节相应级别。';

    $lang->disable_download = '禁止下载';
    $lang->about_disable_download = '没有积分时，将禁止下载。 (图片除外)';

    $lang->level_point_calc = '计算级别积分';
    $lang->expression = '使用级别变数<b>"i"</b>输入JS数学函数。例: Math.pow(i, 2) * 90';
    $lang->cmd_exp_calc = '计算';
    $lang->cmd_exp_reset = '初始化';

    $lang->cmd_point_recal = '积分初始化';
    $lang->about_cmd_point_recal = '积分初始化。即只保留文章/评论/附件/新会员注册的相关积分项。<br />其中，初始化后的新会员注册积分项，将在会员有相关动作(发表主题/评论等)时，才付与其相应的积分。<br />此项功能请务必慎用！此项功能只能在数据转移或真的需要初始化所有积分时才可以使用。';

    $lang->point_link_group = '用户组绑定';
    $lang->about_point_link_group = '即级别绑定用户组。当级别达到指定级别时，会员所属用户组将自动更新为与其相对应的用户组。只是更新为新的用户组时，之前的默认用户组将自动被删除。';

    $lang->about_module_point = '可以分别对各模块进行积分设置，没有被设置的模块将使用默认值。<br />所有积分在相反动作下恢复原始值。即：发表新帖后再删除得到的积分为0分。';

    $lang->point_signup = '注册';
    $lang->point_insert_document = '发表新帖';
    $lang->point_delete_document = '删除主题';
    $lang->point_insert_comment = '发表评论';
    $lang->point_delete_comment = '删除评论';
    $lang->point_upload_file = '上传文件';
    $lang->point_delete_file = '删除文件';
    $lang->point_download_file = '下载文件 (图片除外)';
    $lang->point_read_document = '查看主题';


    $lang->cmd_point_config = '基本设置';
    $lang->cmd_point_module_config = '对象模块设置';
    $lang->cmd_point_act_config = '功能act设置';
    $lang->cmd_point_member_list = '会员积分目录';

    $lang->msg_cannot_download = '积分不足无法下载！';

    $lang->point_recal_message = '计算并应用中(%d / %d)。';
    $lang->point_recal_finished = '积分重新计算并应用完毕。';
?>
