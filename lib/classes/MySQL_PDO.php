<?php

// error_reporting(E_ALL);

class MySQL_PDO
{
    // Singleton Instance Storage
    private $instance;


    //result holder object
    private $last_select_prepared;
    private $_result;
   
    public function __construct($host = "localhost", $dbname ="reports2dropbox", $user = "root", $pass = "")
    {
        if ( MODE === 'live' ) {
            $dbname = "io4r2d_reports2dropbox";
            $user = "io4r2d_kenneth";
            $password = "0=dT-n,5GC~u";
        }

        if ( !$this->instance )
        {
            if ( class_exists(PDO) || extension_loaded('pdo') ) {
                $this->instance = new PDO(sprintf("mysql:host=%s;dbname=%s", $host, $dbname), $user, $pass);
                $this->instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            } else {
                throw new Exception("PDO extension is not available on this server.");
            }
        }

        return $this->instance;
    }
    
    function __destruct() {}

    public function query_sql( $sql_statement )
    {
        $exec = $this->instance->prepare( $sql_statement );
        $rel = $exec->execute();
        return $rel;
    }
    
    /* The function executes a prepared statement
    * @param sql String containing an SQL statement
    * @param arr Array containing values for the prepared statement[optional]
    * @return true if the query executed successfuly, false if not
    */
    public function select_sql( $sql_statement, $arr = null )
    {
        //var_dump($arr);
        
        if ( !$this->isConnected() ) {
            return false;
        } else {
            $this->last_select_prepared = null;

            $this->last_select_prepared = $this->instance->prepare( $sql_statement );
            
            $rel = ( is_null( $arr ) ) ?
                    $this->last_select_prepared->execute() :
                    $this->last_select_prepared->execute( $arr );
            return $rel;
        }
    }
    
    public function execute_sql($sql_statement, $arr)
    {
        return $this->select_sql($sql_statement, $arr);
    }
    
    /*
    * Wrapper for insert function
    * - This will generate sql statement and execute automatically
    * $tableName - Name of the table to be inserted to.
    * $valuesArray = An associative array containing
    * the column names as keys and values as data.
    * -- column_name => column_value
    * @return true on success, false on failure
    */
    public function insert( $tableName, $valuesArray )
    {
        if ( !$this->isConnected() ) {
            return false;
        } else {
            $sql = $this->_BuildSQLInsert( $tableName, $valuesArray );
            $exec = $this->instance->prepare( $sql );

            //We simply call the execute with each array of data.
            $this->_result = $exec->execute( $valuesArray );
            return ( !$this->_result ) ? false : true;
        }
    }
    
    /*
    * Wrapper for update function
    * - This will generate sql statement and execute automatically
    * $tableName - Name of the table to be inserted to.
    * $valuesArray = An associative array containing
    * the column names as keys and values as data.
    * -- column_name => column_value
    * $whereArray = An associative array containing
    * the column names as keys and values as data.
    * -- column_name => column_value
    * @return true on success, false on failure
    */
    public function update( $tableName, $valuesArray, $whereArray = null )
    {
        if ( !$this->isConnected() ) {
            return false;
        } else {
            $sql = $this->_BuildSQLUpdate( $tableName, $valuesArray , $whereArray );
            $exec = $this->instance->prepare( $sql );
          
            //wrap the two array together
            $fina_data_array = $this->combine_arrays( $valuesArray, $whereArray );
           
            //We simply call the execute with each array of data.
            $this->_result = $exec->execute( $fina_data_array );
            
            return ( !$this->_result ) ? false : true;
        }
    }

    /*
    * Wrapper for delete function
    * - This will generate sql statement and execute automatically
    * $tableName - Name of the table on where to be delete to.
    * $whereArray = An associative array containing
    * the column names as keys and values as data ( conditions )
    * -- column_name => column_value
    * @return true on success, false on failure
    */
    public function delete( $tableName, $whereArray = null )
    {
        if ( !$this->isConnected() ) {
            return false;
        } else {
            $sql = $this->_BuildSQLDelete( $tableName, $whereArray );
            $exec = $this->instance->prepare( $sql );

            $this->_result = $exec->execute( $whereArray );
            
            return ( !$this->_result ) ? false : true;
        }
    }
    
