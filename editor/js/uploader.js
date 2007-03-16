/**
 * @author zero (zero@nzeo.com)
 * @version 0.1
 * @brief 파일 업로드 관련
 **/
var uploading_file = false;
var uploaded_files = new Array();

// 업로드를 하기 위한 준비 시작
function editor_upload_init(upload_target_srl) {
  xAddEventListener(window,'load',function() {editor_upload_form_set(upload_target_srl);} );
}

// upload_target_srl에 해당하는 form의 action을 iframe으로 변경
function editor_upload_form_set(upload_target_srl) {
  // 업로드용 iframe을 생성
  if(!xGetElementById('tmp_upload_iframe')) {
    if(xIE4Up) {
      window.document.body.insertAdjacentHTML("afterEnd", "<iframe name='tmp_upload_iframe' style='display:none;width:1px;height:1px;position:absolute;top:-10px;left:-10px'></iframe>");
    } else {
      var obj_iframe = xCreateElement('IFRAME');
      obj_iframe.name = obj_iframe.id = 'tmp_upload_iframe';
      obj_iframe.style.display = 'none';
      obj_iframe.style.width = '1px';
      obj_iframe.style.height = '1px';
      obj_iframe.style.position = 'absolute';
      obj_iframe.style.top = '-10px';
      obj_iframe.style.left = '-10px';
      window.document.body.appendChild(obj_iframe);
    }
  }

  // form의 action 을 변경
  var field_obj = xGetElementById("uploaded_file_list_"+upload_target_srl);
  if(!field_obj) return;
  var fo_obj = field_obj.parentNode;
  while(fo_obj.nodeName != 'FORM') { fo_obj = fo_obj.parentNode; }
  fo_obj.target = 'tmp_upload_iframe';

  // upload_target_srl에 해당하는 첨부파일 목록을 로드
  var module = "";
  if(fo_obj["module"]) module = fo_obj.module.value;
  var mid = "";
  if(fo_obj["mid"]) mid = fo_obj.mid.value;

  var url = "./?act=procDeleteFile&upload_target_srl="+upload_target_srl;
  if(module) url+="&module="+module;
  if(mid) url+="&mid="+mid;

  // iframe에 url을 보내버림
  var iframe_obj = xGetElementById('tmp_upload_iframe');
  if(!iframe_obj) return;

  iframe_obj.contentWindow.document.location.href=url;
}

// 파일 업로드
function editor_file_upload(field_obj, upload_target_srl) {
    if(uploading_file) return;

    var fo_obj = field_obj.parentNode;
    while(fo_obj.nodeName != 'FORM') { fo_obj = fo_obj.parentNode; }

    uploading_file = true;
    fo_obj.submit();
    uploading_file = false;

    var sel_obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
    var str = 'wait for uploading...';
    var opt_obj = new Option(str, '', true, true);
    sel_obj.options[sel_obj.options.length] = opt_obj;
}

// 업로드된 파일 목록을 삭제
function editor_upload_clear_list(upload_target_srl) {
  var obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
  while(obj.options.length) {
    obj.remove(0);
  }
  var preview_obj = xGetElementById('uploaded_file_preview_box_'+upload_target_srl);
  xInnerHtml(preview_obj,'')
}

// 업로드된 파일 정보를 목록에 추가
function editor_insert_uploaded_file(upload_target_srl, file_srl, filename, file_size, disp_file_size, uploaded_filename, sid) {
  var obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
  var string = filename+' ('+disp_file_size+')';
  var opt_obj = new Option(string, file_srl, true, true);
  obj.options[obj.options.length] = opt_obj;

  var file_obj = {file_srl:file_srl, filename:filename, file_size:file_size, uploaded_filename:uploaded_filename, sid:sid}
  uploaded_files[file_srl] = file_obj;

  editor_preview(obj, upload_target_srl);
}

// 파일 목록창에서 클릭 되었을 경우 미리 보기
function editor_preview(sel_obj, upload_target_srl) {
  if(sel_obj.options.length<1) return;
  var file_srl = sel_obj.options[sel_obj.selectedIndex].value;
  var obj = uploaded_files[file_srl];
  if(typeof(obj)=='undefined'||!obj) return;
  var uploaded_filename = obj.uploaded_filename;
  var preview_obj = xGetElementById('uploaded_file_preview_box_'+upload_target_srl);

  if(!uploaded_filename) {
    xInnerHtml(preview_obj, '');
    return;
  }

  var html = "";

  // 플래쉬 동영상의 경우
  if(/\.flv$/i.test(uploaded_filename)) {
    html = "<EMBED src=\""+editor_path+"component/flvplayer/flvplayer.swf?autoStart=true&file="+uploaded_filename+"\" width=\"120\" height=\"120\" type=\"application/x-shockwave-flash\"></EMBED>";
  // 플래쉬 파일의 경우
  } else if(/\.swf$/i.test(uploaded_filename)) {
    html = "<EMBED src=\""+uploaded_filename+"\" width=\"120\" height=\"120\" type=\"application/x-shockwave-flash\"></EMBED>";
  // wmv, avi, mpg, mpeg등의 동영상 파일의 경우
  } else if(/\.(wmv|avi|mpg|mpeg|asx|asf|mp3)$/i.test(uploaded_filename)) {
    html = "<EMBED src=\""+uploaded_filename+"\" width=\"120\" height=\"120\" autostart=\"true\" Showcontrols=\"0\"></EMBED>";
  // 이미지 파일의 경우
  } else if(/\.(jpg|jpeg|png|gif)$/i.test(uploaded_filename)) {
    html = "<img src=\""+uploaded_filename+"\" border=\"0\" width=\"120\" height=\"120\" />";
  }
  xInnerHtml(preview_obj, html);
}

