/**
 * @file tree_menu.js
 * @author zero (zero@nzeo.com)
 * @brief xml파일을 읽어서 트리 메뉴를 그려줌
 *
 * 일단 이것 저것 꽁수가 좀 들어간 것이긴 한데 속도나 기타 면에서 쓸만함...
 * 언제나 그렇듯 필요하신 분은 가져가서 쓰세요.
 * 다만 제로보드에 좀 특화되어 있어서....
 **/

// 트리메뉴에서 사용될 아이콘의 위치
var tree_menu_icon_path = "./common/tpl/images/";

// 아이콘을 미리 생성해 놓음
var tree_folder_icon = new Image();
tree_folder_icon.src = tree_menu_icon_path+"page.gif";
var tree_open_folder_icon = new Image();
tree_open_folder_icon.src = tree_menu_icon_path+"page.gif";

var tree_minus_icon = new Image();
tree_minus_icon.src = tree_menu_icon_path+"minus.gif";
var tree_minus_bottom_icon = new Image();
tree_minus_bottom_icon.src = tree_menu_icon_path+"minusbottom.gif";
var tree_plus_icon = new Image();
tree_plus_icon.src = tree_menu_icon_path+"plus.gif";
var tree_plus_bottom_icon = new Image();
tree_plus_bottom_icon.src = tree_menu_icon_path+"plusbottom.gif";

// 폴더를 모두 열고/닫기 위한 변수 설정
var tree_menu_folder_list = new Array();

// 노드의 정보를 가지고 있을 변수
var node_info_list = new Array();

// menu_id별로 요청된 callback_func
var node_callback_func = new Array();

// 트리메뉴의 정보를 담고 있는 xml파일을 읽고 drawTreeMenu()를 호출하는 함수
function loadTreeMenu(url, menu_id, zone_id, title, callback_func, manual_select_node_srl) {
    // 일단 그릴 곳을 찾아서 사전 작업을 함 (그릴 곳이 없다면 아예 시도를 안함)
    var zone = xGetElementById(zone_id);
    if(typeof(zone)=="undefined") return;

    // xml_handler를 이용해서 직접 메뉴 xml파일(layout module에서 생성)을 읽음
    var oXml = new xml_handler();
    oXml.reset();
    oXml.xml_path = url;

    // 사용자 정의 함수가 없다면 moveTreeMenu()라는 기본적인 동작을 하는 함수를 대입
    if(typeof(callback_func)=='undefined') {
        callback_func = moveTreeMenu;
    }

    // 한 페이지에 다수의 menu_id가 있을 수 있으므로 menu_id별로 함수를 저장
    node_callback_func[menu_id] = callback_func;

    // 직접 선택시키려는 메뉴 인자값이 없으면 초기화
    if(typeof(manual_select_node_srl)=='undefined') manual_select_node_srl = '';

    // menu_id, zone_id는 계속 달고 다녀야함 
    var param = {menu_id:menu_id, zone_id:zone_id, title:title, manual_select_node_srl:manual_select_node_srl}

    // 요청후 drawTreeMenu()함수를 호출 (xml_handler.js에서 request method를 직접 이용)
    oXml.request(drawTreeMenu, oXml, null, null, null, param);
}

// 트리메뉴 XML정보를 이용해서 정해진 zone에 출력
var manual_select_node_srl = '';
function drawTreeMenu(oXml, callback_func, resopnse_tags, null_func, param) {
    var xmlDoc = oXml.getResponseXml();
    if(!xmlDoc) return null;

    // 그리기 위한 object를 찾아 놓음
    var menu_id = param.menu_id;
    var zone_id = param.zone_id;
    var title = param.title;
    if(param.manual_select_node_srl) manual_select_node_srl = param.manual_select_node_srl;
    var zone = xGetElementById(zone_id);
    var html = "";

    if(title) html = '<div style="height:20px;"><img src="'+tree_menu_icon_path+'folder.gif" alt="root" align="top" />'+title+'</div>';

    tree_menu_folder_list[menu_id] = new Array();

    // node 태그에 해당하는 값들을 가져와서 html을 작성
    var node_list = xmlDoc.getElementsByTagName("node");
    if(node_list.length>0) {
        var root = xmlDoc.getElementsByTagName("root")[0];
        var output = drawNode(root, menu_id);
        html += output.html;
    }

    // 출력하려는 zone이 없다면 load후에 출력하도록 함
    if(!zone) {
        xAddEventListener(window, 'load', function() { drawTeeMenu(zone_id, menu_id, html); });

    // 출력하려는 zone을 찾아졌다면 바로 출력
    } else {
        xInnerHtml(zone, html);
        if(manual_select_node_srl) manualSelectNode(menu_id, manual_select_node_srl);
    }

}

// 페이지 랜더링 중에 메뉴의 html이 완성되었을때 window.onload event 후에 그리기 재시도를 하게 될 함수
function drawTeeMenu(zone_id, menu_id, html) {
    xInnerHtml(zone_id, html);
    if(manual_select_node_srl) manualSelectNode(menu_id, manual_select_node_srl);
}

