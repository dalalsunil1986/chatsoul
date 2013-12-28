<?php
ob_start();
session_start( );
require_once($_SERVER['DOCUMENT_ROOT'].'/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/chatroomscleaner.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/chat.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/dbconn.php');

$cleaner = new chatroomscleaner();
$cleaner->deleteTemporaryFiles(1,'uploads');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>File Transfer</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="CSS/sendform.css?y=4"> 
		<script type="text/javascript" src="/uploadify/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="/uploadify/swfobject.js"></script>
		<script type="text/javascript" src="/uploadify/jquery.uploadify.v2.1.4.js"></script>
		<script type="text/javascript" src="/Script/sendform.js"></script>
	</head>
	<body>
	<div id="skm_LockPane" class="LockOff" ></div>
		<div id="contentWrapper">
		<div><p id="notify"></p></div>
			<form id="transferForm" action="send.php" method="post">
				<div id="formWrapper">
				<fieldset class="login">
				<legend>Send A File</legend>
				<div>
					<label for="nptRecvname">
					 Choose a user to send the file to:
					</label>
				</div>
					<div>
					<select id="nptRecvname" name="nptRecvname">
					<option value="">&nbsp;</option>
<?php

if (!isset($_SESSION['username']) && !isset($_SESSION['pin']))
    die('Cannot find your session.');

$chat = new chatroom();	
$dbconn = new dbconn();

if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) == 1) 
{
	$intPin = intval($_SESSION['pin']);
	
	if(isset($_REQUEST['isComet']))
	{
	   //get online comet users except the user loading this page
	   $chat->createOnlineUsersOptions($_SESSION['username'],$intPin,true);
		
	}
	else
	  $chat->createOnlineUsersOptions($_SESSION['username'],$intPin,false);
		
}
 else
   print('');

?>
						</select>
						</div>
						<br />
						<div>
						<label for="nptFile" id="status-message">
						 Select a file:
						</label>
						</div>
						<div>
							<input type="hidden" id="fileUploaded" name="fileUploaded" />
							<input type="file" id="nptFile" name="nptFile"/>
						</div>
						<input type="hidden" id="nptUsername" name="nptUsername" value="<?php print($_SESSION['username']); ?>" />
						<script type="text/javascript">
							//<![CDATA[
							var d = new Date( );
							document.writeln('<input type="hidden" id="nptLasttime" name="nptLasttime" value="' + d.getTime( ) + '" />');
							//]]>
						</script>
						<br />
						<div>
						<input type="button" class="button" value="Send File" onclick="gopost()" />
						</div>
						</fieldset>
						<?php
						if(isset($_REQUEST['isComet']))
						{
						  echo '<input type="hidden" id="isComet" name="isComet" value="' .$_REQUEST['isComet']. '" />';
						}
						?>
				</div>
			</form>
		</div>
	</body>
</html>