<?php
require_once( 'config.php' );
lib::header(array('Install'));

// Load Company table
if (file_exists( 'data/client.csv' )) {
    lib::notice( "Loading Customer Data" );

    $fields = array(
        'oldid',
        'name',
        'address1',
        'address2',
        'address3',
        'town',
        'county',
        'country',
        'postcode',
        'telephone',
        'fax',
        'website',
        'status',
        'oldde',
        'invoiceaddress1',
        'invoiceaddress2',
        'invoiceaddress3',
        'invoicetown',
        'invoicecounty',
        'invoicecountry',
        'invoicepostcode',
        'oldisame'
);

    $db->insert_csv( 'data/client.csv', 'company', $fields );
    $db->update( "delete from company where name=''" );
    // note: there was no invoiceaddresses in the sample data, so leave default 'Yes'
    // $db->update( "update company set invaddresssame='Yes' where oldisame='1'" );
    // $db->update( "update company set invaddresssame='No' where oldisame='0'" );

    // nasty convert date sql
    $sql = "update company set dateentered=concat('20',substring(oldde,7,2),".
        "'-',substring(oldde,1,2),'-',substring(oldde,4,2),' ',substring(oldde,10))";
    $db->update( $sql );

    // fix the countries field
    require_once( 'countries.php' );
    foreach( $countries as $key => $country ) {
        $sk = addslashes( $key );
        $sc = addslashes( $country );
        $db->update( "update company set country='$sk' where country=\"$sc\"" );
    }
    $db->update( "update company set country='United Kingdom' where country='GB'" );
    $db->update( "update company set country='United Kingdom' where country='UK'" );
}

// Load Contact Table
if (file_exists( 'data/contact.csv' )) {
    lib::notice( "Load Contact Data" );

    $fields = array(
        'oldid',
        'oldcompanyid',
        'title',
        'firstname',
        'surname',
        'jobtitle',
        'telephone',
        'fax',
        'mobile',
        'email',
        'oldde',
        'oldxmas',
        'oldtag'
    );

    $db->insert_csv( 'data/contact.csv', 'contact', $fields );
    $db->update( "update contact set tag='Yes' where oldtag='1'" );

    // nasty convert date sql
    $sql = "update contact set dateentered=concat('20',substring(oldde,7,2),".
        "'-',substring(oldde,1,2),'-',substring(oldde,4,2),' ',substring(oldde,10))";
    $db->update( $sql );

    // link to company record
    $sql = "update contact set companyid = " .
        "(select id from company where company.oldid=oldcompanyid)";
    $db->update( $sql );
}

// Load Job table
if (file_exists( 'data/job4.csv' )) {
     lib::notice("Load Job Data" );

    $fields = array(
        'oldclientid',
        'oldcontactid',
        'jobnumber',
        'projectname',
        'jobdescription',
        'keybuzzzcontact',
        'invoiceschedule',
        'jobstatus',
        'reasonforfail',
        'projecttype',
        'totalchargeout',
        'totalprojectcost',
        'totalmargin',
        'marginpercent',
        'oldde',
        'totalactualcosts',
        'totalactualmargin',
        'totalactualmarginpercent',
        'variance',
        'variancepercent',
        'calcoldway'
    );

    $db->insert_csv( 'data/job4.csv', 'job', $fields );

    // nasty convert date sql
    $sql = "update job set dateentered=concat('20',substring(oldde,7,2),".
        "'-',substring(oldde,1,2),'-',substring(oldde,4,2),' ',substring(oldde,10))";
    $db->update( $sql );

    // fix drop downs
    $db->update( 'update job set jobstatus="Completed job" where jobstatus="Completed Job"' );

    // link to company record
    $sql = "update job set companyid = " .
        "(select id from company where company.oldid=oldclientid)";
    $db->update( $sql );

    // link to contact record
    $sql = "update job set contactid = " .
        "(select id from contact where contact.oldid=oldcontactid)";
    $db->update( $sql );

    // get list of key buzzcontacts
    $sql = "select distinct keybuzzzcontact from job where keybuzzzcontact<>''";
    $kbcontacts = $db->query( $sql,false,true );
    lib::setconfig('keybuzzzers',implode(',',$kbcontacts) );
}

// Load invoice table
if (file_exists( 'data/InvoiceHeader.csv' )) {
    lib::notice( "Load Invoice Data (main part)" );

    $fields = array(
        'invoicenumber',
        'oldcustomerid',
        'oldjobnumber',
        'oldinvoicedate',
        'subtotal',
        'vat',
        'latepayment',
        'balancein10days',
        'balanceover10days',
        'paid',
        'contact name',
        'description',
        'address1',
        'address2',
        'address3',
        'town',
        'county',
        'postcode',
        'invoiceaddresssame',
        'ponumber',
        'invoicetype',
        'country' 
    );

    $db->insert_csv( 'data/InvoiceHeader.csv', 'invoice', $fields );

    // nasty convert date sql
    $sql = "update invoice set invoicedate=concat('20',substring(oldinvoicedate,7,2),".
        "'-',substring(oldinvoicedate,1,2),'-',substring(oldinvoicedate,4,2))";
    $db->update( $sql );

    // link to company record
    $sql = "update invoice set companyid = " .
        "(select id from company where company.oldid=oldcustomerid)";
    $db->update( $sql );

    // match up jobid to jobnumber
    $sql = "update invoice set jobid = ".
        "(select id from job where job.jobnumber=oldjobnumber)";
    $db->update( $sql );

    // paid 1/0 to Yes/No
    $sql = "update invoice set paid=if(paid='1','Yes','No')";
    $db->update( $sql );
}

// Load invoice detail table
if (file_exists('data/InvoiceDetails.csv')) {
    lib::notice( 'Loading Invoice Data (detail part)' );

    $fields = array(
        'oldid',
        'oldinvoicenumber',
        'description',
        'netamount',
        'subjecttovat',
        'vatamount',
        'totalamount'
    );

    $db->insert_csv( 'data/InvoiceDetails.csv','invoicedetail', $fields );

    // match up invoiceid to oldinvoicenumber
    $sql = "update invoicedetail set invoiceid = ".
        "(select id from invoice where invoice.invoicenumber=oldinvoicenumber)";
    $db->update( $sql );

    // subject to vat TRUE/FALSE to Yes/No
    $sql = "update invoicedetail set subjecttovat=if(subjecttovat='TRUE','Yes','No')";
    $db->update( $sql );
}

lib::notice( 'Data has been loaded into tables','index.php' );

lib::footer();
?>
