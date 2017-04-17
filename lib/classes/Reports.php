<?php

Class Reports
{	
    public $created_at;
    public $fields;
    public $form_id;
    public $id;
    public $isProtected;
    public $list_type;
    public $settings;
    public $status;
    public $title;
    public $updated_at;
    public $url;
    public static $extension = "xlsx";
    
    protected $_db;
    protected static $sessionVar = "_reports_data";
    protected $tablename = "reports";

    public static function isExistedFromDB($reportID = null)
    {
        return self::fetchSingle($reportID);
    }

    public static function isUserAuthorized($userID)
    {
        $user = User::getUserData();
        return ( $user['id'] == $userID );
    }

    /**
     * Will mask the report's password
     * @param $report - the report array to mask password field
     * @param $multiArray - whether the report comes from a multi array
     *                    - fetchAllAssoc() is a multi array result
     *                    - fetchAssoc() is a single array result
     * @access protected
     * @return array
     */
    protected static function _maskPassword($report, $multiArray = false)
    {
        if ( is_null($report) OR !$report ) return $report;

        if ( $multiArray )
        {
            foreach ($report as $key => $val) {
                if ( isset( $val['password'] ) AND !is_null($val['password']) ) {
                    $report[$key]['isProtected'] = true;
                    unset($report[$key]['password']);
                } else {
                    $report[$key]['isProtected'] = false;
                }
            }
        }
        else
        {
            if ( isset( $report['password'] ) AND !is_null($report['password']) ) {
                $report['isProtected'] = true;
                unset($report['password']);
            } else {
                $report['isProtected'] = false;
            }
        }

        return $report;
    }

    public static function fetchAll($userID = null, $limit = false)
    {
        if ( is_null($userID) ) throw new Exception("User ID is required when attempting to pull all reports");

        $_db = new MySQL(MYSQL_CONNECTION);

        $select = $_db->select();
        $select->from('reports', array(
            'filename', 'password',
            'filepath',
            'created' => 'created_at',
            'updated' => 'updated_at',
            'id', 'uid', 'fid', 'jotform_rid', 'jotform_title', 'jotform_url'
        ))->where('uid = :uid')->orderBy('uid', true)->limit($limit);
        $stmt = $select->execute(array('uid' => $userID));
        return self::_maskPassword($stmt->fetchAllAssoc(), true);
    }

    public static function fetchSingle($reportID = null, $includeFormData = false)
    {
        if ( is_null($reportID) ) throw new Exception("Report ID is required when fetching a report");

        $_db = new MySQL(MYSQL_CONNECTION);

        $user = User::getUserData();
        $select = $_db->select();

        //modify query if form data should be included
        if ( $includeFormData ) {
            //SELECT *,r.id as id, f.id as fid FROM `reports` as r INNER JOIN `forms` as f ON f.id = r.fid
            $select->from(array('r' => 'reports'), array(
                'filename', 'password',
                'filepath',
                'created' => 'created_at',
                'updated' => 'updated_at',
                'id', 'uid', 'fid', 'jotform_rid', 'jotform_title', 'jotform_url'
            ))->join('inner', 
                array('f' => 'forms'),
                'f.id = r.fid',
                array('jotform_formid', 'submission_count', 'last_submission_id', 'last_submission_created_at')
            );
        } else {
            //"SELECT * FROM `reports` as r "
            $select->from(array('r' => 'reports'))->column("*");
        }

        $select->where('r.`uid` = :uid')->where('r.`jotform_rid` = :jotform_rid')->limit(1);
        $stmt = $select->execute(array(
            'uid' => $user['id'],
            'jotform_rid' => $reportID
        ));

        return self::_maskPassword($stmt->fetchAssoc());
    }

    public static function remove($reportID = null, $userID = null)
    {
        if ( is_null($userID) ) throw new Exception("User IDs is required when removing a report");
        if ( is_null($userID) ) throw new Exception("Report ID is required when removing a report");

        if ( !User::isLoggedIn() || !self::isUserAuthorized($userID) )
        {
            throw new Exception('Unable to remove report, authentication problem');
        }

        $_db = new MySQL(MYSQL_CONNECTION);

        return self::_removeReport($_db, $reportID, $userID);
    }

    public static function _removeReport(MySQL $_db, $reportID, $use_userID = false)
    {
        $user = ( !$use_userID ) ? User::getUserData() : User::getUserData(false, $use_userID);
        $where = 'id = :id AND uid = :uid';
        $binds = array('id' => $reportID, 'uid' => $user['id']);

        //we need to delete the form associated with the report, we need the formid
        $select = $_db->select();
        $select->column('fid')->from('reports')->where( $where )->limit(1);
        $stmt = $select->execute( $binds );
        $result = $stmt->fetchAssoc();
        $reportFormID = $result['fid'];
        
        //delete now the report
        $_db->delete('reports', $where, $binds);

        //now the form associated with the report
        $binds['id'] = $reportFormID;
        $_db->delete('forms', $where, $binds);

        return $_db->lastStatement->affectedRows();
    }

    /**
     * Save the reports form to the database
     * @param $_db - the mysql db instance
     * @param $form - the form object to save
     * @param $use_userID (optional) - if set, only the report form of the user to save
     */
    public static function _saveForm(MySQL $_db, $form = null, $use_userID = false)
    {
        $result = false;
        $last_id = 0;
        $user = ( !$use_userID ) ? User::getUserData() : User::getUserData(false, $use_userID);

        $values = array(
            'uid' => $user['id'],
            'jotform_formid' => $form['id'],
            'submission_count' => (int) $form['count'],
            'last_submission_id' => $form['last_submission_id'],
            'last_submission_created_at' => (!$form['last_submission_created_at']) ? $_db->expr('CURRENT_TIMESTAMP') : $form['last_submission_created_at']
        );

        //insert every prepared data to table
        $result = $_db->insert('forms', $values);

        if ( !$result ) throw new Exception("Error saving of form to database");

        $last_id = $_db->insertId;
        
        return array(
            'last_id' => $last_id,
            'result' => $result
        );
    }

    /**
     * Update a reports form to the database
     * @param $_db - the mysql db instance
     * @param $form - the form obect to save
     * @param $use_userID (optional) - if set, only the report form of the user to update
     */
    public static function _updateForm(MySQL $_db, $form = null, $use_userID = false)
    {
        $result = false;
        $last_id = 0;
        $user = ( !$use_userID ) ? User::getUserData() : User::getUserData(false, $use_userID);

        $where = "`id` = :id";
        $binds = array('id' => $form['local_id']);
        $values = array(
            'uid' => $user['id'],
            'jotform_formid' => $form['id'],
            'submission_count' => (int) $form['count'],
            'last_submission_id' => $form['last_submission_id'],
            'last_submission_created_at' => (!$form['last_submission_created_at']) ? $_db->expr('CURRENT_TIMESTAMP') : $form['last_submission_created_at']
        );

        //update it
        $result = $_db->update('forms', $values, $where, $binds);

        if ( !$result ) throw new Exception('Error updating of form to database');

        $last_id = $form['local_id'];

        return array(
            'last_id' => $last_id,
            'result' => $result
        );
    }

    /**
     * Saves a report to the database
     * @param $_db - the mysql db instance
     * @param $report - the report object to save
     * @param $formID - the jotform form id
     */
    private static function _saveReport(MySQL $_db, $report = null, $formID)
    {
        $user = User::getUserData();
        $values = array(
            'uid' => $user['id'],
            'fid' => $formID,
            'jotform_rid' => $report['id'],
            'jotform_title' => $report['title'],
            'jotform_url' => $report['url'],
            'filepath' => $report['filepath'],
            'filename' => $report['filename'],
            'created_at' => $_db->expr('CURRENT_TIMESTAMP'),
            'updated_at' => $_db->expr('CURRENT_TIMESTAMP')
        );

        //if a password is set save it
        if ( isset($report['report_password']) AND $report['report_password'] ) {
            $values['password'] = $report['report_password'];
        }

        //insert every prepared data to table
        $result = $_db->insert('reports', $values);

        if ( !$result ) throw new Exception('Error saving of report to database');

        $last_id = $_db->insertId;
        
        return array(
            'last_id' => $last_id,
            'result' => $result
        );
    }

    /**
     * Update a report to the database
     * @param $_db - the mysql db instance
     * @param $report - the report object to save
     * @param $formID - the jotform form id
     */
    private static function _updateReport(MySQL $_db, $report = null, $formID)
    {
        $user = User::getUserData();
        $where = "`id` = :id";
        $binds = array('id' => $report['local_id']);
        $values = array(
            'uid' => $user['id'],
            'fid' => $formID,
            'jotform_rid' => $report['id'],
            'jotform_title' => $report['title'],
            'jotform_url' => $report['url'],
            'filepath' => $report['filepath'],
            'filename' => $report['filename']
        );

        //if a password is set save it
        if ( isset($report['report_password']) AND $report['report_password'] AND $report['report_password'] !== ':default:' ) {
            $values['password'] = $report['report_password'];
        }

        //update
        $result = $_db->update('reports', $values, $where, $binds);

        if ( !$result ) throw new Exception('Error updating of report to database');

        $last_id = $report['local_id'];
    
        return array(
            'last_id' => $last_id,
            'result' => $result
        );
    }

    private static function _initialUpload($report = null)
    {
        $user = User::getUserData(false);
        $first = $user['first_integration'];

        //send to dropbox at first integration once report is save
        if ( $user['first_integration'] == 1 OR $user['first_integration'] == true)
        {
            //get user data / access tokens
            $user_tokens = User::getDropboxTokens();

            //modify user first_integration to not upload on the next report integration
            User::_flagUserFirstimeIntegration();

            //create dropboxhandler instance
            $dropbox = new DropboxHandler();
            $dropbox->setTokens($user_tokens);

            //now read the reports file
            $response = Curl::get($report['url'], array('get_binary' => true));

            if ( $response['http_code'] == 200 )
            {
                $rawReportFile = $response['content'];
                unset($response);

                //now put to dropbox
                $dropbox->putFile($rawReportFile, $report['filepath'] . "/" . $report['title'] . '.' . self::$extension);
            }
            else
            {
                throw new Exception("Cannot access file from " . $report['url'] . ' with error : ' . json_encode($response));
            }
        }
    }

    public static function save($form = null, $report = null)
    {
        if ( is_null($form) ) throw new Exception("Form object is missing");
        if ( is_null($report) ) throw new Exception("Report object is missing");

        //sanitize array values
        $form = Tools::sanitizeArray($form);
        $report = Tools::sanitizeArray($report);

        $_db = new MySQL(MYSQL_CONNECTION);

        //save form data
        $formResult = self::_saveForm( $_db, $form );

        //save report data
        $reportResult = self::_saveReport( $_db, $report, $formResult['last_id'] );

        //send to dropbox if first time to integrate
        self::_initialUpload($report);

        return ( $formResult && $reportResult ) ? array('form' => $form, 'report'=> $report) : false;
    }

    public static function update($form = null, $report = null)
    {
        if ( is_null($form) ) throw new Exception("Form object is missing");
        if ( is_null($report) ) throw new Exception("Report object is missing");

        //sanitize array values
        $form = Tools::sanitizeArray($form);
        $report = Tools::sanitizeArray($report);

        $_db = new MySQL(MYSQL_CONNECTION);

        //save form data
        $formResult = self::_updateForm( $_db, $form );

        //save report data
        $reportResult = self::_updateReport( $_db, $report, $formResult['last_id'] );

        return ( $formResult && $reportResult ) ? array('form' => $form, 'report'=> $report) : false;
    }
}

?>