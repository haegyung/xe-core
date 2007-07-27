<?php
    /**
     * @file   modules/point/lang/jp.lang.php
     * @author zero <zero@nzeo.com> 翻訳：RisaPapa
     * @brief  ポイント（point）モジュールの基本言語パッケージ
     **/

    $lang->point = "ポイント"; 
    $lang->level = "レベル"; 

    $lang->about_point_module = "ポイントモジュールでは、書き込み作成/コメント作成/アップロード/ダウンロードなどのユーザの活動に対してポイントの計算を行います。但し、ポイントモジュールでは設定のみを行い、アドオンでポイントシステムを「使用」に設定しなければポイントは累積されません。";
    $lang->about_act_config = "掲示板、ブログなどのモジュールごと書き込み作成・削除/コメント作成・削除などのアクションがあります。掲示板/ブログ以外のモジュールにポイントシステムを連動させたい場合は、各機能のアクションの「act値」を追加します。連動は「,（コンマ）」で行います。";

    $lang->max_level = '最高レベル';
    $lang->about_max_level = '最高レベルを指定することができます。最高レベルは「100」がマクシマムなので、レベルアイコンに注意が必要です。';

    $lang->level_icon = 'レベルアイコン';
    $lang->about_level_icon = 'レベルアイコンは、「./modules/point/icons/レベル.gif」で指定されるため、最高レベルとアイコンセットが異なる場合がありますので、注意してください。';

    $lang->point_name = 'ポイント名';
    $lang->about_point_name = 'ポイントの名前、単位が指定できます。';

    $lang->level_point = 'レベルポイント';
    $lang->about_level_point = '下の各レベルのポイントが増加したり、減少するとレベルが調節されます。';

    $lang->disable_download = 'ダウンロード禁止';
    $lang->about_disable_download = 'チェックするとポイントがない場合、ダウンロードを禁止します（イメージファイル除外）。';

    $lang->about_module_point = 'モジュール別にポイントを指定することができますが、指定されていないモジュールでは、デフォルトポイントが使用されます。すべてのポイント数は、反対のアクションを行った際には原状復帰されます。';

    $lang->point_insert_document = '書き込み作成';
    $lang->point_delete_document = '書き込み削除';
    $lang->point_insert_comment = 'コメント作成';
    $lang->point_delete_comment = 'コメント削除';
    $lang->point_upload_file = 'アップロード';
    $lang->point_delete_file = 'ファイル削除';
    $lang->point_download_file = 'ダウンロード';


    $lang->cmd_point_config = 'デフォルト設定';
    $lang->cmd_point_module_config = 'モジュール別設定';
    $lang->cmd_point_act_config = '機能別アクション設定';
    $lang->cmd_point_member_list = '会員ポイントリスト';

    $lang->msg_cannot_download = 'ポイントが不足しているため、ダウンロードできません。';
?>
