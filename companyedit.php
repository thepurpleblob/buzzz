<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Company Edit Screen
 * Create a new or edit an existing
 * company
 ************************************/

require_once( 'config.php' );

// get paramters
$cid = lib::optional_param( 'id',0 );
$cdel = lib::optional_param( 'cdel',0 );

// delete the country
if (!empty($cdel)) {
    $sql = "delete from countrycovered where id=$cdel";
    $db->update( $sql );
}

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','<a href="company.php">Companies</a>',
    'Edit Company' );

// if an id was supplied get the existing value from
// the database
if (!empty( $cid )) {
    $company = $db->get_record( 'company','id',$cid );
    $name = $company->name;
}
else {
    $name = 'New Company';
    $joblink = '';
    $invoicelink = '';
}

// create company menu
$menu = array();
if (!empty($cid)) {
    $menu['jobs'] = "jobsummary.php?cid=$cid";
    $menu['invoices'] = "invoicesummary.php?cid=$cid"; 
    $menu['contacts'] = "contact.php?cid=$cid";
    $menu['new job'] = "jobedit.php?cid=$cid";
    $menu['new contact'] = "contactedit.php?cid=$cid";
}

// create the countries covered list
if (!empty($cid)) {
    $coveredcountries = $db->get_records('countrycovered','companyid',$cid);
}
if (!empty($coveredcountries)) {
    $covered = '<div class="cntrycon"><ul class="countries">';
    foreach ($coveredcountries as $country) {
        $covered .= "<li>{$country->country}";
        $covered .= " <a href=\"?cdel={$country->id}&amp;id=$cid\">[X]</a></li>";
    }
    $covered = $covered . "</ul></div>\n";
}
else {
    $covered = 'None';
}


