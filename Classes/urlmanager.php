<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/db.php');

class urlmanager
{
    
    
    
    
  public function createUrl($url,$pincode = null){
        if(URLFORMAT == 0)
        {
            
            if($pincode){
                $url='/chatroom';
                return $url.'/'.$pincode;
            }
            else
              return $url;
        }
        else
        {
            if($pincode)
            {
                $url='/chat';
                return $url.'.php?pincode='.$pincode;
            }
            else
              return $url.'.php';
        }
    
  }
  
  public function createUri(){
    if(URLFORMAT == 0)
    {
        return '/chatroom/';
    }
    else
       return '/chat.php?=';
  }
    
    
    
    
}

?>