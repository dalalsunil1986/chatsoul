Event.observe(window, 'load', StartClient);

//use to initiate the constant for lasttime field in database
var u = 0;
var mk = parseInt(document.getElementById('timetoadd').value);
var g_message = 0;
//for displaying file request
var g_file = 0;
//for checking server used by users
var g_userserver = 0;
//used for detecting if users are typing
var keydowncount = 0;
var keydowntime = 0;
var iskeypressed = false;
var typing = 0;
//flag to prevent double clicking the download button
var flag = true;
//flag used when users try to leave the page
var iflag = false;
//store this loading user to a global variable
var thisuser = $j.trim($F('username'));
//store this chatroom pin
var thispin = $j.trim($F('pincode'));
//store this chatroom channel
var thischannel = '/' + thispin;
var onFileCall = false;
//initiate time for last call in sending file
var lastFileTime = 0;
//use for querying users server on this chatroom
var userCall = false;
//initialize whiteboard vars
//pen size 
var POINT_SIZE = 2;
//color
var POINT_COLOR = '#f00';
var whiteBoard = null;
var X_OFFSET = -5;
var Y_OFFSET = -15;
var lastPointX = -1;
var lastPointY = -1;
var savedPoints = null;
var cursorStatus = true;

/* end of GLOBAL variable initiation*/

//check first if we successfully retrieve the comet server script
if(fm)
{
    var client = fm.websync.client;
    var util = fm.utilities;
    var net = fm.network;
    var thekey = document.getElementById('key').value;
    var channel = thischannel;
        
        //initialize connection
        client.initialize({
        	key: thekey 
             });
             
        //connect the client
        client.connect({
        	   stayConnected: true,
            onSuccess: function(args) {
		confirmEntrance();
                },
            onFailure: function(args) {
            	 removecometuser();
		 $j(window).unbind('beforeunload');
                 window.location = '/changeserver.php';
            },
            onStreamFailure: function(args){
            	 removecometuser();
		 $j(window).unbind('beforeunload');
            	 window.location = '/changeserver.php';
                }
        });
        
        //start subscribing for published data
        client.subscribe({
            channel: channel,
            onSuccess: function(args) {
		confirmEntrance();
               },
            onFailure: function(args) {
            	removecometuser();
		$j(window).unbind('beforeunload');
            	window.location = '/changeserver.php';
            },
            onReceive: function(args) {
                //create a function on what to do
                if (args.data.text)
                {
		    var thedata = args.data.text.split('||');
		    DisplayMessage(thedata[0],thedata[1],thedata[2]);
                }
            }
        });
	
   

    $j(function() {
	
       $j(window).bind('beforeunload', function() {
	    
	    iflag = true;
	    setTimeout(function() {
		setTimeout(function() { 
			    //set the flag back to false
			    iflag = false;
			    confirmEntrance();
			    //re-subscribe user
			    client.subscribe({
				channel: channel,
				onFailure: function(args) {
				    removecometuser();
				    $j(window).unbind('beforeunload');
				    window.location = '/changeserver.php';
				},
				onReceive: function(args) {
				    if (args.data.text)
				    {
					var thedata = args.data.text.split('||');
					DisplayMessage(thedata[0],thedata[1],thedata[2]);
				    }
				}
			    });
		},1000);
	    },1);
	    
	    if (iflag == true){
		client.unsubscribe({
		      channel: channel,
		    });
		new Ajax.Request('/removeuser.php', {
			  method: 'post',
			  parameters: { username: thisuser, pin: thispin, isComet: 'yes' },
			  onSuccess: function(p_xhrResponse) {
			    },
		});
	    }
	    
	    return 'You are about to leave the chatroom.';
	});
	
    });
    
}
else
{
    removecometuser();
    $j(window).unbind('beforeunload');
    window.location = '/changeserver.php';
}



//start observing if users are typing on the keyboard
document.observe('keydown', istyping);
document.observe('keypress',istyping);

//function to be called to unsubscribe the user from this channel
function unsubscribe()
{
   if(fm)
    {
	fm.websync.client.unsubscribe({
	    channel: thischannel,
	    onSuccess: function(args) {
		//util.log('Unsubscribed!');
	    },
	    onFailure: function(args) {
		//util.log('Could not unsubscribe: ' + args.error);
	    }
	});
    }
    else
     return;
}


