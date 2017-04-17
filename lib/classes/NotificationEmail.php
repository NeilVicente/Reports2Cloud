<?php

Class NotificationEmail
{
    public static function sendEmailToUsers()
    {
        //get all reports from db
        $sql = "SELECT
                    `jotform_email` AS email,
                    `id` AS user_id
                FROM
                    `accounts`
                WHERE
                    `jotform_apikey` IS NOT NULL
                    AND `dropbox_data` IS NOT NULL 
                    AND `subscribe_notification` = '1'
                GROUP BY email";

        //query database
        $_db = new MySQL(MYSQL_CONNECTION);
        $stmt = $_db->query($sql);
        $results = $stmt->fetchAllAssoc();

        $email_template = file_get_contents(ROOT . "emails/email_template.html");
        $email_content = file_get_contents(ROOT . "emails/email_content.txt");
        $email_template = str_replace("%%EMAIL_CONTENT%%", $email_content, $email_template);

        // $results = array(array("email" => "kenma9123@gmail.com", "user_id" => "1001"));

        foreach( $results as $line )
        {
            echo "Send email to " . $line['email'] . "<br/>";

            Email::send(array(
                'to' => $line['email'],
                'from' => "Reports To Cloud <root@hosting.interlogy.com>",
                'subject' => 'JotForm - Reports To Cloud Application Updates!',
                'contents' => str_replace(array("%%EMAIL%%", "%%ID%%"), array($line['email'], $line['user_id']), $email_template)
            ));
        }

        echo "Done";
    }

    public static function unsubscribe($request = null)
    {
        if ( is_null($request) OR !is_array($request) OR ( !isset($request['email']) AND !isset($require['id']) ) ) {
            throw new Exception("Error!");
        }

        $email = htmlspecialchars(strip_tags($request['email']));
        $id = htmlspecialchars(strip_tags($request['id']));

        $sql = sprintf("SELECT subscribe_notification AS notif FROM `accounts` WHERE `id` = '%s' AND `jotform_email` = '%s' LIMIT 1", $id, $email);

        //query database
        $_db = new MySQL(MYSQL_CONNECTION);
        $stmt = $_db->query($sql);
        $result = $stmt->fetchAssoc();

        if ( $result['notif'] ) {
            $data = array('subscribe_notification' => 0);
            $success = $_db->update('accounts', $data, 'id = ?', array($id));
            echo "You have successfully unsubscribe!";
        } else {
            echo "Already unsubscribe!";
        }
    }
}

?>