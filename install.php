<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Database installation
 * Performs initial installation
 * of database tables
 ************************************/

require_once( 'config.php' );

lib::header(array('Install'));

// we need to double check this should
// be called
$tables = $db->get_tables();
if (!empty($tables)) {
    lib::notice( 'The database is already set up', 'index.php' );
}



// create company table
lib::notice( 'Creating company table' );
$sql = <<<EOT
    create table company (
    id integer not null auto_increment primary key, 
    name text not null, 
    address1 text not null, 
    address2 text not null, 
    address3 text not null,
    town text not null,
    county text not null,
    country text not null,
    postcode text not null,
    telephone text not null,
    fax text not null,
    website text not null,
    status text not null,
    industrytype text not null,
    dateentered datetime,
    datemodified datetime,
    invoiceaddress1 text not null,
    invoiceaddress2 text not null,
    invoiceaddress3 text not null,
    invoicetown text not null,
    invoicecounty text not null,
    invoicecountry text not null,
    invoicepostcode text not null,
    invaddresssame char(10) default 'Yes' not null,
    invoicecurrency char(30) default 'British Pound' not null,
    notes text not null,
    suppliertype text not null,
    countrycovered text not null,
    oldid integer not null,
    oldisame integer not null,
    oldde char( 25 ) not null
    )
EOT;
$db->update( $sql );

lib::notice( 'Creating countries covered table' );
$sql = <<<EOT
    create table countrycovered (
    id integer auto_increment not null primary key,
    companyid integer not null,
    country text not null
    )
EOT;
$db->update( $sql );

// create contacts table
lib::notice( 'Creating contact table' );
$sql = <<<EOT
    create table contact (
    id integer not null auto_increment primary key,
    companyid integer not null,
    title text not null,
    firstname text not null,
    surname text not null,
    jobtitle text not null,
    telephone text not null,
    fax text not null,
    mobile text not null,
    email text not null,
    dateentered datetime,
    datemodified datetime,
    selected text not null,
    tag text not null,
    oldid integer not null,
    oldcompanyid integer not null,
    oldde char(25) not null,
    oldxmas char(25) not null,
    oldtag integer not null
    )
EOT;
$db->update( $sql );

// create jobs table
lib::notice( 'Creating jobs table' );
$sql = <<<EOT
    create table job (
    id integer not null auto_increment primary key,
    dateentered datetime not null,
    datemodified datetime not null,
    companyid integer not null,
    contactid integer not null,
    jobnumber text not null,
    projectname text not null,
    jobdescription text not null,
    jobstatus text not null,
    projecttype text not null,
    totalchargeout decimal(15,2) not null,
    totalprojectcost decimal(15,2) not null,
    totalmargin decimal(15,2) not null,
    marginpercent decimal(15,2) not null,
    totalactualcosts decimal(15,2) not null,
    totalactualmargin decimal(15,2) not null,
    totalactualmarginpercent decimal(15,2) not null,
    variance decimal(15,2) not null,
    variancepercent decimal(15,2) not null,
    calcoldway text not null,
    keybuzzzcontact text not null,
    reasonforfail text not null,
    invoiceschedule text not null,
    oldde char(25) not null,
    oldclientid integer not null,
    oldcontactid integer not null
    )
EOT;
$db->update( $sql );

// create job detail table 
lib::notice( 'Creating job detail table' );
$sql = <<<EOT
    create table jobdetail (
    id integer not null auto_increment primary key,
    jobid integer not null,
    element char(2) not null,
    quantity integer not null,
    fee decimal(15,2) not null
    )
EOT;
$db->update( $sql );

// create job costing table
lib::notice( 'Creating job costing table' );
$sql = <<<EOT
    create table jobcost (
    id integer not null auto_increment primary key,
    jobid integer not null,
    element char(2) not null,
    detail char(2) not null,
    cost char(2) not null,
    amount decimal(15,2) not null,
    actual decimal(15,2) not null
    )
EOT;
$db->update( $sql );

// create config table
lib::notice( 'Creating config table' );
$sql = <<<EOT
    create table config (
    id integer not null auto_increment primary key,
    name text not null,
    value text not null
    )
EOT;
$db->update( $sql );

// add 17.5% vat into table cos I'll forget
$db->update( "insert into config set name='vatrate', value='17.5'" ); 

// create invoice table
lib::notice( 'Creating invoice table' );
$sql = <<<EOT
    create table invoice (
    id integer not null auto_increment primary key,
    invoicenumber integer not null,
    companyid integer not null,
    jobid integer not null,
    subtotal decimal(15,2) not null,
    vat decimal(15,2) not null,
    latepayment decimal(15,2) not null,
    balancein10days decimal(15,2) not null,
    balanceover10days decimal(15,2) not null,
    paid char(10) default 'No' not null,
    paidlate char(10) default 'No' not null,
    contactname text not null,
    description text not null,
    invoicedate date not null,
    address1 text not null,
    address2 text not null,
    address3 text not null,
    town text not null,
    county text not null,
    country text not null,
    postcode text not null,
    ponumber text not null,
    invoicetype text not null,
    exchangerate decimal(15,4) default 1.00 not null,
    exchangeratedate date not null,
    oldcustomerid integer not null,
    oldjobnumber integer not null,
    invoiceaddresssame char(10) default 'Yes' not null,
    oldinvoicedate text not null
    )
EOT;
$db->update( $sql );

// create invoice detail table
lib::notice( 'Creating invoicedetail table' );
$sql = <<<EOT
    create table invoicedetail (
    id integer not null auto_increment primary key,
    invoiceid integer not null,
    description text not null,
    netamount decimal(15,2) not null,
    subjecttovat char(10) default 'Yes' not null,
    vatamount decimal(15,2) not null,
    totalamount decimal(15,2) not null,
    oldid integer not null,
    oldinvoicenumber integer not null
    )
EOT;
$db->update( $sql );

// that's it 
lib::notice( 'Database has been set up','dataload.php' );
?>


