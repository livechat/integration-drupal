(function (Drupal, $) {

	"use strict";
	
	Drupal.behaviors.livechat_admin = {
		attach: function (context, settings) {
			
			var logoutLiveChat = function () {
				sendMessage('logout');
			};

			var login_with_livechat = document.getElementById('login-with-livechat');

			var props = settings.livechat.livechat_admin.livechat_props;

			if (!(props.licence_number === '0' || props.licence_number === '' || props.licence_number === null)) {
				$('#login_panel').hide();
				$('#admin_panel').show();
			}
			
			if (!(props.login === '0' || props.login === null)) {
				$('#livechat_login').text(props.login);
			}

			if (!(props.sounds === '0' || props.sounds === null)) {
				$('#livechat_sounds').val(props.sounds);
			}

			if (!(props.mobile === '0' || props.mobile === null)) {
				$('#livechat_mobile').val(props.mobile);
			}
			
			$('#livechat_mobile').on('change', function() {
				
				var mobile = $('#livechat_mobile').val();
				var sounds = $('#livechat_sounds').val();
				
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: settings.livechat.livechat_admin.save_properties_url,
					data: {
						mobile: mobile,
						sounds: sounds
					}
				});
			});
			
			$('#livechat_sounds').on('change', function() {
				var mobile = $('#livechat_mobile').val();
				var sounds = $('#livechat_sounds').val();
				
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: settings.livechat.livechat_admin.save_properties_url,
					data: {
						mobile: mobile,
						sounds: sounds
					}
				});
			});
			
			$('#reset_settings').on('click', function () {
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: settings.livechat.livechat_admin.reset_properties_url,
				}).done(function(){
					logoutLiveChat();
					
					$('#login_panel').show();
					$('#admin_panel').hide();
					$('iframe#login-with-livechat').removeClass('hidden');
					$('.progress-button').addClass('hidden');
				});
				
			});

			var sendMessage = function (msg) {
				login_with_livechat.contentWindow.postMessage(msg, '*');
			};

			var logoutLiveChat = function () {
				sendMessage('logout');
			};

			function receiveMessage(event) {
				var livechatMessage = JSON.parse(event.data);

				if (livechatMessage.type === 'logged-in' && livechatMessage.eventTrigger === 'click') {

					$('#login_panel').hide();
					$('#admin_panel').show();
					$('iframe#login-with-livechat').addClass('hidden');
					$('.progress-button').removeClass('hidden');
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: settings.livechat.livechat_admin.save_license_url,
						data: {

							email: livechatMessage.email,
							license: livechatMessage.license
						}
					});

					$('#livechat_license_number').val(livechatMessage.license);
					$('#livechat_login').val(livechatMessage.email);

					$('#livechat_already_have form')
							.unbind('submit')
							.submit();
				}
			}

			window.addEventListener("message", receiveMessage, false);
		}
	};

})(Drupal, jQuery);