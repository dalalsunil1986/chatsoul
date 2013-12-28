<?php
if(!isset($_SESSION)){
ob_start(); 
session_start();   
}
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/configwriter.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>Configuration</title>
<link rel="stylesheet" type="text/css" href="/CSS/configure.css"/> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
</head>
<body>
<div id="main">
<?php
$email = '';
$password = '';
if(count($_POST)){
try
   {
    $credentials = configwriter::login($_POST['adminEmail'],$_POST['adminPassword']);
    $_SESSION['admin_keys'] = $credentials; 
    header("Location: /setup/configure.php");
    die();
    
   }
   catch (Exception $ex)
   {
     
        $error = $ex->getMessage();
        if(isset($_SESSION['admin_keys'])){
           unset($_SESSION['admin_keys']);
        }
        if($error)
          echo '<div class="error">'.$error.'</div>';
          $email = $_POST['adminEmail'];
          $password = $_POST['adminPassword'];
     
     
   }
 
}
?>
      
   
    
    <div id="header">Administration Login</div>
     
      <!--start of form-->
       <form id="SignupForm" action="" method="post">
        
            <fieldset>
                
                <legend>Enter admin credentials to configure chat application.</legend>
               
                <label for="email">Email</label>
                <input id="email" type="text" name="adminEmail" value='<? echo $email; ?>'/>
                
                
                <label for="Password">Password</label>
                <input id="Password" type="password" name="adminPassword" value='<? echo $password; ?>'/>
                
                
            </fieldset>
           
            <p>
                <input id="login" type="submit" value="Login"/>
            </p>
            
        </form>
         <!--end of form-->
         
    </div>
</body>
</html>