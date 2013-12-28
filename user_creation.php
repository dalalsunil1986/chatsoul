<?php
ob_start();
session_start();
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');

#start a new error handler
$errorhandler = new errorhandler(false);

#retrieve the posted pin number and assign to a variable
if (isset($_POST['pin']) && is_numeric($_POST['pin']))
{
	$pinNumber = $_POST['pin'];
	
	$dbconn = new dbconn();
	
	if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1)
		{ 
		   $chat = new chatroom();
		
			$pin = intval($pinNumber);
			
			if ($chat->isRoomExist($pin))
			{
			
				$classname = "class";
				$randomUsername = $chat->generateUsername();
				
				while($chat->isUserNameExist($randomUsername))
				{
				   $randomUsername = $chat->generateUsername();
				}
				
				//check the status of what server we're using
				$cometstatus = 0;
				if(!$ISCOMET)
				{
				    $cometstatus = 1;
				}
				
				#login the user
				if ($chat->login($randomUsername,$classname,date('Y-m-d H:i:s'),$pin,$cometstatus) == 1)
				{
					/* declare the session variable */
					$_SESSION['username'] = $randomUsername;
					$_SESSION['pin'] = $pinNumber;
					session_regenerate_id();
					print('1');
				}
				else
					#something wrong inserting the user on the database 
					print('4');
				
			}
			else
			  #the pin number does not exist
			  print('3');
		
	  }
	   else
		#something wrong w/ the connection
		print('4');
	
}
else
{
#the posted pin number is not set or not a valid number
print('2');
}

$errorhandler->Stop();

?>