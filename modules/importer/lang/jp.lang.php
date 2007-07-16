<?php
    /**
     * @file   jp.lang.php
     * @author zero (zero@nzeo.com) 翻訳：RisaPapa
     * @brief  Importer(importer) モジュールの基本言語パッケージ
     **/

    // ボタンに使用する用語
    $lang->cmd_sync_member = '同期化';
    $lang->cmd_continue = '続ける';

    // 항목
    $lang->importer = 'ゼロボードのデータ変換';
    $lang->source_type = 'データ変換の対象';
    $lang->type_member = '会員情報';
    $lang->type_module = '書き込みデータの情報';
    $lang->type_syncmember = '会員情報の同期化';
    $lang->target_module = '対象モジュール';
    $lang->xml_file = 'XMLファイル';

    $lang->import_step_title = array(
        1 => 'Step 1. 以前対象選択',
        12 => 'Step 1-2. 対象モジュール選択',
        13 => 'Step 1-3. 対象カテゴリ選択',
        2 => 'Step 2. XMLファイルアップロード',
        3 => 'Step 2. 会員情報と書き込みデータの同期化',
    );

    $lang->import_step_desc = array(
        1 => '変換するXMLファイルの種類を選択してください。',
        12 => 'データ変換を行う対象のモジュールを選択してください。',
        13 => 'データ変換を行う対象のカテゴリを選択してください。',
        2 => "データ変換を行うXMLファイルのパスを入力してください。\n同じアカウントのサーバ上では、相対または絶対パスを、異なるサーバにアップロードされている場合は、「http://アドレス..」を入力してください。",
        3 => '会員情報と書き込みデータの情報の変換を行った後、データが合わない場合があります。この時に同期化を行うと「user_id」をもとに正しく動作するようにします。',
    );

    // 案内/警告
    $lang->msg_sync_member = '同期化ボタンをクリックすると会員情報と書き込みデータの情報の同期化が始まります。';
    $lang->msg_no_xml_file = 'XMLファイルが見つかりません。パスをもう一度確認してください。';
    $lang->msg_invalid_xml_file = 'XMLファイルのフォーマットが正しくありません。';
    $lang->msg_importing = '%d個のデータの内、%d個を変換中です（止まったままの場合は「続ける」ボタンをクリックしてください）。';
    $lang->msg_import_finished = '%d個のデータ変換が完了しました。場合によって変換されていないデータがあることもあります。';
    $lang->msg_sync_completed = '会員情報、書き込みデータ、コメントのデータの同期化（変換）が完了しました。';

    // Bla, Blah..
    $lang->about_type_member = 'データ変換の対象が会員情報の場合は選択してください。';
    $lang->about_type_module = 'データ変換の対象が書き込みデータである場合は選択してください。';
    $lang->about_type_syncmember = '会員情報と書き込みデータなどの変換を行った後、会員情報を同期化する必要がある場合は、選択してください。';
    $lang->about_importer = "ゼロボード4、zb5betaまたは他のプログラムの書き込みデータをゼロボードXEのデータに変換することができます。\n変換するためには、<a href=\"#\" onclick=\"winopen('');return false;\">XML Exporter</a>を利用して変換したい書き込みデータをXMLファイルで作成してアップロードしてください。";
?>