jQuery(function($) {
	$('.panel-btn').click(function(){
		$('.panel').toggleClass('open');
	})
});


jQuery.(document).ready(function(){

	if ($.cookie("cookie_background")) {
		$('#custom-background-css').attr('href', $.cookie("cookie_background"));
	} else {
		$('#custom-background-css').attr('href', 'css/background/dott.css');
	}

	$('.background_categ a').on('click', function(e) {
		e.preventDefault();
		set_background = $(this).attr('rel');
		$('#custom-background-css').attr('href', set_background);
		$.cookie("cookie_background", set_background);
	});
});