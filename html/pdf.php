<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage billing
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$filename = "observium-report.pdf";
$html     = "";
$type     = (isset($_GET['type']) ? $_GET['type'] : "");
$report   = (isset($_GET['report']) ? $_GET['report'] : "");

include_once("../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { $html .= "unauthenticated"; }

require_once("includes/tcpdf/config/lang/eng.php");
require_once("includes/tcpdf/tcpdf.php");

// Extend TCPDF to use custom footer
class ObsPDF extends TCPDF
{
  public function Footer()
  {
    // Posistion at 15mm from bottom
    $this->SetY(-15);
    // Set Font
    $this->SetFont('helvetica', 'N', 8);
    // Set Footer text
    $this->Cell(0, 0, 'Created by '.OBSERVIUM_PRODUCT.' ('.OBSERVIUM_URL.')', 0, false, 'L', 0, OBSERVIUM_URL, 0, false, 'M', 'M');
    $this->Cell(10, 0, 'Page '.$this->getAliasNumPAge().' of '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'M', 'M');
  }
}

// create new PDF document
$pdf = new ObsPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document security
$protection['permissions'] = array('modify', 'copy', 'annot-forms', 'fill-forms');
$protection['userpass']    = null;
//$protection['ownerpass']  = "6q49qp783sqo8p3o45q30nno51q01q35";
$protection['ownerpass']   = str_rot13(md5(str_rot13(pow(rand(), rand(0, 1000)))));
$protection['mode']        = 3;
$protection['pubkey']      = null;
$pdf->SetProtection($protection['permissions'], $protection['userpass'], $protection['ownerpass'], $protection['mode'], $protection['pubkey']);

// disable header
$pdf->setPrintHeader(false);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 10, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

if ($_SESSION['authenticated'])
{
  if ($type == "billing")
  {
    if ($report == "history")
    {
      include($config['html_dir']."/pages/bill/pdf_history.inc.php");
    }
  }
}

// Print text using writeHTMLCell()
$pdf->writeHTML($html, $ln=true, $fill=false, $reseth=true, $cell=false, $align='');

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($filename, 'I');

?>
