 <?php

class SocialMediaFeed{
   
    private $tags;//array->an array of tags to search
    private $count;//integer->number of items to be returned from the query   
    
    public function __construct($tags, $count=200)
    {
       $this->tags = $tags;
       $this->count = $count; 
    }  
    
    
    private function doCurl($url){
            
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
    
    
    private function getTags(){
        $tags = array();
        if(!is_array($this->tags)){
            $tags[]=$this->tags;
        }else{
            $tags = $this->tags;
        }
        
        return $tags;
    }
    
    
    private function instagramUrl($tag){
      $tag = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $tag);//strip out special characters since instagram does not allow them
      $url = "https://api.instagram.com/v1/tags/$tag/media/recent?access_token=175159176.f59def8.431c3ffc12e64e7bbd814cf7db8f58a6&count=".$this->count; 
      return $url; 
    }
    
    private function twitterUrl($tag){
        $url = "http://search.twitter.com/search.json?q=$tag&include_entities=1&count=".$this->count;
        return $url;
    }
    
    
    
    public function queryInstagram(){
        $tags = $this->getTags();
        $responses=array();//store all responses for each tag
        foreach($tags as $tag){
           $result = $this->doCurl($this->instagramUrl($tag));
           if(!empty($result))
             $responses[] = $result; 
        }
        return $responses;
    }
    
    
    public function queryTwitter(){
        $tags = $this->getTags();
        $responses = array();
        foreach($tags as $tag){
            $result = $this->doCurl($this->twitterUrl($tag));
            if(!empty($result))
             $responses[] = $result; 
        }
        return $responses;
    }
   
    public function mediaTypeId($type){
      if($type == 'twitter'){
        return 1;
      }
      else
       return 2;
    }
    
    public function mediaType($type){
        if($type=='photo' || $type=='image'){
            return 1;
        }
        else
          return 2;
    }
    
    
    
     //combine instagram and twitter queries
    public function getSocialResult(){
        
        $return_array = array();
        $ordered_array = array();
        
        $twitter = $this->queryTwitter();
        
        $twitter_media = 'twitter';
      
        $instagram = $this->queryInstagram();
       
        $instagram_media = 'instagram';
       
        foreach($twitter as $tweet){ //loop through each twitter requested tags
         
            $data = json_decode($tweet,true);
            
            if(!isset($data['results']))//if no data then just continue to next tag
              continue;
            
            foreach($data['results'] as $datum){
              
              $entities = $datum['entities'];
              $return_array['date_created'][] = strtotime($datum['created_at']);
              $return_array['unique_identifier'][] = $datum['id_str'];
              $return_array['username'][]=$datum['from_user'];
              $return_array['profile_image_url'][]=$datum['profile_image_url'];
              $return_array['media_id'][]=$this->mediaTypeId($twitter_media);
              $return_array['text'][] = ( count($entities['hashtags']) > 0 ? $entities['hashtags'][0]['text']:$datum['text'] );
              
              if(!isset($entities['media'])){
                  $return_array['display_url'][] = ( isset($entities['urls'][0]) ? $entities['urls'][0]['display_url']:null );
                  $return_array['media_url'][] = null;
                  $return_array['media_type_id'][] = null; 
              }
              else{
                $return_array['display_url'][]=$entities['media'][0]['url'];
                $return_array['media_url'][] = $entities['media'][0]['media_url'];
                $return_array['media_type_id'][]=$this->mediaType($entities['media'][0]['type']);
              }
              
            }
        }
        
        
        foreach($instagram as $data){ //loop through each instagram requested tags
            
            $data = json_decode($data,true);
            if(!isset($data['data']))
              continue;
              
            foreach($data['data'] as $datum){
              $return_array['media_id'][] = $this->mediaTypeId($instagram_media);
              $return_array['unique_identifier'][] = $datum['caption']['id'];
              $return_array['media_url'][] = $datum['images']['standard_resolution']['url'];
              $return_array['username'][] = $datum['caption']['from']['username'];
              $return_array['profile_image_url'][] = $datum['caption']['from']['profile_picture'];
              $return_array['date_created'][] = $datum['created_time'];
              $return_array['text'][]=$datum['caption']['text'];
              $return_array['media_type_id'][]=$this->mediaType($datum['type']);
              $return_array['display_url'][]= $datum['link'];
            }
            
        }
       
         print_r($return_array);die();
         
        $date_array = $return_array['date_created']; 
        arsort($date_array);//sort the date created from recent to older post
        
        foreach($date_array as $k=>$v){//loop through each date array and reassign values
           $ordered_array['date_created'][] = $return_array['date_created'][$k];
           $ordered_array['media_id'][] = $return_array['media_id'][$k];
           $ordered_array['username'][]= $return_array['username'][$k];
           $ordered_array['profile_image_url'][]= $return_array['profile_image_url'][$k];
           $ordered_array['text'][]= $return_array['text'][$k];
           $ordered_array['display_url'][]= $return_array['display_url'][$k];
        }
        
        return $ordered_array;
    }
    
    
    //create the social feed html
    public function createSocialFeed(){
        
        $results = $this->getSocialResult();
         $str = '<div id="activityFeed">
                <ul>';
            
            for($i = 0; $i <= count($results['media_id'])-1; $i++){
              
              $str .= '<li>';
              $current_time = strtotime(date("Y-m-d H:i:s"));
              $time_posted = $results['date_created'][$i];
              $time_diff = $current_time - $time_posted;
              $timeposted = $this->computeDate($time_diff);
              
              $username = $results['username'][$i];
              if($results['media_id'][$i] == 1)
                 $username = '<a href="http://twitter.com/'.$results['username'][$i].'">'.$results['username'][$i].'</a>';
              
              $display_url = '';   
              if(isset($results['display_url'][$i]))
                $display_url = $results['display_url'][$i];  
            
              $str .= '<img src="'.$results['profile_image_url'][$i].'" title="'.$results['text'][$i].'" />'
              .$results['text'][$i].'<br /><a href="'.$this->cleanUrl($display_url).'" target="_blank">'.$display_url.'</a><br />Posted: '.$timeposted.
              '<br /><br /> Posted by: '.$username;  
             
              $str .= '</li>';
              
            }
            
        $str .= '</ul>
                </div>';
                
        return $str;
        
    }
    
    public function isValidURL($url)
    {
      return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }
    
    public function cleanUrl($url){
        if(!$this->isValidURL($url))
        {
           $url = 'http://'.$url;
        }
        return $url;
    }
    
   
    /**
    *compute a date
    *@param $arg(bigint)- date in unix format(milliseconds)
    */
    private function computeDate($arg)
    {
        $arg = $arg * 1000;
        $time = 0;
        $s = 's';
        
        if($arg < 0):
            return "less than a minute ago";
        elseif($arg >= 0 && $arg <= 60000):
            $time = round($arg/1000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " second".$s." ago";
        elseif ($arg>=60000 && $arg < 3600000):
            $time = round($arg/60000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " minute".$s." ago";
        elseif ($arg>=3600000 && $arg < 86400000):
            $time = round($arg/3600000,0);
            if ($time == 1)
            {$s = '';}
            return (string)$time . " hour".$s." ago";
        else:
            $time = round($arg/86400000,0);
            if ($time == 1)
            {$s = '';}
            if($time > 30){
              $months = round($time/30,0);
              if($months == 1)
              {$s = '';}
              return (string)$months . " month".$s." ago";  
            }
            else
             return (string)$time . " day".$s." ago";
        endif;
        
    } 
}

?>