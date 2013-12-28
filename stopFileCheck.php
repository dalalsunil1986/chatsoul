<?php
ob_start();
session_start( );
require_once('db.php');
require_once('Classes/chat.php');

$errorhandler = new errorhandler(false);

if(isset($_SESSION['username']) && isset($_SESSION['pin']))
{
	
if (isset($_POST['action']))
{
	if($_POST['action'] == 'stop')
	{
	$chat = new chatroom();
	$chat->publish($_SESSION['pin'],'5', $_SESSION['username'], 'clear interval', $COMETKEY);
	}
}

}

$errorhandler->Stop(); 
?>