    /*
    * Wrapper for select function
    * - This will generate sql statement and execute automatically
    * $tableName - Name of the table to be selected to.
    * $query['where'] = An associative array containing
    *   the column names as keys and values as data.
    *   e.g column_name => column_value
    * $query['columns'] = An associative array containing
    *   the column names, Can also be a string only
    * $query['columns'] = array( "field1 ", "field2" )
    *   can be interpreted as: field1, field2
    *   or
    * $query['columns'] = "field1"
    * can be interpreted as: field1
    * -- leave $query['columns'] empty to use "*"
    * $query['sortCol'] = the same as $query['columns'], but WITH ALIAS exception
    * $query['sortType'] = TRUE: sort ascending, FALSE: sort descending
    * $query['limit'] = limit the queries. eg "10" or "0,10"
    * @return true on success, false on failure
    */
    public function select( $tableName, $query = array() )
    {
        $q = array_merge(array(
            'where' => null,
            'columns' => null,
            'sortCol' => null,
            'sortType' => true,
            'limit' => null
        ), $query);
        
        if ( !$this->isConnected() ) {
            return false;
        } else {
            #empty last prepared var
            $this->last_select_prepared = null;

            $sql = $this->_BuildSQLSelect( $tableName, $q['where'], $q['columns'], $q['sortCol'], $q['sortType'], $q['limit'] );

            $this->last_select_prepared = $this->instance->prepare( $sql );
            
            #produce true when success, false if not
            return $this->last_select_prepared->execute( $q['where'] );
        }
    }
    
    /*
    * @return sigle row in array format
    */
    public function fetch_assoc()
    {
        return $this->last_select_prepared->fetch( PDO::FETCH_ASSOC );
    }
    
    /*
    * @return all rows in array format
    */
    public function fetch_all()
    {
        return $this->last_select_prepared->fetchAll();
    }
    /*
    * @return all rows in array format -assoc mode
    */
    public function fetch_all_assoc()
    {
        return $this->last_select_prepared->fetchAll( PDO::FETCH_ASSOC );
    }
    
    /*
    * @return all rows in array format - class mode
    */
    public function fetch_class( $classname )
    {
        return $this->last_select_prepared->fetchAll( PDO::FETCH_CLASS, $classname );
    }
    
    /*
    * @return number of rows from last select query
    */
    public function count_rows()
    {
        return $this->last_select_prepared->rowCount();
    }
    
    /*
    * @return the last inserted ID (depends on the primary key)
    */
    public function last_insert_id()
    {
        return $this->instance->lastInsertId();
    }
    
    /*
    * directly query a statement
    * @return the query object
    */
    public function query( $sql )
    {
        return ( !$this->last_select_prepared ) ? $this->instance->query( $sql ) : false;
    }
    
    /*
    * SQL INSERT statement builder
    * Sample:
    * $tableName = listings
    * $valuesArray = array( price => 100 , month => June 1,2012 ) ( KEYS WILL BE USE )
    * _BuildSQLInsert( $tableName, $valuesArray );
    * Return String with Named Placeholders - ready for PDO execution:
    * -INSERT INTO listings( price, month ) VALUES( :price, :month )
    */
    private function _BuildSQLInsert( $tableName, $valuesArray )
    {
        $columns = "";
        $values = "";
        foreach ( $valuesArray as $key => $value ) {
            // Build the columns
            if ( strlen( $columns ) == 0 ) {
                $columns = "" . $key . "";
                $values = ":" . $key . "";
            } else {
                $columns .= ", " . $key . "";
                $values .= ", :" . $key . "";
            }
        }
        $sql = "INSERT INTO " . $tableName . " (" . $columns . ") VALUES (" . $values . ")";
        return $sql;
    }

    /*
    * SQL UPDATE statement builder
    * Sample:
    * $tableName = listings
    * $valuesArray = array( price => 100 , month => June 1,2012 ) ( KEYS WILL BE USE )
    * $whereArray = array( pkey => 1 ) ( KEYS WILL BE USE )
    * _BuildSQLUpdate( $tableName, $valuesArray, $whereArray );
    * Return String with Named Placeholders - ready for PDO execution:
    * -UPDATE listings SET price = :price, month = :month WHERE pkey = :pkey
    */
    private function _BuildSQLUpdate( $tableName, $valuesArray, $whereArray = null )
    {
        $sql = "";
        foreach ( $valuesArray as $key => $value ) {
            //key = :key
            if ( strlen( $sql ) == 0 ) {
                $sql = "" . $key . " = :" . $key;
            } else {
                $sql .= ", " . $key . " = :" . $key;
            }
        }
        $sql = "UPDATE " . $tableName . " SET " . $sql;
        //if where array is set, Build the where clause
        if ( is_array( $whereArray ) ) {
            $sql .= $this->_BuildSQLWhereClause( $whereArray );
        }
        return $sql;
    }

