<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Configuration
 * This file must be 'required' by
 * all other files
 ************************************/

// system configuration
unset( $cfg );

// database host
$cfg->dbhost = 'lurch.e-learndesign.scot';

// database name
$cfg->dbname = 'buzzz';

// database user
$cfg->dbuser = 'buzzzuser';

// database password
$cfg->dbpassword = 'buzzzpass';

//======================
// END OF CONFIGURATION
//======================

// various requires
require_once( 'db.php' );
require_once( 'pieform.php' );
require_once( 'lib.php' );
require_once( 'table.php' );
require_once( 'countries.php' );
require_once( 'currencies.php' );
require_once( 'currsymbols.php' );
require_once( 'pieformlib.php' );

// set exception handler (in lib)
set_exception_handler('exception_handler');

// random defines
$cfg->dateformat = '%d/%c/%y';

// company status
$clientstatusoptions = array();
$clientstatusoptions['Prospect'] = 'Prospect';
$clientstatusoptions['Suspect'] = 'Suspect';
$clientstatusoptions['Client'] = 'Client';
$clientstatusoptions['Supplier'] = 'Supplier';

// yes/no options
$yesnooptions = array();
$yesnooptions['Yes'] = 'Yes';
$yesnooptions['No'] = 'No';

// invoice paid choices
$invoicepaid = array();
$invoicepaid['No'] = 'No';
$invoicepaid['YesLate'] = 'Yes late';
$invoicepaid['Yes'] = 'Yes early';

// supplier types
$supplieroptions = array();
$supplieroptions['NA'] = 'Not applicable';
$supplieroptions['Online quant'] = 'Online quant';
$supplieroptions['Telephone interviews'] = 'Telephone interviews';
$supplieroptions['Face to face interview'] = 'Face to face interview';
$supplieroptions['Moderation'] = 'Moderation';
$supplieroptions['Qual recruitment'] = 'Qual recruitment';
$supplieroptions['Video editing'] = 'Video editing';
$supplieroptions['Filming'] = 'Filming';
$supplieroptions['Marketing support'] = 'Marketing support';
$supplieroptions['Office support'] = 'Office support';
$supplieroptions['Quant analysis'] = 'Quant analysis';
$supplieroptions['Translation'] = 'Translation';
$supplieroptions['Hostessing'] = 'Hostessing';
$supplieroptions['Consultancy'] = 'Consultancy';

// invoice schedule types
$scheduleoptions = array();
$scheduleoptions['100upfront'] = '100% up front';
$scheduleoptions['50/50'] = '50/50';
$scheduleoptions['50/25/25'] = '50/25/25';
$scheduleoptions['30/35/35'] = '30/35/35';
$scheduleoptions['100completion'] = '100% on completion';

// project status options
$statusoptions = array();
$statusoptions['Initial interest registered'] = 'Initial interest registered';
$statusoptions['Briefing Meeting'] = 'Briefing Meeting';
$statusoptions['Proposal'] = 'Proposal';
$statusoptions['Current job'] = 'Current job';
$statusoptions['Completed job'] = 'Completed job';
$statusoptions['Failed proposal'] = 'Failed proposal';

// project type options
$typeoptions = array();
$typeoptions['Qual'] = 'Qual';
$typeoptions['Quant'] = 'Quant';
$typeoptions['Consultancy'] = 'Consultancy';
$typeoptions['Qual and Quant'] = 'Qual and Quant';
$typeoptions['Training'] = 'Training';

// project detail elements
$detailelements = array();
$detailelements['OI'] = 'Online interviews';
$detailelements['TI'] = 'Telephone interviews';
$detailelements['FF'] = 'Face to face/Street interviews';
$detailelements['HT'] = 'Hall tests';
$detailelements['II'] = 'Intercept interviews';
$detailelements['DS'] = 'Discussion groups - standard';
$detailelements['DE'] = 'Discussion groups - extended';
$detailelements['VP'] = 'Vox pops';
$detailelements['WS'] = 'Workshops';
$detailelements['TD'] = 'Telephone depth interviews';
$detailelements['FD'] = 'Face to face depth interviews';
$detailelements['CY'] = 'Consultancy';
$detailelements['OT'] = 'Other';
$detailelements['PM'] = 'Project management';
$detailelements['AD'] = 'Additional Debrief';

// project costs
$costs = array();
$costs['RE'] = 'Recruitment';
$costs['IN'] = 'Incentives';
$costs['VH'] = 'Venue hire';
$costs['HS'] = 'Hostessing/supervision';
$costs['SP'] = 'Stimulus production';
$costs['EH'] = 'Equipment hire';
$costs['VE'] = 'Video editing';
$costs['FI'] = 'Filming';
$costs['DT'] = 'Document translation';
$costs['ST'] = 'Simultaneous translation of groups';
$costs['MO'] = 'Moderating';
$costs['MB'] = 'Moderator briefing';
$costs['EM'] = 'Extra moderators';
$costs['TC'] = 'Travel costs';
$costs['AC'] = 'Accommodation costs';
$costs['PT'] = 'Presentation, travel & accommodation';
$costs['QF'] = 'Quant fieldwork costs';
$costs['QS'] = 'Questionnaire set-up';
$costs['CD'] = 'Conding & DP';
$costs['DA'] = 'Data analysis';
$costs['FS'] = 'Freelance support';
$costs['SU'] = 'Sundries';
$costs['OC'] = 'Other costs';

// project costs matrix
$cmat = array();
$cmat['OI'] = array('RE','IN','SP','DT','PT','QF','QS','CD','DA','FS','SU','OC');
$cmat['TI'] = $cmat['OI'];
$cmat['FF'] = $cmat['OI'];
$cmat['HT'] = array('RE','IN','VH','HS','SP','EH','DT','TC','AC','PT','QF','QS','CD','DA','FS','SU','OC');
$cmat['DS'] = array('RE','IN','VH','HS','SP','EH','VE','FI','DT',
    'ST','MO','MB','EM','TC','AC','PT','FS','SU','OC');
$cmat['DE'] = $cmat['DS'];
$cmat['FD'] = array('RE','IN','VH','SP','EH','VE','FI','DT',
    'ST','MO','MB','EM','TC','AC','PT','FS','SU','OC');
$cmat['VP'] = $cmat['DS'];
$cmat['WS'] = $cmat['DS'];
$cmat['TD'] = $cmat['DS'];
$cmat['CY'] = array('VH','SP','EH','VE','FI','TC','AC','PT','FS','SU','OC');
$cmat['PM'] = $cmat['CY'];
$cmat['AD'] = $cmat['CY'];
$cmat['HT'] = array('RE','IN','VH','HS','SP','EH','VE','FI','DT','ST','MO',
    'MB','EM','TC','AC','PT','QF','QS','CD','DA','FS','SU','OC');
$cmat['OT'] = array_keys( $costs );

// invoice types (payment times)
$itype = array();
$itype['7'] = '7 days';
$itype['14'] = '14 days';
$itype['30'] = '30 days';

// session setup
session_name( 'buzzzjobs' );
session_start();

// connect to the database
$db = new db();
$db->connect( $cfg->dbhost, $cfg->dbuser, $cfg->dbpassword );
$db->select( $cfg->dbname );

// get the configuration settings
$tables = $db->get_tables(); 
if (!empty($tables)) {
    lib::getconfig();
    $config->databasesetup = true;
}
else {
    $config->databasesetup = false;
}
?>
