<?php
ob_start();
session_start( );
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');

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
			
			#take note the iscomet or fourth param to be false and cometkey is null
			$chat->checkusers($username,$intPin,$now,false);
	
	   }
	     else
	      #problem with the connection
		   print('');
}
 else
#problem with the posted values
print('');
	
?>