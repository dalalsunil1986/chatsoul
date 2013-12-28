<?php
ob_start();
session_start( );
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chat.php');

$errorhandler = new errorhandler(false);

/* Did we get everything we expected? */
if (isset($_REQUEST['file_id']) && isset($_SESSION['username']) && isset($_SESSION['pin']))
{
	   $dbconn = new dbconn();
	   
		if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
		{
			
			
			    #stop the sender's file checking
				 if(isset($_REQUEST['isComet']))
					{
						
						$sql = "SELECT filename, from_user FROM file WHERE file_id = ".mysql_real_escape_string($_REQUEST['file_id']).";";
						$result = mysql_query($sql) or die('Error, query failed');
	               list($filename, $from_user) = mysql_fetch_array($result);
	               
	               #publish
	               $chat = new chatroom();
						$chat->publish($_SESSION['pin'],'5', $from_user, 'clear interval', $COMETKEY);
						
						//do some cleanup
                  @mysql_free_result($result);
					}
			#------------------------------------------------------------------------------------------------------#		
				
				/* Set everything to NULL as the indicator */
				$sql = 'UPDATE file SET filename = NULL, file_data = NULL, user_id = NULL, from_user = NULL, file_size = NULL, file_type = NULL WHERE file_id = '.$_REQUEST['file_id'].';';
				@mysql_query($sql);
				
				
		}
	
}

$errorhandler->Stop();
?>