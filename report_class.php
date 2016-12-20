<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Report Class
 ************************************/

require_once( 'class-excel-xml.inc.php' );

// report classes extend this
abstract class report {

    // method to return description of report
    abstract function getdescription();

    // method to get the report data
    abstract function getdata();

    // method to return the setup form 
    abstract function setupform();

    // method to get the headings
    abstract function getheadings();

    // custom report overrides table view
    function customformat() {
        return false;
    }

    // create form, plugin needs only
    // supply unique elements
    function buildform( $elements, $type ) {
        $fixedelements = array(
            'type' => array(
                'type' => 'hidden',
                'value' => $type
                ),
            'mode' => array(
                'type' => 'select',
                'options' => array(
                    'd' => 'Display',
                    'xml' => 'Excel download'
                    ),
                'title' => 'Select output'
                ),
            'submit' => array(
                'type' => 'submit',
                'value' => 'Generate report'
                ),
            );
        $formdef = array(
            'name' => 'reportsetup',
            'method' => 'post',
            'autofocus' => 'true',
            'elements' => array_merge( $elements, $fixedelements)
            );
        return $formdef;
    }

    function datatoxml( $data, $filename ) {
        if (empty($data) or !is_array($data)) {
            return false;
        }

echo "<pre>"; print_r( $data ); die;
        // convert to 2d array
        $xmldata = array();

        // get the headings out of the first row
        $row = $data[0];
        $headers = array();
        foreach ($row as $key=>$value) {
            $headers[$key] = $key;
        }
        $xmldata[] = $headers;

        // now the actual data
        foreach ($data as $row) {
            $xmldata[] = (array)$row;
        }
        $xls = new Excel_XML;
        $xls->addArray( $xmldata );
        $xls->generateXML( $filename );
        die;
    }

    function displaytable( $data, $fields, $headings ) {
        $table = new paginated_table();
        $table->setLinesPerPage( 0 );
        $table->setFields( $fields );
        $table->setHeadings( $headings );
        $table->setRecords( $data );
        $table->setPrintable( true );
        return $table;
    }

}

