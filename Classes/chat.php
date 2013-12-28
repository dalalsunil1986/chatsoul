<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/library/HTMLPurifier.auto.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/CometServer/Publisher.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/db.php');
require_once ('mailsender.php');

#class that contains all the chatroom functions
class chatroom 
{



	#----------------------[ACTIONS]///////function that returns a value-------------------		
	/**
	*compute a date
	*@param $arg(bigint)- date in unix format(milliseconds)
	*/
	private function computeDate($arg)
	{
		$time = 0;
		$s = 's';
		if($arg < 60000):
		$time = round($arg/1000,0);
		if ($time == 1)
		{$s = '';}
		return (string)$time . " second".$s." ago";
		elseif ($arg>=60000 && $arg < 3600000):
		$time = round($arg/60000,0);
		if ($time == 1)
		{$s = '';}
		return (string)$time . " minute".$s." ago";
		elseif ($arg>=3600000 && $arg < 86400000):
		$time = round($arg/3600000,0);
		if ($time == 1)
		{$s = '';}
		return (string)$time . " hour".$s." ago";
		else:
		$time = round($arg/86400000,0);
		if ($time == 1)
		{$s = '';}
		return (string)$time . " day".$s." ago";
		endif;
	}
	
	
	/**
	*clean chat messages before inserting to database
	*@param $message(string) - chat message to be cleaned up
	*@param $ispurify(boolean) - determine whether to use htmlpurifier or just regular string filter
	*/
	private function cleanmessage($message, $ispurify)
	{
		
		if($ispurify)
		{		
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.DefinitionID', 'mypurifier');
			$config->set('HTML.DefinitionRev', 1);
			$config->set('Core.Encoding', 'ISO-8859-1'); // not using UTF-8
			$config->set('HTML.Allowed', 'a[href]'); // Allow links
			$def = $config->getHTMLDefinition(true);
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
			$purifier = new HTMLPurifier($config);
						    
			return trim($purifier->purify($message));
		
		}
		else 
		{
		
		        return filter_var($message, FILTER_SANITIZE_STRING);
		
		}
		
	}
	
	
	/**
	*get a single user id with a given name; 
	*returns a -1 if nothing found
	*/
	public function getauserid($username)
	{
		 
		$sql = sprintf("SELECT user_id  FROM users WHERE username = %s", $this->quote_smart($username));
		$user_id = -1;
		
		if ($result = mysql_query($sql)) 
		{
					
			if ($row = mysql_fetch_assoc($result))
			{
				$user_id = $row['user_id'];
				mysql_free_result($result);
			}
		}	
				
		return $user_id;
	}
	
	
	/**
	*get a username of a user with a given user_id
	*
	*/
	public function getausername($userid)
	{
		$username = '';
		$sql = sprintf("SELECT username FROM users WHERE user_id = '%s'", $userid);
		if ($result = mysql_query($sql)) 
		     {
		      if ($row = mysql_fetch_assoc($result))
			       $username = $row['username'];
			       mysql_free_result($result);
		}
		return $username;
	}
	
	/**
	*returns true if a user has a pending file transfer request
	*/
	private function isuserdownloading($userid)
	{
	
		$downloading = 1;
		$sql = sprintf("SELECT file_id FROM file WHERE user_id = '%s' AND isdownloading = '%s'", $userid, $downloading);	 
		$result = mysql_query($sql);
		if(mysql_num_rows($result))
		{
		return true;	
		}
		else 
		{
		return false;
		}
		mysql_free_result($result);
	}
	
