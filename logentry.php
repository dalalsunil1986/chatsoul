<?php
ob_start();
session_start( );
require_once('db.php');
require_once ('Classes/dbconn.php');
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
			
			#inform everybody that this guy entered the chatroom
			$userid = -1;
			$intPin = intval(trim($_POST['pin']));
			$username = trim($_POST['username']);
			$lasttime = $_POST['lasttime'];
			$message = $username." entered the chatroom.";
			$admin = 'admin';
			
			#insert a message but don't publish
			$chat->insertmessage($message,15,$lasttime,$intPin,$admin,'',$COMETKEY,false,false);
			
			#check if we are using a comet
		  if(isset($_POST['isComet']))
			{
				# insert this user on our cometusers table and notify others for his/her entry
				$chat->insertcometuser($username,$intPin,$message,$COMETKEY);
				
				# publish the online comet users on this chatroom
				#$chat->publishcometusers($intPin,$COMETKEY);
			}
			else
			{  
			   #update this user comet status for not using a regular server
			   $chat->updatecometstatus(1,$username);
			}
			
			#Check if this user got some pending file transfer request
			#if yes then clear the pending request to receive a new request
			
			 $user_id = $chat->getauserid($username);
			
			 if ( $userid != -1 )
			 {
			     $chat->updateoldfile($userid,true,0);
			 }
			    
			 	
			
		}
		
}

$errorhandler->Stop();

?>