<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Invoice Summary Screen
 * List all invoicess in the system
 * etc.
 ************************************/

require_once( 'config.php' );

// get parameters
$delete = lib::optional_param( 'delete',0 );
$delconf = lib::optional_param( 'delconf',0 );
$cid = lib::optional_param( 'cid',0 );
$jid = lib::optional_param( 'jid',0 );
$paid = lib::optional_param( 'paid',0 );
$id = lib::optional_param( 'id',0 );

// paid not paid
if (!empty($paid)) {
    if ($paid=='No') {
        $newpaid = 'Yes';
    }
    elseif ($paid=='Yes') {
        $newpaid = 'YesLate';
    }
    else {
        $newpaid = 'No';
    }
    $sql = "update invoice set paid='$newpaid' where id=$id";
    $db->update( $sql );
}

// setup menu
$menu = array();

// confirmed delete?
if (!empty($delconf)) {
    $db->update( "delete from invoice where id=$delconf" );
}

// get company info
if (!empty($cid)) {
    $company = $db->get_record( 'company','id',$cid );
    $name = "Invoices for $company->name";
    $menu['show all'] = '?';
    $menu['new invoice'] = "invoiceedit.php?cid=$cid";
}
else {
    $name = "All invoices";
}

// get job info (can't have company too)
if (!empty($jid)) {
    $cid = 0;
    $job = $db->get_record( 'job','id',$jid );
    $name = "Invoices for job $job->jobnumber";
    $menu['show all'] = '?';
    $menu['new invoice'] = "invoiceedit.php?jid=$jid";
}
else {
    $name = "All invoices";
}

// breadcrumb
if (empty($delete)) {
    $trail = array("<a href=\"index.php\">Home</a>","Invoices");
}
else {
    $trail = array("<a href=\"index.php\">Home</a>","<a href=\"invoicesummary.php\">Invoices</a>",
        "Confirm delete");
}

// construct the table
$table = new paginated_table;
$table->setName( 'invoicesummary' );
$table->setHeadings( array( 'Invoice number', 'Invoice Date', 'Paid', 'Client Name','Job Number', 'Project Name',
    'Subtotal','VAT','PO Number','Delete' ) );
$table->setFields( array( 'invoicenumber', 'ukdate', 'paid','name','jobnumber','projectname',
    'subtotal','vat','ponumber','options' ) );
$table->setSortfields( array( 'invoicenumber', 'invoicedate', 'paid','name','jobnumber','projectname',
    'subtotal','vat','ponumber','' ) );
$table->setBaseUrl( "?cid=$cid&amp;jid=$jid" );
$table->setSort( 'invoicenumber','desc' );
$table->setDataCallback( 'getInvoiceData' );

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

// display the page
echo "<h3>$name</h3>\n";
lib::linmenu( $menu );
$table->display();

lib::footer();

//====================
// FUNCTION
//====================

// callback function for data
function getInvoiceData( $table, $extrasql ) {
    global $db;
    global $cid;
    global $jid;
    global $cfg;
    global $invoicepaid;

    // company select snippet
    if (!empty($cid)) {
        $comsql = "and invoice.companyid=$cid ";
    }
    elseif (!empty($jid)) {
        $comsql = "and invoice.jobid=$jid";
    }
    else {
        $comsql = '';
    }

    //$table->setFields( array( 'invoicenumber', 'ukdate', 'paid','name','jobnumber','projectname',
    //'subtotal','vat','ponumber','options' ) );

    // need to build our own query to munge values
    $sql = "select invoice.id as id, invoicenumber,date_format(invoicedate,'{$cfg->dateformat}') as ukdate, paid, name, jobnumber, projectname, ".
        "subtotal, vat, ponumber, jobid, job.companyid as cid ";
    $sql .= 'from invoice, job, company where invoice.jobid=job.id and invoice.companyid=company.id ';
    $sql .= $comsql . $extrasql;
    $invoices = $db->query( $sql );

    // add controls
    foreach ($invoices as $invoice) {
        $iid = $invoice->id;
        $invoicenumber = $invoice->invoicenumber;

        // paid not paid
        $paid = $invoicepaid[$invoice->paid];
        $invoice->paid = "<a href=\"?id=$iid&amp;paid={$invoice->paid}&amp;jid=$jid&amp;cid=$cid\">$paid</a>";

        $invoice->invoicenumber = "<a href=\"invoiceedit.php?id=$iid\">{$invoice->invoicenumber}</a>";
        $invoice->jobnumber = "<a href=\"jobedit.php?id={$invoice->jobid}\">{$invoice->jobnumber}</a>";
        $invoice->name = "<a href=\"companyedit.php?id={$invoice->cid}\">{$invoice->name}</a>";
        $invoice->options .= 
            "<input type=\"checkbox\" onclick=\"confirmation('?delconf=$iid','?','Are you sure you want to delete $invoicenumber?')\" />";

        //numbers
        $invoice->subtotal = number_format($invoice->subtotal);
        $invoice->vat = number_format($invoice->vat);
    }

    return $invoices;
}
?>
