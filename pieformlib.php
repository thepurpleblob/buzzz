<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Pieform Config functions
 ************************************/

/**
 * set up pieform defaults
 * pieform finds this automagically
 * (i.e., it has to be called this)
 */
function pieform_configure() {

$defaults = array(
    'method' => 'post',
    'autofocus' => true,
    'elementclasses' => true,
    'elements' => array(
        'sesskey' => array(
            'type' => 'hidden',
            'value' => lib::sesskey()
        )
    )
);
return $defaults;
}

/**
 * default validation of forms
 */
function pieform_validate(Pieform $form, $values) {
    // check sesskey was defined
    if (!isset($values['sesskey'])) {
        throw new Exception('No session key');
    }

    // ...and is correct
    $sesskey = lib::sesskey();
    if ($sesskey != $values['sesskey']) {
        throw new Exception('Invalid session key');
    }
}
?>
