<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Reports
 * Main menu
 ************************************/

require_once( 'config.php' );
require_once( 'report_class.php' );

// get parameters
$type = lib::optional_param( 'type','' );

// breadcrumb
$trail = array("<a href=\"index.php\">Home</a>","Reports");

// if type is specified then going for setup page
if (!empty($type)) {
    $file = "reports/$type.php";
    if (!file_exists( $file )) {
        throw new Exception( "report file not found = $file" );
    }
    require_once( $file );    
    $reportclass = "report_$type";
    $report = new $reportclass;
    $setupform = $report->setupform();
    $description = $report->getdescription();
    $setupform = $report->buildform( $setupform, $type );
    $form = pieform( $setupform );
}
else {
    // get plugins
    $plugins = scandir( 'reports' );
    $menuhtml = '<ul class="menu">';
    foreach ($plugins as $plugin) {
        if ($plugin[0]=='.') {
            continue;
        }
        $file = "reports/$plugin";
        if (!file_exists( $file )) {
            throw new Exception( "report file not found = $file" );
        }
        require_once( $file );
        $name = basename( $plugin,'.php');
        $reportclass = "report_$name";
        $report = new $reportclass;
        $description = $report->getdescription();
        $setupform = $report->setupform();
        if (!$setupform) {
            $menuhtml .= "<li><a href=\"reportview.php?type=$name&amp;mode=d\">$description</a>"; 
            $menuhtml .= "&nbsp; [<a href=\"reportview.php?type=$name\">Download</a>]</li>";
        }
        else {
            $menuhtml .= "<li><a href=\"?type=$name\">$description</a>"; 
        }
        $menuhtml .= "</li>\n";
    }
    $menuhtml .= "</ul>\n";
}

// function to accept setupform
function reportsetup_submit( Pieform $form, $values ) {
    $query = http_build_query( $values );
    lib::redirect( "reportview.php?$query" );
};

//===================
// DISPLAY PAGE
//===================

lib::header($trail);

if (!empty($form)) {
    echo "<h3>Parameters for $description</h3>\n";
    echo $form;
}
else {
?>

<div id="mainmenu">
  <div class="sectionhead">
      Reports Menu
  </div>
  <?php echo $menuhtml ?>
</div>

<?php
}

lib::footer();
?>
