<?php
require 'Send_Mail.php';
$to = "kenma9123@gmail.com";
$subject = "Test Mail Subject";
$body = "Hi<br/>Test Mail<br/>Gmail"; // HTML  tags
Send_Mail($to,$subject,$body);
?>