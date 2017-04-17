<?php 

Class Tools
{
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Sanitize the string against to XSS
     */
    public static function sanitize($str)
    {
        if(self::isJson($str))
        {
            return $str;
        }

        $str = strip_tags($str);

        $str = htmlspecialchars($str,ENT_QUOTES);

        return $str;
    }

    /**
     * Sanitize the strings on an array against to XSS
     */
    public static function sanitizeArray($array)
    {
        foreach($array as $key => $value)
        {
            if ( is_string($value) )
            {
                $array[$key] = self::sanitize($value);
            }
        }

        return $array;
    }
}

?>