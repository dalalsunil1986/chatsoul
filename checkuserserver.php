<?php
ob_start();
session_start();
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');

$errorhandler = new errorhandler(false);

if (isset($_POST['username']) && isset($_POST['pin'])) 
{
	
      $dbconn = new dbconn();
	
		if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		{  
		   
		   $chat = new chatroom();
			#convert the past pin value from the post to an integer
			$intPin = intval(trim($_POST['pin']));
			$username = trim($_POST['username']);
			$now = time();
			
			#update and check this specific users last-logged date and find for offline users
			$chat->checkusers($username,$intPin,$now,true,$COMETKEY);
			
			#check user server;will print 1 if there is and 0 if none
			$chat->checkuserserver($intPin,$now);
			
			
	       }
	     else
	     #problem with the connection
		  print('0');
}
else
{
#problem with the posted values
print('0');
}

$errorhandler->Stop();	
?>