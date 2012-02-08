// Join Form
jQuery(function($){
	var $formTable = $('#formTable');

	// if no set 'use', disable radio
	$formTable.find(':checkbox').change(function(){
		var $this = $(this);

		if($this.is(':checked')){
			$this.closest('tr').find(':radio').removeAttr('disabled');
		}else{
			$this.closest('tr').find(':radio').attr('disabled', 'disabled');
		}
	});

	// get HTML before open modal
	$.fn.makeEdit = function(){
		this.bind('before-open.mw', function(e){
			var $this = $(this);
			var formSrl = $this.closest('td').attr('id');
			var radioChecked = $this.closest('tr').find(':radio:checked').val();

			// after get HTML
			function onComplete(data){
				$('#extendFormInput').html(data.tpl)
					.find('#radio_' + radioChecked).attr('checked', 'checked').end()
					.find('input').labelBold();

				// make ajax request
				$form = $('#extendFormInput').closest('form');
				$form.submit(function(e){
					if(e.isDefaultPrevented()) return false;

					params = {};

					$form.find('input:text, input:hidden, input:checked, select, textarea').each(function(){
						var $this = $(this);
						params[$this.attr('name')] = $this.val();
					});

					$.exec_json('member.' + params.act, params, insertRow);

					return false;
				});
			}

			// get HTML
			$.exec_json('member.getMemberAdminInsertJoinForm', {'member_join_form_srl': formSrl}, onComplete);
		});
	}
	$('a.modalAnchor._extendFormEdit').makeEdit();

	// delete
	$.fn.makeDelete = function()
	{
		this.click(function(event){
			var $this = $(this);

			event.preventDefault();
			if (!confirm(xe.lang.msg_delete_extend_form)) return;

			var memberFormSrl = $this.closest('td').attr('id');
			var targetTR = $this.closest('tr');

			function onComplete(data){
				if(data.error){
					alert(data.message);
					return;
				}

				targetTR.remove();
			}

			$.exec_json('member.procMemberAdminDeleteJoinForm', {'member_join_form_srl': memberFormSrl, 'driver': 'me2day'}, onComplete);
		});
	}

	$('a._extendFormDelete').makeDelete();
});

// insert row after ajax call
var params = {};
function insertRow(data)
{
	if(data.error){
		alert(data.message);
		return;
	}

	var $table = $('#formTable');
	var $target = null;

	if(!data.is_insert){
		$target = $table.find('#' + data.member_join_form_srl).closest('tr');
	}

	var $row = $('<tr></tr>');

	$row
		.append('<th scope="row"><input type="hidden" name="list_order[]" value="' + data.member_join_form_srl + '" /><div class="wrap"><button type="button" class="dragBtn">Move to</button>' + params.column_title + '</div></th>')
		.append('<td><input type="checkbox" name="is_use_' + data.member_join_form_srl + '" value="Y" title="' + xe.lang.cmd_use + '" checked="checked" /></td>');

	var $td = $('<td></td>');
	var $radioRequired = $('<input type="radio" id="' + params.column_name + '_re" name="required_' + data.member_join_form_srl + '" value="Y" />');
	var $radioOption = $('<input type="radio" id="' + params.column_name + '_op" name="required_' + data.member_join_form_srl + '" value="N" />');

	if(params.required == 'Y'){
		$radioRequired.attr('checked', 'checked');
		$radioOption.removeAttr('checked');
	}else{
		$radioRequired.removeAttr('checked');
		$radioOption.attr('checked', 'checked');
	}

	$td
		.append($radioRequired).append("\n")
		.append('<label for="' + params.column_name + '_re">' + xe.lang.cmd_required + '</label>').append("\n")
		.append($radioOption).append("\n")
		.append('<label for="' + params.column_name + '_op">' + xe.lang.cmd_optional + '</label>');

	$row
		.append($td)
		.append('<td class="text">' + params.description + '</td>')
		.append('<td id="' + data.member_join_form_srl + '"><a href="#userDefine" class="modalAnchor _extendFormEdit">' + xe.lang.cmd_edit + '</a> | <a href="#" class="_extendFormDelete">' + xe.lang.cmd_delete + '</a></td>');

	$row.find('a._extendFormDelete').makeDelete();
	$row.find('a._extendFormEdit').makeEdit();

	if($target){
		$target.after($row);
		$target.remove();
	}else{
		$table.append($row);
	}

	$row.find('input').labelBold().end()
		.find('a.modalAnchor').xeModalWindow();

	$('.modalAnchor._extendFormEdit').trigger('close.mw');
}
