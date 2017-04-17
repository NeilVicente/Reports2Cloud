<?php

require_once(__DIR__."/../lib/init.php");

try
{
	NotificationEmail::unsubscribe($_GET);
}
catch(Exception $e)
{
	echo $e->getMessage();
}

die;exit;

?>