function istyping(){
    var x = document.getElementById(thisuser +'li');
    if(x)
    {
     x.className = 'usersActiveme';
    }
    
    //this will not go through unless the flag will be set back to false
    if(!iskeypressed){
       var a = new Date();
	keydowntime = a.getTime();
	new Ajax.Request('/updateUserStatus.php',{
	method: 'post',
	parameters: { username: thisuser, istyping: 1, isComet:'yes'}
	});
	//put this flag back to true to stop sending updates to comet server even the user keeps typing                
	iskeypressed = true;
    }

}


//this function will run every second	
function isnottyping()
{
    //perform ajax update
    var b = new Date();
    var z = b.getTime() - keydowntime;
    //even the user keeps typing this will wait till the last pressed key and wait 2 seconds
    if (iskeypressed == true && z >= 2000)
    {
	var x = document.getElementById(thisuser +'li');
	if(x)
	{
	x.className = 'usernameMe';
	}
	
	//update to make the user not typing
	new Ajax.Request('/updateUserStatus.php',{
	method: 'post',
	parameters: { username: thisuser, istyping: 0, isComet:'yes'}
	});
	
	//set this flag back to false to stop sending updates to our current server
	iskeypressed = false;
    }

}



function StartClient( ) {
	
	$j("#text2Chat").Watermark("Type your chat messages here.........");
	
	//notify user that we're loading
	NotifyUser('Loading chatroom...........');
	logEntry();
	
	//initialize whiteboard
	whiteBoard = new jsGraphics('messageCenter');
	whiteBoard.setColor(POINT_COLOR);
	whiteBoard.setStroke(POINT_SIZE);

         
	/* has the username been passed? */
	if ($F('username') != '') {
		Event.observe('loginForm', 'submit', SendMessage);
		Event.observe('submitButton', 'click', SendMessage);
		Event.observe('quitButton', 'click', QuitChat);
		AjaxDisplayMessages();
		//fires every 1 minute to check if a user has been using a regular chat
		g_userserver = setInterval(checkUserServer, 60000);
		//fires every 1 second to query if a user is typing
		typing = setInterval(isnottyping, 1000);
	   
	}
	
}

//function that will add the name of an online user
function AddUser(arg, you, theclass)
{
    $j('#onlineuser').append('<li class="'+ theclass +'" id="'+ arg +'li" >' + arg + you + '</li>');	
}



//Retrieve messages and welcome the user on the first load
function AjaxDisplayMessages( ) {
	
    new Ajax.Request('/get_messages.php', {
	    method: 'post',
	    parameters: { username: thisuser, lasttime: 0, pin: thispin, startTime: 0, isComet: 'yes' },
	    onSuccess: function (p_xhrResponse) {
		/* put the new messages on top */
		new Insertion.Top('message', p_xhrResponse.responseText);
		var d = new Date( );
		    
	    },
	    onFailure: function( ) {
		new Insertion.Top('message', '<p class="errorMessage">ERROR: Could not retrieve messages.</p>');
	    }
    });

}

