<?php
    /**
     * @file   modules/editor/lang/en.lang.php
     * @author zero <zero@nzeo.com>
     * @brief  WYSIWYG Editor module's basic language pack
     **/

    $lang->editor = 'WYSIWYG Editor';
    $lang->component_name = 'Component';
    $lang->component_version = 'Version';
    $lang->component_author = 'Developer';
    $lang->component_link = 'Link';
    $lang->component_date = 'Date';
    $lang->component_license = 'License';
    $lang->component_history = 'Updates';
    $lang->component_description = 'Description';
    $lang->component_extra_vars = 'Option Variable';
    $lang->component_grant = 'Permission Setting';
    $lang->content_style = 'Content Style';
    $lang->content_font = 'Content Font';

    $lang->about_component = 'About component';
    $lang->about_component_grant = 'Selected group(s) will be able to use expanded components of editor.<br />(Leave them blank if you want all groups to have permission)';
    $lang->about_component_mid = 'Editor components can select targets.<br />(All targets will be selected when nothing is selected)';

    $lang->msg_component_is_not_founded = 'Cannot find editor component %s';
    $lang->msg_component_is_inserted = 'Selected component is already inserted';
    $lang->msg_component_is_first_order = 'Selected component is located at the first position';
    $lang->msg_component_is_last_order = 'Selected component is located at the last position';
    $lang->msg_load_saved_doc = "There is an automatically saved article. Do you wish to recover it?\nThe auto-saved draft will be discarded after saving current article";
    $lang->msg_auto_saved = 'Automatically Saved';

    $lang->cmd_disable = 'Inactive';
    $lang->cmd_enable = 'Active';

    $lang->editor_skin = 'Editor Skin';
    $lang->upload_file_grant = 'Permission for Uploading';
    $lang->enable_default_component_grant = 'Permission for Default Components';
    $lang->enable_component_grant = 'Permission for Components';
    $lang->enable_html_grant = 'Permission for HTML';
    $lang->enable_autosave = 'Auto-Save';
    $lang->height_resizable = 'Height Resizable';
    $lang->editor_height = 'Height of Editor';

    $lang->about_editor_skin = 'You may select the skin of editor.';
    $lang->about_content_style = '문서 편집 및 내용 출력시 원하는 서식을 지정할 수 있습니다';
    $lang->about_content_font = '문서 편집 및 내용 출력시 원하는 폰트를 지정할 수 있습니다.<br/>지정하지 않으면 사용자 설정에 따르게 됩니다<br/> ,(콤마)로 여러 폰트를 지정할 수 있습니다.';
    $lang->about_upload_file_grant = 'Selected group(s) will be able to upload files. (Leave them blank if you want all groups to have permission)';
    $lang->about_default_component_grant = 'Selected group(s) will be able to use default components of editor. (Leave them blank if you want all groups to have permission)';
    $lang->about_editor_height = 'You may set the height of editor.';
    $lang->about_editor_height_resizable = 'You may decide whether height of editor can be resized.';
    $lang->about_enable_html_grant = 'Selected group(s) will be able to use HTML';
    $lang->about_enable_autosave = 'You may decide whether auto-save function will be used.';

    $lang->edit->fontname = 'Font';
    $lang->edit->fontsize = 'Size';
    $lang->edit->use_paragraph = 'Paragraph Function';
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

    $lang->edit->header = 'Style';
    $lang->edit->header_list = array(
    'h1' => 'Subject 1',
    'h2' => 'Subject 2',
    'h3' => 'Subject 3',
    'h4' => 'Subject 4',
    'h5' => 'Subject 5',
    'h6' => 'Subject 6',
    );

    $lang->edit->submit = 'Submit';

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

    $lang->edit->help_remove_format = 'Tags in selected area will be removed';
    $lang->edit->help_strike_through = 'Strike will be on the words';
    $lang->edit->help_align_full = 'Align left and right';

    $lang->edit->help_fontcolor = 'Select font color';
    $lang->edit->help_fontbgcolor = 'Select background color of font';
    $lang->edit->help_bold = 'Make font bold';
    $lang->edit->help_italic = 'Make italic font';
    $lang->edit->help_underline = 'Underline font';
    $lang->edit->help_strike = 'Strike font';
    $lang->edit->help_sup = 'Superscript';
    $lang->edit->help_sub = 'Subscript';
    $lang->edit->help_redo = 'Redo';
    $lang->edit->help_undo = 'Undo';
    $lang->edit->help_align_left = 'Align left';
    $lang->edit->help_align_center = 'Align center';
    $lang->edit->help_align_right = 'Align right';
    $lang->edit->help_add_indent = 'Add indent';
    $lang->edit->help_remove_indent = 'Remove indent';
    $lang->edit->help_list_number = 'Apply number list';
    $lang->edit->help_list_bullet = 'Apply bullet list';
    $lang->edit->help_use_paragraph = 'Press Ctrl+Enter to use paragraph. (Press Alt+S to submit)';

    $lang->edit->url = 'URL';
    $lang->edit->blockquote = 'Blockquote';
    $lang->edit->table = 'Table';
    $lang->edit->image = 'Image';
    $lang->edit->multimedia = 'Movie';
    $lang->edit->emoticon = 'Emoticon';

    $lang->edit->upload = 'Attachment';
    $lang->edit->upload_file = 'Attach';
    $lang->edit->link_file = 'Insert to Content';
    $lang->edit->delete_selected = 'Delete Selected';

    $lang->edit->icon_align_article = 'Occupy a paragraph';
    $lang->edit->icon_align_left = 'Align Left';
    $lang->edit->icon_align_middle = 'Align Center';
    $lang->edit->icon_align_right = 'Align Right';

    $lang->about_dblclick_in_editor = 'You may set detail component configures by double-clicking background, text, images, or quotations';


    $lang->edit->rich_editor = 'Rich Text Editor';
    $lang->edit->html_editor = 'HTML Editor';
    $lang->edit->extension ='Extension Components';
    $lang->edit->help = 'Help';
    $lang->edit->help_command = 'Help Hotkeys';
    
    $lang->edit->lineheight = 'Line Height';
	$lang->edit->fontbgsampletext = 'ABC';
	
	$lang->edit->hyperlink = 'Hyperlink';
	$lang->edit->target_blank = 'New Window';
	
	$lang->edit->quotestyle1 = '왼쪽 실선';
	$lang->edit->quotestyle2 = '인용부호';
	$lang->edit->quotestyle3 = '실선';
	$lang->edit->quotestyle4 = '실선 + 배경';
	$lang->edit->quotestyle5 = '굵은 실선';
	$lang->edit->quotestyle6 = '점선';
	$lang->edit->quotestyle7 = '점선 + 배경';
	$lang->edit->quotestyle8 = '적용 취소';


    $lang->edit->jumptoedit = '편집 도구모음 건너뛰기';
    $lang->edit->set_sel = '칸 수 지정';
    $lang->edit->row = '행';
    $lang->edit->col = '열';
    $lang->edit->add_one_row = '1행추가';
    $lang->edit->del_one_row = '1행삭제';
    $lang->edit->add_one_col = '1열추가';
    $lang->edit->del_one_col = '1열삭제';

    $lang->edit->table_config = '표 속성 지정';
    $lang->edit->border_width = '테두리 굵기';
    $lang->edit->border_color = '테두리 색';
    $lang->edit->add = '더하기';
    $lang->edit->del = '빼기';
    $lang->edit->search_color = '색상찾기';
    $lang->edit->table_backgroundcolor = '표 배경색';
    $lang->edit->special_character = '특수문자';
    $lang->edit->insert_special_character = '특수문자 삽입';
    $lang->edit->close_special_character = '특수문자 레이어 닫기';
    $lang->edit->symbol = '일반기호';
    $lang->edit->number_unit = '숫자와 단위';
    $lang->edit->circle_bracket = '원,괄호';
    $lang->edit->korean = 'Korean';
    $lang->edit->greece = 'Greek';
    $lang->edit->Latin  = 'Latin';
    $lang->edit->japan  = 'Japanese';
    $lang->edit->selected_symbol  = '선택한 기호';

    $lang->edit->search_replace  = 'Find/Replace';
    $lang->edit->close_search_replace  = '찾기/바꾸기 레이어 닫기';
    $lang->edit->replace_all  = 'Replace All';
    $lang->edit->search_words  = '찾을단어';
    $lang->edit->replace_words  = '바꿀단어';
    $lang->edit->next_search_words  = '다음찾기';
    $lang->edit->edit_height_control  = '입력창 크기 조절';

    $lang->edit->merge_cells = 'Merge Table Cells';
    $lang->edit->split_row = '행 분할';
    $lang->edit->split_col = '열 분할';
    
    $lang->edit->toggle_list   = '목록 접기/펼치기';
    $lang->edit->minimize_list = '최소화';
    
    $lang->edit->move = '이동';
    $lang->edit->materials = '글감보관함';
    $lang->edit->temporary_savings = '임시저장목록';
    
    $lang->edit->drag_here = '아래의 단락추가 툴바에서 원하는 유형의 단락을 추가해 글 쓰기를 시작하세요.<br />글감 보관함에 글이 있으면 이곳으로 끌어 넣기 할 수 있습니다.';

	$lang->edit->paging_prev = '이전';
	$lang->edit->paging_next = '다음';
	$lang->edit->paging_prev_help = '이전 페이지로 이동합니다.';
	$lang->edit->paging_next_help = '다음 페이지로 이동합니다.';
?>
