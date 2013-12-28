<?php
ob_start();
session_start( );
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');


function isComet()
{
if(isset($_POST['isComet']))
{
	return true;
}	
else  
   return false;
}



if (isset($_POST['username']) && isset($_POST['lasttime']) && isset($_POST['pin']))
{	
	$dbconn = new dbconn();
		
		if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		{   
		    $chat = new chatroom();
		    
			 $intPin = intval(trim($_POST['pin']));
			 $username = trim($_POST['username']);
			 $lasttime = $_POST['lasttime'];
			 $startTime = $_POST['startTime'];
	        #store a boolean for better performance
	       $iscomet = isComet();
	        
	        if($iscomet)
	        {
	        	$startTime = time()*1000;
	        	$lasttime = 0;
	        }
			
			if ( $lasttime != 0 )
			{
			 
			 $chat->displaymessage($username,$lasttime,$startTime,$OFFSET,$intPin);
			 
			}
			else
			 
			 $chat->displaymessageonload($username,$lasttime,$startTime,$intPin,$iscomet);
			
		 } 
		 else
		   print('');
}
else
	print('');
?>