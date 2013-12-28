<?php
/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2010 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

if (!empty($_FILES)) {
	try
	{
	
	if (is_uploaded_file($_FILES['Filedata']['tmp_name']))
	{
	
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
	$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
	
	#now check the upload directory if a file of this name exist and uploaded 10 seconds ago, delete it if true
	if(file_exists($targetFile))
	{
		if(filemtime($targetFile) < (time() - 10))
		unlink($targetFile);
	}
	
	
	
	// $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
	// $fileTypes  = str_replace(';','|',$fileTypes);
	// $typesArray = split('\|',$fileTypes);
	// $fileParts  = pathinfo($_FILES['Filedata']['name']);
	
	// if (in_array($fileParts['extension'],$typesArray)) {
		// Uncomment the following line if you want to make the directory if it doesn't exist
		// mkdir(str_replace('//','/',$targetPath), 0755, true);
	
	if (move_uploaded_file($tempFile,$targetFile))
		{
		
		#$thefile = str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		$fileName = $_FILES['Filedata']['name'];
	   $fileSize = $_FILES['Filedata']['size']; 
      $fileType = $_FILES['Filedata']['type']; 
      $fileinfo = $targetFile.'||'.$fileName.'||'.$fileSize.'||'.$fileType;
      echo $fileinfo;
      
		}
		else
		echo 'error';
		
		
	}
	else 
	echo 'error';
	
	}
	catch (Exception $e)
	{
         echo ('error');
   }
		
	// } else {
	// 	echo 'Invalid file type.';
	// }
}
?>