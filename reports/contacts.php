<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Unpaid Invoices Report
 ************************************/

// report classes extend this
class report_contacts extends report {

    var $description = "Contacts";

    function getdescription() {
        return $this->description;
    }

    function setupform() {
        // select company
        global $db;
        global $clientstatusoptions;

        // get select list for companies
        $companies = $db->get_records( 'company' ); 
        $options = lib::records2options( $companies,'id','name','--All--' );

        // get select list for status
        $status = array( 0 => '--Any--' );
        foreach ( $clientstatusoptions as $key=>$value ) {
            $status[ $key ] = $value;
        }
        
        $elements = array(
            'company' => array(
                'type' => 'select',
                'title' => 'Select client',
                'options' => $options
                ),
            'status' => array(
                'type' => 'select',
                'title' => 'Client type',
                'options' => $status
                ),
            'tag' => array(
                'type' => 'checkbox',
                'title' => 'Tagged',
                ),
            'selected' => array(
                'type' => 'checkbox',
                'title' => 'Selected',
                ),
            );
        return $elements;
    }

    // method to get the report data
    function getdata() {
        global $db;

        // get parameters
        $cid = lib::required_param( 'company' );
        $status = lib::required_param( 'status' );
        $tag = lib::required_param( 'tag' );
        $selected = lib::required_param( 'selected' );

        // build sql fragments
        $company = empty($cid) ? '' : "and companyid=$cid";
        $statusfrag = empty($status) ? '' : "and status='$status'";
        $tagfrag = empty($tag) ? '' : "and tag='Yes'";
        $selectfrag = empty($selected) ? '' : "and selected='Yes'";
      
        // construct sql
        $sql = 'select * from contact, company '.
            'where contact.companyid = company.id '.
            "$company ".
            "$statusfrag ".
            "$tagfrag ".
            "$selectfrag "; 
        $data = $db->query( $sql );

        return $data;
    }

    // return columns for default display
    function getcolumns() {
        $cols = array('title','firstname','surname','jobtitle','telephone','email','name',
            'address1','address2','address3','town','country','postcode');
        return $cols;
    }

    // get the headings for display
    function getheadings() {
        return array( 'Title','First name','Surname','Job title','Telephone','Email','Company',
            'Address 1','Address 2','Address 3','Town','Country','Post Code'); 
    }

    // return totals
    function gettotals() {
        return false;
    }

}

?>
