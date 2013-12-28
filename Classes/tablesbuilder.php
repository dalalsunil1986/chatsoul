<?php

require_once ('dbconn.php');

class tablesbuilder
{
    
    
 private $chatroomtable = 'chatroom';
 private $cometuserstable = 'cometusers';
 private $filetable = 'file';
 private $messagestable = 'messages';
 private $userstable = 'users';
 
    public function createChatAppTables($configs)
    {
       
       $isCreated = false;
       
       $tables_array = array($this->chatroomtable,
                             $this->cometuserstable,
                             $this->filetable,
                             $this->messagestable,
                             $this->userstable);
       $database = '';
       $sqlserver = '';
       $username = '';
       $password = '';
       
       if(isset($configs) && !empty($configs) && count($configs) >= 4){
         $database = $configs['db_database'];
         $sqlserver = $configs['db_server'];
         $username = $configs['db_username'];
         $password = $configs['db_password'];
       }
       
       if(strlen($database) == 0 && strlen($sqlserver) == 0 && strlen($username) == 0 && strlen($password) == 0){
         throw new Exception('Cannot retrieve password, username, server for database.');
       }
       
      
       $dbconn = new dbconn();
       $databaseCreated = $dbconn->createDatabase($sqlserver,$username,$password,$database,$tables_array);
       
       if($databaseCreated == 0){
          throw new Exception('Error in creating your database. Your database name should only be one word and no space in between. Example is "ChatApplication".');
       }
       
       if($databaseCreated == 1){ //database exist already so do not create it
         return $isCreated;
         exit();
       }
       
      
       if ($dbconn->connect($sqlserver,$username,$password,$database) == 1) 
       {
           
           if(!$this->createChatroomTable())
              throw new Exception('Cannot create chatroom table.');
           
           if(!$this->createCometUsersTable())
            throw new Exception('Cannot create comet users table.');
           
           if(!$this->createFileTable())
             throw new Exception('Cannot create file table.');
          
           if(!$this->createMessageTable())
            throw new Exception('Cannot create chatroom table.');
           
           if(!$this->createUsersTable())
             throw new Exception('Cannot create users table.');
       }
       else
       {
         throw new Exception('Cannot connect to our database.');
       }
       
       $isCreated = true;
       return $isCreated;
        
    }
    
    
    
    
    public function createChatroomTable()
    {
       $isCreated = false;
       $query = "CREATE TABLE ".$this->chatroomtable." (
                     room_id int(11) NOT NULL AUTO_INCREMENT,
                     pin_num int(11) NOT NULL,
                     date_created datetime DEFAULT NULL,
                     ipadress int(11) DEFAULT NULL,
                     PRIMARY KEY (room_id),
                     UNIQUE KEY pin_num (pin_num))
                     ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=latin1";
           
           if (mysql_query($query)){
                $isCreated = true;
           }
           return $isCreated;
    }
    
    
    public function createCometUsersTable()
    {
          $isCreated = false;
          $query = "CREATE TABLE ".$this->cometuserstable."(
                     id int(11) NOT NULL AUTO_INCREMENT,
                     pin_num int(11) NOT NULL,
                     username varchar(100) NOT NULL,
                     PRIMARY KEY (id),
                     UNIQUE KEY username (username))
                     ENGINE=MyISAM AUTO_INCREMENT=1301 DEFAULT CHARSET=latin1";
           
           if (mysql_query($query)){
               $isCreated = true;
           }
           return $isCreated;
       
    }
    
    
    public function createFileTable()
    {
        $isCreated = false;
        $query = "CREATE TABLE ".$this->filetable." (
                   file_id int(11) NOT NULL AUTO_INCREMENT,
                   filename varchar(60) DEFAULT NULL,
                   file_data longblob NOT NULL,
                   user_id int(11) DEFAULT NULL,
                   file_dte bigint(20) DEFAULT NULL,
                   from_user varchar(30) DEFAULT NULL,
                   file_type varchar(50) DEFAULT NULL,
                   file_size varchar(50) DEFAULT NULL,
                   isdownloading int(11) NOT NULL DEFAULT '0',
                   percent int(11) DEFAULT '0',
                   PRIMARY KEY (file_id),
                   UNIQUE KEY user_id (user_id))
                   ENGINE=MyISAM AUTO_INCREMENT=303 DEFAULT CHARSET=latin1";
           
           if (mysql_query($query)){
              $isCreated = true;
           }
           
           return $isCreated;
        
    }
    
    public function createMessageTable()
    {
       $isCreated = false;
       $query = "CREATE TABLE ".$this->messagestable." (
                  msg_id int(11) NOT NULL AUTO_INCREMENT,
                  msg_dte bigint(20) NOT NULL,
                  message varchar(300) DEFAULT NULL,
                  user_id int(11) NOT NULL,
                  pin_num int(11) NOT NULL,
                  m_username varchar(30) NOT NULL,
                  PRIMARY KEY (msg_id))
                  ENGINE=MyISAM AUTO_INCREMENT=4979 DEFAULT CHARSET=latin1";
           
           if (mysql_query($query)){
               $isCreated = true;
           }
           
           return $isCreated;
        
    }
    
    
    public function createUsersTable()
    {
         $isCreated = false;
         $query = "CREATE TABLE ".$this->userstable." (user_id int(11) NOT NULL AUTO_INCREMENT,
                    username varchar(30) NOT NULL,class varchar(30) DEFAULT NULL,
                    logged_in tinyint(4) NOT NULL DEFAULT '0',
                    last_logged datetime DEFAULT NULL,
                    pin_num int(11) NOT NULL,
                    status tinyint(4) DEFAULT NULL,
                    iscomet tinyint(4) DEFAULT '0',
                    PRIMARY KEY (user_id),
                    UNIQUE KEY username (username))
                    ENGINE=MyISAM AUTO_INCREMENT=1351 DEFAULT CHARSET=latin1 ";
           
           if (mysql_query($query)){
              $isCreated = true;
           }
           
        return $isCreated;
    }


}

?>