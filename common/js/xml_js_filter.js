/**
 * @file   common/js/xml_js_filter.js
 * @author NHN (developers@xpressengine.com)
 * @brief  xml filter (validator) plugin
 * 
 * A rule is a method validate one field.
 * A filter is made up of one or more rules.
 **/
(function($){

var messages  = [];
var rules     = [];
var filters   = [];
var callbacks = [];
var extras    = {};

var Validator = xe.createApp('Validator', {
	init : function() {
		// {{{ add filters
		// email
		var regEmail = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/;
		this.cast('ADD_RULE', ['email', regEmail]);
		this.cast('ADD_RULE', ['email_address', regEmail]);

		// userid
		var regUserid = /^[a-z]+[\w-]*[a-z0-9_]+$/i;
		this.cast('ADD_RULE', ['userid', regUserid]);
		this.cast('ADD_RULE', ['user_id', regUserid]);

		// url
		var regUrl = /^(https?|ftp|mms):\/\/[0-9a-z-]+(\.[_0-9a-z-\/\~]+)+(:[0-9]{2,4})*$/;
		this.cast('ADD_RULE', ['url', regUrl]);
		this.cast('ADD_RULE', ['homepage', regUrl]);

		// korean
		var regKor = /^[가-힣]*$/;
		this.cast('ADD_RULE', ['korean', regKor]);

		// korean_number
		var regKorNum = /^[가-힣0-9]*$/;
		this.cast('ADD_RULE', ['korean_number', regKorNum]);

		// alpha
		var regAlpha = /^[a-z]*$/i;
		this.cast('ADD_RULE', ['alpha', regAlpha]);

		// alpha_number
		var regAlphaNum = /^[a-z][a-z0-9_]*$/i;
		this.cast('ADD_RULE', ['alpha_number', regAlphaNum]);

		// number
		var regNum = /^[0-9]*$/;
		this.cast('ADD_RULE', ['number', regNum]);
		// }}} add filters
	},
	// run validator
	run : function(oForm) {
		var filter = '';

		if (oForm._filter) filter = oForm._filter.value;

		var params = [oForm, filter];
		var result = this.cast('VALIDATE', params);
		if (typeof result == 'undefined') result = false;

		return result;
	},
	API_ONREADY : function() {
		var self = this;

		// hook form submit event
		$('form')
			.each(function(){
				if (this.onsubmit) {
					this['xe:onsubmit'] = this.onsubmit;
					this.onsubmit = null;
				}
			})
			.submit(function(){
				var legacyFn = this['xe:onsubmit'];		
				var hasLegacyFn = $.isFunction(legacyFn);
				var bResult = hasLegacyFn?legacyFn.apply(this):self.run(this);

				return bResult;
			});
	},
	API_VALIDATE : function(sender, params) {
		var result = true, form = params[0], filter=null, callback=null;
		var name, el, val, mod, len, lenb, max, min, maxb, minb, rules, e_el, e_val, i, c, r, result;

		if (form.elements['_filter']) filter = form.elements['_filter'].value;
		if (!filter) return true;
		if ($.isFunction(callbacks[filter])) callback = callbacks[filter];
		filter = $.extend({}, filters[filter.toLowerCase()] || {}, extras);

		for(name in filter) {
			if(!filter.hasOwnProperty(name)) continue;

			f   = filter[name];
			el  = form.elements[name];
			val = el?$.trim(get_value($(el))):'';
			mod = (f.modifier||'')+',';

			if(!el) continue;

			if(!val) {
				if(f['default']) val = f['default'];
				if(f.required) return this.cast('ALERT', [form, name, 'isnull']) && false;
				else continue;
			}

			min  = parseInt(f.minlength) || 0;
			max  = parseInt(f.maxlength) || 0;
			minb = /b$/.test(f.minlength||'');
			maxb = /b$/.test(f.maxlength||'');
			len  = val.length;
			if(minb || maxb) lenb = get_bytes(val);
			if((min && min > (minb?lenb:len)) || (max && max < (maxb?lenb:len))) {
				return this.cast('ALERT', [form, name, 'outofrange', min, max]) && false;
			}
			
			if(f.equalto) {
				e_el  = form.elements[f.equalto];
				e_val = e_el?$.trim(get_value($(e_el))):'';
				if(e_el && e_val !== val) {
					return this.cast('ALERT', [form, name, 'equalto']) && false;
				}
			}
			
			rules = (f.rule || '').split(',');
			for(i=0,c=rules.length; i < c; i++) {
				if(!(r = rules[i])) continue;

				result = this.cast('APPLY_RULE', [r, val]);
				if(mod.indexOf('not,') > -1) result = !result;
				if(!result) {
					return this.cast('ALERT', [form, name, 'invalid_'+r]) && false;
				}
			}
		}

		if ($.isFunction(callback)) return callback(form);

		return true;
	},
	API_ADD_RULE : function(sender, params) {
		var name = params[0].toLowerCase();
		rules[name] = params[1];
	},
	API_DEL_RULE : function(sender, params) {
		var name = params[0].toLowerCase();
		delete rules[name];
	},
	API_GET_RULE : function(sender, params) {
		var name = params[0].toLowerCase();

		if (rules[name]) {
			return rules[name];
		} else {
			return null;
		}
	},
	API_ADD_FILTER : function(sender, params) {
		var name   = params[0].toLowerCase();
		var filter = params[1];

		filters[name] = filter;
	},
	API_DEL_FILTER : function(sender, params) {
		var name = params[0].toLowerCase();
		delete filters[name];
	},
	API_GET_FILTER : function(sender, params) {
		var name = params[0].toLowerCase();

		if (filters[name]) {
			return filters[name];
		} else {
			return null;
		}
	},
	API_ADD_EXTRA_FIELD : function(sender, params) {
		var name = params[0].toLowerCase();
		var prop = params[1];

		extras[name] = prop;
	},
	API_GET_EXTRA_FIELD : function(sender, params) {
		var name = params[0].toLowerCase();
		return extras[name];
	},
	API_DEL_EXTRA_FIELD : function(sender, params) {
		var name = params[0].toLowerCase();
		delete extras[name];
	},
	API_APPLY_RULE : function(sender, params) {
		var name  = params[0];
		var value = params[1];

		if(typeof(rules[name]) == 'undefined') return true; // no filter
		if($.isFunction(rules[name])) return rules[name](value);
		if(rules[name] instanceof RegExp) return rules[name].test(value);
		if($.isArray(rules[name])) return ($.inArray(value, rules[name]) > -1);

		return true;
	},
	API_ALERT : function(sender, params) {
		var form = params[0];
		var field_name = params[1];
		var msg_code = params[2];
		var minlen   = params[3];
		var maxlen   = params[4];

		var field_msg = this.cast('GET_MESSAGE', [field_name]);
		var msg = this.cast('GET_MESSAGE', [msg_code]);

		if (msg != msg_code) msg = (msg.indexOf('%s')<0)?(field_msg+msg):(msg.replace('%s',field_msg));
		if (minlen||maxlen) msg +=  '('+(minlen||'')+'~'+(maxlen||'')+')';

		this.cast('SHOW_ALERT', [msg]);

		// set focus
		$(form.elements[field_name]).focus();
	},
	API_SHOW_ALERT : function(sender, params) {
		alert(params[0]);
	},
	API_ADD_MESSAGE : function(sender, params) {
		var msg_code = params[0];
		var msg_str  = params[1];

		messages[msg_code] = msg_str;
	},
	API_GET_MESSAGE : function(sender, params) {
		var msg_code = params[0];

		return messages[msg_code] || msg_code;
	},
	API_ADD_CALLBACK : function(sender, params) {
		var name = params[0];
		var func = params[1];

		callbacks[name] = func;
	},
	API_REMOVE_CALLBACK : function(sender, params) {
		var name = params[0];

		delete callbacks[name];
	}
});

var oValidator = new Validator;

// register validator
xe.registerApp(oValidator);

// 호환성을 위해 추가한 플러그인 - 에디터에서 컨텐트를 가져와서 설정한다.
var EditorStub = xe.createPlugin('editor_stub', {
	API_BEFORE_VALIDATE : function(sender, params) {
		var form = params[0];
		var seq  = form.getAttribute('editor_sequence');

		// bug fix for IE6,7
		if (seq && typeof seq == 'object') seq = seq.value;

		if (seq) {
			try {
				editorRelKeys[seq].content.value = editorRelKeys[seq].func(seq) || '';
			} catch(e) { }
		}
	}
});
oValidator.registerPlugin(new EditorStub);

// functions
function get_value(elem) {
	var vals = [];
	if (elem.is(':radio')){
		return elem.filter(':checked').val();
	} else if (elem.is(':checkbox')) {
		elem.filter(':checked').each(function(){
			vals.push(this.value);
		});
		return vals.join('|@|');
	} else {
		return elem.val();
	}
}

function get_bytes(str) {
	str += '';
	if(!str.length) return 0;

	str = encodeURI(str);
	var c = str.split('%').length - 1;

	return str.length - c*2;
}

})(jQuery);

