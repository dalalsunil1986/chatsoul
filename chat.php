<?php
ob_start();
session_start( );
require_once($_SERVER['DOCUMENT_ROOT'].'/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/dbconn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/Classes/chat.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
$urlmanager = new urlmanager();

//set the error handler to true to redirect the page
//incase of errors
$errorhandler = new errorhandler(true);

    #-------------------start of variables-----------------#
    
    $err = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
                <head>
                        <title>Error</title>
                        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
                        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
                        <link rel="stylesheet" type="text/css" href="/CSS/login.css"/> 
                </head>
                <body>
                    <div id="contentWrapper">
                        <div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT.'">'.$TITLE.'</h1><p>'.$SAY.'</p></div>  <br /><br />
                        <div class="innerdiv"><div>
                        <h2>Ooops, something wrong happened while logging you in.<br /><br />
                        Go  to <a href="/">homepage</a>.</h2>
                        </div>
                    </div>
                </body>
            </html>';
    
    $cantfind = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
                        <head>
                                <title>Error 404</title>
                                <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
                                <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
                                <link rel="stylesheet" type="text/css" href="/CSS/login.css"/> 
                        </head>
                        <body>
                                <div id="contentWrapper">
                                    <div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT.'">'.$TITLE.'</h1><p>'.$SAY.'</p></div><br /><br />
                                        <div class="innerdiv"><div>
                                        <h2>We\'re sorry, the page you\'re requesting does not exist.<br /><br/>
                                        Go back <a href="/">here</a>.</h2>
                                    </div>
                                </div>
                        </body>
                </html>';
    
    $noexist = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html mlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
                        <head>
                                <title>No Exist</title>
                                <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
                                 <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
                                <link rel="stylesheet" type="text/css" href="/CSS/login.css"/> 
                                
                        </head>
                        <body>
                                <div id="contentWrapper">
                                    <div id="logo"><img src="/Images/beta.png" /><br /><h1 style="font-size:'.$TITLEFONTSIZE.'px; font-family:'.$TITLEFONT.'">'.$TITLE.'</h1><p>'.$SAY.'</p></div> <br /><br />
                                        <div class="innerdiv"><div>
                                        <h2>Ooops, the chatroom you are trying to enter does not exist anymore. It must have expired and got deleted.<br /><br/>
                                        Go back <a href="/">here</a>.</h2>
                                    </div>
                                </div>
                        </body>
                 </html>';
    
    $chatbody1 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
                    <head>
                        <title>'.$TITLE.'</title>
                        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
                        <meta http-equiv="imagetoolbar" content="no" />
                        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family='.$TITLEFONT.'">
                        <link href="/CSS/chat.css" rel="stylesheet" type="text/css" />
                        <link href="/CSS/osx.css" rel="stylesheet" type="text/css" />
                        <link href="/CSS/jquery.gritter.css" rel="stylesheet" type="text/css"/>
                        <link href="http://fonts.googleapis.com/css?family=Cuprum&subset=latin" rel="stylesheet" type="text/css"/>
                        '.getcometScript($ISCOMET).'
                        <script type="text/javascript" src="/Script/prototype.v161.js"> </script>
                        <script type="text/javascript" src="/Script/ws_jsgraphics.js"> </script>
                        <script src="/Script/jquery.min.js"></script>
                        <script src="/Script/jquery.watermark.js"></script>
                        <script type="text/javascript" src="/Script/jquery.gritter.min.js"> </script>
                        <script src="/Script/simplemodal.js" type="text/javascript"></script>
                        <script type="text/javascript">
                        var $j = jQuery.noConflict();
                        </script>
                        <script type="text/javascript" src="/Script/osx.js"> </script>
                    </head>
                    <body>
                        <div id="skm_LockPane" class="LockOff" ></div>
                        <div style="text-align:center;">
                        <div id="chatClient">
                        <form id="loginForm" action="self" method="post" onsubmit="return false;">
                        <span style="float:right;margin-right:15px"><a href="#" class="osx" title="Invite family and friends to enter chatroom." style="color:transparent"><img src="/Images/invite.png" class="clearico" style="border:0px" /></a><a href="#" class="osx" title="Invite family and friends to enter chatroom.">Invite People</a></span><br />
                        <h2 style="font-size:'.($TITLEFONTSIZE*.5).'px;font-family:'.$TITLEFONT.'">'.$TITLE.'</h2><p style="text-align:center">( Chatroom ID Number : <b style="color:blue"><a href="'.$_SERVER['REQUEST_URI'].'">';
                                    
    $chatmiddle = '</a></b> )</p>
                    <div class="left">'.getHeader($ISCOMET).'</div>
                    <div class="right"></div>
                    <div class="clear"></div>
                    <div id="message" class="left"><div id="messageCenter"></div></div>
                    <div id="usernameContainer" class="right">'.getUserContainer($ISCOMET).'</div>
                    <div class="clear"></div>
                    <div id="control">
                    <input id="text2Chat" name="text2Chat" class="textbox" type="text" maxlength="215" size="80" value="" />
                    <input id="submitButton" type="button" class="button" value="Send"/>&nbsp;&nbsp;
                    <input id="quitButton" type="button" class="button" value="Quit" />&nbsp;&nbsp;
                    <a href="#" onclick="DisplayPastMessages()" title="Show all past messages." style="color:transparent"><img src="/Images/redo.png" class="clearico" style="border:0px"/></a><a href="#" onclick="DisplayPastMessages()" id="pop" title="Show all past messages.">History</a>&nbsp;&nbsp;
                    <a href="#" onclick="ClearMessages()" title="Clear all." style="color:transparent"><img src="/Images/clear.png" class="clearico" style="border:0px" /></a><a href="#" onclick="ClearMessages()" id="pop" title="Clear all.">Clear</a>&nbsp;&nbsp;
                    <a href="#" title="Send file to any online users." style="color:transparent" onclick="showframe()"><img src="/Images/sharefile.png" class="clearico" style="border:0px" /></a><a href="#" id="pop" title="Send file to any online users." onclick="showframe()">Send File</a>
                    </div>
                    <input type="hidden" id="username" name="username" value="';
    
    $chatbody2 = '" /><input type="hidden" id="pincode" name="pincode" value="';
    
    $chatbody3 = '" />
                    <div id="osx-modal-content"> 
                    <div id="osx-modal-title"><img src="/Images/invite.png" class="clearico" />Invite friends to chat.</div> 
                    <div class="close"><a href="#" class="simplemodal-close">x</a></div> 
                    <div id="osx-modal-data"> 
                    <iframe  id="ifrPhoto" scrolling="no" frameborder="0" hidefocus="true" style="text-align:center;vertical-align:middle;border-style:none;margin:0px;width:100%;height:325px" src="/invite.php" ></iframe> 
                    <p style="visibility:hidden"><button class="simplemodal-close">Close</button> <span>(or press ESC or click the overlay)</span></p> 
                    </div></div>
                    </form>
                    </div>
                    <input type="hidden" id="key" value="'.$COMETKEY.'"/>
                    <input type="hidden" id="timetoadd" value="'.$TIMETOADD.'"/></div>
                    <script type="text/javascript" src="/Script/'.isComet($ISCOMET).'?y=1"></script>
                    <div style="text-align:center">
                    <p style="font-size:12px">
                    Powered by: <a href="http://www.frozenmountain.com/websync/" target="_blank">WebSync Comet Server</a>
                    <br />Created by: <a href="http://www.linkedin.com/pub/joel-capillo/26/8b4/ba3" target="_blank">Joel Capillo</a>
                    <br /><br /><a href="'.$urlmanager->createUrl('/about').'" >About</a> | <a href="'.$urlmanager->createUrl('/terms').'">Terms Of Use</a></p>
                    </div>
                  </body>
                </html>';
    #--------------------end of variables-----------------------------------------#
    
    #----------start of functions------------------------------------------------#
    
    #function that determine if we will hardcode the usercontainer or not
    function getUserContainer($arg)
    {
     if(!isset($_SESSION['changeserver']))
      {
        if ($arg)
        {
         return '<div id="users"><div id="userheader"><p style="text-align:center;font-weight:bold"><img src="/Images/users.png" class="clearico"/><span>Online Users</span></p></div><ul id="onlineuser"></ul></div>';
        }
        else 
          return;
      }
      else 
        return;	
    }
    
    #return the header or title  on chat messages container
    function getHeader($arg)
    {
        if(!isset($_SESSION['changeserver']))
        {
            if ($arg)
            {
               return '↓↓ Draw your ideas and share in real-time. ↓↓';
            }
            else 
               return 'Sorry, whiteboard is not available this time.';
        }
        else 
         return 'Sorry, whiteboard is not available this time.';	
    }
    
    #print the comet script
    function getcometScript($arg)
    {
            if(!isset($_SESSION['changeserver']))
            {
                    if ($arg)
                    {
                      return '<script type="text/javascript" src="http://sync3.frozenmountain.com/client.js?v=3.4.1"></script>';
                    }
                    else 
                      return '';
            }
            else 
              return '';
    }
    
    #function that determine if we're going to use comet server or just regular ajax
    function isComet($arg)
    {
        if(!isset($_SESSION['changeserver']))
        {
            if ($arg){
               return 'chatcomet.js';
            }
            else 
              return 'chat.js';
            
        }
        else 
          return 'chat.js';
    }
    
    
    
    #function that return the whole chatbody
    function concatenate($arg1, $argmiddle, $arg2, $arg3)
    {
        #concatenate the whole chat body
        $chatbody = $arg1.$_SESSION['pin'].$argmiddle.$_SESSION['username'].$arg2.$_SESSION['pin'].$arg3;
        return $chatbody;	
    }
    #------------------------end of functions----------------------------------#
    
    #start authenticating by checking first the url
  
    if (isset($_GET['pincode']))
    {
        $pinNumber = $_GET['pincode'];
        
        #looks like the user is not authenticated and try to enter the chatroom through the url
        if (!is_numeric($pinNumber)){
            echo $err;
            die();
        }
          
        $dbconn = new dbconn();
        
        if ($dbconn->connect($SQL_SERVER,$USERNAME,$PASSWORD,$DATABASE) != 1){
            echo $err;
            die();
        }
       
        $chat = new chatroom();
        $pin = intval($pinNumber);
        
        if(!$chat->isRoomExist($pin)){
            print($noexist);
            if(isset($_SESSION['pin'],$_SESSION['username'])){
                if($_SESSION['pin'] == $pinNumber){
                    unset($_SESSION['username'],$_SESSION['pin']);
                }
            }
            die();
        }
       
        #looks like the user is still login
        if (isset($_SESSION['username']) && isset($_SESSION['pin']))
        {
                        
            if( $_SESSION['pin'] == $pinNumber )
            {
                #user is reloading the same chatroom
                $body = concatenate($chatbody1, $chatmiddle, $chatbody2, $chatbody3);
                print($body);
            }
            else 
            {
               
                # try to inform or leave a message that this user just logged out from the old chatroom
                $oldusername = $_SESSION['username'];
                $oldpin = intval($_SESSION['pin']);
                # get the current unix time and convert to milliseconds
                $lasttime = time() * 1000;
                $message = $oldusername." exited the chatroom.";
                $admin = 'admin';
                
                #take note that $_POST['none'] is really not posted just to prevent publishing this message to comet server
                $chat->insertmessage($message,15,$lasttime,$oldpin,$admin,'',$COMETKEY,false,false);
                
                #delete this user from the old chatroom
                $chat->logout($oldusername);
                
                #clear the old sessions
                unset($_SESSION['username']);
                unset($_SESSION['pin']);
                
                #now begin logging-in the user to the new chatroom	
                $classname = "class";
                $randomUsername = $chat->generateUsername();
                
                while($chat->isUserNameExist($randomUsername))
                {
                  $randomUsername = $chat->generateUsername();
                }
                
                //check the status of what server we're using
                $cometstatus = 0;
                if(!$ISCOMET)
                {
                  $cometstatus = 1;
                }
                
                if ($chat->login($randomUsername,$classname,date('Y-m-d H:i:s'),$pin,$cometstatus) != 1){
                    #something wrong inserting the user on the database 
                    print($err);
                    die();
                }
                
                /* declare the session variable */
                $_SESSION['username'] = $randomUsername;
                $_SESSION['pin'] = $pinNumber;
                session_regenerate_id();
                $body = concatenate($chatbody1, $chatmiddle, $chatbody2, $chatbody3);
                print($body);
            }
                        
        }
        else 
        {
                $classname = "class";
                $randomUsername = $chat->generateUsername();
                
                while($chat->isUserNameExist($randomUsername))
                {
                   $randomUsername = $chat->generateUsername();
                }
                
                //check the status of what server we're using
                $cometstatus = 0;
                if(!$ISCOMET)
                {
                  $cometstatus = 1;
                }
                
                if ($chat->login($randomUsername,$classname,date('Y-m-d H:i:s'),$pin,$cometstatus) != 1){
                    print($err);
                    die();
                }        
                /* declare the session variable */
                $_SESSION['username'] = $randomUsername;
                $_SESSION['pin'] = $pinNumber;
                session_regenerate_id();
                $body = concatenate($chatbody1, $chatmiddle, $chatbody2, $chatbody3);
                print($body);
    
        }
                
            
    }
    elseif( $_SERVER["REQUEST_URI"] == $urlmanager->createUrl('/index') || $_SERVER["REQUEST_URI"] == $urlmanager->createUrl('/index').'/' || $_SERVER["REQUEST_URI"] == $urlmanager->createUrl('/chat') )
    {
      
       if (isset($_SESSION['username']) && isset($_SESSION['pin']))
        {
          $chaturl = $urlmanager->createUrl('/chatroom',$_SESSION['pin']);
          header("Location: $chaturl");
          die();
        }
        else 
           header("Location: /");
           die();
    }
    else
        echo($cantfind);


$errorhandler->Stop();

?>