    /*
    * DELETE statement builder.
    * $tableName = name of the table on where to delete
    * $whereArray = array( pkey => 1, date => June 1 2012 ) ( KEYS WILL BE USE )
    * _BuildSQLDelete( $whereArray );
    * Return String with Named Placeholders - ready for PDO execution:
    * WHERE key = :key AND date = :date
    */
    private function _BuildSQLDelete( $tableName, $whereArray = null )
    {
        $sql = "DELETE FROM " . $tableName . "";
        if ( !is_null( $whereArray ) ) {
            $sql .= $this->_BuildSQLWhereClause( $whereArray );
        }
        return $sql;
    }
    
    /*
    * WHERE clause statement builder that is used by _BuildSQLUpdate and _BuildSQLDelete
    * $whereArray = array( pkey => 1, date => June 1 2012 ) ( KEYS WILL BE USE )
    * _BuildSQLWhereClause( $whereArray );
    * Return String with Named Placeholders - ready for PDO execution:
    * WHERE key = :key AND date = :date
    */
    private function _BuildSQLWhereClause( $whereArray )
    {
        $where = "";
        foreach ( $whereArray as $key => $value ) {
            $value = ( !empty( $value ) ) ? $value : '';
            //key = :key
            if ( strlen( $where ) == 0 ) {
                $where = " WHERE " . $key . " = :" . $key;
            } else {
                $where .= " AND " . $key . " = :" . $key;
            }
        }
        return $where;
    }

    /*
    * @function _BuildSQLSelect
    * @abstract Build an sql SELECT statement
    * depends from the passed array
    */
    private function _BuildSQLSelect( $tableName, $whereArray = null, $columns = null, $sortColumns = null, $sortAscending = true, $limit = null )
    {
        if ( !is_null( $columns ) ) {
            $col = $this->_BuildSQLColumns( $columns );
        } else {
            $col = "*";
        }
        $sql = "SELECT " . $col . " FROM " . $tableName;
        //concat where array
        if ( !is_null( $whereArray ) ) {
            $sql .= $this->_BuildSQLWhereClause( $whereArray );
        }
        //concat sort if set
        if ( !is_null( $sortColumns ) ) {
            $sql .= " ORDER BY " . $this->_BuildSQLColumns( $sortColumns ) . ( $sortAscending ? " ASC" : " DESC" );
        }
        //concat limt if set
        if ( !is_null( $limit ) ) {
            $sql .= " LIMIT " . $limit;
        }
        //echo $sql ." <br/>";
        return $sql;
    }
    
    /*
    * @function _BuildSQLColumns
    * @abstract Build an sql COLUMN statement (with alias)
    * depends from the passed array
    */
    private function _BuildSQLColumns( $columns, $alias = null )
    {
        switch ( gettype( $columns ) ) {
            case "array":
                $sql = "";
                foreach ( $columns as $keys => $value ) {
                    // Build the columns
                    if ( strlen( $sql ) == 0 ) {
                        $sql = $value;
                    } else {
                        $sql .= ", " . $value;
                    }
                }
                return $sql;
            break;
            case "string":
                return $columns;
            break;
            default:
                return false;
            break;
        }
    }
    
    /*
    * @function isConnected
    * @abstract Check if an instance is set
    */
    private function isConnected()
    {
        if ( !$this->instance ) {
            throw new Exception("Not connected to any Database");
        } else {
            return true;
        }
    }
    
    /*
    * @function combine_arrays
    * @abstract Combine two arrays
    */
    public function combine_arrays( $arrayA, $arrayB )
    {
        $first_array = (array)$arrayA;
        $second_array = (array)$arrayB;

        foreach( $second_array as $key => $val ) {
            $first_array[ $key ] = $val;
        }
        return $first_array;
    }
}

?>