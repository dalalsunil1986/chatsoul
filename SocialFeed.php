<?php
  require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/SocialMediaFeed.php');
?>
<!DOCTYPE html>
<html lang="en">
    
	<head>
		<title>Social Media Feed Sample</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" type="text/css" href="/CSS/login.css"/>
                <style>
                    #activityFeed ul {
                    padding: 0;
                    margin: 0;
                    list-style: none;
                    }
                    #activityFeed ul li {
                    background-color: #232323 !important;
                    background-position: 10px center !important;
                    background-repeat: no-repeat !important;
                    margin-bottom: 1px !important;
                    padding: 5px 5px 5px 38px !important;
                    font-size: 12px;
                    color:#FFF;
                    border-radius: 5px; 
                    -moz-border-radius: 5px; 
                    -webkit-border-radius: 5px; 
                    
                    }
                    #activityFeed ul li img{
                    width:52px;
                    height:50px;
                    float:left;
                    margin-right: 10px;
                    }
                    #activityFeed ul li a {
                    color: #d6ffff;
                    text-decoration: underline;
                    }
                    #feed_container {
                    margin: 10px 0 10px 20px;
                    width: 320px;
                    }
                </style>
	</head>
        
	<body>
            <div id="feed_container">
            
                <h2>Social Media Feed Sample</h2><br/>
                <p>Results are Instagram and Twitter posts that contains or tagged with words 'fly360jets', '#360jets', 'fly', or '#haiku'. </p><br />
                <?php
                    $tags = array('fly360jets','#360jets','fly','#haiku');
		    $social_media = new SocialMediaFeed($tags,500);
                    $str = $social_media->createSocialFeed();
                    echo $str;
                ?>
            
            </div>
	</body>
</html>