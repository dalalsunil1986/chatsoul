<?php
require_once('db.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
$urlmanager = new urlmanager();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<title>About <?php print($TITLE); ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $TITLEFONT; ?>">
		<link rel="stylesheet" type="text/css" href="/CSS/login.css"/> 
		
	</head>
	<body>
		<div id="contentWrapper">
		<a href="/" style="float:right">Back</a><br /><div id="logo"><img src="/Images/beta.png" /><br /><h1 style="<?php echo 'font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT; ?>" ><a href="/" style="text-decoration:none;color:black"><?php echo $TITLE; ?></a></h1>
		<p><?php echo $SAY; ?></p></div><br />
		<div class="innerdiv">
		<div id="aboutdiv">
		<h2>What is this?</h2>
		<p>
		<?php print($TITLE); ?> is a web application where you can create a chatroom, share ideas by drawing on a whiteboard and share files in real-time.
		No sign-ups needed, no giving away of your personal information to create a chatroom.
		</p>
		<br />
		<h2>Why <?php print($SITE); ?> is so simple?</h2>
			<p>
			The reason is it's so simple you don't need to register or log-in to start or enter a 
			chatroom. You don't even need to tell us who you are. All you need to do is push the "Create Chatroom" button, click the generated
			 chatroom url to enter the chatroom, and share the url to people whom you want to chat and share files 
			 with.
			</p>
			<br />
			<h2>What is a chatroom url?</h2>
			<p>A url is a link you click to go to a website or an address you type on your browser address bar such as yahoo.com, facebook.com, etc. Therefore, 
			a chatroom url is a link you can click or type to your browser address bar to enter a chatroom. It goes on a format like this: 
			<b>http://<?php print($SITE); ?>.com<?php echo $urlmanager->createUri(); ?>####</b> where <b>####</b> is to be replaced by a chatroom id.</p><br />
			<h2>Why are chatrooms disposable?</h2>
			<p>Disposable since created chatrooms that aren't active can only last up to <?php if($MAXCHATROOMEXPIRATION == 1){ echo $MAXCHATROOMEXPIRATION.' day';} else echo $MAXCHATROOMEXPIRATION.' days'; ?>. Chatroom older than
			<?php if($MAXCHATROOMEXPIRATION == 1){ echo $MAXCHATROOMEXPIRATION.' day';} else echo $MAXCHATROOMEXPIRATION.' days'; ?>
			 with no activity will be deleted. Don't worry, all the messages stored on the chatroom will be deleted too.
			 The idea here is if you want a quick way to chat and share files w/ somebody or any group of people, you
			 can have a chatroom instantly without giving away your email address, username, name, etc. </p>
			 <br />
			 <h2>How private are the created chatrooms?</h2>
			 <p>Well, it's really up to you. If you want that only a certain people can enter the chatroom,
			 then just share the chatroom id or url to these certain people and tell/inform them not to share the
			 chatroom with anybody outside the group. However, if you want everybody or anybody can enter the chatroom, you can publish
			 the url to Facebook, Twitter, or anywhere in the web. It's really up to you. If you think your chatroom privacy
			 got compromised, feel free to create another one. </p>
		</div>
		</div>
		</div>
	</body>
</html>