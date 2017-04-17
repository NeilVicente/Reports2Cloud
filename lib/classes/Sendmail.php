<?php


require_once( __DIR__ . '/Mailer/PHPMailer.php' );
require_once( __DIR__ . '/Mailer/SMTP.php' );

Class Sendmail
{

    /*
    * Mail configuration
    */
    private $mail;
    private $IsSMTP = true;
    private $SMTPAuth = true;
    private $SMTPSecure = "ssl";
    private $host = "smtp.gmail.com";
    private $port = 587;
    
    /*
    * Amazon Webservice credentials
    */
    protected $username = "kenma9123@gmail.com";
    protected $password = "kenMA__A123";
    
    /*
    * Mail content
    */
    protected $to;
    protected $address;
    protected $from_name  = "Reports2Cloud - JotForm App";
    protected $from_email = "kenma9123@gmail.com";
    protected $website_url;
    
	/*
    * Class constructor
    * @param $to: email where to send to
    * @param $code: Code to send to the recently registered user
    */
    function __construct( $to = null )
    {
        try 
        {
            $this->mail = new PHPMailer();
            if( $this->mail ){
                $this->set_config();
            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    /*
    * Set the configuration of the mailer
    */
    protected function set_config()
    {
        //use SMTP server
        $this->mail->isSMTP();
        
        #Host
        $this->mail->Host = $this->host;

        #SMTP authentication
        $this->mail->SMTPAuth = $this->SMTPAuth;
        
        $this->mail->SMTPSecure = $this->SMTPSecure;
        
        #SMTP Port
        $this->mail->Port = $this->port;
        
        #SMTP  Username
        $this->mail->Username = $this->username;
        
        #SMTP Password
        $this->mail->Password = $this->password;
        
        
        $this->set_from();
        $this->add_reply_to();
        
    }
    
    /**
    * Use to send a custom mail
    */
    public function send_custom_mail( $email, $subject, $bodytemplate )
    {
        $this->to = $email;
        $this->set_subject( $subject );
        $this->add_address();
        $this->set_html_body( $bodytemplate );
        
        return $this->send();
    }

    /**
    * Set whoever sends the message
    * @access protected
    */
    protected function set_from()
    {
        $this->mail->SetFrom( $this->from_email, $this->from_name );
    }
    
    /**
    * Set the recepient
    * @access protected
    */
    protected function add_reply_to()
    {
        $this->mail->addReplyTo( $this->from_email, $this->from_name );
    }
    
    /**
    * Set the message subject
    * @access protected
    */
    protected function set_subject( $subject )
    {
        $this->mail->Subject = $subject;
    }
    
    /**
    * Set to whom to send the message
    * @access protected
    */
    protected function add_address()
    {
        if ( is_array( $this->to ) )
        {
            foreach( $this->to as $email )
            {
                $this->mail->addAddress( $email );
            }
        }
        else
        {
            $this->mail->addAddress( $this->to );
        }
    }
    
    /**
    * Clean email addresses
    */
    protected function ClearAddresses()
    {
        $this->mail->ClearAddresses();
    }
    
    /**
    * Set the html body
    * @param $template - template of the whole message
    * @access protected
    */
    protected function set_html_body( $template )
    {
        $this->mail->msgHTML($template);
    }
    
    /**
    * Execute and send the message entirely
    * @access protected
    */
    protected function send()
    {
        $sent = false;
        if( $this->mail->send() )
        {
            echo 'send';
            $sent = true;
        }
        
        //clear email address to avoid sending in other emails.
        $this->ClearAddresses();
        
        return $sent;
    }

}
?>
