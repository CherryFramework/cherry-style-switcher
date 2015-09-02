/**
 * cherry_preset_swither
 */
(function($){
	"use strict";

	CHERRY_API.utilites.namespace('cherry_preset_swither');
	CHERRY_API.cherry_preset_swither = {
		ajaxRequestSuccess: true, // ajax success check
		ajaxImportPresetRequest: null, // ajax success check
		init: function () {
			var self = this;

			if( CHERRY_API.status.is_ready ){
				self.render();
			}else{
				CHERRY_API.variable.$document.on('ready', self.on_ready() );
			}

			if( CHERRY_API.status.on_load ){
				self.render();
			}else{
				CHERRY_API.variable.$window.on('load', self.on_load() );
			}
		},
		on_ready: function () {
			var
				self = this
			,	$panel = $('.style-switcher-panel')
			,	$preset_list = $('.preset-list li')
			,	$panel_toggle = $('.panel-toggle', $panel)
			,	$cover = $('.site-cover')
			,	$body = $('body')
			,	is_panel_open = 'false'
			;

			if ( this.is_local_storage_available ){
				is_panel_open = localStorage.getItem('is_panel_open');

				if( is_panel_open == 'true' ){
					$panel.addClass('open');
					$cover.show();
					$body.addClass('cover');
				}else{
					$panel.removeClass('open');
					$cover.hide();
				}

				$('[data-preset=' + localStorage.getItem('current_preset') + ']').addClass('active');
			}

			$preset_list.on('click', function(){
				var
					$this = $(this)
				,	data_preset = $this.data('preset')
				,	data_group = $this.parents('.preset-list').data('group')
				;

				self.ajax_process_import( data_group, data_preset );
			})

			$('.panel-toggle').on('click', function(){
				$panel.toggleClass('open');

				if( $panel.hasClass('open') ){
					localStorage.setItem('is_panel_open', 'true' );
					$cover.fadeIn(300);
					$body.addClass('cover');
				}else{
					localStorage.setItem('is_panel_open', 'false' );
					$cover.fadeOut(300);
					$body.removeClass('cover');
				}
			});

			$panel.tooltip({
				tooltipClass: "custom-tooltip-styling",
				track: true,
				position: { my: "left+15 center", at: "right center" },
				show: { duration: 300, delay: 200 },
				hide: { duration: 0 },
			});
		},
		on_load: function () {
			var
				self = this
			,	$preloader = $('.site-preloader')
			,	$spinner = $('.spinner', $preloader)
			;

			$spinner.fadeOut();
			$preloader.delay(200).fadeOut('slow');
		},
		ajax_process_import: function ( group_id, preset_id ) {
			var
				$preset_spinner = $('.preset-spinner')
			;

			localStorage.setItem('current_preset', preset_id);

			if( this.ajaxImportPresetRequest !== null || !this.ajaxRequestSuccess ){
				this.ajaxImportPresetRequest.abort();
			}

			this.ajaxImportPresetRequest = jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'cherry_preset_import',
					group: group_id,
					preset: preset_id,
					_wpnonce: jQuery('#preset-import-nonce').val()
				},
				cache: false,
				beforeSend: function(){
					this.ajaxRequestSuccess = false;
					$preset_spinner.slideDown(300);
				},
				success: function(response){
					$preset_spinner.delay(400).slideUp(300, function(){ this.ajaxRequestSuccess = true; });
					window.location.reload();
				},
				dataType: 'json'
			});
		},
		is_local_storage_available : function(){
			try {
				return 'localStorage' in window && window['localStorage'] !== null;
			} catch (e) {
				return false;
			}
		}
	}
	CHERRY_API.cherry_preset_swither.init();
}(jQuery));
