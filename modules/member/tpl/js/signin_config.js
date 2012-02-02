function getDroppable(){
	return jQuery('<div class="droppable"></div>').makeDroppable();
}

function addType(obj, name, type){
	var $obj = jQuery(obj);

	$clone = $obj.parents('.type').children('.container').clone().makeDraggable().applySelect().addClass('selectable');
	$droppable = getDroppable();
	jQuery('<div class="wrap"></div>').data('name', $clone.data('name')).data('type', $clone.data('type')).append($clone).append($droppable).appendTo('#preview_content');
}

function addHorizontalBox(){
	$horizonBox = jQuery('<div class="horizon selectable"><div class="items"><div class="clear"></div></div></div>');
	$horizonBox.makeDraggable().applySelect();
	$droppable = jQuery('<div class="wrap default"></div>').append(getDroppable());
	$horizonBox.children('.items').prepend($droppable);

	$droppable = getDroppable();
	
	jQuery('<div class="wrap"></div>').data('name', 'horizontal').append($horizonBox).append($droppable).appendTo('#preview_content');
}

var $currentSelect = null;
function delType(){
	if(!$currentSelect) return;

	$controller = jQuery('#preview .previewController');
	$controller.appendTo('#preview').hide();
	$currentSelect.parent('.wrap').remove();
}

function moveUp(){
	if(!$currentSelect) return;

	$wrap = $currentSelect.parent('.wrap');
	$prev = $wrap.prev();

	if(!$prev.length) return;

	if($prev.hasClass('default')){
		$horizon = $wrap.parents('.horizon').parent('.wrap');
		if($horizon.length){
			$horizon.before($wrap);
			$wrap.children('.droppable').height('');
		}
		return;
	}

	if($prev.children('.horizon').length){
		var $clear = $prev.find('.default');
		$wrap.insertAfter($clear);
	}else{
		$wrap.insertBefore($prev);
	}
}

function moveDown(){
	if(!$currentSelect) return;

	$wrap = $currentSelect.parent('.wrap');
	$next = $wrap.next();

	if(!$next.length) return;
	
	if($next.hasClass('clear')){
		$horizon = $wrap.parents('.horizon').parent('.wrap');
		if($horizon.length){
			$horizon.after($wrap);
			$wrap.children('.droppable').height('');
		}
		return;
	}

	if($next.children('.horizon').length){
		var $clear = $next.find('.clear');
		$wrap.insertBefore($clear);
	}else{
		$wrap.insertAfter($next);
	}
}

function getData(){
	$xml = jQuery('<xml></xml>');

	jQuery('#preview_content').children('.wrap').each(function(){
		$this = jQuery(this);
		if(!$this.data('name')) return;

		$type = jQuery('<item />');
		$type.attr('name', $this.data('name'));

		if($this.data('name') == 'horizontal'){
			$this.find('.wrap').each(function(){
				$item = jQuery(this);
				if(!$item.data('name')) return;

				$type2 = jQuery('<item />');
				$type2.attr('name', $item.data('name'));
				$type2.attr('type', $item.data('type'));
				$type.append($type2);
			});
		}else{
			$type.attr('type', $this.data('type'));
		}

		$xml.append($type);
	});
	jQuery('#signinConfig').val('<items>' + $xml.html() + '</items>');
}

function doSubmit(obj){
	getData();	
	return true;
}

jQuery(function($) {
	$.fn.makeDraggable = function(){
		this.draggable({
			helper: 'clone',
			opacity: 0.5,
			zindex: 1000,
			cursorAt: {left: 0, top: 0},
			start: function(e, ui){
				ui.helper.css('z-index', '1000');
				ui.helper.width($(this).width());
				
				var $items = $('#preview .horizon .items');
				$items.each(function(){
					$(this).find('.droppable').each(function(){
						$(this).height($(this).prev().height());
					});
				});

				if($(this).hasClass('horizon')){
					$('#preview .horizon .droppable').droppable('disable');
				}
			},
			stop: function(e, ui){
				$('#preview .horizon .droppable').droppable('enable');
			}
		});
		return this;
	};

	$.fn.makeDroppable = function(){
		this.droppable({
			hoverClass: 'drophover',
			activeClass: 'dropactive',
			tolerance: 'pointer',
			drop: function(e, ui){
				if(ui.draggable.data('origin')){
					$clone = ui.draggable.clone().makeDraggable().applySelect().data('origin', null);
					$droppable = getDroppable();
					$wrap = $('<div class="wrap"></div>').data('name', $clone.data('name')).data('type', $clone.data('type')).append($clone).append($droppable);
				}else{
					$wrap = ui.draggable.parent('.wrap');
					$wrap.children('.droppable').height('');
				}

				$target = $(this).parent('.wrap');
				if($wrap.get(0) == $target.get(0)) return;

				$target.after($wrap);
			},
		});

		return this;
	};

	$.fn.applySelect = function(){
		if(this.data('bindedSelect')) return this;

		this.click(function(e){
			var $this = $(this);
			$controller = $('#preview .previewController');

			if($this.parents('.horizon').length){
				$controller.children('.up').hide();
				$controller.children('.down').hide();
				$controller.children('.left').show();
				$controller.children('.right').show();

				if($this.parents('.wrap').prev().hasClass('default')){
					$controller.children('.up').show();
					$controller.children('.left').hide();
				}
				
				if($this.parents('.wrap').next().hasClass('clear')){
					$controller.children('.down').show();
					$controller.children('.right').hide();
				}
			}else{
				$controller.children('.up').show();
				$controller.children('.down').show();
				$controller.children('.left').hide();
				$controller.children('.right').hide();
			}
			$this.append($controller);
			var width = $controller.outerWidth();
			$controller.show().css('right', '-' + (width+1) + 'px');

			$('#preview_content .selectable').removeClass('select');
			$currentSelect = $this;
			$this.addClass('select');
			e.stopPropagation();
		})
		.addClass('selectable')
		.data('bindedSelect', 'true');

		return this;
	}
	$('.type_list .type .container').makeDraggable().data('origin', true);
	$('#preview_content .container').makeDraggable().applySelect();
	$('#preview_content .horizon').makeDraggable().applySelect();
	
	$('#preview .droppable').makeDroppable();

	$('body').mousedown(function(){
		$('#preview_content .selectable').removeClass('select');
		$('#preview .previewController').hide();
	});
	$('#preview .previewController').mousedown(function(e){
		e.stopPropagation();
	});

	getData();
});
