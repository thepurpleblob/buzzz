<?php

// exceptions
class DatabaseException extends Exception {}

class db {

    var $dh;

    /**
     * Report error
     */
    function error( $message='' ) {
        echo "<p class=\"error\">\n";
        if (!empty($message)) {
             echo $message;
        }
        else {
            echo "Database error: " . mysql_error() . "\n";
        }
        echo "</p>\n";
        die;
    }

    /**
     * Connect to database
     */
    function connect( $host, $username, $password ) {
        if ($this->dh = mysql_connect( $host, $username, $password)) {
            return true;
        }
        else {
            throw new DatabaseException( "cannot connect to database ".mysql_error() );
        }
    }

    /**
     * select database
     */
    function select( $database ) {
        if (!mysql_select_db( $database )) {
            throw new DatabaseException( "cannot select database ".mysql_error() );
        }
        return true;
    }

    /**
     * Run query (return results)
     * @param single expecting only a single record (no array)
     * @param onefield 
     *
     */
    function query( $sql, $single=false, $onefield=false, $index='' ) {
        if (!$result = mysql_query($sql)) {
            throw new DatabaseException( "error in query " .mysql_error() );
        }
     
        // get results into array
        $rows = array();
        while ($row = mysql_fetch_assoc($result)) {
            $rowobject = (object)$row;
            if ($onefield and count($row)==1) {
                $rows[] = array_pop($row);
            }
            else {
                $rows[] = $rowobject;
            }
        }

        // if only one result, just return the object
        if (count($rows)==1) {
            if ($single) {
                return $rows[0];
            }
        }

        // if not a single result, and we get here (0 results is ok)..
        if ($single and (count($rows)>1)) {
            throw new DatabaseException( "Error in query - not a single result" );
        }

        // if index specified, change the index keys
        if (!empty($index)) {
            $newrows = array();
            foreach ($rows as $key => $row) {
                $newrows[$row->$index] = $row;
            }
            $rows = $newrows;
        }

        // return array (0 results, means empty array)
        return $rows;
    }

    /*
     * Run an update (no results)
     */
    function update( $sql ) {
        if (!mysql_query($sql)) {
            throw new DatabaseException( "Error in update ".mysql_error() );
        }
    }

    /*
     * Get the names of the tables
     */
    function get_tables() {
        return $this->query( 'show tables', false, true );
    }

    /*
     * get the names of the fields in a table
     */
    function get_fieldnames( $table ) {
        $rawfields = $this->query( "describe $table", false, true );
        $fieldnames = array();
        foreach ($rawfields as $rawfield) {
            $fieldnames[] = $rawfield->Field;
        }
        return $fieldnames;
    }

    /*
     * Insert a record in object form
     * only write fields that exist in the table
     */
    function insert_record( $record, $table ) {
        // get the table's fields
        $fieldnames = $this->get_fieldnames( $table );

        // bodge - can't write id field
        unset( $record->id );

        // build sql
        $sql = '';
        
        // convert to array and loop
        $fields = (array)$record;
        foreach ($fields as $field => $value) {
            if (in_array($field, $fieldnames )) { 
                if (!empty($sql)) {
                    $sql .= ",\n ";
                }
                $sql .= "$field = '$value'";
            }
        }

        $sql = "insert into $table\n set\n " . $sql;

        // do it
        $this->update( $sql );       

        // get the id field
        $sql = "select last_insert_id()";
        $id = $this->query( $sql, true, true );
        return $id;
    }

    /*
     * Update a record in object form
     * only write fields that exist in the table
     */
    function update_record( $record, $table, $where='id', $select=null ) {
        // get the table's fields
        $fieldnames = $this->get_fieldnames( $table );

        // bodge - can't write id field
        if ($where=='id') {
            $select = $record->id;
            unset( $record->id );
        }

        // build sql
        $sql = '';
        
        // convert to array and loop
        $fields = (array)$record;
        foreach ($fields as $field => $value) {
            if (in_array($field, $fieldnames )) { 
                if (!empty($sql)) {
                    $sql .= ",\n ";
                }
                $sql .= "$field = '$value'";
            }
        }
        $sql = "update $table\n set\n  $sql\n where $where = $select";

        // do it
        $this->update( $sql );       
    }

    /**
     * Get a records from the database, given field (name)
     * and matching value
     */
    function get_records( $table, $field='', $value=null, $extrasql='', $keyfield='' ) {
        // build sql
        if (!empty($field)) {
            $where = " where $field='$value'";
        }
        else {
            $where = '';
        }
        
        $sql =  "select * from $table $where $extrasql";
        $records = $this->query( $sql );

        // if keyfield defined replace array index
        if (!empty($keyfield)) {
            $new = array();
            foreach ($records as $record) {
                if (!isset($record->$keyfield)) {
                    throw new DatabaseException( "Field $keyfield not in record" );
                }
                $new[$record->$keyfield] = $record;
            }
            $records = $new;
        }
        return $records;
    }

    /**
     * Get a sing record from the database, given field (name)
     * and matching value
     */
    function get_record( $table, $field, $value ) {
        // build sql
        $sql =  "select * from $table where $field='$value'";
        $record = $this->query( $sql,true );
        return $record;
    }

    /**
     * insert from csv
     * get fields from csv file, fields are identified
     * by $fields array
     */
    function insert_csv( $filename, $table, $fields ) {
        if (!$fh = fopen($filename, 'r')) {
            throw new Exception( 'Could not open file' );
        }
        $header = true;
        while (($data = fgetcsv( $fh )) !== false) {
            // get rid of header line
            if ($header) {
                $header = false;
                continue;
            }

            // build data array
            $insert = array();
            $i = 0;
            foreach ($fields as $field) {
                $insert[$field] = addslashes(array_shift( $data ));
            }
            $this->insert_record( $insert, $table );
        }
        fclose( $fh );
    }
}

?>
