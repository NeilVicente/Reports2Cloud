<?php

Class User
{
    protected $apikey;
    protected $_db;
    protected $_session;
	protected $tablename = "accounts";

    public static $removeSensitiveInfo = true;

    public static $sessionVariable = "_accounts_session";

	function __construct($username = null, $email = null, $apikey = null)
	{
        if ( is_null($username) ) throw new Exception("Username cannot be empty");
        if ( is_null($email) ) throw new Exception("Email cannot be empty");
        if ( is_null($apikey) ) throw new Exception("API key is missing");

        $this->jot_apikey = $apikey;
        $this->jot_username = $username;
        $this->jot_email = $email;

        //initialize session
        // $this->_session = new Session();

        //initialize mysql
        $this->_db = new MySQL(MYSQL_CONNECTION);
	}

    private function getAccountID()
    {
        return $this->_db->insertId;
    }

    public static function isLoggedIn()
    {
        // Session::destroy();
        // exit;die;
        return (Session::read(self::$sessionVariable)) ? true : false;
    }

    public static function getUserData($fromSession = true, $uid = null)
    {
        $user_data = array('empty');
        if ( Session::read(self::$sessionVariable) )
        {
            $user_data = Session::read(self::$sessionVariable);
        }

        if ( !$fromSession )
        {
            $user_id = (is_null($uid)) ? $user_data['id'] : $uid;
            $_db = new MySQL(MYSQL_CONNECTION);
            $stmt = $_db->query("SELECT `id`,`jotform_username`, `jotform_email`, `dropbox_data`, `first_integration` FROM `accounts` WHERE `id` = :id LIMIT 1", array('id' => $user_id));
            $user_data = $stmt->fetchAssoc();
        }

        //merge session id
        $user_data['user_session'] = Session::getSessionID();

        //remove senstive tokens of dropbox
        if ( self::$removeSensitiveInfo ) {
            $user_data = DropboxHandler::removeTokensFromPublic($user_data);
        }

        //decode the any json-strings
        if ( isset($user_data['folderLayout']) AND Tools::isJson($user_data['folderLayout']) ) {
            $user_data['folderLayout'] = json_decode($user_data['folderLayout']);
        }
        if ( isset($user_data['dropbox_data']) AND Tools::isJson($user_data['dropbox_data']) ) {
            $user_data['dropbox_data'] = json_decode($user_data['dropbox_data']);
        }

        return $user_data;
    }

    public static function countUsers()
    {
        $_db = new MySQL(MYSQL_CONNECTION);
        $stmt = $_db->query("SELECT id FROM `accounts`");
        $results = $stmt->fetchAllAssoc();

        return count($results);
    }

    public static function setDropboxData($data)
    {
        $user = User::getUserData();
        $_db = new MySQL(MYSQL_CONNECTION);

        //save/update the json encoded to db
        $values = array('dropbox_data' => $data);
        $where = "`id` = :id AND `jotform_username` = :jotform_username";
        $binds = array('id' => $user['id'], 'jotform_username' => $user['jotform_username']);
        $result = $_db->update('accounts', $values, $where, $binds);

        //update session
        $user['dropbox_data'] = $data;

        //remove senstive tokens of dropbox
        if ( self::$removeSensitiveInfo ) {
            $user = DropboxHandler::removeTokensFromPublic($user);
        }

        Session::save(self::$sessionVariable, $user);
    }

    public static function getDropboxTokens($fromUserData = null)
    {
        //we allow to get the sensitive info like tokens
        self::$removeSensitiveInfo = false;

        $me = (is_null($fromUserData)) ? self::getUserData(false) : $fromUserData;

        if ( is_null($me['dropbox_data']) OR !$me['dropbox_data'] ) {
            throw new Exception("Dropbox data is empty");
        }

        //disable again
        self::$removeSensitiveInfo = true;

        return array(
            'access_token' => $me['dropbox_data']->access_token,
            'access_token_secret' => $me['dropbox_data']->access_token_secret
        );
    }

    public function createAccount()
    {
        $values = array(
            'jotform_username' => $this->jot_username,
            'jotform_email' => $this->jot_email
        );

        return $this->_db->insert($this->tablename, $values);
    }

    public function login($user = null, $email = null)
    {
        if ( is_null($user) || is_null($email) ) throw new Exception("Username and email are missing");

        $binds = array('j_u' => $user, 'j_e' => $email);
        $queryStatement = "SELECT `id`,`jotform_username`, `jotform_email`, `dropbox_data`, `first_integration` FROM `".$this->tablename."` WHERE `jotform_username` = :j_u AND `jotform_email` = :j_e LIMIT 1";
        $stmt = $this->_db->query($queryStatement, $binds);
        $userData = $stmt->fetchAssoc();

        if ( $userData ) {
            //remove senstive tokens of dropbox
            if ( self::$removeSensitiveInfo ) {
                $userData = DropboxHandler::removeTokensFromPublic($userData);
            }

            Session::save(self::$sessionVariable, $userData);
            $user = Session::read(self::$sessionVariable);

            //after successfull login, update the API key just in case
            $this->updateUser_APIkey();

            return "Successfully logged in as : " . $user['jotform_username'];
        } else {
            return "Cannot login, incorrect username and email";
        }
    }

	public function handleAccount()
	{
        if ( self::isLoggedIn() ) {
            $userData = self::getUserData();
            return "Currently logged in as " . $userData['jotform_username'];
        }

        $binds = array('j_u' => $this->jot_username, 'j_e' => $this->jot_email);
        $queryStatement = "SELECT id FROM `".$this->tablename."` WHERE `jotform_username` = :j_u AND `jotform_email` = :j_e LIMIT 1";
        $stmt = $this->_db->query($queryStatement, $binds);
        $results = $stmt->fetchAllAssoc();

        if ( $results ) {
            //account already existed
            $logged_in = $this->login($this->jot_username, $this->jot_email);
            return "Account already existed : " . $logged_in;
        } else {
            //create new account
            if ( $this->createAccount() ) {
                $accountID = $this->getAccountID();
                $logged_in = $this->login($this->jot_username, $this->jot_email);
                return "Account successfully created with accountID: " . $accountID . " AND " . $logged_in;
            }
        }
	}

    public static function _flagUserFirstimeIntegration()
    {
        $user = User::getUserData();
        $_db = new MySQL(MYSQL_CONNECTION);

        $values = array('first_integration' => 0);
        $where = "`id` = :id AND `jotform_username` = :jotform_username";
        $binds = array('id' => $user['id'], 'jotform_username' => $user['jotform_username']);
        $result = $_db->update('accounts', $values, $where, $binds);
    }

    public static function saveAccessToken( $dropbox = null )
    {
        if ( !self::isLoggedIn() ) {
            throw new Exception("User is not logged in");
        }

        if ( is_null($dropbox) ) {
            throw new Exception("Dropbox data is required");
        }

        $user = self::getUserData(false);
        $values = array('dropbox_data' => $dropbox);
        $db = new MySQL(MYSQL_CONNECTION);
        $result = $db->update('accounts', $values, 'id = ?', array($user['id']));

        //once dropbox_data is updated merge it to session for latest user data
        $latest_userData = array_merge( $user, $values );

        //remove senstive tokens of dropbox
        if ( self::$removeSensitiveInfo ) {
            $latest_userData = DropboxHandler::removeTokensFromPublic($latest_userData);
        }

        Session::save(self::$sessionVariable, $latest_userData);

        return ($result) ? $dropbox : false;
    }

    private function updateUser_APIkey()
    {
        $user = User::getUserData();

        //save/update the json encoded to db
        $values = array('jotform_apikey' => $this->jot_apikey);
        $where = "`id` = :id AND `jotform_username` = :jotform_username";
        $binds = array('id' => $user['id'], 'jotform_username' => $user['jotform_username']);
        
        $this->_db->update('accounts', $values, $where, $binds);
    }

    public static function notif_email( $userid, $type = "set" )
    {
        $db = new MySQL(MYSQL_CONNECTION);
        $notifIsSet = true;

        if ( $type == 'set' )
        {
            $db->update('accounts', array('notif_email' => 1), "`id` = :id", array('id' => $userid));
        }
        else if ( $type == 'get' )
        {
            $queryStatement = "SELECT `notif_email` FROM `accounts` WHERE `id` = :id LIMIT 1";
            $stmt = $db->query($queryStatement, array('id' => $userid));
            $data = $stmt->fetchAssoc();

            if ( !$data['notif_email'] )
            {
                $notifIsSet = false;
            }
        }

        return $notifIsSet;
    }
}

?>