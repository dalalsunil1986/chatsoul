<?php
ob_start();
session_start( );
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');

$errorhandler = new errorhandler(false);


if (isset($_POST['username']) && isset($_POST['lasttime']) && isset($_POST['pin']))
{	
	$dbconn = new dbconn();

	if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		 {
		 	 $chat = new chatroom();
		 	 
			 $intPin = intval(trim($_POST['pin']));
			 $username = trim($_POST['username']);
			 $lasttime = $_POST['lasttime'];
			 
			 $chat->displayoldmessages($lasttime,$intPin,$username,1000*60*60*24*$OLDMESSAGEAGE);
			 
			 
		} 
	
}

$errorhandler->Stop();
	
?>