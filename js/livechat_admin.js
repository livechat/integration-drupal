(function (Drupal, $) {

	"use strict";
	
	Drupal.behaviors.livechat_admin = {
		attach: function (context, settings) {
			
			var showNotification = function () {
				$('.notification').css('visibility','visible');
				setTimeout(function () {
					$('.notification').addClass("fadeout");
				},1000);
				setTimeout(function () {
					$('.notification').css('visibility','hidden');
					$('.notification').removeClass("fadeout");
				},1300);
			};
			
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

			if (!(props.mobile === '0' || props.mobile === null)) {
				$('#livechat_mobile').val(props.mobile);
			}
						
			$('.advanced_options').on('change', function() {
				var mobile = $('#livechat_mobile').val();
				$('#advanced_settings').addClass('disable_advanced_settings');
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: settings.livechat.livechat_admin.save_properties_url,
					data: {
						mobile: mobile
					}
				}).done(function(){
					showNotification();
					$('#advanced_settings').removeClass('disable_advanced_settings');
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
				try {
					var livechatMessage = JSON.parse(event.data);
				} catch (e) {
					return false;
				}

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