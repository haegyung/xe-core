/**
 * @file common.js
 * @author zero (zero@nzeo.com)
 * @brief 몇가지 유용한 & 기본적으로 자주 사용되는 자바스크립트 함수들 모음
 **/

/**
 * @brief location.href에서 특정 key의 값을 return
 **/
String.prototype.getQuery = function(key) {
    var idx = this.indexOf('?');
    if(idx == -1) return null;
    var query_string = this.substr(idx+1, this.length);
    var args = {}
    query_string.replace(/([^=]+)=([^&]*)(&|$)/g, function() { args[arguments[1]] = arguments[2]; });

    var q = args[key];
    if(typeof(q)=="undefined") q = "";

    return q;
}

/**
 * @brief location.href에서 특정 key의 값을 return
 **/
String.prototype.setQuery = function(key, val) {
    var idx = this.indexOf('?');
    var uri = this;
    uri = uri.replace(/#$/,'');
    if(idx != -1) {
        uri = this.substr(0, idx);
        var query_string = this.substr(idx+1, this.length);
        var args = new Array();
        query_string.replace(/([^=]+)=([^&]*)(&|$)/g, function() { args[arguments[1]] = arguments[2]; });

        args[key] = val;

        var q_list = new Array();
        for(var i in args) {
            var arg = args[i];
            if(!arg.toString().trim()) continue;

            q_list[q_list.length] = i+'='+arg;
        }
        return uri+"?"+q_list.join("&");
    } else {
        if(val.toString().trim()) return uri+"?"+key+"="+val;
        else return uri;
    }
}

/**
 * @brief string prototype으로 trim 함수 추가
 **/
String.prototype.trim = function() {
    return this.replace(/(^\s*)|(\s*$)/g, "");
}

/**
 * @brief 주어진 인자가 하나라도 defined되어 있지 않으면 false return
 **/
function isDef() {
    for(var i=0; i<arguments.length; ++i) {
        if(typeof(arguments[i])=="undefined") return false;
    }
    return true;
}

/**
 * @brief 윈도우 오픈
 * 열려진 윈도우의 관리를 통해 window.focus()등을 FF에서도 비슷하게 구현함
 **/
var winopen_list = new Array();
function winopen(url, target, attribute) {
    try {
        if(target != "_blank" && winopen_list[target]) {
            winopen_list[target].close();
            winopen_list[target] = null;
        }
    } catch(e) {
    }

    if(typeof(target)=='undefined') target = '_blank';
    if(typeof(attribute)=='undefined') attribute = '';
    var win = window.open(url, target, attribute);
    win.focus();
    if(target != "_blank") winopen_list[target] = win;
}

/**
 * @brief 팝업으로만 띄우기 
 * common/tpl/popup_layout.html이 요청되는 제로보드 XE내의 팝업일 경우에 사용
 **/
function popopen(url, target) {
    if(typeof(target)=="undefined") target = "_blank";
    winopen(url, target, "left=10,top=10,width=10,height=10,scrollbars=no,resizable=no,toolbars=no");
}

/**
 * @brief 메일 보내기용
 **/
function sendMailTo(to) {
    location.href="mailto:"+to;
}

/**
 * @brief url이동 (open_window 값이 N 가 아니면 새창으로 띄움)
 **/
function move_url(url, open_wnidow) {
    if(!url) return false;
    if(typeof(open_wnidow)=='undefined') open_wnidow = 'N';
    if(open_wnidow=='N') open_wnidow = false;
    else open_wnidow = true;

    if(/^\./.test(url)) url = request_uri+url;

    if(open_wnidow) {
        winopen(url);
    } else {
        location.href=url;
    }
    return false;
}

/**
 * @brief 특정 div(or span...)의 display옵션 토글
 **/
function toggleDisplay(obj, opt) {
    obj = xGetElementById(obj);
    if(typeof(opt)=="undefined") opt = "inline";
    if(obj.style.display == "none") obj.style.display = opt;
    else obj.style.display = "none";
}

/**
 * @brief 멀티미디어 출력용 (IE에서 플래쉬/동영상 주변에 점선 생김 방지용)
 **/
function displayMultimedia(src, width, height, auto_start) {
    if(src.indexOf('files')==0) src = request_uri+src;
    if(auto_start) auto_start = "true";
    else auto_start = "false";

    var clsid = "";
    var codebase = "";
    var html = "";

    if(/\.swf/i.test(src)) {
        clsid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"; 
        codebase = "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0";
        html = ""+
            "<object classid=\""+clsid+"\" codebase=\""+codebase+"\" width=\""+width+"\" height=\""+height+"\" >"+
            "<param name=\"wmode\" value=\"transparent\" />"+
            "<param name=\"allowScriptAccess\" value=\"always\" />"+
            "<param name=\"movie\" value=\""+src+"\" />"+
            "<param name=\"quality\" value=\"high\" />"+
            "<embed src=\""+src+"\" autostart=\""+auto_start+"\"  width=\""+width+"\" height=\""+height+"\"></embed>"+
            "<\/object>";
    } else if(/\.flv/i.test(src)) {
        html = "<embed src=\""+request_uri+"common/tpl/images/flvplayer.swf?autoStart="+auto_start+"&file="+src+"\" width=\""+width+"\" height=\""+height+"\" type=\"application/x-shockwave-flash\"></embed>";
    } else {
        html = "<embed src=\""+src+"\" autostart=\""+auto_start+"\" width=\""+width+"\" height=\""+height+"\"></embed>";
    }

    document.writeln(html);
}

/**
 * @brief 화면내에서 상위 영역보다 이미지가 크면 리사이즈를 하고 클릭시 원본을 보여줄수 있도록 변경
 **/
function resizeImageContents() {
    var objs = xGetElementsByTagName("IMG");
    for(var i in objs) {
        var obj = objs[i];
        var parent = obj.parentNode;
        if(!obj||!parent) continue;
        while(parent.parentNode && parent.nodeName != "TD" && parent.nodeName != "DIV") {
            parent = parent.parentNode;
        }
        if(parent.nodeName != "TD" && parent.nodeName != "DIV") continue;

        if(/\/modules\//i.test(obj.src)) continue;
        if(/\/common\/tpl\//i.test(obj.src)) continue;
        if(/\/member_extra_info\//i.test(obj.src)) continue;

        var parent_width = xWidth(parent);
        var obj_width = xWidth(obj);
        var orig_img = new Image();
        orig_img.src = obj.src;

        if(parent_width<0 || obj_width < 0) continue;
        if(parent_width > obj_width && orig_img.width <= obj_width) continue;

        obj.style.cursor = "pointer";

        obj.source_width = orig_img.width;
        obj.source_height = orig_img.height;

        if(obj_width >= parent_width) {
            var per = parent_width/obj_width;
            xWidth(obj, xWidth(parent)-1);
            xHeight(obj, xHeight(obj)*per);
        }

        xAddEventListener(obj,"click", showOriginalImage);
    }
}
xAddEventListener(window, "load", resizeImageContents);

/**
 * @brief 에디터에서 사용되는 내용 여닫는 코드 (고정, zbxe용)
 **/
function zbxe_folder_open(id) {
    var open_text_obj = xGetElementById("folder_open_"+id);
    var close_text_obj = xGetElementById("folder_close_"+id);
    var folder_obj = xGetElementById("folder_"+id);
    open_text_obj.style.display = "none";
    close_text_obj.style.display = "block";
    folder_obj.style.display = "block";
}

function zbxe_folder_close(id) {
    var open_text_obj = xGetElementById("folder_open_"+id);
    var close_text_obj = xGetElementById("folder_close_"+id);
    var folder_obj = xGetElementById("folder_"+id);
    open_text_obj.style.display = "block";
    close_text_obj.style.display = "none";
    folder_obj.style.display = "none";
}


/**
 * @brief 에디터에서 사용하되 내용 여닫는 코드 (zb5beta beta 호환용으로 남겨 놓음)
 **/
function svc_folder_open(id) {
    var open_text_obj = xGetElementById("_folder_open_"+id);
    var close_text_obj = xGetElementById("_folder_close_"+id);
    var folder_obj = xGetElementById("_folder_"+id);
    open_text_obj.style.display = "none";
    close_text_obj.style.display = "block";
    folder_obj.style.display = "block";
}

function svc_folder_close(id) {
    var open_text_obj = xGetElementById("_folder_open_"+id);
    var close_text_obj = xGetElementById("_folder_close_"+id);
    var folder_obj = xGetElementById("_folder_"+id);
    open_text_obj.style.display = "block";
    close_text_obj.style.display = "none";
    folder_obj.style.display = "none";
}

/**
 * @brief 팝업의 경우 내용에 맞춰 현 윈도우의 크기를 조절해줌 
 * 팝업의 내용에 맞게 크기를 늘리는 것은... 쉽게 되지는 않음.. ㅡ.ㅜ
 * popup_layout 에서 window.onload 시 자동 요청됨.
 **/
function setFixedPopupSize() {

    if(xGetElementById('popBody')) {
        if(xHeight('popBody')>600) {
            xGetElementById('popBody').style.overflowY = 'scroll';
            xGetElementById('popBody').style.overflowX = 'hidden';
            xHeight('popBody', 600);
        }
    }

    var w = xWidth("popup_content");
    var h = xHeight("popup_content");

    var obj_list = xGetElementsByTagName('div');
    for(i=0;i<obj_list.length;i++) {
        var ww = xWidth(obj_list[i]);
        var id = obj_list[i].id;
        if(id == 'waitingforserverresponse' || id == 'fororiginalimagearea' || id == 'fororiginalimageareabg') continue;
        if(ww>w) w = ww;
    }

    // 윈도우에서는 브라우저 상관없이 가로 픽셀이 조금 더 늘어나야 한다.
    if(xUA.indexOf('windows')>0) {
        if(xOp7Up) w += 10;
        else if(xIE4Up) w += 10;
        else w += 6;
    }
    window.resizeTo(w,h);
   
    var h1 = xHeight(window.document.body);
    window.resizeBy(0,h-h1);

    window.scrollTo(0,0);
}

/**
 * @brief 본문내에서 컨텐츠 영역보다 큰 이미지의 경우 원본 크기를 보여줌
 **/
function showOriginalImage(evt) {
    var e = new xEvent(evt);
    var obj = e.target;
    var src = obj.src;

    var orig_image = xGetElementById("fororiginalimage");
    var tmp_image = new Image();
    tmp_image.src = src;
    var image_width = tmp_image.width;
    var image_height = tmp_image.height;

    orig_image.style.margin = "0px 0px 0px 0px";
    orig_image.style.cursor = "move";
    orig_image.src = src;

    var areabg = xGetElementById("fororiginalimageareabg");
    xWidth(areabg, image_width+36);
    xHeight(areabg, image_height+46);

    var area = xGetElementById("fororiginalimagearea");
    xLeft(area, xScrollLeft());
    xTop(area, xScrollTop());
    xWidth(area, xWidth(document));
    xHeight(area, xHeight(document));
    area.style.visibility = "visible";
    var area_width = xWidth(area);
    var area_height = xHeight(area);

    var x = parseInt((area_width-image_width)/2,10);
    var y = parseInt((area_height-image_height)/2,10);
    if(x<0) x = 0;
    if(y<0) y = 0;
    xLeft(areabg, x);
    xTop(areabg, y);

    var sel_list = xGetElementsByTagName("select");
    for (var i = 0; i < sel_list.length; ++i) sel_list[i].style.visibility = "hidden";

    xAddEventListener(orig_image, "mousedown", origImageDragEnable);
    xAddEventListener(orig_image, "dblclick", closeOriginalImage);
    xAddEventListener(window, "scroll", closeOriginalImage);
    xAddEventListener(window, "resize", closeOriginalImage);

    areabg.style.visibility = 'visible';
}

/**
 * @brief 원본 이미지 보여준 후 닫는 함수
 **/
function closeOriginalImage(evt) {
    var area = xGetElementById("fororiginalimagearea");
    if(area.style.visibility != "visible") return;
    area.style.visibility = "hidden";
    xGetElementById("fororiginalimageareabg").style.visibility = "hidden";

    var sel_list = xGetElementsByTagName("select");
    for (var i = 0; i < sel_list.length; ++i) sel_list[i].style.visibility = "visible";

    xRemoveEventListener(area, "mousedown", closeOriginalImage);
    xRemoveEventListener(window, "scroll", closeOriginalImage);
    xRemoveEventListener(window, "resize", closeOriginalImage);
}

/**
 * @brief 원본 이미지 드래그
 **/
var origDragManager = {obj:null, isDrag:false}
function origImageDragEnable(evt) {
    var e = new xEvent(evt);
    var obj = e.target;
    if(obj.id != "fororiginalimage") return;

    obj.draggable = true;
    obj.startX = e.pageX;
    obj.startY = e.pageY;

    if(!origDragManager.isDrag) {
        origDragManager.isDrag = true;
        xAddEventListener(document, "mousemove", origImageDragMouseMove, false);
    }

    xAddEventListener(document, "mousedown", origImageDragMouseDown, false);
}

function origImageDrag(obj, px, py) {
    var x = px - obj.startX;
    var y = py - obj.startY;

    var areabg = xGetElementById("fororiginalimageareabg");
    xLeft(areabg, xLeft(areabg)+x);
    xTop(areabg, xTop(areabg)+y);

    obj.startX = px;
    obj.startY = py;
}

function origImageDragMouseDown(evt) {
    var e = new xEvent(evt);
    var obj = e.target;
    if(obj.id != "fororiginalimage" || !obj.draggable) return;

    if(obj) {
        xPreventDefault(evt);
        obj.startX = e.pageX;
        obj.startY = e.pageY;
        origDragManager.obj = obj;
        xAddEventListener(document, 'mouseup', origImageDragMouseUp, false);
        origImageDrag(obj, e.pageX, e.pageY);
    }
}

function origImageDragMouseUp(evt) {
    if(origDragManager.obj) {
        xPreventDefault(evt);
        xRemoveEventListener(document, 'mouseup', origImageDragMouseUp, false);
        xRemoveEventListener(document, 'mousemove', origImageDragMouseMove, false);
        xRemoveEventListener(document, 'mousemdown', origImageDragMouseDown, false);
        origDragManager.obj.draggable  = false;
        origDragManager.obj = null;
        origDragManager.isDrag = false;
    }
}

function origImageDragMouseMove(evt) {
    var e = new xEvent(evt);
    var obj = e.target;
    if(!obj) return;
    if(obj.id != "fororiginalimage") {
        xPreventDefault(evt);
        xRemoveEventListener(document, 'mouseup', origImageDragMouseUp, false);
        xRemoveEventListener(document, 'mousemove', origImageDragMouseMove, false);
        xRemoveEventListener(document, 'mousemdown', origImageDragMouseDown, false);
        origDragManager.obj.draggable  = false;
        origDragManager.obj = null;
        origDragManager.isDrag = false;
        return;
    }

    xPreventDefault(evt);
    origDragManager.obj = obj;
    xAddEventListener(document, 'mouseup', origImageDragMouseUp, false);
    origImageDrag(obj, e.pageX, e.pageY);
}

/**
 * @brief 이름을 클릭하였을 경우 메뉴를 보여주는 함수
 * 이름 클릭시 MemberModel::getMemberMenu 를 호출하여 그 결과를 보여줌 (사용자의 속성에 따라 메뉴가 달라지고 애드온의 연결을 하기 위해서임) 
 **/
xAddEventListener(document, 'click', chkMemberMenu);
xAddEventListener(window, 'load', function() { setMemberMenuObjCursor(xGetElementsByTagName("div")); xGetElementsByTagName("span"); } );
var loaded_member_menu_list = new Array();

// className = "member_*" 일 경우의 object가 클릭되면 해당 회원의 메뉴를 출력함
function chkMemberMenu(evt) {
    var area = xGetElementById("membermenuarea");
    if(!area) return;
    if(area.style.visibility!="hidden") area.style.visibility="hidden";

    var e = new xEvent(evt);
    if(!e) return;

    var obj = e.target;
    while(obj) {
        if(obj && obj.className && obj.className.search("member_")!=-1) break;
        obj = obj.parentNode;
    }
    if(!obj || !obj.className || obj.className.search("member_")==-1) return;

    var member_srl = parseInt(obj.className.replace(/member_([0-9]+)/ig,'$1').replace(/([^0-9]*)/ig,''),10);
    if(!member_srl) return;

    // 현재 글의 mid, module를 구함
    var mid = current_mid;

    // 서버에 메뉴를 요청
    var params = new Array();
    params["member_srl"] = member_srl;
    params["cur_mid"] = mid;
    params["cur_act"] = current_url.getQuery('act');
    params["page_x"] = e.pageX;
    params["page_y"] = e.pageY;

    var response_tags = new Array("error","message","menu_list");

    if(loaded_member_menu_list[member_srl]) {
        params["menu_list"] = loaded_member_menu_list[member_srl];
        displayMemberMenu(params, response_tags, params);
        return;
    }
    show_waiting_message = false;
    exec_xml("member", "getMemberMenu", params, displayMemberMenu, response_tags, params);
    show_waiting_message = true;
}

function displayMemberMenu(ret_obj, response_tags, params) {
    var area = xGetElementById("membermenuarea");
    var menu_list = ret_obj['menu_list'];
    var member_srl = params["member_srl"];

    var html = "";

    if(loaded_member_menu_list[member_srl]) {
        html = loaded_member_menu_list[member_srl];
    } else {
        var infos = menu_list.split("\n");
        if(infos.length) {
            for(var i=0;i<infos.length;i++) {
                var info_str = infos[i];
                var pos = info_str.indexOf(",");
                var icon = info_str.substr(0,pos).trim();

                info_str = info_str.substr(pos+1, info_str.length).trim();
                var pos = info_str.indexOf(",");
                var str = info_str.substr(0,pos).trim();
                var func = info_str.substr(pos+1, info_str.length).trim();

                var className = "item";
                //if(i==infos.length-1) className = "item";

                if(!str || !func) continue;

                html += "<div class=\""+className+"\" onmouseover=\"this.className='"+className+"_on'\" onmouseout=\"this.className='"+className+"'\" style=\"background:url("+icon+") no-repeat left;\" onclick=\""+func+"\">"+str+"</div><br />";
            }
        } 
        loaded_member_menu_list[member_srl] = html;
    }

    if(html) {
        xInnerHtml(area, html);
        xWidth(area, xWidth(area));
        xLeft(area, params["page_x"]);
        xTop(area, params["page_y"]);
        if(xWidth(area)+xLeft(area)>xClientWidth()+xScrollLeft()) xLeft(area, xClientWidth()-xWidth(area)+xScrollLeft());
        if(xHeight(area)+xTop(area)>xClientHeight()+xScrollTop()) xTop(area, xClientHeight()-xHeight(area)+xScrollTop());
        area.style.visibility = "visible";
    }
}

// className = "member_*" 의 object의 cursor를 pointer로 본경
function setMemberMenuObjCursor(obj) {
    for (var i = 0; i < obj.length; ++i) {
        var node = obj[i];
        if(node.className && node.className.search(/member_([0-9]+)/ig)!=-1) {
            var member_srl = parseInt(node.className.replace(/member_([0-9]+)/ig,'$1').replace(/([^0-9]*)/ig,''),10);
            if(member_srl<1) continue;
            node.style.cursor = "pointer";
        }
    }
}

// 날짜 선택 (달력 열기)
function open_calendar(fo_id, day_str, callback_func) {
    if(typeof(day_str)=="undefined") day_str = "";

    var url = "./common/tpl/calendar.php?";
    if(fo_id) url+="fo_id="+fo_id;
    if(day_str) url+="&day_str="+day_str;
    if(callback_func) url+="&callback_func="+callback_func;

    popopen(url, 'Calendar');
}

// 언어코드 (lang_type) 쿠키값 변경
function doChangeLangType(obj) {
    if(typeof(obj)=="string") {
        setLangType(obj);
    } else {
        var val = obj.options[obj.selectedIndex].value;
        setLangType(val);
    }
    location.reload();
}
function setLangType(lang_type) {
    var expire = new Date();
    expire.setTime(expire.getTime()+ (7000 * 24 * 3600000));
    xSetCookie('lang_type', lang_type, expire);
}

/* 미리보기 */
function doDocumentPreview(obj) {
    var fo_obj = obj;
    while(fo_obj.nodeName != "FORM") {
        fo_obj = fo_obj.parentNode;
    }
    if(fo_obj.nodeName != "FORM") return;

    var content = fo_obj.content.value;

    var win = window.open("","previewDocument","toolbars=no,width=700px;height:800px,scrollbars=yes");

    var dummy_obj = xGetElementById("previewDocument");

    if(!dummy_obj) {
        var fo_code = '<form id="previewDocument" target="previewDocument" method="post" action="'+request_uri+'">'+
                      '<input type="hidden" name="module" value="document" />'+
                      '<input type="hidden" name="act" value="dispDocumentPreview" />'+
                      '<input type="hidden" name="content" />'+
                      '</form>';
        var dummy = xCreateElement("DIV");
        xInnerHtml(dummy, fo_code);
        window.document.body.insertBefore(dummy,window.document.body.lastChild);
        dummy_obj = xGetElementById("previewDocument");
    }

    if(dummy_obj) {
        dummy_obj.content.value = content;
        dummy_obj.submit();
    }
}
