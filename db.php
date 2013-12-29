<?php
   require_once(__DIR__.'/Classes/configwriter.php');
   if(!configwriter::isConfigured()){ 
           header("Location: /setup/configure.php");
           die();
    }
    
   $configs = configwriter::configs();
   
   $ADMIN_EMAIL=$configs['admin_username'];
   $SQL_SERVER=$configs['db_server']; 
   $USERNAME=$configs['db_username'];       
   $PASSWORD=$configs['db_password'];     
   $DATABASE=$configs['db_database'];   
   $ISCOMET = configwriter::boolstr($configs['is_comet']);
   $COMETKEY=$configs['comet_key']; 
   $FROMNAME=$configs['admin_name'];
   $FROMADDRESS=$configs['admin_email'];
   $SMTPAUTH=configwriter::boolstr($configs['smtp_auth']);
   $SMTPUSERNAME=$configs['smtp_username']; 
   $SMTPPASSWORD=$configs['smtp_password'];
   $SMTPSERVER=$configs['smtp_server'];
   $TITLE=$configs['site_title'];
   $SAY=$configs['site_slogan'];
   $SITE=$configs['site_name'];
   $WEBSITE=$configs['site_domain_name'];
   $ISPURIFYHTML = configwriter::boolstr($configs['is_purify']);
   $OLDMESSAGEAGE = configwriter::integerStr($configs['max_message_expire']);
   $MAXCHATROOMCOUNT = configwriter::integerStr($configs['max_chatroom_count']);
   $MAXCHATROOMEXPIRATION = configwriter::integerStr($configs['max_chatroom_expire']);
   $PINLENGTH = configwriter::integerStr($configs['pin_number_length']);
   $URLFORMAT = configwriter::integerStr($configs['url_format']);
   $TITLEFONT = $configs['font_title'];
   $TITLEFONTSIZE = configwriter::integerStr($configs['font_size_title']);
   
   $MAXFILETIMEPENDING = 120000;//Time in milliseconds for a file to be pending during transfer
   $OFFSET = 50;//(-)use as constant to our php script @ get_messages.php.
   $TIMETOADD = 230;//(+)use as constant to our javascript code @ chatcomet.js
   
   //values are used inside a class
   define('SQLSERVER',$SQL_SERVER);
   define('USERNAME',$USERNAME);
   define('PASSWORD',$PASSWORD);
   define('DATABASE',$DATABASE);
   define('SMTPUSERNAME',$SMTPUSERNAME);
   define('SMTPPASSWORD',$SMTPPASSWORD);
   define('SMTPSERVER',$SMTPSERVER);
   define('SMTPAUTH',$SMTPAUTH);
   define('ADMINEMAIL',$ADMIN_EMAIL);
   define('MAXCHATROOMEXPIRATION',$MAXCHATROOMEXPIRATION);
   define('PINLENGTH',$PINLENGTH);
   define('URLFORMAT',$URLFORMAT);
   define('TITLEFONT',$TITLEFONT);
   define('WEBSITE',$WEBSITE);
   define('TITLEFONTSIZE',$TITLEFONTSIZE);
?> 