	/**
	*get a single comet user
	*/
	public function getacometuser($username)
	{
	
		$sql = sprintf("SELECT id  FROM cometusers WHERE username = %s", $this->quote_smart($username));
		$id = -1;
		
		if ($result = mysql_query($sql)) 
			{
						/* Did we successfully get a row? */
			if ($row = mysql_fetch_assoc($result))
				{
					$id = $row['id'];
				   mysql_free_result($result);
				}
		   }	
				
				return $id;
	}
	
		
	#used for database protection
	public function quote_smart($value)
	{
		//Strip slashes
		if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
		}
		//Quote if not integer
		if (!is_numeric($value)) {
		$value = "'" . mysql_real_escape_string($value) . "'";
		}
		return ($value);
	}
	
	/**
	*login the user to the chatroom and returns 1 if success else 0 if it fails
	*@param $username(string)- name of the user
	*@param $classname(string)- class to use for styling the name for this user
	*@param $date(datetime)- logged in date initially the current date.
	*@param $chatpin(integer) - pin number or id for the chatroom
	*@param $cometstatus(boolean) - whether using comet server or not.
	*/
	public function login($username,$classname,$date,$chatpin,$cometstatus)
	{
		$sql = sprintf("Insert INTO users  VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", null, $username, $classname, 1, $date, $chatpin, 0, $cometstatus);
		if(mysql_query($sql))
		{
			return 1;
		}
		else 
		return 0;	
	}
	
	/**
	*function responsible to auto-generate a username with a pattern user_00107
	*/
	public function generateUsername()
	{
		$characters = array("0","1","2","3","4","5","6","7","8","9");	
		$keys = array();
		$random_chars = '';
		
		while(count($keys) < 6) {
		    $x = mt_rand(0, count($characters)-1);
		    if(!in_array($x, $keys)) {
		       $keys[] = $x;
		    }
		}
		
		foreach($keys as $key){
		   $random_chars .= $characters[$key];
		}
		return "user_".$random_chars;	
	}
	
	
	/**
	*insert a file in database
	*for transfer
	*if success return 1 else return 0 if failure
	*@param filename(string)- name of the file.
	*@param $content(blob)- data being transferred
	*@param $user_id(integer)- id for the receiving user
	*@param $filedate(bigint)- date of the file transfer request in unix form
	*@param $from_user(string)- username of the sending user
	*@param $filetype(string)- type of file to transfer like jpeg, png, etc...
	*@param $filesize(string)- size of file to transfer
	*/
	public function insertfile($filename,$content,$user_id,$filedate,$from_user,$filetype,$filesize)
	{
		$sql = sprintf("INSERT INTO file (filename, file_data, user_id, file_dte, from_user, file_type, file_size, isdownloading, percent) VALUES ('%s', '%s', '%s', '%s', %s, '%s', '%s', '%s', '%s')", $filename,  $content, $user_id, $filedate, $this->quote_smart($from_user), $filetype, $filesize, 0, 0);	
		if(mysql_query($sql))
		{
		       return 1;
		}
		else 
		return 0;
	}
	
	/**
	*get an id of a file with some given parameters
	*/
	public function getafileid($filename,$user_id,$filedate,$from_user)
	{
		$fileid = -1;
		$sql = sprintf("SELECT file_id FROM file WHERE filename = '%s' AND user_id = '%s' AND file_dte = '%s' AND from_user = %s", $filename, $user_id, $filedate, $this->quote_smart($from_user));
		if ($result = mysql_query($sql))
			{
			 if ($row = mysql_fetch_assoc($result))
					 $fileid = $row['file_id'];
					 mysql_free_result($result);
		   }
					
		return $fileid;
	}
	
	
	
	
	
	
	/**
	*function responsible for checking if the entered pin number exist on the database;
	*return true or false
	*/
	public function isRoomExist($chatpin)
	{
		$sql = sprintf("SELECT room_id FROM chatroom WHERE pin_num = '%s'", $chatpin);	
		if ( $result = mysql_query($sql))
		{
			if ( $row = mysql_num_rows($result))
			{
				return true;
			}
			else
			return false;
		}
		else
		return false;
	}
	
	
	/**
	*function responsible for checking if autogenerated username exist on the database;
	*returns true or false
	*/
	public function isUsernameExist($username)
	{
		$sql = sprintf("SELECT username FROM users WHERE username = '%s'", $username);	
		if ( $result = mysql_query($sql))
		{
			if ( $row = mysql_num_rows($result))
			{
				return true;
			}
			else
			return false;
		}
		else
		return false;
	}
	
	
	#-------------------------------[SUB]/////////functions that do actions and return nothing-------------------------------------
	
	/**
	*insert message to database
	*@param $message(string)
	*@param $user_id(integer)
	*@param $msg_dte(bigint)
	*@param $pin_num as int 
	*@param $m_username(string)
	*@param $iscomet(boolean)- will determine if we're using a comet or not
	*@param $cometkey(string)- our comet public api key
	*@param $prinout(boolean)- whether to print something or not
	*@param $ispurify(boolean)- whether to use htmlpurifier or just php's built-in string filter
	*/
	public function insertmessage($message, $user_id, $msg_dte, $pin_num, $m_username, $iscomet, $cometkey, $printout, $ispurify)
	{
		
		$cleanmessage = $this->cleanmessage($message, $ispurify);
				
		$sql = sprintf("INSERT INTO messages (msg_id, message, user_id, msg_dte, pin_num, m_username) VALUES ('%s', %s, '%s', '%s', '%s', '%s')", null, $this->quote_smart($cleanmessage), $user_id, $msg_dte, $pin_num, $m_username);
			
		if (!mysql_query($sql))
		{
		if($printout == true)
		print (mysql_error());
		}
		else
		{
		#check if we are using a comet
		if(isset($iscomet) && $iscomet != '')
		{
		$this->publish((string)$pin_num,'1', $m_username, $cleanmessage, $cometkey);
		}
		if($printout == true)
		print(1);
		}
		
	}
	
	/**
	*responsible for displaying current chat messages
	*@param $starttime(bigint)- date in unix form when the first message retrieval request started
	*@param $offset(bigint)- constant that can be configured in db.php
	*/
	public function displaymessage($username,$lasttime,$starttime,$offset,$chatpin)
	{
		
		
		$sql = sprintf("SELECT msg_id, msg_dte, username, message FROM messages m INNER JOIN users u ON m.user_id = u.user_id WHERE (msg_dte >= '%s' AND m.pin_num = '%s') ORDER BY msg_dte DESC", $lasttime - $offset, $chatpin);
			if ($result = mysql_query($sql)) 
				{
					$counter = 0;
					$notifycounter = 0;
					#$idstorage = 0;
					
					/* While there is data, loop... */
					while ($row = mysql_fetch_assoc($result))
					{
					
					
					if ( $row['username'] == 'admin' )
					{
						if ($counter == 0)
				      printf("<p class='admin'> Admin says: %s</p>\n",((strpos($row['message'],$username) !== false) ? 'Welcome to the chatroom, <b>'.$username.'</b>.' : $row['message'])) ;
				      $counter = $counter + 1;
					}
						else
					{
						 $startnotify = "";
						 
						 $timeposted = (($lasttime != 0) ? '' : '[ '.$this->computeDate($starttime - $row['msg_dte']).' ]');
						 
						 if ($notifycounter == 0){
							#$idstorage = $row['msg_id'];
						 $startnotify = (($lasttime != 0) ? '' :"<p style='font-weight:bold'>---------LATEST CHAT MESSAGE(S)-----------</p>");
						}
						 #if ($idstorage != $row['msg_id'])
						 printf($startnotify."<p%s>%s %s: %s</p>\n", (($row['username'] == $username ) ? ' class="usernameMe"' : ''), $timeposted,  (($row['username'] == $username) ? 'you said' : $row['username'] . ' says' ), $row['message']);
					    
					    $notifycounter = $notifycounter + 1;
					 }
					
				   }
				   
					mysql_free_result($result);
				 } 
						else
					print('');
		
		
	}
	
	
	
	/**
	*display chat messages during chatroom entry
	*/
	public function displaymessageonload($username,$lasttime,$starttime,$chatpin,$iscomet)
	{
		
		      /* Get rid of anything too old in the queue like 1 day older and just for this chat room */
				$sql = sprintf("DELETE FROM messages WHERE msg_dte < '%s'", $lasttime - 86400000);
				mysql_query($sql);	
				
				$sql = sprintf("SELECT msg_dte, m_username, message FROM messages WHERE (msg_dte >= '%s' AND pin_num = '%s') ORDER BY msg_dte DESC", $starttime - 1800000 , $chatpin);
				
				if ($result = mysql_query($sql)) 
				{
					$counter = 0;
					$notifycounter = 0;
					/* While there is data, loop... */
					while ($row = mysql_fetch_assoc($result))
					{
						
					if ( $row['m_username'] == 'admin' )
					{  
					   
					   if(!$iscomet)
					   {
						 if ($counter == 0)
						 {
					   printf("<p class='admin'> Admin says: %s</p>\n",((strpos($row['message'],$username) !== false) ? 'Welcome to the chatroom, <b>'.$username.'</b>.<br />Clear everything here by clicking the [clear] button below.' : $row['message'])) ;
					   $counter = $counter + 1;
				       }    
				      }
				      else 
				      {
					 if ($counter == 0)
						     {
					  print("<p class='admin'> Admin says: Welcome to the chatroom, <b>".$username."</b>.<br /> Clear everything here by clicking the [clear] button below.");
					  $counter = $counter + 1;
					   }	
				      }
				      
					}
					 else
					{ 
					 //check if we already printed the welcome message
					 if ( $counter != 0 ) 
					 {
						 $startnotify = "";
						 
						 $timeposted = (($lasttime != 0) ? '' : '[ '.$this->computeDate($starttime - $row['msg_dte']).' ]');
						 
						 if ($notifycounter == 0)
						 {
						 $startnotify = (($lasttime != 0) ? '' :"<p style='font-weight:bold'>---------LATEST CHAT MESSAGE(S)-----------</p>");
						 }
						
						 printf($startnotify."<p%s>%s %s: %s</p>\n", (($row['m_username'] == $username ) ? ' class="usernameMe"' : ''), $timeposted,  (($row['m_username'] == $username) ? 'you said' : $row['m_username'] . ' says' ), $row['message']);
					    
					    $notifycounter = $notifycounter + 1;
					  }
					    
					}
					
				   }
				   
					mysql_free_result($result);
				 } 
				else
				 print('');
	}
	
	
	/**
	*display old messages originally 345600000 ms(4days)
	@param $timetosubtract(bigint)- date in unix form that determines how old ago we can retrieve past messages
	*/
	public function displayoldmessages($lasttime, $chatpin, $username, $timetosubtract)
	{
	 
	                        $notifycounter = 0;
				
				#retrieve the past 3 days old chat messages
				$sql = sprintf("SELECT msg_dte, m_username, message FROM messages WHERE (msg_dte >= '%s' AND pin_num = '%s' AND m_username <> 'admin') ORDER BY msg_dte DESC", $lasttime - $timetosubtract , $chatpin);
				
				if ($result = @mysql_query($sql)) 
				{
					if (@mysql_num_rows($result))
					{
					/* While there is data, loop... */
					     while ($row = @mysql_fetch_assoc($result))
					      {
						$startnotify = "";
						$timeposted = "[ <b>".$this->computeDate($lasttime - $row['msg_dte'])."</b> ]";
						      if ($notifycounter == 0)
						       {
							 $startnotify = "<p style='font-weight:bold'>---------ALL PAST MESSAGE(S)------------</p>";
						       }
						       printf($startnotify."<p%s>%s %s: %s</p>\n", (($row['m_username'] == $username ) ? ' class="usernameMe"' : ''), $timeposted,  (($row['m_username'] == $username) ? 'you said' : $row['m_username'] . ' says' ), $row['message']);
						  $notifycounter = $notifycounter + 1;
					  }
				   
					@mysql_free_result($result);
					}
					else
					print("<p style='font-weight:bold'>---------NO PAST MESSAGE(S)------------</p>");
				 } else
					print("<p style='font-weight:bold'>---------NO PAST MESSAGE(S)------------</p>");	
	}
	
	
	/**
	*insert a comet user to our database to be online;
	*required a chatroom id, a username, and a cometkey
	*/
	public function insertcometuser($username,$chatpin,$message='',$cometkey='')
	{
		if($cometkey != '')
		{
		 
		 $this->publish((string)$chatpin, '2', $username, $message, $cometkey);	
			
		}
		
		# insert this user on our cometusers table
		try
		{
		 $insert = sprintf("INSERT INTO cometusers (id, pin_num, username) VALUES ('%s', '%s', '%s')", null, $chatpin, $username);
		 mysql_query($insert);
		}
		catch(Exception $e)
		{
		}
	}
	
	
	/**
	*show all comet users in a chatroom with given chatroom id;
	*returns concatenated names of users;
	*only used if using a comet server
	*/
	public function publishcometusers($chatpin,$cometkey)
	{
		
		$cometusers = '';
		$select = sprintf("SELECT username FROM cometusers WHERE pin_num = '%s'", $chatpin);
		if ($result = @mysql_query($select)) 
		{
		       while ($row = @mysql_fetch_assoc($result))
			{
			      $cometusers .= $row['username'];
			      $cometusers .= ','; 
			}
		}
		
		if($cometusers != '')
		{
			#publish to update online users on the chatroom
			$this->publish((string)$chatpin, '6', $cometusers, 'online', $cometkey);
		}
		
		mysql_free_result($result);
	
	}
	
	/**
	*remove a user from comet server
	*/
	public function removeacometuser($username, $chatpin, $message, $cometkey)
	{
		$sql = sprintf('DELETE FROM cometusers WHERE username = %s', $this->quote_smart($username));
		mysql_query($sql);
		if($message != '')
		{		 	
		$this->publish((string)$chatpin, '3', $username, $message, $cometkey);
		}
		$this->publish((string)$chatpin, '7', $username, $message, $cometkey);
	}
	
	
	/**
	*log-out a user from the chatroom
	*/
	public function logout($username)
	{
		
		$sql = sprintf("DELETE FROM users WHERE username = '%s'", $username);
		mysql_query($sql);
	
	}
	
	
	/**
	*publish to comet server
	*/	
	public function publish($chanel,$status,$user,$message,$key)
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
	
	
	
	/**
	*update a users comet status inorder for others to be informed if comet is used or not
	*/
	public function updatecometstatus($status,$username)
	{
		 
		$query = 'UPDATE users SET iscomet = '.$status.' WHERE username = "'.$username.'";';
		mysql_query($query);
	
	}
	
	
	/**
	*takes care if the file is too old in the que or databse
	*/
	public function updateoldfile($userid,$isownedonly,$timespan)
	{
		
		$sql = 'UPDATE file SET filename = NULL, file_data = NULL, user_id = NULL, from_user = NULL, file_size = NULL, file_type = NULL, isdownloading = 3 WHERE user_id = '.$userid.';';
		
		if($isownedonly != true)
		{
			$sql = 'UPDATE file SET filename = NULL, file_data = NULL, file_size = NULL, file_type = NULL, isdownloading = 3 WHERE file_dte < '.$timespan.' AND isdownloading = 0;';
			
		}
		
		$bigger = mysql_query($sql) or die(print(mysql_error()));
	}
	
	
	/**
	*used to check for file transfer request for a given user_id
	*/
	public function checkfiletransfer($userid,$lasttime)
	{
		
		$sql = sprintf("SELECT from_user, file_id, filename, isdownloading FROM file f INNER JOIN users u ON f.user_id = u.user_id WHERE file_dte >= '%s' AND f.user_id = '%s' ORDER BY file_dte DESC", $lasttime-60000, $userid);
		
		  if ($result = mysql_query($sql)) 
			 {
			/* Do we have a first result to send? */
			  if ($row = mysql_fetch_assoc($result))
			   {
			    if ($row['isdownloading'] == 0)
				   {
					 
					 printf("['%s', '%s', '%s']", $row['from_user'], $row['file_id'], $row['filename']);
				   
				   }
				    else
				     { 
				      print("['0','0','0']");
				     }
				    
			   }
			    else
				    {
					print("['0','0','0']");
				    }
				    
			  } 
			  else
				{
					print("['0','0','0']");
				}
			  
			   mysql_free_result($result);
		
	}
	
	
	
	/**
	*handles the retrieval for online users;
	*if $iscomet = true(see# checkuserserver.php) used to update users while if false used to display
	* online users on regular chat server(see# get_users.php)
	*/
	public function checkusers($username,$chatpin,$now,$iscomet,$cometkey='')
	{
			#update the users last logged-in date
			$update = 'UPDATE users SET last_logged = FROM_UNIXTIME('.$now.') WHERE username = "'.$username.'";';
			$perform = mysql_query($update) or die(mysql_error());
			
			#initialize our first query
			$sql = sprintf("SELECT username, last_logged, status FROM users WHERE pin_num = '%s' ORDER BY username ASC", $chatpin);
			
			if($iscomet)
			{
				
			  $sql = sprintf("SELECT username FROM cometusers WHERE pin_num = '%s' ORDER BY username ASC", $chatpin);
				
			}
				
			
			if ($result = mysql_query($sql)) 
			{
				if(!$iscomet)
				{
						
					#this is the task if using regular server
					print('<div id="users"><div id="userheader"><p style="text-align:center;font-weight:bold"><img src="/Images/users.png" class="clearico"/><span>Online Users</span></p></div><ul>');
					/* While there is data, loop... */
					while ($row = mysql_fetch_assoc($result))
					{
						#get the difference between the last-logged in and the current time in unix time stamp
						$diff = $now - strtotime($row['last_logged']);
						$istyping = 0;
						
						if ($row['status'] != null)
						{
						 $istyping = $row['status'];	
						}
						
						#last logged-in date < 5 seconds ago
						if ( $diff < 5)	
						{
						  printf('<li%s>%s%s</li>', (($row['username'] == $username) ? ' class="usernameMe"' : ' class="user"'), (($row['username'] == $username) ? $row['username'].' (You)' : $row['username'] ), (($istyping == 1 && $row['username'] != $username) ? ' <span style="color:red;font-style:italic;font-size:small">(typing......)</span>' : ''));
						}
					}
					
					print('</ul></div>');
				  
				}
				else
				{
					
					#this is the task if using comet server  
					while ($row = mysql_fetch_assoc($result))
					{
				   
						$thename = $row['username'];
						
						$sq = sprintf("SELECT last_logged, user_id FROM users WHERE username = '%s'", $thename);
						
						if ( $resulta = mysql_query($sq) )
						{
						     
						     $rowa = mysql_fetch_assoc($resulta);
						     #check if this user is busy downloading something
						     $isthisuserbusy = $this->isuserdownloading($rowa['user_id']);
						     
						     
						     #if yes update his last_logged status since his browser might be stuck
						     if($isthisuserbusy)
						     {
							     $update = 'UPDATE users SET last_logged = FROM_UNIXTIME('.$now.') WHERE username = "'.$thename.'";';	
							     $perform = mysql_query($update) or die(mysql_error());	
						     }
						     
						     #get the difference between the last-logged in and the current time in unix time stamp
						     $diff = $now - strtotime($rowa['last_logged']);
						     
						     #last logged-in date is more than 2 minutes ago
						     #means user hasn't updated his/her last-logged
						     if ( $diff >= 120 && $this->getacometuser($thename) != -1 && !$isthisuserbusy)	
						     {
						      $message = $thename.' has been logged-out from the chatroom.';
						      $this->removeacometuser($thename,$chatpin,$message,$cometkey);
						     }
						     
						     #do a clean-up for this specific user	
						     mysql_free_result($resulta);
						}
						else
						{
							$message = $thename.' has been logged-out from the chatroom.';
							$this->removeacometuser($thename,$chatpin,$message,$cometkey);
						}
					
					
					}
					
					
					
				}
				#do some clean-up
				mysql_free_result($result);
		        }
	}
	
	
	
	/**
	*used if user is using a comet server;
	* check if a chatroom member is using a regular chatroom
	*/
	public function checkuserserver($chatpin,$now)
	{
		
	     $one = 1;
	     $sql = sprintf("SELECT last_logged FROM users WHERE pin_num = '%s'  AND iscomet = '%s' ", $chatpin, $one);
				if ($result = mysql_query($sql)) 
				{
					
					
					while ($row = mysql_fetch_assoc($result))
					{
						#get the difference between the last-logged in and the current time in unix time stamp
						$diff = $now - strtotime($row['last_logged']);
						
						#last logged-in date < 5 seconds ago since users in regular chatroom got their login status updated every 5 seconds
						#so we verified that this user is using a regular chatroom
						if ( $diff < 5)	
						{
						
						#get out the while loop and print 1
						die ('1');
						
						}
					}
					
					print ('0');
					
					mysql_free_result($result);
					
				}
				 else
				#no users found
				 print('0');	
					
			
					
	}
	
		
		
	/**
	*update users typing status if he's typing or not
	*/
	public function updatetypingstatus($status,$username)
	{
		
		$sql = 'UPDATE users SET status = '.$status.' WHERE username = "'.$username.'";';
		mysql_query($sql);
		 
	}
	
	/**
	*set max allowed packet for uploading larger files in database;
	*used in send.php for file transfer
	*/
	public function setmaxpacket()
	{
	   $query = 'SET GLOBAL max_allowed_packet = 100000000;';
	   $bigger = mysql_query($query) or die('<p>Ooops, the file you\'re sending is too big. You\'re limited to 10.0 Mb per file send.');
	}
	
	
	
	//gets all the online comet users excluding the @param userName
	//@param $userName-> the username of the username that should not be included
	//@param $intPin-> the room id number for these users
	//@parma $isComet-> check if using comet server or not
	public function createOnlineUsersOptions($userName,$intPin,$isComet){ 
		
		if($isComet){
			
			$sql = sprintf("SELECT username FROM cometusers WHERE username <> %s AND pin_num = '%s'",  $this->quote_smart($userName), $intPin);
			
			if ($result = @mysql_query($sql)) 
			{
				while ($row = @mysql_fetch_assoc($result))
				{
				   $name = $row['username'];
				   $query = sprintf("SELECT user_id FROM users WHERE username = %s",  $this->quote_smart($name));
				   
				 if ($result1 = @mysql_query($query))
				   {
				      if ($row1 = @mysql_fetch_assoc($result1))
					{
						
					  printf("<option value=\"%s\">%s</option>\n", $row1['user_id'], $name );	
						
					}
				    }   	
					
					
				}
				  @mysql_free_result($result);
				  @mysql_free_result($result1);
			} 
			else
			  print('');
			
		}
		else{
			
			$now = time();
			
			$sql = sprintf("SELECT username, user_id, last_logged FROM users WHERE username <> %s AND pin_num = '%s'", $this->quote_smart($userName), $intPin);
			
			if ($result = @mysql_query($sql)) 
			{
			     while ($row = @mysql_fetch_assoc($result))
				{
				   $diff = $now - strtotime($row['last_logged']);
				   
				   if( $diff < 8 )
				   {
				     printf("<option value=\"%s\">%s</option>\n", $row['user_id'], $row['username']);
				   }
				}
				@mysql_free_result($result);
				
			} 
			else
			print('');
			
		}
		
	}
	
	
	
}




