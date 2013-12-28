<?php
ob_start();
session_start( );
require_once('db.php');


if (isset($_POST['file_id']))
{	
	if ($conn = @mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD)) 
	{
	  if (@mysql_select_db($DATABASE, $conn)) 
	  {
			$sql = 'SELECT * FROM file WHERE file_id = '.$_POST['file_id'].';';
			 if ($result = @mysql_query($sql)) 
			 {
				if ($row = @mysql_fetch_assoc($result)) 
				{
					$filename = $row['filename'];
					$file = $row['file_data'];
					$user_id = $row['user_id'];
					$file_dte = $row['file_dte'];
					$from = $row['from_user'];
					$size =  $row['file_size'];
					$type = $row['file_type'];
					$isdownloading = $row['isdownloading'];
					$percent = (string)$row['percent'];
					/* Is there data in the record? */
					if ($filename == null && $file == null && $user_id == null && $from == null && $size == null && $type == null && $isdownloading == 0)
						print('0||0');
					elseif ($filename != null && $file != null && $user_id != null && $from != null && $size != null && $type != null && $isdownloading == 1)
					//file is accepted by receiver and being transferred
					   print('2||'.$percent);
					elseif ($isdownloading == 2)
					//incomplete download
					   print('3||3');
					elseif ($isdownloading == 3)
					//file reciever takes too long to respond more than 2 minutes or so
					   print ('4||4');
					elseif ($isdownloading == 1)
					//file reciever click the download button but it's too late for his/her response
					   print ('4||4');
					else
					//file is still there and waiting a response from a receiver
						print('1||1');
					
				} else
				//no rows have been found so looks like the file was not there
					print('-1||-1');
				@mysql_free_result($result);
			} else
			//looks the file was deleted and downloaded
				print('-1||-1');
		} else
			print('-2||-2');
		/* Close the server connection */
		@mysql_close($conn);
	} else
		print('-2||-2');
}
else
//problem with file_id not set
	print('-2||-2');
?>