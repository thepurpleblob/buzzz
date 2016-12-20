<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Library function to calculate
 * costing totals
 ************************************/

/**
 * do all the calculations for a job
 * $id job id
 */
function total( $id ) {
    global $db;

    // total up the job details to get the total charge out
    $sql = "select sum(fee) from jobdetail where jobid=$id";
    $totalchargeout = $db->query( $sql,true,true );
    if ($totalchargeout!==null) {
        $sql = "update job set totalchargeout=$totalchargeout where id=$id";
        $db->update( $sql );
    }

    //  sum the job costs into project estimated and actual costs
    $sql = "select sum(amount) from jobcost where jobid=$id";
    $totalprojectcost = $db->query( $sql,true,true );
    if ($totalprojectcost!==null) {
        $sql = "update job set totalprojectcost=$totalprojectcost where id=$id";
        $db->update( $sql );
    }
    $sql = "select sum(actual) from jobcost where jobid=$id";
    $totalactualcosts = $db->query( $sql,true,true );
    if ($totalactualcosts!==null) {
        $sql = "update job set totalactualcosts=$totalactualcosts where id=$id";
        $db->update( $sql );
    }

    // do the margin calculations for the estimates (ie, not actual)
    $sql = "update job set totalmargin=totalchargeout-totalprojectcost, ".
        "marginpercent = (totalchargeout-totalprojectcost)/totalchargeout ".
        "where id=$id";
    $db->update( $sql );

    // do the margin calculations for the actual amounts
    $sql = "update job set totalactualmargin=totalchargeout-totalactualcosts, ".
        "totalactualmarginpercent = (totalchargeout-totalactualcosts)/totalchargeout ".
        "where id=$id";
    $db->update( $sql );

    // do the variance calculation
    $sql = "update job set variance=totalactualmargin-totalmargin where id=$id";
    $db->update( $sql );
}

?>
