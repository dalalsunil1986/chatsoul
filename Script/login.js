Event.observe(window, 'load', StartClient);
		
		function StartClient( ) {
	Event.observe('loginForm', 'submit', CheckUsername);
	Event.observe('submitButton', 'click', CheckUsername);
		}
		
		function CheckUsername(e) {
			Event.stop(e);
	/* Should we even bother requesting anything? */
	if ($F('nptUsername') != '') {
		var d = new Date( );
		var g_lastTime = d.getTime( );
		new Ajax.Request('login.php', {
			method: 'post',
			parameters: { username: $F('nptUsername'), lasttime: g_lastTime },
			onSuccess: function(p_xhrResponse) {
				var a = parseInt(p_xhrResponse.responseText);
				//alert (a);
				
				switch (a) {
					
					case 1:
						alert('Welcome ' + $F('nptUsername') + ' .');
						window.location = 'chat.php';
						break;
					
					case 2:
						
						alert('This username is in use.  Please try another one.');
						$('nptUsername').focus( );
						break;
					
					case 3:
						
						alert('Refrain from vulgarity in the username.  Thank you.');
						$('nptUsername').focus( );
						break;
					
					case 4:
					   
					   alert('Something unexpected happened while logging you in.  Please try again later.');
						$('nptUsername').focus( );
						break;
						
				   default:
				       
				        alert('Something unexpected happened while logging you in.  Please try again later.');
						  $('nptUsername').focus( );
						  break;
						
				
				}
				return (false);
			},
			onFailure: function(p_xhrResponse) {
				alert('There was an error while logging you in:\n\n' + p_xhrResponse.statusText);
				$('nptUsername').focus( );
				return (false);
			}
		});
	} else {
		alert('Enter in a valid username before clicking the button.');
		return (false);
	}
}