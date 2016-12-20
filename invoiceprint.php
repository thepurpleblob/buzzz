<?php
/************************************
 * Buzzz Job Management System
 *
 * Copyright, Howard Miller 2008
 *
 * Invoice Print Screen
 ************************************/

require_once( 'config.php' );

// get paramters
$id = lib::required_param( 'id' );

// get invoice record
$invoice = $db->get_record( 'invoice','id',$id );
$cid = $invoice->companyid;
$jid = $invoice->jobid;
$invoicenumber = $invoice->invoicenumber;
$currency = $invoice->currency;
$rate = $invoice->exchangerate;

// is the currency other that GBP?
$printcurrency = $currency!='British Pound';

// get invoicedetail records
$invoicedetails = $db->get_records( 'invoicedetail','invoiceid',$id );

// set monetary locale
$iso = array_search( $invoice->currency, $isocurrency );
setlocale( LC_MONETARY, $iso );
$locale = (object)localeconv();
//echo "<pre>"; print_r($locale); die;
$symbol = $locale->currency_symbol;

// convert date
$invoice->invoicedate = lib::decodemysqldate( $invoice->invoicedate );
$invoice->exchangeratedate = lib::decodemysqldate( $invoice->exchangeratedate );

// job details
$job = $db->get_record( 'job','id',$jid );

// company details
$company = $db->get_record( 'company','id',$cid );

// build customer address block (so no blank lines)
$address = '';
$address .= empty($invoice->contactname) ? '' : $invoice->contactname . '<br />';
$address .= empty($company->name) ? '' : $company->name . '<br />';
$address .= empty($invoice->address1) ? '' : $invoice->address1 . '<br />';
$address .= empty($invoice->address2) ? '' : $invoice->address2 . '<br />';
$address .= empty($invoice->address3) ? '' : $invoice->address3 . '<br />';
$address .= empty($invoice->town) ? '' : $invoice->town . '<br />';
$address .= empty($invoice->county) ? '' : $invoice->county . '<br />';
$address .= empty($invoice->country) ? '' : $invoice->country . '<br />';
$address .= empty($invoice->postcode) ? '' : $invoice->postcode . '<br />';


//====================
// DISPLAY PAGE
//====================

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="buzzzprint.css" >
  <title>Print screen for invoice <?php echo $invoicenumber ?></title>
</head>
<body>

<div id="pr_container">

<div id="pr_invoicenumber">
    Invoice <?php echo $invoicenumber; ?>
</div>

<div class="spacer">&nbsp;</div>
<div id="pr_address">
  <?php echo $address; ?><br />
</div>
<div id="pr_info">
  <table>
    <tr><td>Invoice number</td><td><?php echo $invoice->invoicenumber ?></td></tr>
    <tr><td>Invoice date</td><td><?php echo $invoice->invoicedate; ?></td></tr>
    <tr><td>PO Number</td><td><?php echo $invoice->ponumber; ?></td></tr>
    <?php if ($printcurrency) { ?>
        <tr><td>Invoice Currency</td><td><?php echo $currency; ?></td></tr>
    <?php } ?>
  </table>
</div>
<div class="spacer">&nbsp;</div>

<div id="pr_project">
    PROJECT DESCRIPTION: <?php echo $invoice->description; ?>
</div>

<!-- table for main invoice layout -->
<table id="pr_info">
  <tr id="pr_headings">
    <td>DESCRIPTION</td>
    <td>AMOUNT</td>
    <td>VAT AMOUNT</td>
    <td>TOTAL AMOUNT</td>
  </tr>
<?php foreach ($invoicedetails as $detail) { ?>
  <tr class="pr_detail">
    <td><?php echo $detail->description; ?></td>
    <td class="pr_money"><?php echo lib::money($detail->netamount,2,$currency,$rate); ?></td>
    <td class="pr_money"><?php echo lib::money($detail->vatamount,2,$currency,$rate); ?></td>
    <td class="pr_money"><?php echo lib::money($detail->totalamount,2,$currency,$rate); ?></td>
  </tr>
<?php } /* foreach */ ?>
<tr>
    <td colspan="2" class="pr_space">&nbsp;</td>
    <td class="pr_totals">Sub Total</td>
    <td class="pr_totals pr_money"><?php echo lib::money($invoice->subtotal,2,$currency,$rate) ?></td>
</tr>
<tr>
    <td colspan="2" class="pr_space">&nbsp;</td>
    <td class="pr_totals">VAT @<?php echo $config->vatrate; ?>%</td>
    <td class="pr_totals pr_money"><?php echo lib::money($invoice->vat,2,$currency,$rate) ?></td>
</tr>
<tr id="pr_late">
    <td colspan="4"><img src="spacer.png" /></td>
<tr>
<tr>
    <td class="pr_space">&nbsp;</td>
    <td class="pr_totals" colspan="2">Balance due in <?php echo $invoice->invoicetype; ?> days</td>
    <td class="pr_totals pr_money"><?php echo lib::money($invoice->balancein10days,2,$currency,$rate); ?></td>
</tr>
<tr>
    <td class="pr_space">&nbsp;</td>
    <td class="pr_totals" colspan="2">10% late payment</td>
    <td class="pr_totals pr_money"><?php echo lib::money($invoice->latepayment,2,$currency,$rate) ?></td>
</tr>
<tr>
    <td class="pr_space">&nbsp;</td>
    <td class="pr_totals" colspan="2">Balance due in over <?php echo $invoice->invoicetype; ?> days</td>
    <td class="pr_totals pr_money"><?php echo lib::money($invoice->balanceover10days,2,$currency,$rate); ?></td>
</tr>
</table>

<!-- boilerplate bits -->

<div id="pr_boilerplate">
  <div class="spacer">&nbsp;</div>
     <div id="pr_boilerplate_left">
         <?php echo lib::txt2html( $config->invoiceboilerplateleft ); ?>
     </div>
     <div id="pr_boilerplate_right">
         <?php echo lib::txt2html( $config->invoiceboilerplateright ); ?>
     </div>
  <div class="spacer">&nbsp;</div>
</div>

<div id="pr_footer">
    <?php echo lib::txt2html( $config->invoiceboilerplatebottom ); ?>
</div>

</div> <!-- pr_container -->

<body>
</html>
