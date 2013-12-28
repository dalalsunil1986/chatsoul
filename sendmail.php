<?php
 ob_start();
 session_start();
 require_once($_SERVER['DOCUMENT_ROOT']."/db.php");
 require_once($_SERVER['DOCUMENT_ROOT']."/Classes/mailsender.php");
 require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/chat.php');
 require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
 
$errorhandler = new errorhandler(false);

 if(isset($_POST['name'],$_POST['phone'],$_SESSION['pin'])) 
 {
 	$mailsender = new mailsender();
	$urlmanager = new urlmanager();
 	
 	$cleanName = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
 	$cleanMail = $_POST['phone'];
 	$cleanmyMail = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
 	
 	if (strlen(trim($cleanName)) < 1)
 	{
	   die('2');
	}
 	
	$message = $mailsender->createHtmlInvitation($cleanName,ucwords($SITE),$WEBSITE,$_SESSION['pin']);
	
 	$result = $mailsender->sendMail($cleanMail, 'Invitation to chat and share files.', $message, $cleanName, $cleanmyMail, $SMTPAUTH, $SMTPUSERNAME, $SMTPPASSWORD, $SMTPSERVER);
 	
 	$str = (string)$result;
 	
 	echo $str;
 	
 }
 else
 { 
  echo '1';
 }
 
 $errorhandler->Stop();
?>