// setup the form
$form = pieform( array(
    'name' => 'companyedit',
    'method' => 'post',
    'renderer' => 'div',
    'goto' => 'company.php',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' =>  empty($company->id) ? '' : $company->id
        ),
        'lefthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'name' => array(
                    'type' => 'text',
                    'title' => 'Company name',
                    'rules' => array('required' => true),
                    'defaultvalue' => empty($company->name) ? '' : $company->name
                    ),
                'address1' => array(
                    'type' => 'text',
                    'title' => 'Address line 1',
                    'defaultvalue' => empty($company->address1) ? '' : $company->address1
                    ),
                'address2' => array(
                    'type' => 'text',
                    'title' => 'Address line 2',
                    'defaultvalue' => empty($company->address2) ? '' : $company->address2
                    ),
                'address3' => array(
                    'type' => 'text',
                    'title' => 'Address line 3',
                    'defaultvalue' => empty($company->address3) ? '' : $company->address3
                    ),
                'town' => array(
                        'type' => 'text',
                        'title' => 'Town',
                        'defaultvalue' => empty($company->town) ? '' : $company->town
                        ),
                'county' => array(
                        'type' => 'text',
                        'title' => 'County',
                        'defaultvalue' => empty($company->county) ? '' : $company->county
                        ),
                'country' => array(
                        'type' => 'select',
                        'options' => $countries,
                        'title' => 'Country',
                        'defaultvalue' => empty($company->country) ? 'United Kingdom' : $company->country
                        ),
                'postcode' => array(
                        'type' => 'text',
                        'title' => 'Post code',
                        'defaultvalue' => empty($company->postcode) ? '' : $company->postcode
                        ),
                'telephone' => array(
                        'type' => 'text',
                        'title' => 'Telephone',
                        'defaultvalue' => empty($company->telephone) ? '' : $company->telephone
                        ),
                'fax' => array(
                        'type' => 'text',
                        'title' => 'Fax',
                        'defaultvalue' => empty($company->fax) ? '' : $company->fax
                        ),
                'website' => array(
                        'type' => 'text',
                        'title' => 'Website',
                        'defaultvalue' => empty($company->website) ? '' : $company->website
                        ),
                'status' => array(
                        'type' => 'select',
                        'options' => $clientstatusoptions,
                        'title' => 'Client status',
                        'defaultvalue' => empty($company->status) ? 'Prospect' : $company->status
                        ),
                            'industrytype' => array(
                                'type' => 'text',
                                'title' => 'Client industry type',
                                'defaultvalue' => empty($company->industrytype) ? '' : $company->industrytype
                                ),
                    )
                ),
                'righthand' => array(
                        'type' => 'fieldset',
                        'collapsable' => false,
                        'class' => 'formcontainer',
                        'elements' => array( 
                            'invaddresssame' => array(
                                    'type' => 'select',
                                    'options' => $yesnooptions,
                                    'title' => 'Invoice address same?',
                                    'defaultvalue' => empty($company->invaddresssame) ? 'Yes' : $company->invaddresssame
                                    ),
                            'invoiceaddress1' => array(
                                'type' => 'text',
                                'title' => 'Invoice address line 1',
                                'defaultvalue' => empty($company->invoiceaddress1) ? '' : $company->invoiceaddress1
                                ),
                            'invoiceaddress2' => array(
                                'type' => 'text',
                                'title' => 'Invoice address line 2',
                                'defaultvalue' => empty($company->invoiceaddress2) ? '' : $company->invoiceaddress2
                                ),
                            'invoiceaddress3' => array(
                                'type' => 'text',
                                'title' => 'Invoice address line 3',
                                'defaultvalue' => empty($company->invoiceaddress3) ? '' : $company->invoiceaddress3
                                ),
                            'invoicetown' => array(
                                'type' => 'text',
                                'title' => 'Invoice town',
                                'defaultvalue' => empty($company->invoicetown) ? '' : $company->invoicetown
                                ),
                            'invoicecounty' => array(
                                    'type' => 'text',
                                    'title' => 'Invoice county',
                                    'defaultvalue' => empty($company->invoicecounty) ? '' : $company->invoicecounty
                                    ),
                            'invoicecountry' => array(
                                    'type' => 'select',
                                    'options' => $countries,
                                    'title' => 'Invoice country',
                                    'defaultvalue' => empty($company->invoicecountry) ? 'United Kingdom' : $company->invoicecountry
                                    ),
                            'invoicepostcode' => array(
                                    'type' => 'text',
                                    'title' => 'Invoice post code',
                                    'defaultvalue' => empty($company->invoicepostcode) ? '' : $company->invoicepostcode
                                    ),
                            'invoicecurrency' => array(
                                    'type' => 'select',
                                    'options' => $currencies,
                                    'title' => 'Invoice currency',
                                    'defaultvalue' => empty($company->invoicecurrency) ? 'British Pound' : $company->invoicecurrency
                                    ),
                            'notes' => array(
                                    'type' => 'textarea',
                                    'rows' => 5,
                                    'cols' => 25,
                                    'title' => 'Notes',
                                    'defaultvalue' => empty($company->notes) ? '' : $company->notes,
                                    ),
                            'suppliertype' => array(
                                    'type' => 'select',
                                    'options' => $supplieroptions,
                                    'title' => 'Supplier type',
                                    'defaultvalue' => empty($company->suppliertype) ? 'NA' : $company->suppliertype
                                    ),
                            'countrylist' => array(
                                    'type' => 'html',
                                    'nolabel' => false,
                                    'value' => '<label>Countries covered</label>'.$covered
                                    ),
                            'newcountry' => array(
                                    'type' => 'select',
                                    'options' => $countries,
                                    'title' => 'Add country',
                                    'defaultvalue' => 'NA'
                                    )
                                )
                            ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array( 'Save entry', 'Cancel')
        )
    )
));

/*
 * process submitted form
 */
function companyedit_submit(Pieform $form, $values) {
    global $db;
    global $cid;

    if (empty($cid)) {
        $cid = $db->insert_record( (object)$values, 'company' );
        $db->update( "update company set dateentered = cast(now() as datetime) where id=$cid" );
    }
    else {
        $db->update_record( (object)$values, 'company' );
    }

    // if new country has been selected add to countries covered
    if ($values['newcountry']!='NA') {
        $country = $values['newcountry'];
        $sql = "select * from countrycovered where companyid=$cid and country='$country'";
        $coveredcountries = $db->query($sql);
        if (empty($coveredcountries)) {
            $sql = "insert into countrycovered set companyid=$cid, country='$country'";
            $db->update( $sql );
        }
    }

    // update date modified
    $db->update( "update company set datemodified = cast(now() as datetime) where id=$cid" );

    lib::redirect( "companyedit.php?id=$cid" );
    exit;
}

function companyedit_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'company.php' );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$name</h3>\n";
lib::linmenu( $menu );
echo "<div class=\"pform\">\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "$form\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "</div>\n";

lib::footer();
?>
