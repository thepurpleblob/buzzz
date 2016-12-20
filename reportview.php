<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Report View
 ************************************/

require_once( 'config.php' );
require_once( 'report_class.php' );

// get parameters
$type = lib::optional_param( 'type','' );
$mode = lib::optional_param( 'mode','xml' ); // display or xml

$file = "reports/$type.php";
if (!file_exists( $file )) {
    throw new Exception( "report file not found = $file" );
}
require_once( $file );
$reportclass = "report_$type";
$report = new $reportclass;

// filename for xml 
$filename = "$type" . date( 'Ymd-His' );

$data = $report->getdata();
$description = $report->getdescription();
// :echo "<pre>"; print_r( $data ); die;

// if xml dump the data and exit
if ($mode=='xml') {
    if (!$report->datatoxml( $data, $filename )) {
        lib::redirect( 'report.php' );
    }
    die;
}


// try for custom format, if this is defined then forget
// everything else and just display
$custom = $report->customformat();

if (empty($custom)) {
    // table stuff
    $fields = $report->getcolumns();
    $headings = $report->getheadings();
    $table = $report->displaytable( $data, $fields, $headings );
    $totals = $report->gettotals();

    // totals
    if (!empty($totals)) {
        $tothtml = '<table id="totals">';
        foreach  ($totals as $key => $total) {
            $tothtml .= "<tr><td>$key</td><td>$total</td></tr>\n";
        }
        $tothtml .= "</table>";
    }
}

//===================
// DISPLAY PAGE
//===================

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <link rel="stylesheet" type="text/css" href="buzzzprint.css" >
  <title>Report <?php echo $description ?></title>
</head>
<body>

<div id="pr_report">
<?php
if (!empty($custom)) {
    echo $custom;
}
else {
    echo "<h2>$description</h2>";
    $table->display();
    if (!empty($tothtml)) {
        echo "<h3>Report Totals</h3>\n";
        echo $tothtml;
    }
    ?>
    <img src="pix/buzzzlogo_mono.png" />
<?php } ?>
</div> <!-- pr_report -->

</body>
</html>
