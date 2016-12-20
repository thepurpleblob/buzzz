<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Unpaid Invoices Report
 ************************************/

// report classes extend this
class report_unpaid extends report {

    function getdescription() {
        return "Unpaid invoices";
    }

    function setupform() {
        // no form required for this one
        return false;
    }

    // method to get the report data
    function getdata() {
        global $db;
     
        $sql = "select invoicenumber,invoicedate,jobnumber,name,subtotal,projectname, ".
            "datediff(now(),invoicedate)-invoicetype as overdue ".
            "from invoice,company,job where ".
            "invoice.companyid=company.id and ".
            "invoice.jobid=job.id and ".
            "paid='No' order by invoicenumber desc"; 
        $data = $db->query( $sql );

        // format the data
        foreach ($data as $row) {
            $row->invoicedate = lib::decodemysqldate($row->invoicedate);
            $row->subtotal = number_format($row->subtotal,2);

            // check overdue days not negative
            if ($row->overdue<1) {
                $row->overdue = '-';
            }
        }
        return $data;
    }

    // return columns for default display
    function getcolumns() {
        $cols = array('invoicenumber','invoicedate','jobnumber','name','projectname',
            'subtotal','overdue');
        return $cols;
    }

    // get the headings for display
    function getheadings() {
        return array( 'Invoice number','Date','Job Number','Client','Job title',
            'Invoice Amount','Days Overdue' );
    }

    // return totals
    function gettotals() {
        global $db;
    
        $sql = "select sum(subtotal) as amount from invoice where paid='No'";
        $amount = $db->query( $sql,true,true );
        $totals = array( 'Total amount outstanding' => number_format($amount) );
   
        return $totals;
    }

}

?>