class errorhandler
{

	protected $redirect;
	
	static $smtpserver = SMTPSERVER;
	static $username = SMTPUSERNAME;
	static $password = SMTPPASSWORD;
	static $auth = SMTPAUTH;
	static $admin_email = ADMINEMAIL;
	
	static $IGNORE_DEPRECATED = true;
	 
	public function __construct($isredirect) 
	{
	    $this->redirect = $isredirect;
	    $this->Start(null,$isredirect);
	}
	/**
	 * Start redirecting PHP errors
	 * @param int $level PHP Error level to catch (Default = E_ALL & ~E_DEPRECATED)
	 */
	static function Start($level = null,$direct)
	{
	
		if ($level == null)
		{
			if (defined("E_DEPRECATED"))
			{
				$level = E_ALL & ~E_DEPRECATED ;
			}
			else
			{
				// php 5.2 and earlier don't support E_DEPRECATED
				$level = E_ALL;
				self::$IGNORE_DEPRECATED = true;
			}
		}
		
		if($direct)
		{
		 set_error_handler(array("errorhandler", "HandleError1"), $level);
		}
		else 
		 set_error_handler(array("errorhandler", "HandleError2"), $level);
		
	}
	
	
	static function Stop()
	{
	  restore_error_handler();
	}
	
	static function HandleError1($code, $string, $file, $line, $context)
	{
		if (error_reporting() == 0) 
		 return;
		
		if (self::$IGNORE_DEPRECATED && strpos($string,"deprecated") === true) 
		 return true;
		
		$mailsender = new mailsender();
		$err = "";
		$err .= "---- PHP Error ----\n";
		$err .= "Number: [" . $code . "]\n";
		$err .= "String: [" . $string . "]\n";
		$err .= "File: [" . $file . "]\n";
		$err .= "Line: [" . $line . "]\n\n";
		$err .= "Content: [" .$context."]\n\n";
		$subject = 'Error on '.$file;
		
		$mailsender->sendMail(self::$admin_email,$subject,$err,'admin','',self::$auth,self::$username,self::$password,self::$smtpserver);
		
		$url = '/Errors/error500.php' ;
		header("Location: $url");
	}	
	
	/**
	*error handler without redirection
	*/
	static function HandleError2($code, $string, $file, $line, $context)
	{
		if (error_reporting() == 0) 
		 return;
		
		if (self::$IGNORE_DEPRECATED && strpos($string,"deprecated") === true) 
		 return true;
		
		$mailsender = new mailsender();
		$err = "";
		$err .= "---- PHP Error ----\n";
		$err .= "Number: [" . $code . "]\n";
		$err .= "String: [" . $string . "]\n";
		$err .= "File: [" . $file . "]\n";
		$err .= "Line: [" . $line . "]\n\n";
		$err .= "Content: [" .$context."]\n\n";
		$subject = 'Error on '.$file;
		
		$mailsender->sendMail(self::$admin_email,$subject,$err,'admin','',self::$auth,self::$username,self::$password,self::$smtpserver);
	      
	}	
	
}	
	
	
	
	
	
	
	
	
?>