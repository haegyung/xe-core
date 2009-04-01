<?php
  /**
     * @file   ru.lang.php
     * @author zero <zero@nzeo.com> | translation by Maslennikov Evgeny aka X-[Vr]bL1s5 | e-mail: x-bliss[a]tut.by; ICQ: 225035467;
     * @brief  Russian basic language pack
     **/

    $lang->editor = 'WYSIWYG-Редактор';
    $lang->component_name = 'Компонент';
    $lang->component_version = 'Версия';
    $lang->component_author = 'Разработчик';
    $lang->component_link = 'Ссылка';
    $lang->component_date = 'Дата';
    $lang->component_license = 'License';
    $lang->component_history = 'History';
    $lang->component_description = 'Описание';
    $lang->component_extra_vars = 'Экстра перем.';
    $lang->component_grant = 'Настройки прав доступа';

    $lang->about_component = 'О компоненте';
    $lang->about_component_grant = 'Только выбранным группам позволено использование.<br /> (Каждый может использовать его, если режим выключен)';
    $lang->about_component_mid = '에디터 컴포넌트가 사용될 대상을 지정할 수 있습니다.<br />(모두 해제시 모든 대상에서 사용 가능합니다)';

    $lang->msg_component_is_not_founded = 'Невозможно найти компонент редактора %s';
    $lang->msg_component_is_inserted = 'Выбранный компонент уже присутствует';
    $lang->msg_component_is_first_order = 'Выбранный компонент находится на первой позиции';
    $lang->msg_component_is_last_order = 'Выбранный компонент находится на последней позиции';
    $lang->msg_load_saved_doc = "Существует автоматически сохраненная статья. Хотите ли Вы ее восстановить?\nАвтоматически сохраненный черновик будет отменен после сохранения текущей статьи";
    $lang->msg_auto_saved = 'Автоматически сохранено';

    $lang->cmd_disable = 'Неавтивно';
    $lang->cmd_enable = 'Активно';

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
    $lang->about_editor_height = '에디터의 기본 높이를 지정하실 수 있습니다';
    $lang->about_editor_height_resizable = '에디터의 높이를 직접 변경할 수 있도록 허용합니다';
    $lang->about_enable_html_grant = 'HTML편집 권한을 부여할 수 있습니다.';
    $lang->about_enable_autosave = '글작성시 자동 저장 기능을 활성화 시킬 수 있습니다';

    $lang->edit->fontname = 'Шрифт';
    $lang->edit->fontsize = 'Размер';
    $lang->edit->use_paragraph = 'Функции параграфа';
    $lang->edit->fontlist = array(
    'Arial'=>'Arial',
    'Arial Black'=>'Arial Black',
    'Tahoma'=>'Tahoma',
    'Verdana'=>'Verdana',
    'Sans-serif'=>'Sans-serif',
    'Serif'=>'Serif',
    'Monospace'=>'Monospace',
    'Cursive'=>'Cursive',
    'Fantasy'=>'Fantasy',
    );

    $lang->edit->header = 'Стиль';
    $lang->edit->header_list = array(
    'h1' => 'Заголовок 1',
    'h2' => 'Заголовок 2',
    'h3' => 'Заголовок 3',
    'h4' => 'Заголовок 4',
    'h5' => 'Заголовок 5',
    'h6' => 'Заголовок 6',
    );

    $lang->edit->submit = 'Принять';

    $lang->edit->fontcolor = 'Text Color';
    $lang->edit->fontbgcolor = 'Background Color';
    $lang->edit->bold = 'Bold';
    $lang->edit->italic = 'Italic';
    $lang->edit->underline = 'Underline';
    $lang->edit->strike = 'Strike';
    $lang->edit->sup = 'Sup';
    $lang->edit->sub = 'Sub';
    $lang->edit->redo = 'Re Do';
    $lang->edit->undo = 'Un Do';
    $lang->edit->align_left = 'Align Left';
    $lang->edit->align_center = 'Align Center';
    $lang->edit->align_right = 'Align Right';
    $lang->edit->align_justify = 'Align Justify';
    $lang->edit->add_indent = 'Indent';
    $lang->edit->remove_indent = 'Outdent';
    $lang->edit->list_number = 'Orderd List';
    $lang->edit->list_bullet = 'Unordered List';
    $lang->edit->remove_format = 'Style Remover';

    $lang->edit->help_fontcolor = 'Выберать цвет шрифта';
    $lang->edit->help_fontbgcolor = 'Выберать цвет фона шрифта';
    $lang->edit->help_bold = 'Сделать шрифт жирным';
    $lang->edit->help_italic = 'Сделать шрифт наклонным';
    $lang->edit->help_underline = 'Сделать шрифт подчеркнутым';
    $lang->edit->help_strike = 'Сделать шрифт зачеркнутым';
    $lang->edit->help_sup = 'Sup';
    $lang->edit->help_sub = 'Sub';
    $lang->edit->help_redo = 'Восстановить отмененное';
    $lang->edit->help_undo = 'Отмена';
    $lang->edit->help_align_left = 'Выровнять по левому краю';
    $lang->edit->help_align_center = 'Выровнять по центру';
    $lang->edit->help_align_right = 'Выровнять по правому краю';
    $lang->edit->help_add_indent = 'Добавить отступ';
    $lang->edit->help_remove_indent = 'Удалить отступ';
    $lang->edit->help_list_number = 'Применить числовой список';
    $lang->edit->help_list_bullet = 'Применить маркированный список';
    $lang->edit->help_use_paragraph = 'Нажмите Ctrl+Enter, чтобы отметить параграф. (Нажмите Alt+S , чтобы сохранить)';

    $lang->edit->url = 'URL';
    $lang->edit->blockquote = 'Blockquote';
    $lang->edit->table = 'Table';
    $lang->edit->image = 'Image';
    $lang->edit->multimedia = 'Movie';
    $lang->edit->emoticon = 'Emoticon';

    $lang->edit->upload = 'Вложение';
    $lang->edit->upload_file = 'Вложить';
    $lang->edit->link_file = 'Вставить в содержание';
    $lang->edit->delete_selected = 'Удалить выбранное';

    $lang->edit->icon_align_article = 'Занять весь параграф';
    $lang->edit->icon_align_left = 'Выровнять по левому краю';
    $lang->edit->icon_align_middle = 'Выровнять по центру';
    $lang->edit->icon_align_right = 'Выровнять по правому краю';

    $lang->about_dblclick_in_editor = 'Вы можете установить детальную конфигурацию компонента двойным щелчком по фону, тексту, рисункам или цитатам';


    $lang->edit->rich_editor = '스타일 편집기';
    $lang->edit->html_editor = 'HTML 편집기';
    $lang->edit->extension ='확장 컴포넌트';
    $lang->edit->help = '도움말';
    $lang->edit->help_command = '단축키 안내';
    
    $lang->edit->lineheight = '줄간격';
	$lang->edit->fontbgsampletext = '가나다';
	
	$lang->edit->hyperlink = '하이퍼링크';
	$lang->edit->target_blank = '새창으로';
	
	$lang->edit->quotestyle1 = '왼쪽 실선';
	$lang->edit->quotestyle2 = '인용 부호';
	$lang->edit->quotestyle3 = '실선';
	$lang->edit->quotestyle4 = '실선 + 배경';
	$lang->edit->quotestyle5 = '굵은 실선';
	$lang->edit->quotestyle6 = '점선';
	$lang->edit->quotestyle7 = '점선 + 배경';
	$lang->edit->quotestyle8 = '적용 취소';
?>
