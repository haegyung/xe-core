
/* 금지아이디 관련 작업들 */
function doUpdateDeniedID(user_id, mode, message) {
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    exec_xml(
		'member',
		'procMemberAdminDriverInterface',
		{driver: 'default', dact: 'procUpdateDeniedId', user_id: user_id, mode: mode},
		function(){
			if (mode == 'delete'){
				jQuery('#denied_'+user_id).remove();
				jQuery('._deniedIDCount').html(jQuery('#deniedList li').length);
			}
		},
		['error','message','tpl']
	);
}

jQuery(function($){
	// hide form if enable_join is setted "No" 
	var suForm = $('fieldset.suForm'); // 회원가입 양식
	suForm.find(':checkbox').each(function(){
		var $i = $(this);
		$i.change(function(){
			if($i.is(':checked')){
				$i.parent('td').next('td')
							   .find(':radio, :text')
									.removeAttr('disabled')
									.end()
							   .find(':radio[value=option]').attr('checked', 'checked');
				
			} else {
				$i.parent('td').next('td').find(':radio, :text').attr('disabled','disabled').removeAttr('checked').next('label').css('fontWeight','normal');
			}
		});
	});

	suForm.find('._imageType')
		.find('input:checkbox:not(:checked)').closest('tr')
			.find('._subItem').hide().end()
			.end()
		.end()
		.find('input:checkbox')
			.change(function(){
				var $subItem = $(this).closest('tr').find('._subItem');
				if($(this).is(':checked')) $subItem.show();
				else $subItem.hide();
			})
		.end();

		$('a.modalAnchor._extendFormEdit').bind('before-open.mw', function(event){
		var memberFormSrl = $(event.target).parent().attr('id');
		var checked = $(event.target).closest('tr').find('input:radio:checked').val();


		exec_xml(
			'member',
			'getMemberAdminInsertJoinForm',
			{member_join_form_srl:memberFormSrl},
			function(ret){
				var tpl = ret.tpl.replace(/<enter>/g, '\n');
				$('#extendForm').html(tpl).find('#radio_'+checked).attr('checked', 'checked').end()
											.find('input').labelBold();

				// Make ajax request
				var $form = $('#extendForm').closest('form');

				$form.submit(function(e){
					if (e.isDefaultPrevented()) return false;

					var params = {};

					$form.find('input:text, input:hidden, input:checked, select, textarea').each(function(){
						var $this = $(this);
						params[$this.attr('name')] = $this.val();
					});

					function onComplete(response){
						var $table = $('#formTable');
						var $target = null;

						if(!response.is_insert){
							$target = $table.find('#'+response.member_join_form_srl).closest('tr');
						}

						var $row = $('<tr></tr>');

						$row
							.append('<input type="hidden" name="list_order[]" value="' + params.column_name + '" />')
							.append('<input type="hidden" name="' + params.column_name + '_member_join_form_srl" value="' + response.member_join_form_srl +'" />')
						
							.append('<th scope="row"><div class="wrap"><button type="button" class="dragBtn">Move to</button><span class="_title">'+ params.column_title +'</span></div></th>')
							.append('<td></td>')
							.append('<td><input type="checkbox" name="usable_list[]" value="' + params.column_name+ '" title="' + xe.lang.cmd_use + '" checked="checked" /></td>');

						var $td = $('<td></td>');
						var $radioRequired = $('<input type="radio" id="' + params.column_name + '_re" name="' + params.column_name + '" value="required"/>');
						var $radioOption = $('<input type="radio" id="' + params.column_name + '_op" name="' + params.column_name + '" value="option" />');

						if(params.required == 'Y')
						{
							$radioRequired.attr('checked', 'checked');
							$radioOption.removeAttr('checked');
						}
						else
						{
							$radioRequired.removeAttr('checked');
							$radioOption.attr('checked', 'checked');
						}

						$td
							.append($radioRequired).append("\n")
							.append('<label for="' + params.column_name + '_re">' + xe.lang.cmd_required + '</label>').append("\n")
							.append($radioOption).append("\n")
							.append('<label for="' + params.column_name + '_op">' + xe.lang.cmd_optional + '</label></td>');

						$row
							.append($td)
							.append('<td class="text">' + params.description + '</td>')
							.append('<td id="' + response.member_join_form_srl+ '"><a href="#userDefine" class="modalAnchor _extendFormEdit">' + xe.lang.cmd_edit + '</a> | <a href="#" class="_extendFormDelete">' + xe.lang.cmd_delete + '</a></td>');

						$row.find('._extendFormDelete').makeDelete();

						if($target) {
							$target.after($row);
							$target.remove();
						}else{
							$table.append($row);
						}

						$row.find('input').labelBold().end()
							.find('a.modalAnchor').xeModalWindow();

						$('.modalAnchor._extendFormEdit').trigger('close.mw');
					}
					exec_xml(
						'member',
						params.act,
						params,
						onComplete,
						['member_join_form_srl', 'is_insert']
					);

					return false;
				});

				if (checked)$('#extendForm #radio_'+checked).attr('checked', 'checked');
			},
			['error','message','tpl']
		);

	});
	
	$.fn.makeDelete = function()
	{
		this.click(function(event){
			event.preventDefault();
			if (!confirm(xe.lang.msg_delete_extend_form)) return;

			var memberFormSrl = $(event.target).parent().attr('id');
			var targetTR = $(event.target).closest('tr'); 

			exec_xml(
				'member',
				'procMemberAdminDeleteJoinForm',
				{member_join_form_srl:memberFormSrl, driver:"default"},
				function(ret){
					targetTR.remove();
				},
				['error','message','tpl']
			);
		});
	}

	$('a._extendFormDelete').makeDelete();

	$('button._addDeniedID').click(function(){
		var ids = $('#prohibited_id').val();
		if(ids == ''){ 
			alert(xe.lang.msg_null_prohibited_id);
			$('#prohibited_id').focus();
			return;
		}
		

		ids = ids.replace(/\n/g, ',');

		var tag;
		function on_complete(data){
			if(data.error){
				alert(data.message);
				return;
			}
			var uids = data.user_ids.split(',');
			for (var i=0; i<uids.length; i++){
				tag = '<li id="denied_'+uids[i]+'">'+uids[i]+' <a href="#" class="side" onclick="doUpdateDeniedID(\''+uids[i]+'\', \'delete\', \''+xe.lang.confirm_delete+'\');return false;">'+xe.lang.cmd_delete+'</a></li>';
				$('#deniedList').append($(tag));
			}
			$('#prohibited_id').val('');

			$('._deniedIDCount').html($('#deniedList li').length);
		}

		jQuery.exec_json('member.procMemberAdminDriverInterface', {driver: 'default', dact: 'procInsertDeniedId', user_id: ids}, on_complete);

	});

	$('input[name=identifier]').change(function(){
		var $checkedTR = $('input[name=identifier]:checked').closest('tr');
		var $notCheckedTR = $('input[name=identifier]:not(:checked)').closest('tr');
		var name, notName;
		if (!$checkedTR.hasClass('sticky')){
			name = $checkedTR.find('input[name="list_order[]"]').val();
			if (!$checkedTR.find('input[type=hidden][name="usable_list[]"]').length) $('<input type="hidden" name="usable_list[]" value="'+name+'" />').insertBefore($checkedTR);
			if (!$checkedTR.find('input[type=hidden][name='+name+']').length) $('<input type="hidden" name="'+name+'" value="required" />').insertBefore($checkedTR);
			$checkedTR.find('th').html('<span class="_title" style="padding-left:20px" >'+$checkedTR.find('th ._title').html()+'</span>');
			$checkedTR.find('input[type=checkbox][name="usable_list[]"]').attr('checked', 'checked').attr('disabled', 'disabled');
			$checkedTR.find('input[type=radio][name='+name+'][value=required]').attr('checked', 'checked').attr('disabled', 'disabled');
			$checkedTR.find('input[type=radio][name='+name+'][value=option]').removeAttr('checked').attr('disabled', 'disabled');
			$checkedTR.addClass('sticky');
			$checkedTR.parent().prepend($checkedTR);

			notName = $notCheckedTR.find('input[name="list_order[]"]').val();
			if (notName == 'user_id'){
				if ($notCheckedTR.find('input[type=hidden][name="usable_list[]"]').length) $notCheckedTR.find('input[type=hidden][name="usable_list[]"]').remove();
				if ($notCheckedTR.find('input[type=hidden][name='+name+']').length) $notCheckedTR.find('input[type=hidden][name='+name+']').remove();
				$notCheckedTR.find('input[type=checkbox][name="usable_list[]"]').removeAttr('disabled');
				$notCheckedTR.find('input[type=radio][name='+notName+']').removeAttr('disabled');
			}
			$notCheckedTR.find('th').html('<div class="wrap"><button type="button" class="dragBtn">Move to</button><span class="_title" >'+$notCheckedTR.find('th ._title').html()+'</span></div>');
			$notCheckedTR.removeClass('sticky');

			// add sticky class 
		}
	});

	$('#formTable').prepend($('input[name=identifier]:checked').closest('tr'));
});
