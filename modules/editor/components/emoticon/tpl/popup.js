function insertEmoticon(obj) {
    if(typeof(opener)=='undefined') return;

    var url = obj.src.replace(request_uri,'');
    var text = "<img src=\""+url+"\" border=\"0\" alt=\"emoticon\" />";

    opener.editorFocus(opener.editorPrevSrl);

    var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)

    opener.editorReplaceHTML(iframe_obj, text);
    opener.editorFocus(opener.editorPrevSrl);

    window.close();
}
