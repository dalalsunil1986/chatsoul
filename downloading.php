<?php
ob_start();
session_start( );
require_once('db.php');

/* Did we get everything we expected? */
if (isset($_POST['file_id'])){
	/* Can we connect to the MySQL server? */
	if ($conn = @mysql_connect($SQL_SERVER, $USERNAME, $PASSWORD)) {
		/* Can we connect to the correct database? */
		if (@mysql_select_db($DATABASE, $conn)) {
			   $theValue = 1;
				/* Set everything to NULL as the indicator */
				$sql = 'UPDATE file SET isdownloading = '.$theValue.' WHERE file_id = '.$_POST['file_id'].';';
				@mysql_query($sql);
			}
		}
		/* Close the server connection */
		@mysql_close($conn);
	}
?>