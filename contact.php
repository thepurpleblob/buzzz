<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Contact Details Screen
 * List all contacts in the system
 * etc.
 ************************************/

require_once( 'config.php' );
require_once( 'contactlib.php' );

// get parameters
$delete = lib::optional_param( 'delete',0 );
$delconf = lib::optional_param( 'delconf',0 );
$cid = lib::optional_param( 'cid',0 );

// get company details
if (!empty($cid)) {
    $company = $db->get_record( 'company', 'id', $cid );
    $name = $company->name;
}

// confirmed delete?
if (!empty($delconf)) {
    $db->update( "delete from contact where id=$delconf" );
}

// breadcrumb
if (empty($delete)) {
    $trail = array("<a href=\"index.php\">Home</a>","Contacts");
}
else {
    $trail = array("<a href=\"index.php\">Home</a>","<a href=\"contact.php\">Contacts></a>",
        "Confirm delete");
}

// construct the table
$table = defineContactTable();

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

// display the page
if (empty($name)) {
    echo "<h3>All contacts</h3>";
}
else {
    echo "<h3>Contacts for $name</h3>";
}
//  new contact only if linked to a company
if (!empty($cid)) {
    lib::linmenu( array("show all"=>"?","new contact"=>"contactedit.php?cid=$cid") );
}
$table->display();


lib::footer();

?>
