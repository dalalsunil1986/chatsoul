<?php

class dbconn 
{

	#connect to database; returns 1 if successfully connected else 0 if unsuccessful
	
	public function connect($SQL_SERVER, $USERNAME, $PASSWORD, $DATABASE) 
	{
		
		if($conn = mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD))
		{
		
		if(mysql_select_db($DATABASE,$conn))
		{
			return 1;
		}
		else 
		  $this->disconnect($conn);
		  return 0;
		  
		
		}
		else 
		return 0;
		
	}
	
	#disconnect to database
	private function disconnect($connection)
	{
	 mysql_close($connection);
	}
	
	//returns an integer
	public function createDatabase($SQL_SERVER, $USERNAME, $PASSWORD,$DATABASE,$tablesArray)
	{
          
	  $isCreated = 0;
	  
	  if($conn = mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD))
	  {
		
		if($this->isDbExist($DATABASE,$conn,$tablesArray)){
		  $isCreated = 1; //return 1 if database exist already
		  return $isCreated;
		  exit();
		}
		
		if(strtolower($USERNAME) != strtolower($DATABASE)){//for private server
		   $sql = 'CREATE DATABASE '.$DATABASE;
		   if(mysql_query($sql)){
		      $isCreated = 2;
		   }
		   else{
		        throw new Exception('Failed to create a new database.
					 Maybe you are on a shared hosting account environment. Try to change
					 your database name to be the same with your username. If still failed you need
					 to create the database within your hosting environment.');
		   }
		}
		else{ //for shared hosting
		  $isCreated = 2;	
		}
	  }
	  
	  return $isCreated;
	
	}
	
	
	
	public function isDbExist($DATABASE,$conn,$tablesArray)
	{
		$isExist = false;
		
		$db_selected = mysql_select_db($DATABASE, $conn);
		
		if ($db_selected) {
		   if($this->areTablesExist($tablesArray)){//make sure the 5 chat app tables exist
		         $isExist = true;
		   }
		}
		
		return $isExist; 
	}
	
	
	
	public function areTablesExist($tablesArray)
	{
	   foreach($tablesArray as $table)
	   {
		$numrow = mysql_num_rows( mysql_query("SHOW TABLES LIKE '".$table."'")); 
		if($numrow == 0 || $numrow < 1){
			return false;
		        exit;
		}
	   }
	   return true;
	}

}

?>