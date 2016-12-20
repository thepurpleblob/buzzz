<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Job Edit Screen
 * Create a new or edit an existing
 * job
 ************************************/

require_once( 'config.php' );
require_once( 'table.php' );
require_once( 'total.php' );

// get paramters
$id = lib::optional_param( 'id',0 );
$cid = lib::optional_param( 'cid',0 );
$hide = lib::optional_param( 'hide','' );

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','<a href="jobsummary.php">Jobs</a>',
    'Edit Job' );

// hide setting for hide button
$hidebutton = empty($hide) ? 1 : 0;
$hidemessage = empty($hide) ? 'Hide' : 'Show';
$hideurl = "?id=$id&amp;cid=$cid&amp;hide=$hidebutton";

// if an id was supplied get the existing value from
// the database
if (!empty( $id )) {
    $job = $db->get_record( 'job','id',$id );
    $number = "$job->jobnumber";
    $cid = $job->companyid;

    // create the job menu
    $menu = array();
    $menu['invoices'] = "invoicesummary.php?jid=$id";
    $menu['new invoice'] = "invoiceedit.php?jid=$id";

    // do the totals now, in case they haven't been
    total( $id );
}
else {
    $number = 'New job';

    // allocate new id number
    $newjob = $db->query( 'select max(jobnumber)+1 as newjob from job',true,true );
}

// if a company was specified get the record
if (!empty($cid)) {
    $company = $db->get_record( 'company','id',$cid );
    $companyname = $company->name;
    $number = "$number for <a href=\"companyedit.php?id=$cid\">$companyname</a>";
}
else {
    // should not get here, must be $id or $cid
    throw new Exception('Must supply either job or company id');    
}

// get list of possible contacts for this company
$contacts = $db->get_records( 'contact','companyid',$cid );
if (!empty($contacts)) {
    $keycontacts = array();
    foreach ($contacts as $contact) {
        $keycontacts[$contact->id] = $contact->firstname . ' ' . $contact->surname;
    }
}
else {
    $keycontacts = array( '0'=>'No contacts' );
}

// get the keybuzzzers
$keybuzzzoptions = explode(',',$config->keybuzzzers);
if (empty($keybuzzzoptions)) {
    $keybuzzzoptions = array('--none--');
}

// setup the form
$form = pieform( array(
    'name' => 'jobedit',
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' =>  empty($job->id) ? '' : $job->id
        ),
        'companyid' => array(
            'type' => 'hidden',
            'value' => empty($job->companyid) ? $cid : $job->companyid
        ),
        'lefthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'jobnumber' => array(
                    'type' => 'text',
                    'title' => 'Job number',
                    'defaultvalue' => empty($job->jobnumber) ? $newjob : $job->jobnumber,
                    'disabled' => true,
                    'rules' => array('required'=>true, 'integer'=>true)
                    ),
                'projectname' => array(
                    'type' => 'text',
                    'title' => 'Project name',
                    'defaultvalue' => empty($job->projectname) ? '' : $job->projectname,
                    'rules' => array('required'=>true)
                    ),
                'contactid' => array(
                    'type' => 'select',
                    'options' => $keycontacts,
                    'title' => 'Key contact',
                    'defaultvalue' => empty($job->contactid) ? null : $job->contactid
                    ),
                'jobdescription' => array(
                    'type' => 'textarea',
                    'title' => 'Job description',
                    'cols' => 25,
                    'rows' => 5,
                    'defaultvalue' => empty($job->jobdescription) ? '' : $job->jobdescription
                    ),
                'invoiceschedule' => array(
                    'type' => 'select',
                    'title' => 'Invoice schedule',
                    'options' => $scheduleoptions,
                    'defaultvalue' => empty($job->invoiceschedule) ? '100upfront' : $job->invoiceschedule
                    ),
                'totalprojectcost' => array(
                    'type' => 'text',
                    'title' => 'Estimated total project cost',
                    'defaultvalue' => empty($job->totalprojectcost) ? 0 : $job->totalprojectcost
                    ),
                'totalmargin' => array(
                    'type' => 'text',
                    'title' => 'Estimated total margin',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalmargin) ? 0 : $job->totalmargin
                    ),
                'marginpercent' => array(
                    'type' => 'text',
                    'title' => 'Estimated margin percentage',
                    'disabled' => true,
                    'defaultvalue' => empty($job->marginpercent) ? 0 : $job->marginpercent * 100
                    ),
                )
            ),
        'righthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'keybuzzzcontact' => array(
                    'type' => 'select',
                    'options' => $keybuzzzoptions,
                    'title' => 'Key Buzzzer',
                    'defaultvalue' => empty($job->keybuzzzcontact) ? $keybuzzzoptions[0] : $job->keybuzzzcontact
                    ),
                'projecttype' => array(
                    'type' => 'select',
                    'options' => $typeoptions,
                    'title' => 'Project type',
                    'defaultvalue' => empty($job->projecttype) ? 'Qual' : $job->projecttype
                    ),
                'jobstatus' => array(
                    'type' => 'select',
                    'options' => $statusoptions,
                    'title' => 'Project status',
                    'defaultvalue' => empty($job->jobstatus) ? 'Initial interest registered' : $job->jobstatus
                    ),
                'reasonforfail' => array(
                    'type' => 'textarea',
                    'cols' => 25,
                    'rows' => 5,
                    'title' => 'Reason for fail',
                    'defaultvalue' => empty($job->reasonforfail) ? '' : $job->reasonforfail
                    ),
                'totalchargeout' => array(
                    'type' => 'text',
                    'title' => 'Total charge out',
                    'defaultvalue' => empty($job->totalchargeout) ? 0 : $job->totalchargeout
                    ),
                'totalactualcosts' => array(
                    'type' => 'text',
                    'title' => 'Total actual costs',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalactualcosts) ? 0 : $job->totalactualcosts
                    ),
                'totalactualmargin' => array(
                    'type' => 'text',
                    'title' => 'Total actual margin',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalactualmargin) ? 0 : $job->totalactualmargin
                    ),
                'totalactualmarginpercent' => array(
                    'type' => 'text',
                    'title' => 'Total actual margin%',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalactualmarginpercent) ? 0 : $job->totalactualmarginpercent * 100
                    ),
                )
            ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array( 'Save entry', 'Cancel')
        )
    )
));

