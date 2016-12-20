<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Revenue for month
 ************************************/

// report classes extend this
class report_revenue extends report {

    var $totalmargin = 0;
    var $totalchargeout = 0;
    var $totalprojectcost = 0;
    var $description = "Revenue for month";

    function getdescription() {
        return $this->description;
    }

    function setupform() {
        // select quarter
        global $db;

        // first get the years in which invoices exist
        $sql = "select year(invoicedate) as iyear,month(invoicedate) as imonth ".
            " from invoice ".
            " order by invoicedate desc";
        $months = $db->query( $sql );

        // add the quarters to the years
        // we'll reconstitute these into date ranges
        $options = array();
        foreach ($months as $month) {
            $iyear = $month->iyear;
            $imonth = $month->imonth;
            if ($imonth<3) {
                $iyear--;
                $quarter = 4;
            }
            else {
                $quarter = floor(($imonth-3)/3)+1;
            }
            $options["{$iyear}_{$quarter}"] = "$iyear Quarter $quarter"; 
        }

        $elements = array(
            'quarter' => array(
                'type' => 'select',
                'title' => 'Select year/quarter',
                'options' => $options
                ),
            );
        return $elements;
    }

    // method to get the report data
    function getdata() {
        global $db;

        $select = lib::required_param( 'quarter' );

        // get the year and the quarter and work out the date range
        list( $year,$quarter ) = explode( '_',$select );
        $nextyear = $year+1;
        switch ($quarter) {
        case 1:
            $start = "$year-03-01";
            $finish = "$year-05-31";
            break;
        case 2:
            $start = "$year-06-01";
            $finish = "$year-08-31";
            break;
        case 3:
            $start = "$year-09-01";
            $finish = "$year-11-30";
            break;
        case 4:
            $start = "$year-12-01";
            $finish = "$nextyear-02-29";
            break;
        default;
            throw new Exception( "invalid quarter value" );
        }
 
        $sql = "select *,month(invoicedate) as reportmonth from invoice,job,company ".
            "where invoice.jobid = job.id ".
            "and   invoice.companyid = company.id ".
            "and   (invoicedate>='$start') and (invoicedate<='$finish')"; 
        // !! bodge: jobnumber as key kills duplicate jobs
        $data = $db->query( $sql,false,false,'jobnumber' );

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
            'totalchargeout','totalprojectcost','totalmargin');
        return $cols;
    }

    // get the headings for display
    function getheadings() {
        return array( 'Job number','Client','Date created','Job title',
            'Total revenue','Total estm costs','Total estm margin' );
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
