<?php
ob_start();
session_start( );
require_once('db.php');
require_once ('Classes/dbconn.php');
require_once ('Classes/chat.php');

#---------------------------start of functions-----------------------------#
function isComet($arg)
{
	if(!isset($_SESSION['changeserver']))
	{
		if ($arg)
		{
		  return true;
		}
		else 
		{
		 return false;
		}
	}
	else 
	#user was redirected from changeserver.php page because of problem connecting to our comet server
	return false;
}


function getclientScript()
{
	if(isset($_GET['isComet']))
	{
	  return '<script type="text/javascript" src="Script/sendfile.comet.js"></script>';
	}
	else  
	return '<script type="text/javascript" src="Script/sendfile.js"></script>';
}


function formerrorurl($errno)
{
	$theurl = '';
	if(isset($_POST['isComet']))
	{
	  $theurl = '/send.php?error='.$errno.'&isComet=yes';
	}
	else  
	{
	  $theurl = '/send.php?error='.$errno;
	}
					
	return $theurl;	
}
#-----------------------------end of functions------------------------



#check what action this user are doing
if($_SERVER['REQUEST_METHOD'] == "POST")
{
	
	$_SESSION['posted'] = 'yes';
		
	$theurl = '';
	
	if (isset($_POST['nptUsername'],$_POST['nptRecvname'],$_POST['nptLasttime'],$_POST['fileUploaded']))
	{
		#get the concatenated string from posted page
		$fileinfoall = $_POST['fileUploaded'];
		
		if ($fileinfoall != 'error')
		{
			#no error in file uploading 
			$dbconn = new dbconn();
				
			if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
			{
				$chat = new chatroom();
				
				#get this username with a given user_id for the receiver
				$username = $chat->getausername($_POST['nptRecvname']);
						
				if ($username != '') 
				{
							
					#split the string to get the values of the uploaded file
					$fileinfo = explode("||", $fileinfoall);
					#assign the splitted values
					$tmpName = $fileinfo[0];
					$fileName =  filter_var($fileinfo[1], FILTER_SANITIZE_STRING);
					$fileSize = $fileinfo[2];
					$fileType = $fileinfo[3];
					
					if(!get_magic_quotes_gpc())
					{
					  $fileName = addslashes($fileName);
					}
					
					# read the uploaded file to store on the database  
					$content=addslashes(file_get_contents($tmpName));
					
					#get the size of uploaded file
					$intfileSize = filesize($tmpName);
					
					#if file size is larger than the set max allowed in database
					if ($intfileSize > 10000000)
					{
					  $chat->setmaxpacket();
					}
					
					
					#start inserting the file to databse
					if ($chat->insertfile($fileName,$content,$_POST['nptRecvname'],$_POST['nptLasttime'],trim($_POST['nptUsername']),$fileType,$fileSize) == 1)
					{
						#insert is success so begin retrieving the file's id 
						$fileid = $chat->getafileid($fileName,$_POST['nptRecvname'],$_POST['nptLasttime'],trim($_POST['nptUsername']));
						
						if ($fileid != -1)
						{
						
							#delete file we don't need it
							unlink($tmpName);
							
							if(isset($_POST['isComet']))
							{
								#take note of the fourth parameter; concatenated with |
								$cleanFileName = filter_var($fileinfo[1], FILTER_SANITIZE_STRING);
								$chat->publish($_SESSION['pin'], '4', $username, trim($_POST['nptUsername']) .'|'. trim((string)$fileid) .'|'. $cleanFileName, $COMETKEY);
								$theurl = '/send.php?username='.$username.'&fileid='.(string)$fileid.'&isComet=yes';
							}
							else 
							{
								$theurl = '/send.php?username='.$username.'&fileid='.(string)$fileid;
							}
							
							header("Location: $theurl");
							exit();
							
						
						
						}
						else
						#something wrong or we can't find the just inserted file
						#delete the file from the directory we don't need it
						unlink($tmpName);
						$errno = '1';
						$theurl = formerrorurl($errno);
						header("Location: $theurl");
						exit();
							
						
						
					
					}
					else
					#configure maximum allowed packet for large file
					#receiving user has a pending file transfer request
					#problem happened while inserting the file to our database
					$errno = '2';
					$theurl = formerrorurl($errno);
					header("Location: $theurl");
					exit();
					
				}
				else
				#looks like the receiver log outs
				$errno = '3';
				$theurl = formerrorurl($errno);
				header("Location: $theurl");
				exit();
					
			} 
			else
			#something wrong happened while connecting to our database
			$errno = '1';
			$theurl = formerrorurl($errno);
			header("Location: $theurl");	
			exit();
		}
		else 
		#error result on the file being uploaded
		$errno = '1';
		$theurl = formerrorurl($errno); 
		header("Location: $theurl");
		exit();
	}
	else
	#problem on our expected data since some of them are not posted
	$errno = '1';
	$theurl = formerrorurl($errno);
	header("Location: $theurl");
	exit();
}
else
{
	#the action taken is a GET

	if(isset($_SESSION['posted']))
	{
	
		unset($_SESSION['posted']);
		
		$head ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
			<head>
				<title>Ajax File Transfer</title>
				<meta http-equiv="content-type" content="text/html;charset=utf-8" />
				<meta http-equiv="Expires" content="Tue, 01 Jan 2000 12:12:12 GMT">
		      <meta http-equiv="Pragma" content="no-cache">
				<script type="text/javascript" src="Script/prototype.v161.js"> </script>
				'.getclientScript().'
			</head>
			<body>
				<div id="contentWrapper" style="width:400px;margin-left:auto;margin-right:auto;
				padding-top:50px;text-align:center;font-size:16px;font-family:Arial">
				<div id="divUploadMessage" style="display:none;"></div>
				 <div id="divUploadProgress" style="display:none">
				    <p id="notifier"></p>
				    <div>
					<table border="0" cellpadding="0" cellspacing="2" style="width:100%">
					    <tbody>
						<tr>
						    <td id="tdProgress1">&nbsp; &nbsp;</td>
						    <td id="tdProgress2">&nbsp; &nbsp;</td>
						    <td id="tdProgress3">&nbsp; &nbsp;</td>
						    <td id="tdProgress4">&nbsp; &nbsp;</td>
						    <td id="tdProgress5">&nbsp; &nbsp;</td>
						    <td id="tdProgress6">&nbsp; &nbsp;</td>
						    <td id="tdProgress7">&nbsp; &nbsp;</td>
						    <td id="tdProgress8">&nbsp; &nbsp;</td>
						    <td id="tdProgress9">&nbsp; &nbsp;</td>
						    <td id="tdProgress10">&nbsp; &nbsp;</td>
						</tr>
					    </tbody>
					</table>
				    </div>
				</div>';
		   
		   
		$tail = '</div></body></html> ';          
		
		
		echo $head;
		
		#get method
		if(isset($_GET['username']) && isset($_GET['fileid']))
		{
			#no error
			$username = $_GET['username'];
			$fileid = $_GET['fileid'];
			print("<script type='text/javascript'>init('".$username."',".$fileid.")</script>");
		}
		else 
		{
			#an error have been found
			$error = $_GET['error'];
			
			if($error == '2')
			{
				#print($_GET['message']);
				print("<script type='text/javascript'>busyuser()</script>");	
			}
			elseif($error == '3')
			{
			        print("<script type='text/javascript'>nouser()</script>");	
			}
			else  
			        print("<script type='text/javascript'>starterr()</script>");	
		
		}
		
		echo $tail;
	
	}
	else 
	{
	 
		$iscomet = isComet($ISCOMET);
		$theurl = '';
		
		if($iscomet)
		{
		  $theurl = '/sendform.php?isComet=yes';
		}
		else  
		{
		   $theurl = '/sendform.php';
		}
		
		header("Location: $theurl");
		exit();	
		    
	}

}

?>
	
