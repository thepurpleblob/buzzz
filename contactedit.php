<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Contact Edit Screen
 * Create a new or edit an existing
 * contact
 ************************************/

require_once( 'config.php' );

// get paramters
$id = lib::optional_param( 'id',0 );
$cid = lib::optional_param( 'cid',0 );

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','<a href="contact.php">Contacts</a>',
    'Edit Contact' );

// if an id was supplied get the existing value from
// the database
if (!empty( $id )) {
    $contact = $db->get_record( 'contact','id',$id );
    $name = "$contact->firstname $contact->surname";
    $cid = $contact->companyid;
}
else {
    $name = 'New Contact';
}

// if a company was specified get the record
if (!empty($cid)) {
    $company = $db->get_record( 'company','id',$cid );
    $companyname = $company->name;
    $name = "$name for $companyname";
}

// setup the form
$form = pieform( array(
    'name' => 'contactedit',
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' =>  empty($contact->id) ? '' : $contact->id
        ),
        'companyid' => array(
            'type' => 'hidden',
            'value' => $cid
            ),
        'lefthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'title' => array(
                    'type' => 'text',
                    'title' => 'Title',
                    'defaultvalue' => empty($contact->title) ? '' : $contact->title
                    ),
                'firstname' => array(
                    'type' => 'text',
                    'title' => 'Firstname',
                    'defaultvalue' => empty($contact->firstname) ? '' : $contact->firstname,
                    'rules' => array( 'required' => true )
                    ),
                'surname' => array(
                    'type' => 'text',
                    'title' => 'Surname',
                    'defaultvalue' => empty($contact->surname) ? '' : $contact->surname,
                    'rules' => array( 'required' => true )
                    ),
                'jobtitle' => array(
                    'type' => 'text',
                    'title' => 'Job title',
                    'defaultvalue' => empty($contact->jobtitle) ? '' : $contact->jobtitle,
                    ),
                'telephone' => array(
                        'type' => 'text',
                        'title' => 'Telephone',
                        'defaultvalue' => empty($contact->telephone) ? '' : $contact->telephone,
                        ),
                'fax' => array(
                        'type' => 'text',
                        'title' => 'Fax',
                        'defaultvalue' => empty($contact->fax) ? '' : $contact->fax,
                        ),
                'mobile' => array(
                        'type' => 'text',
                        'title' => 'Mobile',
                        'defaultvalue' => empty($contact->mobile) ? '' : $contact->mobile,
                        ),
                'email' => array(
                        'type' => 'text',
                        'title' => 'Email',
                        'defaultvalue' => empty($contact->email) ? '' : $contact->email,
                        )
                )
            ),
        'righthand' => array(
            'type' => 'fieldset',
            'collapsable' => false,
            'class' => 'formcontainer',
            'elements' => array(    
                'selected' => array(
                    'type' => 'select',
                    'options' => $yesnooptions,
                    'title' => 'Selected',
                    'defaultvalue' => empty($contact->selected) ? 'No' : $contact->selected
                    ),
                'tag' => array(
                    'type' => 'select',
                    'options' => $yesnooptions,
                    'title' => 'Tag',
                    'defaultvalue' => empty($contact->tag) ? 'No' : $contact->tag
                    ),
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
function contactedit_submit(Pieform $form, $values) {
    global $db;
    global $id;

    if (empty($id)) {
        $db->insert_record( (object)$values, 'contact' );
    }
    else {
        $db->update_record( (object)$values, 'contact' );
        $db->update( "update contact set datemodified = cast(now() as datetime) where id=$id" );
    }

    lib::redirect( 'contact.php' );
    exit;
}

function contactedit_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'contact.php' );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>$name</h3>\n";
echo "<div class=\"pform\">$form</div>";

lib::footer();
?>
