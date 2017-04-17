<?php

Class Cron
{
    protected $temp = array();

    /**
     * Flush buffers
     * @access private
     * @return void
     */
    private static function flush_buffers()
    { 
        flush();
        ob_flush(); 
    }

    /**
     * Convert JotForm specific Date format to proper timestamp
     * @param string $dateStr - the Date format to be converted to timestamp
     * @access private
     * @return the timestamp
     */
    private static function toTimeStamp($dateStr)
    {
        //'%Y-%m-%d %H:%i:%s'
        $strSplit = explode(' ', $dateStr);

        list($year, $month, $day) = explode('-', $strSplit[0]);
        list($hour, $minute, $second) = explode(':', $strSplit[1]);
        try{
            $time= mktime($hour, $minute, $second, $month, $day, $year);    
            return $time;
        }
        catch(Exception $e)
        {
            return mktime(1,1,1,1,1,1961);
        }
        
    }

    /**
     * Send report field to dropbox specific path
     * @param object $report - the whole report data that includes title,url,path, etc
     * @param string $extension - the file extesion
     * @access private
     * @return object of the dropbox upload file
     */
    private static function sendFile($report = null, $filename = false, $extension = 'xlsx')
    {
        if ( is_null($report) ) {
            return 'report_empty';
            // throw new Exception("Report data is empty");
        }

        User::$removeSensitiveInfo = false;
        $userData = User::getUserData($fromSession = false, $report['user_id']);
        $dropbox_tokens = User::getDropboxTokens($userData);
        User::$removeSensitiveInfo = true;

        //create dropboxhandler instance
        $dropbox = new DropboxHandler();
        $dropbox->setTokens($dropbox_tokens);

        //we need to verify if the report has a password$
        $hasPassword = isset($report['password']) AND $report['password'];
        if ( $hasPassword ) {
            // $url = parse_url($report['report_url']);
            // $report['report_url'] = sprintf('https://%s%s?passKey=%s', $url['host'], $url['path'], urlencode($report['password']));
            // echo "URL: " . $report['report_url'];
            //now read the reports file
            $response = Curl::post($report['report_url'], array(
                'params' => array(
                    'passKey' => $report['password']
                )
            ));
            // print_r($response);
        } else {
            //now read the reports file
            $response = Curl::get($report['report_url'], array('get_binary' => true));
        }
        
        if ( $response['http_code'] == 200 )
        {
            // if it has a password but the response type still text/html
            // then probably the password was incorrect
            if ( $hasPassword AND stripos($response['content_type'], 'text/html') !== false ) {
                return 'password_fail';
            }

            $rawReportFile = $response['content'];
            unset($response);

            //now put to dropbox
            $_filename = ($filename) ? $filename : $report['report_title'];
            $res = $dropbox->putFile($rawReportFile, $report['report_path'] . "/" . $_filename . '.' . $extension);
            return ( $res ) ? 'success' : 'fail';
        }
        else
        {
            $content = strtolower(strip_tags($response['content']));
            if ( strpos($content, 'file not found') !== false )
            {
                return 'file_not_found';
            }
            else
            {
                return 'file_error';
            }
            
            // throw new Exception("Cannot access file from " . $report['report_url'] . ' with error : ' . json_encode($response));
        }
    }

    /**
     * Start the Cron queue to upload all results
     * @access public
     * @return void
     */
    public static function startQueue()
    {
        ob_start();

        //get all reports from db
        $sql = "SELECT
                    r.id AS r_id,
                    r.uid AS user_id,
                    a.jotform_username AS j_user,
                    a.jotform_email AS j_email,
                    a.jotform_apikey AS j_apikey,
                    r.jotform_rid AS report_id,
                    r.jotform_title AS report_title,
                    r.jotform_url AS report_url,
                    r.password,
                    r.filepath  AS report_path,
                    f.id,
                    f.jotform_formid AS form_id,
                    f.submission_count AS form_submission_count,
                    f.last_submission_id AS form_last_submission_id,
                    f.last_submission_created_at AS form_last_submission_created_at,
                    r.filename
                FROM
                    `reports` AS r
                INNER JOIN
                    `forms` as f
                ON
                    r.fid = f.id
                INNER JOIN
                    `accounts` as a
                ON
                    r.uid = a.id
                ";

        //query database
        $_db = new MySQL(MYSQL_CONNECTION);
        $stmt = $_db->query($sql);
        $reports = $stmt->fetchAllAssoc();

        $table = "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" style=\"border-collapse:collapse;\">";
        $table .= "<th>Username</th><th>Filename Mod</th><th>Status</th><tbody>";

        //loop through the reports
        foreach( $reports as $local )
        {   

            try{
                if ( $local['j_apikey'] )
                {
                    //check report form if for new submissions
                    $call_url = "https://api.jotform.com/form/".$local['form_id']."/submissions?orderby=created_at,DESC&apiKey=" . $local['j_apikey'];
                    $response = Curl::get($call_url);

                    if ( $response['http_code'] == 200 )
                    {
                        ini_set('memory_limit', '64M');
                        //pul request content
                        $content = json_decode($response['content'], true);

                        //only get the top value, since it was sorted by descending order
                        $submissions = $content['content'];

                        //get the first submission
                        $submission = $submissions[0];

                        //get some of the data
                        $live = array(
                            'submission_id' => $submission['id'],
                            'form_id' => $submission['form_id'],
                            'count' => count( $submissions ),
                            'created_at' => $submission['created_at'],
                            'timestamp' => self::toTimeStamp($submission['created_at'])
                        );

                        //timestamp difference
                        $local['timestamp'] = self::toTimeStamp($local['form_last_submission_created_at']);

                        //filename modification
                        $_filename = (isset($local['filename']) && ($local['filename'] || !is_null($local['filename']))) ? $local['filename'] : false;

                        //do multiple verifications
                        if (
                            $live['form_id'] == $local['form_id'] AND //verify for same form id
                            $live['submission_id'] != $local['form_last_submission_id'] AND //if their last submission id is not equal
                            $live['count'] > $local['form_submission_count'] AND //if live submission count is greater than the local submission count
                            $live['timestamp'] > $local['timestamp'] //now check for the date created_at
                        )
                        {
                            //there is a new submission

                            //send file to dropbox
                            $file = self::sendFile($local, $_filename, Reports::$extension);
                            switch( $file )
                            {
                                case 'success':
                                    $table .= sprintf("<tr><td>%s</td><td>%s</td><td>%d New submission! File successfully uploaded!</td></tr>", $local['j_user'], $_filename, $live['count']);
                                    // echo sprintf("[%s][%s][http://www.jotform.com/form/%s] - %d New submission! File successfully uploaded!\n", $local['j_user'], $live['count']);
                                break;
                                case 'fail':
                                case 'file_error':
                                    $table .= sprintf("<tr><td>%s</td><td>%s</td><td>Failed to Upload!</td></tr>", $local['j_user'], $_filename);
                                    // echo sprintf("[%s][%s][http://www.jotform.com/form/%s] - Failed to Upload!\n", $local['j_user']);
                                break;
                                case 'password_fail':
                                    $table .= sprintf("<tr><td>%s</td><td>%s</td><td>Wrong report password!</td></tr>", $local['j_user'], $_filename);
                                break;
                                case 'file_not_found':
                                    //remove report from db
                                    $res = Reports::_removeReport($_db, $local['r_id'], $local['user_id']);
                                    $table .= sprintf("<tr><td>%s</td><td>%s</td><td>File not found! DELETED!</td></tr>", $local['j_user'], $_filename);
                                    // echo sprintf("[%s][%s][http://www.jotform.com/form/%s] - File not found! DELETED!\n", $local['j_user']);
                                break;
                            }

                            //update the db with the new submission count,created, and id
                            Reports::_updateForm($_db, array(
                                'id' => $live['form_id'],
                                'local_id' => $local['id'],
                                'count' => $live['count'],
                                'last_submission_id' => $live['submission_id'],
                                'last_submission_created_at' => $live['created_at']
                            ), $local['user_id']);
                        }
                        else
                        {
                            $table .= sprintf("<tr><td>%s</td><td>%s</td><td>No new submission!  </td></tr>", $local['j_user'], $_filename);
                            // echo sprintf("[%s][%s][http://www.jotform.com/form/%s] - No new submission!\n", $local['j_user']);
                        }
                    }
                    else if ( !User::notif_email( $local['user_id'], "get" ) )
                    {
                        echo 'oba';
                        var_dump($response);
                        Email::send(array(
                            'to' => $local['j_email'],
                            'from' => "Reports2Cloud Admin <root@hosting.interlogy.com>",
                            'subject' => 'JotForm App: Reports2Cloud - WARNING',
                            'contents' => "
                                You're API key used by the Reports2Cloud application has been expired.<br/>
                                In order to receive JotForm reports to your Dropbox Account we require you to get a new API key.<br/>
                                Please read and understand the guidelines below:<br/>
                                <br/>
                                1. Visit http://www.jotform.com/myaccount/ and login if necessary.<br/>
                                2. From the left side, navigate to <b>API</b> section<br/>
                                3. Find the <b>Excel2Dropbox</b> application from <b>Authorized Apps</b><br/>
                                4. Click the Delete(x) button, and wait for it to disappear<br/>
                                5. Navigate back to http://reports2cloud.jotform.io/. If you asked to login please do step 6 and 7 only<br/>
                                6. Click the <b>Allow</b> button and click <b>Integrate Now!</b><br/>
                                7. If you see a message <b>Verifying User</b> on a modal you're done, otherwise proceed to next step<br/>
                                8. Hover your mouse to the right side of the screen to open <b>Sidebar</b><br/>
                                9. Click the <b>Logout</b> below your avatar<br/>
                                10. Redo step 6<br/>
                                <br/>
                                Thank you for your cooperation!<br/>
                                <br/>
                                <br/>
                                Kenneth - JotForm Team"
                            )
                        );

                        User::notif_email( $local['user_id'] );
                    }
                }
                }
            catch(Exception $e)
            {
               echo "error happened ->"; var_dump($e); var_dump($reports);continue;
            }
        }

        $table .= "</tbody></table>";
        echo $table;

        Email::send(array(
            'to' => "kenma9123@gmail.com",
            'from' => "io4r2c Cron <root@hosting.interlogy.com>",
            'subject' => 'Reports2Cloud Report',
            'contents' => $table
        ));
    }
}

?>