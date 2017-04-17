<?php
// error_reporting(E_ALL);

//global file that will automatically call the required class
require_once( __DIR__ . '/lib/init.php' );

try
{
    $request = $_POST ? $_POST : $_GET;

    $ajax = new Ajax($request);
    $ajax->_setHeaders();

    $ajax->execute();
}
catch (Exception $e)
{
    $ajax->error($e->getMessage());
}
?>
