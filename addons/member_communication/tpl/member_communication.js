(function($){

window.xeNotifyMessage = function(text, count){
	var $bar;

	$bar = $('div.notifyMessage');
	if(!$bar.length) {
		$bar = $('<div class="message info" />')
			.hide()
			.css({
				'position'   : 'absolute',
				'z-index' : '100',
			})
			.appendTo(document.body);
	}

	text = text.replace('%d', count);
	h = $bar.html('<p><a href="'+current_url.setQuery('act','dispCommunicationMessages')+'">'+text+'</a></p>').height();
	$bar.css('top', -h-4).show().animate({top:0});

	// hide after 10 seconds
	setTimeout(function(){
		$bar.animate({top:-h-4}, function(){ $bar.hide() });
	}, 5000);
};

})(jQuery);
