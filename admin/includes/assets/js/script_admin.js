jQuery(function ($) {

/*	var ajaxSaveOptionsRequest = null,
		ajaxRequestSuccess = true;

	if (true == cherryOptions.isShow) {
		$(document).ajaxSuccess(function (event, xhr, settings) {
			var actionData = /action=get_options_section/.test(settings.data);

			if ('/wp-admin/admin-ajax.php' == settings.url && 'html' == settings.dataType && actionData) {
				$('#wrap-cherry-skin').hide();
				$('#wrap-cherry-nav').hide();
			}
		});
	}

	$('body').on('click', '.cherry-switcher-panel', function () {
		var inputValue = $('.cherry-input-switcher', this).attr('value');

		if (inputValue == "false") {
			$('#wrap-cherry-skin').slideDown('slow');
			$('#wrap-cherry-nav').slideDown('slow');
			ajaxSaveOptions();
		} else {
			$('#wrap-cherry-skin').slideUp('slow');
			$('#wrap-cherry-nav').slideUp('slow');
			ajaxSaveOptions();
		}
	});

	function ajaxSaveOptions() {
		var
			serializeObject = $('#cherry-options').serializeObject(), data = {
				action: 'cherry_save_options',
				post_array: serializeObject.cherry
			}
			;

		if (ajaxSaveOptionsRequest != null && !ajaxRequestSuccess) {
			ajaxSaveOptionsRequest.abort();
		}

		ajaxSaveOptionsRequest = jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			cache: false,
			beforeSend: function () {
				ajaxRequestSuccess = false;
				$('#cherry-save-options').addClass('spinner-state');
			},
			success: function (response) {
				ajaxRequestSuccess = true;
				setTimeout(function () {
					$('#cherry-save-options').removeClass('spinner-state');
				}, 1000);
				$.cherryOptionsPage.noticeCreate(response.type, response.message);
				if (response.success) {
					console.log("json");
				}
			},
			dataType: 'json'
		});

		return false;
	}*/
});
