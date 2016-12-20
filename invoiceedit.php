<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Invoice Edit Screen
 * Create a new or edit an existing
 * invoice
 ************************************/

require_once( 'config.php' );
require_once( 'total.php' );

// get paramters
// must have one of these, $id to edit others for new
$id = lib::optional_param( 'id',0 );
$cid = lib::optional_param( 'cid',0 );
$jid = lib:: optional_param( 'jid',0 );

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','<a href="jobsummary.php">Jobs</a>',
    'Edit Job' );

// !!! the order of the next 3 bits matters.
// !!! decreasing amounts of information

// if an invoice id was supplied get the existing value from
// the database
if (!empty( $id )) {
    total( $id );
    $invoice = $db->get_record( 'invoice','id',$id );
    $cid = $invoice->companyid;
    $jid = $invoice->jobid;
    $invoicenumber = $invoice->invoicenumber;
    $name = "Editing invoice $invoicenumber";

    // convert date
    $invoice->invoicedate = lib::decodemysqldate( $invoice->invoicedate );
    $invoice->exchangeratedate = lib::decodemysqldate( $invoice->exchangeratedate );
    if ($invoice->exchangeratedate=='00/00/0000') {
        $invoice->exchangeratedate=lib::ukdate();
    }
}

// if a job id was supplied...
if (!empty($jid)) {
    $job = $db->get_record( 'job','id',$jid );
    $cid = $job->companyid;
    if (empty($name)) {
        $name = "New invoice for job {$job->jobnumber}";
    }
}

// if a companyid was supplied
if (!empty($cid)) {
    $company = $db->get_record( 'company','id',$cid );
    if (empty($name)) {
        $name = "New invoice for company {$company->name}";
    }
}
else {
    // shouldn't really get here
    throw new Exception( 'No parameters for invoiceedit' );
}

// if not id, allocate new invoice number
if (empty($id)) {
    $invoicenumber = $db->query( 'select max(invoicenumber)+1 as newinvoice from invoice',true,true );
    $name .= " (#$invoicenumber)";
}

// if we don't have a jobnumber need the possibilities
if (empty($jid)) {
    $sql = "select id,jobnumber from job where companyid=$cid";
    $jobnumberopts = $db->query( $sql, false, true,'id' ); 
}

// if we have an id, the get the invoice details
if (!empty($id)) {
    $details = $db->get_records( 'invoicedetail','invoiceid',$id );
}

// if we have a company id but not an invoice id
// work out the default address
if (!empty($cid) and empty($id)) {
    $same = $company->invaddresssame == 'Yes';
    $address1 = $same ? $company->address1 : $company->invoiceaddress1;
    $address2 = $same ? $company->address2 : $company->invoiceaddress2;
    $address3 = $same ? $company->address3 : $company->invoiceaddress3;
    $town = $same ? $company->town : $company->invoicetown;
    $county = $same ? $company->county : $company->invoicecounty;
    $country = $same ? $company->country : $company->invoicecountry;
    $postcode = $same ? $company->postcode : $company->invoicepostcode;
}
else {
    $address1 = '';
    $address2 = '';
    $address3 = '';
    $town = '';
    $county = '';
    $country = '';
    $postcode = '';
}

// if country still blank make it United Kingdom
$country = empty($country) ? 'United Kingdom' : $country;

// get the contact list for the company
// if we have a job id spot the contact therein.
$jobcontact = null;
$contacts = $db->get_records( 'contact','companyid',$cid );
$contactoptions = array();
foreach ($contacts as $contact) {
    $fullname = $contact->firstname . ' ' . $contact->surname;
    $contactoptions[$fullname] = $fullname;
    if (!empty($jid) and ($contact->id == $job->contactid)) {
        $jobcontact = $fullname;
    }
}

// build element for contact 
// use select unless existing name doesn't match 
if (empty($id) or in_array($invoice->contactname,$contacts) or empty($invoice->contactname)) {
    // ok to use select
    $contactelement = array(
        'type' => 'select',
        'options' => $contactoptions,
        'title' => 'Contact name',
        'defaultvalue' => empty($invoice->contactname) ? $jobcontact : $invoice->contactname
        );
}
else {
    // need to use a disable text box
    $contactelement = array(
        'type' => 'text',
        'title' => 'Contact name',
        'disabled' => true,
        'defaultvalue' => $invoice->contactname
        );
}

// get possible job numbers
$jobnumbers = $db->get_records( 'job','companyid',$cid );

