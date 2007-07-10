<?php
    /**
     * @file   modules/file/lang/jp.lang.php
     * @author zero <zero@nzeo.com> 翻訳：RisaPapa
     * @brief  添付ファイル（file）モジュールの基本言語パッケージ
     **/

    $lang->file = '添付ファイル';
    $lang->file_name = 'ファイル名';
    $lang->file_size = 'ファイルサイズ';
    $lang->download_count = 'ダウンロード数';
    $lang->status = '状態';
    $lang->is_valid = '有効';
    $lang->is_stand_by = '待機';
    $lang->file_list = '添付ファイルリスト';
    $lang->allowed_filesize = 'ファイルサイズの制限';
    $lang->allowed_attach_size = 'ドキュメント添付の制限';
    $lang->allowed_filetypes = 'アップロードできるファイルの種類';

    $lang->about_allowed_filesize = '一つのファイルに対してアップロードできる最大サイズを指定します（管理者除外）。';
    $lang->about_allowed_attach_size = '一つのドキュメント（書き込み）に対して添付できる最大サイズを指定します（管理者除外）。';
    $lang->about_allowed_filetypes = 'アップロードできるファイルの種類のみ添付できます。"*.拡張子"で指定し、 ";"で区切って任意の拡張子を追加して指定できます（管理者除外）。<br />ex) *.* or *.jpg;*.gif;<br />';

    $lang->cmd_delete_checked_file = '選択項目削除';
    $lang->cmd_move_to_document = 'ドキュメントに移動する';
    $lang->cmd_download = 'ダウンロード';

    $lang->msg_cart_is_null = '削除するファイルを選択してください';
    $lang->msg_checked_file_is_deleted = '%d個の添付ファイルを削除しました';
    $lang->msg_exceeds_limit_size = 'ファイルサイズの制限を超えたため、添付できません。';

    $lang->search_target_list = array(
        'filename' => 'ファイル名',
        'filesize' => 'ファイルサイズ（(Byte以上）',
        'download_count' => 'ダウンロード数（以上）',
        'regdate' => '登録日',
        'ipaddress' => 'IPアドレス',
    );
?>
