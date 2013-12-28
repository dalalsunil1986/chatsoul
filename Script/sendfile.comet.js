    var PROGRESS_INTERVAL = 500;
      var PROGRESS_COLOR = 'green';
      
      var _divUploadMessage;
      var _divUploadProgress;
      var _notifier;
     
      var _loopCounter = 1;
      var _maxLoop = 10;
      var _photoUploadProgressTimer;
      
      //variables for ajax checking for response
      var checkFile = 0;
      var oncheckFileCall;
      
      //id of the file from the database
      var fileId;
      
      //variable to store the name of the receiver
      var username;
      
      function AjaxCheck(){
      	if(!oncheckFileCall) {
      		oncheckFileCall = true;
      		new Ajax.Request('check_file.php',{
      			method: 'post',
      			parameters: {file_id: fileId},
      			onSuccess: function(response){
      				var y = response.responseText.split('||');
      				var x = parseInt(y[0]);
      				if(x == 1)
      				//still waiting
      				{
      					waitingResponse("Waiting a response from <span style='color:blue;font-weight:bold'>" + username + "</span>........");
      					}
      					else if (x == 2)
      					//if the file is being downloaded
      					{
      						waitingResponse("Transferring file to <span style='color:blue;font-weight:bold'>" + username + "</span>.<br /><span style='color:red;padding-top:15px'>" + y[1] + "% transferred..................</span>");
      					}
      				else if (x == 0)
      				//code goes here if file is rejected
      				{
      					responseMade("Ooops, <span style='color:blue;font-weight:bold'>" + username + "</span> rejected the file.");
      					clearInterval(checkFile);
      					}
      					else if (x == 3)
      				//incomplete download
      				  {
      					responseMade("Oooops, your file was not completely downloaded.<br /><br />Click <a href='sendform.php?isComet=yes&nocache="+ Math.random() +"'>here</a> to try to send again.");
      					clearInterval(checkFile);
      					}
      					else if (x == 4)
      					{
      					responseMade("Oooops, looks like the receiver for your file has been <b>unresponsive</b> for quite a while.<br /><br />Click <a href='sendform.php?isComet=yes&nocache="+ Math.random() +"'>here</a> to send again.");	
      					clearInterval(checkFile);
      					}
      				else if (x == -1)
      				//code goes here if file is accepted
      				{
      					responseMade("Cool, file transfer is completed.");
      					clearInterval(checkFile);
      					}
      					else if (x == -2)
      					{
      						usererr(username);
      						clearInterval(checkFile);
      					}
      				oncheckFileCall = false;
      			},
      			onFailure: function(){
      				oncheckFileCall = false;
      				}
      				});
      		
      		}
      	
      	}
      
      
      //handles for general problem
		function starterr(){
		var div = document.getElementById('contentWrapper');
		div.innerHTML = "<p>Oooops, there's a problem sending the file. <br /><br />Click <a href='sendform.php?isComet=yes'>here</a> to send again.</p>"
		}
		 //handles for general problem
		function busyuser(){
		var div = document.getElementById('contentWrapper');
		div.innerHTML = "<p>Oooops, looks like the user you're trying to send the file is still busy. <br /><br />Click <a href='sendform.php?isComet=yes&nocache="+ Math.random() +"'>here</a> to try again.</p>"
		}
		
		//handles if no user can be found
		function nouser(){
			var div = document.getElementById('contentWrapper');
		div.innerHTML = "<p>Oooops, looks like there's no receiver for your file. Click <a href='sendform.php?isComet=yes'>here</a> to try again.</p>";
			}
		//handles if we can't find a user
		function usererr(a){
				var div = document.getElementById('contentWrapper');
		div.innerHTML = "<p>Oooops, problem connecting with <b>"+a+"</b>. <br /><br />Click <a href='sendform.php?isComet=yes'>here</a> to send again.</p>";
		}
		//function to start progress timer
		function beginPhotoUploadProgress() {
          _divUploadProgress.style.display = '';
          clearPhotoUploadProgress();
          _photoUploadProgressTimer = setTimeout(updatePhotoUploadProgress, PROGRESS_INTERVAL);
}
      
      function clearPhotoUploadProgress() {
    for (var i = 1; i <= _maxLoop; i++) {
        document.getElementById('tdProgress' + i).style.backgroundColor = 'transparent';
    }

    document.getElementById('tdProgress1').style.backgroundColor = PROGRESS_COLOR;
    _loopCounter = 1;
}
     //function responsible for animating the progress bar
     function updatePhotoUploadProgress() {
    _loopCounter += 1;

    if (_loopCounter <= _maxLoop) {
        document.getElementById('tdProgress' + _loopCounter).style.backgroundColor = PROGRESS_COLOR;
    }
    else {
        clearPhotoUploadProgress();
    }

    if (_photoUploadProgressTimer) {
        clearTimeout(_photoUploadProgressTimer);
    }

    _photoUploadProgressTimer = setTimeout(updatePhotoUploadProgress, PROGRESS_INTERVAL);
}


function photoUploadComplete(message) {
    clearPhotoUploadProgress();

    if (_photoUploadProgressTimer) {
        clearTimeout(_photoUploadProgressTimer);
    }

   

    _divUploadProgress.style.display = 'none';
    _divUploadMessage.style.display = 'none';
   

    if (message.length) {
       _divUploadMessage.innerHTML = "<p>" + message + "</p>";
        _divUploadMessage.style.display = '';
       }
 
}


function stopFileChecking()
{
	 
	 
	new Ajax.Request('/stopFileCheck.php', {
		method: 'post',
		parameters: { action:'stop' },
		onSuccess: function(p_xhrResponse) {
			
		},
		onFailure: function( ) {
					
		}
	});
	
}




function init(x,y){
	_divUploadMessage = document.getElementById('divUploadMessage');
    _divUploadProgress = document.getElementById('divUploadProgress');
	_notifier =  document.getElementById('notifier');
	_notifier.innerHTML = "Contacting <span style='color:blue;font-weight:bold'>" + x + "</span>.........";
	oncheckFileCall = false;
	//set the file id
	username = x;
	fileId = y;
	//turn on the progress bar
	beginPhotoUploadProgress();
	//begin the ajax request for response every 5 seconds
	checkFile = setInterval(AjaxCheck,5000);
	 }
	 
	 function waitingResponse(x){
	 	_notifier.innerHTML = x;
	 	beginPhotoUploadProgress();
	 	}
	 	
	 	function responseMade(x){
	 		photoUploadComplete(x);
	 		//set to true to stop the ajax request since we got a response
	 		oncheckFileCall = true;
	 		//stop the file checking
	 		stopFileChecking();
	 		}

