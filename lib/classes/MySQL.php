<?php

require_once(__DIR__."/AMysql/AMysql.php");
Class MySQL extends AMysql
{
    public function __construct($conn)
    {
        return parent::__construct($conn);
    }
}