<?php

set_include_path(substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'Classes')));
include_once ('Classes/encryption.php');
include_once ('Classes/tablesbuilder.php');
include_once ('Classes/validation.php');

class configwriter
{
    
    private $adminUserName;//should be the admin email($adminEmail)
    private $adminPassword;
    
    private $adminConfirmPassword;
    
    
    private $dbPassword;
    private $dbUsername;
    private $dbServer;
    private $dbDatabase;
    
    private $cometKey;
    private $iscomet;//boolean
    
    private $adminName;
    private $adminEmail;
    private $smtpAuth;//boolean
    private $smtpUsername;
    private $smtpPassword;
    private $smtpServer;
    
    private $siteTitle;
    private $siteSlogan;
    private $siteName;//lower case of site title
    private $siteDomainName;
    
    private $isPurifyHtml;//boolean
    
    private $maxAgeMessages;//integer
    
    private $maxchatroomcount;
    private $maxchatroomexpire;
    private $chatroomPinLength;//length of a chatroom pin number
    
    private $urlFormat;
    
    private $fontTitle;
    private $fontSizeTitle;
    
    
    
    const fileUrl = '/configs/dbaseconfig/config.txt';
    const folderName = '/configs/dbaseconfig';
    
    
    function __construct($values) {
      
      $validation = new validation();
      
      $this->adminUserName = $validation->validate(array('required','email'),$values['admin_username'],'Admin Email');
      $this->adminConfirmPassword = $validation->validate('required',$values['admin_confirm_password'],'Confirm Password');
      $this->adminPassword = $validation->validate(array('required','password'),$values['admin_password'],'Admin Password',$this->adminConfirmPassword);
      
      $this->dbPassword = $values['db_password'];
      $this->dbUsername = $validation->validate('required',$values['db_username'],'Database Username');
      $this->dbServer = $validation->validate('required',$values['db_server'],'Database Server');
      $this->dbDatabase = $validation->validate('required',$values['db_database'],'Database Name');
      
      $this->iscomet = $validation->validate('required',$values['is_comet'],'Comet Server Option');
      
      if($this->iscomet == 1){
        $this->cometKey = $validation->validate('required',$values['comet_key'],'Comet Key');
      }
      else{
        $this->cometKey = '';
      }
      
      $this->adminName = 'admin';//always set this to "admin"
      
      if(!isset($values['admin_email']) || strlen($values['admin_email']) == 0){
        $this->adminEmail = $this->adminUserName;
      }
      else{
        $this->adminEmail = $validation->validate(array('required','email'),$values['admin_email'],'Admin Email');
      }
      
      //smtpserver
      $this->smtpAuth = $validation->validate('required',$values['smtp_auth'],'SMTP Auth Option');
      $this->smtpUsername = $validation->validate('required',$values['smtp_username'],'SMTP Username');
      $this->smtpPassword = $validation->validate('required',$values['smtp_password'],'SMTP Password');
      $this->smtpServer = $validation->validate('required',$values['smtp_server'],'SMTP Server');
      
      //site config
      $this->siteTitle = $validation->validate('required',$values['site_title'],'Chat Application Title');
      $this->siteSlogan = $validation->validate('required',$values['site_slogan'],'Chat Application Slogan');
      $this->siteName = strtolower($this->siteTitle);
      $this->siteDomainName = $validation->validate(array('required','url'),$values['site_domain_name'],'Chat Application Domain Name');
      
      //utilities
      $this->isPurifyHtml = $validation->validate('required',$values['is_purify'],'Chat Messages Filter Option');
      $this->maxAgeMessages = $validation->validate(array('required','numeric'),$values['max_message_expire'],'Chat Messages Expiration');
      
      $this->maxchatroomcount = $validation->validate(array('required','numeric'),$values['max_chatroom_count'],'Maximum Number Of Times To Create A Chatroom');
      $this->maxchatroomexpire = $validation->validate(array('required','numeric'),$values['max_chatroom_expire'],'Chatroom Expiration');
      $this->chatroomPinLength = $validation->validate(array('required','numeric'),$values['pin_number_length'],'Chatroom Pin Number Length');
      
      $this->urlFormat = $validation->validate(array('required','numeric'),$values['url_format'],'Application Url Format');
      
      $this->fontTitle = $validation->validate('required',$values['font_title'],'Application Title Font Family');
      $this->fontSizeTitle = $validation->validate(array('required','numeric'),$values['font_size_title'],'Application Title Font Size');
    }
    
