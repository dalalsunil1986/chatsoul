<?php

#This page should be called by a cron job every day.
#The purpose is to find a chatroom older than the configured days.
#Check if there is no activity by checking associated messages and users.
#Messages should be older than 1 day and users last logged should be older than 1 day.
#Just then we delete this chatroom

include_once (substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'cronjobs')).'Classes/chatroomscleaner.php');

$cleaner = new chatroomscleaner();
$cleaner->cleanup();


?>