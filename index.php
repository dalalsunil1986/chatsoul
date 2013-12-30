<?php
ob_start();
session_start( );
require_once($_SERVER['DOCUMENT_ROOT'].'/db.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
$urlmanager = new urlmanager();

if (isset($_SESSION['username']) && isset($_SESSION['pin'])) 
{
 $chaturl = $urlmanager->createUrl('/chatroom',$_SESSION['pin']);
 header("Location: $chaturl");
 die();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<title>Welcome To Chatsoul</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $TITLEFONT; ?>">
		<link rel="stylesheet" type="text/css" href="/CSS/login.css"/>
		<script type="text/javascript" src="/Script/prototype.v161.js"> </script>
		<script type="text/javascript" src="/Script/checkpin.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.min.js"></script>
		<script type="text/javascript" src="/Script/quickpager.js"></script>
		<script type="text/javascript">
		   var $j = jQuery.noConflict();
		</script>
	</head>
	<body>
	<!--Github Ribbon-->
	<div class="github-fork-ribbon-wrapper left-bottom">
	 <div class="github-fork-ribbon">
	    <a href="https://github.com/hunyoboy/chatsoul">Fork me on GitHub</a>
	 </div>
	</div>
	<!--Github Ribbon-->
	<div id="contentWrapper">
		<span style="float:right">
		 <a href="<?php echo $urlmanager->createUrl('/about'); ?>" >About</a> |
		 <a href="<?php echo $urlmanager->createUrl('/terms'); ?>">Terms Of Use</a>
		</span>
		<br />
		
		<div id="logo">
			<img src="/Images/beta.png" />
			<br />
			<h1 style="<?php echo 'font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT; ?>" ><?php echo $TITLE; ?></h1>
			<p><?php echo $SAY; ?></p>
		</div>
		<br />
		<br />
		
		<div class="innerdiv">
			<h2>
			   <b>Want to start a chatroom?</b>
			</h2>
			
			<form id="loginForm2" method="post" action="<?php echo $urlmanager->createUrl('/pincode'); ?>">
				<fieldset id="formWrapper" class="login">
					 <div align="center" id="generatepindiv">
					      <button type="submit" class="submitBtn" value="generate pin" >
					      <span style="font-size:20px">Create Chatroom</span>
					      </button>
					</div>
					<div>
					<input type="hidden" value="true" name="hiddenvar" id="hiddenvar"/>
					</div>
				 </fieldset>
			</form>
		</div>
			<?php
			if(!empty($_COOKIE['chatroom']) && isset($_COOKIE['chatroom'])):   
			?>
				<div align="center" class="ordiv"><h1><b>OR</b></h1></div>
				<div class="centerdiv">
					<h2>
						<b>
						 Want to enter your created chatrooms?
						</b>
						<ul class="paging">
							<?php
							 $counter = 0;
							 $room = $_COOKIE['chatroom'];
							 $rooms = explode("||",$room);
							 foreach(array_reverse($rooms) as $room)
							 {
							   print('
								 <li>
								 <a href="'.$urlmanager->createUrl('/chatroom',$room).'" >Chatroom ID: '.$room.'</a>
								 </li>'
								 );
							   $counter++;
							 }
							?>
						</ul>
					</h2> 
				</div>
			<?php
			   endif;
			?>
		<div style="padding-left:281px" class="ordiv">
			<h1><b>OR</b></h1>
		</div>
		
		<div class="innerdiv">
		<h2>
		   <b>Want to enter a chatroom?</b>
		</h2> 
			<form id="loginForm" method="post" action="self" onsubmit="return false;">
				<fieldset id="formWrapper" class="login">
				   <legend>Enter the <?php echo $PINLENGTH; ?>-digit chatroom ID.</legend>
					<div>
					  <div><input type="text" class="textbox" id="pincode" name="pincode" value=""/></div>
					</div>
					<div>
					  <input type="button" class="button" value="" id="submitButton" />
					</div>
				</fieldset>
			</form>
		</div>
			
		<div class="footer">
			<p>Created by: <a href="http://www.linkedin.com/pub/joel-capillo/26/8b4/ba3" target="_blank">Joel Capillo</a></p>
			<br />
			<p>Powered by: <a href="http://www.frozenmountain.com/websync/" target="_blank">WebSync Comet Server</a></p>
		</div>
	</div>
		<input type="hidden" id="hiddenUrl" name="hiddenUrl" value="<?php echo $urlmanager->createUrl('/chat'); ?>"/>
		
		<?php
		  
		  if($counter > 2){
		     echo '<script type="text/javascript">
				$j(document).ready(function() {
				   $j("ul.paging").quickPager({
				      pageSize: 2,
				      currentPage: 1
				   });
				});
			   </script>'; 
		  }
		
		?>
		
	</body>
</html>

