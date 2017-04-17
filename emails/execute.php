<?php

require_once(__DIR__."/../lib/init.php");

try
{
	NotificationEmail::sendEmailToUsers();
}
catch(Exception $e)
{
	echo $e->getMessage();
}

die;exit;

?>