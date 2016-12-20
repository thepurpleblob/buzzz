<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Search Screen
 ************************************/

require_once( 'config.php' );

$trail = array("<a href=\"index.php\">Home</a>",'Search');

// construct the form
$form = pieform( array(
    'name' => 'search',
    'method' => 'post',
    'renderer' => 'div',
    'goto' => 'company.php',
    'elements' => array(
        'company' => array(
            'type' => 'text',
            'title' => 'Company name',
        ),
        'name' => array(
            'type' => 'text',
            'title' => 'Contact name'
        ),
        'suppliertype' => array(
            'type' => 'select',
            'options' => $supplieroptions,
            'title' => 'Supplier type'
        ),
        'countrycovered' => array(
            'type' => 'select',
            'options' => $countries,
            'title' => 'Country covered'
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array( 'Search', 'Cancel')
        )
    )
));

/*
 * process submitted form
 */
function search_submit(Pieform $form, $values) {

    // construct url for search results page
    $vals = (object)$values;
    $url = "company=$vals->company&"
         . "name=$vals->name&"
         . "suppliertype=$vals->suppliertype&"
         . "countrycovered=$vals->countrycovered";

    lib::redirect( 'searchresults.php?'.$url );
    exit;
}

function search_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'index.php' );
}


//====================
// DISPLAY PAGE
//====================

lib::header( $trail );

// display the search form
echo "<h3>Search</h3>\n";
echo "<div class=\"pform\">\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "$form\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "</div>\n";

lib::footer();

//====================
// FUNCTION
//====================

// callback function for data
function getCompanyData( $table, $extrasql ) {
    global $db;
    global $cfg;

    // need to build our own query to munge values
    $sql = "select id,name,town,status,date_format(dateentered,'{$cfg->dateformat}') as ukdate ";
    $sql .= 'from company ' . $extrasql;
    $companies = $db->query( $sql );

    // add controls
    foreach ($companies as $company) {
        $cid = $company->id;
        $company->options = "<a href=\"companyedit.php?id=$cid\" title=\"Edit entry\">";
        $company->options .= "<img src=\"pix/edit.png\" alt=\"Edit entry\" /></a>";
        $company->options .= "<a href=\"?delete=$cid\" title=\"Delete entry\">";
        $company->options .= "<img src=\"pix/delete.png\" ></a>";
        $company->options .= "<a href=\"contact.php?cid=$cid\" title=\"Show contacts\">";
        $company->options .= "<img src=\"pix/contact.png\" ></a>";
        $company->options .= "<a href=\"jobsummary.php?cid=$cid\" title=\"Show jobs\">";
        $company->options .= "<img src=\"pix/jobs.png\" /></a>";
    }

    return $companies;
}
?>
