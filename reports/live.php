<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Unpaid Invoices Report
 ************************************/

// report classes extend this
class report_live extends report {

    var $totalmargin = 0;
    var $totalchargeout = 0;
    var $totalactualmargin = 0;
    var $totalvariance = 0;

    function getdescription() {
        return "Live Jobs";
    }

    function setupform() {
        // no form required for this one
        return false;
    }

    // method to get the report data
    function getdata() {
        global $db;
     
        $sql = "select jobnumber,name,projectname,projecttype,totalmargin, ".
            "totalchargeout, totalactualmargin, variance ". 
            "from job, company ".
            "where job.companyid=company.id and ".
            "jobstatus not in('Failed proposal','Completed job') ".
            "order by jobnumber desc"; 
        $data = $db->query( $sql );

        // format the data
        foreach ($data as $row) {
            $this->totalmargin += $row->totalmargin;
            $row->totalmargin = number_format($row->totalmargin,2);
            $this->totalchargeout += $row->totalchargeout;
            $row->totalchargeout = number_format($row->totalchargeout,2);
            $this->totalactualmargin += $row->totalactualmargin;
            $row->totalactualmargin = number_format($row->totalactualmargin,2);
            $this->totalvariance += $row->variance;
            $row->variance = number_format($row->variance,2);
        }
        return $data;
    }

    // return columns for default display
    function getcolumns() {
        $cols = array('jobnumber','name','projectname','projecttype','totalmargin',
            'totalchargeout','totalactualmargin','variance');
        return $cols;
    }

    // get the headings for display
    function getheadings() {
        return array( 'Job number','Client','Job title','Job type','Total estm margin',
            'Total revenue','Total margin','Total variance' );
    }

    // return totals
    function gettotals() {
        $totals = array();
        $totals[ 'Estimated margins' ] = number_format( $this->totalmargin,2 );
        $totals[ 'Revenue' ] = number_format( $this->totalchargeout,2 );
        $totals[ 'Actual margins' ] = number_format( $this->totalactualmargin,2 );
        $totals[ 'Variance' ] = number_format( $this->totalvariance,2 );
        return $totals;
    }

}

?>
