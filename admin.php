<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Admin Edit Screen
 * Program settings
 ************************************/

require_once( 'config.php' );

// breadcrumb
$trail = array( '<a href="index.php">Home</a>','Admin');

// setup the form
$form = pieform( array(
    'name' => 'admin',
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'keybuzzzers' => array(
            'type' => 'text',
            'title' => 'Key buzzzers (separate by comma)',
            'defaultvalue' =>  empty($config->keybuzzzers) ? '' : $config->keybuzzzers
            ),
        'vatrate' => array(
            'type' => 'text',
            'title' => 'Standard VAT rate',
            'defaultvalue' => empty($config->vatrate) ? '0' : $config->vatrate
            ),
        'invoiceboilerplateleft' => array(
            'type' => 'textarea',
            'title' => 'Invoice boilerplate LHS',
            'cols' => 40,
            'rows' => 10,
            'defaultvalue' => empty($config->invoiceboilerplateleft) ? '' : $config->invoiceboilerplateleft
            ),
        'invoiceboilerplateright' => array(
            'type' => 'textarea',
            'title' => 'Invoice boilerplate RHS',
            'cols' => 40,
            'rows' => 10,
            'defaultvalue' => empty($config->invoiceboilerplateright) ? '' : $config->invoiceboilerplateright
            ),
        'invoiceboilerplatebottom' => array(
            'type' => 'textarea',
            'title' => 'Invoice boilerplate bottom/footer',
            'cols' => 40,
            'rows' => 5,
            'defaultvalue' => empty($config->invoiceboilerplatebottom) ? '' : $config->invoiceboilerplatebottom
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
function admin_submit(Pieform $form, $values) {
    global $db;

    lib::setconfig( 'keybuzzzers',$values['keybuzzzers'] );
    lib::setconfig( 'vatrate', $values['vatrate'] );
    lib::setconfig( 'invoiceboilerplateleft', $values['invoiceboilerplateleft'] );
    lib::setconfig( 'invoiceboilerplateright', $values['invoiceboilerplateright'] );
    lib::setconfig( 'invoiceboilerplatebottom', $values['invoiceboilerplatebottom'] );

    lib::redirect( 'index.php' );
    exit;
}

function admin_cancel_submit(Pieform $form, $values) {
    lib::redirect( 'index.php' );
}

//====================
// DISPLAY PAGE
//====================

lib::header($trail);

echo "<h3>Admin Settings</h3>\n";
echo "<div class=\"pform\">\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "$form\n";
echo "<div class=\"spacer\">&nbsp;</div>\n";
echo "</div>\n";

lib::footer();
?>
