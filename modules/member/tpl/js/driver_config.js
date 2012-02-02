
function addType(obj){
	var $obj = jQuery(obj);

	$obj.parents('.type').children('.container').clone().applySelect().addClass('selectable').appendTo('#preview_content');
	jQuery('#preview_content').trigger('change');
}

function addHorizontalBox(){
	jQuery('<div class="horizon selectable"><div class="sortable"></div><div style="clear:both"></div></div>')
		.applySelect()
		.appendTo('#preview_content')
		.bind('mousedown', function(){
			jQuery('#preview_content .horizon .sortable').sortable('disable');
		})
		.children('.sortable')
		.sortable({
			opacity: 0.5,
			zindex: 10,
			placeholder: 'ui-state-highlight',
			connectWith: '.sortable',
			// cursorAt: {left: 5, top: 5},
			start: function(e, ui){
				ui.item.trigger('click');
				jQuery('#preview .previewController').hide();
			},
			stop: function(e, ui){
				jQuery('#preview_content .horizon .sortable').sortable('enable');
			},
			change: function(e, ui){
				console.log(11);
			}
		})
		.disableSelection();
}

var $currentSelect = null;
function delType(){
	if(!$currentSelect) return;

	$controller = jQuery('#preview .previewController');
	$controller.appendTo('#preview').hide();
	$currentSelect.remove();
	jQuery('#preview_content').trigger('change');
}

function moveUp(){
	if(!$currentSelect) return;

	$prev = $currentSelect.prev();
	if(!$prev.length){
		$horizon = $currentSelect.parent().parent();
		if($horizon.hasClass('horizon')){
			$currentSelect.insertBefore($horizon);
			return;
		}
	}

	if($prev.hasClass('horizon')){
		$currentSelect.appendTo($prev.children('div.sortable'));
	}else{
		$currentSelect.insertBefore($prev);
	}
}

function moveDown(){
	if(!$currentSelect) return;

	$next = $currentSelect.next();
	if(!$next.length){
		$horizon = $currentSelect.parent().parent();
		if($horizon.hasClass('horizon')){
			$currentSelect.insertAfter($horizon);
			return;
		}
	}

	if($next.hasClass('horizon')){
		$currentSelect.appendTo($next.children('div.sortable'));
	}else{
		$currentSelect.insertAfter($next);
	}
}

jQuery(function($) {
	$('.type .add').click(function(){
		addType(this);
	});
	$('.type_list .type .container').draggable({
		connectToSortable: '#preview_content',
		helper: 'clone',
		// cursorAt: {left: 5, top: 5},
		opacity: 0.5,
		zindex: 10,
	});
	$.fn.applySelect = function(){
		if(this.data('bindedSelect')) return this;

		var $this = this;
		this.click(function(e){
			$controller = $('#preview .previewController');

			if($this.parent().parent().hasClass('horizon')){
				$controller.children('.updown').hide();
				$controller.children('.leftright').show();
			}else{
				$controller.children('.updown').show();
				$controller.children('.leftright').hide();
			}
			$this.append($controller);
			var width = $controller.outerWidth();
			$controller.show().css('right', '-' + (width+1) + 'px');

			$('#preview_content .selectable').removeClass('select');
			$currentSelect = $this;
			$this.addClass('select');
			e.stopPropagation();
		})
		.data('bindedSelect', 'true');

		return this;
	}
	$('#preview_content').sortable({
		opacity: 0.5,
		zindex: 10,
		connectWith: '.sortable',
		placeholder: 'ui-state-highlight',
		// cursorAt: {left: 5, top: 5},
		start: function(e, ui){
			ui.item.trigger('click');
			$('#preview .previewController').hide();
		},
		stop: function(e, ui){
			$('#preview_content .horizon .sortable').sortable('enable');
			ui.item.applySelect().addClass('selectable').css('float', '');
		},
		change: function(e, ui){
			console.log(112);
		}
	})
	.disableSelection();

	$('#preview_content .container').applySelect();
});
