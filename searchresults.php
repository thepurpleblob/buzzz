<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Search Results Screen
 ************************************/

require_once( 'config.php' );

// get parameters
$company = lib::optional_param('company','');
$name = lib::optional_param('name','');
$suppliertype = lib::optional_param('suppliertype','');
$countrycovered = lib::optional_param('countrycovered','');

// breadcrumb
$trail = array("<a href=\"index.php\">Home</a>","<a href=\"search.php\">Search</a>",
        "Search results");

// build url (again) for table
$url = "company=$company&"
     . "name=$name&"
     . "suppliertype=$suppliertype&"
     . "countrycovered=$countrycovered";

// construct the table
$table = new paginated_table;
$table->setName( 'searchresults' );
$table->setHeadings( array( 'Company', 'Name', 'Supplier Type','Country covered' ) );
$table->setFields( array( 'company', 'cname', 'suppliertype','countrycovered' ) );
$table->setSortfields( array( 'company', 'cname', 'suppliertype','countrycovered' ) );
$table->setDataCallback( 'getSearchresultData' );
$table->setBaseurl( "searchresults.php?$url" );
$table->setSort( 'company','asc' );

//====================
// DISPLAY PAGE
//====================

lib::header( $trail );

echo "<h3>Search results</h3>\n";
$table->display();

lib::footer();

//====================
// FUNCTION
//====================

// build sql query used here and there
function build_search_sql($extrasql='') {
    global $company, $name, $suppliertype, $countrycovered;

    // need to build our own query to munge values
    $companysql = !empty($company) ? "and company.name like '%$company%'" : '';
    $namesql = !empty($name) ? "and concat(firstname,' ',surname) like '%$name%'" : ''; 
    $suppliertypesql = (!empty($suppliertype) and ($suppliertype!='NA')) ? 
        "and company.suppliertype='$suppliertype'" : '';
    $countrycoveredsql = (!empty($countrycovered) and ($countrycovered!='NA')) ? 
        "and company.countrycovered='$countrycovered'" : '';

    $sql = "select company.name as company,".
        "concat(contact.firstname, ' ',contact.surname) as cname,".
        "suppliertype,".
        "countrycovered, ".
        "company.id as cid, ".
        "contact.id as nid ".
        "from company,contact where company.id=contact.companyid ".
        " $companysql $namesql $suppliertypesql $countrycoveredsql $extrasql ";
    return $sql;
}

// callback function for data
function getSearchresultData( $table, $extrasql ) {
    global $db;
    global $cfg;

    $sql = build_search_sql($extrasql);

    $results = $db->query( $sql );

    // modify results to add links
    foreach ($results as $tag=>$result) {
        $results[$tag]->company = "<a href=\"companyedit.php?id={$result->cid}\">{$result->company}</a>";
        $results[$tag]->cname = "<a href=\"contactedit.php?id={$result->nid}\">{$result->cname}</a>";
    }

    return $results;
}
?>
