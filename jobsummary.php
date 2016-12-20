<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Job Summary Screen
 * List all jobs in the system
 * etc.
 ************************************/

require_once( 'config.php' );

// get parameters
$delete = lib::optional_param( 'delete',0 );
$delconf = lib::optional_param( 'delconf',0 );
$cid = lib::optional_param( 'cid',0 );

// confirmed delete?
if (!empty($delconf)) {
    $db->update( "delete from job where id=$delconf" );
}

// setup menu items
$menu = array();

// get company info
if (!empty($cid)) {
    $company = $db->get_record( 'company','id',$cid );
    $name = "Jobs for $company->name";
    $menu['all jobs'] = '?';
    $menu['invoices'] = "invoicesummary.php?cid=$cid";
    $menu['new job for company'] = "jobedit.php?cid=$cid";
}
else {
    $name = "All jobs";
}

// breadcrumb
$trail = array("<a href=\"index.php\">Home</a>","Jobs");

// construct the table
$table = new paginated_table;
$table->setName( 'jobsummary' );
$table->setHeadings( array( 'Job number', 'Date entered', 'Client Name','Project Name',
    'Job Status','Project Type','Total Charge Out','Delete' ) );
$table->setFields( array( 'jobnumber', 'ukdate', 'name','projectname',
    'jobstatus','projecttype','totalchargeout','options' ) );
$table->setSortfields( array( 'jobnumber', 'job.dateentered', 'name','projectname',
    'jobstatus','projecttype','totalchargeout','' ) );
$table->setBaseUrl( "?cid=$cid" );
$table->setSort( 'jobnumber','desc' );
$table->setDataCallback( 'getJobData' );

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

// delete?
if ($delete) {
    lib::confirm( 'Are you sure you want to delete entry',"?delconf=$delete","?" );
}
else {

    // display the page
    echo "<h3>$name</h3>\n";
    lib::linmenu( $menu );
    $table->display();
}

lib::footer();

//====================
// FUNCTION
//====================

// callback function for data
function getJobData( $table, $extrasql ) {
    global $db;
    global $cid;
    global $cfg;

    // company select snippet
    if (!empty($cid)) {
        $comsql = "and job.companyid=$cid ";
    }
    else {
        $comsql = '';
    }

    // need to build our own query to munge values
    $sql = "select job.id as id,jobnumber,date_format(job.dateentered,'{$cfg->dateformat}') as ukdate, name, ".
        "company.id as cid, projectname, jobdescription, ";
    $sql .= 'jobstatus, projecttype, totalchargeout, totalactualmargin, (marginpercent*100) as marginpercent ';
    $sql .= 'from job, company where job.companyid=company.id ' . $comsql . $extrasql;
    $jobs = $db->query( $sql );

    // add controls
    foreach ($jobs as $job) {
        $jid = $job->id;
        $jobnumber = $job->jobnumber;
        $job->jobnumber = "<a href=\"jobedit.php?id=$jid\">$jobnumber</a>";
        $job->name = "<a href=\"companyedit.php?id={$job->cid}\">{$job->name}</a>";
        $job->options .= 
            "<input type=\"checkbox\" onclick=\"confirmation('?delconf=$jid','?','Are you sure you want to delete $jobnumber?')\" />";

        //numbers
        $job->totalactualmargin = number_format($job->totalactualmargin);
        $job->totalchargeout = number_format($job->totalchargeout);
        $job->marginpercent = number_format($job->marginpercent,0).'%';
    }

    return $jobs;
}
?>
