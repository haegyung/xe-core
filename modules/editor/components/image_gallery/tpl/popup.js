var selected_node = null;
function getSlideShow() {
    // 부모창이 있는지 체크 
    if(typeof(opener)=="undefined") return;

    // 부모 위지윅 에디터에서 선택된 영역이 있으면 처리
    var node = opener.editorPrevNode;
    var selected_images = "";
    if(node && node.nodeName == "DIV") {
        selected_node = node;

        var width = xWidth(selected_node)-6;
        var height = xHeight(selected_node)-6;
        var gallery_style = selected_node.getAttribute("gallery_style");
        var border_color = selected_node.getAttribute("border_color");
        var bg_color = selected_node.getAttribute("bg_color");

        xGetElementById("width").value = width; 
        xGetElementById("height").value = height; 

        if(gallery_style=="list") xGetElementById("gallery_style").selectedIndex = 1;
        else xGetElementById("gallery_style").selectedIndex = 0;

        xGetElementById("border_color_input").value = border_color; 
        manual_select_color("border", xGetElementById("border_color_input"));

        xGetElementById("bg_color_input").value = bg_color; 
        manual_select_color("bg", xGetElementById("bg_color_input"));

        selected_images = xInnerHtml(selected_node);
    }

    // 부모창의 업로드된 파일중 이미지 목록을 모두 가져와서 세팅 
    var fo = xGetElementById("fo");
    var upload_target_srl = fo.upload_target_srl.value;

    var parent_list_obj = opener.xGetElementById("uploaded_file_list_"+upload_target_srl);

    var list_obj = xGetElementById("image_list");

    for(var i=0;i<parent_list_obj.length;i++) {
        var opt = parent_list_obj.options[i];
        var file_srl = opt.value;
        var file_obj = opener.uploaded_files[file_srl];
        var filename = file_obj.uploaded_filename;
        if((/(jpg|jpeg|gif|png)$/).test(filename)) {
            var selected = false;
            if(selected_images.indexOf(filename)!=-1) selected = true;
            var opt = new Option(opt.text, opt.value, false, selected);
            list_obj.options.add(opt);
        }
    }
}

function insertSlideShow() {
    if(typeof(opener)=="undefined") return;

    var list = new Array();
    var list_obj = xGetElementById("image_list");
    for(var i=0;i<list_obj.length;i++) {
        var opt = list_obj.options[i];
        if(opt.selected) {
            var file_srl = opt.value;
            var file_obj = opener.uploaded_files[file_srl];
            var filename = file_obj.uploaded_filename;
            list[list.length] = filename;
        }
    }

    if(!list.length) {
        window.close();
        return;
    }

    var width = xGetElementById("width").value;
    var height = xGetElementById("height").value;

    var gallery_style = xGetElementById("gallery_style").options[xGetElementById("gallery_style").selectedIndex].value;
    var border_color = xGetElementById("border_color_input").value;
    var bg_color = xGetElementById("bg_color_input").value;

    var images_list = "";
    for(var i=0; i<list.length;i++) {
        images_list += list[i]+"\n";
    }

    if(selected_node) {
        selected_node.setAttribute("width", width);
        selected_node.setAttribute("height", height);
        selected_node.setAttribute("gallery_style", gallery_style);
        selected_node.setAttribute("border_color", border_color);
        selected_node.setAttribute("bg_color", bg_color);
        selected_node.style.width = width+"px";
        selected_node.style.height = height+"px";
        xInnerHtml(selected_node, images_list);
    } else {
        var text = "<div editor_component=\"image_gallery\" class=\"editor_component_output\" width=\""+width+"\" height=\""+height+"\" gallery_style=\"+gallery_style+\" border_color=\""+border_color+"\" bg_color=\""+bg_color+"\" style=\"width:"+width+"px;height:"+height+"px;\" >"+images_list+"</div>";
        opener.editorFocus(opener.editorPrevSrl);
        var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)
        opener.editorReplaceHTML(iframe_obj, text);
    }

    opener.editorFocus(opener.editorPrevSrl);

    window.close();
}

/* 색상 클릭시 */
function select_color(type, code) {
  xGetElementById(type+"_preview_color").style.backgroundColor = "#"+code;
  xGetElementById(type+"_color_input").value = code;
}

/* 수동 색상 변경시 */
function manual_select_color(type, obj) {
  if(obj.value.length!=6) return;
  code = obj.value;
  xGetElementById(type+"_preview_color").style.backgroundColor = "#"+code;
}

/* 색상표를 출력 */
function printColor(type, blank_img_src) {
  var colorTable = new Array('22','44','66','88','AA','CC','EE');
  var html = "";

  for(var i=0;i<8;i+=1) html += printColorBlock(type, i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16), blank_img_src);

  for(var i=0; i<colorTable.length; i+=3) {
    for(var j=0; j<colorTable.length; j+=2) {
      for(var k=0; k<colorTable.length; k++) {
        var code = colorTable[i] + colorTable[j] + colorTable[k];
        html += printColorBlock(type, code, blank_img_src);
      }
    }
  }

  for(var i=8;i<16;i+=1) html += printColorBlock(type, i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16), blank_img_src);

  document.write(html);
}

/* 개별 색상 block 출력 함수 */
function printColorBlock(type, code, blank_img_src) {
  if(type=="bg") {
    return "<div style=\"float:left;background-color:#"+code+"\"><img src=\""+blank_img_src+"\" class=\"color_icon\" onmouseover=\"this.className='color_icon_over'\" onmouseout=\"this.className='color_icon'\" onclick=\"select_color('"+type+"','"+code+"')\" alt=\"color\" \/><\/div>";
  } else {
    return "<div style=\"float:left;background-color:#"+code+"\"><img src=\""+blank_img_src+"\" class=\"color_icon\" onmouseover=\"this.className='color_icon_over'\" onmouseout=\"this.className='color_icon'\" onclick=\"select_color('"+type+"','"+code+"')\" alt=\"color\" \/><\/div>";
  }
}

xAddEventListener(window, "load", getSlideShow);
