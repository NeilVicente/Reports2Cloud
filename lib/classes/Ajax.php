<?php

class Ajax extends AjaxHandler
{
    public $httpresponse;

    public function _setHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: Content-Type');       
    }

    public function handleAccount()
    {
        if ( User::isLoggedIn() )
        {
            $userData = User::getUserData();
            $this->success("Currently logged in as " . $userData['jotform_username'], array('user_data' => $userData) );
        }

        $user = new User($this->get('username'), $this->get('email'), $this->get('key'));
        $response = $user->handleAccount();
        $userData = User::getUserData(false);
        $this->success($response, array('user_data' => $userData));
    }

    public function logout()
    {
        Session::destroy();
        $this->success("Successfully logged out");
    }

    public function saveReport()
    {
        if ( count($this->get('form')) < 1 OR count($this->get('report')) < 1 ) {
            $this->error("Unable to save report");
        }

        $forms_raw = json_decode(stripslashes($this->get('form')), true);
        $reports_raw = json_decode(stripslashes($this->get('report')), true);
        
        //save report data
        $response = Reports::save($forms_raw, $reports_raw);

        $this->success("Reports successfully saved", array_merge( $response ));
    }

    public function updateReport()
    {
        if ( count($this->get('form')) < 1 OR count($this->get('report')) < 1 ) {
            $this->error("Unable to update report");
        }

        $forms_raw = json_decode(stripslashes($this->get('form')), true);
        $reports_raw = json_decode(stripslashes($this->get('report')), true);

        //update report data
        $response = Reports::update($forms_raw, $reports_raw);

        if ( $response ) {
            $this->success("Report successfully updated", array_merge($response));
        } else {
            $this->error("Unable to update report", array('response' => $response));
        }
    }

    public function checkReport()
    {
        $reports_raw = json_decode(stripslashes($this->get('report')), true);
        $isExisted = Reports::isExistedFromDB($reports_raw['id']);

        $this->success("Report successfully verified", array("isExisted" => $isExisted));
    }

    public function getSingleReport()
    {
        $result = Reports::fetchSingle($this->get('rid'), $this->get('withForm'));
        $this->success(($result) ? "Report successfully fetched" : "Report not found", array("reports" => $result));
    }

    public function getReports()
    {
        if ( is_null($this->get('userID')) )
        {
            $user = User::getUserData();
            $this->request['userID'] = $user['id'];
        }

        $result = Reports::fetchAll($this->get('userID'));
        $this->success(($result) ? "All reports successfully fetched" : "Reports not found", array("reports" => $result));
    }

    public function removeReport()
    {
        if ( is_null($this->get('userID')) )
        {
            $user = User::getUserData();
            $this->request['userID'] = $user['id'];
        }

        if ( is_null($this->get('reportID')) )
        {
            $this->error("Report ID is missing, please try again");
        }

        $result = Reports::remove($this->get('reportID'), $this->get('userID'));
        $isDeleted = ($result) ? true : false;
        $this->success(($isDeleted) ? "Report successfully removed" : "Report not found", array("isDeleted" => $isDeleted));
    }

    public function getDropboxFolders()
    {
        $directory = $this->get('dir');
        $response = DropboxFolders::getTree( $directory );

        $this->success("Dropbox folder fetch results", array('status' => $response['status'], 'content' => $response['content']));
    }
}

?>