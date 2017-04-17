<?php

require_once(__DIR__."/init.php");

try
{
	Cron::startQueue();
}
catch(Exception $e)
{
	echo $e->getMessage();
}

die;exit;

?>