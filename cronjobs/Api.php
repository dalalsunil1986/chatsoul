<?php

     $var = doCurl('http://mytodaysgospel.com/site/api');
     echo $var;
     
     function doCurl($url){
            
            $clean_url = str_replace(" ","%20",$url);
            // Set up cURL
            $ch = curl_init();
            // Set the URL
            curl_setopt($ch, CURLOPT_URL, $clean_url);
            // don't verify SSL certificate
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // Return the contents of the response as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // Follow redirects
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            
            return $response; 
    } 


    

?>