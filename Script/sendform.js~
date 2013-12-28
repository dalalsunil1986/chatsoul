var flag = true;
           
$(document).ready(function() {
            $('#nptFile').uploadify({
              'uploader'  : '/uploadify/uploadify.swf',
              'script'    : '/uploadify/uploadify.php',
              'cancelImg' : '/uploadify/cancel.png',
              'folder'    : '/uploads',
              'auto'      : true,
              'onComplete' : function(event, ID, fileObj, response, data) {
                   document.getElementById('fileUploaded').value = response.toString();
                   if(response.toString() != 'error'){
                   	$('#status-message').html('File Uploaded, ' + response.toString().split("||")[1]);
                   }
                   else{
                   	 $('#status-message').html('Ooops, something went wrong uploading your file.');
                   	}
                  }
  });
});
    
function gopost()
     {
     	
var q = document.getElementById('nptRecvname').value;
var b = document.getElementById('fileUploaded').value;
     	   if (q != '')
     	   {
     	   	if (b!='')
     	   	{
     	   		if (flag != false)
     	   		{
     	   		 flag = false;
     	   		 NotifyUser('Please wait');
     		       $('#nptFile').uploadifyUpload();
                document.forms['transferForm'].submit();
               }
               else
               {
               	return;
               }
            }
            else
            {
             $('#notify').html('Ooops, select a file to send.');
            }
     		}
     		else
     		{
     		$('#notify').html('Ooops, choose a user to send your file.');
     		}
 }
     
 function NotifyUser(arg) {
        var lock = document.getElementById("skm_LockPane");
        if (lock)
            lock.className = "LockOn";
        lock.innerHTML = '<span style="font-size:16px">' + arg + '............' + '</span>';
    }