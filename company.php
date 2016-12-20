<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Company Details Screen
 * List all companies in the system
 * etc.
 ************************************/

require_once( 'config.php' );

// get parameters
$delete = lib::optional_param( 'delete',0 );
$delconf = lib::optional_param( 'delconf',0 );

// confirmed delete?
if (!empty($delconf)) {
    $db->update( "delete from company where id=$delconf" );
}

// breadcrumb
if (empty($delete)) {
    $trail = array("<a href=\"index.php\">Home</a>","Companies");
}
else {
    $trail = array("<a href=\"index.php\">Home</a>","<a href=\"company.php\">Companies></a>",
        "Confirm delete");
}

// construct the table
$table = new paginated_table;
$table->setName( 'company' );
$table->setHeadings( array( 'Name', 'Town', 'Status','Date Entered','Supplier Type','Delete' ) );
$table->setFields( array( 'name', 'town', 'status','ukdate','suppliertype','options' ) );
$table->setSortfields( array( 'name', 'town', 'status','suppliertype','dateentered','' ) );
$table->setDataCallback( 'getCompanyData' );
$table->setSort( 'name','asc' );

//====================
// DISPLAY PAGE
//====================

lib::header( $trail );

//  new company
lib::button( 'Add a new Company', 'companyedit.php' );

// display the page
$table->display();

//  new company (again)
lib::button( 'Add a new Company', 'companyedit.php' );

lib::footer();

//====================
// FUNCTION
//====================

// callback function for data
function getCompanyData( $table, $extrasql ) {
    global $db;
    global $cfg;

    // need to build our own query to munge values
    $sql = "select id,name,town,status,date_format(dateentered,'{$cfg->dateformat}') as ukdate,suppliertype ";
    $sql .= 'from company ' . $extrasql;
    $companies = $db->query( $sql );

    // add controls
    foreach ($companies as $company) {
        $name = $company->name;
        $cid = $company->id;
        $company->name = "<a href=\"companyedit.php?id=$cid\">$name</a>";
        $company->options .= 
            "<input type=\"checkbox\" onclick=\"confirmation('?delconf=$cid','?','Are you sure you want to delete $name?')\" />";
    }

    return $companies;
}
?>
