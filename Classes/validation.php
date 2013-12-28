<?

class validation{
    
   
    //validates an input and throw an error if there is
    //@param $rules -> array of rules to be applied
    public function validate($rules,$val,$field,$confirmVal=null){
        
        $rules_array = array();
        $error = null;
        
        if(!is_array($rules)){ //take care if rule is just a string
             $rules_array = array($rules);
        }
        else{
            $rules_array = $rules;
        }
        
        foreach($rules_array as $rule){
            
            if(isset($confirmVal)){
              $error = $this->errorMessage($rule,$val,$field,$confirmVal);  
            }
            else{
              $error = $this->errorMessage($rule,$val,$field);   
            }
            
            if(isset($error)){
                throw new Exception($error);
               } 
        }
        
        return $val;
    }
    
    
    private function errorMessage($rule,$val,$field,$confirmVal=null){
        
        switch($rule){
                
                case 'required':
                        
                        if(gettype($val) == 'integer'){
                            $val = (string)$val;
                        }
                        
                        if(strlen(trim($val)) == 0){
                             return '"<b>'.$field.'</b>" is required.';  
                          }
                   
                        break;
                        
                case 'numeric':
                        
                        if(!is_numeric($val)){
                           return '"<b>'.$field.'</b>" should be an integer.';
                        }
                        
                        break;
                       
                case 'email':
                    
                        if(!filter_var($val, FILTER_VALIDATE_EMAIL)){
                           return '"<b>'.$field.'</b>" should be a valid email.';
                        }
                        
                        break;
                
                case 'url':
                    
                        if(!filter_var($val,FILTER_VALIDATE_URL)){
                           return '"<b>'.$field.'</b>" should be a valid domain name.';
                        }
                        
                        break;
                
                case 'password':
                        
                        if(!isset($confirmVal)){
                          return '"<b>'.$field.'</b>" should be confirmed.';
                        }
                        elseif($val != $confirmVal){
                           return '"<b>'.$field.'</b>" and <b>Confirm Password</b> should be the same.';
                        }
                        
                        break;
                    
            }
           
           
    }
   
    
}



?>