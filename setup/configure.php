<?php
ob_start();
session_start();
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/configwriter.php');
    $values = array();
    $login_url = '/setup/login.php';
    if(isset($_SESSION['admin_keys'])){
       $values = configwriter::checkAdmin($login_url,$_SESSION['admin_keys']);
    }
    else{
     $values = configwriter::checkAdmin($login_url);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >

<head>
    <title>Configuration</title>
    <link rel="stylesheet" type="text/css" href="/CSS/configure.css?j=1"/> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
    <script type="text/javascript" src="/Script/formToWizard.js"></script>
    <script type="text/javascript">
    
    function checkCometOption(){
        if($("#isComet").val() == 0){
            $("#keyDiv").hide('slow');
         }
         else
           $("#keyDiv").show('medium');
    }
    
    $(function() {
        $("#SignupForm").formToWizard({ submitButton: 'SaveAccount'
                });
        
        checkCometOption();
        
        $("#isComet").change(function(){
           checkCometOption();
        });
        
    });
     </script>
</head>

<body>
   
    

<div id="main">
     
     <?
        
        $oldchatroom_expiration = 0;
        
        if(strlen($values['max_chatroom_expire']) > 0){
            $oldchatroom_expiration = configwriter::integerStr($values['max_chatroom_expire']);
        }
        
        if(count($_POST)){
         
         //set the values
         $values['admin_username'] = $_POST['adminEmail'];
         $values['admin_password'] = $_POST['adminPassword'];
         $values['admin_confirm_password'] = $_POST['confirmPassword'];
         
         $values['db_password'] = $_POST['dbPassword'];
         $values['db_username'] = $_POST['dbUsername'];
         $values['db_server'] = $_POST['dbServer'];
         $values['db_database'] = $_POST['dbDatabase'];
         
         $values['is_comet'] = $_POST['isComet'];
         $values['comet_key'] = $_POST['cometKey'];
         
         $values['smtp_auth'] = $_POST['smtpAuth'];
         $values['smtp_username'] = $_POST['smtpUserName'];
         $values['smtp_password'] = $_POST['smtpPassword'];
         $values['smtp_server'] = $_POST['smtpServer'];
         
         $values['site_title'] = $_POST['title'];
         $values['site_slogan'] = $_POST['slogan'];
         $values['site_domain_name'] = $_POST['domainName'];
         
         $values['is_purify'] = $_POST['isPurify'];
         $values['max_message_expire'] = $_POST['maxMessageAge'];
         
         $values['max_chatroom_count'] = $_POST['maxChatroomCount'];
         $values['max_chatroom_expire'] = $_POST['maxChatroomExpire'];
         $values['pin_number_length'] = $_POST['pinLength'];
         
         $values['url_format'] = $_POST['urlFormat'];
         $values['font_title'] = $_POST['fontTitle'];
         $values['font_size_title'] = $_POST['fontSizeTitle'];
       
         try
           {
            
                $configwriter = new configwriter($values);
                $isNewDatabase = $configwriter->configureChatApp();
                
                if($isNewDatabase == true)//user created a new database so delete all the site's session and cookies
                { 
                    if(isset($_SESSION))
                      $configwriter->refreshSession($_SESSION);
                    if (isset($_SERVER['HTTP_COOKIE']))
                       $configwriter->refreshCookies($_SERVER['HTTP_COOKIE']);
                }
                
                if(isset($_SESSION['admin_keys'])){
                    unset($_SESSION['admin_keys']);
                }
                
                if(isset($_COOKIE['chatroom']) && $oldchatroom_expiration != $values['max_chatroom_expire']){
                 $configs = configwriter::configs();
                 $configwriter->editCookieChatroom($_COOKIE['chatroom'],configwriter::integerStr($configs['max_chatroom_expire'])); //change this users chatroom cookie expiration  
                }
                header("Location: /");
                die();
            
           }
           catch (Exception $ex)
           {
             
             $error = $ex->getMessage();
             configwriter::unConfigure();
             
             if($error)
                echo '<div class="error">Error: '.$error.'</div>';
            
              $values['is_comet'] = configwriter::boolInt($values['is_comet']);
              $values['smtp_auth'] = configwriter::boolInt($values['smtp_auth']);
              $values['is_purify'] = configwriter::boolInt($values['is_purify']);
             
             
           }
         
         
        }

        
        
        
    ?>
      
   
    
    <div id="header">Chat Application Configuration</div>
     
      <!--start of form-->
       <form id="SignupForm" action="" method="post">
        
        <fieldset>
            
            <legend>Admin Account</legend>
            
            <p class="title">Set-up your admin account.</p>
            
            <label for="email">Email</label>
            <input id="email" type="text" name="adminEmail" value='<? echo $values['admin_username']; ?>'/>
            
            
            <label for="Password">Password</label>
            <input id="Password" type="password" name="adminPassword" value='<? echo $values['admin_password']; ?>'/>
            
            <label for="dPassword">Confirm Password</label>
            <input id="dPassword" type="password" name="confirmPassword" value='<? echo $values['admin_confirm_password']; ?>'/>
            
        </fieldset>
        
        <fieldset>
            
            <legend>Database</legend>
             <p class="title">Set-up your database connection.</p>
            
            <label for="dbUsername">Username <span class='hint'>( Hint: This could be "root" on your local development/web server. )</span></label>
            <input id="dbUsername" type="text" name="dbUsername" value='<? echo $values['db_username']; ?>'/>
            
            
            <label for="dbPassword">Password <span class='hint'>( Hint: You could leave this empty. )</span></label>
            <input id="dbPassword" type="password" name="dbPassword" value='<? echo $values['db_password']; ?>'/>
            
            <label for="dbServer">Database Server/IP Address <span class='hint'>( Hint: This could be "localhost" on your local development/web server. )</span></label>
            <input id="dbServer" type="text" name="dbServer" value='<? echo $values['db_server']; ?>'/>
            
            <label for="dbDatabase">Database Name <span class='hint'>( Hint: This could be the same with your username if you're in a shared hosting such as GoDaddy Shared Hosting Account. )</span></label>
            <input id="dbDatabase" type="text" name="dbDatabase" value='<? echo $values['db_database']; ?>'/>
        
        </fieldset>
        
        <fieldset>
            
            <legend>Comet Server</legend>
             <p class="title">Set-up the chat server you're going to use.</p>
             
            <label for="isComet">Enable Comet Server</label>
            <select id="isComet" name="isComet">
                <?
                
                $iscomet = 1;
                
                if(!configwriter::boolstr($values['is_comet'])){
                   $iscomet = 0; 
                }
               
                $option_array = array(1=>'True',0=>'False');
                foreach($option_array as $k=>$v){
                    if($k == $iscomet){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                
                ?>
            </select>
          
          <div id="keyDiv">
            <label for="cometKey">Public API Key From <a href="http://www.frozenmountain.com/websync/" target="_blank">Frozen Mountain</a></label>
            <input id="cometKey" type="text" name="cometKey" value='<? echo $values['comet_key']; ?>'/>
          </div>
            
           </fieldset>
        
        <fieldset>
            
            <legend>Email Server</legend>
            <p class="title">Set-up your email server.</p>
            
            <label for="smtpUserName">Username</label>
            <input id="smtpUserName" type="text" name="smtpUserName" value='<? echo $values['smtp_username']; ?>'/>
            
            <label for="smtpPassword">Password</label>
            <input id="smtpPassword" type="password" name="smtpPassword" value='<? echo $values['smtp_password']; ?>'/>
            
            <label for="smtpServer">Email Server <span class='hint'>( Hint: This could be "smtp.gmail.com" if you're using Gmail as an email server. )</span></label>
            <input id="smtpServer" type="text" name="smtpServer" value='<? echo $values['smtp_server']; ?>'/>
            
            <label for="smtpAuth">Use Email Authentication</label>
              <select id="smtpAuth" name="smtpAuth">
                <?
                
                $isauth = 1;
                
                if(!configwriter::boolstr($values['smtp_auth'])){
                   $isauth = 0; 
                }
              
                $option_array = array(1=>'True',0=>'False');
                foreach($option_array as $k=>$v){
                    if($k == $isauth){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
            </select>
            
        </fieldset>
        
        
        <fieldset>
            
            <legend>Site</legend>
             <p class="title">Set-up your chat application title, domain name, etc.</p>
            
            <label for="title">Title</label>
            <input id="title" type="text" name="title" value='<? echo $values['site_title']; ?>'/>
            
            <label for="slogan">Slogan</label>
            <input id="slogan" type="text" name="slogan" value='<? echo $values['site_slogan']; ?>'/>
          
            <label for="domainName">Domain Name</label>
            <input id="domainName" type="text" name="domainName" value='<? echo $values['site_domain_name']; ?>'/>
        
        </fieldset>
        
         <fieldset>
            
            <legend>Utilities</legend>
            <p class="title">Configure your utilities.</p>
            
            <label for="isPurify">Enable Chat Messages Filter</label>
            <select id="isPurify" name="isPurify">
                <?
                
                $ispure = 1;
                
                if(!configwriter::boolstr($values['is_purify'])){
                   $ispure = 0; 
                }
                
                
                $option_array = array(0=>'False',1=>'True');
                foreach($option_array as $k=>$v){
                    if($k == $ispure){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
            </select>
            
            <label for="maxMessagesAge">Set Maximum Expiration For Chat Messages</label>
             
              <select id="maxMessagesAge" name="maxMessageAge">
                <?
                $option_array = array(''=>'',1=>'1 Day',2=>'2 Days',3=>'3 Days');
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['max_message_expire'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
               
               
            <label for="maxChatroomExpire">Set Maximum Days A Chatroom Can Stay Active</label>
             
              <select name="maxChatroomExpire">
                <?
                $option_array = configwriter::createDropdown(100,'day');
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['max_chatroom_expire'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
               
            
            <label for="maxChatroomCount">Set Maximum Count A User Can Create A Chatroom</label>
             
              <select name="maxChatroomCount">
                <?
                $option_array = configwriter::createDropdown(100);
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['max_chatroom_count'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
            
            
            <label for="pinLength">Set The Length Of A Chatroom Pin Number</label>
             
              <select name="pinLength">
                <?
                $option_array = configwriter::createDropdown(9);
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['pin_number_length'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
               
               
               
               <label for="urlFormat">Select Application Url Format</label>
             
              <select name="urlFormat">
                <?
                $option_array = array(''=>'',0=>'Mod Rewrite Enabled',1=>'Regular Url/Mod Rewrite Disabled');
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['url_format'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
              
              
        </fieldset>
        
        <fieldset>
             
             <legend>Style</legend>
             <p class="title">Choose the design and look for the application title.</p>
             
             <label for="fontTitle">Select Application Title Font Style</label>
               <select name="fontTitle">
                <?
                $option_array = array(''=>'','Tangerine'=>'Tangerine','Capriola'=>'Capriola',
                                      'Amarante'=>'Amarante','Courgette'=>'Courgette','Eagle Lake'=>'Eagle Lake',
                                      'Henny Penny'=>'Henny Penny','Caesar Dressing'=>'Caesar Dressing',
                                      'Advent Pro'=>'Advent Pro','Anonymous Pro'=>'Anonymous Pro','Titan One'=>'Titan One',
                                       'Merienda One'=>'Merienda One','Dr Sugiyama'=>'Dr Sugiyama','Karla'=>'Karla','Ropa Sans'=>'Ropa Sans',
                                       'Chivo'=>'Chivo','Chango'=>'Chango');
                
                foreach($option_array as $k=>$v){
                    if($k == $values['font_title']){
                        echo '<option value="'.$k.'" selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value="'.$k.'">'.$v.'</option>';  
                    }
                }
                ?>
               </select>
               
               <label for="fontSizeTitle">Select Application Title Font Size</label>
               <select name="fontSizeTitle">
                <?
                $option_array = configwriter::createDropdown(100,'px');
                foreach($option_array as $k=>$v){
                    if($k == configwriter::integerStr($values['font_size_title'])){
                        echo '<option value='.$k.' selected>'.$v.'</option>';
                    }
                    else{
                      echo '<option value='.$k.'>'.$v.'</option>';  
                    }
                }
                ?>
               </select>
               
        </fieldset>
        
        <p>
            <input id="SaveAccount" type="submit" value="Configure"/>
        </p>
        
        </form>
         <!--end of form-->
         
    </div>
</body>
</html>