// if job exists setup table for project details
if (!empty($job)) {
    // get existing value
    $jobdetails = $db->get_records( 'jobdetail','jobid',$id,'','element' );

    // are any items visible
    $visible = false;

    // build elements array
    $elements = array(
        'id' => array(
            'type' => 'hidden',
            'value' => empty($job->id) ? '' : $job->id
             ),
        'quantity' => array(
            'type' => 'html',
            'title' => ' ',
            'value' => 'Quantity',
            'class' => 'header'
            ),        
        'fee' => array(
            'type' => 'html',
            'title' => ' ',
            'value' => 'Fee',
            'class' => 'header'
            ),        
        );
    foreach ($detailelements  as $key => $detailelement) {
        // check if we are hiding an unused row 
        if (!empty($hide) and empty($jobdetails[$key])) {
            continue;
        }
        if (!empty($hide) and empty($jobdetails[$key]->quantity)) {
            if ($jobdetails[$key]->fee<0.005) {
                continue;
            }
        }

        $visible = true;
        $elements["{$key}_quantity"] = array(
            'title' => $detailelement,
            'type' => 'text',
            'defaultvalue' => empty($jobdetails[$key]->quantity) ? 0 : $jobdetails[$key]->quantity
        );
        $elements["{$key}_fee"] = array(
            'title' => $detailelement,
            'type' => 'text',
            'defaultvalue' => empty($jobdetails[$key]->fee) ? 0 : $jobdetails[$key]->fee
        );
    }
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array( 'Save entry', 'Cancel')
        );

    $detailform = pieform( array(
        'name' => 'jobdetail',
        'method' => 'post',
        'renderer' => 'multicolumntable',
        'elements' => $elements
        )
    );
    
}

/*
 * process submitted form
 */
function jobedit_submit(Pieform $form, $values) {
    global $db;
    global $id;

    // don't store values that are calculated
    unset( $values['totalmargin'] ); 
    unset( $values['marginpercent'] );
    unset( $values['totalactualcosts'] );
    unset( $values['totalactualmargin'] );
    unset( $values['totalactualmarginpercent'] );

    if (empty($id)) {
        $id = $db->insert_record( (object)$values, 'job' );
        $db->update( "update job set dateentered = cast(now() as datetime) where id=$id" );
    }
    else {
        $db->update_record( (object)$values, 'job' );
    }
    $db->update( "update job set datemodified = cast(now() as datetime) where id=$id" );

    // process the totals
    total( $id );

    lib::redirect( "jobedit.php?id=$id" );
    exit;
}

function jobdetail_submit(Pieform $form, $values) {
    global $db;
    global $detailelements;

    $values = $values;
    $id = $values['id'];

    // sum total fee
    $totalfee = 0;

    // store records
    foreach ($detailelements as $key => $detailelement) {
        // get needed values
        // convert empty response to 0 to be nice to db
        $quantity = $values["{$key}_quantity"];
        $quantity = empty($quantity) ? 0 : $quantity;
        $fee = $values["{$key}_fee"];
        $fee = empty($fee) ? 0 : $fee;

        // track total fee
        $totalfee += $fee;

        // see if it's there already
        $sql = "select * from jobdetail where jobid=$id and element='$key'";
        $old = $db->query( $sql,true );
    
        // either update or insert as appropriate 
        if (!empty($old))  {
            $sql = "update jobdetail set element='$key', jobid=$id, fee=$fee, quantity=$quantity where id={$old->id}";
        }
        else {
            $sql = "insert into jobdetail set jobid=$id, element='$key', fee=$fee, quantity=$quantity";
        }
        $db->update( $sql );
    }

    // update totalchargeout in job table
    $sql = "update job set totalchargeout=$totalfee, ".
        "totalactualmargin=$totalfee-totalactualcosts, ".
        "marginpercent=($totalfee-totalactualcosts)/$totalfee ".
        "where id=$id";
    $db->update( $sql );

    // do totals
    total( $id );

    lib::redirect( "jobedit.php?id=$id&hide=1" );
}

function jobedit_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'jobsummary.php' );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$number</h3>\n";
if (!empty($menu)) {
    lib::linmenu( $menu );
}
echo "<div class=\"pform\">$form</div>";

if (!empty($detailform)) {
    echo "<h3>Project cost analysis</h3>\n";
    lib::button( "$hidemessage unused elements",$hideurl );
    echo "<div class=\"pform\">$detailform</div>";
    if ($visible) {
        lib::button( 'Enter cost details',"jobcost.php?id=$id" );
    }
}

lib::footer();
?>
