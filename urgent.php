<?php

//global file that will automatically call the required class
require_once( __DIR__ . '/lib/init.php' );

$body = '<p>Hi guys I hope you are well.<br>We have made some changes on the site lately, we have moved to a new domain.<br>The old <a href="http://reports2dropbox.jotform.io" title="reports2Dropbox" target="_blank">http://reports2dropbox.jotform.io</a> is no longer working.<br><br>So to avoid confusions, please visit our new place <a href="http://reports2cloud.jotform.io" title="reports2Dropbox" target="_blank">http://reports2cloud.jotform.io</a>.<br>We are planning to make it even better and maybe to support other cloud storage out there.<br><br>Thank you so much,<br>Kenneth</p>';

$mail = new Sendmail();
$mail->send_custom_mail(array(
	'kenma9123@gmail.com',
	'kenneth@jotform.com'
),'Reports2Dropbox users: We moved to a new domain', $body);

?>