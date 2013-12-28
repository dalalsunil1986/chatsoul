<?php
ob_start();
session_start();
require_once('db.php');
require_once('Classes/dbconn.php');
require_once('Classes/chatroombuilder.php');
require_once('Classes/chat.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
$urlmanager = new urlmanager();
	

#start a new handler that will get redirected when error occurs
$errorhandler = new errorhandler(true);

$toomuch = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<title>Too Much</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
		<link rel="stylesheet" type="text/css" href="CSS/login.css" /> 
		
	</head>
	<body>
		<div id="contentWrapper">
		<div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT.'">'.$TITLE.'</h1><p>'.$SAY.'</p></div> <br /><br />
		<div class="innerdiv"><div>
		<h2>Ooops, you exceeded the allowed number of chatrooms created.<br />
		Go back to <a href="/">homepage</a>.</h2>
			</div>
		</div>
	</body>
</html>';


$err = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<title>Error</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
		<link rel="stylesheet" type="text/css" href="CSS/login.css"/> 
		
	</head>
	<body>
		<div id="contentWrapper">
		<div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT.'">'.$TITLE.'</h1><p>'.$SAY.'</p></div> <br /><br />
		<div class="innerdiv"><div>
		<h2>Ooops, something wrong happened.<br />
		Go back to <a href="/">homepage</a>.</h2>
			</div>
		</div>
	</body>
</html>';


#check what action this user are doing
if($_SERVER['REQUEST_METHOD'] == "POST")
{	
	
	#initiate our ip
	$ip = NULL;
	$numchatrooms = 0;
	
	if (!empty($_COOKIE['chatroom']) && isset($_COOKIE['chatroom']))
	{
	 $numchatrooms = count(explode("||",$_COOKIE['chatroom']));
	}
	
	if (isset($_POST['hiddenvar']) && ($_POST['hiddenvar'] == 'true') )
	{
	
	 #retrieve the cookie to see how many times this user created a chatroom
	 #$numtimes = intval($_COOKIE["NUMTIMES"]);
	        if ($numchatrooms < $MAXCHATROOMCOUNT)
		{
			
			$dbconn = new dbconn();
		 
				if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1)
				{  
				   
				   $builder = new chatroombuilder();
				   
					#get users ip address
					$ip = $builder->get_ip_address();
					$numberofips = $builder->ipnum($ip);
					
					if(empty($_COOKIE['chatroom']) && $numberofips > 0)
					{
						#user deleted his cookies and we count the number of ips he got on the database
						if($numberofips >= $MAXCHATROOMCOUNT)
						{
						        $theurl = $urlmanager->createUrl('/pincode').'?error=1';		
							header("Location: $theurl");
							exit();
					   
						}
					}
					
					
					$randomNumber = $builder->generatePinNumber();
					
					while($builder->isRoomExist($randomNumber))
					{
					 $randomNumber = $builder->generatePinNumber();
					}
					
					
					
					if ($builder->createroom($randomNumber,date('Y-m-d H:i:s'),$ip) == 1)
					{
					
						$expire = time()+60*60*24*$MAXCHATROOMEXPIRATION;//set cookie expiration
						$thecookieval = '';
						
						if ($numchatrooms == 0)
						{
							#looks like this is the first visit so assign the first chatroom number on the cookies
							$thecookieval = $randomNumber;
						}
						else 
						{
						    $thecookieval = $_COOKIE['chatroom'].'||'.$randomNumber;
						}
						
						#set the new value for our cookie
						setcookie("chatroom", $thecookieval, $expire );	
						
						$theurl = $urlmanager->createUrl('/pincode').'?x='. (string)$randomNumber;		
						header("Location: $theurl");
						exit();
		
					}
					else
					   #problem inserting the chatroom
					   $theurl = $urlmanager->createUrl('/pincode').'?error=0';		
					   header("Location: $theurl");
					   exit();
					
			
				}
				else
				#problem with the connection
				     $theurl = $urlmanager->createUrl('/pincode').'?error=0';		
				     header("Location: $theurl");
				     exit();
			
		}
		else
		#too much creation of chatrooms
		    $theurl = $urlmanager->createUrl('/pincode').'?y=toomuch';		
		    header("Location: $theurl");
		    exit();
		
		    
	}
	else
	 #deny the user from creating a chatroom
	   $theurl = $urlmanager->createUrl('/pincode').'?error=0';		
	   header("Location: $theurl");
	   exit();
	
	

}
else 
{
#request happening is a GET request
	
	if(isset($_GET['x']) && !isset($_GET['y']))
	{
		#successfully created a chatroom
		$randomnumber = $_GET['x'];
		$builder = new chatroombuilder();
		$builder->buildsuccess($randomnumber,$TITLE,$SAY,$SITE);
		
	}	
	elseif(!isset($_GET['x']) && isset($_GET['y']))
	{
	  
		#exceeded the number of chatrooms created
		$builder = new chatroombuilder();	
		$rooms = explode("||",$_COOKIE['chatroom']);
		echo ($builder->exceeded($rooms,$WEBSITE,$TITLE,$SAY));
     
	}
	else 
	{
		#output an error
		if(isset($_GET['error']))
		{
			$errorval = intval($_GET['error']);
			if($errorval == 1)
			{
				echo ($toomuch);
			}
			else 
			   echo ($err);
		}
		else 
		 echo ($err);
	}
	
	
	
}

$errorhandler->Stop();

?>