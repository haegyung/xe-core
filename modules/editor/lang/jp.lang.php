<?php
    /**
     * @file   modules/editor/lang/jp.lang.php
     * @author zero <zero@nzeo.com> 翻訳：RisaPapa、ミニミ
     * @brief  ウィジウィグエディター（editor）モジュールの基本言語パッケージ
     **/

    $lang->editor = "ウイジウイグエディター";
    $lang->component_name = "コンポーネント";
    $lang->component_version = "バージョン";
    $lang->component_author = "作者";
    $lang->component_link = "リンク";
    $lang->component_date = "作成日";
    $lang->component_description = "説明";
    $lang->component_extra_vars = "設定変数";
    $lang->component_grant = "権限設定"; 

    $lang->about_component = "コンポーネント情報";
    $lang->about_component_grant = "選択されたグループでのみ使用できます（すべて解除時はすべて使用可能）。";

    $lang->msg_component_is_not_founded = '%s エディターのコンポーネントが見つかりません。';
    $lang->msg_component_is_inserted = '選択されたコンポーネントは既に入力されています。';
    $lang->msg_component_is_first_order = '選択されたコンポーネントは最初に位置しています。';
    $lang->msg_component_is_last_order = '選択されたコンポーネントは最後に位置しています。';
    $lang->msg_load_saved_doc = "自動保存された書き込みがあります。復旧しますか？\n書き終わってから登録すると前の自動保存データは削除されます。";
    $lang->msg_auto_saved = "自動保存されました。";

    $lang->cmd_disable = "未使用";
    $lang->cmd_enable = "使用";

    $lang->editor_skin = '에디터 스킨';
    $lang->upload_file_grant = '파일 첨부 권한'; 
    $lang->enable_default_component_grant = '기본 컴포넌트 사용 권한';
    $lang->enable_component_grant = '컴포넌트 사용 권한';
    $lang->enable_html_grant = 'HTML편집 권한';
    $lang->enable_autosave = '자동저장 사용';
    $lang->height_resizable = '높이 조절 가능';
    $lang->editor_height = '에디터 높이';

    $lang->about_editor_skin = '에디터 스킨을 선택하실 수 있습니다';
    $lang->about_upload_file_grant = '파일을 첨부할 수 있는 권한을 지정하실 수 있습니다 (모두 해제시 아무나 첨부 가능합니다)';
    $lang->about_default_component_grant = '에디터에서 사용되는 기본 컴포넌트를 사용할 수 있는 권한을 지정할 수 있습니다. (모두 해제시 아무나 사용 가능합니다)';
    $lang->about_component_grant = '기본 컴포넌트외의 확장 컴포넌트 기능을 사용할 수 있는 권한을 지정할 수 있습니다. (모두 해제시 아무나 사용 가능합니다)';
    $lang->about_editor_height = '에디터의 기본 높이를 지정하실 수 있습니다';
    $lang->about_editor_height_resizable = '에디터의 높이를 직접 변경할 수 있도록 허용합니다';
    $lang->about_enable_html_grant = 'HTML편집 권한을 부여할 수 있습니다.';
    $lang->about_enable_autosave = '글작성시 자동 저장 기능을 활성화 시킬 수 있습니다';

    $lang->edit->fontname = 'フォント';
    $lang->edit->fontsize = 'フォントサイズ';
    $lang->edit->use_paragraph = '段落機能';
    $lang->edit->fontlist = array(
    "ＭＳ Ｐゴシック",
    "ＭＳ Ｐ明朝",
    "Osaka－等幅",
    "ヒラギノ角ゴ Pro W3",
    "times",
    "Courier",
    "Tahoma",
    "Arial",
    );

    $lang->edit->header = "見出し";
    $lang->edit->header_list = array(
    "h1" => "見出し１",
    "h2" => "見出し２",
    "h3" => "見出し３",
    "h4" => "見出し４",
    "h5" => "見出し５",
    "h6" => "見出し６",
    );

    $lang->edit->submit = '送信';

    $lang->edit->help_remove_format = "選択領域の中のタグを消します。";
    $lang->edit->help_strike_through = "テキストに取り消し線を表示します。";
    $lang->edit->help_align_full = "左右の余白に合わせて文字列を配置します。";

    $lang->edit->help_fontcolor = "テキストの色を指定します。";
    $lang->edit->help_fontbgcolor = "テキストの背景色を指定します。";
    $lang->edit->help_bold = "テキストを太字に指定します。";
    $lang->edit->help_italic = "テキストを斜体にします。";
    $lang->edit->help_underline = "テキストに下線（アンダーライン）を引きます。";
    $lang->edit->help_strike = "取り消し線を引きます。";
    $lang->edit->help_redo = "繰り返し";
    $lang->edit->help_undo = "元に戻す";
    $lang->edit->help_align_left = "テキストを左揃えで表示します。";
    $lang->edit->help_align_center = "テキストを中央揃えで表示します。";
    $lang->edit->help_align_right = "テキストを右揃えで表示します。";
    $lang->edit->help_add_indent = "インデントを増やします。";
    $lang->edit->help_remove_indent = "インデントを減らします。";
    $lang->edit->help_list_number = "段落番号";
    $lang->edit->help_list_bullet = "箇条書き";
    $lang->edit->help_use_paragrapth = "段落機能を使用する場合は、「Ctrl+Enter」を押します（書き終わった後、「Alt+S」を押すと保存されます）。";

    $lang->edit->upload = '添付';
    $lang->edit->upload_file = 'ファイル添付'; 
    $lang->edit->link_file = 'テキスト挿入';
    $lang->edit->delete_selected = '選択リスト削除';

    $lang->edit->icon_align_article = '一段落';
    $lang->edit->icon_align_left = '左揃え';
    $lang->edit->icon_align_middle = '中央揃え';
    $lang->edit->icon_align_right = '右揃え';

    $lang->about_dblclick_in_editor = '背景、文字、イメージ、引用文の上にカーソルを合わせ、ダブルクリックすると詳細設定できるコンポーネントを表示します。';
?>