//function to display chat messages from comet server
function DisplayMessage(x,y,z)
{
	var status = parseInt(x);
	var message = '';
	var user = thisuser;
	
	switch (status)
	{
		    case 1:
		             //just a regular message
			if(y == user)
			{
			   message = '<p class = "usernameMe">you said: '+ z +'</p>\n';
			}
			else
			{
			    message = '<p class ="">'+ y + ' says: '+ z +'</p>\n ';
			}
			new Insertion.Top('message', message);
			
			break;
					   
					
		    case 2:
			//user logging in
			
			if(y != user)
			{
			    message = y + ', entered the chatroom.';
			    notify(message);
			}
	                
	                 break;
						
					
		    case 3:
			//user logging out
			message =  y +', exited the chatroom.';
			notify(message);
			break;
	                 
	            case 4:
	                 //split the third parameter
	                 var v = z.split('|');
	                
	                 if(y == user)
	                 {
	                 	//this is the receiver; show the file comfirmation request
	                 	showFileQue(v[0],v[1],v[2]);
	                 }
	                 else if(v[0] == user)
	                 {
	                 	//this is the file sender; continue checking for files every 5 seconds to keep track of the receiver's response
	                 	g_file = setInterval(keepTracking, 5000);
	                 }
	                 
			break;
						
		    case 5:
	                 
	                 //stop tracking the file receiver's response
	                 if(y == user)
	                 {
	                    clearInterval(g_file);
	                 }
	               
	                 break;
	             
	            case 6:
	                 //add a user on the usercontainer
	                 var splitnames = y.split(',');
	                
			 if($j("li").length > 0){
			    $j("li").remove();
			 }
	                    
			for (i=0; i<splitnames.length; i++)
			{
			    
			      if(splitnames[i] == user)
			      {
				 if(splitnames[i].length > 0){
				       AddUser(splitnames[i],'(You)','usernameMe');
				       }
			      }
			      else
			       {
				 if(splitnames[i].length > 0){
				       AddUser(splitnames[i],'','user');
				       }
			       }
			}

                        break;
	              
	            case 7:
	                 //remove the user on the user container
	                 if(y != user)
	                 {
	                    $j("li").remove('"#'+ y +'li"');
	                 }
			
			break;
						
		    case 8:
						  
			//update user's typing status
			if(y != user)
	                 {
    
			    var num = parseInt(z);
		      
			    var b = document.getElementById(y + 'li');
			    
			    if (num == 0)
			    {
				  if (b)
				   {
				     b.className = 'user';
				   }	
			    }
			    else
			    {
				  if(b)
				    {
				      b.className = 'usersActive';
				    }
			     }
		      
			  }
						 
			break;
		    
		    case 9:
		      
		      if(y != user)
		      {
			    
			    var thepoints = z.split('*');
			    for( i = 0, k = thepoints.length; i<k; i++)
			    {
			        if(!i)
				{
				    var p = thepoints[i].split(',');
				    var a = parseInt(p[0]);
				    var b = parseInt(p[1]);
				    whiteBoard.drawLine(a,b,a,b);
				}
				else
				{
				    //get the earlier values
				    var xi = thepoints[i-1].split(',');
				    var ai = parseInt(xi[0]);
				    var bi = parseInt(xi[1]);
				    var p = thepoints[i].split(',');
				    var a = parseInt(p[0]);
				    var b = parseInt(p[1]);
				    
				    whiteBoard.drawLine(ai,bi,a,b);	
				}
				
			    }
			    
			    whiteBoard.paint();
			    //notify other people about this user's action	
			    message = y + ', draw on the whiteboard.';
			    shortnotify(message);
			}
						  
			break;
						  
		    case 10:
						   
			if(y != user)
	                  {
	                  
			    if(whiteBoard)
			    {
			      //clear the white board
			      whiteBoard.clear();
			      //notify other people about this user's action	
			      message = y + ', cleared the whiteboard.';
			       shortnotify(message);
			     }
	                  
	                  }
	                
			break;
			    
		    default:
			
			if(y == user)
	                 {
		            message = '<p class = "usernameMe">you said: '+ z +'</p>\n';
	                 }
	                else
	                 {
	                    message = '<p class ="">'+ y + ' says: '+ z +'</p>\n ';
	                 }
	                 
			 new Insertion.Top('message', message);
	                 break;
	}

}


//display the past messages
function DisplayPastMessages()
{
	NotifyUser('Processing');
	$j('#message p').empty();
	
	var d = new Date( );
	var currentTime = d.getTime( );
				
	new Ajax.Request('/get_pastmessages.php', {
			method: 'post',
			parameters: { username: thisuser, lasttime: currentTime, pin: thispin  },
			onSuccess: function (p_xhrResponse) {
				/* put the new messages on top */
				StopNotify();
				new Insertion.Top('message', p_xhrResponse.responseText);
				
			},
			onFailure: function( ) {
				    StopNotify();
				    new Insertion.Top('message', '<p class="errorMessage">ERROR: Could not retrieve messages.</p>');
			}
	});
	
	return false;
}


//function that will run every 30 seconds if a chatroom member uses chat.js or regular ajax
function checkUserServer()
{
   if (!userCall)
    {
		userCall = true;
		
		/* make a new request to the server for messages it has in its queue */
		new Ajax.Request('/checkuserserver.php',
		{
			method: 'post',
			parameters:
			        {
				    username: thisuser,
				    pin: thispin
				},
			
			onSuccess: function (p_xhrResponse)
			        {
				    var x = parseInt(p_xhrResponse.responseText);
				    if(x == 1)
				    {   
					removecometuser();
					//undo the beforeunload event we binded on the window
					$j(window).unbind('beforeunload');
					window.location = '/changeserver.php';
				    }
				    
				    var d = new Date( );
				    //return the flag to false
				    userCall = false;
				
				},
			
			onFailure: function( )
			        {
				    //return the flag to false
				    userCall = false;
			        }
		});
    }	
}

