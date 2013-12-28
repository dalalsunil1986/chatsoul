<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/db.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');

class chatroombuilder
{
	

   #functions that will retrieve users address
   public function get_ip_address() 
   {
     // check for shared internet/ISP IP
     if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP']))
      return $_SERVER['HTTP_CLIENT_IP'];
   
     // check for IPs passing through proxies
     if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      // check if multiple ips exist in var
       $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
       foreach ($iplist as $ip) {
	if ($this->validate_ip($ip))
	 return $ip;
       }
   }
	   
   if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
      return $_SERVER['HTTP_X_FORWARDED'];
     if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
      return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
     if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
      return $_SERVER['HTTP_FORWARDED_FOR'];
     if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
      return $_SERVER['HTTP_FORWARDED'];
   
     // return unreliable ip since all else failed
     return $_SERVER['REMOTE_ADDR'];
    }
    
    
    private function validate_ip($ip) 
    {
	if (filter_var($ip, FILTER_VALIDATE_IP, 
			    FILTER_FLAG_IPV4 | 
			    FILTER_FLAG_IPV6 |
			    FILTER_FLAG_NO_PRIV_RANGE | 
			    FILTER_FLAG_NO_RES_RANGE) === false)
	    return false;
	self::$ip = $ip;
	return true;
    }
	   
   
   #function responsible for checking if the entered pin number exist on the database
   public function isRoomExist($pin)
   {
    
      $sql = sprintf("SELECT room_id FROM chatroom WHERE pin_num = '%s'", $pin);	
      
      if ( $result = @mysql_query($sql))
      {
	 if ( $row = @mysql_num_rows($result))
	 {
		 return true;
	 }
	 else
	 return false;
      }
      else
      return false;
  
   }
  
   #responsible for generating the random pin number for a chatroom
   public function generatePinNumber()
   {
      $characters = array("0","1","2","3","4","5","6","7","8","9");
      $keys = array();
      $random_pin = '0';
      $count = 0;   
      
      while(count($keys) < PINLENGTH)
      {
	$x = mt_rand(0, count($characters)-1);
	if(!in_array($x, $keys))
	{
	   if($count < 1)
	   {
	     if($x != '0')
	     {
	      $keys[] = $x;
	      $count++;//increment counter so we're not gonna repeat this again
	     }
	   }
	   else
	      $keys[] = $x;
	}
      }

      foreach($keys as $key){
	$random_pin .= $characters[$key];
      }
      
      return intval($random_pin);
   }
   
   
   #responsible for counting how many ips are there in the directory
   public function ipnum($ip)
   {
      $numrows = 0;
      $sql = sprintf("SELECT ipadress FROM chatroom WHERE ipadress = '%s'", ip2long($ip));
      if ($result = @mysql_query($sql)) 
      {
	$numrows = @mysql_num_rows($result);
      }
      
      return $numrows;
   }
   
    #responsible for inserting the new chatroom in the database
   public function createroom($randnum, $date, $ipaddress)
   {
     $sql = sprintf("Insert INTO chatroom  VALUES ('%s', '%s', '%s', '%s')", null, $randnum, $date, ip2long($ipaddress));
     
     if (mysql_query($sql))
     {
      return 1;
     }
     else 
      return 0;
   }
   
   #responsible to display a message if a user exceeded in creating numbers of chatroom
   public function exceeded($vars,$siteurl,$title,$slogan)
   {
      $urlmanager = new urlmanager();
      $part = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	 <html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	 <head>
	      <title>Ooops, alert</title>
	      <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	      <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.TITLEFONT.'">
	      <link rel="stylesheet" type="text/css" href="CSS/login.css" /> 
	 </head>
	 <body>
	      <div id="contentWrapper">
	      <div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.TITLEFONTSIZE.'px; font-family:'.TITLEFONT.'">'.$title.'</h1><p>'.$slogan.'</p></div><br /><br />
	      <div class="innerdiv"><div>
	      <h2>Ooops, you exceeded the allowed number for chatroom creation.<br />
	      Go back <a href="/">here</a>.<br /><br />
	      Anyways, here are the list of chatroom you\'ve created:<br />
	      <ul style="padding:12px">';
	 
      foreach($vars as $var)
      {
         $part .= '<li><a href="'.$urlmanager->createUrl('/chatroom',$var).'" >'.$siteurl.$urlmanager->createUrl('/chatroom',$var).'</a></li>';
      }
      
      $part .= '</ul></h2></div></div></body></html>';
      
      return $part;
   }
   
  #called when a user successfully created a chatroom
   public function buildsuccess($randomnumber,$title,$say,$site)
   {
    
    $urlmanager = new urlmanager();
    echo ('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		     <html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
			 <head>
			       <title>Congratulations</title>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
				<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.TITLEFONT.'">
			       <link rel="stylesheet" type="text/css" href="CSS/login.css"/> 
			       <script type="text/javascript" src="http://prototypejs.org/assets/2009/8/31/prototype.js"> </script>
			      <script type="text/javascript" src="Script/checkpin.js"></script>
			 </head>
			    <body>
			      <div id="contentWrapper">
			      <div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.TITLEFONTSIZE.'px; font-family:'.TITLEFONT.'">'.$title.'</h1><p>'.$say.'</p></div><br /><br />
			      <div class="innerdiv"><div style="text-align:justice">
			      <h2>
			      Congrats, you\'ve just created a new chatroom with ID :
			      <b style="color:blue">'); 
			echo ($randomnumber);
			echo '</b><br /><br/>
		    Click Chatroom URL : <br /><a href="'.$urlmanager->createUrl('/chatroom',$randomnumber);
			echo('" title="Click to enter chatroom."><b style="color:blue">http://'.$site.$urlmanager->createUrl('/chatroom',$randomnumber));
			echo('</b></a></h2><br />
			       <h2>Most importantly, only share this number or url to people whom you want to chat with.</h2>
			       </div><br/>
				     <div>
				<p><b></b></p> 
				     <form id="loginForm" method="post" action="self">
					  <fieldset id="formWrapper" class="login">
					  <legend>Enter the '.PINLENGTH.'-digit chatroom ID.</legend><br />
					       <div>
						<div><input type="text" class="textbox" id="pincode" name="pincode" value=""/></div>
					       </div>
					       <div>
					       <input type="button" class="button" value="Login" id="submitButton" />
					       </div>
				     </fieldset>
				     </form></div><input type="hidden" id="hiddenUrl" name="hiddenUrl" value="'.$urlmanager->createUrl('/chat').'"/>
				     </div>
				</div>
			   <body>
		      </html>');	
   }
   


	
}

?>