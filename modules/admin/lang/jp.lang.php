<?php
    /**
     * @file   jp.lang.php
     * @author zero (zero@nzeo.com) 翻訳：RisaPapa、ミニミ // 細かい修正：liahona
     * @brief  日本語言語パッケージ（基本的な内容のみ）
     **/

    $lang->admin_info = '管理者情報';
    $lang->admin_index = '管理者トップページ';
    $lang->control_panel = 'コントロールパネル';

    $lang->module_category_title = array(
        'service' => 'サービス管理',
        'member' => '会員管理',
        'content' => 'コンテンツ管理',
        'statistics' => '統計確認',
        'construction' => 'サイト設定',
        'utility' => '機能設定',
        'interlock' => '連動設定',
        'accessory' => '付加機能設定',
        'migration' => 'データ管理／復元',
        'system' => 'システム管理',
    );

    $lang->newest_news = "最新ニュース";

    $lang->env_setup = "環境設定";
    $lang->default_url = '基本URL';
    $lang->about_sso_url = "複数のvirtual siteを運営する場合、どちらからログインしてもvirtual siteの間でログイン情報を維持出来るようにするためには、基本になるサイトでのXEをインストールしたurlを登録して下さい。 (例: http://ドメイン/インストールパス)";
    

    $lang->env_information = "環境情報";
    $lang->current_version = "インストールバージョン";
    $lang->current_path = "インストールパス";
    $lang->released_version = "最新バージョン";
    $lang->about_download_link = "インストールされたバージョンより新しいバージョンが配布されています。\n｢ダウンロード｣リンクをクリックするとダウンロード出来ます。";

    $lang->item_module = "モジュールリスト";
    $lang->item_addon  = "アドオンリスト";
    $lang->item_widget = "ウィジェットリスト";
    $lang->item_layout = "レイアウトリスト";

    $lang->module_name = "モジュール名";
    $lang->addon_name = "アドオン名";
    $lang->version = "バージョン";
    $lang->author = "作者";
    $lang->table_count = "テーブル数";
    $lang->installed_path = "インストールパス";

    $lang->cmd_shortcut_management = "メニュー編集";

    $lang->msg_is_not_administrator = '管理者のみアクセス出来ます';
    $lang->msg_manage_module_cannot_delete = 'モジュール、アドオン、ウィジェットのショットカットは削除出来ません。';
    $lang->msg_default_act_is_null = 'デフォルトの管理者のアクションが指定されていないため、ショットカットを登録することが出来ません。';

    $lang->welcome_to_xe = 'XEの管理者ページです。';
    $lang->about_admin_page = "管理者ページはまだ未完成です。\nクローズベータバージョンの期間に、多くの方々からご意見をいただきながら、必ず必要なコンテンツを埋めていきたいと思います。";
    $lang->about_lang_env = "上で設定された言語を、初めてサイトに訪問したユーザに同じく適用させるためには、希望する言語に変更してから「保存」ボタンをクリックして下さい。";

    $lang->xe_license = 'XEのライセンスはGPLです。';
    $lang->about_shortcut = 'よく使用するモジュールに登録されたショートカットは削除出来ます。';

    $lang->yesterday = "昨日";
    $lang->today = "今日";

    $lang->cmd_lang_select = "言語選択";
    $lang->about_cmd_lang_select = "選択された言語のみでサービスを行います。";
    $lang->about_recompile_cache = "要らないｄか誤ったキャッシューファイルを整理します。";
    $lang->use_ssl = "SSL環境設定";
    $lang->ssl_options = array(
        'none' => "使わない",
        'optional' => "部分的に使う",
        'always' => "常に使う"
    );
    $lang->about_use_ssl = "「部分的に使う場合」は「会員登録/会員情報変更」など特定のactionでSSLを利用する場合、「常に使う」は全てのサービスがSSLを使う場合に選択します。";
    $lang->server_ports = "サーバーポート指定";
    $lang->about_server_ports = "一般的に使われているHTTPの80、HTTPSの443以外の他のポートを使うために、ポートを指定して下さい。";
    $lang->use_db_session = '인증 세션 DB 사용';
    $lang->about_db_session = '인증시 사용되는 PHP 세션을 DB로 사용하는 기능입니다.<br/>웹서버의 사용율이 낮은 사이트에서는 비활성화시 사이트 응답 속도가 향상될 수 있습니다';
?>
