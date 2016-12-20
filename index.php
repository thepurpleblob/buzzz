<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Index
 * Main menu
 ************************************/

require_once( 'config.php' );

// clear session info if we get here
session_unset();

// if the database is empty then
// we need to run the install script
if (!$config->databasesetup) {
    lib::redirect( 'install.php' );
}


//===================
// DISPLAY PAGE
//===================

lib::header( array('Home') );
?>

<div id="mainmenu">
  <div class="sectionhead">
      Main Menu
  </div>
  <ul class="menu">
    <li><a href="company.php">Companies </a></li>
    <li><a href="contact.php">Contacts</a></li>
    <li><a href="jobsummary.php">Job Summary</a></li>
    <li><a href="invoicesummary.php">Invoicing</a></li>
    <li><a href="report.php">Reporting</a></li>
    <li><a href="search.php">Search</a><li>
    <li>&nbsp;</li>
    <li><a href="admin.php">Admin</a></li>
  </ul>
</div>

<?php
lib::footer();
?>