//called if a user has a request for file transfer
function showFileQue(x,y,v)
{
	         
    var cleanId = $j.trim(y);
    var b = parseInt(cleanId);
    flag = false;
    $j.confirm({ 
		     'title'    : 'File Download Confirmation',
		     
		     'message'	: x + " wish to send you a file "+"'"+ v +"'" +". Do you want to download the file?",
		     
		     'buttons'	: {
			  
				    'Yes' : {
					  'class'	: 'blue',
					      'action': function(){
						  if (!flag){ 
							flag = true;
							new Ajax.Request('/downloading.php',{
							    method: 'post',
							    parameters: { file_id: b }
							});
							window.open("/downloadfile.php?file_id=" + cleanId +
								    "&isComet=yes","download","width=600,height=500,resizable=no,scrollbars=yes,location=no");
						    } 
						}
					     },
				     'No' : {
					      'class'	: 'gray',
					      'action': function(){
						      if (!flag)
						      {
							flag = true;
							new Ajax.Request('/delete_file.php?isComet=yes',{
								method: 'post',
								parameters: { file_id: b}
							});
						       } 
						    }	
					    }
		                    }
	      });
}	




//function that will display file transfer request
function keepTracking()
{
	
	if( !onFileCall)
	{
	    onFileCall = true;
	    //get a new date to pass as a param
	    var d = new Date( );
	    lastFileTime = d.getTime();
	    
	    new Ajax.Request('/get_file.php',{
		    method: 'post',
		    parameters: {username: thisuser, lasttime: lastFileTime, isComet: 'yes'},
		    onSuccess: function ( response ){
			    onFileCall = false;
		    },
		    onFailure: function() {
			    onFileCall = false;
		    }
	    });
	}
	
}



/**
 * @param {Object} e The event object that triggered this event.
 */
function SendMessage(e) {
	/* do not let the event continue beyond this point */
	Event.stop(e);
	NotifyUser('Sending message');
	$j.Watermark.HideAll();
	var d = new Date( );
	u = d.getTime() + mk;
	/* make an Ajax request to the server with the new message */
	new Ajax.Request('/put_message.php', {
		method: 'post',
		parameters: {
			message: $F('text2Chat'),
			username: thisuser,
			lasttime: u,
			pin: thispin,
			isComet: 'yes'
		},
		onSuccess: function(p_xhrResponse) {
			$('text2Chat').value = '';
			/* was the send unsuccessful? */
			StopNotify();
			if (p_xhrResponse.responseText != 1)
				new Insertion.Top('message', '<p class="errorMessage">ERROR: Could not send message.</p>');
				
		},
		onFailure: function( ) {
			$('text2Chat').value = '';
			StopNotify();
			new Insertion.Top('message', '<p class="errorMessage">ERROR: Could not send message.</p>');
		}
	});
}

