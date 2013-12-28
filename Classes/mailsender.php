<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/phpmailerv5/class.phpmailer.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/phpmailerv5/class.smtp.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');

class mailsender 
{
	
	public function createHtmlInvitation($username,$siteTitle,$website,$chatroompin){
		
		$urlmanager = new urlmanager();
		
		$html = "<html><head><title>Chat Invitation</title></head><body><br />";
		
		$html .= "<p style='font-size:12px'>
		             Hi there,
			  </p>";
		
		$html .= "<p style='font-size:12px'>
				You've received this email because <b>".$username. "</b> invited you
				to chat and share files @ <a href='"
				.$website.$urlmanager->createUrl('/chat',$chatroompin)."' >
				<b>".$siteTitle."</b></a>.
			  </p>";
				
	        $html .= "<p style='font-size:12px'>
		           <b>".ucwords($siteTitle)."</b>
			   is a simple and free web application where you can
			   create a private chatroom, share files and draw your
			   ideas on a whiteboard.
			 </p>";
			
		$html .= "<p style='font-size:12px;font-weight:bold'>
		            All these you can do without signing up and
			    without giving your email and other private informations.
			  </p>";
		
		$html .= "<p style='font-size:12px'>
			       Check it out here:
			       <a href='".$website.$urlmanager->createUrl('/chat',$chatroompin)."'
			       style='color:blue;text-decoration:underline'>"
			       .$website.$urlmanager->createUrl('/chat',$chatroompin)."
			       </a>
			  </p>"; 
	        
		$html .= "<p style='font-size:12px'>
		              Best Regards,<br /><br />
			      admin@".$siteTitle.".com
			  </p>";
		
		return $html;
	}
	
	
	
	
	#function to check if email is in valid format
	private function isvalidEmail($arg)
	{
		
	    if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $arg)) 
	       { 
		 return false; 
	       } 
	       else 
	        return true; 
	       
	}
	
	
	#verify if the whole string contains an @
	private function verify($arg)
	{
		$pos = strripos($arg, '@');
		if ($pos === false) {
		  return false;
		}
		else 
		   return true;
		
	}
	
	
		
	
	#function responsible for sending the email
	public function sendMail($to, $subject, $message, $fromname, $from="", $auth, $username, $password, $server) 
	{
		try
		{
			$mailer = new PHPMailer();
			  
			if( strlen(trim($to)) > 5 && $this->verify($to))
			{
				$tos = explode (",", $to);
				if(count($tos) == 1)
				{
				      if ($this->isvalidEmail($to))
				      {
				       $mailer->AddBCC(trim($to));
				      }
				      else 
					return 1;
				     
				}
				else
				{
					$tossed = array_unique($tos);
					foreach ($tossed as $to) 
					{
					  if ($this->isvalidEmail($to))
					     $mailer->AddBCC(trim($to));
						
					}
				}
			}
		       else 
			 return 1;
			
			
			$mailer->IsSMTP();
			
			if(strtolower($server) == 'smtp.gmail.com' || strtolower($server) == 'ssl://smtp.gmail.com'){
				
				$mailer->SMTPSecure = "tls";
				$mailer->Port = 465;
				if(strlen(trim($from)) > 0)
				{
				  $mailer->SetFrom($from, $fromname);
				}
				else 
				   $mailer->FromName = $fromname;
			}
			else{
			  
			  $mailer->Port = 25;
			  $mailer->FromName = $fromname;
			    
			}
			
			$mailer->Subject = $subject;
			
			$mailer->Body = $message;
			$mailer->IsHTML(true);
			$mailer->SMTPAuth = $auth;
			
			if($mailer->SMTPAuth){
			  $mailer->Username = $username;
			  $mailer->Password = $password;
			}
			
			$mailer->Send();	
			
			$mailer->SmtpClose();
			
			if ($mailer->IsError()) 
			{
			  return 2;
			} 
			else 
			{
			  return 0;
			}
		}
		catch(Exception $ex)
		{
			return 2;
		}
		
	 }  


} 
?>