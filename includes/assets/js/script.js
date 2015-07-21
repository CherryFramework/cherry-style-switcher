jQuery(function($) {

	$(window).on('load', function () {
		var $preloader = $('.preloader'),
		$spinner = $preloader.find('.spinner');
		$spinner.fadeOut();
		$preloader.delay(350).fadeOut('slow');
	});

	$('.panel-btn').click(function(){
		$('.panel').toggleClass('open');
	});


	$(document).ready(function($){

		$('.panel-inner').mCustomScrollbar({
			autoHideScrollbar : true,
		});

		if ($.cookie("cookie_skins")) {
			$('#cherry-dynamic-css').attr('href', $.cookie("cookie_skins"));
			$('.skins li a[href="' + $.cookie("cookie_skins") + '"]').parent().addClass('active');
		} else {
			$('#cherry-dynamic-css').attr('href', $('.skins a:first').attr('href'));
			$('.skins li:first').addClass('active');
		}

		if ($.cookie("cookie_nav")) {
			$('#style-switcher-nav-css').attr('href', $.cookie("cookie_nav"));
			$('.nav-type li a[href="' + $.cookie("cookie_nav") + '"]').parent().addClass('active');
		} else {
			$('#style-switcher-nav-css').attr('href', $('.nav-type a:first').attr('href'));
			$('.nav-type li:first').addClass('active');
		}

		if ($.cookie("cookie_layout")) {
			$('#style-switcher-layout-css').attr('href', $.cookie("cookie_layout"));
			$('.layout-type li a[href="' + $.cookie("cookie_layout") + '"]').parent().addClass('active');
		} else {
			$('#style-switcher-layout-css').attr('href', $('.layout-type a:first').attr('href'));
			$('.layout-type li:first').addClass('active');
		}

		$('.skins a').on('click', function(e) {
			e.preventDefault();
			$(this).parents('ul').find('li').removeClass('active');
			$(this).parents('li').addClass('active')
			set_skins = $(this).attr('href');
			$('#cherry-dynamic-css').attr('href', set_skins);
			$.cookie("cookie_skins", set_skins);
		});

		$('.nav-type a').on('click', function(e) {
			e.preventDefault();
			$(this).parents('ul').find('li').removeClass('active');
			$(this).parents('li').addClass('active')
			set_nav = $(this).attr('href');
			$('#style-switcher-nav-css').attr('href', set_nav);
			$.cookie("cookie_nav", set_nav);
		});

		$('.layout-type a').on('click', function(e) {
			e.preventDefault();
			$(this).parents('ul').find('li').removeClass('active');
			$(this).parents('li').addClass('active')
			set_layout = $(this).attr('href');
			$('#style-switcher-layout-css').attr('href', set_layout);
			$.cookie("cookie_layout", set_layout);
		});
	});
});


