<?php
ob_start();
session_start( );
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');
require_once('db.php');

$errorhandler = new errorhandler(false);

if (isset($_POST['username']) && isset($_POST['message']) && isset($_POST['lasttime']) && isset($_POST['pin']))
{
	if (strlen(trim($_POST['message'])) > 0)
	{
		$dbconn = new dbconn();
		
		   if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		     {
			    
			    $chat = new chatroom();
			     
			    $intPin = intval(trim($_POST['pin']));
			    $username = trim($_POST['username']);
			    $lasttime = $_POST['lasttime'];
			     
			    $user_id = $chat->getauserid($username);
		
				if ($user_id != -1) 
				{
				if(isset($_POST['isComet']))
				{
				$chat->insertmessage($_POST['message'],$user_id,$lasttime,$intPin,$username,$_POST['isComet'],$COMETKEY,true,$ISPURIFYHTML);
				}
				else  
				$chat->insertmessage($_POST['message'],$user_id,$lasttime,$intPin,$username,'',$COMETKEY,true,$ISPURIFYHTML);
				} 
			   else
				print(0);
				
			
		     }
		      else
			   print(0);
		
   } 
    else
	 print(0);
 
}
else
{
print(0);
}

$errorhandler->Stop();
?>
