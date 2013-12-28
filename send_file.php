<?php
session_start( );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
                <title>Ajax File Transfer</title>
                <meta http-equiv="content-type" content="text/html;charset=utf-8" />
                <script type="text/javascript" src="http://prototypejs.org/assets/2009/8/31/prototype.js"> </script>
                <script type="text/javascript" src="Script/sendfile.js"></script>
                
        </head>
        <body>
                <div id="contentWrapper" style="width:400px;margin-left:auto;margin-right:auto;padding-top:50px;text-align:center;font-size:16px;font-family:Arial">
                <div id="divUploadMessage" style="display:none;"></div>
                 <div id="divUploadProgress" style="display:none">
                    <p id="notifier"></p>
                    <div>
                        <table border="0" cellpadding="0" cellspacing="2" style="width:100%">
                            <tbody>
                                <tr>
                                    <td id="tdProgress1">&nbsp; &nbsp;</td>
                                    <td id="tdProgress2">&nbsp; &nbsp;</td>
                                    <td id="tdProgress3">&nbsp; &nbsp;</td>
                                    <td id="tdProgress4">&nbsp; &nbsp;</td>
                                    <td id="tdProgress5">&nbsp; &nbsp;</td>
                                    <td id="tdProgress6">&nbsp; &nbsp;</td>
                                    <td id="tdProgress7">&nbsp; &nbsp;</td>
                                    <td id="tdProgress8">&nbsp; &nbsp;</td>
                                    <td id="tdProgress9">&nbsp; &nbsp;</td>
                                    <td id="tdProgress10">&nbsp; &nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
<?php
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

if (isset($_POST['nptUsername']) && isset($_POST['nptRecvname']) && isset($_POST['nptLasttime']) && is_uploaded_file($_FILES['nptFile']['tmp_name']))
{
        if ($conn = @mysql_connect("localhost", "root", "")) 
        {
                if (@mysql_select_db("test", $conn)) 
                {
                        $sql = sprintf("SELECT username FROM users WHERE user_id = '%s'", $_POST['nptRecvname']);
                        $username = '';
                        
                        if ($result = @mysql_query($sql)) 
                        {
                                if ($row = @mysql_fetch_assoc($result))
                                        $username = $row['username'];
                                   @mysql_free_result($result);
                        }
                        
                        if ($username != '') 
                        {
                                
                                $fileName = $_FILES['nptFile']['name']; 
            $tmpName  = $_FILES['nptFile']['tmp_name']; 
            $fileSize = $_FILES['nptFile']['size']; 
            $fileType = $_FILES['nptFile']['type']; 
 
         if(!get_magic_quotes_gpc())
              {
              $fileName = addslashes($fileName);
              }
         
         # read the uploaded file to store on the database  
         $content=addslashes(file_get_contents($tmpName ));
         
         $sql = sprintf("INSERT INTO file (filename, file_data, user_id, file_dte, from_user, file_type, file_size, isdownloading) VALUES ('%s', '%s', '%s', '%s', %s, '%s', '%s', '%s')", $fileName,  $content, $_POST['nptRecvname'], $_POST['nptLasttime'], quote_smart(trim($_POST['nptUsername'])), $fileType, $fileSize, 0);
                                
                                if (@mysql_query($sql))
                                {
                                $sql = sprintf("SELECT file_id FROM file WHERE filename = '%s' AND user_id = '%s' AND file_dte = '%s' AND from_user = %s", $fileName, $_POST['nptRecvname'], $_POST['nptLasttime'], quote_smart(trim($_POST['nptUsername'])));
                                $fileid = -1;
                                if ($result = @mysql_query($sql))
                                {
                                        if ($row = @mysql_fetch_assoc($result))
                                        $fileid = $row['file_id'];
                                        @mysql_free_result($result);
                                }
                                        if ($fileid != -1)
                                        {
                                                print("<script type='text/javascript'>init('".$username."',".$fileid.")</script>");
                                        }
                                        else
                                        {
                                                print("<script type='text/javascript'>starterr()</script>");        
                                        }
                                }
                                else
                                #configure maximum allowed packet for large file
                                #receiving user has a pending file transfer request
                                #print(mysql_error());
                                print("<script type='text/javascript'>busyuser()</script>");
                        } 
                           else
                           #looks like the receiver log outs
                                print("<script type='text/javascript'>nouser()</script>");
                } 
                else
                print("<script type='text/javascript'>starterr()</script>");
                /* Close the server connection */
                @mysql_close($conn);
        } 
        else
        print("<script type='text/javascript'>starterr()</script>");
}
else
        print("<script type='text/javascript'>starterr()</script>");
?>
                </div>
        </body>
</html>