
Event.observe(window, 'load', StartClient);


/*start of variables*/


//This variable, g_message, will control the interval for getting messages from the server
var g_message = 0;

//for displaying file request
var g_file = 0;

var keydowncount = 0;
var keydowntime = 0;
var iskeypressed = false;
var typing = 0;

//check if a button is still clicked
var flag = true;

/* This variable, g_lastTime, keeps track of the last request for new messages */
var g_lastTime = 0;

/* This variable, g_onCall, tracks whether there already is a request going or not */
var g_onCall = false;

//check if there's a pending file request
var onFileCall = false;

var lastFileTime = 0;
var startval = 0;

//store this loading user to a global variable
var thisuser = $j.trim($F('username'));
//store this chatroom pin
var thispin = $j.trim($F('pincode'));


/*End Of Variables*/

//document.observe('keydown', istyping);
//document.observe('keypress',istyping);


function istyping(){
	//perform ajax update
	
	if (keydowncount == 2)
	   {
	   //back to 0
		keydowncount = 0;
		//create a new time everytime a key pressed
		var a = new Date();
		keydowntime = a.getTime();
		iskeypressed = true;
		//update the users status to typing
		new Ajax.Request('/updateUserStatus.php',{
							method: 'post',
						   parameters: { username: thisuser, istyping: 1}
                });
      }
		else
		{
			//haven't finished pressing the whole key
		keydowncount = keydowncount + 1;
		
		}
	
}
	
function isnottyping()
{
	//perform ajax update
	var b = new Date();
	var z = b.getTime() - keydowntime;
	if (iskeypressed == true && z >= 1000){
	//update to make the user not typing
	new Ajax.Request('/updateUserStatus.php',{
							method: 'post',
						   parameters: { username: thisuser, istyping: 0}
});
iskeypressed = false;
}
}


/**
 * This function, StartClient, adds events to controls on the page.
 */
function StartClient( ) {
	//notify user that we're loading
	NotifyUser('Loading');
	
	
	/* has the username been passed? */
	if ($F('username') != '') {
		Event.observe('loginForm', 'submit', SendMessage);
		Event.observe('submitButton', 'click', SendMessage);
		Event.observe('quitButton', 'click', QuitChat);
		
	//updates the online user
	new Ajax.PeriodicalUpdater('usernameContainer', '/get_users.php', {
		method: 'post',
		parameters: { username: thisuser, pin: thispin },
			frequency: 4
		});
		
		//fires every 5 seconds to query for file uploads done
		g_file = setInterval(AjaxDisplayFiles, 5000);
		//fires every 0.5 second to query for new messages
		g_message = setInterval(AjaxDisplayMessages, 500);
		
		//fires every 1 second to query if a user is typing
		//typing = setInterval(isnottyping, 1000);
		
		//logenry for this user
	   logEntry();
	
	}
	
}


/**
 * This function, AjaxDisplayMessages, checks the server for messages it has
 * in queue since the last time it was queried and adds new messages to the top
 * of the message container.
 */
function AjaxDisplayMessages( ) {
	/* is there already a request going? */
	if (!g_onCall) {
		g_onCall = true;
		if(g_lastTime == 0)
		{
			var q = new Date();
			startval = q.getTime();
		}
		/* make a new request to the server for messages it has in its queue */
		new Ajax.Request('/get_messages.php', {
			method: 'post',
			parameters: { username: thisuser, lasttime: g_lastTime, pin: thispin, startTime: startval  },
			onSuccess: function (p_xhrResponse) {
				/* put the new messages on top */
				new Insertion.Top('messageCenter', p_xhrResponse.responseText);
				var d = new Date( );
				/* change the time of the last request */
				g_lastTime = d.getTime( );
				g_onCall = false;
			},
			onFailure: function( ) {
				new Insertion.Top('messageCenter', '<p class="errorMessage">ERROR: Could not retrieve messages.</p>');
				g_onCall = false;
			}
		});
	}
	
}


function DisplayPastMessages(){
	NotifyUser('Processing');
	$j('#messageCenter').empty();
	
	         var d = new Date( );
				var currentTime = d.getTime( );
				
	new Ajax.Request('/get_pastmessages.php', {
			method: 'post',
			parameters: { username: thisuser, lasttime: currentTime, pin: thispin  },
			onSuccess: function (p_xhrResponse) {
				/* put the new messages on top */
				StopNotify();
				new Insertion.Top('messageCenter', p_xhrResponse.responseText);
				
			},
			onFailure: function( ) {
				StopNotify();
				new Insertion.Top('messageCenter', '<p class="errorMessage">ERROR: Could not retrieve messages.</p>');
				}
		});
	
	return false;
}

//function that'll run every 5 seconds to check for file transfer request
function AjaxDisplayFiles(){
	
	if( !onFileCall){
		onFileCall = true;
		new Ajax.Request('/get_file.php',{
			method: 'post',
			parameters: {username: thisuser, lasttime: lastFileTime},
			onSuccess: function ( response ){
				//do we got an ajax response?
				var x = response.responseText.evalJSON();
				var a = parseInt(x[0]);
				var b = parseInt(x[1]);
				var c = parseInt(x[2]);
				
				if (a != 0 && b != 0 && c != 0){
				flag = false;
            $j.confirm({ 
			              'title'		: 'File Download Confirmation',
			               'message'	: x[0] + " wish to send you a file "+"'"+x[2]+"'" +". Do you want to download the file?",
			               'buttons'	: {
				            'Yes'	: {
					          'class'	: 'blue',
					          'action': function(){
					              if (!flag){
					              	//set the flag back to true
					              	flag = true;
					                  //add a value inorder for this row not to be queried again
							            var d = new Date( );
				                    lastFileTime = d.getTime() + 60000;
				                    	
				                    	//update the file to notify that it is being accepted and downloaded
						              new Ajax.Request('/downloading.php',{
								            method: 'post',
								            parameters: { file_id: b}
							                    });
							            //download the file      
						              window.open("/downloadfile.php?file_id=" + x[1],"download","width=600,height=500,resizable=no,scrollbars=yes,location=no");
						        }      
						            
					}
				},
				           'No'	: {
					         'class'	: 'gray',
					         'action': function(){
						if(!flag){
							      
							      flag = true;
						         new Ajax.Request('/delete_file.php',{
								            method: 'post',
								            parameters: { file_id: b}
							                    });
							                    
							            var d = new Date( );
							           lastFileTime = d.getTime() + 60000;
							           
							           }
							           }	
				               }
			             }
		        });
 
					
                
					}
				else
				{
				
				var d = new Date( );
				lastFileTime = d.getTime();
				
				}
				
				onFileCall = false;
				
			},
			onFailure: function() {
				onFileCall = false;
			}
			});
		}
	
}



