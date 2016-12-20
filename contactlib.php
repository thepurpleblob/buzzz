<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Contact Details Shared Functions 
 ************************************/

// construct table
function defineContactTable($url='') {
    $table = new paginated_table;
    $table->setName( 'contact' );
    $table->setHeadings( array( 'Title', 'Name', 'Job title','Email','Telephone','Delete' ) );
    $table->setFields( array( 'title', 'name', 'jobtitle','email','telephone','options' ) );
    $table->setSortfields( array( 'title', 'sortname', 'jobtitle','email','telephone','' ) );
    $table->setDataCallback( 'getContactData' );
    $table->setBaseUrl($url);
    return $table;
}

// callback function for data
function getContactData( paginated_table $table, $extrasql ) {
    global $db;
    global $cid;

    if (!empty($cid)) {
        $filter = " where companyid=$cid";
    }
    else {
        $filter = '';
    }

    // need to build our own query to munge values
    $sql = "select id,title,concat(firstname,' ',surname) as name, " .
        "concat(surname,' ',firstname) as sortname, jobtitle,email,companyid,telephone ";
    $sql .= 'from contact ' . $filter . $extrasql;
    $contacts = $db->query( $sql );

    // add controls
    foreach ($contacts as $contact) {
        $id = $contact->id;
        $name = $contact->name;
        $contact->email = "<a href=\"mailto:{$contact->email}\">{$contact->email}</a>";
        $contact->name = "<a href=\"contactedit.php?id=$id\">$name</a>";
        $contact->options .= 
            "<input type=\"checkbox\" onclick=\"confirmation('?delconf=$id','?','Are you sure you want to delete $name?')\" />";
    }

    return $contacts;
}
?>
