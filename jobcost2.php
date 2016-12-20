<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Job Costing Screen
 * Part II
 ************************************/

require_once( 'config.php' );
require_once( 'table.php' );
require_once( 'total.php' );

// get paramters
$id = lib::required_param( 'id' );
$element = lib::required_param( 'element' );

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
$elementname = $detailelements[$element];
$number = "$number ($elementname) for $companyname";

// get the job cost records
$sql = "select * from jobcost where jobid=$id and element='$element'";
$jobcosts = $db->query( $sql,false,false,'detail' );

// get the appropriate cost details for this element
$details = $cmat[$element];

// build the elements array
$elements = array(
    'id' => array(
        'type' => 'hidden',
        'value' =>  $id
        ),
    'element' => array(
        'type' => 'hidden',
        'value' => $element
        ),
    'cost' => array(
        'type' => 'html',
        'title' =>  ' ',
        'value' => 'Estimated Cost',
        'class' => 'header'
        ),
    'actual' => array(
        'type' => 'html',
        'title' =>  ' ',
        'value' => 'Actual Cost',
        'class' => 'header'
        ),
    );
foreach ($details as $detail) {
    $elements["{$detail}_amount"] = array(
        'type' => 'text',
        'title' => $costs[$detail],
        'defaultvalue' => empty( $jobcosts[$detail]->amount ) ? 0 : $jobcosts[$detail]->amount
    ); 
    $elements["{$detail}_actual"] = array(
        'type' => 'text',
        'title' => $costs[$detail],
        'defaultvalue' => empty( $jobcosts[$detail]->actual ) ? 0 : $jobcosts[$detail]->actual
    ); 
}
$elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array( 'Save entry', 'Cancel')
    );

// construct the offending form
$form = pieform( array(
    'name' => 'jobcost',
    'method' => 'post',
    'renderer' => 'multicolumntable',
    'goto' => 'company.php',
    'elements' => $elements
));

/*
 * process submitted form
 */
function jobcost_submit(Pieform $form, $values) {
    global $db;
    global $details;

    $id = $values['id'];
    $element = $values['element'];

    // run through details and write the records
    foreach ($details as $detail) {
        $amount = $values["{$detail}_amount"];
        $actual = $values["{$detail}_actual"];

        // in the table already?
        $sql = "select * from jobcost where jobid=$id and element='$element' and detail='$detail'";
        $record = $db->query($sql,true);
        if (empty($record)) {
            $sql = "insert into jobcost set jobid=$id, element='$element', detail='$detail', ".
                "amount='$amount', actual='$actual'";
        }
        else {
            $sql = "update jobcost set amount='$amount', actual='$actual' where " .
                "id={$record->id}";
        }
        $db->update( $sql );
    }

    // total figures
    total( $id );

    lib::redirect( "jobcost?id=$id" );
}

function jobcost_cancel_submit(Pieform $form, $values) {
    global $id;
    lib::redirect( "jobcost?id=$id" );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$number</h3>\n";
echo "<div class=\"pform\">\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "$form\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "</div>\n";

lib::footer();
?>