// root부터 시작해서 recursive하게 노드를 표혐
function drawNode(parent_node, menu_id) {
    var output = {html:"", expand:"N"}

    for (var i=0; i< parent_node.childNodes.length; i++) {
        var html = "";

        // nodeName이 node가 아니면 패스~
        var node = parent_node.childNodes.item(i);
        if(node.nodeName!="node") continue;

        // node의 기본 변수들 체크 
        var node_srl = node.getAttribute("node_srl");
        var text = node.getAttribute("text");
        var url = node.getAttribute("url");
        var expand = node.getAttribute("expand");

        // 자식 노드가 있는지 확인
        var hasChild = false;
        if(node.hasChildNodes()) hasChild = true;

        // nextSibling가 있는지 확인 
        var hasNextSibling = false;
        if(i==parent_node.childNodes.length-1) hasNextSibling = true;

        // 후에 사용하기 위해 node_info_list에 node_srl을 값으로 하여 node object 추가
        node_info_list[node_srl] = node;

        // zone_id 값을 세팅
        var zone_id = "menu_"+menu_id+"_"+node_srl;
        tree_menu_folder_list[menu_id][tree_menu_folder_list[menu_id].length] = zone_id;

        // url을 확인하여 현재의 url과 동일하다고 판단되면 manual_select_node_srl 에 값을 추가 (관리자페이지일 경우는 무시함)
        if(node_callback_func[menu_id] == moveTreeMenu && url && typeof(zbfe_url)!="undefined" && zbfe_url == url) manual_select_node_srl = node_srl;

        // manual_select_node_srl이 node_srl과 같으면 펼침으로 처리
        if(manual_select_node_srl == node_srl) expand = "Y";

        // 아이콘 설정
        var line_icon = null;
        var folder_icon = null;

        // 자식 노드가 있을 경우 자식 노드의 html을 구해옴
        var child_output = null;
        var child_html = "";
        if(hasChild) {
            // 자식 노드의 zone id를 세팅
            var child_zone_id = zone_id+"_child";
            tree_menu_folder_list[menu_id][tree_menu_folder_list[menu_id].length] = child_zone_id;
            
            // html을 받아옴 
            child_output = drawNode(node, menu_id);
            var chtml = child_output.html;
            var cexpand = child_output.expand;
            if(cexpand == "Y") expand = "Y";

            // 무조건 펼침이 아닐 경우
            if(expand!="Y") {
                if(!hasNextSibling) child_html += '<div id="'+child_zone_id+'"style="display:none;padding-left:18px;background:url('+tree_menu_icon_path+'line.gif) repeat-y left;">'+chtml+'</div>';
                else child_html += '<div id="'+child_zone_id+'" style="display:none;padding-left:18px;">'+chtml+'</div>';
            // 무조건 펼침일 경우
            } else {
                if(!hasNextSibling) child_html += '<div id="'+child_zone_id+'"style="display:block;padding-left:18px;background:url('+tree_menu_icon_path+'line.gif) repeat-y left;">'+chtml+'</div>';
                else child_html += '<div id="'+child_zone_id+'" style="display:block;padding-left:18px;">'+chtml+'</div>';
            }
        }

        // 자식 노드가 있는지 확인하여 있으면 아이콘을 바꿈
        if(hasChild) {
            // 무조건 펼침이 아닐 경우
            if(expand != "Y") {
                if(!hasNextSibling) {
                    line_icon = "minus";
                    folder_icon = "page";
                } else {
                    line_icon = "minusbottom";
                    folder_icon = "page";
                }
            // 무조건 펼침일 경우 
            } else {
                if(!hasNextSibling) {
                    line_icon = "plus";
                    folder_icon = "page";
                } else {
                    line_icon = "plusbottom";
                    folder_icon = "page";
                }
            }

        // 자식 노드가 없을 경우
        } else {
            if(hasNextSibling) {
                line_icon = "joinbottom";
                folder_icon = "page";
            } else {
                line_icon = "join";
                folder_icon = "page";
            }
        }


        // html 작성
        html += '<div id="'+zone_id+'" style="margin:0px;font-size:9pt;">';

        if(hasChild) html+= '<span style="cursor:pointer;" onclick="toggleFolder(\''+zone_id+'\');return false;">';
        else html+= '<span>';

        html += ''+
                '<img id="'+zone_id+'_line_icon" src="'+tree_menu_icon_path+line_icon+'.gif" alt="line" align="top" />'+
                '<img id="'+zone_id+'_folder_icon" src="'+tree_menu_icon_path+folder_icon+'.gif" alt="folder" align="top" />'+
                '</span>'+
                '<span id="'+zone_id+'_node" style="padding:1px 2px 1px 2px;margin-top:1px;cursor:pointer;" onclick="selectNode(\''+menu_id+'\','+node_srl+',\''+zone_id+'\')" ondblclick="toggleFolder(\''+zone_id+'\');return false;">'+
                text+
                '</span>'+
                '';

        html += child_html;

        html += '</div>';

        output.html += html;

        if(expand=="Y") output.expand = "Y";
    }
    return output;
}

