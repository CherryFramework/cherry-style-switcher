/**
 * cherry_preset_swither
 */
(function($){
	"use strict";

	CHERRY_API.utilites.namespace('cherry_preset_swither');
	CHERRY_API.cherry_preset_swither = {
		ajaxRequestSuccess: true, // ajax success check
		ajaxImportPresetRequest: null, // ajax success check
		activePresetsObject: {}, // presets object
		init: function () {
			var self = this;

			if ( CHERRY_API.status.is_ready ) {
				self.render();
			} else {
				CHERRY_API.variable.$document.on('ready', self.on_ready() );
			}

			if ( CHERRY_API.status.on_load ) {
				self.render();
			} else {
				CHERRY_API.variable.$window.on('load', self.on_load() );
			}
		},
		on_ready: function () {
			var self = this,
				$panel = $('.style-switcher-panel'),
				panelWidth = $panel.width(),
				$presetList = $('.preset-list li'),
				$panelToggle = $('.panel-toggle', $panel),
				$cover = $('.site-cover'),
				$body = $('body'),
				isPanelOpen = 'false';

			if ( this.isLocalStorageAvailable ) {
				isPanelOpen = localStorage.getItem('is_panel_open');
				panelWidth = $panel.width();

				if ( isPanelOpen == 'true' ) {
					$panel.addClass('open');
					$panel.css({'right':0});

					$cover.show();

					if ( $('.style-switcher-panel')[0] ) {
						$body.addClass('cover');
					}

				} else {
					$panel.removeClass('open');
					$panel.css({'right': panelWidth * -1 });
					$cover.hide();
				}

				if ( localStorage.getItem( 'active_presets' ) !== 'null' ) {
					self.activePresetsObject = $.parseJSON( localStorage.getItem( 'active_presets' ) );
					self.selectCurrentPresets( self.activePresetsObject );
				}
			}

			$presetList.on('click', function(){
				var $this = $(this),
					data_group = $this.parents('.preset-list').data('group'),
					data_preset = $this.data('preset');

				if ( ! $this.hasClass('coming-soon') ) {

					// update activePresetsObject
					self.activePresetsObject[ data_group ] = data_preset;

					// select current item
					self.selectCurrentPresets( self.activePresetsObject );

					self.ajax_process_import( data_group, data_preset );
				}
			})

			$panelToggle.on('click', function() {
				$panel.toggleClass('open');
				panelWidth = $panel.width();

				if ( $panel.hasClass('open') ) {
					localStorage.setItem('is_panel_open', 'true' );
					$cover.fadeIn(300);
					$body.addClass('cover');
					$panel.css({'right':0});
				} else {
					localStorage.setItem('is_panel_open', 'false' );
					$cover.fadeOut(300);
					$panel.css({'right': panelWidth * -1 });
					$body.removeClass('cover');
				}
			});

			CHERRY_API.variable.$window.on('resize.style_switcher_panel', function() {
				var panelWidth = $panel.width();
				if ( ! $panel.hasClass('open') ) {
					$panel.css({'right': panelWidth * -1 });
				}
			})

			$panel.tooltip({
				tooltipClass: "custom-tooltip-styling",
				track: true,
				position: { my: "left+15 center", at: "right center" },
				show: { duration: 300, delay: 200 },
				hide: { duration: 0 },
			});
		},
		on_load: function () {
			var self = this,
				$preloader = $('.site-preloader'),
				$spinner = $('.spinner', $preloader);

			$spinner.fadeOut();
			$preloader.delay(200).fadeOut('slow');
		},
		selectCurrentPresets: function ( active_presets ) {

			// set localStorage active_presets
			localStorage.setItem( 'active_presets', $.toJSON( active_presets ) );

			for ( var group in active_presets ) {
				if ( active_presets.hasOwnProperty( group ) ) {
					var $group = $('[data-group=' + group + ']');

					$('li', $group).removeClass('active');
					$('[data-preset=' + active_presets[ group ] + ']', $group).addClass('active');
				}
			}
		},
		ajax_process_import: function ( group_id, preset_id ) {
			var $preset_spinner = $('.preset-spinner');

			localStorage.setItem('current_preset', preset_id);

			if ( this.ajaxImportPresetRequest !== null || !this.ajaxRequestSuccess ) {
				this.ajaxImportPresetRequest.abort();
			}

			this.ajaxImportPresetRequest = jQuery.ajax({
				type: 'POST',
				url: preset_import_ajax.url,
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
				success: function( response ) {
					$preset_spinner.delay(400).slideUp(300, function(){ this.ajaxRequestSuccess = true; });
					document.location.replace( response.url );
					window.location.reload();
				},
				dataType: 'json'
			});
		},
		isLocalStorageAvailable : function(){
			try {
				return 'localStorage' in window && window['localStorage'] !== null;
			} catch (e) {
				return false;
			}
		}
	}
	CHERRY_API.cherry_preset_swither.init();
}(jQuery));