/**
 * This function, SendMessage, sends the text taken from the text box to
 * the server to be inserted in the messages queue.
 *
 * @param {Object} e The event object that triggered this event.
 */
function SendMessage(e) {
	/* do not let the event continue beyond this point */
	Event.stop(e);
	$j.Watermark.HideAll();
	//NotifyUser('Sending message');
	var d = new Date( );
	/* make an Ajax request to the server with the new message */
	new Ajax.Request('/put_message.php', {
		method: 'post',
		parameters: {
			message: $F('text2Chat'),
			username: thisuser,
			lasttime: d.getTime( ),
			pin: thispin
		},
		onSuccess: function(p_xhrResponse) {
			$('text2Chat').value = '';
			/* was the send unsuccessful? */
				//StopNotify();
			if (p_xhrResponse.responseText != 1)
				new Insertion.Top('messageCenter', '<p class="errorMessage">ERROR: Could not send message.</p>');
			
		},
		onFailure: function( ) {
			$('text2Chat').value = '';
			//StopNotify();
			new Insertion.Top('messageCenter', '<p class="errorMessage">ERROR: Could not send message.</p>');
			
		}
	});
}


//function that logs the entry of this user
function logEntry(){
	 
	 var d = new Date( );
	
	new Ajax.Request('/logentry.php', {
		method: 'post',
		parameters: { username: thisuser, lasttime: d.getTime( ), pin: thispin },
		onSuccess: function(p_xhrResponse) {
			StopNotify();
			
		},
		onFailure: function( ) {
			StopNotify();		
					
		}
	});
	
}


function QuitChat(e) 
{
	var x = window.confirm('Are you sure you want to log-out?');
	if(x){
		
		Event.stop(e);
	   var d = new Date( );
	   NotifyLogOff('Please wait........logging off');
	 
	 new Ajax.Request('/logout.php', {
		method: 'post',
		parameters: { username: thisuser, lasttime: d.getTime( ), pin: thispin },
		onSuccess: function(p_xhrResponse) {
			
			window.location = '/';
	
		},
		onFailure: function( ) {
					
					window.location = '/';
		}
	});
		
		}
}
		
//handles opening another small window for sending file
function newwindow() 
{ 
window.open("/sendform.php", "jav", "width=600,height=380,resizable=no,scrollbars=yes,location=no")
}



		 
//jquery confirm dialog
$j.confirm = function(params){
 
		if($j('#confirmOverlay').length){
			// A confirm is already shown on the page:
			return false;
		}

		var buttonHTML = '';
		$j.each(params.buttons,function(name,obj){

			// Generating the markup for the buttons:

			buttonHTML += '<a href="#" class="button '+obj['class']+'">'+name+'<span></span></a>';

			if(!obj.action){
				obj.action = function(){};
			}
		});

		var markup = [
			'<div id="confirmOverlay">',
			'<div id="confirmBox">',
			'<h1>',params.title,'</h1>',
			'<p>',params.message,'</p>',
			'<div id="confirmButtons">',
			buttonHTML,
			'</div></div></div>'
		].join('');

		$j(markup).hide().appendTo('body').fadeIn();

		var buttons = $j('#confirmBox .button'),
			i = 0;

		$j.each(params.buttons,function(name,obj){
			buttons.eq(i++).click(function(){

				// Calling the action attribute when a
				// click occurs, and hiding the confirm.

				obj.action();
				$j.confirm.hide();
				return false;
			});
		});
	}

	$j.confirm.hide = function(){
		$j('#confirmOverlay').fadeOut(function(){
			$j(this).remove();
		});
	}
	
	//clear messages
function ClearMessages()
{
		
		$j('#messageCenter').empty();
		$j('#text2Chat').focus()
		return false;
		
}
	
		
	//start of alternative dialog for file sharing
function showframe()
{
		        $j.modal('<h3>File Transfer Window</h3><iframe scrolling="no" frameborder="0" hidefocus="true" style="text-align:center;vertical-align:middle;border-style:none;margin:0px;width:100%;height:350px" src="/sendform.php" ></iframe>');
                return false;
}


            
function NotifyUser(arg) 
{
        var lock = document.getElementById("skm_LockPane");
        if (lock)
            lock.className = "LockOn";
        lock.innerHTML = '<span style="font-size:16px">' + arg + '............' + '</span>';
}

//notification while logging off
function NotifyLogOff(arg)
{

var lock = document.getElementById("skm_LockPane");
if (lock)
    lock.className = "LogoffOn";
    lock.innerHTML = '<span style="font-size:16px">' + arg + '............' + '</span>';
	
}
    
function StopNotify()
{
var lock = document.getElementById("skm_LockPane");
if (lock)
   lock.className = "LockOff";
}           
