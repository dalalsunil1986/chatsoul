<?php
ob_start();
session_start( );
require_once ('Classes/configwriter.php');
$configs = configwriter::configs();
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="CSS/sendform.css?y=4"> 
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/redmond/jquery-ui.css" type="text/css" media="all" />
       
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
        <script type="text/javascript" src="http://sync3.frozenmountain.com/client.js?v=3.4.1"></script>
	
        <meta charset=utf-8 />
        <title></title>
        <style>
            iframe {
                display: none
            }
            #demo {
                width: 460px;
                margin: 200px auto
            }
	    #log{
	      text-align: center;
              font-size:18px;
              font-weight:bold;
              font-family: Ms Trebuchet;
	    }
	   
	    #progressbar{width:360px; margin:20px auto;height:20px}
	    .ui-progressbar-value{background-image:url(../Images/pbar-ani.gif); }
        </style>
        <script>
           
	    function startListening(){
	      if(fm){
		
		var client = fm.websync.client;
		var util = fm.utilities;
		var net = fm.network;
		var thekey = $('#key').val();
		var channel = '/' + $('#pincode').val();
		var user = $('#username').val();
		
		client.initialize({
		 key: thekey 
		});
		
		client.connect({
		  stayConnected: true,
		  onSuccess: function(args){
		      downloadInProgress();
		    },
		  onFailure: function(args) {},
		  onStreamFailure: function(args){}
		});
		
		client.subscribe({
		  channel: channel,
		  onSuccess: function(args) {},
		  onFailure: function(args) {},
		  onReceive: function(args) {
		    
		    if (args.data.text)
		    {
		      var thedata = args.data.text.split('||');
                      var numresult = parseInt(thedata[0]);
		      
                      if(numresult == 5 && thedata[2] != 'null'){
			downloadFinished();
		      }
                      if(numresult == 5 && thedata[2] == 'null'){
			downloadError();
		      }
                      
		    }
		  }
		});
	      }
	    }
            
	    function getParameterByName(name)
            {
                name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
                var regexS = "[\\?&]" + name + "=([^&#]*)";
                var regex = new RegExp(regexS);
                var results = regex.exec(window.location.search);
                if(results == null)
                 return "";
                else
                 return decodeURIComponent(results[1].replace(/\+/g, " "));
            }
	    
	    function downloadFinished(){
	      $("#demo").css("display","none");
	    }

             function downloadIncomplete(){
	      $("#progressbar").progressbar('destroy');
              $("#log").html("<p>Sorry download is not completed.</p>");
	    }
	    
            function downloadError(){
	      $("#progressbar").progressbar('destroy');
              $("#log").html("<p>Sorry problem downloading the file.</p>");
	    }
	    
	    function downloadInProgress(){
	      var pGress = setInterval(function() {
	      var pVal = $('#progressbar').progressbar('option', 'value');
	      var pCnt = !isNaN(pVal) ? (pVal + 1) : 1;
		if (pCnt > 100) {
		  clearInterval(pGress);
		} else {
		   $('#progressbar').progressbar({value: pCnt});
		}
	      },10);
	    }
	   
            $(function() {
                
		  startListening();
		  var file_id = getParameterByName('file_id');
		  var page = '/filedownloader.php?file_id='+file_id;
		  
		  $('<iframe>').attr({
		    id : file_id,
		    src : page,
		    frameBorder : 0,
		    width : 460
		  }).appendTo('#iframes-wrapper');
		    
	    });
      </script>
    </head>
    <body>
        <div id="demo">
            <div id="log"></div>
            <div id="progressbar">
            </div>
            <div id="iframes-wrapper"></div>
            <input type="hidden" id="key" value="<?php echo $configs['comet_key']; ?>"/>
	    <input type="hidden" id="pincode" name="pincode" value="<?php echo $_SESSION['pin']; ?>" />
	    <input type="hidden" id="username" name="username" value="<?php echo $_SESSION['username']; ?>" />
        </div>
    </body>
</html>