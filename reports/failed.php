<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Unpaid Invoices Report
 ************************************/

// report classes extend this
class report_failed extends report {

    var $totalmargin = 0;
    var $totalchargeout = 0;
    var $totalprojectcost = 0;
    var $description = "Failed proposals";

    function getdescription() {
        return $this->description;
    }

    function setupform() {
        // select company
        global $db;

        $companies = $db->get_records( 'company' ); 
        $options = lib::records2options( $companies,'id','name','--All--' );

        $elements = array(
            'company' => array(
                'type' => 'select',
                'title' => 'Select client',
                'options' => $options
                ),
            );
        return $elements;
    }

    // method to get the report data
    function getdata() {
        global $db;

        $cid = lib::required_param( 'company' );
        if ($cid==0) {
            $company = '';
        }
        else {
            $company = "and companyid=$cid";
            $record = $db->get_record( 'company','id',$cid );
            $this->description = "Failed proposals for {$record->name}";
        }
     
        $sql = "select jobnumber,name,job.dateentered as dateentered,projectname, ".
            "totalchargeout, totalprojectcost, totalmargin, reasonforfail ". 
            "from job, company ".
            "where job.companyid=company.id $company ".
            "and jobstatus='Failed proposal' ".
            "order by jobnumber desc"; 
        $data = $db->query( $sql );

        // format the data
        foreach ($data as $row) {
            $this->totalmargin += $row->totalmargin;
            $row->totalmargin = number_format($row->totalmargin,2);
            $this->totalchargeout += $row->totalchargeout;
            $row->totalchargeout = number_format($row->totalchargeout,2);
            $this->totalprojectcost += $row->totalprojectcost;
            $row->totalprojectcost = number_format($row->totalprojectcost,2);
        }

        return $data;
    }

    // return columns for default display
    function getcolumns() {
        $cols = array('jobnumber','name','datentered','projectname',
            'totalchargeout','totalprojectcost','totalmargin','reasonforfail');
        return $cols;
    }

    // get the headings for display
    function getheadings() {
        return array( 'Job number','Client','Date created','Job title',
            'Total revenue','Total estm costs','Total estm margin','Reason for fail' );
    }

    // return totals
    function gettotals() {
        $totals = array();
        $totals[ 'Estimated margins' ] = number_format( $this->totalmargin,2 );
        $totals[ 'Revenue' ] = number_format( $this->totalchargeout,2 );
        $totals[ 'Estimated costs' ] = number_format( $this->totalprojectcost,2 );
        return $totals;
    }

}

?>
