<?php
function Send_Mail($to,$subject,$body)
{
require 'class.phpmailer.php';
$from = "kenma9123@gmail.com";
$mail = new PHPMailer();
$mail->IsSMTP(true); // SMTP
$mail->SMTPAuth   = true;  // SMTP authentication
$mail->Mailer = "smtp";
$mail->Host       = "ssl://smtp.gmail.com"; // Amazon SES server, note "tls://" protocol
$mail->Port       = 465;                    // set the SMTP port
$mail->Username   = "kenma9123@gmail.com";  // SES SMTP  username
$mail->Password   = "kenMA__A123";  // SES SMTP password
$mail->SetFrom($from, 'From Name');
$mail->AddReplyTo($from,'9lessons Labs');
$mail->Subject = $subject;
$mail->MsgHTML($body);
$address = $to;
$mail->AddAddress($address, $to);

if(!$mail->Send()){
 echo 'Message could not be sent.';
   echo 'Mailer Error: ' . $mail->ErrorInfo;
return false;
}
else {
	echo "Message sent";
return true;
}

}
?>