// if this is empty then we are stuffed - go to new job screen
if (empty($jobnumbers)) {
    notice( 'There are no jobs for this company. Create a job first',"job.php?cid=$cid");
    die;
}
$joboptions = array();
foreach ($jobnumbers as $jobnumber) {
    $shortname = substr($jobnumber->projectname,0,15) . '..';
    $joboptions[$jobnumber->id] = "{$jobnumber->jobnumber} $shortname";
}

// if we have an invoice record, a job record but no description
// copy the job description to the invoice
if (!empty($invoice) and !empty($job) and empty($invoice->description)) {
    $invoice->description = $job->jobdescription;
}

// setup the form
$form = pieform( array(
    'name' => 'invoiceedit',
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' =>  $id
        ),
        'companyid' => array(
            'type' => 'hidden',
            'value' => $cid
        ),
        'jobid' => array(
            'type' => 'hidden',
            'value' => $jid
        ),
        'lefthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'companyname' => array(
                    'type' => 'text',
                    'title' => 'Company name',
                    'defaultvalue' => $company->name,
                    'disabled' => true,
                    ),
                'address1' => array(
                    'type' => 'text',
                    'title' => 'Address 1',
                    'defaultvalue' => empty($invoice->address1) ? $address1 : $invoice->address1,
                    ),
                'address2' => array(
                    'type' => 'text',
                    'title' => 'Address 2',
                    'defaultvalue' => empty($invoice->address2) ? $address2 : $invoice->address2,
                    ),
                'address3' => array(
                    'type' => 'text',
                    'title' => 'Address 3',
                    'defaultvalue' => empty($invoice->address3) ? $address3 : $invoice->address3,
                    ),
                'town' => array(
                    'type' => 'text',
                    'title' => 'Town',
                    'defaultvalue' => empty($invoice->town) ? $town : $invoice->town,
                    ),
                'county' => array(
                    'type' => 'text',
                    'title' => 'County',
                    'defaultvalue' => empty($invoice->county) ? $county : $invoice->county,
                    ),
                'country' => array(
                    'type' => 'select',
                    'options' => $countries,
                    'title' => 'Country',
                    'defaultvalue' => empty($invoice->country) ? $country : $invoice->country,
                    ),
                'postcode' => array(
                    'type' => 'text',
                    'title' => 'Post Code',
                    'defaultvalue' => empty($invoice->postcode) ? $postcode : $invoice->postcode,
                    ),
                'contactname' => $contactelement,
                'description' => array(
                    'type' => 'textarea',
                    'title' => 'Description',
                    'cols' => 25,
                    'rows' => 5,
                    'defaultvalue' => empty($invoice->description) ? '' : $invoice->description
                    )
                )
            ),
        'righthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'invoicenumber' => array(
                    'type' => 'text',
                    'title' => 'Invoice number',
                    'disabled' => false,
                    'defaultvalue' => empty($invoice->invoicenumber) ? $invoicenumber : $invoice->invoicenumber
                    ),
                'invoicedate' => array(
                    'type' => 'text',
                    'title' => 'Invoice date',
                    'defaultvalue' => empty($invoice->invoicedate) ? lib::ukdate() : $invoice->invoicedate
                    ),
                'ponumber' => array(
                    'type' => 'text',
                    'title' => 'PO number',
                    'defaultvalue' => empty($invoice->ponumber) ? '' : $invoice->ponumber
                    ),
                'jobid' => array(
                    'type' => 'select',
                    'title' => 'Job number',
                    'options' => $joboptions,
                    'defaultvalue' => empty($invoice->jobid) ? null : $invoice->jobid
                    ),
                'invoicetype' => array(
                    'type' => 'select',
                    'title' => 'Invoice type',
                    'options' => $itype,
                    'defaultvalue' => empty($invoice->invoicetype) ? 7 : $invoice->invoicetype
                    ),
                'totalchargeout' => array(
                    'type' => 'text',
                    'title' => 'Total charge out',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalchargeout) ? 'Select job' : number_format($job->totalchargeout)
                    ),
                'totalactualmargin' =>array(
                    'type' => 'text',
                    'title' => 'Total actual margin',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalactualmargin) ? '' : number_format($job->totalactualmargin)
                    ),
                'totalactualmarginpercent' => array(
                    'type' => 'text',
                    'title' => 'Total actual margin %',
                    'disabled' => true,
                    'defaultvalue' => empty($job->totalactualmarginpercent) ? '' : number_format($job->totalactualmarginpercent*100,2).'%'
                    ),
                'currency' => array(
                    'type' => 'select',
                    'options' => $currencies,
                    'title' => 'Currency',
                    'disabled' => false,
                    'defaultvalue' => empty($invoice->currency) ? $company->invoicecurrency : $invoice->currency,
                    ),
                'exchangerate' => array(
                    'type' => 'text',
                    'title' => 'Exchange rate',
                    'defaultvalue' => empty($invoice->exchangerate) ? '1.0' : $invoice->exchangerate
                    ),
                'exchangeratedate' => array(
                    'type' => 'text',
                    'title' => 'Exchange rate date',
                    'defaultvalue' => empty($invoice->exchangeratedate) ? lib::ukdate() : $invoice->exchangeratedate
                    ),
                )
            ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array( 'Save entry', 'Cancel')
        )
    )
));

