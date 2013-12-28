<?php

set_include_path(substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'Classes')));
include_once ('Classes/configwriter.php');

class chatroomscleaner
{
        
      
    private function ismessageold($arg)//function that computes if the latest message from a chatroom is older than one day
    {
            $sql = 'SELECT msg_dte FROM messages where pin_num = "'.$arg.'" ORDER BY msg_dte DESC;';
            $result = mysql_query($sql);
            if(mysql_num_rows($result))
            {
                if (mysql_data_seek($result, 0))
                { 
                    $row = mysql_fetch_assoc($result); //Get first record
                    $latest = $row['msg_dte'];
                    $onedayago = time()*1000 - 86400000;
                    if ($latest < $onedayago)
                    {
                     return true;
                    }
                    else 
                    return false;
                   
                }
            }
            else 
            {
             return true;
            }
            
            mysql_free_result($result);
    }
    
    
        
    private function isuserold($arg)//function that computes if the last logged in of the user is older than one day
    {
            $sql = 'SELECT last_logged FROM users where pin_num = "'.$arg.'" ORDER BY last_logged DESC;';
            $result = mysql_query($sql);
            if(mysql_num_rows($result))
            {
                if (mysql_data_seek($result, 0))
                { 
                    $row = mysql_fetch_assoc($result); //Get first record
                    $latest = strtotime($row['last_logged']);
                    $onedayago = time() - 86400;
                    if ($latest < $onedayago)
                    {
                     return true;
                    }
                    else 
                    return false;
                }
            }
            else 
            {
            return true;
            }
            mysql_free_result($result);
    }

    
    private function ischatroomactive($pin)//function that determine if a chatroom is inactive or active
    {
        if($this->isuserold($pin) && $this->ismessageold($pin))
            return false;
        else 
            return true;
            
    }

   
    private function DeleteAll($pin) //function that deletes chatroom and the associated messages and users
    {
        $sql = 'DELETE FROM chatroom where pin_num = "'.$pin.'" ;';
        mysql_query($sql);
        
        $query = 'DELETE FROM messages where pin_num = "'.$pin.'" ;';
        mysql_query($query);
        
        $que = 'DELETE FROM users where pin_num = "'.$pin.'" ;';
        mysql_query($que);
    }

    private function deleteoldfiles()
    {
    
        $onedayago = time()*1000 - 86400000;
        $query = 'DELETE FROM file where file_dte <= "'.$onedayago.'" ;';
        mysql_query($query);
    
    }
    
    
    
    public function deleteTemporaryFiles($hour,$tempfolder)//cleans up temporary files from folders
    {
       
        $dir = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'Classes')).$tempfolder;
	$seconds_old = 1800 * $hour;
        
        $directory = str_replace('//','/',$dir);
                
        if( !$dirhandle = opendir($directory) )
        return;
        while( false !== ($filename = readdir($dirhandle)) )
        {
                
            if( $filename != "." && $filename != ".." )
            {
                    
             $filename = $directory. "/". $filename;
             if( filemtime($filename) < (time() - $seconds_old) )
                    @unlink($filename);
                            
            }
        }
          
    }
    
    
    public function cleanup()
    {
        $configs = configwriter::configs(); 
        if($conn = mysql_connect($configs['db_server'], $configs['db_username'],$configs['db_password']))
        {
            if (mysql_select_db($configs['db_database'], $conn))
            {
                $this->deleteoldfiles();
                $this->deleteTemporaryFiles(2,'temporaryfiles'); //delete files that were 2 hours old on the temporaryfiles folder
                
                $sql = 'SELECT pin_num FROM chatroom WHERE DATE(date_created) <= DATE(DATE_ADD(NOW(),
                        INTERVAL -'.configwriter::integerStr($configs['max_chatroom_expire']).' DAY));';
               
                if ($result = mysql_query($sql)) 
                {
                   
                   while($row = mysql_fetch_assoc($result))
                   {
                        $pin = $row['pin_num'];
                        $this->DeleteAll($pin);
                   }
                  
                   echo 'Clean up finished.';
                }
                
                mysql_free_result($result);
            }
            else
                mysql_close($conn);
        }

    }

        
        
    
}




?>