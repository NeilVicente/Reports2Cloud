<?php

Class DropboxHandler
{
    protected $appKey;
    protected $appSecret;

    protected $redirect_uri;
    protected $oauth_req_token_url;
    protected $oauth_authorize_url;

    const DROPBOX_BASE_URL = 'https://www.dropbox.com/';
    const DROPBOX_URL = 'https://www.dropbox.com/1/';
    const DROPBOX_API_URL = 'https://api.dropbox.com/1/';
    const DROPBOX_CONTENT_URL = 'https://api-content.dropbox.com/1/';

    private $_headers = array();

    function __construct()
    {
        //set redirect uri
        $this->redirect_uri = sprintf("%sapi/", HTTP_URL);

        //set request token url
        $this->oauth_req_token_url = self::DROPBOX_API_URL . "oauth/request_token";

        //set authorize url
        $this->oauth_authorize_url = self::DROPBOX_URL . "oauth/authorize";

        //set request access_token url
        $this->oauth_access_token_url =  self::DROPBOX_API_URL .  "oauth/access_token";

        $this->appKey = (MODE=='dev') ? '4odd8qtliohbfbw' : 'ln0q2z7j25k755o';
        $this->appSecret = (MODE=='dev') ? '1gbtsf6cdp2yama' : '8jlt3wgjjohb9nd';

        //set root
        $this->root = 'dropbox';
    }

    /**
     * Generates an authorization URL for user
     * where in, user will need to authenticate the application to be able to use it later
     * @access public
     * @return void
     */
    public function authorize()
    {
        //Dropbox uses plaintext OAuth 1.0; make the header for this request
        $headers = sprintf('Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="%s", oauth_signature="%s&"', $this->appKey, $this->appSecret);
        $response = Curl::post($this->oauth_req_token_url, array(
            'headers' => array($headers)
        ));
        $content = $response['content'];

        if ( $response['http_code'] == 200 AND User::isLoggedIn() )
        {
            //  parse the returned data which has the format:
            // "access_token=<access-token>&access_token_secret=<access-token-secret>"
            parse_str($content, $parsed_request);

            //check for any errors
            $json_access = json_decode($content);
            if(@$json_access->error) {
                throw new Exception('FATAL ERROR: '.$json_access->error);
            }

            //set these variables in a $_SESSION variable
            $this->setTokens(array(
                'access_token' => $parsed_request['oauth_token'],
                'access_token_secret' => $parsed_request['oauth_token_secret']
            ));

            //get the request URL; this is where you send the user to authorize your request. Be sure to set the CALLBACK_URL before doing this.
            $authorize_url = sprintf("%s?oauth_token=%s&oauth_callback=%s", $this->oauth_authorize_url, $this->access_token, $this->redirect_uri);
            if(!@header("Location: $authorize_url"))
            {
                echo "<script>location.href = '$authorize_url'; </script>";
                // ob_flush();
                // flush();
            }
            exit;
        }
        else
        {
            echo "authorize()::Something went wrong, please close the window and reconnect to Dropbox";
            // print_r($response);
        }
    }

    /**
     * Complete the authorization of a Dropbox user
     * It will generate tokens to be able to use on accessing dropbox data
     * @param array $dropbo - The array response from the redirect URI
     * @access public
     * @return void
     */
    public function completeAuthorization($dropbox)
    {
        //set the header from the previous requesting token
        $access_token = (Session::read('access_token')) ? Session::read('access_token') : $dropbox['access_token'];
        $access_token_secret = Session::read('access_token_secret');
        $header = $this->getHeaderRequest($access_token, $access_token_secret);

        //get new access token
        $response = Curl::post($this->oauth_access_token_url, array(
            'headers' => array($header)
        ));

        $content = $response['content'];

        if ( $response['http_code'] == 200 AND User::isLoggedIn() )
        {
            //  parse the returned data which has the format:
            // "access_token=<access-token>&access_token_secret=<access-token-secret>&uid=<userid>"
            parse_str($content, $parsed_request);

            //check for errors
            $json_access = json_decode($content);
            if(@$json_access->error)
            {
                throw new Exception('FATAL ERROR: '.$json_access->error);
            }

            //replace old session oauth values with the new one
            $this->setTokens(array(
                'access_token' => $parsed_request['oauth_token'],
                'access_token_secret' => $parsed_request['oauth_token_secret']
            ));

            //request dropbox user info
            $account = $this->getDropboxAccountInfo();

            //remove uneccessary attributes
            unset($account['referral_link']);

            //attach account url
            $account['account_url'] = self::DROPBOX_BASE_URL . "account/settings";
            $user = json_encode($account);

            //include the file needed, that will be loaded in the popup window
            $access_token = $this->access_token;
            $access_token_secret = $this->access_token_secret;

            //merge the dropbox tokens and userinfo to user data in session
            //avoid being endetected on first login
            User::setDropboxData(json_encode(array(
                'access_token' => $access_token,
                'access_token_secret' => $access_token_secret,
                'user' => $account
            )));

            echo "<script>window.opener.Backbone.View.prototype.global.pickerView.dropbox_auth_finish('".$user."');window.close();</script>";

            // include ROOT."/api/content.php";
            exit;
        }
        else
        {
            echo "completeAuthorization()::Something went wrong, please close the window and reconnect to Dropbox";
            // print_r($response);
        }
    }

    /**
     * Remove the tokens from the public user_data
     */
    public static function removeTokensFromPublic($user_data)
    {
        if ( isset($user_data['dropbox_data']) )
        {
            $decoded = false;
            if ( Tools::isJson($user_data['dropbox_data']) )
            {
                $user_data['dropbox_data'] = json_decode($user_data['dropbox_data'], true);
                $decoded = true;
            }

            if (
                isset($user_data['dropbox_data']['access_token']) AND
                isset($user_data['dropbox_data']['access_token_secret'])
            )
            {
                unset($user_data['dropbox_data']['access_token']);
                unset($user_data['dropbox_data']['access_token_secret']);
            }

            if ( $decoded )
            {
                $user_data['dropbox_data'] = json_encode($user_data['dropbox_data']);
            }
        }

        return $user_data;
    }

    /**
     * Get the account info of a dropbox user
     */
    public function getDropboxAccountInfo()
    {
        $headers = $this->getHeaderRequest($this->access_token, $this->access_token_secret);
        $result = Curl::get( self::DROPBOX_API_URL . "account/info", array(
            'headers' => array($headers)
        ));
        return json_decode($result['content'], true);
    }

    /**
     * Retrieves file and folder metadata
     * @param string $form - the folder path where to get hte metadata
     * @param boolean $list - include all the contents of a folder
     * @param string $locale - The upload language
     * @access public
     * @return The metadata for the file or folder at the given $path
     */
    public function getMetaData($path = '/home', $list = true, $locale = 'en')
    {
        $url = sprintf("%smetadata/%s/%s", self::DROPBOX_API_URL, $this->root,  $this->encodePath($path));
    
        //concat to url with params
        $url .= '?' . http_build_query(array(
            'list' => $list,
            'locale' => $locale
        ), '', '&');

        //make the call
        $header = $this->getHeaderRequest($this->access_token, $this->access_token_secret);

        //build request param
        $request_param = array(
            'headers' => array($header)
        );
        $result = Curl::get( $url, $request_param );

        return $result;
    }

    /**
     * Set the dropbox tokens to be able to access it globally
     * @param array $tokens - An array containing the tokens
     * @access public
     * @return void
     */
    public function setTokens($tokens = array())
    {
        //replace old session oauth values with the new one
        Session::save('access_token_secret', $tokens['access_token_secret']);
        Session::save('access_token', $tokens['access_token']);

        //retrieve the access_token
        $this->access_token = $tokens['access_token'];
        $this->access_token_secret = $tokens['access_token_secret'];
    }

    /**
     * Uploads a physical file from disk
     * Dropbox impose a 150MB limit to files uploaded via the API. If the file
     * exceeds this limit, an Exception will be thrown
     * @param string $rawFile - the whole binary data of the file
     * @param string $toFilepath - The destination filename of the uploaded file
     * @param boolean $overwrite - Should the file be overwritten? (Default: true)
     * @param string $locale - The upload language
     * @access public
     * @return object stdClass
     */
    public function putFile($rawFile, $toFilepath, $overwrite = true, $locale = 'en')
    {
        $filesize = strlen($rawFile);
        if ($filesize <= 157286400)
        {
            $call_url = self::DROPBOX_CONTENT_URL . 'files_put/' . $this->root . '/' . $this->encodePath($toFilepath);

            //concat to url with params
            $call_url .= '?' . http_build_query(array(
                'overwrite' => $overwrite,
                'locale' => $locale
            ), '', '&');

            //get the file
            $filehandle = fopen('php://temp/maxmemory:256000', 'w');
            if (!$filehandle) throw new Exception('Could not open temp memory data');

            //write raw file to stream
            fwrite($filehandle, $rawFile);
            fseek($filehandle, 0);

            //make the call
            $header = $this->getHeaderRequest($this->access_token, $this->access_token_secret);

            //build request param
            $request_param = array(
                'params' => array(
                    'file' => $filehandle, 
                    'fileSize' => $filesize
                ),
                'headers' => array($header)
            );
            $result = Curl::put( $call_url, $request_param );

            return $result;
        }
        // Throw an Exception if the file exceeds upload limit
        throw new Exception('File exceeds 150MB upload limit');
    }

    /** 
     * Generate the authorization header for each request
     * for a user to have an access to their dropbox data
     * @param string $access_token - The user access token provided by the application
     * @param string $access_token_secret - The user access token secret provided by the application
     * @access public
     * @return the header sting
     */
    public function getHeaderRequest($access_token, $access_token_secret)
    {
        return sprintf('Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="%s", oauth_token="%s", oauth_signature="%s&%s"', $this->appKey, $access_token, $this->appSecret, $access_token_secret);
    }

    /**
     * Encode the path, then replace encoded slashes
     * with literal forward slash characters
     * @param string $path The path to encode
     * @return string
     */
    private function encodePath($path)
    {
        //Trim the path of forward slashes and replace consecutive forward slashes with a single slash
        $path = preg_replace('#/+#', '/', trim($path, '/'));

        //url encode
        $path = str_replace('%2F', '/', rawurlencode($path));
        return $path;
    }
}
?>