<?php
ob_start();
session_start( );
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');
require_once('db.php');

$errorhandler = new errorhandler(false);

if (isset($_POST['username']) && isset($_POST['pin']))
{
	
		$dbconn = new dbconn();
		
		   if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		     {
			   
			   $chat = new chatroom();
			   
			   $intPin = intval(trim($_POST['pin']));
			   $username = trim($_POST['username']);
			   
			    #insert the user but don't notify others about his/her entry
			    #take note that message and cometkey are intentionally left blank
			   $chat->insertcometuser($username,$intPin);
			   
			   $chat->publishcometusers($intPin,$COMETKEY);
			   
		     } 
		     
 
}

$errorhandler->Stop();
 ?>
 