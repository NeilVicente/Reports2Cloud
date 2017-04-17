<?php

Class Email
{

    public static function split($emails)
    {
        # Emails will be collected in this array.
        $mails = array();

        if(is_array($emails)){
            foreach ($emails as $email){
                $mails = array_merge( $mails, self::split($email) );
            }
        }else{
            $tokens = preg_split("/\;|\,|\s+|\n/", $emails);
            foreach($tokens as $t){
                if(self::check($t)){
                    array_push($mails, $t);
                }
            }
        }

        return $mails;
    }

    /**
     * Validates the email
     * @param object $email
     * @return
     */
    public static function check($email, $checkDB = false)
    {

        // 22 TLDs as of Sep 2009. From http://en.wikipedia.org/wiki/Tld
        // eregi DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
        return preg_match('/^[a-z0-9_\-\+]+(\.[_a-z0-9\-\+]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|asia|arpa|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|nato|net|org|pro|tel|travel)$/i', $email);
    }

    public static function send($options)
    {

        $options = array_merge(array(
            "is_html"      => true,
            "from"         => false,
            "cc"           => false,
            "customHeader" => false,
            "replyTo"      => "none"
        ), $options);

        $to           = $options['to'];
        $subject      = $options['subject'];
        $contents     = $options['contents'];
        $is_html      = $options['is_html'];
        $frm          = $options['from'];
        $cc           = $options['cc'];
        $customHeader = $options['customHeader'];
        $replyTo      = $options['replyTo'];


        $from = ($frm)? $frm : "Jotform"."<noreply@jotform.com>";
        $from_header = "From: $from\r\nReturn-Path: $from\r\n";
        if($cc){
            $from_header .= "Cc: ".join(", ", self::split($cc))."\r\n";
        }

        # Reply-to Address
        if (($replyTo != "none") && self::check($replyTo) ){
            $from_header .= "Reply-To: ". $replyTo ."\r\n";
        }

        if($customHeader){
            $from_header .= $customHeader."\r\n";
        }

        if($is_html) $from_header .="Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $to = str_replace(" ", "", $to);
        $to = preg_replace("/\n\r|\n|\r\n|\r/", "\n", $to);

        $subject = preg_replace('/([^a-z ])/ie', 'sprintf("=%02x",ord(StripSlashes("\\1")))', $subject);
        $subject = str_replace(' ', '_', $subject);
        $subject = "=?UTF-8?Q?$subject?=";

        $mails = self::split($to);
        foreach($mails as $to){
            $o = @mail($to, $subject, stripslashes($contents), $from_header); // removed block message
        }
        /*file_put_contents("/tmp/oldmail.log", "sent\n", FILE_APPEND);*/
    }
}

?>