<?php
ob_start();
session_start( );
require_once('db.php');
require_once ('Classes/dbconn.php');
require_once ('Classes/chat.php');


/* Did we get everything we expected? */
if (isset($_POST['istyping']) && isset($_POST['username']))
{  
   $chat = new chatroom();
   
	$theValue = intval(trim($_POST['istyping']));
   $username = trim($_POST['username']);
	
	if(isset($_POST['isComet']))
	{
			     	
	 $chat->publish($_SESSION['pin'], '8', $username, (string)$theValue, $COMETKEY);
			     	
   }
   else 
   {
	
      $dbconn = new dbconn();
      
		if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		   {
		   	$chat->updatetypingstatus($theValue,$username);
			}
			else 
			print 'o';
	}
}
?>