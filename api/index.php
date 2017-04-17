<?php

require_once(__DIR__. "/../lib/init.php");

$dropbox = new DropboxHandler();
if(isset($_GET['oauth_token']) && isset($_GET['uid'])){
    $dropbox->completeAuthorization($_GET);
    exit;
} else {
	$dropbox->authorize();
}
?>