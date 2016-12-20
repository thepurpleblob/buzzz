<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Projects in Pipeline Report
 ************************************/

// report classes extend this
class report_pipeline extends report {

    function getdescription() {
        return "Projects in the pipeline";
    }

    function setupform() {
        // no form required for this one
        return false;
    }

    // method to get the report data
    function getdata() {
        global $db;
     
        $sql = "select jobnumber,name,projectname,projecttype,totalmargin,totalchargeout,totalactualmargin ".
            "from job,company where ".
            "job.companyid = company.id and ".
            "jobstatus='Proposal' ".
            "order by jobnumber desc "; 
        $data = $db->query( $sql );

        // format the data
        foreach ($data as $row) {
            $row->totalmargin = number_format($row->totalmargin,2);
            $row->totalchargeout = number_format($row->totalchargeout,2);
            $row->totalactualmargin = number_format($row->totalactualmargin,2);
        }
        return $data;
    }

    // return columns for default display
    function getcolumns() {
        $cols = array('jobnumber','name','projectname','projecttype','totalmargin',
            'totalchargeout','totalactualmargin');
        return $cols;
    }

    // return column headers
    function getheadings() {
        $cols = array('Job number','Client','Job Title','Job Type','Total Estimated Margin',
            'Total Revenue','Total Actual Margin');
        return $cols;
    }

    // return totals
    function gettotals() {
        global $db;
    
        $sql = "select sum(totalchargeout) as amount from job where jobstatus='Proposal'";
        $amount = $db->query( $sql,true,true );
        $totals = array( 'Total revenue' => number_format($amount) );
   
        return $totals;
    }

}

?>
