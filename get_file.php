<?php
ob_start();
session_start( );
require_once('db.php');
require_once ('Classes/dbconn.php');
require_once('Classes/chat.php');


if (isset($_POST['username']) && isset($_POST['lasttime']))
{
	
 $dbconn = new dbconn();
	
 if($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
	{ 
	      $chat = new chatroom();
			
			$username = trim($_POST['username']);
			$lasttime = $_POST['lasttime'];
			
			$user_id = $chat->getauserid($username);
			
	if($user_id != -1)
		{
			 /* Get rid of anything x minutes older in the queue since this file stays longer here */
			 /* Important to take note here is not to change from_user to NULL since we need it when the file receiver click the prompt button*/
			   $timespan = $lasttime - $MAXFILETIMEPENDING;
			   
			   $chat->updateoldfile($user_id,false,$timespan);
			    
			   #check if we're using comet server then just exit if true since we really don't care what happen past this point 
			   if(isset($_POST['isComet']))
			   {
			    exit();
			   }
			   
			   #will print 'o' 'o' 'o' if nothing found
			   $chat->checkfiletransfer($user_id,$lasttime);
			   
		 }
		  else
		   print("['0','0','0']");
	} 
	else
     print("['0','0','0']");
}
else
	print("['0','0','0']");
?>