//function that logs the entry of this user
function logEntry()
{
	 
    var d = new Date( );
    new Ajax.Request('/logentry.php', {
	   method: 'post',
	   parameters: { username: thisuser, lasttime: d.getTime( ), pin: thispin, isComet: 'yes' },
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
	
	if(x)
	{
	    
	    $j(window).unbind('beforeunload');
	    
	    NotifyLogOff('Please wait........logging off');
	    
	    unsubscribe();
	    
	    Event.stop(e);
	    
	    var d = new Date( );
	    
	    new Ajax.Request('/logout.php', {
		   method: 'post',
		   parameters: { username: thisuser, lasttime: d.getTime( ), pin: thispin, isComet: 'yes' },
		   onSuccess: function(p_xhrResponse) {
			   window.location = '/';
		       },
		   onFailure: function( ) {
		       window.location = '/';
		   }
	   });
		
	}
		
}
		


 $j.confirm = function(params){
    
    if($j('#confirmOverlay').length){
	return false;
    }
    var buttonHTML = '';
    $j.each(params.buttons,function(name,obj){
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
	
//clear messages on the box
function ClearMessages()
{
  $j('#message p').empty();
   if(whiteBoard)
    {
      whiteBoard.clear();
    }
    clearPoints();
    return false;
		
}
		

	
//start dialog for file sharing request
function showframe() 
{
    $j.modal('<h3>File Transfer Window</h3><iframe scrolling="no" frameborder="0" hidefocus="true" style="text-align:center;vertical-align:middle;border-style:none;margin:0px;width:100%;height:350px" src="/sendform.php?isComet=yes&nocache='+ Math.random +'" ></iframe>');
    return false;
}

//used to display the growl-like notification when a user exist	
function notify(arg)
{
    $j.gritter.add({
	title: 'Alert!!',
	text: arg,
	time: 10000
    });
}	

//used to display the growl-like notification when a user exist	
function shortnotify(arg)
{
	$j.gritter.add({
	    title: 'Alert!!',
	    text: arg,
	    time: 2000
	});
}	
	
//notify user if server is still on process	
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
 
 //stop notification   
function StopNotify()
{
    var lock = document.getElementById("skm_LockPane");
    if (lock)
       lock.className = "LockOff";
}

  
//confirm users entrance on the page
function confirmEntrance()
{
  	
  	new Ajax.Request('/adduser.php', {
		method: 'post',
		parameters: { username: thisuser, pin: thispin, isComet: 'yes' },
		onSuccess: function(p_xhrResponse) {
			},
		onFailure: function( ) {
		    }
	});
	
}

function removecometuser()
{
    new Ajax.Request('/removeuser.php', {
	method: 'post',
	parameters: { username: thisuser, pin: thispin, isComet: 'yes' },
	onSuccess: function(p_xhrResponse) {},
	onFailure: function( ) {}
    });	
}

//whiteboard functions
function StartDrawing(e) {
    if (Event.element(e).id == 'message') {
	DrawPoint(Event.pointerX(e), Event.pointerY(e));
	Event.observe('message', 'mousemove', ContinueDrawing);
    }
}


function StopDrawing(e){
    Event.stopObserving('message', 'mousemove', ContinueDrawing);
    lastPointX = -1;
    lastPointY = -1;
    //publish the points
    SendPoints();
}

function ContinueDrawing(e){
    if (Event.element(e).id == 'message')
       DrawPoint(Event.pointerX(e), Event.pointerY(e));
}

function SavePoints(px,py)
{
  if(savedPoints != null)
  {
   savedPoints += '*';
  }
  savedPoints += px.toString() + ',' + py.toString();	
}

function DrawPoint(p_x, p_y){
    p_x = p_x - (X_OFFSET + POINT_SIZE);
    p_y = p_y - (Y_OFFSET + POINT_SIZE);
    //save this point and concatenate to a string
    SavePoints(p_x,p_y);
    if (lastPointX == -1 || lastPointY == -1) {
    lastPointX = p_x;
    lastPointY = p_y;
    }
    whiteBoard.drawLine(p_x, p_y, lastPointX, lastPointY);
    whiteBoard.paint();
    lastPointX = p_x;
    lastPointY = p_y;
}

function SendPoints(points)
{
	if(!points)
	{
	    points = savedPoints;
	    savedPoints = null;
	}
	
	fm.websync.client.publish({
            channel: thischannel,
            data: {
            	
                text: '9||' + thisuser + '||' + points 
            },
            onSuccess: function(args) { 
            },
            onFailure: function(args) {
            	SendPoints(points); 
            }
        });
}

function clearPoints()
{
    if(fm)
    {
	fm.websync.client.publish({
		    channel: thischannel,
		    data: {
			text: '10||' + thisuser + '||clear whiteboard'
		    },
		    onSuccess: function(args) {},
		    onFailure: function(args) {
			clearPoints(); 
		    }
	});
    }
	
}

$j(function() {
    
    $j("#message").mousedown(
	function(event) {
	    StartDrawing(event);
	    $j(this).css("cursor","url(../Images/pen.png),auto");
	    $j("#messageCenter").css("cursor","url(../Images/pen.png),auto");
	}
    );
    
    $j("#message").mouseup(
	function(event) {
	    $j(this).css("cursor","default");
	    $j("#messageCenter").css("cursor","default");
	    StopDrawing(event);
	}
    );
    
});



