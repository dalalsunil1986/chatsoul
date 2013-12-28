<?php
ob_start();
session_start();
require_once('db.php');
require_once ('Classes/dbconn.php');
require_once ('Classes/chat.php');

$errorhandler = new errorhandler(false);

if (isset($_POST['username']) && isset($_POST['pin'])) 
{
	 $dbconn = new dbconn();
		
		if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		{
			 $chat = new chatroom();
			 
			 $intPin = intval(trim($_POST['pin']));
			 $username = trim($_POST['username']);
			
        if(isset($_POST['isComet']))
			{
				$message = "";
				$chat->removeacometuser($username,$intPin,$message,$COMETKEY);
				
			}
		
	  }
	
        

} 
else
{
print('0');
}

$errorhandler->Stop();

?>