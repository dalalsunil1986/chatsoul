<?php
ob_start();
session_start( );
require_once('../db.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<title>Error 404</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $TITLEFONT; ?>">
		<link rel="stylesheet" type="text/css" href="/CSS/login.css"/> 
		
	</head>
	<body>
		<div id="contentWrapper">
		<div id="logo"><img src="/Images/beta.png" /><br />
		<h1 style="<?php echo 'font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT; ?>" ><?php echo $TITLE; ?></h1><p><?php echo $SAY; ?></p></div>  <br /><br />
		<div class="innerdiv"><div>
		<h2>We're sorry, the page you're requesting does not exist.<br /><br/>
		Go back <a href="/">here</a>.</h2>
			</div>
		</div>
	</body>
</html>