<?php

Class Session
{
    /**
     * Session ID
     */
    public static $session_id;

    /**
     * Class session constructor
     */
    function __construct($session_id = null)
    {
        if ( !isset( $_SESSION ) )
        {
            //if session id is present, then set, otherwise get
            if ( !is_null($session_id) ) {
                self::setSessionID($session_id);
            } else {
                self::$session_id = self::getSessionID();
            }

            //start session
            session_start();
        }
    }

    /**
     * Set session ID
     * @param $session_id = the session id
     * @access public
     * @return void
     */
    public static function setSessionID($session_id)
    {
        session_id($session_id);
    }

    /**
     * Get the current session ID
     * @access public
     * @return the session id
     */
    public static function getSessionID()
    {
        return session_id();
    }

    /**
     * Regenerate session ID
     * @access public
     * @return the new generated session id
     */
    public static function regenerateID()
    {
        session_regenerate_id();
        self::$session_id = session_id();

        //return generated id
        return self::$session_id;
    }

    /**
     * Save a session variable
     * @param $session_name - the session variable name
     * @param $session_val - the session variable value
     * @access public
     * @return the created session variable
     */
    public static function save( $session_name = null, $session_val = null )
    {
        if ( is_null($session_name) ) throw new Exception("Session name required");
        if ( is_null($session_val) ) throw new Exception("Session value required");

        $_SESSION[ $session_name ] = $session_val;

        //return session
        return $_SESSION[ $session_name ];
    }

    /**
     * Get/Read a session variable
     * @param $session_name - the session variable name that will be read
     * @access public
     * @return the session variable value otherwise false
     */
    public static function read( $session_name = null )
    {
        if ( is_null($session_name) ) throw new Exception("Session name required");

        return (isset($_SESSION[ $session_name ])) ? $_SESSION[ $session_name ] : false;
    }

    /**
     * Check wheter a session variable is existed - HELPER for read()
     * @access public
     * @return boolean value
     */
    public static function isExist($session_name = null)
    {
        if ( is_null($session_name) ) throw new Exception("Session name required");
        return (isset($_SESSION[ $session_name ])) ? true : false;
    }

    /**
     * Remove a specific session variable
     * @param $session_name - the session variable name that will be remove
     * @access public
     * @return boolean value, true if successfully deleted otherwise false
     */
    public static function remove( $session_name = null )
    {
        if ( is_null($session_name) ) throw new Exception("Session name required");

        if ( isset($_SESSION[ $session_name ]) ) {
            unset($_SESSION[ $session_name ]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fully destroy both session variables and the session
     * @access public
     * @return void
     */
    public static function destroy()
    {
        session_unset(); //destroys variables
        session_destroy(); //destroys session;
    }
}

?>