// if the invoice exists we can create the detail form
if (!empty($id)) {
    
    // get the invoicedetail
    $details = $db->get_records( 'invoicedetail','invoiceid',$id,'','id' );
    $detailcount = count($details);

    // initial elements for header
    $elements = array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id
            ),
        'count' => array(
            'type' => 'hidden',
            'value' => $detailcount
            ),
        'description' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'Description'
            ),
        'netamount' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'Net amount'
            ),
        'subjecttovat' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'VAT?'
            ),
        'vatamount' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'VAT amount'
            ),
        'totalamount' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'Total amount'
            ),
        'delete' => array(
            'type' => 'html',
            'title' => ' ',
            'class' => 'header',
            'value' => 'Delete?'
            )
        );

    // add in dummy row
    if ($invoice->currency=='British Pound') {
        $subjecttovat = 'Yes';
    }
    else {
        $subjecttovat = 'No';
    }
    $dummy = array( 'id'=>0,'description'=>'','netamount'=>0,'subjecttovat'=>$subjecttovat,
        'vatamount'=>0, 'totalamount'=>0 );
    $details['NEW'] = (object)$dummy;

    // rows will be numbered and actual id's hidden
    $count = 1;

    // construct element rows for details
    foreach ($details as $did => $detail) {
        $title = $did=='NEW' ? 'New row' :  "row $count";

        $elements["r{$count}_id"] = array(
            'type' => 'hidden',
            'value' => $detail->id
            );    
        $elements["r{$count}_description"] = array(
            'type' => 'text',
            'title' => "$title",
            'defaultvalue' => $detail->description
            );
        $elements["r{$count}_netamount"] = array(
            'type' => 'text',
            'title' => "$title",
            'defaultvalue' => $detail->netamount
            );
        $elements["r{$count}_subjecttovat"] = array(
            'type' => 'select',
            'options' => $yesnooptions,
            'title' => "$title",
            'defaultvalue' => $detail->subjecttovat
            );
        $elements["r{$count}_vatamount"] = array(
            'type' => 'text',
            'title' => "$title",
            'defaultvalue' => $detail->vatamount
            );
        $elements["r{$count}_totalamount"] = array(
            'type' => 'text',
            'title' => "$title",
            'defaultvalue' => $detail->totalamount
            );
        $elements["r{$count}_delete"] = array(
            'type' => 'checkbox',
            'title' => "$title",
            'defaultvalue' => ''
            );
        $count++;
    }

    // submit
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array( 'Save entry', 'Cancel')
        );

    $detailform = pieform( array(
        'name' => 'invoicedetailedit',
        'method' => 'post',
        'renderer' => 'multicolumntable',
        'elements' => $elements
        )
    );

    // yet another form for the totals.
    // this one never gets submittedd, just for formatting
    $totels = array();
    $totels['subtotal'] = array(
        'type' => 'text',
        'title' => 'Subtotal',
        'disabled' => true,
        'value' => lib::money($invoice->subtotal,2,$invoice->currency,$invoice->exchangerate)
        );
    $totels['vat'] = array(
        'type' => 'text',
        'title' => 'VAT',
        'disabled' => true,
        'value' => lib::money($invoice->vat,2,$invoice->currency,$invoice->exchangerate)
        );
    $totels['latepayment'] = array(
        'type' => 'text',
        'title' => '10% late payment charge',
        'disabled' => true,
        'value' => lib::money($invoice->latepayment,2,$invoice->currency,$invoice->exchangerate)
        );
    $totels['balancein10days'] = array(
        'type' => 'text',
        'title' => "Balance due in {$invoice->invoicetype} days",
        'disabled' => true,
        'value' => lib::money($invoice->balancein10days,2,$invoice->currency,$invoice->exchangerate)
        );
    $totels['balanceover10days'] = array(
        'type' => 'text',
        'title' => "Balance due over {$invoice->invoicetype} days",
        'disabled' => true,
        'value' => lib::money($invoice->balanceover10days,2,$invoice->currency,$invoice->exchangerate)
        );

    $totalsform = pieform( array(
        'name' => 'totalsdisplay',
        'method' => 'post',
        'renderer' => 'div',
        'elements' => $totels
        )
    );
}