// 수동으로 메뉴를 선택하도록 함
function manualSelectNode(menu_id, node_srl) {
    var zone_id = "menu_"+menu_id+"_"+node_srl;
    selectNode(menu_id,node_srl,zone_id,false);
    return;
}

// 노드의 폴더 아이콘 클릭시
function toggleFolder(zone_id) {
    // 아이콘을 클릭한 대상을 찾아봄
    var child_zone = xGetElementById(zone_id+"_child");
    if(!child_zone) return;

    // 대상의 아이콘들 찾음 
    var line_icon = xGetElementById(zone_id+'_line_icon');
    var folder_icon = xGetElementById(zone_id+'_folder_icon');

    // 대상의 자식 노드들이 숨겨져 있다면 열고 아니면 닫기
    if(child_zone.style.display == "block") {
        child_zone.style.display = "none";
        if(line_icon.src.indexOf('bottom')>0) line_icon.src = tree_minus_bottom_icon.src;
        else line_icon.src = tree_minus_icon.src;

        folder_icon.src = tree_folder_icon.src;
    } else {
        if(line_icon.src.indexOf('bottom')>0) line_icon.src = tree_plus_bottom_icon.src;
        else line_icon.src = tree_plus_icon.src;
        folder_icon.src = tree_open_folder_icon.src;
        child_zone.style.display = "block";
    }
}

// 노드의 글자 선택시
var prev_selected_node = null;
function selectNode(menu_id, node_srl, zone_id, move_url) {
    // 이전에 선택된 노드가 있었다면 원래데로 돌림
    if(prev_selected_node) {
        prev_selected_node.style.backgroundColor = "#ffffff";
        prev_selected_node.style.fontWeight = "normal";
        prev_selected_node.style.color = "#000000";
    }

    // 선택된 노드를 찾아봄
    var node_zone = xGetElementById(zone_id+'_node');
    if(!node_zone) return;

    // 선택된 노드의 글자를 변경
    node_zone.style.backgroundColor = "#0e078f";
    node_zone.style.fontWeight = "bold";
    node_zone.style.color = "#FFFFFF";
    prev_selected_node = node_zone;

    // 함수 실행
    if(typeof(move_url)=="undefined"||move_url==true) {
        var func = node_callback_func[menu_id];
        func(menu_id, node_info_list[node_srl]);
        toggleFolder(zone_id);
    }
}

// 선택된 노드의 표시를 없앰
function deSelectNode() {
    // 이전에 선택된 노드가 있었다면 원래데로 돌림
    if(!prev_selected_node) return;
    prev_selected_node.style.backgroundColor = "#ffffff";
    prev_selected_node.style.fontWeight = "normal";
    prev_selected_node.style.color = "#000000";
}


// 모두 닫기
function closeAllTreeMenu(menu_id) {
    for(var i in tree_menu_folder_list[menu_id]) {
        var zone_id = tree_menu_folder_list[menu_id][i];
        var zone = xGetElementById(zone_id);
        if(!zone) continue;
        var child_zone = xGetElementById(zone_id+"_child");
        if(!child_zone) continue;

        child_zone.style.display = "block";
        toggleFolder(zone_id);
    }
}

// 모두 열기
function openAllTreeMenu(menu_id) {
    for(var i in tree_menu_folder_list[menu_id]) {
        var zone_id = tree_menu_folder_list[menu_id][i];
        var zone = xGetElementById(zone_id);
        if(!zone) continue;
        var child_zone = xGetElementById(zone_id+"_child");
        if(!child_zone) continue;

        child_zone.style.display = "none";
        toggleFolder(zone_id);
    }
}

// 메뉴 클릭시 기본으로 동작할 함수 (사용자 임의 함수로 대체될 수 있음)
function moveTreeMenu(menu_id, node) {
    // url과 open_window값을 구함
    var node_srl = node.getAttribute("node_srl");
    var url = node.getAttribute("url");
    var open_window = node.getAttribute("open_window");
    var hasChild = false;
    if(node.hasChildNodes()) hasChild = true;

    // url이 없고 child가 있으면 해당 폴더 토글한다 
    if(!url && hasChild) {
        var zone_id = "menu_"+menu_id+"_"+node_srl;
        toggleFolder(zone_id);
        return;
    }

    // url이 있으면 url을 분석한다 (제로보드 특화된 부분. url이 http나 ftp등으로 시작하면 그냥 해당 url 열기)
    if(url) {
        // http, ftp등의 연결이 아닌 경우 제로보드용으로 처리
        if(url.indexOf('://')==-1) {
            url = "./?"+url;
        }

        // open_window에 따라서 처리
        if(open_window != "Y") location.href=url;
        else {
            var win = window.open(url);
            win.focus();
        }
    }
}

