<?php
    // error_reporting(E_ALL);
    define("MODE", ( $_SERVER['HTTP_HOST'] === 'localhost' ) ? 'dev' : 'live');
    define("ROOT", realpath(__DIR__."/../") . "/");
    define("BASE_URL", (MODE==="dev") ? '/reports2Dropbox/' : '/');
    if( !defined('STDIN') ) {
        define("HTTP_URL", "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . BASE_URL);
    }
    /**
     * Auto load function which loads all classes automatically no need to write includes for each class
     * @param object $class_name
     * @return bool
     */
    //we only need to load php
    spl_autoload_extensions('.php');
    function __autoload($className)
    {
        //list comma separated directory name
        $directory = array('lib/classes/');

        //list of comma separated file format
        $fileFormat = array('%s.php');

        foreach ($directory as $current_dir)
        {
            foreach ($fileFormat as $current_format)
            {
                $path = ROOT . $current_dir . sprintf( $current_format, $className );
                if ( file_exists( $path ) ) //if a file matched load it
                {
                    // echo $path . "\n";
                    require_once($path);
                    break;
                }
            }
        }
    }

    spl_autoload_register('__autoload');

    //start session
    if( !isset( $_SESSION ) ){
        session_start();
    }

    //start database
    define('DB_HOST', "localhost");
    define('DB_NAME', "io4r2c_reports2cloud");
    define('DB_USER', (MODE=='dev')?"root":"io4r2c_kenma9123");
    define('DB_PASS', (MODE=='dev')?"":"kenMA__io4r2c");

    if( !defined('MYSQL_CONNECTION') ) {
        $conn = @mysql_connect(DB_HOST, DB_USER, DB_PASS);
        @mysql_select_db(DB_NAME, $conn);
        define('MYSQL_CONNECTION', $conn);
    }
?>