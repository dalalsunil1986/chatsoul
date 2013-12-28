        Event.observe(window, 'load', StartClient);
		
	function StartClient( )
	{
	  Event.observe('loginForm', 'submit', CheckUsername);
	  Event.observe('submitButton', 'click', CheckUsername);
	}
	
        
       function CheckUsername(e)
       {
	       Event.stop(e);
		/* Should we even bother requesting anything? */
		if ($F('pincode') != '')
		{
			var redirectUrl = $F('hiddenUrl');
			var d = new Date( );
			var g_lastTime = d.getTime( );
			new Ajax.Request('user_creation.php', {
			
			method: 'post',
			parameters: { pin: $F('pincode') },
			onSuccess: function(p_xhrResponse) {
			var a = parseInt(p_xhrResponse.responseText);
			
				switch (a)
				{
					
					case 1:
						alert('Welcome !!!' );
						window.location = redirectUrl;
						break;
					
					case 2:
						
						alert('The chatroom id you entered is invalid.  Please enter a valid chatroom id.');
						$('pincode').focus( );
						break;
					
					case 3:
						
						alert('Sorry, this chatroom id does not exist anymore. You can start a new chatroom by clicking the button above thereby generating a new id number.');
						$('pincode').focus( );
						break;
					
					case 4:
					   
					   alert('Something unexpected happened while logging you in.  Please try again later.');
						$('pincode').focus( );
						break;
						
					default:
					    
					    alert('Something unexpected happened while logging you in.  Please try again later.');
						  $('pincode').focus( );
						  break;
						
				
				}
			 
			  return false;
			},
			onFailure: function(p_xhrResponse) {
				alert('There was an error while logging you in:\n\n' + p_xhrResponse.statusText);
				$('pincode').focus( );
				return false;
			}
		   });
		}
		else
		{
			alert('Oooops, enter a valid chatroom id number.');
			return false;
		}
	}
	
	