(function($){

if (!window.xe)  xe = {};
if (!xe.Editors) xe.Editors = [];

function editorStart_xe(editor_seq, primary_key, content_key, editor_height, colorset, content_style, content_font, content_font_size) {
	var $textarea, $form, $input, saved_str, xeed, opt = {};

	if(typeof(colorset)=='undefined') colorset = 'white';
	if(typeof(content_style)=='undefined') content_style = 'xeStyle';
	if(typeof(content_font)=='undefined') content_font= '';
	if(typeof(content_font_size)=='undefined') content_font_size= '';

	$textarea = $('#xeed-'+editor_seq);
	$form     = $($textarea[0].form).attr('editor_sequence', editor_seq);
	$input    = $form.find('input[name='+content_key+']');

	if($input[0]) $textarea.val($input.remove().val()).attr('name', content_key);
	$textarea.attr('name', content_key);
	
	// Use auto save feature?
	opt.use_autosave = !!$form[0].elements['_saved_doc_title'];
	
	// min height
	if (editor_height) opt.height = parseInt(editor_height) || 200;

	// create an editor
	xe.Editors[editor_seq] = xeed = new xe.Xeed($textarea, opt);
	xe.registerApp(xeed);
	
	// filters
	xeed.cast('REGISTER_FILTER', ['r2t', plz_standard]);
	xeed.cast('REGISTER_FILTER', ['r2t', remove_baseurl]);
	xeed.cast('REGISTER_FILTER', ['r2t', unwrap_single_para]);
	xeed.cast('REGISTER_FILTER', ['in',  inline_styled['in']]);
	xeed.cast('REGISTER_FILTER', ['out', inline_styled['out']]);
	xeed.cast('REGISTER_FILTER', ['r2t', auto_br.br2ln]);
	xeed.cast('REGISTER_FILTER', ['in',  auto_br.br2ln]);
	xeed.cast('REGISTER_FILTER', ['t2r', auto_br.ln2br]);
	xeed.cast('REGISTER_FILTER', ['out', auto_br.ln2br]);

	// Set standard API
	editorRelKeys[editor_seq] = {
		primary   : $form[0][primary_key],
		content   : $form[0][content_key],
		editor    : xeed,
		func      : function(){ return xeed.cast('GET_CONTENT'); },
		pasteHTML : function(text){ xeed.cast('PASTE_HTML', [text]); }
	};
	
	// Auto save
	if (opt.use_autosave) xeed.registerPlugin(new xe.Xeed.AutoSave());

	return xeed;
}

// standard filters
function plz_standard(code) {
	var single_tags = 'area br col hr img input param'.split(' '), replaces = {b:'strong',i:'em'};

	code = code.replace(/<(\/)?([A-Za-z0-9:]+)(.*?)(\s*\/?)>/g, function(m0,is_close,tag,attrs,closing){
		tag = tag.toLowerCase();
		if (replaces[tag]) tag = replaces[tag];
		
		attrs = attrs.replace(/([\w:-]\s*)=(?:([^"' \t\r\n]+)|\s*("[^"]*")|\s*('[^']*'))/g, function(m0,name,m2,m3,m4){
			var val = m2||m3||m4;

			if (m3||m4) val = val.substr(1,val.length-2);
		
			return $.trim(name.toLowerCase())+'='+'"'+val+'"';
		});

		if (attrs=$.trim(attrs)) attrs = ' '+attrs;
	
		closing = $.trim(closing);
		if (!is_close && !closing && $.inArray(tag, single_tags) != -1) closing = ' /';

		return '<'+(is_close||'')+tag+attrs+closing+'>';
	});

	return code;
}

// remove base url
function remove_baseurl(code) {
	var reg = new RegExp(' (href|src)\s*=\s*(["\'])?'+request_uri.replace('\\', '\\\\'), 'ig');

	return code.replace(reg, function(m0,m1,m2){ return ' '+m1+'='+m2; });
}

// unwrap single paragraph
function unwrap_single_para(code) {
	var match = $.trim(code).match(/<p[\s>]/g);

	if (match && match.length == 1) {
		code = code.replace(/<\/?p(?:\s[^>]+)?>/g, '');
	}

	return code;
};

// inline styled box
var inline_styled = {
	'in' : function(code) {
		return code.replace(/<(div|blockquote)(?:[^>]*) class="(bx|bq)"(?:[^>]*)>/ig, '<$1 class="$2">');
	},
	'out' : function(code) {
		return code.replace(/<(div|blockquote)(?:[^>]*) class="(bx|bq)"(?:[^>]*)>/ig, function(a,tag,cls){
			if (tag == 'div' && cls == 'bx') {
				return '<'+tag+' class="'+cls+'" style="background:#fafafa;border:1px dotted #999;margin:1em 0;padding:0 2em">';
			} else if (tag == 'blockquote' && cls == 'bq') {
				return '<'+tag+' class="'+cls+'" style="border-left:3px solid #ccc;margin:1em 0;padding-left:1em">';
			}
			return a;
		});
	}
};

// auto_br
var auto_br = {
	'br2ln' : function(code) {
		return code.replace(/<br ?\/?>/ig, '\n');
	},
	'ln2br' : function(code) {
		return code.replace(/(>)?[ \t]*((?:\r?\n[ \t]*)+)[ \t]*(<)?/g, function(m0,m1,m2,m3){
			if ( !m1 || !m3 ) m2 = m2.replace(/\r?\n/g, '<br />');
			return (m1||'')+m2+(m3||'');
		});
	}
};

window.editorStart_xe = editorStart_xe;

})(jQuery);

function editorGetAutoSavedDoc(form) {
	var param = new Array();
	param['mid'] = current_mid;
	param['editor_sequence'] = form.getAttribute('editor_sequence')
	setTimeout(function() {
	  var response_tags = new Array("error","message","editor_sequence","title","content","document_srl");
	  exec_xml('editor',"procEditorLoadSavedDocument", param, function(a,b,c) { editorRelKeys[param['editor_sequence']]['primary'].value = a['document_srl']; if(typeof(uploadSettingObj[param['editor_sequence']]) == 'object') editorUploadInit(uploadSettingObj[param['editor_sequence']], true); }, response_tags);
	}, 0);
	
}
