<?php

Class DropboxFolders
{
    private static function _getFolders( $dir = '/' )
    {
        //get user data / access tokens
        $user_tokens = User::getDropboxTokens();

        //create dropboxhandler instance
        $dropbox = new DropboxHandler();
        $dropbox->setTokens($user_tokens);
        $response = $dropbox->getMetaData($dir);

        $folders = array();

        if ( $response['http_code'] == 200 )
        {
            $metadata = json_decode($response['content'], true);

            //check if folder/directory
            if ( $metadata['is_dir'] == 1 )
            {
                //check for contents
                $contents = $metadata['contents'];
                if ( $contents AND count($contents) > 0 )
                {
                    //read contents
                    foreach ($contents as $content) {
                        //only read directories
                        if ( $content['is_dir'] OR $content['is_dir'] == 1 )
                        {
                            //push path to folders
                            $folders[] = $content['path'];
                        }
                    }
                }
                else
                {
                    throw new Exception("Directory is empty");
                }
            }
            else
            {
                // print_r($metadata);
                throw new Exception("Specified root is not a directory");
            }
        }
        else
        {
            // print_r($response);
            $errors = json_decode($response['content'], true);
            $errmsg = $errors['error'];
            throw new Exception($errmsg);
        }

        return $folders;
    }

    public static function getTree( $dir = '/' , $debug = false)
    {
        try
        {
            $folders = self::_getFolders( $dir );

            if( !empty($folders) )
            {
                foreach( $folders as $key => $value )
                {
                    $parts = explode('/', $value);
                    $name = htmlentities($parts[ count($parts) - 1]);
                    $folder_path = htmlentities($value);

                    $folders[ $key ] = array(
                        'name' => $name,
                        'path' => $folder_path
                    );
                }
            }

            if ( $debug ) return $folders;

            //throw an error
            if (empty($folders)) throw new Exception("No Folder(s) found");

            return array(
                'status' => 'success',
                'content' => $folders
            );
        }
        catch (Exception $e)
        {
            return array(
                'status' => 'error',
                'content' => $e->getMessage()
            );
        }
    }
}

// require_once(__DIR__."/../init.php");
// $output = DropboxFolders::getTree('/reports2Dropbox', true);
// echo "<pre>"; print_r($output);
?>