    //delete all the site cookies
    public function refreshCookies($site_cookie){
            $cookies = explode(';', $site_cookie);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-1000);
                setcookie($name, '', time()-1000, '/');
            }
       
    }
    
    public function editCookieChatroom($chatroom_cookie,$age){ //set the chatroom's new cookie expiration
          setcookie('chatroom', $chatroom_cookie, time()+60*60*24*$age);
    }
    
    //delete all the sites session
    public function refreshSession($session){
        $session = array();
        session_destroy();
    }
    
    
    public function isConfigured()
    {
     
      $root = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'Classes')-1);
    
      if(!file_exists($root.self::fileUrl)){
         return false;
        }
        else
         return true;
    }
    
    //delete the configuration file and folder
    public static function unConfigure()
    {
        if(self::isConfigured()){
            @unlink($_SERVER['DOCUMENT_ROOT'].self::fileUrl);
              @rmdir($_SERVER['DOCUMENT_ROOT'].self::folderName);
            }
    }
    
    
    public function createFolder()
    {
      $folder = $_SERVER['DOCUMENT_ROOT'].self::folderName;
      if(!mkdir($folder)){
        return false;
       }
       else
        return true;
    }
    
    public function createFile()
    {
       
       $isWritten = false;
       
       $filename = $_SERVER['DOCUMENT_ROOT'].self::fileUrl;
       
       if (!$handle = fopen($filename, 'w+')) {
            return $isWritten;
            exit;
        }
        
        if (fwrite($handle, $this->writeContent()) === FALSE) {
            return $isWritten;
            exit;
        }
        
        fclose($handle);
        
        $isWritten = true;
        return $isWritten;
        
    }
    
    public function clearConfigFile(){
        
       $isDeleted = false;
       
       $filename = $_SERVER['DOCUMENT_ROOT'].self::fileUrl;
       
       if (!$handle = fopen($filename, 'w+')) {
            return $isDeleted;
            exit;
        }
        
        if (fwrite($handle,"") === FALSE) {
            return $isDeleted;
            exit;
        }
        
        fclose($handle);
        
        $isDeleted = true;
        return $isDeleted;
        
    }
    
    public function configureChatApp()
    {
        
        if($this->isConfigured()){ //check if the config file exist already so we're just updating
           
           if(!$this->clearConfigFile()) //make sure to clear the file first if it exist
                throw new Exception('An error occured while clearing the old configuration.');
        }
        else{
        
           if(!$this->createFolder())
                throw new Exception('An error occured while creating the configuration folder.'); 
        }
        
        if(!$this->createFile()) //write our new configs to the text file
            throw new Exception('An error occured while writing the new configuration.');
        
        $tablebuilder = new tablesbuilder();    
        
        $isNewDatabase = $tablebuilder->createChatAppTables(self::configs());
        
        return $isNewDatabase;
    }
    
    
    public static function checkAdmin($login_url,$session = null){
            
            $values = array();
            
            if(self::isConfigured() && !isset($session)){ 
              header("Location: $login_url");
              die();
            }
            elseif(self::isConfigured() && isset($session)){
            
                $configs = self::configs();
                $admin_keys = $session;
                
                if($admin_keys['admin_username'] == $configs['admin_username']
                   && $admin_keys['admin_password'] == $configs['admin_password'])
                {
                   
                   $values = $configs; 
                }
                else{
                    unset($session);
                    header("Location: $login_url");
                    die(); 
                }
            
            }
            else
            {
            
                $values['admin_username'] = '';
                $values['admin_password'] = '';
                $values['admin_confirm_password'] = '';
                
                $values['db_password'] = '';
                $values['db_username'] = '';
                $values['db_server'] = '';
                $values['db_database'] = '';
                
                $values['is_comet'] = '';
                $values['comet_key'] = '';
                
                $values['smtp_auth'] = '';
                $values['smtp_username'] = '';
                $values['smtp_password'] = '';
                $values['smtp_server'] = '';
                
                $values['site_title'] = '';
                $values['site_slogan'] = '';
                //$values['site_name'] = '';
                $values['site_domain_name'] = '';
                
                $values['is_purify'] = '';
                $values['max_message_expire'] = '';
                
                $values['max_chatroom_count'] = '';
                $values['max_chatroom_expire'] = '';
                $values['pin_number_length'] = '';
                
                $values['url_format'] = '';
                
                $values['font_title'] = '';
                $values['font_size_title'] = '';
            
            }

        return $values;
    }
    
    //authenticate the user
    //@ return -> admin password and email if authenticated
    public static function login($email,$password)
    {
        $configs = self::configs();
        
        if(strlen($email) == 0)
           throw new Exception('Please enter your admin email.');
        if(strlen($password) == 0)
           throw new Exception('Please enter your password.');
        if(strlen($email) == 0 && strlen($password) == 0)
           throw new Exception('Please enter your admin email and password.');
           
        if($email == $configs['admin_username'] && $password == $configs['admin_password']){
            return array('admin_username'=>$configs['admin_username'],'admin_password'=>$configs['admin_password']);
        }
        else{
         throw new Exception('Login credentials does not match from the record. Please enter the correct email and password.');
        }
    }
    
    
    public function writeContent(){
      
      $encryption = new Encryption(); 
      
      $content = $encryption->encode($this->adminUserName)."\n";
      $content .= $encryption->encode($this->adminPassword)."\n";
      
      $content .= $encryption->encode($this->dbPassword)."\n";
      $content .= $encryption->encode($this->dbUsername)."\n";
      $content .= $encryption->encode($this->dbServer)."\n";
      $content .= $encryption->encode($this->dbDatabase)."\n";
      
      $content .= $encryption->encode($this->cometKey)."\n";
      $content .= $encryption->encode($this->strbool($this->iscomet))."\n";
      
      $content .= $encryption->encode($this->adminName)."\n";
      $content .= $encryption->encode($this->adminEmail)."\n";
      $content .= $encryption->encode($this->strbool($this->smtpAuth))."\n";
      $content .= $encryption->encode($this->smtpUsername)."\n";
      $content .= $encryption->encode($this->smtpPassword)."\n";
      $content .= $encryption->encode($this->smtpServer)."\n";
      
      $content .= $encryption->encode($this->siteTitle)."\n";
      $content .= $encryption->encode($this->siteSlogan)."\n";
      $content .= $encryption->encode($this->siteName)."\n";
      $content .= $encryption->encode($this->siteDomainName)."\n";
      
      $content .= $encryption->encode($this->strbool($this->isPurifyHtml))."\n";
      
      $content .= $encryption->encode($this->strInteger($this->maxAgeMessages))."\n";
      
      $content .= $encryption->encode($this->strInteger($this->maxchatroomcount))."\n";
      $content .= $encryption->encode($this->strInteger($this->maxchatroomexpire))."\n";
      $content .= $encryption->encode($this->strInteger($this->chatroomPinLength))."\n";
      
      $content .= $encryption->encode($this->strInteger($this->urlFormat))."\n";
      
      $content .= $encryption->encode($this->fontTitle)."\n";
      $content .= $encryption->encode($this->strInteger($this->fontSizeTitle));
      
      return $content;
      
    }
    
    
    //return key array of configs
    public static function configs()
    {
       
        $configArray = array();
        
        $cofigKeyArray = array('admin_username',
                               'admin_password',
                               'db_password',
                               'db_username',
                               'db_server',
                               'db_database',
                               'comet_key',
                               'is_comet',
                               'admin_name',
                               'admin_email',
                               'smtp_auth',
                               'smtp_username',
                               'smtp_password',
                               'smtp_server',
                               'site_title',
                               'site_slogan',
                               'site_name',
                               'site_domain_name',
                               'is_purify',
                               'max_message_expire',
                               'max_chatroom_count',
                               'max_chatroom_expire',
                               'pin_number_length',
                               'url_format',
                               'font_title',
                               'font_size_title',
                               );
        
        $encryption = new Encryption();
      
        if(!self::isConfigured()){
            return $configArray;
            exit();
        }
        
        $root = substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'Classes')-1);
        $data = file_get_contents($root.self::fileUrl); 
        $convert = explode("\n", $data);
        
        for ($i=0;$i<count($convert);$i++)  
        {
          $configArray[$cofigKeyArray[$i]] = $encryption->decode($convert[$i]); 
        }
        
        return $configArray;
        
    }
    
    //helper functions
    
    public function strbool($value)
    {
      if($value == 1){
        return 'true';
      }
      else
       return 'false';
    }
    
    public function boolstr($value)
    {
        if($value == 'true'){
            return true;
        }
        else
         return false;
    }
    
    public function strInteger($value){
        return (string)$value;
    }
    
    public function integerStr($value)
    {
        return intval($value);
    }
    
    public function boolInt($value){
        if($value == 1){
                return true;
        }
        else
         return false;
    }
    
    public function createDropdown($count,$word = null){
        
        $option = array();
        
        for($i = 0;$i<=$count;$i++){
            if($i == 0){
                $option[''] = '';
            }
            else{
                
                if($i == 1 && isset($word)){
                   $option[$i] = $i .' '.$word;  
                }
                elseif($i > 1 && isset($word)){
                   $option[$i] = $i .' '.$word.'s';   
                }
                else
                   $option[$i] = (string)$i;
            }
        }
        
        return $option;
    }

    
    
}


?>