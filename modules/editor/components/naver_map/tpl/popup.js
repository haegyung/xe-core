/**
 * popup으로 열렸을 경우 부모창의 위지윅에디터에 select된 멀티미디어 컴포넌트 코드를 체크하여
 * 있으면 가져와서 원하는 곳에 삽입
 **/
function getNaverMap() {
    // 부모 위지윅 에디터에서 선택된 영역이 있는지 확인
    if(typeof(opener)=="undefined") return;

    var node = opener.editorPrevNode;
    if(!node || node.nodeName != "DIV") return;

    var x = node.getAttribute("x");
    var y = node.getAttribute("y");
    var width = xWidth(node);
    var height = xHeight(node);
    var address = node.getAttribute("address");
    var selected_address = node.getAttribute("selected_address");

    if(!address || !x || !y) return;
    xGetElementById("address").value = address;
    search_address(selected_address);

    xGetElementById("navermap_width").value = width-6;
    xGetElementById("navermap_height").value = height-6;
}

function insertNaverMap(obj) {
    if(typeof(opener)=="undefined") return;

    var listup_obj = xGetElementById("address_list");
    var idx = listup_obj.options[listup_obj.selectedIndex].value;
    if(!idx) {
        window.close();
        return;
    }
    
    var item = naver_address_list[idx];
    var x = item[0];
    var y = item[1];
    var selected_address = item[2];
    var address = xGetElementById("address").value;

    var width = xGetElementById("navermap_width").value;
    if(!width) width = 640;

    var height = xGetElementById("navermap_height").value;
    if(!height) height= 480;

    var text = "<div editor_component=\"naver_map\" class=\"editor_component_output\" address=\""+address+"\" selected_address=\""+selected_address+"\" x=\""+x+"\" y=\""+y+"\" width=\""+width+"\" height=\""+height+"\" style=\"width:"+width+"px;height:"+height+"px;\"></div>";

    opener.editorFocus(opener.editorPrevSrl);

    var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)

    opener.editorReplaceHTML(iframe_obj, text);
    opener.editorFocus(opener.editorPrevSrl);

    window.close();
}

xAddEventListener(window, "load", getNaverMap);

/* 네이버의 map openapi로 주소에 따른 좌표를 요청 */
function search_address(selected_address) {
  if(typeof(selected_address)=="undefined") selected_address = null;
  var address = xGetElementById("address").value;
  if(!address) return;
  var params = new Array();
  params['component'] = "naver_map";
  params['address'] = address;
  params['method'] = "search_address";

  var response_tags = new Array('error','message','address_list');
  exec_xml('editor', 'procCall', params, complete_search_address, response_tags, selected_address);
}

var naver_address_list = new Array();
function complete_search_address(ret_obj, response_tags, selected_address) {
  var address_list = ret_obj['address_list'];
  if(!address_list) return;

  naver_address_list = new Array();

  var listup_obj = xGetElementById("address_list");
  var length = listup_obj.options.length;
  for(var i=0;i<length;i++) listup_obj.remove(0);

  var address_list = address_list.split("\n");
  for(var i=0;i<address_list.length;i++) {
    var item = address_list[i].split(",");

    naver_address_list[naver_address_list.length] = item;

    var selected = false;
    if(selected_address && selected_address == item[2]) selected = true;

    var opt = new Option(item[2], naver_address_list.length-1, false, selected);
    listup_obj.options[listup_obj.length] = opt;
  }
}