/**
 * @function filterAlertMessage
 * @brief ajax로 서버에 요청후 결과를 처리할 callback_function을 지정하지 않았을 시 호출되는 기본 함수
 **/
function filterAlertMessage(ret_obj) {
	var error = ret_obj["error"];
	var message = ret_obj["message"];
	var act = ret_obj["act"];
	var redirect_url = ret_obj["redirect_url"];
	var url = location.href;

	if(typeof(message)!="undefined"&&message&&message!="success") alert(message);

	if(typeof(act)!="undefined" && act) url = current_url.setQuery("act", act);
	else if(typeof(redirect_url)!="undefined" && redirect_url) url = redirect_url;

	if(url == location.href) url = url.replace(/#(.*)$/,'');

	location.href = url;
}

/**
 * @brief Function to process filters
 * @deprecated
 */
function procFilter(form, filter_func) {
	filter_func(form);
	return false;
}

function legacy_filter(filter_name, form, module, act, callback, responses, confirm_msg, rename_params) {
	var v = xe.getApp('Validator')[0], $ = jQuery, args = [];

	if (!v) return false;

	if (!form.elements['_filter']) $(form).prepend('<input type="hidden" name="_filter" />');
	form.elements['_filter'].value = filter_name;

	args[0] = filter_name;
	args[1] = function(f) {
		var params = {}, res = [], elms = f.elements, data = $(f).serializeArray();
		$.each(data, function(i, field) {
			var v = $.trim(field.value), n = field.name;
			if(!v || !n) return true;
			if(rename_params[n]) n = rename_params[n];
			
			if(/\[\]$/.test(n)) n = n.replace(/\[\]$/, '');
			if(params[n]) params[n] += '|@|'+v;
			else params[n] = field.value;			
		});

		if (confirm_msg && !confirm(confirm_msg)) return false;
		exec_xml(module, act, params, callback, responses, params, form);
	};

	v.cast('ADD_CALLBACK', args);
	v.cast('VALIDATE', [form, filter_name]);

	return false;
}