/*
 * process submitted form
 */
function invoiceedit_submit(Pieform $form, $values) {
    global $db;
    global $id;

    $values = (object)$values;

    // return dates to mysql format
    $values->invoicedate = lib::encodemysqldate( $values->invoicedate );
    $values->exchangeratedate = lib::encodemysqldate( $values->exchangeratedate );

    if (empty($id)) {
        $id = $db->insert_record( $values, 'invoice' );
    }
    else {
        $db->update_record( $values, 'invoice' );
    }

    lib::redirect( "invoiceedit.php?id=$id" );
}


function invoiceedit_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'invoicesummary.php' );
}

function invoicedetailedit_submit(Pieform $form, $values) {
    global $db;
    global $id;
    global $config;

    $detailcount = $values['count'];

    // total up these things
    $subtotal = 0;
    $vat = 0;

    // loop through detailcount+1 (for NEW one)
    for ($i=1; $i<=$detailcount+1; $i++) {
        $did = $values["r{$i}_id"];
        $description = $values["r{$i}_description"];
        $netamount = $values["r{$i}_netamount"];
        $subjecttovat = $values["r{$i}_subjecttovat"];
        $vatamount = $values["r{$i}_vatamount"];
        $totalamount = $values["r{$i}_totalamount"];
        $delete = $values["r{$i}_delete"];

        // if delete or no description do nothing for now
        if (empty($delete) and !empty($description)) {

            // if a netamount mount do the sums (beware floating points!!)
            if ($netamount>0.009) {
                if ($subjecttovat=='Yes') {
                    $vatamount = $netamount * ($config->vatrate/100);
                    $totalamount = $netamount * (1 + $config->vatrate/100);
                }
                else {
                    $vatamount = 0;
                    $totalamount = $netamount;
                }
            }

            // if $did is zero then this is the new one (insert)
            if ($did==0) {
                $sql = "insert into invoicedetail set invoiceid=$id, description='$description',".
                    "subjecttovat='$subjecttovat', netamount=$netamount, vatamount=$vatamount, totalamount=$totalamount";
            }
            else {
                $sql = "update invoicedetail set invoiceid=$id, description='$description',".
                    "subjecttovat='$subjecttovat', netamount=$netamount, vatamount=$vatamount, totalamount=$totalamount ".
                    "where id=$did";
            }
            $db->update( $sql );
            $subtotal += $netamount;
            $vat += $vatamount;
        }

        // if delete then delete the record (surprise)
        if (!empty($delete)) {
            $sql = "delete from invoicedetail where id=$did";
            $db->update( $sql );
        }
    }

    // some more dead hard sums
    $latepayment = $subtotal * 0.1;
    $balancein10days = $subtotal + $vat;
    $balanceover10days = $subtotal + $vat + $latepayment;

    // write the revised values to the invoice table
    $sql = "update invoice set subtotal=$subtotal, vat=$vat, latepayment=$latepayment, ".
        "balancein10days=$balancein10days, balanceover10days=$balanceover10days ".
        "where id=$id";
    $db->update( $sql );

    lib::redirect( "invoiceedit.php?id=$id" );
}

function invoicedetailedit_cancel_submit(Pieform $form, $values) {
    global $id;

    lib::redirect( "invoiceedit.php?id=$id" );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$name</h3>\n";
echo "<div class=\"pform\">$form</div>";

if (!empty($detailform)) {
    echo "<div class=\"pform\">$detailform</div>";
    echo "<div class=\"pform\">";
    echo "$totalsform";
    if ($invoice->subtotal>0) {
        lib::button( 'Print Invoice (opens new page)',"invoiceprint.php?id=$id","_invprint" ); 
    }
    echo "</div>";
}

lib::footer();
?>
