jQuery(function($){
	$('.__driverSignup').hide();
	$('.selectBar li a').click(function (e){
		$('.__driverSignup').hide();
		$($(this).attr('href')).show();
	});
});
