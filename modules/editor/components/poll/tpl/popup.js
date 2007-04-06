/**
 * popup으로 열렸을 경우 부모창의 위지윅에디터에 select된 block이 있는지 체크하여
 * 있으면 가져와서 원하는 곳에 삽입
 **/
var survey_index = 1;
function setSurvey() {
    var obj = xCreateElement("div");
    var source = xGetElementById("survey_source");
    xInnerHtml(obj, xInnerHtml(source));
    obj.id = "survey_"+survey_index;
    obj.className = "survey_box";
    obj.style.display = "block";

    source.parentNode.insertBefore(obj, source);
}

/**
 * 부모창의 위지윅에디터에 데이터를 삽입
 **/
function insertSurvey() {
    if(typeof(opener)=="undefined") return;

    var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)
    opener.editorReplaceHTML(iframe_obj, link);

    opener.focus();
    window.close();
}

xAddEventListener(window, "load", setSurvey);

/**
 * 새 설문 추가
 **/
function doSurveyAdd() {
    var obj = xCreateElement("div");
    var source = xGetElementById("survey_source");
    if(survey_index+1>3) return null;
    survey_index++;
    xInnerHtml(obj, xInnerHtml(source));
    obj.id = "survey_"+survey_index;
    obj.className = "survey_box";
    obj.style.display = "block";

    source.parentNode.insertBefore(obj, source);

    setFixedPopupSize();

    return null;
}

/**
 * 항목 삭제
 **/
function doSurveyDelete(obj) {
    var pobj = obj.parentNode.parentNode.parentNode;
    var tmp_arr = pobj.id.split('_');
    var index = tmp_arr[1];
    if(index==1) return;

    pobj.parentNode.removeChild(pobj);

    var obj_list = xGetElementsByClassName('survey_box');
    for(var i=0;i<obj_list.length;i++) {
        var nobj = obj_list[i];
        if(nobj.id == 'survey_source') continue;
        var tmp_arr = nobj.id.split('_');
        var index = tmp_arr[1];
        nobj.id = 'survey_'+(i+1);
    }
    survey_index = i-1;

    setFixedPopupSize();
}

/**
 * 새 항목 추가
 **/
function doSurveyAddItem(obj) {
    var pobj = obj.parentNode.parentNode;
    var source = xPrevSib(pobj);
    var new_obj = xCreateElement("div");
    var html = xInnerHtml(source);

    var idx_match = html.match(/ ([0-9]+)</i);
    if(!idx_match) return null;

    var idx = parseInt(idx_match[1],10);
    html = html.replace( / ([0-9]+)</, ' '+(idx+1)+'<');

    xInnerHtml(new_obj, html);
    new_obj.className = source.className;

    pobj.parentNode.insertBefore(new_obj, pobj);

    var box_obj = pobj.parentNode;
    var box_height = xHeight(box_obj);
    xHeight(box_obj, box_height+29);

    setFixedPopupSize();

    return null;
}
