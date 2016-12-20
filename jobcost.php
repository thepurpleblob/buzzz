<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Job Costing Screen
 ************************************/

require_once( 'config.php' );
require_once( 'table.php' );

// get paramters
$id = lib::required_param( 'id' );
$all = lib::optional_param( 'all',0 );

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','<a href="jobsummary.php">Jobs</a>',
    "<a href=\"jobedit.php?id=$id\">Job edit</a>",'Cost Job' );

// get the existing value from
// the database
$job = $db->get_record( 'job','id',$id );
$number = "$job->jobnumber";
$cid = $job->companyid;

// get the company record
$company = $db->get_record( 'company','id',$cid );
$companyname = $company->name;
$number = "<a href=\"jobedit.php?id=$id\">$number</a> for $companyname";

// get the job detail records
$jobdetails = $db->get_records( 'jobdetail','jobid',$id,'','element' );

// get elements (all or just ones with values)
$sql = "select * from jobdetail where jobid=$id ";
if (empty($all)) {
    $sql .= "and quantity>0 and fee>0";
}
$elements = $db->query( $sql,false,false,'element' );

// build element select html
$es =  "<div id=\"elementselect\">\n";
$es .= "Select the element to analyse cost:\n";
$es .= "<ul>\n";
foreach ($detailelements as $key => $element) {
    if (array_key_exists($key,$elements)) {
        $es .= "<li><a href=\"jobcost2.php?id=$id&amp;all=$all&amp;element=$key\">";
        $es .= "$element</a></li>\n";
    }
}
$es .= "</ul>\n";
$allmarkup = empty($all) ? 'all=1' : 'all=0';
$alltext = empty($all) ? 'Show all elements' : 'Show only elements with values';
$es .= "<a href=\"jobcost.php?id=$id&amp;$allmarkup\">$alltext</a>\n";
$es .= "</div>\n";

/*
 * process submitted form
 */

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$number</h3>\n";
echo $es;

lib::footer();
?>
