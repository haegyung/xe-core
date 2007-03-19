/**
 * popup으로 열렸을 경우 부모창의 위지윅에디터에 select된 block이 있는지 체크하여
 * 있으면 가져와서 원하는 곳에 삽입
 **/
function getText() {
    if(typeof(opener)=="undefined") return;
    var text = opener.editorGetSelectedHtml(opener.editorPrevSrl);
    var fo_obj = xGetElementById("fo_component");
    if(fo_obj.text.value) return;
    fo_obj.text.value = text;
    self.focus();
}

/**
 * 부모창의 위지윅에디터에 데이터를 삽입
 **/
function setText() {
    if(typeof(opener)=="undefined") return;

    var fo_obj = xGetElementById("fo_component");

    var text = fo_obj.text.value;
    var url = fo_obj.url.value;
    var open_window = false;
    var bold = false;
    var link_class = "";

    if(!text) {
        window.close();
        return;
    }

    if(fo_obj.open_window.checked) open_window = true;
    if(fo_obj.bold.checked) bold= true;
    if(xGetElementById("color_blue").checked) link_class = "editor_blue_text";
    else if(xGetElementById("color_red").checked) link_class = "editor_red_text";
    else if(xGetElementById("color_yellow").checked) link_class = "editor_yellow_text";
    else if(xGetElementById("color_green").checked) link_class = "editor_green_text";

    var link = "";
    if(open_window) {
        link = "<a href=\"#\" onclick=\"window.open('"+url+"');return false;\" ";
    } else {
        link = "<a href=\""+url+"\" ";
    }
    
    if(bold || link_class) {
        var class_name = "";
        if(bold) class_name = "bold";
        if(link_class) class_name += " "+link_class;
        link += " class=\""+class_name+"\" ";
    }

    link += ">"+text+"</a>";

    var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)
    opener.editorReplaceHTML(iframe_obj, link);

    opener.focus();
    window.close();
}

xAddEventListener(window, "load", getText);
