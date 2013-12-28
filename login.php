<?php
/*
 * The /users/ table should be something like this:
 *                 user_id                INTEGER                        NOT NULL PRIMARY KEY AUTO_INCREMEMNT,
 *                username        VARCHAR(20)                NOT NULL UNIQUE KEY,
 *                 class                VARCHAR(10)                NOT NULL,
 *                logged_in        BIT                                NOT NULL DEFAULT 0,
 *                last_log        DATETIME                NULL
 * The /excluded/ table should be something like this:
 *                 exclude_id        INTEGER                        NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *                bad_word        VARCHAR(20)                NOT NULL UNIQUE KEY
 */

session_start();

require_once('db.php');
require_once('library/HTMLPurifier.auto.php');

function quote_smart($value)
{
//Strip slashes
if (get_magic_quotes_gpc()) {
$value = stripslashes($value);
}
//Quote if not integer
if (!is_numeric($value)) {
$value = "'" . mysql_real_escape_string($value) . "'";
}
return ($value);
}

function alphanumericAndSpace( $string )
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    }

function strip_it( $text )
{
    $urlbrackets    = '\[\]\(\)';
    $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
    $urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
    $urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;
 
    $specialquotes  = '\'"\*<>';
 
    $fullstop       = '\x{002E}\x{FE52}\x{FF0E}';
    $comma          = '\x{002C}\x{FE50}\x{FF0C}';
    $arabsep        = '\x{066B}\x{066C}';
    $numseparators  = $fullstop . $comma . $arabsep;
 
    $numbersign     = '\x{0023}\x{FE5F}\x{FF03}';
    $percent        = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
    $prime          = '\x{2032}\x{2033}\x{2034}\x{2057}';
    $nummodifiers   = $numbersign . $percent . $prime;
 
    return preg_replace(
        array(
        // Remove separator, control, formatting, surrogate,
        // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
        // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
                $numseparators . $urlall . $nummodifiers . '])/u',
        // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
        // Remove special quotes, dashes, connectors, number
        // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
                '\p{Pd}\p{Pc}]+((?= )|$)/u',
        // Remove special quotes, connectors, and URL characters
        // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
        // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
        // Remove consecutive spaces
            '/ +/',
        ),
        ' ',
        $text );
}


function CheckIfVulgar($name){
        $sql = sprintf("SELECT bad_word FROM excluded WHERE bad_word = %s;", quote_smart($name));
        
                        if ($result = @mysql_query($sql)) {
                                if (@mysql_num_rows($result))
                                        
                                        return true;
                                        
                                else {
                                        
                                        return false;
                                        
                                        }
        
        }
        else
        {
                return true;
                
                }
        }
        
        
                function CreateUser($name,$time){
                        
                        $classname = "class";
                        
                        #$dt = date('Y-m-d H:i:s');
                        $sql = sprintf("Insert INTO users  VALUES ('%s', %s, '%s', '%s', '%s')", null, quote_smart($name), $classname, 1,date('Y-m-d H:i:s') );
                        
                        if (@mysql_query($sql))
                        {  
                                $sql = sprintf("SELECT username, logged_in, last_logged FROM users WHERE username = %s", quote_smart($name));
                                if ($result = @mysql_query($sql)) {
                                        if ($row = @mysql_fetch_assoc($result)) {
                                                /* declare the session variable */
                                                                        $_SESSION['username'] = $row['username'];
                                                                        session_regenerate_id();
                                                                        print('1');
                                                }
                                                else
                                                {
                                                        #something bad happened to the row
                                                        print ('4');
                                                        
                                                        }
                                        
                                        
                                        }
                                        
                                else
                                {
                                        #something bad happened to the result
                                        print ('4');
                                        
                                        }
                                        #free up the result
                                @mysql_free_result($result);
                         }
                         else
                         {
                         
                                #something wrong happened        
                                 print ('4');
                                 
                                 }
                                 }
        
        
        
        
        
        function QueryDb($name, $time){
                /* no vulgarity, next check to see if user is already logged in */
                         #$dt = date('Y-m-d H:i:s');
                                        $sql = sprintf("SELECT username, logged_in, last_logged FROM users WHERE username = %s", quote_smart($name));
                                        
                                        if ($result = @mysql_query($sql)) {
                                                
                                                if ($row = @mysql_fetch_assoc($result)) {
                                                        /* should probably check to see if time is legit */
                                                        $last_log = strtotime($row['last_logged']); 
                                                        $now = time();
                                                        $diff = $now - $last_log;
                                                        if (!$row['logged_in'] || ($row['logged_in'] && $diff > (3600 * 24))) {
                                                                /* 
                                                                 * log in the user if they are not logged in or they have 
                                                                 * been logged in for more than 1 day (3600 secs (1 hour) * 
                                                                 * 24 hours) 
                                                                 */
                                                                $sql = sprintf("UPDATE users SET logged_in = 1, last_logged = '%s' WHERE username = %s", date('Y-m-d H:i:s'), quote_smart($name));
                                                                if (!@mysql_query($sql))
                                                                        /* there was a problem logging in the user */
                                                                        print('4');
                                                                else {
                                                                        /* declare the session variable */
                                                                        $_SESSION['username'] = $row['username'];
                                                                        session_regenerate_id();
                                                                        print('1');
                                                                }
                                                        } else
                                                                /* user is already logged in */
                                                                print('2');
                                                } else
                                                        /* user does not exist, so would need to prompt to add or something other than 4 */
                                                        CreateUser($name, $time);
                                        } else {
                                                /* problem with the SQL statement */
                                                CreateUser($name, $time);
                                }
                                
                                @mysql_free_result($result);
                
                }
                
                
                

$CleanName = trim(alphanumericAndSpace($_POST['username']));

if (isset($_POST['username']) && isset($_POST['lasttime']) && strlen($CleanName) > 0 && strlen($CleanName) <= 30 ){
        
        
        
        $MyTime = $_POST['lasttime'];
        
        if($conn = @mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD))
        {
                        if (@mysql_select_db($DATABASE, $conn)) {
                                
                                if ( CheckIfVulgar($CleanName) == false )
                                {
                                        QueryDb($CleanName, $MyTime);
                                        
                                        }
                                else
                                {
                                        #vulgarity in the words
                                        
                                        print('3');
                                        
                                        }
                                
                                }
                        else
                        {
                                #problem switching the database
                                print('4');
                      @mysql_close($conn);
                                
                                }
                
                }
                else
                {
                        #problem connecting to the database
                        print('4');
                        
                        }
        
        
        
        }
        else 
        {
                #problem with the posted username and last time not being set
                
                print('4');
                
                }
                
                


?>