<?php
ob_start();
session_start();

$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script src="/Script/jquery.min.js"></script>
<script src="/Script/runonload.js"></script>
<script src="/Script/invite.js"></script>
<link href="/CSS/invite.css" media="all" type="text/css" rel="stylesheet">
</head>

<body>
<div id="skm_LockPane" class="LockOff" ></div>
<div id="contact_form" align="left">

<div><p style="font-size:13px">Fill up the form to send invitations to chat here
. Separate multiple emails with coma(eg. honey@gmail.com,go@yahoo.com,miles@stiedu.com).</p></div>
<br /><br /><br />
  <form name="contact" method="post" action="">
    <fieldset>
    
      <label for="name" id="name_label">Your name:</label>
      <input type="text" name="name" id="name" size="20" value="" class="text-input" />
      <label class="error" for="name" id="name_error">Your name is required.</label>
      
      <label for="email" id="email_label">Your email:</label>
      <input type="text" name="email" id="email" size="20" value="" class="text-input" />
      <label class="error" for="email" id="email_error">Invalid email address.</label>
      
      <label for="phone" id="phone_label">Friends emails:</label><br /><br />
      <textarea name="phone" id="phone" value="" class="text-input" ></textarea>
      
      <label class="error" for="phone" id="phone_error">Email to send invite is required.</label>
      <div id="general"></div>
    	<br />
      <input type="submit" name="submit" class="button" id="submit_btn" value="Send" style="width:100px" />
    </fieldset>
  </form>
  
</div></body>
</html>';

if (isset($_SESSION['username']) && isset($_SESSION['pin']))
{
 echo $body;
}
?>