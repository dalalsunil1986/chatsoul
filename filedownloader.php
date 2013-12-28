<?php
ob_start();
session_start( );
set_time_limit(0);
require_once ('CometServer/Publisher.php');
require_once ('Classes/configwriter.php');
#----------------start of variables----------
$configs = configwriter::configs();
$SQL_SERVER=$configs['db_server'];   
$USERNAME=$configs['db_username'];        
$PASSWORD=$configs['db_password'];      
$DATABASE=$configs['db_database'];
$apikey = $configs['comet_key'];
$download_rate = 60.5;
$uniquename = uniqid();
$filetemp = "temporaryfiles/".$uniquename.".txt";
$buffsize = 0;
$percentdownloaded = 0;
$fromname = '';

#----------end of variables-------------



#-------------------start of functions-------------------

function publish($chanel,$status,$user,$message,$key)
{
  $publisher = new Publisher();
  $publisher->domainKey = $key;
  $publications = $publisher->publish(array(
     'channel' => '/'.$chanel,
    'data' => (object) array(
      'text' => $status.'||'.$user.'||'.$message
  )
  ));
}

function shutdown()
{
  $sql = 'UPDATE file SET filename = NULL, file_data = NULL, user_id = NULL,
          from_user = NULL, file_size = NULL, file_type = NULL, isdownloading = 2
	  WHERE file_id = '.$_REQUEST['file_id'].';';
  @mysql_query($sql);
  if(isset($_REQUEST['isComet']))
  {
   publish($_SESSION['pin'],'5', $fromname, 'null', $apikey);
  }
}
#--------------------end of functions



	if (!isset($_REQUEST['file_id'],$_SESSION['username'],$_SESSION['pin'])){
	   die( 'Error: No file chosen.');
	}
	
	/* Can we connect to the MySQL server? */
	if ($conn = @mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD)) 
	{
		/* Can we connect to the correct database? */
		if (@mysql_select_db($DATABASE, $conn)) 
		{
			
		    $sql = "SELECT filename, file_data, file_size, file_type,
		            from_user FROM file WHERE
			    file_id = ".mysql_real_escape_string($_REQUEST['file_id']).";";
			    
		    $result = mysql_query($sql) or die('Error, query failed');
		    list($filename, $file_data, $file_size, $file_type, $from_user) = mysql_fetch_array($result);
		    $sizeofFile = strlen($file_data);
		    $fromname = $from_user;
           
		if ($sizeofFile < 10)
		 {
		  
		   // receiver was too late to accept the file transfer
		  $sql = 'UPDATE file SET user_id = NULL, from_user = NULL WHERE file_id = '.$_REQUEST['file_id'].';';
		  @mysql_query($sql);	
		  if(isset($_REQUEST['isComet']))
		    {
		     publish($_SESSION['pin'],'5', $fromname, 'null', $apikey);
		    }
		    die();
		 }
		 else
		 {
	   
		     
		      register_shutdown_function('shutdown'); //register a function to notify the sender that the file was not downloaded
		      
		      if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');// required for IE
			}
		      
		      header('Pragma: public'); 
		      header('Expires: 0');
		      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		      header('Cache-control: private',false);
		      header("Content-length: $file_size");
		      header("Content-type: $file_type");
		      header("Content-Disposition: attachment; filename=\"$filename\"");
		      header('Content-Transfer-Encoding: binary');
		      header('Connection: close');
		      flush();
		      
		      
		      
		      file_put_contents($filetemp, $file_data); //copy data's content to the created temporary file to be read
		      
		      $file = fopen($filetemp, "r");
		      
		      if($sizeofFile > 1000000){
			$download_rate = 90.5;
		      }
		      
		      while(!feof($file))
		      {
		        $buff = fread($file, round($download_rate * 1024));
			print $buff;
			$buffsize += strlen($buff);
			$percentdownloaded = round(($buffsize / $sizeofFile) * 100);
			//here we can update database to let the user know about the percentage downloaded
			$percent = 'UPDATE file SET percent = '.$percentdownloaded.' WHERE file_id = '.$_REQUEST['file_id'].';';
			@mysql_query($percent);
			flush();
			sleep(1);
		      }
		      
		      fclose($file);
		      if ($buffsize == $sizeofFile)//check if the browser receive all the data being downloaded
		      {
			
			$sql = 'DELETE FROM file WHERE file_id = '.$_REQUEST['file_id'].';';
			@mysql_query($sql);
                        
		        if(isset($_REQUEST['isComet']))
			  {
			    publish($_SESSION['pin'],'5', $fromname, 'clear interval', $apikey);
                          }
		      }
		      else
		      {
			//inform the user that the download is incomplete
			$sql = 'UPDATE file SET filename = NULL, file_data = NULL,
			        user_id = NULL, from_user = NULL, file_size = NULL,
				file_type = NULL, isdownloading = 2 WHERE file_id = '
				.$_REQUEST['file_id'].';';
			
			@mysql_query($sql);
			if(isset($_REQUEST['isComet']))
			{
                          publish($_SESSION['pin'],'5', $fromname, 'null', $apikey);
                        }
		      }
  
			      
		  }
		  @mysql_free_result($result);
		}
		else
		  print('Cannot connect to our database.');
		  @mysql_close($conn);
	}
	else
	print ('Problem with our connection to the server.');



?>