// 업로드된 파일 삭제
function editor_remove_file(upload_target_srl) {
  var obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
  if(obj.options.length<1) return;
  var file_srl = obj.options[obj.selectedIndex].value;
  if(!file_srl) return;

  // 삭제하려는 파일의 정보를 챙김;;
  var fo_obj = obj;
  while(fo_obj.nodeName != 'FORM') { fo_obj = fo_obj.parentNode; }
  var mid = fo_obj.mid.value;
  var upload_target_srl = fo_obj.upload_target_srl.value;
  var url = "./?mid="+mid+"&act=procDeleteFile&upload_target_srl="+upload_target_srl+"&file_srl="+file_srl;

  // iframe에 url을 보내버림
  var iframe_obj = xGetElementById('tmp_upload_iframe');
  if(!iframe_obj) return;

  iframe_obj.contentWindow.document.location.href=url;
}

// 업로드 목록의 선택된 파일을 내용에 추가
function editor_insert_file(upload_target_srl, align) {
  var obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
  if(obj.options.length<1) return;
  var file_srl = obj.options[obj.selectedIndex].value;
  if(!file_srl) return;
  var file_obj = uploaded_files[file_srl];
  var filename = file_obj.filename;
  var sid = file_obj.sid;
  var uploaded_filename = file_obj.uploaded_filename;
  editorPrevSrl = upload_target_srl;

  // 바로 링크 가능한 파일의 경우 (이미지, 플래쉬, 동영상 등..)
  if(uploaded_filename && typeof(align)!='undefined') {

    var type = "";

    // 이미지 파일의 경우
    if(/\.(jpg|jpeg|png|gif)$/i.test(uploaded_filename)) {

      editorFocus(editorPrevSrl);

      var html = "<img src=\""+uploaded_filename+"\" border=\"0\" alt=\""+filename+"\" ";
      if(typeof(align)!='undefined'&&align) html += " align=\""+align+"\"";
      html += " />"
      var obj = editorGetIFrame(editorPrevSrl);
      editorReplaceHTML(obj, html);

    // 이미지외의 경우는 대체 이미지를 넣음
    } else {
      // 플래쉬 동영상의 경우
      if(/\.flv$/i.test(uploaded_filename)) {
        type = "flv";
        uploaded_filename = editor_path+"component/flvplayer/flvplayer.swf?autoStart=true&file="+uploaded_filename;
      // 플래쉬 파일의 경우
      } else if(/\.swf$/i.test(uploaded_filename)) {
        type = "swf";
      // wmv, avi, mpg, mpeg등의 동영상 파일의 경우
      } else if(/\.(wmv|avi|mpg|mpeg)$/i.test(uploaded_filename)) {
        type = "multimedia";
      }

      var alt = "align="+align+"|@|src="+uploaded_filename+"|@|type="+type;
      var html = "<img src=\""+editor_path+"images/blank.gif\" style=\"width:400px;height:300px;\" alt=\""+alt+"\" class=\"editor_multimedia\"/>";

      var iframe_obj = editorGetIFrame(editorPrevSrl);
      editorReplaceHTML(iframe_obj, html);
    }

  // binary파일의 경우 다운로드 링크를 추가
  } else {
    var fo_obj = obj;
    while(fo_obj.nodeName != 'FORM') { fo_obj = fo_obj.parentNode; }
    var mid = fo_obj.mid.value;
    var upload_target_srl = fo_obj.upload_target_srl.value;
    var url = "./?mid="+mid+"&amp;act=procDownload&amp;upload_target_srl="+upload_target_srl+"&amp;file_srl="+file_srl+"&amp;sid="+sid;

    var x = (screen.availWidth - 400)/2;
    var y = (screen.availHeight - 220)/2;
    winopen(editor_path+"popup/add_url.php?title="+escape(filename)+"&url="+escape(url),"_editorPopup","top="+y+",left="+x+",width=400,height=220,resizable=no,toolbars=no,scrollbars=no");
  } 

}

/**
 * 글을 쓰다가 페이지 이동시 첨부파일에 대한 정리
 **/
function editorRemoveAttachFiles(mid, upload_target_srl) {
  var obj = xGetElementById('uploaded_file_list_'+upload_target_srl);
  if(obj.options.length<1) return;

  var params = new Array();
  params['upload_target_srl'] = upload_target_srl;
  exec_xml(mid, 'procClearFile', params, null, null, null);
}
