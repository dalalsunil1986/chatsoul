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
		   
		   if (isset($_SESSION['changeserver']))
			{
				unset($_SESSION['changeserver']);
			}
		   
			# try to inform or leave a message that this user just logged out.
			$intPin = intval(trim($_POST['pin']));
			$username = trim($_POST['username']);
			$lasttime = $_POST['lasttime'];
			$message = $username." exited the chatroom.";
			$admin = 'admin';
			
			
			
			#check if we are using a comet
		   if(isset($_POST['isComet']))
			 {
			 	$chat->insertmessage($message,15,$lasttime,$intPin,$admin,'',$COMETKEY,false,false);
			
			 	$chat->removeacometuser($username,$intPin,$message,$COMETKEY);
			 }
			 else 
			 {
			 	
			 	$chat->insertmessage($message,15,$lasttime,$intPin,$admin,'',$COMETKEY,false,false);
			
			 }
			 	   	
		    #logout the user from the whole chatroom
		    $chat->logout($username);
		    	
			/* Clear the session */
			unset($_SESSION['username']);
			unset($_SESSION['pin']);
			
			
			#passed back an integer for success to inform our client
			print('1');
		    
	    } else
			print('0');
	
} 
else
{
print('0');
}

$errorhandler->Stop();

?>