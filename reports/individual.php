<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Individual Job Report
 ************************************/

require "total.php";

// report classes extend this
class report_individual extends report {

    var $description = "Job report";
    var $jobdata = null;
    var $costdata = null;

    function getdescription() {
        return $this->description;
    }

    function setupform() {
        // select job
        global $db;

        $sql = "select id,concat(jobnumber,' ',projectname) as jobdesc ".
            " from job order by jobnumber desc";
        $jobs = $db->query( $sql ); 
        $options = lib::records2options( $jobs,'id','jobdesc' );

        $elements = array(
            'id' => array(
                'type' => 'select',
                'title' => 'Select job',
                'options' => $options
                ),
            );
        return $elements;
    }

    // method to get the report data
    function getdata() {
        global $db;
        global $config;
        global $costs;

        $jid = lib::required_param( 'id' );

        // total the job
        total( $jid );

        // get list of key buzzzers    
        $keybuzzzoptions = explode(',',$config->keybuzzzers);

        $sql = "select * from job,company where job.companyid = company.id ".
            "and job.id = $jid";
        $data = $db->query( $sql,true );

        // format the data
        $data->totalmargin = number_format($data->totalmargin,2);
        $data->totalactualmargin = number_format($data->totalactualmargin,2);
        $data->totalchargeout = number_format($data->totalchargeout,2);
        $data->totalprojectcost = number_format($data->totalprojectcost,2);
        $data->variance = number_format($data->variance,2);
        $data->keybuzzzcontact = @$keybuzzzoptions[$data->keybuzzzcontact];

        // get the contact
        if (!empty($data->contactid)) {
            $contact = $db->get_record( 'contact','id',$data->contactid );
            $data->contact = "{$contact->firstname} {$contact->surname}";
        }
        else {
            $data->contact = '&nbsp;';
        }

        // get the jobcost data
        $costdata = $db->get_records( 'jobcost','jobid',$jid );

        // what we want is total of each jobcost type, so
        $costdata = array();
        foreach ($costs as $detail => $cost) {
            $sql = "select sum(amount) as est, sum(actual) as act from jobcost ".
                "where jobid=$jid and detail='$detail'";
            $sums = $db->query( $sql,true,false );
            if (($sums->est>0) or ($sums->act>0)) {
                $sums->est = number_format( $sums->est,2 );
                $sums->act = number_format( $sums->act,2 );
                $costdata[$detail] = $sums;
            }
        }

        // combine costdata to data for report
        foreach ($costdata as $detail => $cost) {
            $description = $costs[$detail];
            $data->$description = (array)$cost;
        }

        // we just store all this locally as the report is custom
        $this->jobdata = $data;
        $this->costdata = $costdata;

        return $data;
    }

    // custom report format
    function customformat() {
        global $costs;

        $data = $this->jobdata;
        $costdata = $this->costdata;

        $e =  "<div id=\"pr_jsheader\"><img src=\"pix/buzzzlogo_mono.png\">";
        $e .= "<h2>JOB SHEET</h2></div>\n";

        // job detail
        $e .= "<div class=\"pr_jsbox\">";
        $e .= "<table>\n";
        $e .= "<tr><th>Job Detail</th><th>{$data->jobnumber}</th></tr>\n";
        $e .= "<tr><th>&nbsp;</th><th>{$data->projectname}</th></tr>\n";
        $e .= "<tr><th>&nbsp;</th><th>{$data->jobdescription}</th></tr>\n";
        $e .= "</table></div>\n";

        // client details
        $e .= "<div class=\"pr_jsbox\">";
        $e .= "<table>\n";
        $e .= "<tr><th>Client Details</th><td>{$data->name}</td></tr>\n";
        $e .= "<tr><th>&nbsp;</th><td>{$data->contact}</td></tr>\n";
        $e .= "</table></div>\n";

        // investment details
        $e .= "<div class=\"pr_jsbox \">";
        $e .= "<table>\n";
        $e .= "<tr><th colspan=\"2\">Investment Details</th></tr>\n";
        $e .= "<tr><td>Total Fees</td><td>{$data->totalchargeout}</td></tr>\n";
        $e .= "<tr><td>Estimated Costs</td><td>{$data->totalprojectcost}</td></tr>\n";
        $e .= "<tr><td>Estimated Margin</td><td>{$data->totalmargin}</td></tr>\n";
        $e .= "<tr><td>Actual Margin</td><td>{$data->totalactualmargin}</td></tr>\n";
        $e .= "<tr><td>Variance</td><td>{$data->variance}</td></tr>\n";
        $e .= "</table></div>\n";

        // other elements
        $e .= "<div class=\"pr_jsbox \">";
        $e .= "<table>\n";
        $e .= "<tr><th colspan=\"2\">Other Elements</th></tr>\n";
        $e .= "<tr><td>Date Entered</td><td>{$data->dateentered}</td></tr>\n";
        $e .= "<tr><td>Key Contact</td><td class=\"pr_jsrev\">{$data->keybuzzzcontact}</td></tr>\n";
        $e .= "<tr><td>Project Type</td><td class=\"pr_jsrev\">{$data->projecttype}</td></tr>\n";
        $e .= "<tr><td>Job Status</td><td class=\"pr_jsrev\">{$data->jobstatus}</td></tr>\n";
        $e .= "</table></div>";

        // cost details bit
        $e .= "<div class=\"pr_jsbox\">";
        $e .= "<table>\n";
        $e .= "<tr><th>Cost</th><th>Estimated</th><th>Actual</th></tr>\n";
        foreach ($costdata as $detail => $cost) {
            $e .= "<tr><th>{$costs[$detail]}</th><td>{$cost->est}</td><td>{$cost->act}</td></tr>\n";
        }
        $e .= "</table></div>\n";

        return $e;
    }

    // return columns for default display
    function getcolumns() {
        return false;
    }

    // get the headings for display
    function getheadings() {
        return false;
    }

    // return totals
    function gettotals() {
        return false;
    }

}

?>
