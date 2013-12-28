<?php
ob_start();
session_start( );
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/urlmanager.php');
$urlmanager = new urlmanager();

if (isset($_SESSION['username'],$_SESSION['pin']))
{
    
    if(!isset($_SESSION['retries'])){
            $_SESSION['retries'] = 1;
    }else{
        
        $_SESSION['retries'] += 1;
        
        if($_SESSION['retries'] > 3){
             unset($_SESSION['retries']);
             $redirecturl = '/Errors'.$urlmanager->createUrl("/lostconnection");
             header("Location: $redirecturl");
             die();
        }
       
    }
    
    $chaturl = $urlmanager->createUrl('/chatroom',$_SESSION['pin']);
    header("Location: $chaturl");
    die();

}
else 
   header("Location: /");	

?>